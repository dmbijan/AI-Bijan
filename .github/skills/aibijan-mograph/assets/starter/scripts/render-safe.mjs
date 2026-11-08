#!/usr/bin/env node
// Safe, resumable, versioned render.
//
//   npm run render                       final video: picture + VO + music + SFX, mastered to -14 LUFS
//   npm run render -- --silent-draft     silent preview for design review only (never a delivery)
//   npm run render -- --redo 12-18       after a SMALL, LOCAL revision: re-render only chunks touching 12–18 s
//   npm run render -- --fresh            throw away every cached chunk and start over
//   npm run render -- --budget 600       seconds this command may work (0 = no limit)
//
// The picture is rendered in short chunks that stay on disk (out/working/render). When the time budget runs out
// the script prints NOT FINISHED and exits with code 2: run the SAME command again and it continues where it
// stopped. A Claude chat sandbox stops any command after about three minutes, so there the budget defaults to
// 165 s; on a local computer it is unlimited. Exit 0 = READY (final file written), 1 = error, 2 = not finished.
import {spawnSync} from 'node:child_process';
import {copyFileSync, existsSync, mkdirSync, readFileSync, readdirSync, renameSync, rmSync, writeFileSync} from 'node:fs';
import path from 'node:path';
import {CHAT_OUTPUTS, browserOptions, concurrency, defaultBudget, getBundle, isChat} from './remotion-env.mjs';

const t0 = Date.now();
const args = process.argv.slice(2);
const flag = (name) => args.includes(name);
const option = (name) => { const i = args.indexOf(name); return i >= 0 ? args[i + 1] : undefined; };
const silent = flag('--silent-draft');
const budget = option('--budget') !== undefined ? Number(option('--budget')) : defaultBudget();
const elapsed = () => (Date.now() - t0) / 1000;
const left = () => (budget > 0 ? budget - elapsed() : Infinity);

// ---------------------------------------------------------------- release checks (unchanged since v1.3)
const project = JSON.parse(readFileSync('src/project.json', 'utf8'));
const stop = (msg) => { console.error(msg); process.exit(2); };
if (!project.voiceoverFile && !silent) stop('No voice-over configured. Run npm run prepare-vo -- file.mp3, or use --silent-draft for preview only.');
if (project.voiceoverFile && !existsSync(path.resolve('public', project.voiceoverFile))) stop(`Configured audio is missing: public/${project.voiceoverFile}`);
if (project.needsStoryboard && !silent) stop('Storyboard not finalized. Replace the three placeholder beats with real story scenes, then set needsStoryboard=false in src/project.json.');
if (project.needsSoundDesign && !silent) stop('Sound department QC not finished. Create/mix a music bed and SFX (or document a justified intentional scoreless plan), then set needsSoundDesign=false in src/project.json.');
if (!silent && !project.musicFile && !(typeof project.scorelessReason === 'string' && project.scorelessReason.trim().length > 10)) {
  stop('No music/ambience bed configured. Add musicFile, or document the deliberate scoreless choice in scorelessReason in src/project.json.');
}
if (project.musicFile && !existsSync(path.resolve('public', project.musicFile))) stop(`Configured music bed is missing: public/${project.musicFile}`);
if (!existsSync(path.resolve('node_modules/@remotion/renderer'))) stop('Remotion is not installed. Run npm ci first.');

const call = (cmd, argv) => {
  const r = spawnSync(cmd, argv, {encoding: 'utf8', maxBuffer: 64 * 1024 * 1024, windowsHide: true});
  if (r.status !== 0) { console.error(`Failed: ${cmd} ${argv.slice(0, 3).join(' ')}\n${(r.stderr || r.error?.message || '').slice(-1500)}`); process.exit(1); }
  return r;
};
const notFinished = (what) => {
  console.log(`\nNOT FINISHED (${what}, ${elapsed().toFixed(0)} s used). Run the same command again to continue; finished parts are kept.`);
  process.exit(2);
};

// ---------------------------------------------------------------- bundle + composition
const {renderMedia, selectComposition} = await import('@remotion/renderer');
const browser = browserOptions();
if (isChat() && !browser.browserExecutable) {
  console.log('WARNING: no preinstalled Chromium found in this chat sandbox; Remotion will try to download one, which the sandbox may block.');
}
const {serveUrl, key} = await getBundle();
const composition = await selectComposition({serveUrl, id: 'Mograph', ...browser, logLevel: 'error'});
const fps = composition.fps;
const frames = composition.durationInFrames;

// ---------------------------------------------------------------- chunk cache
const CHUNK = Math.max(30, Number(option('--chunk') ?? 120));
const work = path.resolve('out/working/render');
const chunkDir = path.join(work, 'chunks');
const manifestPath = path.join(work, 'manifest.json');
mkdirSync(chunkDir, {recursive: true});
const name = (a, b) => path.join(chunkDir, `c_${String(a).padStart(6, '0')}_${String(b).padStart(6, '0')}.mp4`);
const ranges = [];
for (let a = 0; a < frames; a += CHUNK) ranges.push([a, Math.min(frames - 1, a + CHUNK - 1)]);
const manifest = existsSync(manifestPath) ? JSON.parse(readFileSync(manifestPath, 'utf8')) : null;
const clearChunks = (keep = () => false) => {
  for (const f of readdirSync(chunkDir)) {
    const m = f.match(/^c_(\d+)_(\d+)\.mp4$/);
    if (!m || !keep(Number(m[1]), Number(m[2]))) rmSync(path.join(chunkDir, f), {force: true});
  }
};
const redo = option('--redo');
if (flag('--fresh') || !manifest || manifest.frames !== frames || manifest.fps !== fps || manifest.chunk !== CHUNK) {
  clearChunks();
} else if (manifest.key !== key) {
  if (redo) {
    const [a, b] = redo.split('-').map(Number);
    if (!Number.isFinite(a) || !Number.isFinite(b) || b < a) stop('--redo expects seconds like 12-18');
    const fa = Math.floor(a * fps), fb = Math.ceil(b * fps);
    clearChunks((ca, cb) => cb < fa || ca > fb);
    console.log(`--redo ${redo}: re-rendering only chunks that touch ${a}–${b} s.`);
  } else {
    clearChunks();
    console.log('Project changed since the last render: all chunks will be rendered again (use --redo A-B for small local fixes).');
  }
}
writeFileSync(manifestPath, JSON.stringify({key, fps, frames, chunk: CHUNK}, null, 2));

// ---------------------------------------------------------------- picture
const todo = ranges.filter(([a, b]) => !existsSync(name(a, b)));
let rendered = 0, msPerFrame = null;
const conc = concurrency();
console.log(`Picture: ${ranges.length - todo.length}/${ranges.length} chunks done (${frames} frames, ${fps} fps, ${conc} thread(s)${budget > 0 ? `, ${budget}s budget` : ''}).`);
for (const [a, b] of todo) {
  const need = msPerFrame ? (msPerFrame * (b - a + 1)) / 1000 + 8 : 45;
  if (rendered > 0 && need > left()) break;
  if (rendered === 0 && left() < 25) break;
  const tmp = name(a, b).replace(/\.mp4$/, '.part.mp4');
  const tc = Date.now();
  await renderMedia({
    composition, serveUrl, codec: 'h264', outputLocation: tmp, frameRange: [a, b], muted: true,
    crf: 17, x264Preset: 'veryfast', pixelFormat: 'yuv420p', imageFormat: 'jpeg', jpegQuality: 95,
    colorSpace: 'bt709', concurrency: conc, ...browser, logLevel: 'error', overwrite: true,
  });
  renameSync(tmp, name(a, b));
  rendered++;
  const ms = (Date.now() - tc) / (b - a + 1);
  msPerFrame = msPerFrame ? msPerFrame * 0.5 + ms * 0.5 : ms;
  const done = ranges.length - todo.length + rendered;
  console.log(`  frames ${a}-${b} in ${((Date.now() - tc) / 1000).toFixed(1)}s  [${done}/${ranges.length}]`);
}
const missing = ranges.filter(([a, b]) => !existsSync(name(a, b)));
if (missing.length) {
  const pct = Math.round(((ranges.length - missing.length) / ranges.length) * 100);
  const eta = msPerFrame ? Math.ceil((missing.reduce((s, [a, b]) => s + b - a + 1, 0) * msPerFrame) / 1000 / 60) : null;
  notFinished(`picture ${pct}% done${eta ? `, about ${eta} min of rendering left` : ''}`);
}

// ---------------------------------------------------------------- sound (one pass, cached per project state)
const audio = path.join(work, `audio_${key}.wav`);
if (!silent && !existsSync(audio)) {
  if (left() < 40) notFinished('picture done, sound next');
  for (const f of readdirSync(work)) if (/^audio_.*\.wav$/.test(f)) rmSync(path.join(work, f), {force: true});
  const ta = Date.now();
  await renderMedia({composition, serveUrl, codec: 'wav', outputLocation: audio + '.part.wav', ...browser, concurrency: conc, logLevel: 'error', overwrite: true});
  renameSync(audio + '.part.wav', audio);
  console.log(`Sound mixed in ${((Date.now() - ta) / 1000).toFixed(1)}s`);
}
if (left() < 25) notFinished('picture and sound done, assembling next');

// ---------------------------------------------------------------- assemble, master, deliver
const slug = (project.title || 'mograph').toLowerCase().normalize('NFKD').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 40) || 'mograph';
const finalDir = path.resolve('out/final');
const workingDir = path.resolve('out/working');
mkdirSync(finalDir, {recursive: true});
let version = 1, raw, output;
const label = silent ? 'silent-draft' : '1080x1920_60fps';
do {
  raw = path.join(workingDir, `${slug}_raw_v${version}.mp4`);
  output = silent ? path.join(workingDir, `${slug}_${label}_v${version}.mp4`) : path.join(finalDir, `${slug}_${label}_v${version}.mp4`);
  version++;
} while (existsSync(raw) || existsSync(output));

const list = path.join(work, 'concat.txt');
writeFileSync(list, ranges.map(([a, b]) => `file '${name(a, b).replace(/'/g, "'\\''")}'`).join('\n') + '\n');
if (silent) {
  call('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-n', '-f', 'concat', '-safe', '0', '-i', list, '-c', 'copy', '-movflags', '+faststart', output]);
} else {
  // Picture + lossless mix in one file, then two-pass EBU R128 loudness normalisation (-14 LUFS, -2 dBTP).
  const mux = raw.replace(/\.mp4$/, '_mux.mov');
  rmSync(mux, {force: true});
  call('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-f', 'concat', '-safe', '0', '-i', list, '-i', audio,
    '-map', '0:v:0', '-map', '1:a:0', '-c:v', 'copy', '-c:a', 'pcm_s16le', '-shortest', mux]);
  const pass1 = call('ffmpeg', ['-hide_banner', '-i', mux, '-af', 'loudnorm=I=-14:TP=-2:LRA=11:print_format=json', '-f', 'null', '-']);
  const m = JSON.parse(pass1.stderr.match(/\{\s*"input_i"[\s\S]*?\}/)?.[0] ?? 'null');
  if (!m) { console.error('Could not read loudness measurements; nothing was overwritten.'); process.exit(1); }
  const filters = `loudnorm=I=-14:TP=-2:LRA=11:measured_I=${m.input_i}:measured_TP=${m.input_tp}:measured_LRA=${m.input_lra}:measured_thresh=${m.input_thresh}:offset=${m.target_offset}:linear=true,aresample=48000`;
  call('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-n', '-i', mux, '-c:v', 'copy', '-af', filters, '-c:a', 'aac', '-b:a', '320k', '-ar', '48000', '-movflags', '+faststart', output]);
  call('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-n', '-i', mux, '-c:v', 'copy', '-c:a', 'aac', '-b:a', '320k', '-movflags', '+faststart', raw]);
  rmSync(mux, {force: true});
}
if (!existsSync(output)) { console.error('The final file was not written; cached chunks are kept.'); process.exit(1); }

const probe = spawnSync('ffprobe', ['-v', 'error', '-show_entries', 'stream=codec_type,width,height,r_frame_rate:format=duration', '-of', 'compact', output], {encoding: 'utf8'});
console.log(probe.stdout.trim());
console.log(`${silent ? 'SILENT DRAFT READY (preview only, not a delivery)' : 'READY'}: ${output}`);
if (!silent) console.log(`Unmastered copy kept for recovery: ${raw}`);
if (isChat() && existsSync(CHAT_OUTPUTS)) {
  const delivered = path.join(CHAT_OUTPUTS, path.basename(output));
  copyFileSync(output, delivered);
  console.log(`DELIVER: ${delivered}  (attach/present this file to the user in the chat)`);
}
console.log(`Total ${elapsed().toFixed(0)} s.`);

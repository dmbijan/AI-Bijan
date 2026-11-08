#!/usr/bin/env node
// QC stills at chosen seconds: npm run stills -- out/qc 0.2 1.2 4.0 8.0   (--comp=KitGallery for the kit sheet)
// Writes one PNG per time (half size by default, SCALE=1 for full) and, for two or more, a labelled contact sheet
// out/qc/sheet.jpg. Look at the sheet first: one image is far cheaper to inspect than many.
import {spawnSync} from 'node:child_process';
import {existsSync, mkdirSync} from 'node:fs';
import path from 'node:path';
import {browserOptions, getBundle} from './remotion-env.mjs';

const args = process.argv.slice(2);
const compArg = args.find((a) => a.startsWith('--comp='));
const [outDir, ...secs] = args.filter((a) => !a.startsWith('--comp='));
const compId = compArg ? compArg.slice('--comp='.length) : (process.env.COMP ?? 'Mograph');
if (!outDir || secs.length === 0) {
  console.error('Usage: npm run stills -- out/qc 0.2 1.2 4.0 8.0   (add --comp=KitGallery to preview the component kit)');
  process.exit(2);
}
mkdirSync(outDir, {recursive: true});
const {openBrowser, renderStill, selectComposition} = await import('@remotion/renderer');
const browser = browserOptions();
const {serveUrl} = await getBundle();
const puppeteerInstance = await openBrowser('chrome', {...browser, logLevel: 'error'});
const files = [];
try {
  const composition = await selectComposition({serveUrl, id: compId, puppeteerInstance, ...browser, logLevel: 'error'});
  for (const sec of secs) {
    const n = Number(sec);
    if (!Number.isFinite(n) || n < 0) throw new Error(`Bad timestamp: ${sec}`);
    const frame = Math.min(composition.durationInFrames - 1, Math.round(n * composition.fps));
    const output = path.join(outDir, `t${n.toFixed(2).padStart(7, '0')}.png`);
    await renderStill({serveUrl, composition, frame, output, puppeteerInstance, scale: Number(process.env.SCALE ?? 0.5), ...browser, logLevel: 'error'});
    files.push({output, n});
  }
  console.log(`Saved ${secs.length} QC still(s) to ${outDir}`);
} finally {
  await puppeteerInstance.close({silent: true});
}

if (files.length > 1) {
  const cols = Math.min(files.length, files.length <= 4 ? files.length : files.length <= 8 ? 4 : 6);
  const rows = Math.ceil(files.length / cols);
  const W = 270, H = 480;
  const font = path.resolve('public/fonts/DejaVuSans-Bold.ttf').replace(/\\/g, '/').replace(/:/g, '\\:');
  const inputs = files.flatMap((f) => ['-i', f.output]);
  const parts = files.map((f, i) => `[${i}:v]scale=${W}:${H},drawbox=x=0:y=0:w=92:h=34:color=black@0.7:t=fill,` +
    `drawtext=fontfile='${font}':text='${f.n.toFixed(1)}s':x=8:y=7:fontsize=20:fontcolor=white[v${i}]`);
  const layout = files.map((_, i) => `${(i % cols) * W}_${Math.floor(i / cols) * H}`).join('|');
  const filter = `${parts.join(';')};${files.map((_, i) => `[v${i}]`).join('')}xstack=inputs=${files.length}:layout=${layout}:fill=black`;
  const sheet = path.join(outDir, 'sheet.jpg');
  const r = spawnSync('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-y', ...inputs, '-filter_complex', filter, '-frames:v', '1', '-q:v', '3', sheet], {encoding: 'utf8'});
  if (r.status === 0 && existsSync(sheet)) console.log(`Contact sheet (${cols}x${rows}): ${sheet}`);
  else {
    // Some FFmpeg builds have no drawtext filter: build the same sheet without time labels (files are in time order).
    const plain = `${files.map((f, i) => `[${i}:v]scale=${W}:${H}[v${i}]`).join(';')};${files.map((_, i) => `[v${i}]`).join('')}xstack=inputs=${files.length}:layout=${layout}:fill=black`;
    const r2 = spawnSync('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-y', ...inputs, '-filter_complex', plain, '-frames:v', '1', '-q:v', '3', sheet], {encoding: 'utf8'});
    if (r2.status === 0 && existsSync(sheet)) console.log(`Contact sheet (${cols}x${rows}, no time labels; order = ${files.map((f) => f.n).join(', ')} s): ${sheet}`);
    else console.log(`Contact sheet skipped: ${(r2.stderr || r.stderr || '').slice(-300)}`);
  }
}

// aibijan-video-editor · Remotion driver (called by scripts/render-video.py; not meant for end users)
//
//   node render.mjs bundle                      -> ./build (cached, reused while src/ is unchanged)
//   node render.mjs stills  --props edit.json --times 1.2,4.5 --outdir stills
//   node render.mjs chunks  --props edit.json --outdir chunks --chunk 90 --budget 170 [--from 0 --to 299]
//
// Chunks are video-only H.264 MP4 files named c_<first>_<last>.mp4. Existing chunks are skipped, so a
// run that hits the time budget can simply be repeated. Exit code 0 = all chunks done, 2 = more to do.
import {bundle} from '@remotion/bundler';
import {renderMedia, renderStill, selectComposition} from '@remotion/renderer';
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const args = process.argv.slice(2);
const cmd = args[0];
const opt = (k, d) => {
  const i = args.indexOf('--' + k);
  return i >= 0 ? args[i + 1] : d;
};
const here = path.dirname(new URL(import.meta.url).pathname);
const browserExecutable = process.env.VY_BROWSER || undefined;
const chromeMode = process.env.VY_CHROME_MODE || 'headless-shell';
const concurrency = Number(process.env.VY_CONCURRENCY || Math.max(1, Math.min(4, (await import('node:os')).cpus().length)));

function hashDir(dir, h, contents = true) {
  if (!fs.existsSync(dir)) return;
  for (const f of fs.readdirSync(dir).sort()) {
    const p = path.join(dir, f);
    const st = fs.statSync(p);
    if (st.isDirectory()) hashDir(p, h, contents);
    else h.update(p + st.size + (contents ? fs.readFileSync(p) : st.mtimeMs));
  }
}

async function getBundle() {
  const src = path.join(here, 'src');
  const h = crypto.createHash('sha1');
  hashDir(src, h, true);
  hashDir(path.join(here, 'public'), h, false); // public assets (footage, fonts, thumbs) are copied into the bundle
  const key = h.digest('hex').slice(0, 16);
  const out = path.join(here, 'build');
  const stamp = path.join(out, '.vy-hash');
  if (fs.existsSync(stamp) && fs.readFileSync(stamp, 'utf8') === key) return out;
  const t0 = Date.now();
  const serveUrl = await bundle({
    entryPoint: path.join(src, 'index.jsx'),
    publicDir: path.join(here, 'public'),
    outDir: out,
    onProgress: () => {},
  });
  fs.writeFileSync(path.join(serveUrl, '.vy-hash'), key);
  console.error(`[remotion] bundled in ${((Date.now() - t0) / 1000).toFixed(1)}s`);
  return serveUrl;
}

async function comp(serveUrl, inputProps) {
  return selectComposition({serveUrl, id: 'Edit', inputProps, browserExecutable, chromeMode, logLevel: 'error'});
}

const readProps = () => JSON.parse(fs.readFileSync(opt('props'), 'utf8'));

if (cmd === 'bundle') {
  console.log(await getBundle());
} else if (cmd === 'stills') {
  const serveUrl = await getBundle();
  const inputProps = readProps();
  const composition = await comp(serveUrl, inputProps);
  const outdir = opt('outdir', 'stills');
  fs.mkdirSync(outdir, {recursive: true});
  const times = opt('times', '0').split(',').map(Number);
  for (const t of times) {
    const frame = Math.min(composition.durationInFrames - 1, Math.max(0, Math.round(t * composition.fps)));
    const output = path.join(outdir, `still_${String(frame).padStart(5, '0')}.png`);
    await renderStill({composition, serveUrl, output, frame, inputProps, browserExecutable, chromeMode, logLevel: 'error'});
    console.log(output);
  }
} else if (cmd === 'chunks') {
  const t0 = Date.now();
  const budget = Number(opt('budget', '170')) * 1000;
  const chunk = Number(opt('chunk', '90'));
  const outdir = opt('outdir', 'chunks');
  fs.mkdirSync(outdir, {recursive: true});
  const serveUrl = await getBundle();
  const inputProps = readProps();
  const composition = await comp(serveUrl, inputProps);
  const first = Number(opt('from', '0'));
  const last = Math.min(composition.durationInFrames - 1, Number(opt('to', String(composition.durationInFrames - 1))));
  const ranges = [];
  for (let a = first; a <= last; a += chunk) ranges.push([a, Math.min(last, a + chunk - 1)]);
  let done = 0;
  for (const [a, b] of ranges) {
    const name = path.join(outdir, `c_${String(a).padStart(5, '0')}_${String(b).padStart(5, '0')}.mp4`);
    if (fs.existsSync(name)) { done++; continue; }
    const elapsed = Date.now() - t0;
    const perFrame = done > 0 ? elapsed / Math.max(1, done * chunk) : 450; // ms/frame estimate
    if (elapsed + perFrame * (b - a + 1) > budget && done > 0) break;
    const tmp = name.replace(/\.mp4$/, '.part.mp4');
    const tc = Date.now();
    await renderMedia({
      composition, serveUrl, codec: 'h264', outputLocation: tmp, inputProps, frameRange: [a, b],
      muted: true, crf: 17, x264Preset: 'veryfast', pixelFormat: 'yuv420p', imageFormat: 'jpeg', jpegQuality: 92,
      concurrency, browserExecutable, chromeMode, logLevel: 'error',
      colorSpace: 'bt709',
    });
    fs.renameSync(tmp, name);
    done++;
    console.error(`[remotion] frames ${a}-${b} in ${((Date.now() - tc) / 1000).toFixed(1)}s`);
  }
  const all = ranges.every(([a, b]) => fs.existsSync(path.join(outdir, `c_${String(a).padStart(5, '0')}_${String(b).padStart(5, '0')}.mp4`)));
  console.log(JSON.stringify({complete: all, chunks: ranges.length}));
  process.exit(all ? 0 : 2);
} else {
  console.error('usage: node render.mjs bundle|stills|chunks ...');
  process.exit(1);
}

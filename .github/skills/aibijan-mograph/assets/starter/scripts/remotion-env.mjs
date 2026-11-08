// Shared runtime helpers for the starter scripts (not run directly).
//
// - Where are we running? A Claude chat sandbox (claude.ai with code execution: /mnt/user-data/outputs exists,
//   every shell command is limited to about three minutes, Remotion cannot download its own browser) or a local
//   computer with Claude Code. Override with AIBIJAN_MODE=chat|local.
// - Which browser should Remotion use? In a chat sandbox the preinstalled Chromium; locally Remotion's own
//   Chrome Headless Shell (downloaded once) unless AIBIJAN_BROWSER points to a binary.
// - A cached Remotion bundle, rebuilt only when src/ or public/ changed.
import {existsSync, mkdirSync, readFileSync, readdirSync, rmSync, statSync, writeFileSync} from 'node:fs';
import {spawnSync} from 'node:child_process';
import crypto from 'node:crypto';
import os from 'node:os';
import path from 'node:path';

export const CHAT_OUTPUTS = '/mnt/user-data/outputs';

export function isChat() {
  const mode = (process.env.AIBIJAN_MODE || '').toLowerCase();
  if (mode === 'chat') return true;
  if (mode === 'local') return false;
  return existsSync(CHAT_OUTPUTS);
}

const firstExisting = (paths) => paths.find((p) => p && existsSync(p)) ?? null;

function fromBrowserRoots() {
  const roots = [process.env.PLAYWRIGHT_BROWSERS_PATH, '/opt/pw-browsers', path.join(os.homedir(), '.cache', 'ms-playwright')];
  const layouts = [
    ['chromium_headless_shell-', ['chrome-headless-shell-linux64/chrome-headless-shell', 'chrome-linux/headless_shell']],
    ['chromium-', ['chrome-linux64/chrome', 'chrome-linux/chrome']],
  ];
  for (const root of roots) {
    if (!root || !existsSync(root)) continue;
    let entries = [];
    try { entries = readdirSync(root).sort().reverse(); } catch { continue; }
    for (const [prefix, subpaths] of layouts) {
      for (const dir of entries.filter((e) => e.startsWith(prefix))) {
        const hit = firstExisting(subpaths.map((s) => path.join(root, dir, s)));
        if (hit) return hit;
      }
    }
  }
  return null;
}

function fromPath() {
  if (process.platform !== 'linux') return null;
  for (const name of ['chromium', 'chromium-browser', 'google-chrome', 'google-chrome-stable']) {
    const r = spawnSync('which', [name], {encoding: 'utf8'});
    const p = r.status === 0 ? r.stdout.trim() : '';
    if (p && existsSync(p)) return p;
  }
  return null;
}

/** A Chromium binary already on this machine, or null. */
export function findBrowser() {
  const forced = process.env.AIBIJAN_BROWSER || process.env.VY_BROWSER;
  if (forced) return existsSync(forced) ? forced : null;
  return fromBrowserRoots() ?? fromPath();
}

/** Options for selectComposition / renderMedia / renderStill / openBrowser. */
export function browserOptions() {
  const forced = process.env.AIBIJAN_BROWSER || process.env.VY_BROWSER;
  if (!isChat() && !forced) return {};
  const exe = findBrowser();
  if (!exe) return {};
  const shell = /headless[-_]shell/.test(exe);
  return {browserExecutable: exe, chromeMode: shell ? 'headless-shell' : 'chrome-for-testing'};
}

export function concurrency() {
  const fromEnv = Number(process.env.AI BIJAN_CONCURRENCY);
  const cpus = Math.max(1, os.cpus().length);
  if (Number.isFinite(fromEnv) && fromEnv >= 1) return Math.min(cpus, Math.floor(fromEnv));
  return Math.max(1, Math.min(isChat() ? 2 : 4, cpus));
}

/** Seconds one command may run. Chat sandboxes stop a command after about three minutes. 0 = no limit. */
export function defaultBudget() {
  const fromEnv = Number(process.env.AI BIJAN_BUDGET);
  if (Number.isFinite(fromEnv) && fromEnv >= 0) return fromEnv;
  return isChat() ? 165 : 0;
}

function hashTree(dir, hash, withContents) {
  if (!existsSync(dir)) return;
  for (const name of readdirSync(dir).sort()) {
    if (name === '.DS_Store') continue;
    const p = path.join(dir, name);
    const st = statSync(p);
    if (st.isDirectory()) hashTree(p, hash, withContents);
    else hash.update(`${path.relative(process.cwd(), p)}|${st.size}|${withContents ? readFileSync(p).toString('base64') : st.mtimeMs}\n`);
  }
}

/** Fingerprint of everything that changes the rendered picture or sound. */
export function projectKey() {
  const h = crypto.createHash('sha1');
  hashTree(path.resolve('src'), h, true);
  hashTree(path.resolve('public'), h, false);
  return h.digest('hex').slice(0, 16);
}

/** Bundle once; reuse out/working/bundle while src/ and public/ are unchanged. */
export async function getBundle() {
  const {bundle} = await import('@remotion/bundler');
  const key = projectKey();
  const outDir = path.resolve('out/working/bundle');
  const stamp = path.join(outDir, '.aibijan-key');
  if (existsSync(stamp) && readFileSync(stamp, 'utf8') === key) return {serveUrl: outDir, key};
  rmSync(outDir, {recursive: true, force: true});
  mkdirSync(path.dirname(outDir), {recursive: true});
  const t0 = Date.now();
  const serveUrl = await bundle({
    entryPoint: path.resolve('src/index.ts'),
    publicDir: path.resolve('public'),
    outDir,
    onProgress: () => {},
  });
  writeFileSync(path.join(serveUrl, '.aibijan-key'), key);
  console.log(`Bundled in ${((Date.now() - t0) / 1000).toFixed(1)}s`);
  return {serveUrl, key};
}

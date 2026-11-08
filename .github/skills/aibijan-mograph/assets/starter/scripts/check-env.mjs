#!/usr/bin/env node
// Environment check. Works in a Claude chat sandbox (claude.ai with code execution) and on a local computer.
import {spawnSync} from 'node:child_process';
import {existsSync} from 'node:fs';
import path from 'node:path';
import {CHAT_OUTPUTS, browserOptions, findBrowser, isChat} from './remotion-env.mjs';

const chat = isChat();
const checks = [];
const run = (cmd, args) => {
  // Windows: .cmd shims (npm.cmd) can only be started through a shell on current Node versions.
  const x = spawnSync(cmd, args, {encoding: 'utf8', timeout: 8000, windowsHide: true, shell: cmd.endsWith('.cmd')});
  return x.status === 0 ? ((x.stdout || x.stderr).trim().split('\n')[0] || 'Available; version output not captured') : null;
};
console.log(`Mode: ${chat ? 'Claude chat sandbox (deliver to ' + CHAT_OUTPUTS + ', ~3 min per command)' : 'local computer (Claude Code)'}`);
const major = Number(process.versions.node.split('.')[0]);
checks.push({label: 'Node.js >=20', ok: major >= 20, detail: process.version});
const npmVersion = run(process.platform === 'win32' ? 'npm.cmd' : 'npm', ['--version']);
checks.push({label: 'npm', ok: !!npmVersion, detail: npmVersion ?? 'Install Node.js/npm'});
for (const cmd of ['ffmpeg', 'ffprobe']) {
  const version = run(cmd, ['-version']);
  checks.push({label: cmd, ok: !!version, detail: version ?? 'Install FFmpeg and add to PATH'});
}
if (chat) {
  const b = browserOptions();
  checks.push({label: 'Chromium for rendering', ok: true,
    detail: b.browserExecutable ? `${b.browserExecutable} (${b.chromeMode})` : 'NOT FOUND - Remotion will try to download Chrome Headless Shell, which a chat sandbox may block'});
} else {
  const local = findBrowser();
  const hasChrome = local || [
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    path.join(process.env.PROGRAMFILES ?? 'C:\\Program Files', 'Google', 'Chrome', 'Application', 'chrome.exe'),
  ].some(existsSync);
  checks.push({label: 'Chrome (optional)', ok: true, detail: hasChrome ? 'Found (Remotion still uses its own headless shell)' : 'Remotion downloads Chrome Headless Shell on first render'});
}
const py = process.platform === 'win32' ? (run('py', ['-3', '--version']) ?? run('python', ['--version'])) : run('python3', ['--version']);
checks.push({label: 'Python 3 (optional, word-accurate captions)', ok: true, detail: py ?? 'Not found; captions will use estimated timing'});
const fonts = ['PlusJakartaSans_500Medium.ttf', 'PlusJakartaSans_700Bold.ttf', 'PlusJakartaSans_800ExtraBold.ttf', 'DejaVuSans-Bold.ttf', 'DejaVuSansMono.ttf'];
checks.push({label: 'Bundled fonts', ok: fonts.every((f) => existsSync(`public/fonts/${f}`)), detail: 'Plus Jakarta Sans + DejaVu fallback (cross-platform)'});
const styleFonts = ['PlayfairDisplay-Var.ttf', 'CourierPrime-Regular.ttf', 'CourierPrime-Bold.ttf', 'SpaceGrotesk-Var.ttf', 'IBMPlexMono-Medium.ttf', 'IBMPlexMono-SemiBold.ttf', 'CaveatBrush-Regular.ttf', 'PatrickHand-Regular.ttf', 'Cinzel-Var.ttf', 'CormorantGaramond-Var.ttf', 'Archivo-Var.ttf', 'Inter-Var.ttf', 'VT323-Regular.ttf', 'JetBrainsMono-Var.ttf'];
checks.push({label: 'Named-look fonts', ok: styleFonts.every((f) => existsSync(`public/fonts/${f}`)), detail: 'ARSIP, BLUEPRINT, SKETSA, HIKAYAT, GEOMETRIK, TERMINAL (SIL OFL)'});
for (const c of checks) console.log(`${c.ok ? '[OK]' : '[FAIL]'} ${c.label}: ${c.detail}`);
if (checks.some((c) => !c.ok)) {
  console.error('Setup incomplete. Install missing tools, then rerun npm run check-env.');
  process.exitCode = 1;
} else {
  console.log(chat
    ? 'Environment check passed. Next: npm ci, then work in commands that each finish within ~3 minutes (render and align setup resume when repeated).'
    : 'Environment check passed. Internet is still required on first npm ci/browser download.');
}

#!/usr/bin/env node
// First command of every new production: can this machine download the render tools from npm?
// Takes about a second. In a Claude chat a blocked package registry means the account's network egress is off,
// which nothing inside the chat can work around, so we stop here with clear instructions instead of failing
// later in `npm ci` (which can hang for minutes against a refusing proxy).
//
// Exit 0 = OK, 10 = registry blocked/unreachable (relay the buyer message, then stop), 2 = npm missing.
// Node built-ins only; needs no project folder. Override the mode with AIBIJAN_MODE=chat|local.
import {spawnSync} from 'node:child_process';
import {existsSync, mkdtempSync, readFileSync, rmSync} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

const skillRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const mode = (process.env.AIBIJAN_MODE || '').toLowerCase();
const chat = mode === 'chat' || (mode !== 'local' && existsSync('/mnt/user-data/outputs'));

let version = 'latest';
try {
  version = JSON.parse(readFileSync(path.join(skillRoot, 'assets', 'starter', 'package.json'), 'utf8')).dependencies.remotion;
} catch {}

// A throwaway cache so a copy cached earlier cannot hide a blocked network. Passed by environment, not argv:
// on Windows npm runs through a shell, where a temp path with spaces (C:\Users\Budi Santoso\...) would split.
const cache = mkdtempSync(path.join(os.tmpdir(), 'aibijan-preflight-'));
const t0 = Date.now();
const r = spawnSync('npm', ['view', `remotion@${version}`, 'version', '--prefer-online', '--fetch-retries=0',
  '--fetch-timeout=15000', '--no-update-notifier'], {
  encoding: 'utf8', timeout: 30000, windowsHide: true, shell: process.platform === 'win32',
  env: {...process.env, npm_config_cache: cache},
});
rmSync(cache, {recursive: true, force: true});
const secs = ((Date.now() - t0) / 1000).toFixed(1);
const out = `${r.stdout || ''}\n${r.stderr || ''}`;

if (r.error?.code === 'ENOENT' || (process.platform === 'win32' && /not recognized/i.test(out))) {
  console.log('PREFLIGHT FAILED: npm not found. Install Node.js 20+ (includes npm), then run /aibijan-mograph again.');
  process.exit(2);
}
if (r.status === 0 && (r.stdout || '').trim().length > 0) {
  console.log(`PREFLIGHT OK: npm registry reachable (remotion@${version}, ${secs}s). Continue with the production.`);
  process.exit(0);
}

const host = (out.match(/https?:\/\/[^\s/'"]+/) || ['https://registry.npmjs.org'])[0];
const code = r.error?.code === 'ETIMEDOUT' || r.signal ? 'TIMEOUT'
  : (out.match(/\b(E403|E401|EAI_AGAIN|ENOTFOUND|ECONNREFUSED|ECONNRESET|ETIMEDOUT|ESOCKETTIMEDOUT|ENETUNREACH|EPROXY|SELF_SIGNED_CERT_IN_CHAIN|UNABLE_TO_GET_ISSUER_CERT_LOCALLY)\b/)
    || out.match(/\b(403)\b/) || ['UNKNOWN'])[0];
const lines = out.split('\n').map((l) => l.trim()).filter((l) => /error|403|ENOTFOUND|EAI_AGAIN|ECONN|ETIMEDOUT|tunnel/i.test(l));
const detail = lines.find((l) => /https?:\/\//.test(l)) || lines[0] || '(no npm output)';

console.log(`PREFLIGHT BLOCKED: the npm registry (${host}) cannot be reached [${code}] after ${secs}s.`);
console.log(`npm said: ${detail.slice(0, 300)}`);
console.log('Claude: relay the buyer message below as written (translate only if the buyer writes in another');
console.log('language), then STOP. Do not run npm ci, do not retry, do not try other registries, mirrors, CDNs or proxies,');
console.log('and do not start the storyboard or scenes: none of it can be rendered in this session.');
console.log('\n===== BUYER MESSAGE =====');
if (chat) {
  console.log(`Videonya belum bisa dibuat di chat ini. Akses internet sandbox chat-mu sedang tertutup (${host} ditolak, kode ${code}), jadi alat render Remotion tidak bisa diunduh dari npm. Ini pengaturan akun Claude, bukan kerusakan skill, dan tidak bisa diakali dari dalam chat. Belum ada pekerjaan yang terbuang.

Cara memperbaikinya (sekali saja, sekitar 1 menit):
1. Buka Settings → Capabilities. Di tampilan Settings terbaru, bagian ini bernama "Create and edit files".
2. Pastikan "Code execution and file creation" (atau "Create and edit files") aktif, lalu nyalakan "Allow network egress". Kalau tampilan Claude-mu berbahasa Indonesia, cari sakelar akses jaringan (network egress) di bagian yang sama.
3. Buka chat BARU, lampirkan lagi VO + naskah, lalu ketik /aibijan-mograph.

Akun Team/Enterprise: sakelar ini diatur owner organisasi di Organization settings → Capabilities. Minta owner mengizinkan network egress, minimal "package managers only" (npm, PyPI, dan GitHub sudah termasuk di situ).

Tanpa mengubah pengaturan: pakai Claude Code di laptop/PC (tab Code di aplikasi desktop Claude). Caranya ada di START_HERE.md bagian "Cara B".`);
} else {
  console.log(`Alat render Remotion belum bisa diunduh dari npm (${host}, kode ${code}). Cek koneksi internet. Kalau memakai VPN, proxy kantor/kampus, atau firewall/antivirus, izinkan akses ke ${host.replace(/^https?:\/\//, '')} (atau matikan sebentar VPN-nya), lalu jalankan /aibijan-mograph lagi. Kalau npm-mu sengaja diarahkan ke registry lain, cek dengan: npm config get registry`);
}
console.log('===== END BUYER MESSAGE =====');
process.exit(10);

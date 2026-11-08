#!/usr/bin/env node
// Candidate discovery for zero-prompt invocations: the workspace, its common input subfolders and, in a Claude
// chat sandbox, the chat's uploaded files (/mnt/user-data/uploads). Does not recurse through projects, system
// folders, or hidden directories.
import {existsSync, readdirSync, statSync} from 'node:fs';
import path from 'node:path';

const root = path.resolve(process.argv[2] || process.cwd());
const audioExt = new Set(['.mp3', '.wav', '.m4a', '.aac', '.flac', '.ogg']);
const scriptExt = new Set(['.txt', '.md', '.srt', '.vtt']);
const inputDirs = ['', 'input', 'inputs', 'uploads', 'attachments', 'media'];
// Files attached to a Claude chat land here; they are the most likely inputs, so they score higher.
const chatDirs = ['/mnt/user-data/uploads', '/mnt/user-data/attachments'].filter((d) => existsSync(d));

if (!existsSync(root) || !statSync(root).isDirectory()) {
  console.error('Expected a readable workspace directory.');
  process.exit(2);
}

const candidates = {audio: [], script: []};
const brandKits = [];
for (const directory of [...inputDirs, ...chatDirs]) {
  const chatUpload = chatDirs.includes(directory);
  const dir = chatUpload ? directory : path.join(root, directory);
  if (!existsSync(dir)) continue;
  let entries;
  try { entries = readdirSync(dir, {withFileTypes: true}); } catch { continue; }
  for (const entry of entries) {
    if (!entry.isFile() || entry.name.startsWith('.')) continue;
    const location = path.join(dir, entry.name);
    if (/^aibijan-brand.*\.json$/i.test(entry.name)) { brandKits.push(location); continue; }
    const ext = path.extname(entry.name).toLowerCase();
    let kind;
    if (audioExt.has(ext)) kind = 'audio';
    else if (scriptExt.has(ext)) kind = 'script';
    else continue;
    if (kind === 'script' && /^(readme|start_here|license|changelog|package)\b/i.test(entry.name)) continue;
    let stat;
    try { stat = statSync(location); } catch { continue; }
    if (stat.size === 0) continue;
    const preferredName = kind === 'audio'
      ? /(voice.?over|narration|narasi|\bvo\b|speech)/i.test(entry.name)
      : /(script|naskah|transcript|caption|subtitle)/i.test(entry.name);
    candidates[kind].push({path: location, filename: entry.name, bytes: stat.size,
      score: (preferredName ? 10 : 0) + (chatUpload ? 5 : directory ? 1 : 0)});
  }
}

for (const kind of ['audio', 'script']) {
  candidates[kind].sort((a,b)=>b.score-a.score || a.filename.localeCompare(b.filename));
}

process.stdout.write(JSON.stringify({
  workspace: root,
  audioCandidates: candidates.audio,
  scriptCandidates: candidates.script,
  brandKit: brandKits[0] ?? null,
  unambiguous: candidates.audio.length === 1 && candidates.script.length === 1,
  hint: 'Chat attachments take precedence. With multiple candidates, select only after reviewing user context.'
}, null, 2)+'\n');

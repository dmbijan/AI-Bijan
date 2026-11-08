#!/usr/bin/env node
// Strip ElevenLabs-style delivery tags from a COPY of narration text.
// A tag is any [bracketed cue] made only of letters, spaces, hyphens or apostrophes ([curious], [dramatic pause],
// [explaining], [berbisik]). Bracketed content with digits or other symbols (e.g. [2026], [1]) is preserved.
import {existsSync, mkdirSync, readFileSync, writeFileSync} from 'node:fs';
import path from 'node:path';

const [srcArg, destArg] = process.argv.slice(2);
if (!srcArg || !destArg) {
  console.error('Usage: node <skill>/scripts/clean-script.mjs <script.txt> <new-output.txt>');
  process.exit(2);
}
const src = path.resolve(srcArg);
const dest = path.resolve(destArg);
if (!existsSync(src) || !['.txt','.md','.srt','.vtt'].includes(path.extname(src).toLowerCase())) {
  console.error('Expected an existing UTF-8 text script (.txt/.md/.srt/.vtt).');
  process.exit(2);
}
if (src === dest || existsSync(dest)) {
  console.error('Refusing to overwrite a source or existing output file.');
  process.exit(2);
}
const known = [
  'curious','mischievously','whispers','whisper','whispering','shouts','shout','shouting',
  'laughs','laughing','chuckles','chuckling','clears throat','sighs',
  'sad','angry','happy','happily','excited','surprised','thoughtful','serious',
  'slow','slower','fast','faster','annoyed','appalled','crying','muttering',
  'short pause','long pause','pause','exhales sharply','inhales deeply',
  'nervous','calm','dramatic','confident','softly','quietly','sarcastic'
];
const knownTag = new RegExp('\\[\\s*(?:'+known.map(x=>x.replace(/ /g,'\\s+')).join('|')+')\\s*\\]', 'gi');
// Any other word-only cue up to 40 characters is a delivery tag too (ElevenLabs v3 accepts free-form cues).
const wordTag = /\[\s*[\p{L}][\p{L}\s'’-]{0,39}\]/gu;
let clean = readFileSync(src, 'utf8').replace(/\r\n?/g, '\n');
const originalLength = clean.length;
const removed = [...new Set([...(clean.match(knownTag) ?? []), ...(clean.match(wordTag) ?? [])].map(t=>t.toLowerCase()))];
clean = clean.replace(knownTag, '').replace(wordTag, '').replace(/<break\s+time\s*=\s*["'][0-9.]+s["']\s*\/>/gi, '');
clean = clean.split('\n').map(line=>line.replace(/[ \t]{2,}/g,' ').trim()).join('\n').replace(/\n{3,}/g,'\n\n').trim()+'\n';
if (!clean.trim()) { console.error('Cleaned script is empty; check source.'); process.exit(2); }
mkdirSync(path.dirname(dest), {recursive:true});
writeFileSync(dest, clean, {flag:'wx'});
console.log(JSON.stringify({source: src, cleaned: dest, originalCharacters: originalLength,
  cleanCharacters: clean.length, removedTags: removed, reminder:'Use actual VO audio for timing; tags are not subtitle text.'}));

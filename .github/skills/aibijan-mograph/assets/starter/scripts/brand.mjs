#!/usr/bin/env node
// Apply the buyer's shared AI BIJAN brand kit (aibijan-brand.json, see BRAND_KIT.md):
//   npm run brand -- /path/to/aibijan-brand.json
// Writes src/brand.json (theme colour slots read by src/theme.ts, plus name/handle/cta/logo), sets the brand name in
// src/project.json and copies a supplied logo into public/brand/. Every colour is contrast-checked; a colour that
// would hurt legibility is not applied and a NOTE says why (tell the buyer in one short sentence).
import {copyFileSync, existsSync, mkdirSync, readFileSync, writeFileSync} from 'node:fs';
import path from 'node:path';

const file = process.argv[2];
if (!file || !existsSync(file)) {
  console.error('Usage: npm run brand -- /path/to/aibijan-brand.json');
  process.exit(2);
}
const kit = JSON.parse(readFileSync(file, 'utf8'));
const notes = [];
const isHex = (c) => typeof c === 'string' && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(c.trim());
const rgb = (c) => {
  let h = c.trim().slice(1);
  if (h.length === 3) h = [...h].map((x) => x + x).join('');
  return [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16) / 255);
};
const lum = (c) => {
  const [r, g, b] = rgb(c).map((v) => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4));
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};
const contrast = (a, b) => {
  const [x, y] = [lum(a), lum(b)].sort((p, q) => q - p);
  return (x + 0.05) / (y + 0.05);
};
const darken = (c, k) => '#' + rgb(c).map((v) => Math.round(v * (1 - k) * 255).toString(16).padStart(2, '0')).join('');

// starter defaults (src/theme.ts)
const slots = {navy: '#0D1233', ink: '#070B22', yellow: '#FFC83D', paper: '#FFFDF6', blue: '#398CE6'};
const CREAM = '#F8F1DD';
const colors = kit.colors ?? {};
for (const [k, v] of Object.entries(colors)) if (v != null && !isHex(v)) notes.push(`${k} '${v}' is not a hex colour like #FFB800; ignored`);

const P = isHex(colors.primary) ? colors.primary : null;
if (P) {
  if (contrast(P, CREAM) >= 4.5) { slots.navy = P; slots.ink = darken(P, 0.45); }
  else if (contrast(P, '#FFFFFF') >= 3) { slots.blue = P; notes.push(`primary ${P} is not dark enough for backgrounds/text; used for UI accents (buttons, bars) instead`); }
  else notes.push(`primary ${P} is too light for text or backgrounds; not applied`);
}
const A = isHex(colors.accent) ? colors.accent : null;
if (A) {
  if (contrast(A, slots.navy) >= 3) slots.yellow = A;
  else notes.push(`accent ${A} has too little contrast with the dark colour ${slots.navy}; highlights keep ${slots.yellow}`);
}
const B = isHex(colors.background) ? colors.background : null;
if (B) {
  if (contrast(B, slots.navy) >= 7) slots.paper = B;
  else notes.push(`background ${B} would make dark text hard to read; light scenes keep ${slots.paper}`);
}
if (kit.font && !/plus jakarta sans/i.test(kit.font)) {
  notes.push(`font '${kit.font}' is not bundled (Plus Jakarta Sans is). Use another font only if the buyer supplies a licensed file`);
}
let logo = null;
if (kit.logo) {
  const src = path.resolve(path.dirname(file), kit.logo);
  if (existsSync(src)) {
    mkdirSync('public/brand', {recursive: true});
    logo = `brand/logo${path.extname(src).toLowerCase()}`;
    copyFileSync(src, path.join('public', logo));
  } else notes.push(`logo '${kit.logo}' not found next to the brand kit; ask the buyer to attach it`);
}

const out = {
  name: kit.brand ?? null, handle: kit.handle ?? null, cta: kit.cta ?? null, logo,
  colors: slots, notes, source: kit,
};
writeFileSync('src/brand.json', JSON.stringify(out, null, 2) + '\n');
if (kit.brand) {
  const pj = JSON.parse(readFileSync('src/project.json', 'utf8'));
  pj.brand = String(kit.brand);
  writeFileSync('src/project.json', JSON.stringify(pj, null, 2) + '\n');
}
console.log(`BRAND KIT APPLIED: ${kit.brand ?? '(no name)'} -> src/brand.json` + (logo ? `, logo public/${logo}` : ''));
console.log(`theme: navy ${slots.navy} · accent ${slots.yellow} · paper ${slots.paper} · ui ${slots.blue}`);
for (const n of notes) console.log(`NOTE: ${n}`);

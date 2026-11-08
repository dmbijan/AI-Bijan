#!/usr/bin/env node
// Safely copy the portable starter into a new/empty project directory.
import {chmodSync, cpSync, existsSync, mkdirSync, readdirSync, statSync} from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

const skillRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const template = path.join(skillRoot, 'assets', 'starter');
const destination = process.argv[2] ? path.resolve(process.argv[2]) : null;
if (!destination) {
  console.error('Usage: node <skill-path>/scripts/bootstrap.mjs <new-or-empty-project-path>');
  process.exit(2);
}
if (destination === path.parse(destination).root || destination === path.resolve(skillRoot)) {
  console.error('Refusing to use an unsafe destination. Choose a dedicated project directory.');
  process.exit(2);
}
if (!existsSync(template)) {
  console.error(`Starter assets missing: ${template}`);
  process.exit(2);
}
if (existsSync(destination) && readdirSync(destination).length > 0) {
  console.error(`Refusing to overwrite a non-empty folder: ${destination}`);
  process.exit(2);
}
mkdirSync(destination, {recursive: true});
for (const entry of readdirSync(template)) {
  cpSync(path.join(template,entry), path.join(destination,entry), {recursive: true, errorOnExist: true, force: false});
}
// Installed skills can be read-only (e.g. a Claude chat's skill mount); the project copy must be editable.
const makeWritable = (p) => {
  const st = statSync(p);
  chmodSync(p, st.mode | 0o200);
  if (st.isDirectory()) for (const e of readdirSync(p)) makeWritable(path.join(p, e));
};
makeWritable(destination);
console.log(`Created MOGRAPH starter: ${destination}`);
console.log('Next: node scripts/check-env.mjs && npm ci && npm run typecheck   (in a Claude chat: keep each command under ~3 min)');

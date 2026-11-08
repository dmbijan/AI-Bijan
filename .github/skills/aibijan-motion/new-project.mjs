// Set up a AI BIJAN Motion project: copies this skill's starter/ (dotfiles included) and, with --kit, that style's
// kit (src/, scripts/, public/) into a folder. Uses Node only, so no shell copy permissions are needed.
//   node <SKILL_DIR>/new-project.mjs [folder] [--kit=<style>]      (folder default: the current one)
// A folder that already is a AI BIJAN project only gets the kit added; a folder with other files is refused.
// A kit with its own project/ (CINÉSTORY) is a project of its own: it is copied instead of starter/, and it never
// shares a folder with another style's kit.
// The skill upload in the Claude app takes at most 200 files, so the release ships the source of starter/ and of
// each kit packed into PACKED_SOURCE*.txt in that folder (docs, examples and fonts stay plain files). tree() reads
// a folder as packed files plus plain files, so a packed and an unpacked copy of the skill set up the same project.

import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const PACK = /^PACKED_SOURCE(-\d+)?\.txt$/;
/** Files of a folder: relative path ("/"-separated) -> { abs } for a plain file or { data } for a packed one. */
function tree(dir) {
  const files = new Map();
  const walk = (d, rel) => {
    for (const e of fs.readdirSync(d, { withFileTypes: true })) {
      const r = rel ? `${rel}/${e.name}` : e.name;
      if (e.isDirectory()) walk(path.join(d, e.name), r);
      else if (rel || !PACK.test(e.name)) files.set(r, { abs: path.join(d, e.name) });
    }
  };
  if (!fs.existsSync(dir)) return files;
  walk(dir, "");
  // pack format: a header, then per file "\n==== <byte length> <path>\n" followed by exactly that many bytes
  for (const name of fs.readdirSync(dir).filter((f) => PACK.test(f)).sort()) {
    const buf = fs.readFileSync(path.join(dir, name));
    let at = buf.indexOf("\n==== ");
    while (at >= 0 && at < buf.length) {
      const eol = buf.indexOf("\n", at + 1);
      const m = /^==== (\d+) (.+)$/.exec(buf.toString("utf8", at + 1, eol));
      if (!m) throw new Error(`${path.join(dir, name)} is damaged — download the skill again`);
      const start = eol + 1, end = start + Number(m[1]);
      files.set(m[2], { data: buf.subarray(start, end) });
      at = end;
    }
  }
  return files;
}
/** Copies the files under `sub` ("" = all) of a tree into `to`; skip(sourcePath) leaves a file out. */
function copyTree(files, dir, sub, to, skip = () => false) {
  for (const [rel, src] of files) {
    if (sub && !rel.startsWith(`${sub}/`)) continue;
    if (skip(path.join(dir, rel))) continue;
    const out = path.join(to, sub ? rel.slice(sub.length + 1) : rel);
    fs.mkdirSync(path.dirname(out), { recursive: true });
    if (src.abs) fs.copyFileSync(src.abs, out);
    else fs.writeFileSync(out, src.data);
  }
}

const here = path.dirname(fileURLToPath(import.meta.url));
const args = process.argv.slice(2);
const kit = (args.find((a) => a.startsWith("--kit=")) ?? "").slice(6);
const dest = path.resolve(args.find((a) => !a.startsWith("--")) ?? ".");
const kits = fs.readdirSync(path.join(here, "kits"));
// PRECISION and INNERWORKS have no kit folder: their projects are the starter (core) alone, built from their references
const CORE_ONLY = ["precision", "innerworks"];
const coreOnly = CORE_ONLY.includes(kit);
if (kit && !coreOnly && !kits.includes(kit)) { console.error(`no kit "${kit}" (available: ${[...kits, ...CORE_ONLY].sort().join(", ")})`); process.exit(1); }

const kitDir = kit && !coreOnly ? path.join(here, "kits", kit) : "";
const kitFiles = kitDir ? tree(kitDir) : new Map();
const standalone = [...kitFiles.keys()].some((f) => f.startsWith("project/"));
// a CINÉSTORY project has the core too, but its own director and scripts
const isOwn = fs.existsSync(path.join(dest, "src/director/compile.ts")) && fs.existsSync(path.join(dest, "src/gen"));
const isProject = fs.existsSync(path.join(dest, "src/core/film/Stage3D.tsx"));
if (isOwn && !standalone) {
  console.error(`${dest} is a CINÉSTORY project — ${kit || "another style"} needs a folder of its own: use a new folder name as the first argument`);
  process.exit(1);
}
if (standalone) {
  if (isOwn) console.log(`existing ${kit} project: ${dest}`);
  else if (isProject) {
    console.error(`${dest} is a project of another style — ${kit} needs a folder of its own: use a new folder name as the first argument`);
    process.exit(1);
  } else {
    const other = fs.existsSync(dest) ? fs.readdirSync(dest).filter((f) => !f.startsWith(".") && !/\.(mp3|wav|m4a|aac|txt|md)$/i.test(f)) : [];
    if (other.length) {
      console.error(`${dest} already has other files (${other.slice(0, 4).join(", ")}${other.length > 4 ? ", …" : ""}) — use a new folder name as the first argument`);
      process.exit(1);
    }
    fs.mkdirSync(dest, { recursive: true });
    copyTree(kitFiles, kitDir, "project", dest);
    console.log(`project ready: ${dest}`);
  }
  console.log(`kit: ${kit} (read ${path.join(here, "kits", kit, "KIT.md")})`);
  console.log("next (inside the project): npm ci, then npm run check-env");
  process.exit(0);
}
if (!isProject) {
  // hidden files (.claude, .git) and the buyer's own VO / script may already be there
  const other = fs.existsSync(dest) ? fs.readdirSync(dest).filter((f) => !f.startsWith(".") && !/\.(mp3|wav|m4a|aac|txt)$/i.test(f)) : [];
  if (other.length) {
    console.error(`${dest} already has other files (${other.slice(0, 4).join(", ")}${other.length > 4 ? ", …" : ""}) — use a new folder name as the first argument`);
    process.exit(1);
  }
  fs.mkdirSync(dest, { recursive: true });
  copyTree(tree(path.join(here, "starter")), path.join(here, "starter"), "", dest);
  console.log(`project ready: ${dest}`);
} else console.log(`existing AI BIJAN project: ${dest}`);

if (coreOnly) console.log(`style: ${kit} — built on the core in src/core, no kit to add (read ${path.join(here, "references", kit + ".md")})`);
else if (kit) {
  for (const part of ["src", "scripts", "public"]) {
    // never overwrite the buyer's own stories or audio in an existing project
    copyTree(kitFiles, kitDir, part, path.join(dest, part), (f) => isProject && /[\\/](stories[\\/](data|registry)|pv-stories[\\/])/.test(f));
  }
  // a kit whose films are registered by a component (root.json) is added to src/Root.tsx once
  const reg = kitFiles.get("root.json"), rootFile = path.join(dest, "src", "Root.tsx");
  if (reg && fs.existsSync(rootFile)) {
    const r = JSON.parse(reg.abs ? fs.readFileSync(reg.abs, "utf8") : reg.data.toString("utf8")), root = fs.readFileSync(rootFile, "utf8");
    const lastImport = [...root.matchAll(/^import .*;$/gm)].pop(), close = root.lastIndexOf("</>");
    if (root.includes(r.marker)) {
      // already registered
    } else if (!lastImport || close < 0) console.log(`  add ${r.element} to src/Root.tsx by hand (${r.import})`);
    else {
      const at = lastImport.index + lastImport[0].length;
      const withEl = root.slice(0, close) + `  ${r.element}\n  ` + root.slice(close);
      fs.writeFileSync(rootFile, withEl.slice(0, at) + `\n${r.import}` + withEl.slice(at));
      console.log(`  registered ${r.element} in src/Root.tsx`);
    }
  }
  console.log(`kit added: ${kit} (read ${path.join(here, "kits", kit, "KIT.md")})`);
}
console.log("next (inside the project): npm ci, then npm run check-env");

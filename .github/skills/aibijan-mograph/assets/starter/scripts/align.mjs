#!/usr/bin/env node
// Word-level timestamps for the narration.
//   npm run align -- --setup                                 prepare Python + download the ~350 MB model (repeatable)
//   npm run align -- <voice-over> <script.clean.txt>         write src/words.json for <WordCaptions> and at('phrase')
// Uses a private .venv (numpy + onnxruntime) when possible, otherwise the system Python. The model cache
// (~/.cache/aibijan-models) is shared with AI BIJAN Video Editor. In a Claude chat sandbox run --setup first and
// repeat it until it prints MODEL READY: an interrupted download or unpack simply continues on the next run.
// Never fatal for the video: without Python, pip or network it prints WORD TIMING NOT AVAILABLE (exit 3) and
// the project keeps estimated caption timing.
import {spawnSync} from 'node:child_process';
import {existsSync, readFileSync} from 'node:fs';
import path from 'node:path';

const argv = process.argv.slice(2);
const setupOnly = argv.includes('--setup');
const [vo, script] = argv.filter((a) => !a.startsWith('--'));
if (!setupOnly && (!vo || !script)) {
  console.error('Usage: npm run align -- --setup   |   npm run align -- <voice-over.mp3|wav|m4a> <script.clean.txt>');
  process.exit(2);
}
if (!setupOnly) for (const f of [vo, script]) if (!existsSync(f)) { console.error(`File not found: ${f}`); process.exit(2); }

const win = process.platform === 'win32';
const venvPy = path.resolve('.venv', win ? 'Scripts/python.exe' : 'bin/python');
const run = (cmd, args) => spawnSync(cmd, args, {stdio: 'inherit', windowsHide: true});
const quiet = (cmd, args) => spawnSync(cmd, args, {stdio: 'ignore', windowsHide: true}).status === 0;
const hasLibs = (cmd, pre = []) => quiet(cmd, [...pre, '-c', 'import numpy, onnxruntime']);
const unavailable = (why) => {
  console.log(`\nWORD TIMING NOT AVAILABLE: ${why}`);
  console.log('Captions keep estimated timing. Tell the buyer once, in plain words, and continue the video.');
  process.exit(3);
};

const candidates = win ? [['py', ['-3']], ['python', []], ['python3', []]] : [['python3', []], ['python', []]];
const system = candidates.find(([c, a]) => quiet(c, [...a, '-c', 'import sys; sys.exit(0 if sys.version_info >= (3, 8) else 1)']));
if (!system && !existsSync(venvPy)) unavailable('Python 3.8+ is not installed on this computer.');

// 1) an existing private environment, 2) a new private environment, 3) the system Python (chat sandboxes).
let py = null, pre = [];
if (existsSync(venvPy) && hasLibs(venvPy)) py = venvPy;
if (!py && system && hasLibs(system[0], system[1])) { [py, pre] = system; }
if (!py) {
  if (!existsSync(venvPy) && system) {
    console.log('Preparing word timing (one time): private Python environment in .venv ...');
    run(system[0], [...system[1], '-m', 'venv', '.venv']);
  }
  if (existsSync(venvPy)) {
    console.log('Installing numpy + onnxruntime into .venv (one time) ...');
    run(venvPy, ['-m', 'pip', 'install', '-q', '--disable-pip-version-check', 'numpy', 'onnxruntime']);
    if (hasLibs(venvPy)) py = venvPy;
  }
}
if (!py && system) {
  console.log('Installing numpy + onnxruntime for the system Python (one time) ...');
  run(system[0], [...system[1], '-m', 'pip', 'install', '-q', '--disable-pip-version-check', '--user', '--break-system-packages', 'numpy', 'onnxruntime']);
  if (hasLibs(system[0], system[1])) { [py, pre] = system; }
}
if (!py) unavailable('could not install numpy/onnxruntime (no internet or the package index is blocked).');

const aligner = path.resolve('scripts/align/align_words.py');
if (setupOnly) {
  const r = run(py, [...pre, aligner, '--setup']);
  if (r.status !== 0) unavailable('the model could not be downloaded (the network may block github.com release assets).');
  console.log('\nMODEL READY: word timing can run now (npm run align -- <voice-over> <script.clean.txt>).');
  process.exit(0);
}

const out = path.resolve('src/words.json');
const r = run(py, [...pre, aligner, path.resolve(vo), path.resolve(script), '--out', out]);
if (r.status !== 0) unavailable('the alignment step failed (see the message above).');
const data = JSON.parse(readFileSync(out, 'utf8'));
if (data.method !== 'ctc-forced-alignment') {
  console.log('\nWORD TIMING ESTIMATED: the alignment model could not be downloaded, so word times are proportional.');
  console.log('Captions work, but check sync by ear and tell the buyer once.');
} else {
  const low = data.low_confidence?.length ?? 0;
  console.log(`\nWORD TIMING READY: ${data.words.length} words aligned to the voice-over -> src/words.json` +
    (low ? ` (${low} low-confidence word(s): check names/numbers by ear)` : ''));
}

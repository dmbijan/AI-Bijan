#!/usr/bin/env node
import {spawnSync} from 'node:child_process';
import {copyFileSync, existsSync, mkdirSync, readFileSync, writeFileSync} from 'node:fs';
import path from 'node:path';

const [file, ...flags] = process.argv.slice(2);
if (!file) {
  console.error('Usage: npm run prepare-vo -- /absolute/path/voiceover.mp3 [--replace]');
  process.exit(2);
}
const source = path.resolve(file);
if (!existsSync(source)) { console.error(`Audio file not found: ${source}`); process.exit(2); }
const ext = path.extname(source).toLowerCase();
if (!['.mp3','.wav','.m4a','.aac'].includes(ext)) { console.error('Supported audio: MP3, WAV, M4A, AAC'); process.exit(2); }
const target = path.resolve('public/audio/voiceover'+ext);
const configPath = path.resolve('src/project.json');
const config = JSON.parse(readFileSync(configPath, 'utf8'));
const previous = config.voiceoverFile ? path.resolve('public',config.voiceoverFile) : null;
if ((existsSync(target) || (previous && existsSync(previous))) && !flags.includes('--replace')) {
  console.error('Voice-over already exists; use --replace only if the existing audio may be replaced.');
  process.exit(2);
}
const probe = spawnSync('ffprobe', ['-v','error','-show_entries','format=duration','-of','default=nokey=1:noprint_wrappers=1',source], {encoding:'utf8'});
const seconds = Number(probe.stdout?.trim());
if (probe.status !== 0 || !Number.isFinite(seconds) || seconds <= 0) {
  console.error('ffprobe failed to read voice-over duration.'); process.exit(2);
}
mkdirSync(path.dirname(target), {recursive:true});
if (source !== target) copyFileSync(source,target);
const oldDuration = Math.max(1,Number(config.durationSeconds)||12);
config.durationSeconds = Math.ceil((seconds + 0.8)*60)/60;
config.voiceoverFile = `audio/voiceover${ext}`;
config.needsStoryboard = true;
// Only a temporary proportional preview. Agent MUST re-time all beats to actual narration.
config.beats = config.beats.map(b=>({...b,start:Math.round(b.start/oldDuration*config.durationSeconds*100)/100,end:Math.round(b.end/oldDuration*config.durationSeconds*100)/100}));
config.beats[config.beats.length-1].end=config.durationSeconds;
writeFileSync(configPath,JSON.stringify(config,null,2)+'\n');
console.log(`VO ready: ${seconds.toFixed(2)}s; canvas ${config.durationSeconds.toFixed(2)}s`);
console.log('IMPORTANT: These beats are proportionally stretched placeholders. Re-time them to the real script before rendering.');

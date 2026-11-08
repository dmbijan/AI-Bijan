#!/usr/bin/env node
// Offline, dependency-free ORIGINAL synthetic underscore. No third-party samples.
// This is a musical fallback, not a substitute for a well-selected licensed score.
import {closeSync, existsSync, mkdirSync, openSync, readFileSync, writeSync} from 'node:fs';
import path from 'node:path';

const [planName, outName] = process.argv.slice(2);
if (!planName || !outName) {
  console.error('Usage: npm run score -- src/audio-plan.json public/audio/music/original-score.wav');
  process.exit(2);
}
const plan = JSON.parse(readFileSync(path.resolve(planName), 'utf8'));
const dest = path.resolve(outName);
if (existsSync(dest)) {
  console.error(`Refusing to overwrite existing score: ${dest}`);
  process.exit(2);
}
const seconds = Number(plan.durationSeconds);
const sections = plan.sections;
const moods = {
  playful:   {chords:[[60,64,67,71],[65,69,72,76],[57,60,64,67],[62,65,69,72]], bpm:106, pad:0.65, pluck:0.8, bass:0.7},
  curious:   {chords:[[60,64,67,74],[62,65,69,72],[57,60,64,69],[65,69,72,76]], bpm:94, pad:0.8, pluck:0.55, bass:0.6},
  suspense:  {chords:[[57,60,64,67],[53,57,60,64],[55,58,62,65],[52,55,59,62]], bpm:76, pad:1.0, pluck:0.12, bass:0.9},
  tense:     {chords:[[57,60,64,67],[53,57,60,64],[55,58,62,65],[52,55,59,62]], bpm:72, pad:1.0, pluck:0.08, bass:1.0},
  dramatic:  {chords:[[57,60,64,69],[53,57,60,65],[55,58,62,67],[52,55,59,64]], bpm:78, pad:1.15, pluck:0.12, bass:1.1},
  reflective:{chords:[[53,57,60,64],[60,64,67,72],[57,60,64,69],[55,60,64,67]], bpm:72, pad:1.0, pluck:0.22, bass:0.5},
  hopeful:   {chords:[[60,64,67,72],[65,69,72,76],[57,60,64,69],[67,71,74,79]], bpm:88, pad:0.9, pluck:0.5, bass:0.6},
  warm:      {chords:[[53,57,60,64],[60,64,67,72],[57,60,64,69],[65,69,72,76]], bpm:80, pad:1.0, pluck:0.32, bass:0.55},
  energetic: {chords:[[60,64,67,71],[57,60,64,69],[65,69,72,76],[67,71,74,79]], bpm:118,pad:0.55,pluck:0.9, bass:0.9},
  neutral:   {chords:[[60,64,67,71],[57,60,64,69],[65,69,72,76],[62,65,69,72]], bpm:85, pad:0.8, pluck:0.35, bass:0.55},
  silence: null,
};

if (!(seconds > 0 && seconds <= 900) || !Array.isArray(sections) || sections.length < 1) {
  throw new Error('audio-plan.json needs durationSeconds (0..900) and a nonempty sections array.');
}
let boundary = 0;
for (const section of sections) {
  if (!Object.hasOwn(moods, section.mood)) throw new Error(`Unknown mood: ${section.mood}`);
  if (![section.start, section.end, section.energy].every(Number.isFinite)) throw new Error('Section must have numeric start, end, energy.');
  if (Math.abs(section.start - boundary) > 0.025 || section.end <= section.start || section.energy < 0 || section.energy > 1) {
    throw new Error(`Sections must be contiguous, ordered, positive-duration, and energy within 0..1; offending section starts at ${section.start}`);
  }
  boundary = section.end;
}
if (Math.abs(boundary - seconds) > 0.025) throw new Error('Sections must reach exact durationSeconds.');

const SR = 48000;
const frames = Math.round(seconds * SR);
const gain = (x) => Math.max(0, Math.min(1, x));
const hz = (midi) => 440 * 2 ** ((midi - 69) / 12);
const tau = Math.PI * 2;
const wave = (t, note, stereo) => {
  const p = tau * hz(note) * t + (stereo ? 0.17 : 0);
  return 0.76*Math.sin(p) + 0.17*Math.sin(p*2.003) + 0.07*Math.sin(p*3.002);
};
const renderSection = (t, section, side) => {
  if (section.mood === 'silence') return 0;
  const profile = moods[section.mood];
  const local = Math.max(0, t-section.start);
  const barSeconds = 4 * 60 / profile.bpm;
  const chordIndex = Math.floor(local / barSeconds);
  const chordNow = profile.chords[chordIndex % profile.chords.length];
  const chordPrev = profile.chords[(chordIndex+profile.chords.length-1)%profile.chords.length];
  const slide = gain((local % barSeconds) / 0.65);
  const padOf = (chord) => chord.reduce((acc,n,j) => acc + wave(local, n, side) * (j===0?0.45:0.32),0);
  const pad = (padOf(chordPrev)*(1-slide) + padOf(chordNow)*slide) * profile.pad * 0.17;
  const bass = Math.sin(tau * hz(chordNow[0]-24)*local + (side?0.1:0)) * profile.bass * 0.11;
  const stepSeconds = 30 / profile.bpm;
  const step = Math.floor(local / stepSeconds);
  const note = chordNow[(step+1)%chordNow.length] + 12;
  const hitAge = local - step*stepSeconds;
  const pluck = Math.exp(-hitAge*7) * (Math.sin(tau*hz(note)*hitAge)+0.27*Math.sin(tau*hz(note)*hitAge*2.01)) * 0.09 * profile.pluck;
  const vibrato = 1 + 0.04*Math.sin(local*0.29+side);
  return (pad*vibrato + bass + pluck) * (0.35+section.energy*0.65);
};
const fadeWeight = (t, section, i) => {
  const left = i===0?1:gain((t-section.start+0.65)/1.3);
  const right = i===sections.length-1?1:gain((section.end+0.65-t)/1.3);
  return left*right;
};

mkdirSync(path.dirname(dest), {recursive:true});
const fd = openSync(dest, 'wx');
const header = Buffer.alloc(44);
header.write('RIFF',0);
header.writeUInt32LE(36+frames*4,4);
header.write('WAVE',8);header.write('fmt ',12);header.writeUInt32LE(16,16);
header.writeUInt16LE(1,20);header.writeUInt16LE(2,22);header.writeUInt32LE(SR,24);
header.writeUInt32LE(SR*4,28);header.writeUInt16LE(4,32);header.writeUInt16LE(16,34);
header.write('data',36);header.writeUInt32LE(frames*4,40);
writeSync(fd, header);

let randomState = (Number.isInteger(plan.seed)?plan.seed:1337)>>>0;
let filteredNoise = 0;
const CHUNK=4096;
try {
  for (let offset=0; offset<frames; offset+=CHUNK) {
    const count = Math.min(CHUNK,frames-offset);
    const out = Buffer.allocUnsafe(count*4);
    for (let i=0;i<count;i++) {
      const t=(offset+i)/SR;
      const silent=sections.some(s=>s.mood==='silence' && t>=s.start && t<s.end);
      let left=0,right=0;
      if (!silent) {
        for (let k=0;k<sections.length;k++) {
          const s=sections[k];
          if (s.mood==='silence' || t<s.start-0.65 || t>s.end+0.65) continue;
          const w=fadeWeight(t,s,k);
          left += renderSection(t,s,0)*w;
          right += renderSection(t,s,1)*w;
        }
        randomState=(1664525*randomState+1013904223)>>>0;
        filteredNoise=0.965*filteredNoise + 0.035*(randomState/0x100000000*2-1);
        left += filteredNoise*0.025;
        right -= filteredNoise*0.02;
      }
      const headFade = gain(t/0.22);
      const tailFade = gain((seconds-t)/0.45);
      const level=headFade*tailFade;
      out.writeInt16LE(Math.round(Math.tanh(left*level)*32760),i*4);
      out.writeInt16LE(Math.round(Math.tanh(right*level)*32760),i*4+2);
    }
    writeSync(fd,out);
  }
} finally {
  closeSync(fd);
}
console.log(`Original synthetic score: ${dest} (${seconds.toFixed(2)}s, 48kHz, stereo, ${sections.length} phases)`);
console.log('This is a restrained fallback bed. Audition/mix it; do not claim professional music quality without listening.');

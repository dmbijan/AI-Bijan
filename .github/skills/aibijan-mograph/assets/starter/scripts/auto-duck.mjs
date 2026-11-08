#!/usr/bin/env node
// Safely build a speech-aware volume curve for Remotion's background music.
// Read VO only; never change user audio. Writes project metadata with a backup.
import {spawnSync} from 'node:child_process';
import {existsSync, readFileSync, copyFileSync, writeFileSync} from 'node:fs';
import path from 'node:path';

const [voiceName, configName='src/project.json', ...flags]=process.argv.slice(2);
if(!voiceName){console.error('Usage: npm run auto-duck -- <voice-over.wav-or-mp3> [src/project.json] [--replace]');process.exit(2);}
const voice=path.resolve(voiceName), config=path.resolve(configName);
if(!existsSync(voice)||!existsSync(config)){console.error('Missing VO or src/project.json.');process.exit(2);}
const project=JSON.parse(readFileSync(config,'utf8'));
if(Array.isArray(project.musicAutomation)&&project.musicAutomation.length>0&&!flags.includes('--replace')){
  console.error('Music automation already exists. Refusing overwrite; pass --replace after reviewing existing curve.');
  process.exit(2);
}
const SR=8000;
const proc=spawnSync('ffmpeg',['-hide_banner','-loglevel','error','-i',voice,'-vn','-f','s16le','-ac','1','-ar',String(SR),'pipe:1'],{encoding:null,maxBuffer:100*1024*1024,windowsHide:true});
if(proc.status!==0)throw new Error(`Cannot decode voice-over: ${proc.stderr?.toString().slice(-700)}`);
const pcm=proc.stdout;
if(pcm.length<SR*2/4)throw new Error('VO too short for automatic ducking.');
const win=SR/10, windowCount=Math.ceil((pcm.length/2)/win);
const rms=[];
for(let i=0;i<windowCount;i++){
  let sum=0,n=0;
  for(let j=i*win;j<Math.min((i+1)*win,pcm.length/2);j++){
    const x=pcm.readInt16LE(j*2)/32768;
    sum+=x*x;n++;
  }
  rms.push(Math.sqrt(sum/Math.max(n,1)));
}
const maxRms=Math.max(...rms);
const threshold=Math.max(0.006,maxRms*0.08);
const base=Number.isFinite(project.musicVolume)?project.musicVolume:0.24;
let current=base*0.56;
const points=[];
for(let i=0;i<windowCount;i++){
  // 100ms lookahead reduces music pumping at the onset of speech.
  const speaking=[i,i+1,i+2].some(j=>j<rms.length&&rms[j]>threshold);
  const target=base*(speaking?0.48:1.08);
  const alpha=target<current?0.7:0.09; // fast duck, slow release
  current += alpha*(target-current);
  points.push({frame:i*6,volume:Number(Math.max(0,Math.min(1,current)).toFixed(4))});
}
project.musicAutomation=points;
const backup=`${config}.before-duck-${Date.now()}.bak`;
copyFileSync(config,backup);
writeFileSync(config,JSON.stringify(project,null,2)+'\n');
console.log(`Speech-aware music automation: ${points.length} keyframes; backup: ${backup}`);
console.log(`Audio RMS threshold: ${(20*Math.log10(threshold)).toFixed(1)} dBFS; source VO was not modified.`);

#!/usr/bin/env node
// Measure final mastered audio objectively. Does not replace listening or rights review.
import {spawnSync} from 'node:child_process';
import {existsSync, readFileSync, writeFileSync, mkdirSync} from 'node:fs';
import path from 'node:path';

const args=process.argv.slice(2);
const file=args[0];
const option=(name)=>{const i=args.indexOf(name);return i<0?null:args[i+1]};
if (!file || args.some((v,i)=>['--plan','--output'].includes(v)&&!args[i+1])) {
  console.error('Usage: npm run audio-qc -- <final-video.mp4> [--plan src/audio-plan.json] [--output out/qc/report.json]');
  process.exit(2);
}
const target=path.resolve(file);
if (!existsSync(target)){console.error(`Missing video: ${target}`);process.exit(2);}
const run=(cmd,argv)=>{
  const result=spawnSync(cmd,argv,{encoding:'utf8',maxBuffer:12*1024*1024,windowsHide:true});
  if(result.status!==0)throw new Error(`${cmd} failed: ${(result.stderr||result.error?.message||'unknown error').slice(-1000)}`);
  return result;
};
const probe=JSON.parse(run('ffprobe',['-v','error','-show_entries','format=duration:stream=codec_type,codec_name,sample_rate,channels','-of','json',target]).stdout);
if(!probe.streams.some(s=>s.codec_type==='audio'))throw new Error('No audio stream present.');
const duration=Number(probe.format.duration);
const loud=run('ffmpeg',['-hide_banner','-nostats','-loglevel','info','-i',target,'-vn','-af','loudnorm=I=-14:TP=-1.5:LRA=11:print_format=json','-f','null','-']).stderr;
const statsMatch=loud.match(/\{\s*"input_i"[\s\S]*?\}/);
if(!statsMatch)throw new Error('FFmpeg did not report EBU R128 measurements.');
const stats=JSON.parse(statsMatch[0]);
const silencesRaw=run('ffmpeg',['-hide_banner','-nostats','-loglevel','info','-i',target,'-vn','-af','silencedetect=noise=-35dB:d=0.25','-f','null','-']).stderr;
const silences=[];
let start=null;
for(const line of silencesRaw.split('\n')){
  const a=line.match(/silence_start:\s*([\d.]+)/);
  const b=line.match(/silence_end:\s*([\d.]+)\s*\|\s*silence_duration:\s*([\d.]+)/);
  if(a)start=Number(a[1]);
  if(b && start!==null){silences.push({start:Number(start.toFixed(3)),end:Number(Number(b[1]).toFixed(3)),duration:Number(Number(b[2]).toFixed(3))});start=null;}
}

let planned=[];
const planName=option('--plan');
if(planName){
  const plan=JSON.parse(readFileSync(path.resolve(planName),'utf8'));
  planned=Array.isArray(plan.intentionalSilences)?plan.intentionalSilences:[];
  for(const entry of planned){if(!Number.isFinite(entry.start)||!Number.isFinite(entry.end)||entry.end<=entry.start)throw new Error('Invalid intentionalSilences entry');}
}
const withReasons=silences.map(s=>{
  const marker=planned.find(p=>s.start>=p.start-0.2&&s.end<=p.end+0.2);
  return {...s,intentional:!!marker,...(marker?{reason:marker.reason||'planned silence'}:{})};
});
const unintended=withReasons.filter(s=>!s.intentional);
const i=Number(stats.input_i),tp=Number(stats.input_tp),lra=Number(stats.input_lra);
const total=arr=>Number(arr.reduce((n,s)=>n+s.duration,0).toFixed(3));
const warnings=[];
if(Math.abs(i-(-14))>1)warnings.push(`Integrated loudness ${i} LUFS is outside -14 ±1 LU.`);
if(tp>-1.45)warnings.push(`True peak ${tp} dBTP exceeds the -1.5 dBTP target (with 0.05 dB measurement tolerance).`);
if(total(unintended)>Math.max(0.5,duration*0.015))warnings.push(`Unplanned quiet gaps total ${total(unintended)}s; review music/ambience coverage and author intentional silences in plan.`);
if(withReasons.some(s=>s.end>=duration-0.08 && s.duration>0.6 && !s.intentional))warnings.push('Extended quiet tail; check video end and music fade.');
const report={file:target,durationSeconds:Number(duration.toFixed(3)),audioCodec:probe.streams.find(s=>s.codec_type==='audio').codec_name,
  measuredIntegratedLUFS:i,measuredTruePeakDBTP:tp,measuredLRA:lra,
  silenceThreshold:'below -35 dBFS for >=0.25 seconds',silenceEvents:withReasons.length,
  totalQuietSeconds:total(withReasons),unplannedQuietSeconds:total(unintended),silences:withReasons,
  status:warnings.length?'REVIEW':'METRICS_OK',warnings,
  limitation:'Signal-only QC. Does not verify taste, SFX timing by ear, VO intelligibility, or media licensing.'};
const outName=option('--output');
if(outName){
  const dest=path.resolve(outName);
  mkdirSync(path.dirname(dest),{recursive:true});
  writeFileSync(dest,JSON.stringify(report,null,2)+'\n',{flag:'wx'});
  console.error(`Wrote audio report: ${dest}`);
}
console.log(JSON.stringify(report,null,2));

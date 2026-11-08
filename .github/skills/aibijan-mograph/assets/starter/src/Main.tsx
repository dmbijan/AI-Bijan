import React from 'react';
import {AbsoluteFill, Audio, interpolate, staticFile, useCurrentFrame} from 'remotion';
import project from './project.json';
import {IsoCity} from './components/IsoCity';
import {WordCaptions} from './components/WordCaptions';
import {SfxTrack} from './components/SfxTrack';
import {FontFaces} from './Fonts';
import {hasWordTiming} from './lib/words';
import {C, FONT, MONO, ramp} from './theme';

// The starter is an editable demonstration, NOT the final storyboard.
// Replace beats/graphics according to the supplied narration and facts.
type Beat = {type: string; start: number; end: number; title: string; caption: string};
const beats = project.beats as Beat[];
const sound = project as {
  musicFile?: string | null;
  musicVolume?: number;
  musicAutomation?: Array<{frame: number; volume: number}>;
};

const musicGain = (frame: number): number => {
  const base = sound.musicVolume ?? 0.24;
  const points = sound.musicAutomation ?? [];
  if (points.length === 0) return base;
  if (frame <= points[0].frame) return points[0].volume;
  if (frame >= points[points.length-1].frame) return points[points.length-1].volume;
  let low = 1, high = points.length-1;
  while (low < high) {
    const mid = Math.floor((low+high)/2);
    if (points[mid].frame >= frame) high = mid;
    else low = mid+1;
  }
  const left = points[low-1], right = points[low];
  const span = Math.max(1,right.frame-left.frame);
  const p = Math.max(0,Math.min(1,(frame-left.frame)/span));
  return left.volume + (right.volume-left.volume)*p;
};


const Hook: React.FC<{t: number; beat: Beat}> = ({t, beat}) => {
  const p = ramp(t, beat.start, beat.start + 0.6);
  return (
    <AbsoluteFill>
      <IsoCity t={t} haze={ramp(t, beat.start + 2, beat.end)} sun={1} camScale={1 + 0.045 * t} />
      <div style={{position:'absolute',top:290,left:74,right:74,textAlign:'center',fontFamily:FONT,fontSize:92,color:C.navy,transform:`scale(${0.9 + 0.1 * p})`,opacity:p,textShadow:'0 5px 32px #ffffffcc'}}>{beat.title}</div>
    </AbsoluteFill>
  );
};

const Mechanism: React.FC<{t: number; beat: Beat}> = ({t,beat}) => (
  <AbsoluteFill style={{background:`linear-gradient(160deg,${C.navy},${C.ink})`,color:C.cream}}>
    <div style={{position:'absolute',top:330,left:80,fontFamily:FONT,fontSize:74,maxWidth:920,lineHeight:1.12}}>{beat.title}</div>
    <div style={{position:'absolute',top:760,left:65,right:65,display:'flex',alignItems:'center',justifyContent:'space-between'}}>
      {['CAUSE','CHANGE','IMPACT'].map((label,i) => {
        const p = ramp(t, beat.start + i * 0.7, beat.start + i * 0.7 + 0.4);
        return <React.Fragment key={label}>
          {i>0 && <div style={{width:75,height:8,background:C.yellow,transform:`scaleX(${p})`}} />}
          <div style={{width:250,height:280,borderRadius:36,background:i===1?C.yellow:C.cream,transform:`translateY(${(1-p)*80}px)`,opacity:p,display:'flex',alignItems:'center',justifyContent:'center',fontFamily:MONO,fontSize:27,color:C.navy,textAlign:'center',boxShadow:'0 24px 50px #0005'}}>{label}</div>
        </React.Fragment>;
      })}
    </div>
    <div style={{position:'absolute',top:1300,left:80,right:80,height:9,borderRadius:6,background:'#ffffff33'}}><div style={{height:'100%',background:C.yellow,width:`${Math.max(0,Math.min(100,100*(t-beat.start)/(beat.end-beat.start)))}%`}}/></div>
  </AbsoluteFill>
);

const Payoff: React.FC<{t: number; beat: Beat}> = ({t,beat}) => {
  const p = ramp(t,beat.start,beat.start+0.65);
  return <AbsoluteFill style={{background:C.paper,alignItems:'center',justifyContent:'center'}}>
    <div style={{position:'absolute',top:350,left:90,right:90,color:C.navy,fontFamily:FONT,textAlign:'center',fontSize:92}}>{beat.title}</div>
    <div style={{width:750,height:850,borderRadius:70,background:C.navy,boxShadow:'0 35px 65px #0d123344',padding:75,transform:`translateY(${(1-p)*200}px) rotate(${(1-p)*-5}deg)`,opacity:p}}>
      <div style={{borderRadius:20,width:270,height:30,background:C.yellow,marginBottom:80}} />
      <div style={{fontFamily:MONO,fontSize:32,color:C.cream,marginBottom:35}}>THE KEY INSIGHT</div>
      <div style={{fontFamily:FONT,fontSize:70,color:C.yellow,lineHeight:1.25}}>Show the result visually.</div>
      <div style={{height:14,background:'#ffffff33',marginTop:110,borderRadius:7}}><div style={{width:`${interpolate(p,[0,1],[0,100])}%`,height:'100%',background:C.coral,borderRadius:7}} /></div>
    </div>
  </AbsoluteFill>;
};

export const Main: React.FC = () => {
  const frame = useCurrentFrame();
  const t = frame / 60;
  const beat = beats.find(b => t>=b.start && t<b.end) ?? beats[beats.length-1];
  const View = beat.type==='mechanism' ? Mechanism : beat.type==='payoff' ? Payoff : Hook;
  const display = beat.caption;
  const captionOpacity = ramp(t,beat.start+0.08,beat.start+0.25) * (1-ramp(t,beat.end-0.15,beat.end));
  return <AbsoluteFill style={{overflow:'hidden',background:C.paper}}>
    <FontFaces />
    <View t={t} beat={beat} />
    <div style={{position:'absolute',top:100,left:78,fontFamily:FONT,fontSize:31,color:beat.type==='mechanism'?C.cream:C.navy,letterSpacing:2}}>{project.brand}</div>
    {hasWordTiming
      ? <WordCaptions t={t} y={1470} />
      : <div style={{position:'absolute',top:1490,left:75,right:75,display:'flex',justifyContent:'center',opacity:captionOpacity}}>
          <div style={{fontFamily:FONT,fontSize:52,lineHeight:1.2,textAlign:'center',padding:'22px 32px',borderRadius:25,background:'#070b22e6',color:C.cream}}>{display}</div>
        </div>}
    <SfxTrack />
    {sound.musicFile && <Audio src={staticFile(sound.musicFile)} volume={musicGain} />}
    {project.voiceoverFile && <Audio src={staticFile(project.voiceoverFile)} />}
  </AbsoluteFill>;
};

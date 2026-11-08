import React from 'react';
import {Audio, Sequence, staticFile, useVideoConfig} from 'remotion';
import plan from '../audio-plan.json';

type Cue = {time: number; asset: string; gain?: number; purpose?: string};

/**
 * Plays every `sfxCues` entry of src/audio-plan.json at its time (seconds), so the auditable plan IS the
 * implementation. Keep it mounted in Main.tsx; add, move or remove effects by editing the plan.
 */
export const SfxTrack: React.FC = () => {
  const {fps} = useVideoConfig();
  const cues = (plan as {sfxCues?: Cue[]}).sfxCues ?? [];
  return (
    <>
      {cues.map((c, i) => (
        <Sequence key={i} from={Math.max(0, Math.round(c.time * fps))} durationInFrames={Math.round(4 * fps)} name={`sfx: ${c.purpose ?? c.asset}`}>
          <Audio src={staticFile(c.asset)} volume={c.gain ?? 0.25} />
        </Sequence>
      ))}
    </>
  );
};

// Skeleton for a per-video custom component (creative mode). Copy it to the work folder, rename the file and the
// export, then list the file in creative.json "custom_components". See references/custom-components.md.
//
// creative.json:
//   {"type": "SceneMyIdea", "scene": true, "start": "w:15-0.05", "end": "w:27-0.08",
//    "text": "spoken words", "items": [{"text": "spoken", "at": "w:18"}], "cues": [{"at": "w:18", "kind": "pop"}]}
import React from 'react';
import {SceneFrame, theme, dispFont, tw, qOut, qIn, eOut, prog, lerp, clamp, Icon, Chip, MaskWord} from '../../lib/kit.js';

export const SceneMyIdea = ({E, t, o}) => {
  const W = E.width, T = theme(E), D = dispFont(E);   // T: solid (flat / light styles) or glass surfaces, style tokens
  const items = o.items || [];
  return (
    // SceneFrame = eased entrance/exit + slow camera push. It also draws o.tag as a chip: pass tag: undefined if your
    // component uses "tag" for something else.
    <SceneFrame E={E} t={t} o={o}>
      <div style={{position: 'absolute', left: 0, width: W, top: 420, display: 'flex', justifyContent: 'center',
        ...D, fontSize: 150, letterSpacing: '-0.04em', color: T.fg}}>
        <MaskWord p={tw(t, o.at ?? o.start + 0.2, 0.7)}>{o.text}</MaskWord>
      </div>
      {items.map((it, k) => {
        if (t < it.at) return null;                    // every element appears on the word it illustrates
        const p = tw(t, it.at, 0.6);                    // easeOutQuint entrance, ~0.6 s
        return (
          <div key={k} style={{position: 'absolute', left: W / 2, top: 700 + k * 110, transform: `translate(-50%, ${(1 - p) * 30}px)`,
            opacity: clamp(p * 1.6), filter: p < 0.98 ? `blur(${((1 - p) * 8).toFixed(1)}px)` : 'none'}}>
            <Chip E={E} text={it.text} size={44} tone={k % 2 ? 'accent' : 'ink'} dot={false} />
          </div>
        );
      })}
    </SceneFrame>
  );
};

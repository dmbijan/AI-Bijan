# Creative mode: per-video custom components

The library covers the common moments. A video's **signature** often needs something the library does not have: the
script's own metaphor drawn on screen (people with search bars above their heads, a price tag on "privacy", a stack of
receipts for "orderan numpuk"). In creative mode you write that element as a small React component for this video only.

**Try `SceneBuild` first** (the scene kit in `creative-plan.md`: figures, 48 objects, tags, bubbles, bars, counters,
charts, arrows, steps, crowds - composed from JSON, already tested in every style). Write a component only for an image
the kit cannot compose (a special mechanism, an unusual shape). Use it for **at most 1–2 moments per video**.

## When
- The concept (SKILL.md "Direct it first") has a concrete image and no library element shows it.
- Not for decoration, not to restyle an existing element (use its params: HeroTitle `layout`/`fx`/`stack`, scene `tag`...).
- Not when the user asked for something simple ("potong jeda + subtitle aja").

## How
1. Copy `assets/custom-examples/_Template.jsx` into the work folder (`$W/components/SceneSomething.jsx`). Worked
   examples: `SceneCrowd.jsx` (crowd / pair of silhouettes with search bars above the heads, a bar that types a spoken
   quote) and `ScenePriceTag.jsx` (hanging price tag that drops and swings, stamp slams on).
2. Write the component. List the file in creative.json and use its export name as an overlay type:
   ```json
   "custom_components": ["/home/claude/work/<style>/components/SceneCrowd.jsx"],
   "overlays": [{"type": "SceneCrowd", "scene": true, "start": "w:15-0.05", "end": "w:32-0.08", "...": "..."}]
   ```
3. `plan-edit.py` validates it (exports, forbidden patterns, unknown types) and grounding-checks its text.
4. Render **stills first**. If the bundle fails, the error names the file and line. Fix it, and if it fails twice,
   drop it and use library elements. Tell the user in one line.

## The contract
```jsx
import React from 'react';
import {SceneFrame, theme, dispFont, tw, qOut, prog, lerp, clamp, Icon, Chip, renderItem, kitColors, THINGS} from '../../lib/kit.js';
export const SceneSomething = ({E, t, o}) => { ... };
```
- `t` = seconds on the edited timeline. `o` = the overlay from edit.json with every time ref already resolved to seconds
  (`start`, `end`, `at` and any key ending in `At`: `typeAt`, `heartAt`, `barsAt`...; `items[].at` too).
- `E` = the edit: `E.width/height`, `E.style.colors`, `E.style.fonts`, `E.style.look`, `E.footage`, `E.faces`.
- Import only from `../../lib/kit.js` (it re-exports `remotion` basics, easing, `theme`, `dispFont`, `SceneFrame`,
  `Chip`, `MaskWord`, `AccentText`, `LiveClip`, `Icon`, `faceOnCanvas`, `rand`, `shutterBlur`, `vel`, `dirBlur`, and the
  scene kit: `renderItem(E, kitColors(E), item, t, key)` draws any SceneBuild item, `THINGS` the 48 objects; and the
  depth pack: `measureText(text, family, weight, size)` (real font metrics, for fitting type), `typeLook(E, look, color,
  size)` (fill/outline/chrome/gradient/extrude/glow CSS), `toneColor(E, tone)` (`auto` = ink on light walls, paper on
  dark), and `DepthText` / `HeadRing` / `Doodles` / `FeedWall` to embed inside a custom scene).
- A custom component placed on `"layer": "behind"` sits behind the speaker too (the planner computes the matte for it).
- **Pure function of t.** No `Math.random` (frames render in parallel: use `rand(seed)`), no `Date`, no state/effects,
  no network, no external images. The planner refuses these.
- Colours only from `theme(E)` / `E.style.colors` so the element wears the current style and look (flat: solid ink /
  accent / paper, no gradients or glows; aurora: glass + gradients). Fonts from `dispFont(E)` and `E.style.fonts.ui`.

## Motion rules (same as the library)
- Entrances easeOutQuint 0.5–0.8 s with blur-to-sharp, exits faster (easeInQuint 0.25–0.35 s), nothing bouncy except
  tiny badges (`backOut`). Fast moves get real motion blur: `shutterBlur(Math.abs(vel(f, t)))`.
- Every element appears **on the word it illustrates** (pass the word refs as `...At` / `items[].at`).
- A full-screen scene uses `"scene": true` (layer under the captions, on the backdrop) inside a layout `none` window
  with an orb/bars cover, and wraps its content in `SceneFrame` (slow push, eased entrance/exit). `SceneFrame` draws
  `o.tag` as a chip: pass `{...o, tag: undefined}` if your component uses `tag` for something else.
- Over the face: keep text off the eyes/mouth and give the planner a `"box": [x0, y0, x1, y1]` so it can lint it, or
  follow the face with `faceOnCanvas(E, t)`.

## Text, captions, sound
- Put text in the standard fields so the planner grounding-checks it: `text`, `title`, `kicker`, `tag`, `sub`,
  `items[].text`, `prompt`. Every word must be spoken (or user copy).
- A typed spoken quote: `prompt` + `typeAt` (+ `typing`) → captions hide automatically while it types.
- `"hide_captions": true` hides captions while the element shows the words being spoken (titles).
- Content between y ≈ 330 and 1110 on 9:16; nothing important in the caption band (≈1117–1244) unless captions are
  hidden there.
- Sound: by default one soft swoosh on entrance. Better: `"cues": [{"at": "w:46", "kind": "pop"}, {"at": "w:52",
  "kind": "typing", "until": "w:52+1.0"}]` (kinds: whoosh swoosh pop click tick typing impact riser ding shimmer snap).

## Honesty
Same rules as every overlay: generic, stylised drawings; no real logos, product UI, brand colours of other companies,
fake screenshots or invented facts.

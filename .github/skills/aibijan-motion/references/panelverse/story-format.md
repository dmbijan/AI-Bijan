# Story format — `src/pv-stories/<id>/story.ts`

One folder per video: `src/pv-stories/<id>/` with `words.json` (from `pv-align.mjs` / `tts.mjs`), `story.ts` (written by you), `elements.tsx` (the story's own objects) and the generated `words.ts`. The folder name is the `id` (a-z, 0-9, "-"). `story.ts` is **data + helpers only** — no JSX, imports only from `./words.ts` and `../../kits/panelverse/director/kit.ts` (pv-make loads it in Node to lint it and build the sound). `src/kits/panelverse/examples/…` is never copied; the example in the skill (`kits/panelverse/examples/oke/`) is a *format* reference only.

## Skeleton
```ts
import { WORDS } from "./words.ts";
import { story, clock, S, FULL, LAYOUTS, LOOKS, look, EXPR, SET_PRESETS, frameCam, camKey, dollyKeys, orbitKeys, jitterKeys,
  panel, enter, move, fx, sfx, type ShotDef, type PoseKey, type ExprKey, type V3 } from "../../kits/panelverse/director/kit.ts";

const W = clock(WORDS, { offset: 0.2 });          // VO starts 0.2 s into the film
const hook = W.at("pernah"), turn = W.at("padahal"), END = W.voEnd + S(0.9);

const kamar: ShotDef = { start: 0, set: { ...SET_PRESETS.bedroomNight, wall: "teal" }, bg: "#1B927C",
  camera: [camKey(0, frameCam([0, 1.8, 0], [0.3, 0, 1], 1.4, 1, 30)), camKey(turn, frameCam([0, 1.7, 0], [0.4, 0.1, 1], 2.2, 1, 34), "inOut")],
  actors: [{ cast: "aku", pose: [{ f: 0, v: { pos: [0, 0, 0] } }] as PoseKey[], expr: [{ f: 0, v: EXPR.worried }] as ExprKey[] }] };

export default story({
  id: "contoh", clock: W, durationInFrames: END, vo: { file: "audio/contoh.mp3", offset: 0.2 },
  cast: { aku: look({ id: "aku", hair: "bob", topSw: "orange", skin: "skinC", lashes: true }) },
  shots: { kamar /* , … */ },
  elements: ["mejaBelajar"],                        // names registered in elements.tsx
  beats: [{ at: hook, words: "pernah ga", idea: "…", device: "close-up + punch zoom" } /* , … */],
  panels: [panel("kamar", 0, END, enter.cut(0, FULL, 0) /* , move.collapse(…) */)],
  fx: [fx.impact(W.at("tamat"), W.end("tamat") + 30, "TAMAT!", { y: 860, size: 200, font: "title" })],
  sfx: [sfx("ring", hook - 20, 0.3)],              // extra sounds; transitions + FX get theirs automatically
});
```

## Time
All frames are **60 fps film frames**. `W.at(word, nth?)`, `W.end(word, nth?)`, `W.s(seconds)`, `W.voEnd`, `S(seconds)` (a duration). Word matching ignores case/punctuation and accepts a prefix ("ngomong" matches "ngomong,"). A word that is not in the transcript throws a clear error with suggestions. `durationInFrames` = `W.voEnd + S(0.6…2.5)` (the closing hold).

## Shots (`ShotDef`) — one live 3D stage each
| field | |
|---|---|
| `start` | film frame where shot-local frame 0 is. **Every key inside a shot is shot-local** (`L = g => g - start`). Shots that appear in several panels share one clock. |
| `freezeAt` | shot-local frame where time stops (freeze frames, shattered panels) |
| `set` | a `SetSpec` ([elements.md](elements.md) › sets): `{ kind: "interior" | "exterior" | "stage", … , items: [{ type, pos, rot, scale, sw, sw2, text, on, n, keys }] }` |
| `setKeys` | animate set fields (`wall`, `wallDots`, `gap`, `tilt`, `drop`, `spin`…) |
| `bg` / `light` / `ink` | canvas colour behind everything, key light direction (world, *towards* the light), ink width multiplier (1–1.25) |
| `camera` | `CamKey[]`: `{ f, v: { pos, target, fov, roll }, ease }` — use `frameCam(target, dir, worldHeight, frac, fov, roll)`; `frac` = fraction of the **canvas** height the subject fills (≈ 0.21–0.25 when the shot is seen through a band panel of a 3-up page, 1 when full screen). `dollyKeys` (vertigo), `orbitKeys` (arc) |
| `shakes` | `[{ at, dur, amp, seed }]` 3D camera shakes (0.02 rumble … 0.09 big hit) |
| `actors` | `[{ cast, pose: PoseKey[], expr: ExprKey[], anchor?, torso?, head?, heldR?, heldL?, holdRotR?, holdRotL?, shadow? }]` |
| `props` | `[{ kind, keys, from?, to? }]` — kinds: `item` (any library item / story element), `phone`, `bubble`, `shatterBubble`, `sign`, `thoughtCloud`, `thoughtTrail`, `steam`, `wisps`, `heart`, `bowl`, `spoon`, `hearts`, `buzz`, `impactStar` |

**Poses** (`PoseKey.v`, cumulative — each key only lists what changes): `pos [x,0,z]`, `rotY` (radians, 0 faces the camera at +z), `squash` (1; 0.86 shrink, 1.08 stretch), `pelvis [x,dy,z]` (sitting: dy ≈ −0.36…−0.42 with `footL/footR` forward), `spine [bend, twist, lean]`, `head [nod, turn, tilt]`, `shrug`, `handL/handR [x,y,z]` (torso space: shoulders at y 0.7, x ±0.33; mouth ≈ [0, 1.0, 0.45]; above head ≈ y 1.2), `elbowL/R` (pole), `fistL/R` 0–1, `twistL/R`, `footL/R` (root space, ankle y 0.08), `kneeL/R`. **Expressions** (`ExprKey.v`): start from `EXPR.<name>` and override fields (`browIn`, `browUp`, `eyeOpen`, `eyeSize`, `pupil`, `look [x,y]`, `eyeMode` open/happy/shut/heart/star, `mouth` smile/frown/flat/wobble/open/grin/shout/chew/o/smirk, `mouthOpen`, `mouthW`, `puff`, `blush`, `sweat`, `tears`, `tremble`, `blink`). **Discrete values switch half-way through an eased move** — add a hold key just before a change (`{ f: word - 3, v: { ...same } }`) so a face or mouth switches on the word, not early.

**Tested pose recipes** (torso space; `±` = left / right hand):
| pose | keys |
|---|---|
| sitting | `pelvis: [0, -0.36, 0], footL: [0.19, 0.08, 0.52], footR: [-0.19, 0.08, 0.5]` (seat ≈ 0.5–0.6 high) |
| phone in both hands, screen to the face | `handL: [0.17, 0.42, 0.36], handR: [-0.17, 0.42, 0.36], elbowL: [0.7, -0.6, -0.2], elbowR: [-0.7, -0.6, -0.2], twistL: -0.4, twistR: 0.4` + torso prop phone `pos: [0, 0.5, 0.42], rot: [1.1, Math.PI, 0]` (screen to the camera instead: `rot: [0.15, 0, 0]`, `pos: [0, 0.6, 0.45]`) |
| hands on hips | `handL: [0.37, 0.12, 0.02], handR: [-0.37, 0.12, 0.02], elbowL: [1, 0.1, -0.35], elbowR: [-1, 0.1, -0.35], fistL: 1, fistR: 1, twistL: 0.6, twistR: -0.6` |
| raised fist / oath hand | `handR: [-0.64, 1.3, 0.25], elbowR: [-1, -0.4, 0.2], fistR: 1` (oath: `fistR: 0`). Keep the hand **well outside the head** (x ≈ ±0.6) or it reads as a salute / covers the face |
| hands over the mouth | `handL: [0.07, 1.0, 0.46], handR: [-0.07, 1.0, 0.46], elbowL: [0.9, -0.5, 0.1], elbowR: [-0.9, -0.5, 0.1], fistL: 0, fistR: 0, twistL: -1.2, twistR: 1.2` |
| reaching forward | `spine: [0.24, 0, 0], handL: [0.14, 0.88, 0.62], handR: [-0.14, 0.82, 0.6], elbowL: [0.6, -0.6, 0], elbowR: [-0.6, -0.6, 0], fistL: 0, fistR: 0` |
| stomp | foot up `footR: [-0.15, 0.42, 0.2], squash: 0.93` → down on the word `footR: [-0.17, 0.08, 0.14], squash: 1.08` → `squash: 1` (+ `impactStar` prop and a shot shake) |
| cower / shrink | `squash: 0.86, shrug: 1, spine: [0.14, 0, 0], head: [0.18, 0, 0]` |

**Cameras that keep faces:** high angle → `dir` y ≤ 0.6 (a steeper camera shows only the top of the head); low "hero" angle → `dir` y ≈ −0.3 … −0.4; close-up → `worldH` ≈ 1.2–1.5; medium → 2.5–3.5; wide → 4–8.

Props in hands: `heldR: { kind: "item", keys: [{ f: 0, v: { type: "mug" } }] }` + `holdRotR`; a two-handed object → `torso: [{ kind: "phone", keys: [{ f: 0, v: { pos: [0, 0.5, 0.42], rot: [1.1, Math.PI, 0], screen: {…} } }] }]` with both hands targeting its sides. Phone screens: `{ kind: "chat", title, messages: [{ from: "me" | "them", text? | scribble?, reveal? }], typing?, read? }`, `{ kind: "notif", app, title, text, time? }`, `{ kind: "balance", label, amount, note?, alert? }`, `{ kind: "lock", time, date?, notif? }`.

## Panels — the page
`panel(shotId, from, to, ...segments)` builds a panel's whole life. Quads come from `LAYOUTS.<name>()[i]` (splash, page, twoUp, threeUp, fourUp, grid4, twoCol, bigTop, bigBottom, strip3, diagonal, inset) or your own `[[x,y]×4]` (clockwise, 1080×1920 px). Later panels draw on top.
- **enter:** `enter.cut(at, quad)`, `enter.slam(at, quad, "zoom"|"left"|"right"|"top"|"bottom")`, `enter.slide(at, quad, from)`, `enter.iris(at, quad)`, `enter.slash(at, "right"|"left", hold)` (diagonal split, then full screen)
- **move:** `move.expand(at, dur, to?)` (→ full screen), `move.collapse(at, quad)` (→ printed panel), `move.fall(at, dur, dir)`, `move.drift(at, dur, [dx,dy], rot)`, `move.zoom(at)`, `move.irisOut(at, dur)`, `move.shatter(at, dur, impact, cuts)`
- The content of a panel is a full 1080×1920 canvas centred on the panel; a band panel shows the middle strip → frame the subject with `frameCam(…, frac ≈ 0.22)` while it is a band, then move the camera to `frac 1` while the panel expands.
- `page.cam` (2.5D move over the page: x, y, scale, rot, tiltX, tiltY) and `page.shakes` (`{ at, dur, amp }` in px, ≤ 14).

## FX — `fx.*`
`impact(at, to, text, {x,y,size,rot,fill,font:"impact"|"title"})` · `caption(at, to, text, {x,y,size,fill})` · `burst(at, dur, {x,y,r,fill,inner})` · `speed(from, to, {cx,cy,inner,opacity,color})` · `flood(at, dur, to, origins)` · `flash(at, dur, color)` · `sfxText(at, to, text, {x,y,size,rot,fill})` (onomatopoeia) · `impactFrame(at, dur)` · `stamp(at, to, text, {x,y,size,rot,color})` · `shock(from, to, x, y, r)` · `halftoneWipe(at, dur, to, {color, reverse})`. Screen coordinates are 1080×1920 px; keep lettering inside x 64–920, y 230–1450.

## Sound — `sfx(name, at, volume, {duck, until})`
See [sound.md](sound.md). Transitions and FX get their sounds automatically (`autoSfx`, default on).

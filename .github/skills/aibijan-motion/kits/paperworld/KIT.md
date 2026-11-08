# PAPERWORLD kit — handcrafted paper dioramas

`node SKILL_DIR/new-project.mjs [folder] --kit=paperworld` copies the starter plus this kit's `src/` into the project's `src/`. It adds `src/kits/paperworld/`
and needs only `remotion`, `@remotion/three`, `@react-three/fiber` and `three` (already in the starter).

| Folder | Content |
|---|---|
| `tokens.ts` | `PALETTE` (ivory, coral, mint, gold, cobalt, brown — each with Deep/Light), `PAPER` constants (card 0.06, board 0.16, puppet height 6.3) |
| `material/paper.ts` | Procedural paper stock: grain albedo + fibre relief, cached per colour. `paperFace`, `paperEdge` (paler cut core), `paperPair`. Options: `relief`, `roughness`, `emissive`, `emissiveIntensity`, `doubleSide` |
| `geometry/shapes.ts` | `S.rect / rectC / arch / circle / ellipse / ring / poly / path / gable / outline / frame / hill / cloud` cut shapes with holes (`Hole` = rect, arch, circle); `extrude` (cached), `flat` |
| `components/Paper.tsx` | `Cut` (extruded card with face + edge materials), `Print` (zero-thickness ink), `Hinge` (fold about a crease), `PopUp` (hinged pop-up), `ShearPop` (pop-up-book box mechanism: folds flat backwards at t = 0, stands square at t = 1) |
| `components/MiniatureEnvironment.tsx` | `Ground`, `GroundPrint`, `PaperHill`, `PaperTree` (round / cone / cloud), `PaperBush`, `PaperCloud` (hung on threads), `PaperLamp`, `Thread` |
| `components/PaperBuilding.tsx` | `PaperBuilding` from a `BuildingSpec` (w, d, h, roof gable/flat/step, colours, window grid cols × rows, optional door, `facadeOnly` for far rows, seed) |
| `components/PaperDoor.tsx` | `PaperDoor` (`DoorSpec`: kind, w, h, frame/leaf/trim colours, panel inset/window/planks, border, reveal; `open` 0–1, bifold, hinge side) and `doorOpening` (its silhouette, for portals) |
| `components/PaperCharacter.tsx` | `PaperCharacter` puppet with `PuppetPose` (walk, stride, lean, head, look, blink, arms, mouth, squash) from `restPose({...})`; `colors` overrides `PUPPET_COLORS`; `scarf` and `curl` switch the scarf and hair curl |
| `components/PaperClock.tsx` | `PaperClock` (r, `hours` as a continuous number; hands follow it) |
| `motion/easing.ts`, `motion/track.ts` | `clamp`, `mix`, `remap`, `ease.*`, `bezier`, `foldEase`, `popEase` (one small overshoot), `settle`, `hash01`; C1 keyframe tracks `keys` / `sample` |
| `render/Stage.tsx` | Light rig + finishing chain: MSAA → contact shadows (GTAO) → sanitize → depth of field → soft bloom → neutral tone map → paper grade (warmth, vignette, grain). `Shot`, `Light`, `mixLight`, and **portals**: `PortalState` renders another world through an open door with true perspective, so the camera can fly through a doorway without a cut |
| `example/` | `PaperworldExample` — a 5 s paper-village morning (pop-ups, clock, door, puppet walk, camera dolly with focus rack). Copy this file to start a scene |

## Register the example

```tsx
import { PaperworldExample, PAPERWORLD_EXAMPLE_SECONDS } from "./kits/paperworld/example/PaperworldExample.tsx";

<Composition id="PaperworldPreview" component={PaperworldExample}
  durationInFrames={PAPERWORLD_EXAMPLE_SECONDS * preview.fps}
  fps={preview.fps} width={preview.width} height={preview.height} />
```

Then `node scripts/stills.mjs out/paper 0.5s,2.6s,4.9s PaperworldPreview`.

## Building a scene

1. **Time in seconds.** `const s = frame / fps;` Every pop, fold and walk is a window `remap(s, a, b)` passed
   through an easing, so the same scene works at 30 fps (preview) and 60 fps (final).
2. **Stage first.** One `ThreeCanvas` (`shadows`, `dpr={1}`, `antialias: false`, `preserveDrawingBuffer: true`)
   holding one `<Stage shot light frame center>`; put every set piece inside it. `center` is where the key
   light centres its shadows: keep it near the action.
3. **Light.** Copy `MORNING` from the example; warm key from the upper left, cool fill, bright paper sky
   (`bg`). For evening scenes lower `keyI`, warm the key and raise lamp `glow`. `mixLight(a, b, t)` blends two.
4. **Sets.** Build from `Cut` + `S` shapes, the ready-made pieces above, or new components following
   `PaperBuilding` (extrude thin card, inset darker backing behind cut windows, trim strips, door on the face).
   Keep scale coherent: the puppet is about 6.3 units tall at scale 1.
5. **Reveal pieces** with `ShearPop t={popEase(remap(s, a, b))}` (whole buildings, trees, hills) or `PopUp`
   (single cards). Stagger 0.1–0.2 s between neighbours, back to front.
6. **Puppet.** Path from `mix`/keyframes; `walk` advances about 1.5 strides per second of walking; ramp
   `stride` in and out over ~0.4 s; set `yaw` to the walking heading (`Math.atan2(dx, dz)`). Give each film its
   own colours via `colors` and decide on `scarf`/`curl` — never reuse the example's look as a buyer's character.
7. **Camera.** One motivated move per shot: dolly, push-in through a door, overhead reveal. `focus` = distance to
   the subject (rack it with `mix` when the subject changes); aperture 0.0006–0.0012, maxBlur 0.003–0.006.
8. **Portals (optional).** For "through the door into another world": build both worlds in the same scene far
   apart, give the door's `Pose` in each world and the destination `Light`; pass `portal={...}` to `Stage`
   while the door is open (`opening: doorOpening(spec)`).

## Checks before showing a preview

- Every piece reads as paper: visible edge thickness, matte surface, contact shadow where it meets the ground.
- Nothing floats or intersects; pop-ups finish (t = 1) before the camera dwells on them.
- At 9:16 the frame is narrow: stack the set in depth (foreground puppet, midground street, background
  hills/sky) rather than spreading it sideways.
- Texture grain stays still in motion (do not change `grainTile` per scene).
- Render stills at the start, middle and end of each scene, then a short clip, before the final.

Known limits: one key light with shadows; no cloth or paper-bending simulation (folds are rigid hinges and
shears); the puppet is a single rig — new characters are new components built the same way.

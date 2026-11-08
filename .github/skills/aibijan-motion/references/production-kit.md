# AI BIJAN production kit (starter + style kits)

The skill ships a small, tested Remotion + React Three Fiber code base so every buyer project starts
from the same proven foundation instead of re-inventing camera, light, post-processing and render
scripts. `SKILL_DIR` = the folder containing `SKILL.md`.

| Folder | What it is | Reference builds |
|---|---|---|
| `starter/` | A complete, empty 3D project: core library + scripts + `Showcase` check scene | Typechecked; smoke stills; previews and 1080×1920/60 finals with muxed VO |
| `kits/timeforge/` | Object-evolution engine (parameter-tree morphs, rewind, subject contract, year odometer, time rail) + a 3-era example subject | Phone-evolution film; **TV evolution** subject built from this kit in an empty project, preview + final |
| `kits/worldforge/` | Outdoor world kit: seeded terrain heightfields, near/far terrain mesh, baked sun/AO, sky dome + environment, outdoor stage with sun shadow box, geometry factories, plants, path travel + a fort example | Mars and Rome films; **underwater city** film (31 s, with VO) built from this kit in an empty project at 1080×1920/60 |
| `kits/buildforge/` | Construction engine: components with seats, placement methods (crane lower, slide, fill, pour front, drive, draw, grow, coat, lay pieces), phase schedule, GPU-free order/clash check, terrain grading/excavation, materials with wet concrete and finish sweeps, crane rigging, blueprint ghosts, world builder, stage labels + a garden-pavilion example | A two-storey house film; the **pavilion** example built in an empty project, check OK, stills at every stage |
| `kits/paperworld/` | Paper-diorama engine: procedural paper stock, cut shapes with holes, extruded cards, hinges and pop-up mechanisms, buildings, doors, puppet character with walk/blink/colours, clock, hills/trees/clouds/lamps, stage with portals between worlds + a paper-village example | A door-portal paper film; the **paper village** example built in an empty project |
| `kits/pixelstory/` | 2.5D pixel engine (no WebGL): palette ramps, ASCII sprites + sel-out finish, painted albedo sets relit per frame, sprite lighting, layered pinhole camera with depth of field, light shafts, dust, pixel typography + four OFL fonts + a moonlit-room example with a walking cat | A pixel-art creator film; the **moonlit room** example built in an empty project |
| `kits/characterflow/` | Character engine + automatic director: procedural rig with leg/arm IK, 10 looks + outfits (incl. hijab), 26 expressions, 38 actions, walking/sitting/bed locomotion, phone + sling-bag props, six environments (campus, bedroom, classroom, office, café, living room — rooms from one data-driven room kit) and several places per video, 18 automatic shots, thought bubbles, comedy props, word captions, synthesized sound, story validation, transcription (whisper.cpp or script), macOS TTS, automated QA (13 checks) + REVIEW.md gate; one `stories/<id>/story.json` per video | Validation films on campus (2 people), in a bedroom (1 person) and across seven places (all six environments); fresh-project tests from the ZIP alone and from a new Claude session given one sentence, 1080×1920/60, QA 13/13 |
| `kits/cinestory/` | CINÉSTORY 3D as a **project of its own** (`project/`): automatic director (portrait + landscape cameras, pacing, captions, light, grade), world builder (rooms, doors, terrain, streets, scatter, fixtures) and object builder from JSON, character rig and acting, story analyzer, VO alignment, synthesized sound, geometry preflight, 13 automated QA checks + a visual review gate, example stories | Fresh buyer tests: an office horror night, a minimarket "what if", a smoky city street — each with its own world built in the session |
| `kits/voxelverse/` | Voxel engine: chamfered-box modelling merged per material, architecture kit, spec-driven buildings with instanced glow windows, diorama plinth, dusk sky/environment/fog, rain, Hermite camera shots, post chain + a dusk-street example | A voxel city film; the **dusk street** example built in an empty project |

PRECISION and INNERWORKS have no dedicated kit: build on the core below (its studio, post chain, slab geometry and materials come from PRECISION) and follow the style reference; budget extra look-development rounds (a new INNERWORKS machine typically needs two fix iterations).

## Creating a project (always with the buyer's permission)

1. Pick a folder (ask; default `~/AI BIJAN/<short-name>`). Never write into an unrelated project.
2. `node SKILL_DIR/new-project.mjs <folder> --kit=<style>` copies the starter (all files, including `package-lock.json`) plus the style's kit: `src/` into the project's `src/`; for PIXELSTORY also `public/` (fonts), for BUILDFORGE, CHARACTER FLOW and PANELVERSE also `scripts/` (PANELVERSE also `public/`: fonts). `--kit=precision` / `--kit=innerworks` copy the starter alone (those styles have no kit). The starter's and the kits' source ships packed in `PACKED_SOURCE*.txt` (the Claude app's skill upload takes at most 200 files), so set projects up with `new-project.mjs` only — never copy those folders by hand.
3. CINÉSTORY instead: `--kit=cinestory` copies the kit's own `project/` as the whole project (not the starter); never add another style's kit to it. Read the kit's `KIT.md`.
4. `npm ci` (≈370 MB of packages; minutes on a first install). Then `npm run check-env`. Budget ≈1.2 GB of disk per project after the first render; `node_modules/.cache` (bundler cache) grows with every bundle and can be deleted any time.
5. `npm run smoke` — renders three stills of the `ShowcasePreview` check scene (first run also downloads Remotion's headless Chrome, ≈170 MB). **Look at `out/smoke/_sheet.jpg`.** If it fails or is black, stop and use `references/setup.md` troubleshooting before promising any video.
6. On the first project on a machine with a kit, register the kit's example (see its `KIT.md`) and look at its stills once.
7. Create the film in `src/scenes/<name>/` (or `src/subjects/<name>/` for TIMEFORGE) and register a **preview** and a **final** composition in `src/Root.tsx` from `PRESETS` (`src/core/film/frame.ts`). Kit examples are technical checks: start a film by copying the example's structure, never its subject, set or characters.

## Core library (`src/core`)

| Module | Use |
|---|---|
| `film/Stage3D.tsx` | The frame: waits for the font, mounts `ThreeCanvas` with the standard settings, draws a 2D `overlay` (laid out at 1080×1920, auto-scaled) and an optional fade |
| `film/Posed.tsx` | Place a group by a world matrix computed for this frame |
| `film/Voiceover.tsx` | VO at an offset (Studio preview; finals mux the same file/offset) |
| `film/frame.ts` | `PRESETS.preview` 540×960@30, `PRESETS.final` 1080×1920@60 |
| `motion/track.ts` | `keys(...)`/`sample()` — C1 Catmull–Rom keyframe tracks with monotone clamp and `hold` keys (used in every reference film) |
| `motion/easing.ts` | `ease.*` named curves, `remap`, `mix`, `seg`, `settle` |
| `camera/director.ts` | `shotAt(rig, t, lens)` — rig keys `[tx,ty,tz, azDeg, elDeg, dist, fovDeg]` → `Shot` |
| `render/Post.tsx` | `CameraRig` + `Post`: MSAA → GTAO → bokeh DOF → bloom → PBR Neutral tone map → grade (`aoRadius` per scale) |
| `studio/Studio.tsx` | Bright studio: PMREM softbox light stage, cyclorama with tint pool, optional plinth + ring, PCF key shadow, blob contact shadow; `NEUTRAL_LOOK`, `PLINTH`, `shadow` bounds for other scales |
| `materials/sweep.ts` | `MatState` numbers (`mat()`), `plainMaterial()` (any mesh), `sweepMaterial()` (Slab meshes only — needs the `tfPos` attribute), `applyMat(m, A, B, sweep)` |
| `materials/presets.ts` | `MATERIALS.*` starting recipes: plastics, aluminium, polished metal, ceramic, dark glass, rubber, paper, concrete, wood |
| `materials/softboxOnly.ts`, `textures.ts` | highlights only from area lights; procedural perforation/board textures |
| `geometry/slab.ts` | Fixed-topology rounded slab (per-corner radius, fillets, seam, bulge; becomes a sphere) — morphs without remeshing |
| `geometry/lathe.ts` | `lathe(profile)` for turned parts; `BARREL` profile |
| `type/fonts.ts` | `FONT` (Manrope, bundled in `public/fonts` and loaded with `FontFace` — no network needed), `INK`, `INK_SOFT` |
| `type/brand.ts` | `BRAND` (buyer brand kit from `src/brand.json`), `brandName()`, `brandAccent(bg, styleColour)` (contrast-checked), `contrast()` |
| `type/project.ts` | `projector(shot, 1080, 1920)` — world point → overlay pixels for labels/callouts |

### Patterns that keep renders deterministic and good

- Compute everything from `t = frame / fps` (or a cut clock); geometry updates in `useLayoutEffect`; the only `useFrame` is the post composer render. No `Math.random()` at render time — seed.
- One coordinate/layout module per film: geometry, labels and camera targets read the same numbers.
- Objects that change shape: one parameter tree, parked (not deleted) parts, staggered per-part timing (see `kits/timeforge`).
- Studio objects: pose with `Posed`, keep the plinth top at `PLINTH.top`, size blob shadow to the footprint. Outdoor scenes: use `kits/worldforge` (sun, sky, terrain) instead of the studio.
- Labels: project a 3D anchor with `projector`, show one label at a time in portrait, keep text out of the top ~17% / bottom ~9% when other typography lives there.
- Close-ups: verify the important part is not occluded (choose the camera side opposite the moving parts) and that removed shells move *away* from the camera.
- **Custom shaders:** keep math finite — clamp before `pow`, guard `normalize` of possibly zero vectors, avoid division by values that can reach 0. Every post chain (`src/core/render/Post.tsx`, and in the kits WORLDFORGE `Stage.tsx`, BUILDFORGE and VOXELVERSE `Post.tsx`, PAPERWORLD `Stage.tsx`) includes a NaN guard right after the scene render, because one NaN pixel spread by bloom once blanked whole frames silently; with the guard a faulty shader shows local black specks instead — still a bug to fix, so inspect stills.
- **Bump/normal maps on thin parts** (paper, slats): screen-space bump mapping can produce NaN on surfaces seen exactly edge-on; the PAPERWORLD material guards it — copy that guard into any new bump-mapped material.
- **Additive / volumetric effects** (light shafts, glows, beams): put the mesh on `FX_LAYER` (`mesh.layers.set(FX_LAYER)`, exported by both post chains) so GTAO and depth of field ignore it; prefer drawing open-water light rays in a background dome shader.
- **Particles** (`Points`): never let them reach the lens — push flakes that come within a few metres outward, and use a soft round sprite; otherwise they render as large squares.
- **Outdoor kit in open water/sky:** the kit's GTAO darkens distant terrain into a visible horizon band; lower `look.ao` for shots without near geometry.
- **Underwater:** not built in — fog colour + gradient background dome, caustics in the terrain shader, rays in the dome, marine snow and fish must be authored (budget about 1 hour of look development).

## Scripts (`starter/scripts`)

| Command | Purpose |
|---|---|
| `npm run check-env` | Node/npm/ffmpeg/disk/platform report; installs nothing |
| `npm run smoke` | Environment + WebGL proof (3 stills of the check scene); falls back to software WebGL and writes `.gl-mode` when the GPU path fails |
| `npm run stills -- out/qa 0.5s,3s,6s <Comp>` | Stills + contact sheet (frames or seconds) |
| `npm run vo -- public/audio/vo.mp3` | Duration, pauses, phrases → `vo.timing.json` (word timings if `WHISPER_CLI`/`WHISPER_MODEL` are set) |
| `npx remotion render <PreviewComp> out/preview.mp4 --codec=h264 --crf=18` | Preview MP4 |
| `npm run render:final -- --comp=<FinalComp> --out=out/final.mp4 --vo=public/audio/vo.mp3 --offset=0.4` | Restartable segmented final, VO mux at offset, −14 LUFS master (`scripts/deliver.mjs`) |
| `node scripts/compare-stills.mjs dirA dirB` | Regression check after edits (GPU noise tolerant) |

The frame-difference scan described in quality gates flags every hard cut; compare the flagged frames with the cuts you planned before calling them pops.

## Quality bar

`references/quality-bar/<style>.jpg` holds seven frames from each reference film (`cinestory.jpg`: a CINÉSTORY film that passed QA and review). Before showing a preview, compare your stills against the style's strip: subject detail, material separation, light, shadow contact, framing and typography should be in the same class. If they are clearly weaker, iterate before presenting (a new film usually needs 1–2 iterations).

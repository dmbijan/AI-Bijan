# TIMEFORGE kit — object evolution through eras

`node SKILL_DIR/new-project.mjs [folder] --kit=timeforge` copies the starter plus this kit's `src/` into the project's `src/`. It adds
`src/kits/timeforge/` and depends only on `src/core`.

| Folder | Content |
|---|---|
| `morph/lerpTree.ts` | Interpolate two parameter trees leaf by leaf; `Rule`s give each dotted path its own window/easing |
| `morph/eraTimeline.ts` | `evaluate()` (hold / morph / **reverse** morph), `placeMorphs()` puts timing-free `MorphDef`s on any clock |
| `film/subject.ts` | The `Subject<P, Fx>` contract: labels, rail, `halfHeight`, `footprint`, `hover`, `anchors`, `Model`, `Extras` |
| `film/cut.ts` | `Cut<P, Fx>`: clock, morph windows, yaw/tilt, camera rig, label visibility, fx tracks, title/closing/fade, VO |
| `film/choreography.ts`, `World.tsx`, `Overlay.tsx`, `TimeforgeFilm.tsx` | Subject + cut + frame → pose, camera, studio tint, material sweep, year odometer, descriptor, time rail |
| `type/YearCounter.tsx`, `TimeRail.tsx` | Odometer year, N-stop rail |
| `example/` | A deliberately simple 3-era camera subject + 7 s cut + `TimeforgeExample` component (copy this folder to start) |

## New subject in 7 steps

1. `src/subjects/<topic>/params.ts` — ONE tree with every part of every era; absent parts parked (sunk, shrunk, retracted).
2. `eras.ts` — values per era + `ERA_LABEL` (year, `decade`, short descriptor).
3. `evolution.ts` — one `MorphDef` per era change; stagger with `Rule`s (outgoing parts → main form → incoming parts); `SWEEP` windows.
4. `Model.tsx` — geometry from params every frame: `Slab` bodies with `sweepMaterial` (front/side/back groups), other meshes with `plainMaterial` + `applyMat`.
5. `subject.ts` — title, rail, `halfHeight` (lowest point below origin, after roll), `footprint`, `hover` (0.02 for objects standing on legs/stands; default 2.2 floats).
6. `cut.ts` — preview cut in seconds; later a full cut in **VO seconds** (`clock = frame/fps − offset`, offset from `npm run vo`).
7. Register: `const Film = () => <TimeforgeFilm subject={S} cut={C} />` + preview/final compositions from `PRESETS`.

Reference builds: a phone-evolution film and a 4-era television subject
(1960 cabinet → 1990 CRT → 2010 LCD → 2026 OLED), both rendered to 1080×1920/60.
Known limits: studio scale tuned for hand-held objects (5–20 units); camera keys are hand-written per film;
`sweepMaterial` only on Slab meshes.

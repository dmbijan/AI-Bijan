# CINÉSTORY 3D kit — cinematic 3D story films from a voice-over, with world building

`node SKILL_DIR/new-project.mjs [folder] --kit=cinestory` sets up a project of its own: it copies
`kits/cinestory/project/` (not `starter/`) into the folder. A CINÉSTORY project never shares a folder with another
style's kit — its director, scripts and Root are its own (the `src/core` library is the same as the starter's).
Node ≥ 22.18 (the TypeScript director and scripts run with type stripping).

| Folder (in the project) | Content |
|---|---|
| `src/director/` | `story.ts` (the story spec), `compile.ts` (blocking, acting, object interactions, shot choice, pacing, UI, captions, light, grade, sound cues), `shots.ts` (every shot type, portrait and landscape framings, camera scoring against walls, people, surfaces at the lens), `actions.ts`, `preflight.ts` (geometry QC), `cues.ts`, `grade.ts` |
| `src/gen/` | world builder (`build.ts`: rooms, doors, terrain, paths, buildings, scatter, light fixtures, places, marks) and object builder (`objects/`: primitives, materials, anchors, states) |
| `src/cine/` | character rig and looks, light rig, post / grade, FX, the motion-graphics kit (`ui.tsx`), set parts |
| `src/worlds/` | built-in worlds `coast`, `kos` and the `mind` studio |
| `src/film/` | the generic renderer and the 2D overlay (portrait / landscape layouts, safe area, word captions, brand outro) |
| `worlds/` | ready-made worlds: `rumah`, `sekolah`, `kantor`, `minimarket`, `perpustakaan` (`node scripts/worlds.mjs`) |
| `library/` | lexicon for the story analyzer and 38 ready objects (home, school, office, shop, park) |
| `scripts/` | `transcribe.mjs` + `align.mjs` (VO → words, whisper.cpp or script alignment), `analyze.mjs` + `story-nlp.mjs` (the story read in context), `objects.mjs` (object sheet), `make.mjs` (validate → direct → preflight → sound → stills / preview / final → QA), `tts.mjs`, `sound-story.ts` + `synth.ts`, `qa.mjs`, `render-final.mjs`, `stills.mjs` |

## Commands

```
node scripts/transcribe.mjs <vo.mp3> --id=<id> --script=<script.txt>   # VO → public/audio/<id>.*, stories/<id>/words.json
node scripts/analyze.mjs stories/<id>                                   # → analysis.json + REQUIREMENTS.md
node scripts/worlds.mjs [<world>]                                       # ready-made worlds: places, marks, seats, props
node scripts/objects.mjs --sheet <ids>                                  # look at new objects
node scripts/make.mjs stories/<id>/story.json --stills=4,10,18          # plan + preflight + sound + stills
node scripts/make.mjs stories/<id>/story.json --final                   # 1080×1920 @ 60 (landscape on request) + master + QA
```

Details: [references/cinestory.md](../../references/cinestory.md) (workflow) and `references/cinestory/` (worlds,
objects, actions, shots, portrait, sound, QA, limits, examples).

## Check the kit on a new machine

`npm ci`, `npm run check-env`, then `npm run smoke` — the cast sheet and the built-in worlds render to
`out/smoke/*/_sheet.jpg`. Look at them before the first film.

## Extending (development work, only when the buyer asks)

A new built-in action, shot type or look is engine work: edit `src/director/` or `src/cine/`, run `npx tsc --noEmit`,
then re-render the examples in `references/cinestory/examples/` and check their stills. Per film, write data only.

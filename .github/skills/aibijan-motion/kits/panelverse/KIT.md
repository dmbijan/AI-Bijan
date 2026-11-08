# PANELVERSE kit — retro pop comic films directed per script

`node SKILL_DIR/new-project.mjs [folder] --kit=panelverse` copies the starter plus this kit (its `src/` into `src/`, `scripts/` into `scripts/`, `public/` into `public/`; the source ships packed, so never copy it by hand). It adds `src/kits/panelverse/`, `src/pv-stories/`, the comic fonts (OFL, `public/fonts/pv-*`) and six scripts, and needs only the starter's packages. Node ≥ 22.13 (story files are TypeScript loaded with type stripping; `pv-make.mjs` adds the flag itself on 22.13–22.17).

| Folder | Content |
|---|---|
| `engine/three/` | `materials.ts` (HalftoneMaterial: cel bands + screen-locked Ben-Day dots + hard spec + dot-only print + object-space clip; InkOutline: constant-pixel inverted hull), `Toon.tsx`, `ComicStage.tsx` (ThreeCanvas + camera rig, preview scale), `rig.ts` (pose / expression model, 23 expressions, two-bone IK), `looks.ts` + `ComicCharacter.tsx` (parametric cast: 11 hair styles, 7 tops, 3 bottoms, 5 builds, accessories; 12 preset looks), `library.tsx` (58 inked objects), `sets.tsx` + `setTypes.ts` + `setPresets.ts` (interior / exterior / stage sets, 10 presets, anchors, `registerItems`), `props.tsx` (phone screens, balloon cards, `Sign`), `storyProps.tsx` (thought cloud, steam, spotlight, cliff, heart, shatter balloon, bakso bowl…) |
| `engine/panels/` | `geometry.ts` (quads, 12 layouts, shards, page camera — pure), `LivingPanel.tsx` (panels, iris, shards, page camera) |
| `engine/fx/` | `ComicFx.tsx` (impact lettering, caption box, ink burst, speed lines, ink flood, flash, onomatopoeia, impact frame, stamp, shock lines, halftone wipe), `PaperTexture.tsx` |
| `director/` | `kit.ts` (the authoring API: word clock, cameras, `panel` + `enter` / `move` transitions, `fx`, `sfx`, auto sound, `story`), `types.ts`, `lint.ts` (validity, safe zone, variety rules), `ShotRenderer.tsx`, `StoryPlayer.tsx` |
| `catalog/` | `PanelverseCatalog` — every item, look, expression, set, stage look, layout and FX on stills |
| `Films.tsx` | `<PanelverseFilms />` — every story as `<id>-preview` (540×960 @ 30, sound), `<id>-stills` (silent), `<id>-final` (1080×1920 @ 60), plus the catalog |
| `src/pv-stories/` | one folder per video (`story.ts`, `elements.tsx`, `words.json`) + generated `registry.ts` |
| `scripts/` | `pv-make.mjs` (resolve → lint → sound → stills / preview / final → QA + REVIEW.md), `pv-sfx.mjs` (45 synthesised sounds), `pv-align.mjs` (word timings: whisper.cpp, or the script's clauses aligned to the VO's pauses), `transcribe.mjs` (shared transcriber), `tts.mjs` (macOS offline narration, with approval), `qa.mjs` (13 checks + contact sheets) |
| `examples/oke/` | `script.txt`, `story.ts`, `elements.tsx` — a format reference (overthinking → three scenarios → bakso reveal). Never copy its beats into another film. |

## Commands
```
node scripts/pv-align.mjs <vo.mp3> --id=<id> --out=src/pv-stories/<id>/words.json --script=<text>   # whisper.cpp if installed, else clause alignment
node scripts/tts.mjs <script.txt> public/audio/<id>.mp3 --words=src/pv-stories/<id>/words.json   # only with approval
node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --lint          # validity + variety lint only
node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --stills=2,5,8  # lint + sound + silent stills sheet
node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --preview       # 540×960 @ 30 with sound
node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --final         # 1080×1920 @ 60 + master + 13 QA checks + REVIEW.md (re-run resumes)
node scripts/stills.mjs out/catalog 0,1,2,3,4,8,9,10,21,22,23,24 PanelverseCatalog 0.5   # the element catalog
```
`new-project.mjs --kit=panelverse` adds `<PanelverseFilms />` to `src/Root.tsx` (from `root.json`); after a manual copy `pv-make.mjs` does it on its first run. Other styles' compositions stay.

## Check the kit on a new machine
1. `node scripts/stills.mjs out/catalog 0,8,11,21,23,24 PanelverseCatalog 0.5` — items, cast, a set, stage looks, layouts and FX render.
2. With a TTS voice (macOS): `mkdir -p src/pv-stories/oke && cp SKILL_DIR/kits/panelverse/examples/oke/* src/pv-stories/oke/`, then `node scripts/tts.mjs src/pv-stories/oke/script.txt public/audio/oke.mp3 --words=src/pv-stories/oke/words.json` and `node scripts/pv-make.mjs src/pv-stories/oke/story.ts --stills=1.5,3.3,6.5,8.3,10.4,13` — a boy with a phone, a frozen panel, a 3-up page, a split, TAMAT!, a girl eating bakso. Delete `src/pv-stories/oke/` afterwards (it is not the buyer's film).

## Extending (development work, only when the buyer asks)
A new library item = one entry in `engine/three/library.tsx` (`desc` + `render`); a new set preset = data in `setPresets.ts`; a new look preset = `looks.ts`; a new FX = component in `fx/ComicFx.tsx` + a case in `director/types.ts`, `director/kit.ts` (`fx`, `soundsFor`) and `director/StoryPlayer.tsx`. Run `npx tsc --noEmit`, the catalog stills and the example after any engine change.

# PANELVERSE — Retro pop comic stories directed from a voice-over, style 11

**In this package:** the comic engine in `kits/panelverse/`:
- **Look:** cel-shaded halftone material with screen-locked Ben-Day dots and constant-width ink contours; paper print.
- **People:** a parametric cast (11 hair styles incl. hijab, 7 tops, 3 bottoms, 5 builds, glasses, moustache, tie) on an IK rig with 23 expressions (heart/star eyes, tears, sweat…).
- **Objects:** 58 inked library objects, 10 set presets plus interior / exterior / 8 stage looks.
- **Panels:** living panels with 12 layouts and 12 transitions (slam, slide, iris, slash split, expand, collapse, fall, shatter…).
- **Comic devices:** impact lettering, onomatopoeia, stamps, caption boxes, bursts, speed and shock lines, ink flood, halftone wipe, impact frames.
- **Sound:** 45 synthesised sounds with automatic placement and ducking.
- **Checks:** a story lint that enforces variety, plus automated QA and a visual review gate.

Every video is **directed from scratch for its script**: one `story.ts` (data anchored to the spoken words) plus the story's own elements in `elements.tsx`. See `kits/panelverse/KIT.md`; detailed references in [panelverse/](panelverse/).

## Identity
A living comic book: thick black ink, flat pop colours (ink #171717, paper #F6E8C8, yellow #F5CC27, red #EF4035, blue #2674B9, teal #1B927C), halftone dots in the shadows, panels that slam onto a printed page, split, expand into full-screen 3D scenes, shatter and freeze. The comic language tells the story: what the narrator says becomes a picture, a device and a sound on the exact word. Bold, funny, punchy, never a slideshow.

Choose PANELVERSE when the VO is a **relatable short story, a twist or a joke** told in beats — overthinking, expectations vs reality, "pernah ga…", lists of scenarios, a punchline reveal ("padahal…", "ternyata…"), money / time / love / work / school / family situations, everyday absurdity — and the buyer wants a *comic* (retro pop art, panels, halftone, lettering). Prefer CHARACTER FLOW for gentle acting in realistic everyday rooms, CINÉSTORY for cinematic mood and place, PAPERWORLD for handcrafted paper.

## Workflow — zero-prompt (overrides the general guided flow for this style)
A request like **"Buatkan video PANELVERSE dari VO ini: <file>"** (or "video komik / gaya komik pop dari VO ini") is the complete brief **and** the go-ahead for setup, direction and the final. Do not recap, ask for confirmation, or stop for preview approval: set up, direct, check, render and deliver, then offer revisions. Ask only when truly blocked (no VO, or no whisper.cpp and no script text). Talk friendly Indonesian; say once what you are doing and roughly how long it takes (≈ 15–30 min for a 15–40 s film).

1. **Project.** In a PANELVERSE project (`src/kits/panelverse/director/kit.ts` exists) continue there. Otherwise `node SKILL_DIR/new-project.mjs --kit=panelverse` sets one up in the current folder (allowed when it holds only hidden files and the buyer's VO/script); if it refuses, `node SKILL_DIR/new-project.mjs panelverse-project --kit=panelverse` and work inside that folder. Then `npm ci`, `npm run check-env`; on a machine's first project also render the catalog (`node scripts/stills.mjs out/catalog 0,1,2,3,4,8,9,10,11,12,21,22,23,24 PanelverseCatalog 0.5`) and look at it — it is also your menu of elements.
2. **VO → words.** Choose the `id` once (from the topic, a-z 0-9 -). `node scripts/pv-align.mjs <buyer's VO> --id=<id> --out=src/pv-stories/<id>/words.json --script=<text file>` copies the VO to `public/audio/<id>.*` and writes the word timings: with whisper.cpp it uses it (exact); without, it aligns the script's clauses to the VO's pauses (clause starts ≈ exact, words ≈ ±0.1–0.25 s). Without whisper the VO text is needed — if the buyer gave none, ask for it. **No VO:** offer (a) the buyer sends one or (b) you write a short narration and make an offline macOS voice (`node scripts/tts.mjs <script.txt> public/audio/<id>.mp3 --words=src/pv-stories/<id>/words.json`) — only after the buyer approves text and voice. Never add a narrator silently. Read the whole transcript.
3. **Direct.** Read [panelverse/directing.md](panelverse/directing.md) (beat sheet, device menu, variety rules), [panelverse/story-format.md](panelverse/story-format.md), [panelverse/elements.md](panelverse/elements.md) and [panelverse/new-elements.md](panelverse/new-elements.md). Write the beat sheet, design **at least one element of your own for this script** in `src/pv-stories/<id>/elements.tsx`, then `src/pv-stories/<id>/story.ts` (cast, sets, shots, panels, FX, extra sounds). `node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --lint` until it has no errors; read and act on the warnings. Never copy the example's beats, cast or sets.
4. **Stills.** `node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --stills=<8–14 seconds: every beat and transition>` → review `out/<id>/stills/_sheet.jpg` with [panelverse/qa.md](panelverse/qa.md); fix and repeat (up to 3 rounds).
5. **Final.** `node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --final` in the **foreground** with the longest command timeout (interrupted → run again, finished segments are reused). Output `out/<id>/<id>_1080x1920_60fps.mp4` (H.264 + AAC, −14 LUFS) + 13 automated checks + `out/<id>/qa/*.jpg`. Then gate 3: `out/<id>/qa/REVIEW.md` — look at every contact-sheet page and every transition, fill every row, set STATUS; fix what you see and run `--final` again. Never call it PASS while a visual problem is unfixed.
6. **Deliver.** MP4 path, length, one line on the story and the devices used, 3 example revisions. Revisions = edit `story.ts` / `elements.tsx` → `--final` again.

## Rules
- Per video write only `src/pv-stories/<id>/…` and the VO copy. Never edit `src/kits/panelverse/` for one video; a new library item, set or device for all future videos is development work the buyer must ask for.
- Make the film fit the script: new beat sheet, cast looks, sets (remixed), devices and at least one element of its own every time. The lint blocks monotony (< 3 layouts, < 3 transition types, repeated devices, no own element, lettering outside the safe zone).
- Never speed up, trim or re-voice the VO; never add dialogue or new voices. On-screen text: short comic lettering (1–3 words) and caption boxes only, in correct Indonesian.
- Wishes outside [panelverse/limits.md](panelverse/limits.md) (fights, dancing, crowds, realistic people, real persons, brand characters, landscape): say kindly it is not in this version and offer the closest thing.

## Quality gates
- Each beat reads with the sound off; faces carry the emotional beats; hands touch what they hold; no limb or object through a body.
- ≥ 3 screen layouts, ≥ 3 transition types, ≥ 2 places / stage looks, ≥ 4 devices, ≥ 1 element made for the script; no static stretch > 4.5 s.
- Lettering inside x 64–920, y 230–1450, never over a face; ink, halftone and palette consistent in every shot.
- 13/13 automated QA checks and REVIEW.md filled honestly.

## Hard bans
- Copying the example (`kits/panelverse/examples/oke`: the "Oke" chat, the bakso warung, the cliff) into unrelated films.
- Slideshows of still images, zooms on flat pictures, or full sentences of text on screen.
- Claiming QA passed without running it and looking at the sheets, or calling a frame fine because it is "a brief transition".

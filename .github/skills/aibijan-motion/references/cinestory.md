# CINÉSTORY 3D — cinematic 3D story films from a voice-over, style 09

**In this package:** the complete CINÉSTORY engine as a project of its own in `kits/cinestory/project/` (characters
and acting, a world builder and an object builder from data, an automatic director with portrait and landscape
cameras, light and grade for phones, word captions, synthesized music + SFX, geometry preflight, automated QA and a
visual review gate); see `kits/cinestory/KIT.md`. Per film only data is written: `stories/<id>/story.json`, usually a
`stories/<id>/<world>.world.json`, and new objects in `assets/objects/<id>.json`. Detailed references:
[cinestory/](cinestory/).

## Identity

A voice-over becomes a short cinematic 3D film: stylized people act the story in a place built for it (an office at
night, a minimarket, a city street in smoke, a coast, a boarding house…), the camera moves like a film, the light
carries the mood (night, dusk, dawn, a lamp switching off), and explanation passages are shown with the story's own
places and objects. Portrait 9:16 1080×1920 @ 60 by default (directed for the vertical frame, never a crop); landscape
16:9 1920×1080 on request.

Choose CINÉSTORY when the VO **tells a story in a place** — a mystery, a horror night, a "what if" told through
someone's day, a documentary-like narration of an event — and needs its own location, objects and light. Prefer
CHARACTER FLOW for relatable 1–2-person acting in an everyday place (campus, bedroom, classroom, office, café,
living room), WORLDFORGE for exploring an environment
without a story cast, PAPERWORLD / PIXELSTORY for those looks.

## Workflow — zero-prompt (overrides the general guided flow for this style)

A request like **"Buatkan video CINÉSTORY 3D dari VO ini: <file>"** (with or without the script) is the complete brief
**and** the go-ahead for project setup, look development and the final. Do not recap, ask for confirmation or stop
for preview approval; set up, build, direct, check, render and deliver, then offer revisions. Ask only when truly
blocked (no VO; no whisper.cpp and no script text). Friendly Indonesian, short, no jargon; say once what you are doing
and roughly how long it takes.

Defaults (never ask): portrait 9:16 1080×1920, 60 FPS, H.264 + AAC, −14 LUFS, light readable on a phone, word captions
burned in inside the TikTok/Reels safe area + an `.srt`, original synthesized music + SFX, VO untouched, no branding.
Landscape only when asked: `"format": "landscape"` in story.json (letterbox, no burned captions). Branding only from the
buyer's `aibijan-brand.json` (outro, optional watermark / intro); never write a channel name into story text, title or
end cards unless asked.

1. **Project.** In a CINÉSTORY project (`src/director/compile.ts` and `src/gen/` exist) continue there. Otherwise
   `node SKILL_DIR/new-project.mjs --kit=cinestory` sets one up in the current folder (allowed when it holds only hidden
   files and the buyer's VO / script); if it refuses, `node SKILL_DIR/new-project.mjs cinestory-project --kit=cinestory`
   and work inside that folder. Then `npm ci`, `npm run check-env` (✗ → `TROUBLESHOOTING.md`); on a machine's first
   project also `npm run smoke` and look at `out/smoke/*/_sheet.jpg`.
2. **VO → words.** `node scripts/transcribe.mjs <VO> --id=<id> --script=<script.txt>` → `public/audio/<id>.*` +
   `stories/<id>/words.json`; copy the script to `stories/<id>/script.txt`. Never retime words.json by hand; fix only
   misspelled words. A ⚠ line → look at that sentence on the stills. **No VO:** offer (a) the buyer sends one or (b) a
   narration you write + an offline macOS voice (`scripts/tts.mjs`), only after the buyer approves text and voice.
3. **Analyse.** `node scripts/analyze.mjs stories/<id>` → `analysis.json` + `REQUIREMENTS.md` (time and clock, places,
   characters, who does what to which object and whether it is acted, said, written, hypothetical or negated, state
   changes, sounds, dialogue, explanation passages, implied set dressing, unknown words). Read the script yourself
   too. Decide the **world**: built-in `coast` / `kos`, a ready-made one (`rumah`, `sekolah`, `kantor`,
   `minimarket`, `perpustakaan` — `node scripts/worlds.mjs [<id>]`, [cinestory/worlds.md](cinestory/worlds.md)), an
   existing `*.world.json`, or a new one ([cinestory/dynamic-worlds.md](cinestory/dynamic-worlds.md)), the **objects** (reuse, or build from primitives —
   [cinestory/objects.md](cinestory/objects.md)) and the **actions** ([cinestory/actions.md](cinestory/actions.md),
   [cinestory/interactions.md](cinestory/interactions.md)). Anything `complex` / `unsupported`
   ([cinestory/limits.md](cinestory/limits.md)) → tell the buyer in one line how it is handled (off screen, by sound,
   by its effects, a stylised stand-in). Story first: explanations use the story's own places and objects; the `mind`
   studio only where the script is about the brain / thoughts.
4. **Build what is missing, one piece at a time, and look at each.** Objects → `node scripts/objects.mjs --sheet <ids>`
   → `out/look/objects/_sheet.jpg`. The world → a minimal test story (one scene per place) → `make.mjs --stills=…`.
   Portrait cameras need room: ≥ 1 m free around marks where people handle things, corridors ≥ 1.4 m.
5. **Direct.** Read [cinestory/directing.md](cinestory/directing.md), [cinestory/story-format.md](cinestory/story-format.md)
   and [cinestory/portrait.md](cinestory/portrait.md); write `stories/<id>/story.json` — scenes on sentence boundaries
   (keep the VO pauses), cast and looks ([cinestory/characters.md](cinestory/characters.md)), a visual beat every 2–4 s,
   interactions on their words. Cameras, light, grade, sound and captions are automatic; force a shot
   ([cinestory/shots.md](cinestory/shots.md)) only to fix something seen. Never copy an example story's beats.
6. **Preflight + stills.** `node scripts/make.mjs stories/<id>/story.json --stills=<8–12 key seconds>` — the plan or every
   story problem in plain words; the geometry preflight ([cinestory/qa.md](cinestory/qa.md) gate 1) stops on errors
   (`camera-crowded`, `subject-out-of-frame`, `face-turned-away`, people inside objects…); read the `⚠ pacing` lines. Look at
   `out/<id>/stills/_sheet.jpg` next to `references/quality-bar/cinestory.jpg` (seven frames of a CINÉSTORY film that
   passed QA and review: the class to reach, not a look to copy); fix by editing data. Up to 3 rounds.
7. **Final.** `node scripts/make.mjs stories/<id>/story.json --final` in the **foreground** with the longest command
   timeout (re-run if interrupted; finished segments are reused). Then gate 3: open `out/<id>/qa/REVIEW.md`, look at
   `shots_start_mid_end.jpg` and **every page** of the 1-fps contact sheet, tick each page, fill every row, set STATUS.
   Never call it PASS while a visual problem is unfixed.
8. **Deliver.** MP4 path (`out/<id>/<id>_1080x1920_60fps.mp4`; landscape `…_1920x1080_60fps.mp4`), `.srt`, length, one line
   on what happens, what was built new (world, objects), what is limited and how it was handled, 3 example revisions.
   Revisions = edit the data → `--final` again.

## Rules

- Per film write only `stories/<id>/…`, `assets/objects/…`, `worlds/…` (reusable worlds) and the VO copy. Never edit
  `src/` or `scripts/` for one film; a real engine bug → work around it and say what is limited.
- Use only names from the references and your own world / objects — the validator rejects anything else and lists
  the valid names.
- Never speed up, trim or re-voice the VO; fiction stays fiction (`disclaimer`); never add facts the VO does not say.
- Wishes outside [cinestory/limits.md](cinestory/limits.md) (big fires, explosions, crowds, vehicles driving, animals
  moving, lip-sync, real people): say kindly it is not in this version and offer the closest thing.

## Quality gates

- Gate 1 preflight 0 errors; gate 2 all automated checks PASS (1080×1920 @ 60, durations, no white / black / frozen
  frames, no blown-out highlights, loudness, VO sync and not cut, contact sheet covers the video, readable on a phone);
  gate 3 the visual review filled honestly.
- Faces read; text never over a face and inside the safe area; nothing at the lens; the story reads with the sound off.

## Hard bans

- Rebuilding the engine or writing a scene by hand for a single film.
- Copying the examples (`perpustakaan`, the coast story) into unrelated films.
- Claiming QA passed without running it and looking at every contact-sheet page.

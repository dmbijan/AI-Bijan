# CHARACTER FLOW — Expressive 3D character stories directed from a voice-over, style 10

**In this package:** the complete character engine in `kits/characterflow/` (procedural rig with leg/arm IK, 10 looks + outfit options incl. hijab, 26 expressions, 38 actions, walking with planted feet, sitting, lying in bed, phone and sling-bag props, six environments — campus, bedroom, classroom, office, café, living room — and several places per video, 18 automatic camera shots, thought bubbles, comedy props, word captions, synthesized sound, automated QA + visual review gate) and an **automatic director**: per video only one `stories/<id>/story.json` is written; see `kits/characterflow/KIT.md`. Detailed references: [characterflow/](characterflow/).

## Identity

Relatable, bright, stylized 3D "soft plastic" characters act out what the narrator says — everyday life, psychology, school/campus, work, friendship, family, habits, night thoughts, "what if" situations — with readable faces, clear gestures, thought bubbles and comedy beats, in one place or moving between several (home, school, office, café, campus). The camera is a patient storyteller (establish, medium, close-up, over-the-shoulder, two-shot), never a showreel. The voice-over drives everything: actions land on the spoken words.

Choose CHARACTER FLOW when the VO is about **people and situations** ("kamu pernah…", "kenapa kita…", "bayangin kalau…", a friend, a habit, a feeling, family, work) and 1–2 characters per place can tell it in a campus walkway, a bedroom, a classroom, an office, a café or a living room. Prefer PIXELSTORY for pixel-art worlds and PAPERWORLD for paper dioramas; CHARACTER FLOW is the 3D character-acting style.

## Workflow — zero-prompt (overrides the general guided flow for this style)

A request like **"Buatkan video CHARACTER FLOW dari VO ini: <file>"** is the complete brief **and** the go-ahead for project setup, look development and the final. Do not recap, ask for confirmation, or stop for preview approval; set up, direct, check, render and deliver, then offer revisions. Ask only when truly blocked (no VO, or no whisper.cpp and no script text). Talk friendly Indonesian; say once what you are doing and roughly how long it takes.

1. **Project.** In a CHARACTER FLOW project (`src/kits/characterflow/director/compile.ts` exists) continue there. Otherwise `node SKILL_DIR/new-project.mjs --kit=characterflow` sets one up in the current folder (allowed when it holds only hidden files and the buyer's VO/script); if it refuses, `node SKILL_DIR/new-project.mjs characterflow-project --kit=characterflow` and work inside that folder. Then `npm ci`, `npm run check-env`; on a machine's first project also `node scripts/stills.mjs out/look 0,12 CharacterFlowLook` and look at the sheet.
2. **VO → words.** Choose the `id` once, before transcribing (from the topic if you know it, else from the VO file name, e.g. `video-1`) and keep it — it names the story folder, audio and output. `node scripts/transcribe.mjs <buyer's VO path> --id=<id> [--script=<text file>] [--lang=id]` copies the VO to `public/audio/<id>.*` and writes `stories/<id>/words.json` (whisper.cpp = exact; without it the VO text is needed, ≈0.3 s less precise). **No VO:** offer (a) the buyer sends one or (b) you write a short narration and make an offline macOS TTS voice (`scripts/tts.mjs`) — only after the buyer approves text and voice. Never add a narrator silently. Read the whole words.json.
3. **Direct.** Read [characterflow/directing.md](characterflow/directing.md), [characterflow/story-format.md](characterflow/story-format.md), [characterflow/environments.md](characterflow/environments.md) and [characterflow/actions.md](characterflow/actions.md); write `stories/<id>/story.json` (environment + time — or `scenes` when the VO moves between situations —, cast with looks / outfits, where each person starts (seat or mark), a beat every 2–4 s cued on spoken words, thoughts, highlights, optional card/comedy). Camera, sound, captions and timing are automatic ([characterflow/shots.md](characterflow/shots.md)). Never copy an example story's beats.
4. **Stills.** `node scripts/make.mjs stories/<id>/story.json --stills=<6–10 seconds at key beats>` — it validates the story (plain-language errors → fix and rerun), registers the films in `src/Root.tsx` once, builds the sound and renders stills. Review `out/<id>/stills/_sheet.jpg` with [characterflow/qa.md](characterflow/qa.md); fix by editing the story. Up to 2 rounds.
5. **Final.** `node scripts/make.mjs stories/<id>/story.json --final` in the **foreground** with the longest command timeout (a session that ends kills a background render); interrupted → run it again, finished segments are reused. Output `out/<id>/<id>_1080x1920_60fps.mp4` (H.264 + AAC, −14 LUFS) + 13 automated checks + `out/<id>/qa/*.jpg`. Then gate 3: open `out/<id>/qa/REVIEW.md`, look at `shots_start_mid_end.jpg` and **every page** of the 1-fps contact sheet as a viewer would, tick each page, fill every row, set STATUS; fix what you see (edit story.json, `--final` again). Never call it PASS while a visual problem is unfixed.
6. **Deliver.** MP4 path, length, one line on the story, 3 example revisions. Revisions = edit story.json → `--final` again.

## Rules

- Per video write only `stories/<id>/…` and the VO copy. Never edit `src/kits/characterflow/` for one video; a new environment or look is a development task the buyer must ask for.
- Use only the actions, environments, looks, props, shots, icons and vignettes in the references — the validator rejects anything else.
- Never speed up, trim or re-voice the VO; never add dialogue or new voices.
- Wishes outside [characterflow/limits.md](characterflow/limits.md) (a place that is not one of the six, > 2 people in one place, lip-sync, running, crowds, vehicles, real people): say kindly it is not in this version and offer the closest thing.

## Quality gates

- Every shot shows the acting person's face (a bowed head over a phone is fine); the action reads (arm visible for point/wave/explain, body for sit/stand).
- No camera inside or against trees, furniture or bedding; nobody blocks the lens; no limbs at odd angles; no cut bubble or caption.
- Captions never cover a face; thought bubbles fully in frame; the story reads with the sound off.
- 13/13 automated QA checks (streams, duration, A/V length, no white / black / frozen / burnt frames, loudness, VO sync ±30 ms, VO not cut, contact sheet covers the video, readable on a phone) and REVIEW.md filled honestly.

## Hard bans

- Rebuilding the engine or writing a scene by hand for a single video.
- Copying the example stories (teman-lama, menunda) into unrelated films.
- Claiming QA passed without running it and looking at the sheets, or calling a frame fine because it is "a brief transition".

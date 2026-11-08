# AI BIJAN Motion style registry

What each of the eleven styles is, what this package includes for it, and its known limits. Read-only during buyer sessions.

| ID | Name | Direction | Engine in this package | Known limits |
| --- | --- | --- | --- | --- |
| 01 | PIXELSTORY | Cinematic 2.5D pixel-character storytelling | `kits/pixelstory` (+ fonts) | Lights cast no shadows (paint them); every film designs its own cast; very large layers slow renders |
| 02 | VOXELVERSE | 3D handcrafted voxel worlds | `kits/voxelverse` | One shadowed key light; voxel characters are built per film; keep far rows simple in large worlds |
| 03 | PRECISION | Premium hard-surface product CGI / exploded view | core (studio, post, slab, materials come from this style) | No SDF/pebble geometry kit; soft organic product forms need extra work |
| 04 | PAPERWORLD | Tactile paper-craft storytelling | `kits/paperworld` | Folds are rigid hinges and shears (no paper bending); one puppet rig — new characters are new components |
| 05 | INNERWORKS | Cutaway technical explainer | core only (no mechanism-layout kit) | A new machine typically needs 1–2 look-dev fix rounds (hidden parts, label collisions, occlusion) |
| 06 | WORLDFORGE | Explorable full-3D environments | `kits/worldforge` | No water, forest, animal, crowd or city-scale systems; author them when a brief needs them |
| 07 | TIMEFORGE | One object evolving through dated eras | `kits/timeforge` | Studio tuned for hand-held objects (5–20 units); camera keys hand-written per film |
| 08 | BUILDFORGE | Component-by-component 3D construction | `kits/buildforge` (+ sequence check) | Exact clash check for 90° rotations; fits simple orthogonal structures; not engineering instruction |
| 09 | CINÉSTORY 3D | Cinematic 3D story films from a voice-over, in a location built for the story, directed automatically | `kits/cinestory/project` — a project of its own (director, world + object builder, transcription, analyzer, TTS, sound, preflight, QA scripts) | Stylized people, no lip-sync; no big fires / explosions / rain / snow, no crowds, moving animals or driving vehicles (handled off screen, by sound or by their effects); portrait 9:16 by default, landscape on request; tested on macOS Apple Silicon |
| 10 | CHARACTER FLOW | Expressive 3D character stories acted out from a voice-over, directed automatically | `kits/characterflow` (+ director, transcription, TTS, sound, QA scripts) | 6 environments (campus, bedroom, classroom, office, café, living room), several per video; 1–2 people per place (up to 4 per story) from 10 looks + outfits; walking only along the campus path; no lip-sync; lying in bed = face/gaze/bubble acting; tested on macOS Apple Silicon |
| 11 | PANELVERSE | Retro pop-art comic stories from a voice-over: living panels, halftone 3D, lettering, directed per script | `kits/panelverse` (+ story kit, lint, transcription, TTS, synthesised SFX, QA scripts) | Stylised people from primitives (mitten hands, no lip-sync, no cloth simulation); no fights / dancing / crowds / realistic people; places = interiors, simple exteriors and abstract comic stages; short lettering only (no full subtitles); portrait 9:16 only; each video needs ≥ 1 element made for its script; tested on macOS Apple Silicon |

PRECISION and INNERWORKS have no dedicated kit; plan for a second preview round (see the preview-rounds note in SKILL.md).

## What this package contains

One installable `aibijan-motion` skill with eleven selectively loaded style references. Never quote prices. This ZIP contains the generic production kit (starter, core library, style kits for PIXELSTORY, VOXELVERSE, PAPERWORLD, WORLDFORGE, TIMEFORGE, BUILDFORGE, CHARACTER FLOW and PANELVERSE with technical examples, scripts, bundled OFL comic fonts; the CINÉSTORY 3D project with example stories and worlds) and small quality-bar stills from the author's reference films. It does **not** contain or license the reference projects, stories, product designs, ElevenLabs recordings, music or video renders. Buyers need Node.js, npm and (for audio work) ffmpeg; see `references/setup.md`.

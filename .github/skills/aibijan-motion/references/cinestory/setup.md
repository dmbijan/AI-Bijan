# Setup, timing, tools

## Requirements
| | |
|---|---|
| Node.js | ≥ 22.18 (runs the `.ts` scripts directly). Tested: 24. |
| ffmpeg + ffprobe | VO analysis, mixing, delivery, QA. |
| Disk | ≥ 5 GB free; a project ≈ 1.2 GB with `node_modules` + browser. |
| OS | macOS Apple Silicon tested. Windows/Linux untested (WebGL must work in headless Chrome; `scripts/gl.mjs` falls back to software GL, very slow). |
| whisper.cpp (optional) | word timing from any VO; with `--script` the script's spelling is kept and timed by whisper. `brew install whisper-cpp` + model `ggml-large-v3-turbo-q5_0.bin` in `~/.cache/whisper.cpp/`. Or set `WHISPER_CLI` / `WHISPER_MODEL`. Without it the script is aligned to the VO's pauses (sentence starts on their pause, words ±0.3 s). `--method=script` forces that path. |
| macOS `say` (optional) | offline Indonesian TTS (voice Damayanti) for a temporary narration — only with the buyer's approval. |

## Commands (inside the project)
| command | what |
|---|---|
| `npm ci` | install the locked packages (once per project) |
| `npm run check-env` | environment report |
| `npm run smoke` | renders the cast sheet and both worlds → `out/smoke/*/_sheet.jpg` |
| `node scripts/transcribe.mjs <vo> --id=<id> [--script=<txt>] [--method=script]` | VO → `public/audio/<id>.*` + `stories/<id>/words.json` (global alignment, no manual retiming) |
| `node scripts/analyze.mjs stories/<id>` | the story read in context → `analysis.json` + `REQUIREMENTS.md` |
| `node scripts/tts.mjs <script.txt> public/audio/<id>.mp3 --words=stories/<id>/words.json` | temporary TTS narration + its words |
| `node scripts/make.mjs stories/<id>/story.json --stills=4,10,18` | plan + sound + still frames |
| `node scripts/make.mjs stories/<id>/story.json --preview` | 960×540 @ 30 with sound (fast check of motion) |
| `node scripts/make.mjs stories/<id>/story.json --final` | portrait 1080×1920 @ 60 (landscape 1920×1080 with `"format": "landscape"`), mastered, QA |
| `… --final --from=25 --to=45` · `… --preview --from=… --to=…` | render only a section (review a passage) |
| `npm run look:cast` / `look:coast` / `look:kos` | lookdev sheets |
| `npm run dev` | Remotion Studio (scrub any composition) |

## Measured times (Apple Silicon, M-series)
| step | 80 s film |
|---|---|
| sound | ≈ 3 s |
| 12 stills | ≈ 30–60 s (first run bundles) |
| preview 960×540@30 | ≈ 40 s |
| final 1080×1920@60 (or 1920×1080@60) + master + QA | ≈ 2–3 min |
First render in a project also downloads headless Chrome (≈170 MB). Slower machines: several times longer.

## Outputs
`out/<id>/<id>_1080x1920_60fps.mp4` (landscape `_1920x1080_`), `out/<id>/<id>.srt` (captions from the VO words), `out/<id>/shots.json`
(the shot plan), `out/<id>/qa/` (sheets + qa.json), `public/audio/<id>-mix.wav` (the soundtrack).

## Packages and audit
Versions are locked (`package-lock.json`). v1.2 adds `"overrides": { "zod": "4.6.7" }`: Remotion 4.0.539 (the
latest stable) pins zod 4.5.4, which has a memory-exhaustion advisory (GHSA-mrhx-c7xc-m3w5) reported by
`npm audit` as 10 "high" findings (zod + 9 Remotion packages that carry it). The override is a patch inside zod 4;
`npm audit` reports 0 vulnerabilities. `npm warn … install-scripts … esbuild` on npm 11 is harmless (the binaries
come from optional dependencies).

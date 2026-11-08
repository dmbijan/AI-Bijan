# Environment setup and troubleshooting (Claude-facing)

Use this before the first render on a buyer's machine. Talk to the buyer in plain Indonesian; run the
checks yourself; ask before installing anything or downloading large files.

## Requirements

| Item | Needed for | Notes |
|---|---|---|
| Node.js ≥ 22.13 + npm (CINÉSTORY: ≥ 22.18) | everything | Claude Code's native installer does **not** install Node. If missing, ask the buyer to install the LTS from nodejs.org (or Homebrew on macOS) — explain it is a one-time program install. |
| 5 GB+ free disk recommended | packages (~370 MB), headless Chrome (~170 MB download, ~570 MB extracted), bundler cache (grows, deletable: `node_modules/.cache`), renders, temporary frames | ≈1.2 GB per project after the first render. Long 1080p renders use segments to avoid filling disk/swap |
| Internet | `npm ci`, first-render Chrome download | Fonts are bundled in `public/fonts`; rendering itself works offline |
| ffmpeg + ffprobe on PATH | VO analysis, contact sheets, final audio mux/loudness | Remotion renders video without them, but `vo`, `stills` sheets and `render:final` with VO need them. macOS: `brew install ffmpeg`; Windows: winget/choco or the official builds |
| whisper.cpp + a model (optional) | CHARACTER FLOW, CINÉSTORY and PANELVERSE word timings from any VO | `brew install whisper-cpp` and a model in `~/.cache/whisper.cpp` (`ggml-large-v3-turbo-q5_0.bin` from huggingface.co/ggerganov/whisper.cpp); `check-env` reports it. Without it CHARACTER FLOW needs the VO text (≈0.3 s less precise) |
| macOS `say` with the Indonesian voice Damayanti (optional) | CHARACTER FLOW, CINÉSTORY and PANELVERSE offline narration, only with the buyer's approval | `check-env` reports it; other systems bring a recorded VO |
| GPU/WebGL in headless Chrome | all 3D styles | Renders use `--gl=angle`. Best on a Mac with Apple Silicon or a PC with a GPU. Always run `npm run smoke` on a new machine. If WebGL fails (no GPU, some VMs or drivers), `stills.mjs` and `render-final.mjs` switch to `swangle` automatically (software, very slow: ~3 s per 540×960 frame on 2 CPUs) and save it in `.gl-mode`, which `remotion.config.ts` also reads; tell the buyer once, in plain words, that renders will be slow on this computer |

## First-run flow

1. `npm run check-env` → read the report. Missing Node or ffmpeg: explain and ask the buyer to install (give one command or link, not a lecture).
2. With permission: `node SKILL_DIR/new-project.mjs <folder> --kit=<style>` (never copy `starter/` or a kit by hand: their source ships packed), then `npm ci`.
3. `npm run smoke` and **look at the sheet**. Only after it passes, start look development.

Suggested buyer wording: "Sebelum mulai, aku cek dulu komputer kamu siap untuk render 3D (sekitar 1–3 menit; pertama kali mengunduh sekitar 540 MB dan memakai sekitar 1,2 GB ruang disk). Boleh aku lanjut?"

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| `node: command not found` / version < 22.13 (CINÉSTORY: < 22.18) | Node missing/old | Install Node LTS, reopen the terminal |
| `npm ci` fails with network errors | offline / proxy | Retry with internet; `npm config get registry` |
| `npm warn install-scripts … esbuild` | npm 11+ blocks install scripts by default | Harmless for this project (binaries come from optional dependencies) |
| Stills are black / `WebGL` errors | GPU path unavailable in headless Chrome | The scripts already fall back to `swangle` and write `.gl-mode`; if stills are still black, check shaders. To retry the GPU after a driver update, delete `.gl-mode` |
| `delayRender` timeout on font | `public/fonts` missing from the project | Copy `starter/public/fonts` into the project again |
| Render dies / disk fills on long 1080p | swap + temp frames | `npm run render:final -- … --concurrency=2 --parts=4`; free disk; re-run (finished segments are reused) |
| `ENOSPC` | disk full | Free space: delete `node_modules/.cache` (bundler cache), old renders in `out/`, leftover `$TMPDIR/remotion-*` bundles |
| Audio out of sync | wrong offset or edited VO | Re-run `npm run vo`, keep cut keys in VO seconds, render final with the same `--offset`; verify with `ffmpeg -af silencedetect` on the output |
| VO too quiet/loud | not mastered | `render:final` masters to −14 LUFS; check the printed `in/out` LUFS |
| Fonts differ between machines | system fallback used | Keep `FONT` from `core/type/fonts.ts`; never rely on system fonts in overlays/canvases |

Report blockers plainly and stop before claiming a render worked.

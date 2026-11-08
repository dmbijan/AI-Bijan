# Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `--check` says python:cv2 MISSING | OpenCV 5 wheels ship without the Haar face cascades | `pip install --break-system-packages 'opencv-python-headless<5'`, then re-run `--check`. Without it, framing falls back to a centre crop. |
| `inspect-media.py --check` says chromium MISSING | No headless browser in the container | `pip install playwright && playwright install chromium-headless-shell` (or `chromium`). If the download host is blocked: `render-video.py --engine ffmpeg` (cuts, zooms, captions, sound — no motion graphics) and tell the user what is missing. Set `VY_BROWSER=/path/to/chrome` if you know a binary. |
| `npm install` fails | registry blocked / offline | Remotion cannot be installed → `--engine ffmpeg` fallback; say so plainly. |
| transcribe: download failed | GitHub release assets blocked by the network allowlist | Ask the user for the script text (teleprompter/notes) → `transcribe.py audio --script script.txt` (CTC alignment still needs the ~350 MB model; if that is also blocked, timing falls back to `proportional` and is marked approximate). |
| transcribe: very slow / `NOT FINISHED` | first run downloads ~860 MB; Whisper turbo on 2 CPUs ≈ 1–1.5× real time | Run `transcribe.py --setup` first (resumable: rerun until `SETUP OK`). Long clips stop at `--budget` with `NOT FINISHED`: run the **same command** again, finished windows are kept. |
| Words wrong (brand names, English terms) | ASR spelling | Fix display with `glossary`. Never change the words themselves. |
| `SLOP:` lines after plan-edit | taste problems (see anti-slop.md) | Fix the plan and re-run until `CLEAN`. Each line names the element, the time and the fix. |
| `SLOP: ... covers the speaker's eyes/mouth` | text graphic over the tracked face | Move it above the head / below the chin, beside the face, or use a card/split layout for that beat. |
| `SLOP: ... words nobody said` | graphic text not in the transcript | Use the spoken words, or mark `"copy": "user"` / add to `user_copy` if the user wrote it. |
| `SLOP: monotony` / `SLOP: same look` | one element type everywhere / three identical titles | Give some beats another device (title behind the head, Cutout, a scene, a custom scene), vary `layout`/`stack`/`fx`/height. |
| `NOTE: CONCEPT?` / `NOTE: SIGNATURE?` | the plan is a template | SKILL.md "Direct it first": write the concept and add one moment only this video has. Skip only for "potong jeda + subtitle aja". |
| `LINT: unknown overlay type` | typo, or a custom component not listed in `custom_components` | Fix the name or list the .jsx file. |
| `LINT: <file>.jsx uses Math.random / the clock / network / React state` | non-deterministic custom component (frames render in parallel) | Use `rand(seed)`, `t`, local drawing only, a pure function of `t`. |
| stills fail with a bundling/JS error naming a custom file | syntax or runtime error in the component | Fix the line named in the error; if it fails twice, remove it from `custom_components`, use library elements and tell the user in one line. |
| Brand kit font ignored (`LINT: font ... is not bundled`) | the brand kit names a font the skill does not ship | Use one of the listed families; tell the user their font can't be embedded. |
| `RETAKE?` note | a phrase restarted within a few seconds | Read it; if it is a restart add the range to `remove_words`; deliberate repetition stays. |
| An "eh" was cut that belonged to a joke | automatic filler removal | Add its word index to `keep_words`. |
| `LINT: BRoll #n: file not found` | wrong path for the user's extra clip/photo | Use the uploaded file's real path (`ls /mnt/user-data/uploads`). |
| `music.file not found` | wrong path | Use the uploaded song's real path; otherwise the procedural bed is used. |
| `--script` on a long clip is slow | the whole script is aligned in one pass (log-probs are computed in 20 s chunks, so memory stays flat) | Fine up to several minutes; for very long footage prefer the Whisper path and fix spellings with `glossary`. |
| "text longer than audio" in alignment | `--script` text does not match the audio | Use Whisper output, or correct the script. |
| Render stops with `NOT FINISHED` (exit 2) | per-command time budget | Run the **same command again**; finished chunks are kept. Never use background processes. |
| Render very slow | 2 CPUs ≈ 3 frames/s at 1080×1920 with motion blur + grain (a 30 s edit = 1,800 frames ≈ 3–4 runs) | Use `--stills` for iteration, `--range` for previews, `--redo A-B` for revisions; lower `VY_CONCURRENCY` if memory is short. |
| A small revision re-renders everything | edit.json changed → all chunks invalid | Use `--redo A-B` with the seconds that changed (only those chunks re-render). Audio-only changes (music/SFX) never re-render frames. |
| Rotated / sideways output | display-matrix rotation not applied | Always render from `work/source.mp4` (normalised). inspect-media prints the rotation it applied. |
| VFR source drifts out of sync | iPhone/Android variable frame rate | Normalisation converts to constant 60 fps; captions/cuts use the same normalised frame timeline. |
| HDR (HLG/PQ) looks dark or washed out | HDR source (iPhone default) | inspect-media tone-maps HLG/PQ to SDR BT.709 with zscale + mobius (brightness matches the SDR look within ~1%); check stills. |
| Captions cover the mouth | face low in frame | Use the `talk` preset (default), or a caption zone `y`, or lower `camera.talk` scale in `style_overrides`. |
| Text in the platform UI area | y outside safe zone | plan-edit prints `LINT:` lines; move the element. |
| Fonts look like Times/Arial | font files not copied | render-video copies `assets/fonts` into `work/remotion/public/fonts`; check the folder, rerun. |
| Output file too large to download | long video at CRF 17 | Re-encode: `ffmpeg -i final.mp4 -c:v libx264 -crf 22 -preset veryfast -c:a copy smaller.mp4`. |
| Upload of footage rejected | file larger than the chat upload limit | Ask the user to trim or export at 1080p H.264 before uploading. |
| Face tracking found no face | profile view, screen recording, no person | Framing falls back to centre crop; use `wide` preset or explicit `fx/fy`. |

## Environment facts the scripts rely on
- Python ≥ 3.10 with numpy, scipy, pillow; `sherpa-onnx`, `onnxruntime`, `opencv-python-headless<5` are pip-installed on demand.
- Node ≥ 18 + npm for Remotion 4.0.538 (pinned). Remotion ships its own FFmpeg for frame encoding.
- System FFmpeg with libx264, libass (fallback captions), zscale (HDR) and ebur128/loudnorm.
- Models are cached in `~/.cache/aibijan-models` (override `AIBIJAN_MODELS_DIR`).

## Licensing notes
- Remotion is free for individuals and companies of up to 3 people; larger companies need a Remotion company licence.
- Whisper (MIT), Omnilingual-ASR CTC (Apache-2.0), sherpa-onnx (Apache-2.0), fonts (SIL OFL) — see README.

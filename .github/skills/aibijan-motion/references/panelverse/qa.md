# Checking a PANELVERSE film

## Stills (before the final — up to 3 rounds)
`node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --stills=<8–14 seconds: every beat + every transition>` → `out/<id>/stills/_sheet.jpg` (silent half-size frames). Look at every tile:
- **Story:** does each frame say what the VO says at that second? Is the turn visible?
- **Faces:** emotional beats show the face (not the back of the head, not hidden by a hand, balloon or lettering). Hands over a mouth, a bowed head over a phone are fine.
- **Anatomy & contact:** no arm through the body or head, no hand floating away from what it holds, feet on the floor / seat, nobody inside a table or another person.
- **Panels:** in a band panel the subject is centred and big enough; the panel border is clean; nothing important is cut by a gutter; during an expand the camera moves too.
- **Lettering:** fully inside the frame and the safe zone (x 64–920, y 230–1450), spelled right, not over a face.
- **Your elements:** visible, readable on a phone, clearly the thing the VO talks about.
- **Variety:** scroll the sheet — if two neighbouring beats look alike (same layout, same angle, same colours), change one.
Fix in `story.ts` / `elements.tsx` and re-run. Use the extra frames around a transition (±0.1 s) when unsure.

## Final
`node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --final` (foreground, longest timeout; re-run resumes). It renders 1080×1920 @ 60, masters to −14 LUFS and runs the **13 automated checks** (streams, duration, A/V length, white / black / frozen frames, blown highlights, loudness, VO sync ±30 ms, VO not cut, contact sheet covers the video, readable on a phone). Large near-white areas (a white cloud, page, screen or card filling > 12 % of the middle of the frame for > 0.5 s) trip the "blown highlights" check — use the `paper` tone for big fills; check suspicious stills with `ffmpeg -i f.png -vf "crop=iw*0.7:ih*0.6:iw*0.15:ih*0.2,lutyuv=y='if(gt(val,222),255,0)',signalstats" -f null -` (YAVG/255 must stay < 0.12).

Then gate 3: open `out/<id>/qa/REVIEW.md`, look at **every page** of `contact_1fps_*.jpg` and `shots_start_mid_end.jpg` as a viewer would, open the video around every transition, fill every row (PASS / FIX + what you saw) and set STATUS. Never call it PASS while a problem you saw is unfixed, and never excuse a bad frame as "just a transition".

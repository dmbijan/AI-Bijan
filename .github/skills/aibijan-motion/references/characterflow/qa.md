# QA — check before delivering

## Automated (run by `make.mjs` after every render; `node scripts/qa.mjs` by hand)
| Check | Pass when |
|---|---|
| video stream | H.264; final = 1080×1920 @ 60 fps |
| audio stream | AAC present |
| duration | = VO end + tail (±0.12 s) |
| audio length = video length | ±0.15 s |
| no white frames | no frame with mean luma > 225 |
| no blown-out highlights | no stretch ≥ 0.5 s with > 12 % of the middle of the picture burnt white (white bedding, a lamp, bloom) |
| no black gaps | no near-black run > 0.7 s outside the fades |
| no frozen picture | no identical frames for ≥ 2.5 s |
| loudness | final: −14 LUFS ±1.2, true peak ≤ −0.8 dBTP |
| VO sync | the VO is found in the film's audio at its offset (±30 ms, correlation > 0.6) |
| VO not cut | the VO ends before the video ends |
| contact sheet covers the whole video | one 1-fps tile per second, in pages of 96 s |
| readable on a phone | the middle of ≤ 15 % of the shots darker than mean luma 22, no practically black shot |

Results: `out/<id>/qa/qa.json`. Sheets: `out/<id>/qa/shots_start_mid_end.jpg` (every shot at start/middle/end, 9 per row) and `contact_1fps_001.jpg`, `_002.jpg`… (one tile per second for the whole video). Then **gate 3**: `out/<id>/qa/REVIEW.md` — tick every contact-sheet page, fill every row PASS / FIX with what you saw, set STATUS. Never call the video QA PASS while REVIEW.md is PENDING or has a FIX.

## Visual checklist (look at the sheets yourself — the automated checks cannot see these)
Judge every frame as a viewer would, not as the author: "it is a brief transition" is not a pass. A frame
fails when the face is cut by the frame edge or hidden, the camera sits inside/against furniture or
bedding, a limb pokes out at an odd angle, a bubble or caption is cut, or the frame shows mostly
blanket/wall/back of a head. Report what you saw honestly; never call a sheet perfect without checking
every frame.
1. Each shot shows the person who is acting, face visible (a head bowed over a phone is fine).
2. The action reads: a point/explain/wave shows the arm; sit/stand shows the body.
3. Nothing between lens and subject (tree, lamp post, furniture); camera not inside anything.
4. Thought bubbles fully in frame and readable; text not cut.
5. Captions readable and not over a face.
6. Two-person moments show both people when both act.
6b. Props in frame when used (the phone in the hand, the bag); after a place change, the new place reads at once.
7. No frozen or broken poses (arms through the body, feet sliding, phone floating).
8. The story reads with the sound off.

## Common fixes (edit story.json, rerun)
| Problem | Fix |
|---|---|
| arm action cropped in a close-up | `"shot": "medium"` or `"explain"` on that beat |
| important beat flashes by (< 1 s) | move its cue to an earlier word or drop the beat before it |
| same framing too often | vary the actions (the camera follows them) |
| bubble crowds the frame | shorter thought text (≤ 6 words) or an icon |
| beat on the wrong moment | cue a different word, or `"word#n"` for a repeated word |
| person faces away while acting toward someone | give `target`; standing people turn automatically |
| a gag does not land | remove it — comedy is optional |

Max 2 fix rounds; then deliver and tell the buyer honestly what is still imperfect.

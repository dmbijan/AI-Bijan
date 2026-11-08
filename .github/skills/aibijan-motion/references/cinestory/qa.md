# QA — three gates

Nothing is "QA PASS" until all three gates pass. Never claim a pass you did not run and look at.

## Gate 1 · Geometry preflight (automatic, every `make.mjs` run, before rendering)
`out/<id>/qa/preflight.json` — errors stop the build (`--force` renders anyway, only to look at a problem).
| check | error when |
|---|---|
| floating | an object hangs in the air touching nothing (forgot `on`, wrong y) |
| buried | an object is completely under the floor |
| objects-overlap / object-in-wall | parts of two objects / an object and a wall sink > 15 cm into each other (6–15 cm = warning) |
| mark-blocked | a mark the story uses is inside furniture or a wall |
| person-in-object | a body is inside furniture, a wall or a tree trunk for > 0.3 s |
| feet-off-ground | a walking person floats / sinks > 15 cm for > 0.5 s |
| people-overlap | two people inside each other for > 0.3 s |
| camera-in-solid / camera-underground | the lens is inside an object, wall, foliage or under the ground |
| subject-hidden | the person a shot is about is hidden behind something for > 75 % of the shot (40–75 % = warning) |
| camera-crowded | a surface right at the lens (a wall in a corridor, an open door leaf, a shelf edge) covers > 30 % of the picture for > 30 % of the shot (> 45 % → error) |
| face-turned-away | a close-up / medium shows only the side or back of the head (lens > 63° / 65° off where the person looks) for > 50 % of the shot; close-up > 70 % → error |
| subject-out-of-frame | the head of the person a framing is about is outside the frame (portrait or landscape) for > 25 % of the shot (> 60 % → error) |
| jump-cut / prop-continuity | warnings: a person jumps 0.1–1.2 m across a hard cut in the same place; a held light appears/vanishes |
Fix in the world / story (move a mark, an object, add space, force another shot side), not with `--force`.

## Gate 2 · Automated video checks (`--preview` / `--final`)
| check | pass when |
|---|---|
| video stream | H.264, 1080×1920 (portrait) or 1920×1080 (landscape), 60 fps (final); previews: the right aspect |
| audio stream | AAC present |
| duration / audio = video | planned length ±0.12 s / ±0.15 s |
| no white frames / no black gaps / no frozen picture | luma > 225 never; no near-black run > 0.7 s outside fades; no identical picture ≥ 2.5 s |
| loudness | −14 LUFS ±1.2, true peak ≤ −0.8 dBTP |
| VO sync / VO not cut | VO found at `vo.offset` ±30 ms; VO ends before the video (sections: sync from their start) |
| no blown-out highlights | never > 12 % of the middle of the picture burnt white (luma > 222) for 0.5 s — a lamp / bloom too close, a pale screen glowing (calibrated: a lamp bloom 32 %, a glowing QR-stand screen in an insert 14 %, the brightest day interior tested 9.5 %) |
| readable on a phone | ≤ 15 % of the shots sink below a mean luma of 22 at their middle, and no shot is practically black (90 % of its pixels at black level for its whole length) |
Writes `out/<id>/qa/shots_start_mid_end.jpg` (every shot at start/middle/end) and the 1-fps contact sheet for the
**whole** video in pages of 96 s: `contact_1fps_001.jpg`, `_002.jpg` … (tiles left→right, top→bottom, one per second;
stamped with the second when ffmpeg has `drawtext`); the check "contact sheet covers the whole video" fails if a page is missing.

## Gate 3 · Visual review (you, every time)
`make.mjs` writes `out/<id>/qa/REVIEW.md` with **STATUS: PENDING VISUAL REVIEW**, a checklist and every
preflight warning. Look at the shot sheet and every contact page (and stills or the video where unsure), fill every row with
PASS / FIX + what you saw, tick each preflight warning as harmless or fix it. Set `STATUS: PASS` only
when no FIX is left; otherwise fix and rebuild. After 2 fix rounds deliver honestly with the list of
what is still imperfect.

Checklist:
- Every shot shows its subject: the face in close-ups, the hands AND the object in inserts, the thing in long shots.
- Nothing blocks the lens (a post, a wall, a crown of leaves, a hand for the whole shot).
- No clipping: hands on surfaces not inside them, bodies not in furniture, objects resting on something.
- Contact is plausible: feet on the ground (also on slopes), hands on handles when pushing, the carried thing in the hand.
- Objects read as what the script says (a stranger would name them) — a "sack" that looks like a pumpkin is a FIX.
- The place reads as the script's place at first sight (a market has many stalls, goods, people-space and buildings around; a forest is dense; a hospital corridor has doors, signs, handrails) — empty halls or bare ground to the horizon are a FIX.
- Two people are told apart at a glance (different top colours).
- Night is moody but readable; practical lights visible; nothing burns out to white.
- Continuity: clothes, positions across cuts, objects stay where they were moved, lights stay switched.
- VO sync: actions land on their words; the pauses of the VO are kept (cuts in pauses).
- Story logic: nothing is seen before the story reveals it, and nothing stays after the story says it left.
- No head / back of a head covers more than about a quarter of a frame (except the subject's own close-up; an OTS
  shoulder edge is fine).
- Portrait: faces and the key object read at phone size; every text (captions, tags, cards, titles) sits inside the
  safe area and never over a face; the captions match the words.
- No long stretch shows one graphic / the same view; the explanation uses the story's own world.

## Common fixes
| you see | fix |
|---|---|
| a post / shelf across a face | another `side`, or move the mark 0.5 m; or `"shot": "medium"` |
| an insert shows only the object or only a head | the touch point too far from the person (anchor ≤ 0.3 m from the edge), or force `"shot": "medium"` |
| the camera in leaves / a green frame | the tree is too close to the action — move it or the mark |
| a walk is cut short (⚠) | move the next beat later or use a closer mark |
| something important is a tiny dot | a `show` beat, or `raise`/`peer` with `target`, or a closer object |
| `face-turned-away` / only the hair shows | the person faces a wall, window or thing too close for a camera in front: put the mark ≥ 1.5 m from it, turn them towards the room (look at it at an angle), or show what they see over the shoulder (`ots` / `reveal`) and the face on another beat |
| `camera-crowded` / a wall or door leaf fills the side of the frame | give the people room (≥ 1 m), move the mark off the wall, put the cast in a cast-less scene, or force another `side` / shot |
| an interaction only from behind | the handling mark is in a corner: move the object or the mark so one side is free |
| `⚠ pacing` | add reaction / gesture beats on the words; replace repeated studio / graphic shots with the story's places and objects |
| too dark | a practical near the action (`lantern`, `bulb`, `panel`), `dusk` instead of `night` |

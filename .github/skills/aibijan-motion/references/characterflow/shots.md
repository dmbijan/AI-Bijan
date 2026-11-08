# Camera shot library (automatic)

Shots are chosen per beat from what the beat needs; you normally never pick one. Every camera is
kept on the open side of the set, inside its bounds, never inside a tree/furniture, and slides
forward past anything that would block the subject. The side of the action line is decided once per
shot, so a camera never flips mid-shot. All shots frame for a 9:16 portrait picture.

| Shot | Frames | Chosen for |
|---|---|---|
| `establish` | high wide crane-down onto the place | the opening on campus |
| `wide` | whole body + surroundings | the opening in the bedroom; bed actions |
| `track` | walking person, moving with them | `walk` |
| `mediumWide` | knees up, room for arms | `proud`, `stop`, `point` alt, `sitDown`/`standUp` alt |
| `medium` | waist up | talk, laugh, wave, phone, bag, shrug, overwhelmed, calm |
| `explain` | waist up from a wider angle so the arm reads | explain, point |
| `closeUp` | face and shoulders | sad, facepalm, lookBack, nod, shake, smile, shy |
| `reaction` | close-up with a slow push-in | happy, surprised, embarrassed |
| `think` | chest up with headroom for the bubble | think, confused, any beat with a big thought |
| `sitStand` | full body from the side | sitDown, standUp (never merged away) |
| `turn` | orbits slightly while someone turns | turn |
| `ots` | over the shoulder of the noticer toward the other | notice (2 people) |
| `pathDown` | down the path from behind one toward the other far away | notice at distance, walking toward each other |
| `twoShot` | both people side-on | pass, meet, highFive, both acting at once |
| `boardWide` | the people + the pop-up sign | while a `board` is up |
| `bedWide` | from the foot of the bed, high: the whole person in bed | lying, `sitUp`, `lieDown`, lamp (camera does not swing while they move) |
| `bedClose` | face and shoulders from above the foot of the bed | lying + close actions (sad, smile, …) |
| `bedThink` | from the side of the bed with headroom for the bubble | lying + `think`/`confused`/any thought |

## Rules the director applies
0. Someone lying in bed is always framed with the bed shots (the head's facing is meaningless while lying).
1. Both people act at the same moment → `twoShot` (or split close-ups for `eyeContact`).
2. The same framing twice in a row → the action's alternative framing (variety).
3. A `pass` is shown from 1.4 s before it happens.
4. A beat longer than ~4.5 s gets a second angle.
5. Shots shorter than 1 s are merged into the previous one (close-ups and sit/stand excepted).
6. With two people, a one-person shot swings around its subject (up to 60°) when the other person would block the lens or the gesture.
7. Captions move to the top when a face would sit under them.

## Overriding
`"shot": "closeUp"` on a beat forces that framing for the beat. Use it only to fix what you saw in
the stills (e.g. an arm action cropped in a close-up → `"shot": "medium"`; a small sitting person →
`"shot": "medium"`). Shot names: `establish wide closeUp medium mediumWide twoShot ots track pathDown reaction explain think sitStand turn boardWide bedWide bedClose bedThink`.

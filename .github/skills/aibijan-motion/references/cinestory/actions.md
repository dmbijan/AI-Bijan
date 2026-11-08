# Acting library

`"do": <action>` on a beat. An action lasts until that person's next beat; poses blend over ≈0.45 s.
Everyone breathes, blinks, shifts weight and moves their eyes on their own. `target` (POI / mark /
cast id) aims head and eyes. Expressions default per action (override with `face`).

**Posture.** People are standing, sitting (on a seat mark), lying (on a bed mark) or sitting up in bed.
Start a scene in a posture with `scene.cast.<id>.do` (`stand` / `sit` / `lie`). Actions marked
*stand-only* need the person standing; the validator says what to do first.

| action | posture | what it does | auto camera | auto sound |
|---|---|---|---|---|
| `stand` | any | idle, looks toward `target` if given | wide | — |
| `look` | any | head + eyes to `target`; if it is far behind, the body turns too | ots (far target) / medium | — |
| `turn` | stand-only | turns the whole body to face `target` (or the mark's facing) | medium | rustle |
| `walk` | stand-only | walks to mark `to` in a straight line, arrives on its own timing, settles into the mark's facing (or toward `target`) | track / lead | footsteps |
| `run` | stand-only | runs to `to` | track | running steps |
| `startle` | any | jolts back, hands open, snaps to `target` | cu | hit |
| `freeze` | any | fear stance: shallow fast breath, trembling hands, staring at `target` | cu | heartbeat |
| `peer` | any | leans in, head forward, squints toward `target` | ots / medium | — |
| `point` | any | right arm points at `target` | ots / medium-wide | rustle |
| `reach` | any | right hand reaches for `target` (a switch, a book…) and returns | medium-wide | rustle |
| `wave` | stand/sit | waves | medium | — |
| `phone` | any | lifts the phone and reads it (needs `"prop": "phone"`; screen text = `phoneText`) | insert | rustle |
| `binoculars` | any | raises binoculars to the eyes toward `target` (needs `"prop": "binoculars"`) | ots → long | rustle |
| `raise` | any | raises the lantern / flashlight; with `target` the body turns and the beam aims at it (needs that prop) | ots (with target) / medium-wide | rustle |
| `think` | any | hand raised under the jaw for ~2 s (mouth stays clear), head tilt (again within 12 s, or inside a close-up: head only) | medium (portrait) / cu | — |
| `nod` / `shake` | any | yes / no with the head | medium / cu | — |
| `sigh` | any | shoulders drop, long breath | medium | breath |
| `talk` | any | speaks: mouth moves with the VO loudness, hand gestures (use only when this person is the one speaking) | medium | — |
| `shiver` | any | arms wrapped, cold trembling | medium | breath |
| `sit` | stand → sit | sits on the seat mark `to` (walks there first if needed) | medium-wide | chair creak |
| `standUp` | sit → stand | stands up from the seat | medium-wide | creak |
| `write` | sit-only | writes at the desk (pen in hand) | insert | pen scribble |
| `lie` | lie | lying in bed, eyes open | high | — |
| `sleep` | lie | asleep, slow breath | high | — |
| `wake` | lie | eyes open slowly, glances toward `target` | high | breath |
| `sitUp` | lie → bed | sits up in bed (legs stay on the bed) | medium-wide | bed creak + breath |

| `push` | stand-only | pushes an object with handles to mark `to` (`obj`) — [interactions.md](interactions.md) | profile / track | rolling |
| `pickUp` / `putDown` | any | takes `obj` in the right hand / puts what it carries on `target` | medium-wide | rustle |
| `give` / `receive` | any | hands what it carries to the `target` person (who receives automatically) | two-shot | — |
| `open` / `close` | stand | opens / closes a door (`obj`: the door id) | medium-wide | door |
| `kneel` | stand | kneels and reaches to a low `target` point | medium-wide | rustle |

## Walking
- Speed is natural (≈1.15 m/s walk, 3 m/s run); if the next beat comes too early the walk speeds up a
  little, else it is cut short with a ⚠ warning — give walks room (3–5 s for 3–5 m).
- In dynamic worlds people walk around furniture, through doorways and over terrain (navigation grid); in the built-in worlds straight lines between marks.
- Nobody walks from one place to another: cut to the next place with them already at a mark (a place can span several rooms of a dynamic world — then they can).

## Faces
`neutral calm alert fear panic squint read sleep drowsy smile laugh sad think uneasy serious relief surprised curious`

# Camera library (automatic)

Every beat that changes what we should see (`do`, `show`, `shot`, `hit`) gets a camera. Cuts land in
the VO pause just before the beat's word, so actions start on screen exactly on their word. All shots
are computed from the people's real positions every frame (no pre-baked paths), with their own framings for portrait 9:16 (default) and landscape 16:9 — see [portrait.md](portrait.md).

Automatic safety: cameras stay inside the place's camera space, are pushed out of walls/furniture, and
framings around a person swing around them (once per shot, never mid-shot) when the ideal spot is
blocked. Depth of field focuses on the shot's subject. Hand-held drift follows the mood (none in
`science`, strongest in `tense`). Smoothing never reaches across a scene cut.

| shot | frames | automatic for |
|---|---|---|
| `establish` | high wide crane-in onto the place | the first shot in a place the film has not shown yet (exterior) |
| `drone` | higher, wider establishing move | — (force it for a grand opening / explanation overview) |
| `wide` | whole person + surroundings | first shot in an interior seen for the first time; `stand` |
| `mediumWide` | knees up | sit, standUp, reach, raise, sitUp, point (near target) |
| `medium` | waist up | think, talk, nod, shake, turn, shiver, look (near target) |
| `cu` | face and shoulders, slow push | startle, freeze, every second talk/think |
| `ecu` | eyes | — (force for a beat of realisation) |
| `ots` | over the shoulder toward `target` | look / peer / binoculars / point at a far target |
| `pov` | from the eyes (person hidden), hand-held | — (force for "what he sees") |
| `long` | telephoto onto the far `target` / `show` from beside the person (from an interior with a window: from just outside the window) | second angle of a far look; `show` of a far POI |
| `track` | behind a walking person | walk, run |
| `lead` | in front of a walking person | every second walk |
| `profile` | side view | — |
| `low` / `high` | low hero angle / from above | lie, sleep, wake (`high`) |
| `insert` | hands / prop close-up (`target` within reach, else the hand); a far `target` becomes a `long` | phone, write |
| `orbit` | slow move around the subject or POI | studio scenes; scenes without people |
| `push` / `pull` | dolly in / pull back to reveal | — (force for tension / endings) |
| `reveal` | starts on the person, pans to `target` | `show` of a POI that is near (≤ 12 m) |
| `map` | high top-down over the place | — (force for explanations with callouts) |
| `twoShot` | two people side-on | — |

## Rules the director applies
1. First shot in a place seen for the first time: `establish` (exterior) / `wide` (interior) / `orbit` (studio).
2. The same framing twice in a row → its alternative (variety), unless forced.
3. Beats closer than 0.9 s play inside the previous shot (unless they have `shot` or `hit`).
4. A shot longer than 5.6 s gets a second angle in a VO pause near its middle (ots → long).
5. A shot shorter than 0.85 s (not forced) is folded into the shot before it.
6. A scene with nobody in it uses establish / orbit / long on the shown POI.

## Overriding
`"shot": "cu"` or `"shot": { "type": "insert", "target": "logbook" }` on a beat. Use it to fix what you
saw in the stills, or for a deliberate cinematic choice (a `pov` scare, a `pull` ending, a `map` explanation).

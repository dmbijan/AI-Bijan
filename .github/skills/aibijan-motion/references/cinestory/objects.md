# Objects (object.json)

Order of preference: **reuse** (`assets/objects/`, `library/objects/` — 38 ready objects for homes, schools,
offices, shops and parks, listed in [worlds.md](worlds.md) —, built-ins) → **build from
primitives** (new `assets/objects/<id>.json`, reusable in every later film) → **report** what cannot be
built well. Never silently swap an important story object for a wrong shape (a "cat" made of boxes, a
"motorbike" that is a cube): tell the user, and either keep it off screen / implied by sound, or use a
stylised stand-in and say so in one line.

What primitives do well: furniture, containers, tools, signs, stalls, carts, medical equipment, simple
vehicles' *parts* (a wheelchair, a trolley), tents, lamps, simple stylised animals sitting still (an owl).
What they do badly (report it): animals in motion, detailed vehicles (cars, motorbikes, bicycles), musical
instruments with strings, statues, anything recognisable by an organic silhouette.

Examples (all render well): [examples/perpustakaan/objects/](examples/perpustakaan/objects/) —
`bookshelf`, `reading-table`, `wooden-chair`, `park-bench`.

## Conventions
- Metres, y up, the object's **origin on the floor at its centre**; its **front faces +z** (a chair you
  sit on facing +z, a counter whose customer side is +z). Rotate it in the world with `yaw`.
- Real dimensions (look up typical sizes). A person is 1.7 m; the object sheet puts a 1.8 m person next
  to every object.
- 5–30 parts. Use `repeat` and `mirror` instead of copying parts.

## Shape hints (what makes common things read right)
| thing | build it as |
|---|---|
| sack (rice, flour) | slightly tapered `cyl` or rounded `box` ~0.4×0.6×0.3, a narrower tied neck (`cyl`) + a `text` label — never a ball or a pot shape |
| basket | `lathe` bowl with a lip `torus` + an arched handle `torus` (arc π), goods as a `pile` on top |
| plastic bag | `lathe` body narrowing to two loop handles (`torus`), translucent colour |
| crate | `box` slats via `repeat` with gaps, not one solid block |
| wheelchair | two big `torus` wheels + small castors, `box` seat + back, `rod` frame, push handles; anchors `seat`, `push`, `brake` |
| stall / counter | counter `box` + 4 `rod` posts + roof board; `top` and `behind` anchors |
| door-side sign, poster | `text` on a thin `box` |
| animal sitting still (owl, cat, rabbit) | 3–6 squashed `sphere`s + ears/tail `cone`s; only as an agreed stand-in, never animated |
Check the silhouette: a stranger must name it from the object sheet alone.

## File
```json
{
  "id": "wooden-chair",
  "doc": "simple wooden chair, sits facing +z (backrest at −z)",
  "anchors": { "seat": [0, 0.46, 0.03], "front": [0, 0, 0.6] },
  "states": { },
  "parts": [
    { "shape": "box", "size": [0.44, 0.04, 0.42], "at": [0, 0.45, 0], "mat": "wood:#8a6440", "round": 0.01 },
    { "shape": "box", "size": [0.04, 0.45, 0.04], "at": [0.19, 0.225, 0.18], "mat": "wood:#6a4a2e", "mirror": "x" },
    { "shape": "box", "size": [0.04, 0.92, 0.04], "at": [0.19, 0.46, -0.19], "mat": "wood:#6a4a2e", "mirror": "x" },
    { "shape": "box", "size": [0.4, 0.07, 0.025], "at": [0, 0.82, -0.19], "mat": "wood:#8a6440", "repeat": { "n": 2, "step": [0, -0.16, 0] } }
  ]
}
```
File name = `id`. Optional top-level: `hand: true` (carried in one hand: bags, money, books, baskets),
`walkable: true` (rugs, mats), `bounds` (override).

## Shapes
| shape | size fields | notes |
|---|---|---|
| `box` | `size: [w, h, d]`, `round?` | `at` is its centre |
| `cyl` | `r` (top), `r2?` (bottom), `h` | upright; tilt with `rot` |
| `cone` | `r`, `h` | |
| `sphere` | `r` | squash with `scale` |
| `capsule` | `r`, `h` | |
| `torus` | `R` ring, `r` tube, `arc?` (radians) | handles, wheels, rings |
| `rod` | `from: [x,y,z]`, `to: [x,y,z]`, `r` | a tube between two points: legs, poles, frames, spokes |
| `lathe` | `profile: [[radius, y], …]` | turned shapes: bottles, bowls, bags, vases, lamp shades |
| `plane` | `size: [w, h]` | thin panels |
| `text` | `size: [w, h]`, `text`, `ink?`, `bg?` | signs and labels (faces +z) |
| `pile` | `size: [w, h, d]`, `count`, `r`, `colors`, `seed?` | heaps of small round goods (fruit, chillies, coins, embers) |

Every part: `at`, `rot` (degrees [x, y, z]), `scale`, `mat`, and:
- `mirror: "x" | "z"` — also add the mirrored copy (left/right legs, wheels).
- `repeat: { n, step?: [dx,dy,dz], stepRot?: [deg…], around?: true }` — n copies moved by `step`; with
  `around: true` the copies swing around the vertical axis by `stepRot[1]` (radial legs, spokes, stones).
- `when: "<state>"` — only shown while that state > 0.5 (an open lid, a lit sign).
- `glowState: "<state>"` — glow follows a state 0..1 (screens, owl eyes, a tent's light).

## Materials (`mat`)
Presets: `wood:#hex` `darkwood` `steel` `chrome` `aluminium` `rubber` `glass` `ceramic:#hex` `paper:#hex`
`bark` `stone:#hex` `plastic:#hex` `paint:#hex` `fabric:#hex` / `cloth:#hex` (woven) `leaf:#hex`
`metal:#hex` `emissive:#hex` `vinyl:#hex` `tiles:#hex` `planks:#hex` `concrete:#hex` `dirt:#hex`
`grass:#hex` `asphalt`. Custom: `{ "color", "roughness", "metal", "emissive", "glow", "opacity", "texture": "weave"|"planks"|"tiles"|"noise" }`.
White things (paper, white fur, white plastic) must be off-white (`#d8d2c6`, `paper:#ddd6c6`) or they
burn out to a glowing blob under lamps and flashlights.

## Anchors (named points, object space)
| anchor | used by |
|---|---|
| `seat` | `sit` (`"to": "<id>:seat"`); y = seat height |
| `bed` + `head` | a bed: `bed` = mattress top centre, `head` = pillow end → auto mark `<id>:bed` for `lie` / `sleep` / `wake` / `sitUp` (a patient, a sleeper — not a blanket mound) |
| `front` | auto mark `<id>:front` (where someone stands to use it) |
| `top` | `putDown` target and `"on": "<id>:top"` (surface height) |
| `behind` | auto mark `<id>:behind` (the seller / clerk side, facing over it) |
| `push` | the handle bar centre → `push` action, auto mark `<id>:push` |
| `grab` | where the hand holds a carried object |
| anything else | a target: `"target": "<id>:papers"`, `"kneel"` at `"<id>:brake"` |
Put touch points **within reach**: a writing/working point ≤ 0.3 m from the edge the person is at.

## Check it — always
`node scripts/objects.mjs --sheet <id>[,<id>…]` → `out/look/objects/_sheet.jpg`: each object from two
angles beside a 1.8 m person. Read it like a stranger: is it obviously a wheelchair / stall / tent? Right
size? Parts floating or sunk into each other? Fix, re-run. Only then use it in the world.

## Built-ins (code, not JSON)
`tree-pine` `tree-broad` `tree-palm` (palm fronds look rough) `bush` `fern` `rock` `log` `grass-tuft`.

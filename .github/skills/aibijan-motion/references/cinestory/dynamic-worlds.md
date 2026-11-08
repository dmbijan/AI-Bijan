# Dynamic worlds (world.json)

When no built-in world (`coast`, `kos`) fits the story, you **write the location as data**:
`stories/<id>/<name>.world.json` (or `worlds/<name>.world.json` to reuse it in other films). The engine
builds the 3D set, terrain, light rig, camera space, collision, walk navigation and the automatic marks
and POIs from it — no code. `story.json` then says `"world": "<name>"`.

Look for something reusable first: `node scripts/worlds.mjs` lists the ready-made worlds (`rumah`, `sekolah`,
`kantor`, `minimarket`, `perpustakaan` — [worlds.md](worlds.md)) and every `stories/*/*.world.json` you made
before; the analyzer lists them too. Use one as it is, or adapt a copy, rather than starting from zero; a new
world is for a place none of them is (a hospital, a market, a forest…). A new world you will need again goes
into `worlds/` so the next film finds it.

Complete working example: [examples/perpustakaan/perpustakaan.world.json](examples/perpustakaan/perpustakaan.world.json)
(a reading room inside a park: interior + exterior, doors, windows, paths, trees, lamp posts).

## Coordinates and scale
- Metres. **y is up**, the floor is y = 0. `yaw` in radians: 0 faces **+z (south)**, π/2 faces +x (east),
  π faces −z (north), −π/2 faces −x (west).
- Walls: `north` = the −z side of a room, `south` = +z, `west` = −x, `east` = +x.
- Real sizes: door 0.9 × 2.1, ceiling 2.8–3.4, corridor 2.2–3 wide, desk top 0.75, counter 0.9–1.1,
  person 1.6–1.8. A room that is too small makes every camera fail; give interiors ≥ 3.5 m clear width
  where people act.

## Top level
| field | meaning |
|---|---|
| `id`, `doc` | name, one-line description (doc helps the analyzer find it for reuse) |
| `times` | which of `night` `dusk` `dawn` `day` the world supports |
| `rooms` | interior spaces (see below) — every room is a closed box with walls, floor, ceiling |
| `ground` | `{ size, material?, hills?, seed?, y? }` — terrain for exteriors (`hills` 0–0.5 = gentle–rolling) |
| `paths` | `[{ id?, points: [[x,z],…], width?, material? }]` — tracks / pavements drawn on the terrain |
| `buildings` | `[{ rect: [x0,z0,x1,z1], height, color?, sign?, windows?, y? }]` — closed façades seen from outside (not enterable) |
| `objects` | placed objects (library ids, built-ins or your own — [objects.md](objects.md)) |
| `scatter` | many copies of a built-in / object over an area (forests, bushes, crowd-less clutter) |
| `lights` | light fixtures (each lights the scene AND shows its lamp) |
| `props` | set props the story can change: `{ "<name>": { default, doc, sfx?: {up?, down?} } }` |
| `places` | the stageable places (what a scene names) with marks |
| `fog` | 0–1 atmosphere (0.3–0.5 for a night forest, 0–0.2 indoors) |

## rooms
```json
{ "id": "lorong", "rect": [-1.5, -14, 1.5, 4], "height": 3.0,
  "floor": "vinyl:#cfd6d4", "walls": "paint:#e9eeec", "wainscot": "#7fb6b0", "handrail": "#dfe3e6",
  "ceiling": "paint:#f2f4f4", "open": [],
  "openings": [ { "wall": "north", "at": 0, "type": "double-door", "sign": "ICU", "glass": true },
                { "wall": "east", "at": -4, "type": "door", "id": "pintu-gudang" },
                { "wall": "west", "at": -8, "type": "window", "w": 1.6, "h": 1.2, "sill": 0.9 } ] }
```
- `rect` = floor rectangle [x0, z0, x1, z1]. Rooms that share a wall line and a doorway at the same spot
  connect (people can walk through the door; cameras of an interior place stay inside its rooms).
- `openings[].at` = position along that wall: world **x** for north/south walls, world **z** for west/east.
  Types `door` `double-door` `window` `arch`. `w`/`h`/`sill` override the size. `sign` puts a sign above it.
- Every door gets an automatic **set prop with the door's id** (0 shut, 1 open, with creak / thud sound):
  `"set": { "pintu-gudang": 1 }` or the `open` / `close` actions. Ids: your `id`, else `pintu-<sign>`,
  `jendela-<n>`, `lengkung-<n>` (the plan prints them in errors).
- `open: ["north", …]` leaves that side without a wall (market halls, verandas, carports).
- Materials: `paint:#hex`, `vinyl:#hex`, `tiles:#hex`, `planks:#hex`, `concrete:#hex`, `stone:#hex`,
  `wood:#hex`, `metal:#hex`, `dirt:#hex`, `grass:#hex`, `asphalt` (full list in [objects.md](objects.md)).
- A room seen from an exterior place is a solid building for cameras (they never end up inside it).

## objects
`{ "use": "<object id>", "id"?: "<name>", "at": [x, z] | [x, y, z], "yaw"?, "scale"?, "on"?: "<id>:top", "states"?: {…} }`
- `[x, z]` stands it on the floor/terrain. `[x, y, z]` outdoors = **height above the ground** at x,z
  (a bird on a branch, a sign on a pole); inside rooms y is absolute.
- `on: "meja-1:top"` rests it on another object's anchor (goods on a counter, a basket on a scale).
- Give an `id` to everything the story will name (targets, `show`, pushed/carried objects).
- Built-in organic objects: `tree-pine`, `tree-broad`, `tree-palm`, `bush`, `fern`, `rock`, `log`, `grass-tuft`.

## scatter
`{ "use": "tree-pine", "count": 120, "area": [x0, z0, x1, z1], "minDist"?: 3, "scale"?: [0.8, 1.3], "seed"?: 1, "avoid"?: ["paths", "rooms", "objects"] }`
- Instanced: hundreds of trees cost little. Trees keep only their trunk off paths, so crowns overhang
  a forest track. Layer two scatters: a dense band along the action, a sparser one far out to the horizon.
- Ferns and grass tufts are walkable (they never block people or cameras).

## lights
`{ "type": …, "at": [x, y, z], "id"?, "color"?, "i"?, "prop"?, "flicker"?, "to"?, "target"? }`
| type | what | `at` |
|---|---|---|
| `panel` / `tube` | ceiling panel / fluorescent tube (`flicker` for a broken one) | on the ceiling (y ≈ room height − 0.05) |
| `bulb` | bare warm bulb | where it hangs |
| `lamp-post` | street / park lamp on a pole | its foot `[x, 0, z]` (or the head at y ≈ 4.2) |
| `string` | string of lights from `at` to `to` | both ends |
| `spot` | spotlight toward `target` | the lamp |
| `exit-sign` | green exit sign | above a door |
| `lantern` / `fire` | lantern / campfire flame (warm, flickering) — not a big fire (see limits.md) | on the ground or a table |
| `screen` | glowing monitor / TV light | the screen |
- `prop` ties the fixture to a set prop (several fixtures can share one) → `"set": { "lampu-taman": 1 }`
  switches them (fades with `fade`). Declare that prop in `props` with its `default`.
- Night needs motivated light: fixtures where the action is, a moon key comes automatically. The engine
  uses the nearest 5 fixtures per place + soft fill.

## places
```json
"bangsal": {
  "doc": "the night ward corridor, nurse station on the east side",
  "kind": "interior", "rooms": ["lorong", "pos-jaga"],
  "marks": { "dekat-icu": { "at": [0.4, -11.5], "yaw": 3.1416, "doc": "in front of the ICU doors" } },
  "view": "pintu-icu", "ambience": "hospital", "surface": "tile"
}
```
| field | meaning |
|---|---|
| `kind` | `interior` (cameras stay inside its `rooms`) or `exterior` (cameras inside `area` ± margin, out of buildings and tree crowns) |
| `rooms` / `area` | what the place covers: room ids, or `[x0, z0, x1, z1]` for exteriors |
| `marks` | where people stand: `{ at: [x, z], yaw?, seat?, bed?: { head: [x, z], y }, doc }` (`bed` = lie there: pillow end + mattress top height; easier: a bed object with `bed`/`head` anchors gives `<id>:bed`). Put marks on open floor, ≥ 0.4 m from furniture and walls, ≤ 0.8 m in front of anything they touch (doors, counters). |
| `view` | default POI for wides (the most important thing in the place) |
| `ambience` | `room` `hall` `outdoor` `hospital` `market` `forest` (or the coast ones) |
| `surface` | footstep sound: `tile` `wood` `stone` `grass` `sand` `metal` `carpet` |
| `establish` | optional `[cx, cy, cz, radius, height, yaw]` — where establishing shots look from |

**Automatic marks** for every object in a place: `<id>:front` (facing it), and when the object has those
anchors `<id>:seat` (sit there), `<id>:bed` (lie there: `lie`/`sleep`/`wake`/`sitUp`), `<id>:push` (behind
the handles), `<id>:behind` (the seller's side of a counter).

**Ground + rooms**: a `ground` sinks automatically under rooms (no flicker); a room's floor is its own. **Automatic POIs**: every object id, every opening id, every fixture id, and any
`<object>:<anchor>` (e.g. `meja:papers`, `kursi-roda:brake`).

## Workflow
1. Sketch the floor plan on paper-like coordinates: rooms, doors, where each beat happens, where the
   camera has room. 2. Write the JSON. 3. Build stills of a minimal story (one scene per place, a `wide`
   and an `establish`) and look. 4. Fix and repeat. 5. Write the real story.

## Design rules (what made the tests work)
- **Coherent, not decorative**: every object where the script needs it, at real size, against walls or
  in sensible rows; doors lead somewhere; no 2D backdrops — distant buildings / trees are real geometry.
- Leave camera room: 2.5–4 m of open space around where people act (cameras retreat to 55 % distance
  when blocked, then pick another side, then a vantage mark).
- Light the story points: a bright practical near the key action, darker surroundings.
- Night forest: dark ground, fog 0.4–0.5, two tree scatters, a path, warm practicals (fire/lantern) as
  the goal, the character's flashlight.
- Market / street: open-sided room (`open`) for a hall with MANY stalls in rows (8+), goods on every stall,
  string lights, and `buildings` (with signs) around the square so every open side looks onto a street —
  never bare ground to the horizon (preflight warns "empty-surroundings").
- Density sells the place: a corridor needs doors along it, a market needs crowding stalls and crates,
  a forest 150+ trees. One prop per story beat is not enough.
- Hospital / office / school: rooms in a row along a corridor, doors with signs, panels/tubes, handrails.

> Besides these built-in worlds the project ships **ready-made worlds** (below) and you can build any location as
> data — see [dynamic-worlds.md](dynamic-worlds.md).

## Ready-made worlds (`worlds/*.world.json`) — reuse before building

Finished, tested sets in every CINÉSTORY project. `"world": "<id>"` in story.json; `node scripts/worlds.mjs`
lists them, `node scripts/worlds.mjs <id>` prints every place with its marks, seats / beds, objects (targets),
doors and set props. Adapt a copy (`stories/<id>/<name>.world.json`) when the story needs a different layout.

| world | places | times | for |
|---|---|---|---|
| `rumah` | `ruang-tamu` (sofa, TV, family photo, front door), `dapur` (stove, fridge, dining table), `kamar` (bed, wardrobe, study table), `lorong` (long back corridor, old wardrobe at its end, bathroom + back door), `teras` (terrace, yard, gate, the lane) | night, day, dusk, dawn | family, home, a knock at the door, a parcel, night at home, horror at home (the wardrobe in the corridor, the bathroom door) |
| `sekolah` | `kelas` (blackboard, teacher's desk, 12 desks, KELAS 8B door), `koridor` (veranda corridor, mading, lockers), `lapangan` (field, flagpole, class blocks) | day, dusk, night | school days, exams, a teacher, bullying (words, looks, the aftermath — no fights), school horror |
| `kantor` | `kantor` (open-plan floor, 23 desks), `pojok-printer` (main desk, printer, CCTV monitor, meeting-room door), `cctv` (fixed CCTV view) | night, day, dusk | work, overtime, a boss, office mystery / horror |
| `minimarket` | `toko` (checkout + QRIS, shelves, glass door), `jalan` (pavement, the street, shops across) | day, dusk, night | shopping, money, prices, a cashier, a street at night |
| `perpustakaan` | `ruang-baca` (shelves, reading tables), `taman` (park path, benches, lamps) | day, dusk, night | reading, studying, a quiet park |

Objects in the library (`library/objects/`, every film can use them): furniture for homes (`sofa`,
`coffee-table`, `tv-cabinet`, `dining-table`, `kitchen-counter`, `fridge`, `bed`, `wardrobe`, `standing-fan`,
`shoe-rack`, `wall-photo`), schools (`school-desk`, `school-chair`, `blackboard`, `locker-row`, `notice-board`,
`flagpole`), offices (`office-desk`, `office-chair`, `desk-monitor`, `laptop`, `office-printer`, `filing-cabinet`,
`meeting-table`, `whiteboard`, `wall-clock`, `cctv-camera`, `cctv-monitor`, `potted-plant`), shops (`counter`,
`store-shelf`, `qris`, `cash`) and the park library (`bookshelf`, `reading-table`, `wooden-chair`, `park-bench`).

# Worlds

A story uses ONE world. A world is a consistent set of places sharing one coordinate plan, so a
location looks the same in every scene. Places are cut between (people do not walk from one place to
another; start the next scene with them already there). Inside a place people walk in straight lines
between marks.

- **mark** — where someone stands / sits / lies (`scene.cast.<id>.at`, `beat.to`).
- **POI** — something to look at, point at, show, or label (`target`, `show`, `ui.callout.poi`). A mark
  name or a cast id also works as a target.
- **set prop** — something that changes over the film (`scene.set`, `beat.set` with `fade` seconds).
- **times** — the time of day per scene (`scene.time`), which sets sky, sun/moon, practical lights.

Choosing: lighthouse, sea, fishermen, keeper, storm (waves, wind and thunder by sound; no rain), coast, island, boat → `coast`. Boarding house,
bedroom, corridor at night, roommates, "kos", dorm, front yard/terrace → `kos`. Brain, perception,
memory, "why do we…" explanations → the `mind` studio (any world).

## coast

a rocky headland with a red-and-white lighthouse, the keeper's cottage and the open sea (Indonesian coast)

**times:** `night` · `dusk` · `dawn` · `day`

**set props** (numbers 0…1 unless noted; change them per scene or beat with `set`):

| prop | default | what |
|---|---|---|
| `beam` | 1 | lighthouse lamp turning, beams sweep the sea (0 off, 1 on) |
| `lamp` | 1 | oil lamp in the cottage + porch light (0 off, 1 on) |
| `seaLight` | 0 | the light far out at sea (0 none, 1 bright); it bobs and seems to blink |
| `boat` | 0 | the fishing boat under the sea light is revealed and lit (0 hidden in the dark, 1 clearly visible) |
| `fog` | 0.3 | sea fog (0 clear, 1 thick; the sea light gets a big halo) |
| `waves` | 1 | swell height (0.5 calm, 1 normal, 1.6 rough) |
| `door` | 0 | cottage door (0 shut, 1 open) |

### place `cliff` — exterior

the headland outside: lighthouse, keeper's cottage, bench and fence at the cliff edge, the open sea below. Footsteps: grass. Ambience: coast.

| mark | what |
|---|---|
| `cottage-door` | outside the cottage door |
| `lighthouse-door` | at the lighthouse door |
| `path` | on the path between the buildings, facing the sea |
| `edge` | at the fence on the cliff edge, facing the sea |
| `edge-left` | further left along the fence |
| `bench` | the bench facing the sea (sit) · seat |

POIs (targets for `target` / `show` / callouts): `sea`, `light`, `horizon`, `boat`, `lighthouse-top`, `lighthouse`, `cottage`, `cottage-window`, `cottage-door`, `bench`, `moon`

### place `gallery` — exterior

the balcony around the lamp room at the top of the lighthouse, railing, the sea far below. Footsteps: metal. Ambience: gallery.

| mark | what |
|---|---|
| `gallery-sea` | at the railing facing the sea |
| `gallery-left` | railing, left side |
| `gallery-door` | by the lamp-room door |

POIs (targets for `target` / `show` / callouts): `sea`, `light`, `horizon`, `boat`, `lamp`, `cottage`, `below`

### place `cottage` — interior

inside the keeper's cottage: desk with logbook, oil lamp and radio under the sea window, bed, door. Footsteps: wood. Ambience: cottage.

| mark | what |
|---|---|
| `desk` | the chair at the desk, facing the window (sit) · seat |
| `window` | standing at the window |
| `door` | just inside the door |
| `middle` | middle of the room |
| `bed` | the bed (lie / sit up / wake) · bed |

POIs (targets for `target` / `show` / callouts): `sea`, `light`, `horizon`, `boat`, `window`, `logbook`, `lamp`, `radio`, `door`, `bed`

### place `sea` — exterior

out on the water near the fishing boat (outrigger perahu with a lamp pole); the headland far behind. Footsteps: wood. Ambience: sea.

POIs (targets for `target` / `show` / callouts): `boat`, `lamps`, `lighthouse`, `cliff`, `horizon`

## kos

an Indonesian boarding house (kos): bedroom, long hallway with seven doors, the front yard (from Dunia Nabel EP01)

**times:** `night` · `day`

**set props** (numbers 0…1 unless noted; change them per scene or beat with `set`):

| prop | default | what |
|---|---|---|
| `door` | 0 | the bedroom door (0 shut, 1 wide open) |
| `tubes` | 0 | hallway ceiling lamps (0 off — moonlit, 1 on) |
| `fan` | 1 | the standing fan in the bedroom turning (0 off, 1 on) |
| `vent` | 0 | the loose vent flap at the end of the hallway rattles (0 still, 1 rattling) |
| `coat` | 1 | the long black raincoat hanging at the end of the hallway (0 gone, 1 there) |
| `pile` | 0 | clothes piled on the desk chair (0 none, 1 a pile that looks like someone sitting) |
| `gudang` | 0 | the storage-room door at the end (0 shut, 1 open) |

### place `room` — interior

a small kos bedroom: bed under the window, desk with an LED clock, standing fan, wardrobe, door to the hallway. Footsteps: tile. Ambience: room.

| mark | what |
|---|---|
| `bed` | the bed (lie / sleep / wake / sitUp) · bed |
| `desk` | the desk chair (sit) · seat |
| `door` | inside the door, facing it |
| `window` | at the window |
| `middle` | middle of the room |

POIs (targets for `target` / `show` / callouts): `door`, `window`, `clock`, `fan`, `bed`, `desk`, `socket`, `wardrobe`

### place `hall` — interior

the long kos hallway (16 m), room doors left and right, ceiling tube lamps, CCTV at the entrance; at the far end: gudang door, vent, raincoat on a hook, small window, water dispenser. Footsteps: tile. Ambience: hall.

| mark | what |
|---|---|
| `entrance` | just inside the entrance, facing down the hallway |
| `room-door` | outside room 2's door (the bedroom), facing it |
| `middle` | halfway down, facing the end |
| `near` | a step closer to the end |
| `dispenser` | at the water dispenser |
| `end` | at the end of the hallway |
| `switch` | at the light switch on the left wall (reach → switch, then set tubes: 1) |

POIs (targets for `target` / `show` / callouts): `end`, `coat`, `vent`, `small-window`, `switch`, `dispenser`, `entrance`, `cctv`, `lamp`, `room-door`, `door-1`, `door-2`, `door-3`, `door-5`, `door-6`, `door-7`, `door-8`

### place `yard` — exterior

outside the kos: front yard, terrace lamp over the entrance, low gate, the street with a sodium lamp, a parked scooter. Footsteps: stone. Ambience: outdoor.

| mark | what |
|---|---|
| `terrace` | on the terrace, facing the gate |
| `terrace-in` | on the terrace, facing the entrance |
| `yard` | in the yard, facing the house |
| `gate` | inside the gate, facing the house |

POIs (targets for `target` / `show` / callouts): `kos`, `entrance`, `gate`, `street`, `terrace-lamp`, `sky`

## (every world)

studio places

### place `mind` — studio

studio: the glass brain floating in a deep-blue neural space (any world) — for explaining perception, memory, fear. Footsteps: carpet. Ambience: studio.

POIs (targets for `target` / `show` / callouts): `brain`, `front`, `top`, `back`, `left`, `right`


## Notes per world

**coast** — night is the signature: moon glitter on the sea, the lighthouse beams sweeping, fog halos.
`seaLight` is a distant light (it blinks as swells hide it); `boat: 1` reveals the outrigger fishing boat
that carries it (best at `dawn`/`day` in place `sea`). The `sea` place has no marks (no people on the
water in this version). Long-lens shots from the cliff or through the cottage window find the sea light
automatically when you `show: "light"`.

**kos** — the hallway at night is moonlit through a small window at the far end; `tubes: 1` switches the
ceiling lamps on (the "lights on, it was nothing" moment) — stage it as `walk` to mark `switch`,
`reach` with `target: "switch"`, then `set: { "tubes": 1 }` on the next word. The raincoat on the hook (`coat`) reads as a
dark figure from the middle of the hallway. The bedroom has a bed (`lie/sleep/wake/sitUp`), a desk
chair (`sit`), a fan (`fan`) and a window. `yard` is outside at the terrace and gate. Times: `night`, `day`.

**mind** (every world) — a deep-blue neural space with a turning glass brain. Use it for 1–3 short
explanation beats with `ui` (callouts on `front`/`top`/…, cards, steps). Mood `science` (no letterbox). Use the studio only where the script is about the brain / thoughts, briefly — explain with the story's own world otherwise.

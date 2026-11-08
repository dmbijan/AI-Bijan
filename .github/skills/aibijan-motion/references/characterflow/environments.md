# Environments

Six finished sets. Each comes with camera bounds and keep-out zones, standing marks, seats, ambience and
lighting per time of day — the director only uses this data. A story plays in one environment, or moves
between several with `scenes` ([story-format.md](story-format.md)): each scene cuts to a place on a spoken
word, with its own time of day and who is there.

| `env` | Place | `time` | Seats (`start`) | Marks (`start`) |
|---|---|---|---|---|
| `campus` | university walkway: long paver path, lecture building "GEDUNG KULIAH B", trees, benches, lamps | `day` `sunset` | `benchL` `benchR` (bench, side by side) | `center` `talkL` `talkR` `lawn` · walking path |
| `bedroom` | small bedroom: bed with a live blanket, bedside lamp + clock, study desk with laptop and swivel chair, window | `night` `day` | `chair` (desk chair) `bedEdge` · bed: `lie` / `sitBed` | `center` `talkL` `talkR` `rug` `window` |
| `classroom` | empty classroom after lessons: whiteboard, teacher's desk, two rows of school desks, windows, door | `day` `sunset` | `deskL` `deskR` (front row, facing the board) | `center` `board` (teacher at the board) `talkL` `talkR` `window` `door` |
| `office` | small modern office: two desks with laptops facing the room, window wall with the city, shelf, dispenser | `day` `night` | `deskA` `deskB` (behind the desks, facing the cameras) | `center` `talkL` `talkR` `window` `dispenser` `door` |
| `cafe` | cosy café: counter with espresso machine and chalk menu, pendant lamps, small tables, street window | `day` `night` | `tableL` `tableR` (across one table, face to face) `window` (window table) `bar` (stool, back to camera) | `center` `counter` (back to camera) `talkL` `talkR` `window` `door` |
| `livingroom` | family living room: sofa, coffee table, TV cabinet, armchair, family photos, window, front door | `day` `night` | `sofaL` `sofaR` (side by side) `armchair` | `center` `talkL` `talkR` `window` `door` (back to camera) `tv` |

- `start.<id>` takes a seat or mark name from the table (`"start": { "naya": "tableL", "bima": "tableR" }`),
  or `sit` / `stand` (then the first seats / `talkL`+`talkR` / `center` are used), `walk` (campus), `lie` /
  `sitBed` (bedroom).
- Two people who talk: put them on two seats of one table / sofa / bench, or on `talkL` + `talkR`. Seats far
  apart (sofa + armchair) work, but the opening wide may show one of them from behind.
- Marks marked "back to camera" suit 1–2 beats (ordering at the counter, opening the front door), not a
  whole scene.
- Night in a room: the lamps are on and the windows show the night outside; day: daylight through the
  windows. Office and café lamps glow by day too.
- Ambience: campus = outdoor air, chatter, birds; bedroom / living room = room tone (crickets at night,
  birds by day); classroom = room tone + distant voices; office = hum + keyboards; café = chatter, cups,
  the espresso machine. Footsteps follow the floor.

## Choosing
| The VO is about… | env |
|---|---|
| walking, meeting / avoiding someone, campus life, a park | `campus` |
| being alone at home, night thoughts, sleep, phone in bed, studying in one's room | `bedroom` |
| school, lessons, exams, a teacher, choosing a major, classmates | `classroom` |
| work, a boss, deadlines, colleagues, a job interview, HR | `office` |
| hanging out, friends, a date, "nongkrong", treating someone, ordering | `cafe` |
| family, parents, home life, guests, TV, a parcel at the door | `livingroom` |
A VO that moves between such situations ("di rumah, sekolah, kantor…", a montage of examples) → one scene
per situation.

## Adding an environment (developer task — not per video)
Only when the buyer explicitly asks for a new set **and** agrees it is extra development work. Do it in the
buyer's project (never in the installed skill).
- **A room** (most new places): one more `RoomLayout` in `src/kits/characterflow/env/room.data.ts` — the room
  box, windows / door, furniture pieces (`desk`, `schoolDesk`, `chair`, `stool`, `sofa`, `armchair`,
  `coffeeTable`, `cafeTable`, `counter`, `shelf`, `board`, `tv`, `plant`, `rug`, `frame`, `clock`,
  `dispenser`, `cabinet`, `lamp`) and named marks; a piece with `seat` / `seats` becomes a seat. Add its id
  to `EnvId`, `ROOM_NAMES`, `ROOM_TIMES` and `ENVS` in `env/registry.ts`. Nothing else changes.
- **An outdoor or special set**: `<name>.data.ts` + `<Name>.tsx` geometry + an `EnvData` entry in
  `env/registry.ts` (bounds, blockers, viewSide, path, marks, seats, ambience, times, light, grade) + the
  component in `env/components.tsx`.
- Test with a short story using every mark and seat, check stills from all shots, then a final render.

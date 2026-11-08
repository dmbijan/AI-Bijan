A complete dynamic-world example (structure reference — never copy its beats for another story):
- `perpustakaan.world.json`: a reading room (interior, door with sign, windows, shelves, tables, chairs, ceiling panels) inside a park (terrain, path, trees, bushes, benches, lamp posts with a set prop).
- `objects/`: the four objects it uses, built from primitives (also in the project's `library/objects`).
- `story.json` + `words.json` + `script.txt`: 4 scenes — walk across the park, open the door, sit and write, the park lamps switch on.
Build it inside a project: copy the folder to `stories/perpustakaan/`, make a VO with `scripts/tts.mjs` from script.txt, then `node scripts/make.mjs stories/perpustakaan/story.json --stills=2,6,9,11,14`.

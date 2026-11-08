# Interacting with objects

People can handle objects of the world. Objects keep their state for the rest of the film (a pushed
wheelchair stays where it was pushed; a handed-over bag stays in the other person's hand).

| beat | does | needs |
|---|---|---|
| `{ "do": "push", "obj": "kursi-roda", "to": "<mark>" }` | walks to the handles (if not there), both hands on them, pushes it to the mark (rolling sound) | object with a `push` anchor |
| `{ "do": "pickUp", "obj": "kantong" }` | reaches and takes it in the right hand | object within 1.3 m (walk to `"<obj>:front"` first); small things should have `hand: true` |
| `{ "do": "putDown", "target": "timbangan:top" }` | puts what they carry on a surface / point (`obj` optional) | carrying something |
| `{ "do": "give", "target": "<person>", "obj"?: "uang" }` | hands it over; the other person reaches and receives it | both in the scene, ≈ 1 m apart |
| `{ "do": "open" / "close", "obj": "pintu-gudang" }` | hand to the door, the door swings (sound) | standing ≤ 0.8 m in front of the door |
| `{ "do": "kneel", "target": "kursi-roda:brake" }` | kneels and reaches to a low point | the point within reach |
| `{ "do": "write", "target": "meja:papers" }` | sits writing on that spot | sitting; the point ≤ 0.3 m from the desk edge |
| `{ "do": "raise", "target": "burung-hantu" }` | aims the flashlight / lantern at it (body turns toward it; the beam lights it) | `"prop": "flashlight"`/`"lantern"` in the cast |
| `{ "do": "point", "target": "jeruk" }` | points at it (turns first if it is beside / behind) | — |
| `{ "move": { "obj": "kursi-roda", "to": [0.5, -12], "dur": 7 } }` | the object moves by itself (rolling, sliding) | any beat, nobody has to act |
| scene cast `{ "at": "ujung", "holding": "uang" }` | starts the scene with it in hand | object in the world |
| `{ "set": { "surat.visible": 1 } }` | the object appears (0 = it is gone) — every object has a `visible` prop | start it hidden with `"states": { "visible": 0 }` in the world |

**Reveals and exits.** Something the story reveals later (a figure behind a bush, a hidden letter)
must NOT be on screen before its word: place it hidden (`"states": { "visible": 0 }`) or out of view, and
make it appear on the reveal beat. An object or stand-in figure that "leaves" (never an animal running off — [limits.md](limits.md)): a quick
`move` (0.4–0.8 s) to a point behind cover, then `"set": { "<id>.visible": 0 }` on the next word. Never leave it sitting there.

- Object ids, anchors (`<id>:<anchor>`) and auto marks (`<id>:front`, `:seat`, `:push`, `:behind`) are
  valid targets / destinations. Callouts (`ui.callout.poi`) follow moved objects.
- An object being pushed or carried never blocks the person handling it (preflight knows).
- The validator says what is missing in plain words (too far, not carrying, no handles, …).

## Limits
- One hand carries (right). Two-hand carrying of big things, throwing, opening drawers/lids by hand,
  and handing objects through a window are not available.
- Pushing goes in straight segments along the navigation path; tight turns look stiff.
- Doors open by a set prop; the hand reaches toward the door but does not grip the handle exactly.

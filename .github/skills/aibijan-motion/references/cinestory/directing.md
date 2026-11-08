# Directing a CINÉSTORY film

CINÉSTORY = a short cinematic film that *shows* the narration: a story played by 3D people in a real
place (≈60%), and a clear visual explanation (≈40%) — mystery → understanding. The VO is the master
clock: every cut and action is placed on its words.

## 1. Read the VO as a film
Read all of words.json. Mark: the hook (first 5–10 s), the turning point(s), the explanation, the reveal,
the last line. Map each sentence to *what we see*. Typical shape:

| part | mood | what we see |
|---|---|---|
| hook / cold open | `mystery` | establishing shot of the place at night, the person, something strange (a light, a sound, a shape) |
| build-up | `tense` | closer: the person reacts (peer, freeze, startle), POV / long lens on the strange thing, strategic `quiet` |
| explanation | `science` | the `mind` studio or a `map` / high view with `ui` callouts and a card — 1 idea per beat |
| reveal | `reveal` | the same place in clear light (`tubes: 1`, `dawn`/`day`, `boat: 1`): it was ordinary |
| reflection / ending | `warm` / `calm` | the person relaxed (smile, sigh), a `pull` back, end card |
Not every VO is a mystery: a calm story can stay `calm`/`warm`; a memory can use `memory`; a story-within-
the-story (someone telling a rumour) uses `told`.

## 2. Scenes
- A new scene when the place, the time of day or the mood changes — on a sentence start.
- Re-use places: going back to the same place later (in clear light) is the strongest reveal.
- Time jumps (later that night, next morning) → `"cut": "fade"`.
- 4–9 scenes for 60–90 s is typical. A scene shorter than ~3 s rarely reads.

## 3. Beats (one every 2–4 s)
- Put an action ON its word: "ia menoleh" → `{ "at": "menoleh", "do": "look", "target": … }`.
- Alternate person and subject: reaction (cu) ↔ what they see (`show` → long lens). Show the strange
  thing small and unclear first, bigger later.
- Give walks room: 3–5 s for a few metres, from a mark with a clear line to the destination.
- Use `set` for story changes the audience must notice: lights on, a light at sea appears, fog thickens,
  a door opens. Change one thing at a time.
- Explanations: one short `ui` per idea (callout on the thing, or a card with ≤ 3 lines). Use the VO's
  own words; never invent numbers or study results.
- `hit` on the big reveal word, `quiet` on the breath before it.

## 4. People
- 1–2 people. Give each a look that reads instantly (colour, cap/hijab/beard) and keep it.
- Their emotion is carried by action + face + framing: freeze/startle → cu; relief → sigh/smile; doubt → think.
- `talk` only when the VO is that person speaking (narration is not dialogue).

## 5. Cards and honesty
- Title card after the hook (2–5 s in), not over the first word.
- A fictional story told as an illustration gets a `disclaimer` ("Kisah ilustrasi — tokoh dan kejadian fiktif").
- End card: the film's title (and the series name if the buyer has one).

## 6. Check the plan
`make.mjs` prints the shot list. Read it: is every strange thing shown at least once? Does every scene
open with a readable establishing shot? Too many similar shots in a row? Then stills (qa.md).

## Directing for the vertical frame (portrait, the default)
- One subject at a time: faces and hands read on a phone, wide geography does not. Establish a place once, then
  stay with the person; show the important object in a shot of its own.
- Reactions on the words (menoleh, kaget, berpikir) — the director gives them close-ups; give each long passage
  something new every 2–4 s (a look, a turn, a reach, a step, an insert of the thing).
- Stage in depth, not side by side: two people one behind the other, the path towards / away from the camera,
  corridors and aisles along the frame.
- Give handling marks room (≥ 1 m in front and on one side) — a person squeezed into a corner can only be filmed
  from behind.
- Explanations stay in the story's world: the thing itself in a close shot, a card or callout over it, a person
  reacting. The `mind` studio only where the script is about the brain / thoughts, briefly.
- Night stays night but faces read: keep a practical light (lamp, screen, window) near where people act.
- Lights going out: show the room getting dark (a wide, the person in the last light) — never a close shot of a lamp
  or panel (bloom burns the frame white; QA fails "no blown-out highlights").
- Darkness has a shape: a dark room seen from outside keeps a sliver of light (the doorway edge, a screen, a window,
  the person's lit face in the foreground) — a practically black shot fails QA "readable on a phone".
- A person at a window, a wall or a mirror: a face shot needs room for the camera in front of the face. Put the mark
  ≥ 1.5 m from the window / wall and let them look at it at an angle, or show the view over the shoulder (`ots`,
  `reveal`) and the face on the next beat — the preflight stops a close-up that shows only the back of the head.
- A big fire, a disaster far away (forest fire, burning building, eruption) is told by what reaches the people: the
  sky turning orange / grey, smoke over the street (`fog`), a glow behind roofs, faces turning to it, a tag with the
  distance. Never make it a shot of `fire` fixtures — they are campfires and read as orange blurs in the dark.
- The reveal of a person (the double, a figure in the dark) shows the face — a close-up from the front or side; the
  camera search refuses close-ups from behind the head.

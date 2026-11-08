# story.json

One file per video: `stories/<id>/story.json`. Everything else is derived from it.

```json
{
  "id": "menunda",
  "title": "Kenapa kita suka menunda?",
  "vo": { "file": "audio/menunda.mp3", "offset": 0.4 },
  "words": "words.json",
  "env": "bedroom",
  "time": "day",
  "cast": [ { "id": "naya", "look": "naya", "prop": "phone" } ],
  "start": { "naya": "sit" },
  "mood": "playful",
  "beats": [
    { "at": "kenapa", "do": "think", "thought": { "text": "Nanti aja deh…" } },
    { "at": "tugas", "do": "overwhelmed" },
    { "at": "tangan", "do": "phone" },
    { "at": "jadi", "do": "standUp" },
    { "at": "coba", "do": "explain" },
    { "at": "kerjakan", "do": "point" },
    { "at": "biasanya", "do": "proud" }
  ],
  "highlights": ["menunda", "langkah kecil", "lima menit"]
}
```

## Fields

| Field | Required | Meaning |
|---|---|---|
| `id` | ✓ | a-z, 0-9, "-". Output goes to `out/<id>/`. |
| `title` | ✓ | for you; not shown. |
| `vo.file` | ✓ | path inside `public/` (e.g. `audio/<id>.mp3`). |
| `vo.offset` | | when the VO starts in the film, seconds (default 0.4). |
| `words` | ✓ | `"words.json"` (file next to story.json, from transcribe/tts) or the inline array `[[word, start, end], …]` in VO seconds. |
| `env` | ✓ | the (first) place: `campus` `bedroom` `classroom` `office` `cafe` `livingroom` ([environments.md](environments.md)). |
| `time` | | per env ([environments.md](environments.md)); default: the first listed. |
| `cast` | ✓ | 1–2 entries `{ id, look, prop?, outfit? }` (with `scenes`: up to 4, at most 2 per scene). `id` is your name for them (used in `who`/`target`); `look` ∈ `raka naya bima dimas boss sari dinda ibu ayah adit` ([characters.md](characters.md)); `prop` ∈ `phone bag both`; `outfit` changes colours / clothes ([characters.md](characters.md)). |
| `start` | | per id: a seat or mark name of the env (`tableL`, `sofaR`, `board`, `window`…), or `walk` (campus path), `stand`, `sit`, `lie` · `sitBed` (bedroom bed). Default: inferred from the first beats (walk if their first beat is `walk` or they `pass`/`meet`; sit if they `standUp` before `sitDown`; else stand). |
| `inScene` | | with `scenes` and more than 2 people: who is in the first scene. |
| `scenes` | | later places, in order: `[{ "at": cue, "env", "time"?, "cast"?: [ids], "start"?: {…} }]` — see below. |
| `beats` | ✓ | the acting, see below. |
| `highlights` | | phrases from the VO that light up in captions. |
| `cards` | | `[{ "at": cue, "until"?: cue, "text": "5 DETIK KEMUDIAN…" }]` full-width title cards. |
| `board` | | campus: `{ "at", "swap"?, "until", "face1": { "kicker", "title", "left"?, "right"? }, "face2"? }` pop-up sign beside the path. |
| `tumbleweed` | | campus: `{ "at", "until" }` awkward-silence gag. |
| `mood` | | `playful` · `warm` · `tense` · `calm` music baseline. |
| `tail` | | seconds of picture after the VO ends (default 1.8). |

## Scenes — more than one place

```json
"env": "bedroom", "time": "day", "start": { "raka": "lie" }, "inScene": ["raka"],
"scenes": [
  { "at": "jurusan", "env": "classroom", "cast": ["raka"], "start": { "raka": "deskL" } },
  { "at": "gebetan", "env": "cafe", "cast": ["raka", "sari"], "start": { "raka": "tableL", "sari": "tableR" } },
  { "at": "jadi", "env": "bedroom", "time": "night", "start": { "raka": "sitBed" } }
]
```
- The story's own `env` / `time` / `start` / `inScene` are the first scene; each entry of `scenes` cuts to a
  place just before its cue word. Beats belong to the scene they fall in (by time).
- `cast` = who is there (default: everyone, max 2); `start` = where they are when the scene opens (a person
  can walk at the start of a campus scene). Each scene opens with a wide of the place.
- Give a scene ≥ 2.5 s (a sentence or two); a montage of 3–6 places reads well, more turns into flicker.
- Keep a person's look the same in every scene (the same `cast` entry).

## Beats

`{ "at": cue, "do": action, "who"?: id | [ids], "target"?: id, "thought"?: {…}, "shot"?: shot, "face"?: expression }`

- `at` — a **spoken word** (case and punctuation ignored, "tiba-tiba" → `"tibatiba"`), `"word#n"` for its n-th occurrence (0-based: `"dia#1"` = second "dia"), or a number = film seconds.
- `do` — an action from [actions.md](actions.md). It lasts until that person's next beat.
- `who` — default: the first cast member (both people for `eyeContact`, `pass`, `meet`, `highFive`). An array plays the same action on both.
- `target` — who to look/wave/point at (default: the other person, else ahead).
- `thought` — bubble above `who` while the beat lasts: `{ "text": "Lomba apa?" }`, `{ "icon": "question" }`, or `{ "vignette": "friends" }`. Icons: `question exclaim cross clock chat cloud heart chart bell dots`. Vignettes (tiny scene in the bubble, played by the thinker and the other cast member — or a generic friend in a one-person story): `friends` (the two of them high-fiving, back when they were close), `forgot` (the thinker waves, the other draws a blank), `weird` (a huge wave, a baffled stare). `confused` adds a "?" icon and `surprised` a "!" icon automatically when no thought is given.
- `shot` — force a camera shot ([shots.md](shots.md)); normally leave it out.
- `face` — force an expression ([characters.md](characters.md)).

## Validation
`make.mjs` validates the story first and prints every problem in plain words (unknown word, action, look, seat or mark name, colour, a walker asked to sit, a bed action outside the bedroom, a scene with too many people, …), scene by scene. Fix them all, rerun. A cue word that is spoken more than once lands on its **first** occurrence — use `"word#n"` for a later one (with scenes a wrong occurrence puts the beat in the wrong place).

## Complete examples
`kits/characterflow/examples/teman-lama.story.json` (campus, two people, walking, pass, board, tumbleweed, card) and `kits/characterflow/examples/menunda.story.json` (bedroom, one person, sit → stand, phone, text thoughts). Structure reference only — do not copy their beats.

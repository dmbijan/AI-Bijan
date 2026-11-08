# story.json

One file per video: `stories/<id>/story.json`. Everything else is derived from it. A complete example:
[examples/cahaya-di-tengah-laut.story.json](examples/cahaya-di-tengah-laut.story.json) (structure reference
only — never copy its beats for another topic).

```json
{
  "id": "cahaya-laut",
  "title": "Cahaya di Tengah Laut",
  "vo": { "file": "audio/cahaya-laut.mp3", "offset": 1.5 },
  "words": "words.json",
  "world": "coast",
  "cast": [{ "id": "darma", "look": "penjaga-mercusuar", "name": "Pak Darma", "prop": "binoculars" }],
  "titleCard": { "at": "Malam", "until": "ini", "text": "CAHAYA DI TENGAH LAUT" },
  "disclaimer": { "at": "Seperti", "until": "tipis", "text": "Kisah ilustrasi — tokoh dan kejadian fiktif" },
  "endCard": { "text": "CAHAYA DI TENGAH LAUT" },
  "scenes": [
    {
      "at": 0, "place": "cliff", "time": "night", "mood": "mystery",
      "set": { "beam": 1, "seaLight": 0, "fog": 0.25 },
      "cast": { "darma": { "at": "lighthouse-door" } },
      "beats": [
        { "at": "Malam" },
        { "at": "Pak", "do": "walk", "to": "cottage-door" }
      ]
    },
    {
      "at": "Seperti", "place": "cottage", "mood": "calm",
      "cast": { "darma": { "at": "desk", "do": "sit" } },
      "beats": [
        { "at": "Seperti", "do": "write" },
        { "at": "Angin", "shot": { "type": "insert", "target": "logbook" } },
        { "at": "Lalu", "do": "look", "target": "window" },
        { "at": "Jauh", "show": "light", "set": { "seaLight": 1 }, "fade": 1.5 }
      ]
    }
  ]
}
```

## Top level
| Field | Required | Meaning |
|---|---|---|
| `id` | ✓ | a-z, 0-9, "-". Output goes to `out/<id>/`. |
| `title` | ✓ | the film's name (end card default). |
| `vo.file` | ✓ | path inside `public/` (`audio/<id>.mp3`, written by transcribe.mjs). |
| `vo.offset` | | seconds of picture before the VO starts (default 1.5 — room for an establishing shot). |
| `words` | ✓ | `"words.json"` (next to story.json) or the inline array `[[word, start, end], …]` (VO seconds). |
| `world` | ✓ | `coast`, `kos` ([worlds.md](worlds.md)) or the name of a dynamic world: `stories/<id>/<name>.world.json` or `worlds/<name>.world.json` ([dynamic-worlds.md](dynamic-worlds.md)). |
| `cast` | | people: `{ id, look, name?, prop?, custom?, phoneText? }` ([characters.md](characters.md)). 0–3 people. |
| `scenes` | ✓ | in VO order, see below. |
| `titleCard` | | `{ at, until?, text, sub?, kicker? }` — big title over the picture (usually after the hook). `kicker` / `sub`: the story's own words only — never a channel / product name (branding comes from the user's brand kit). |
| `disclaimer` | | `{ at, until?, text }` — small lower-left line; use it for fictional illustrations. |
| `endCard` | | `{ text?, sub?, seconds? }` — after the VO ends (default 4 s, text = title; the brand kit adds logo / name / handle / CTA under it). No channel or product name in `sub`. |
| `tail` | | seconds of picture after the VO before the end card (default 1.2). |
| `format` | | `"portrait"` (default) 1080×1920 @ 60 · `"landscape"` 1920×1080 @ 60 (only when the user asks for YouTube / 16:9). Each is directed for its own frame. |
| `captions` | | `"burn"` (portrait default: word-timed captions in the picture, inside the safe area) · `"off"` (landscape default). An `.srt` is always written. |
| `brand` | | `false` = no branding even when a `aibijan-brand.json` exists. Branding comes only from the user's brand kit ([portrait.md](portrait.md)). |

## Scenes
`{ at, place, time?, mood?, set?, cast?, cut?, beats }`

| Field | Meaning |
|---|---|
| `at` | the cue where the scene starts (the first scene starts at the film start whatever you write). The cut lands in the VO pause just before the word. |
| `place` | a place of the world, or `mind` (studio, every world). |
| `time` | the world's time of day (`night`, `day`, …). Default: the previous scene's. |
| `mood` | `mystery` · `tense` · `calm` · `memory` · `told` · `reveal` · `science` · `warm` · `cctv`. Sets grade, letterbox (landscape only, off for `science`/`cctv`; portrait shades top and bottom instead), camera hand-held amount and the score. Default: previous scene's. |
| `set` | set props at the scene start (instant), e.g. `{ "tubes": 0, "fog": 0.4 }`. Props keep their value until changed. |
| `cast` | who is in this scene and where they start: `{ "<id>": { "at": <mark>, "do"?: "stand"|"sit"|"lie", "face"?: <face>, "holding"?: <object id> } }`. Only these people appear. A scene may have no cast (`{}`): then it shows the place / a `show` target. |
| `cut` | transition INTO this scene: `cut` (default) · `fade` (through black, for time jumps) · `dip` (white) · `flash` (hit). |
| `beats` | the visual beats, in VO order. |

## Beats
`{ at, until?, do?, who?, to?, target?, show?, face?, shot?, set?, fade?, sfx?, ui?, hit?, quiet? }`

| Field | Meaning |
|---|---|
| `at` | a spoken word or short phrase (see cues). The action starts ON the word; the camera cut lands in the pause just before it. |
| `do` | an action ([actions.md](actions.md)). It lasts until that person's next beat. |
| `who` | a cast id or array (default: the scene's first cast member). |
| `to` | a mark: destination for `walk`/`run`, the seat for `sit` (a person not at the seat walks there first). |
| `target` | what they look/point/aim at: a POI, a mark, a cast id, or an object anchor `"<object>:<anchor>"`. Also the camera's target for over-the-shoulder / long-lens shots. |
| `show` | camera-only beat: show this POI or person (nobody has to act). |
| `face` | force an expression (`fear`, `smile`, … [characters.md](characters.md)). |
| `shot` | force a camera: `"cu"` or `{ "type": "insert", "target": "logbook", "subject"?: id, "side"?: "left"|"right", "fov"?: deg }`. Normally leave it out. |
| `set` | change set props from this beat, fading over `fade` seconds (default 0.6): `{ "seaLight": 1 }`, `{ "tubes": 1 }`. |
| `sfx` | extra sound effects by name ([sound.md](sound.md)), e.g. `"knock"`, `["thunder", "wind-gust"]`. |
| `ui` | on-screen graphics (one or an array), visible until the next beat or `until`: see below. |
| `until` | end cue for this beat's `ui` (and `quiet`). |
| `hit` | a musical hit + sound accent (reveals, shocks). Also forces a cut here. |
| `quiet` | the music drops out while this beat lasts (strategic silence before a scare/reveal). |
| `obj` | the object an interaction handles (push / pickUp / give / open / close) — [interactions.md](interactions.md). |
| `move` | an object moves by itself: `{ "obj": "kursi-roda", "to": "<mark>" or [x, z], "dur"?: s }`. |

A beat with only `set`, `ui`, `sfx` or `quiet` does not cut — it plays inside the current shot.

### ui
| type | fields | look |
|---|---|---|
| `title` | `text`, `sub?`, `kicker?` | big centred title |
| `lower` | `text`, `sub?` | lower-third statement |
| `tag` | `text`, `sub?`, `pos?` (`tl` `tr` `bl` `br` `c`) | small label |
| `callout` | `text`, `sub?`, `poi` | a dot on a 3D point + leader line + label (follows the camera); `poi` = a POI of the place, an object id, or a cast id (the label sits above that person's head and follows them) |
| `card` | `title?`, `lines` (≤ 4 short lines), `pos?` (`l`/`r`) | glass card, lines appear one by one |
| `stamp` | `text` | rubber-stamp word ("MITOS", "FAKTA") |
| `clock` | `text`, `sub?` | time stamp ("02.17") |
| `steps` | `items` (2–4) | a flow of steps lighting up in order |

## Cues
- A word: `"cahaya"` — case and punctuation ignored. Without `#n`, it means **the next time this word is spoken after the previous beat** (scene starts count too), so beats written in VO order never need counting.
- A short phrase: `"di balik"`, `"Nelayan memasang"` — use a phrase when a word repeats nearby.
- `"word#n"` — the n-th occurrence in the whole VO (0-based), when you really need it.
- A number — VO seconds.
- `words.json` keeps the script's spelling when `--script` is given (with or without whisper.cpp); whisper alone may write digits ("30") or misspell names — check it.

## Validation
`make.mjs` validates first and prints every problem in plain words with the valid choices (unknown word,
mark, POI, action, prop, look; a person asked to walk while sitting; a beat outside its scene; …).
Warnings (⚠) are printed with the shot plan — e.g. a walk that had to be cut short. Fix all, rerun.

## Shot `fov`
A `fov` written in a shot spec is a landscape lens (vertical degrees of a 16:9 frame). In portrait it is widened so
the frame holds the same width (capped at 62°) — a telephoto written for 16:9 does not become a slit in 9:16. Prefer
leaving `fov` out: every framing has its own portrait lens.

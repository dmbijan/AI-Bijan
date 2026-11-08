# Style: NOTED · `noted`

> Simple and clear, like a page of notes stuck around the speaker's head. A small monospace label types the topic of
> the moment above the head, strips of masking tape are laid on the frame as each thing is named, a ruled index card is
> taped over the blurred frame for a real list, the user's own clips are taped around the head, and one yellow
> highlighter stroke marks the word that matters. The footage stays natural, the subtitles stay quiet.

**Use for** explainers, education, tips, "how it works", comparisons and lists, reviews, "kenapa X?", creators who want
the edit to look simple but informative ("simpel aja", "kayak catatan", "penjelasan di atas kepala", "label di atas
kepala"), talking heads that name many things (models, tools, steps, genres, prices).
**Not for** hard-sell ads with offers everywhere (→ IMPACT), dramatic mood storytelling (→ STORYTELLER), loud sticker
comedy (→ CREATOR POP), heavy AE layering on every line (→ DEPTH), dark cinematic glow (→ LUMEN).
**Recommended settings** editing_intensity medium · sound_effects medium · visual_overlays moderate.
**Best footage** some free wall above the head (head below the top quarter of the frame) - the notes live there. With
little headroom use `anchor: "top"` labels, tape on the `left` / `right` slots, and the index card for lists.

## Look
- **Natural grade**: true skin, a touch of warmth, slightly softened contrast, light film grain, a light vignette. It
  should look like a well-shot phone video, not a filter.
- **Stationery, not posters**: masking tape (cream, highlighter yellow, black) with torn ends, a ruled index card with
  blue lines and a red margin, a yellow highlighter, a red marker for strikes and outlined labels. Nothing glows, no
  gradients, no 3D.
- **Type system** (all bundled, SIL OFL): **Geist** for the subtitles, **Geist Mono** (uppercase, wide tracking) for every
  label, heading and margin note, **Inter Tight ExtraBold** on tape and card lines, **Inter Tight Black** for the one
  huge highlighted word. No serif, no script.
- Notes sit ABOVE or BESIDE the head - never on the face. One focal note at a time.

## Captions
- `note`: no box, Geist 560 at 50 px (hook 56) in sentence case **with its punctuation** (commas carry the rhythm of a
  list: "dari cerita horor, sejarah,"), up to 6 words per page; each word fades in out of a soft blur as it is spoken.
- Emphasis: a yellow highlighter stroke is drawn behind the word left → right on the word and the word turns to ink.
  One per page at most, about one per 4–6 s.
- Over the index card the captions stay white on the dimmed, blurred frame. A very soft dark pool keeps white words
  readable on light walls.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| The topic of each section | `HeadNote` (mono label typed above the head, `anchor: "top"` or `"face"`) | retype it (`mode: "new"`) when the topic changes; grow it (`mode: "add"`) as a list is spoken: `A · B · C` |
| The hook word, the one key word | `BigNote`: one huge word with a highlighter stroke drawn behind it (or `mark: "circle"` / `"underline"` in marker red) and a mono margin note with a drawn arrow (`kickerPos: "right"`/`"left"`) or a kicker above | 1–2 per video; behind the head when there is wall |
| Things named in a sentence (2–5 items) | `NoteTags` (tape, `surface: "paper"`) laid above the head on each word, optional mono `label` | 1–3 words per strip |
| A question, a step, "gimana caranya?" | `NoteTags` `surface: "outline"` on the `right` / `left` slot (`dir: "column"`), stacking as each is said | marker-red outline, white text |
| An idea dropped, "dulu… tapi sekarang" | `strikeAt` on the strip(s): a marker line crosses them out; the new idea as a yellow strip (`tone: "accent"`) with `pointer: true` | on the turning word ("tapi", "bukan") |
| A real list, a process, "ada 3 kelas" | `NoteBoard`: a ruled index card taped over the blurred frame - a highlighted mono heading, numbered lines typed on their words, a strike pass | 2–8 lines, 1–3 groups |
| Examples, "ini hasilnya", genres | `NoteClips` (the user's clips/photos taped as cards around the head, tape labels) | 1–3 cards; the user's own media only |
| The proof itself | `BRoll` full frame (the user's clips) cut on the words, `HeadNote` on top with a glass pill | short, `enter`/`exit: "cut"` |
| CTA | `NoteTags` with `pointer: true` (hairline to the head) and a yellow strip on the CTA words | no end card unless the user asks |

## 30–45 s beat recipe
> A rhythm, not a template: fill each beat from this video's concept (SKILL.md "Direct it first").
1. **HOOK** the first head note types; the hook word as a `BigNote` (highlighted, with its margin note).
2. **NAME IT** tape laid above the head on the things being named (or clip cards if the user has media).
3. **STRUCTURE** the index card for the real list/process; strike it out on the turn.
4. **TURN** back to the face; the head note retypes the new topic; one outlined label or a yellow strip.
5. **PROOF** the user's b-roll / clip cards, the head note labelling it.
6. **CTA** a strip with a pointer, the last head note.

## Motion
Quiet and precise. Labels type at ~26 characters/s with a block caret that blinks out; tape is laid left → right in
0.32 s and pressed (a tiny squash); outlined labels drop and turn into place; strikes draw in 0.24 s; the big word comes
into focus and its highlighter is drawn 0.22 s later; the index card rises 170 px and settles at −1° while the frame
behind it blurs; clip cards drop in with motion blur and float.
**The camera never snaps** (`camera.smooth`): cuts are clean jump cuts with no zoom jump and no cut transitions, a
punch-in is a soft 4.5 % push over 0.65 s that eases back over 1 s, rhythm pushes glide back instead of resetting on a
cut, and everything stuck to the head glides to its new place after a cut instead of jumping.

## Sound
`note` bed (86 bpm, maj7/9 colours, half-time kick, soft hats and plucks, light pad) at −16 LU, ducked under the voice.
Quiet typewriter ticks under head notes and card lines, a dry paper tap for every strip, a sheet swish when the card
comes and goes, a marker scribble on strikes and highlights, a swoosh for each clip card.

## Never
Notes on the face · more than one highlighter word per page · strips with sentences on them · two head notes at once ·
neon, glow, gradients or 3D chrome · serif "luxury" words · emoji · invented labels (every label is spoken or user
copy) · fake app UI · somebody else's clips as "results".

## Note pack in this style
The note pack IS this style's language (table above). Every element also renders in every other style (with that
style's fonts and colours where it has them).
- Depth pack: keep it out, or one `DepthText` stack of a number behind the head in Inter Tight Black; no echoes, rings or
  walls.
- Premium pack: a `Checklist` only if the card does not fit; no glossy objects.

## Pre-render checklist
- [ ] A head note names each section, and it changes when the topic changes (not one label all video).
- [ ] Every strip/label/line is a spoken word (or user copy), short, readable ≥ 0.8 s + 0.3 s/word.
- [ ] Notes sit above/beside the head on every still - nothing on the eyes or mouth, nothing in the caption band.
- [ ] The index card shows a real list (2–8 lines) and sits above the captions.
- [ ] Highlighter on ≤ 1 word per page; at least one beat is just the face with captions.

## Motion & finish (automatic from the config below)
Text `type`. Cuts: clean jump cuts, no zoom jump, no transitions; punches eased (`smooth`). Finish: grain 0.02, vignette 0.1. Rhythm ≤ 3.4 s.

```json style-config
{
  "id": "noted",
  "colors": {
    "bg": "#EEE6D3",
    "paper": "#FFFFFF",
    "ink": "#16140F",
    "muted": "#6F6858",
    "soft": "#E6DCC6",
    "line": "#D6CBB2",
    "accent": "#FFD43B",
    "accent2": "#E5383B",
    "danger": "#E5383B",
    "tag": "#ECE3CE",
    "tagEdge": "#C7B891",
    "board": "#EEE6D3",
    "grid": "rgba(22,20,15,0.035)",
    "glow1": "rgba(255,212,59,0.10)",
    "glow2": "rgba(229,56,59,0.05)"
  },
  "fonts": {
    "head": "InterTight",
    "body": "Geist",
    "ui": "Geist",
    "display": "InterTight",
    "displayWeight": 900,
    "serif": "InstrumentSerif",
    "serifWeight": 400,
    "accent": "InstrumentSerif",
    "mono": "GeistMono",
    "tag": "InterTight",
    "tagWeight": 800
  },
  "captions": {
    "variant": "note",
    "size": 50,
    "hookSize": 56,
    "weight": 560,
    "radius": 10,
    "maxWords": 6,
    "maxChars": 34,
    "hookMaxWords": 4,
    "hookMaxChars": 24,
    "maxPageDur": 2.4,
    "text": "#FFFFFF",
    "mark": "#FFD43B",
    "scrim": 0.22
  },
  "grade": {
    "sat": 0.97,
    "con": 1.03,
    "bri": 1.02,
    "warm": 0.03,
    "rawSat": 0.6,
    "rawCon": 0.95,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.06,
    "close": 1.2,
    "jumpZoom": 1.0,
    "punch": 0.045,
    "smooth": true,
    "punchDur": 0.65,
    "relaxDur": 1.0,
    "breathe": 0.01,
    "drift": 1.2,
    "push": 0.035
  },
  "wipe": {
    "band": 120,
    "skew": 130
  },
  "sound": {
    "bed": "note",
    "music_db": -16,
    "sfx_db": -8,
    "punch_sfx": "swoosh",
    "emph_pop": false
  },
  "cuts": {
    "fx": [],
    "minGap": 6.0,
    "dur": 0.22,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 3.4,
    "maxGraphicsPerMin": 22,
    "maxSfxPerSec": 1.3,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.02,
    "vignette": 0.1
  },
  "depth_defaults": {
    "look": "fill",
    "creep": 0.02
  },
  "endcard": {
    "variant": "title"
  },
  "textFx": "type",
  "defaults": {
    "sound_effects": "medium",
    "editing_intensity": "medium",
    "visual_overlays": "moderate"
  }
}
```

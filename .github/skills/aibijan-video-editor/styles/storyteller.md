# Style: STORYTELLER · `storyteller`

> Cinematic narration where the picture follows the emotion of every line. "Bayangin kalau…" stories, what-ifs,
> opinions, mind-blowing facts: the grade shifts with the mood (warm, shocked, whispering, cold, dark), typed captions
> appear letter by letter, ideas float around the speaker's head as 3D objects or search bars, big extruded words slam
> in behind them, and a magnifier reads a cloud of words in the dark. Restrained, dramatic, never noisy.

**Use for** storytelling hooks ("bayangin…", "gimana kalau…"), thought experiments, opinion pieces, social commentary,
psychology / facts, emotional narration with a twist.
**Not for** fast product ads (→ IMPACT), tutorials with UI (→ SYSTEM / MOGRAPH MIX), calm documentary (→ EDITORIAL),
personality comedy (→ CREATOR POP).
**Recommended settings** editing_intensity medium (keep the dramatic pauses the script marks) · sound_effects medium ·
visual_overlays moderate.

## Look
- Cinematic base grade (slightly crushed, warm skin), film grain, soft vignette.
- **Mood grading** per beat (`MoodGrade`): `warm` (setup, wonder) · `dream` (imagine…) · `amused` (jokes) · `excited`
  · `shock` (white flash + red punch) · `whisper` (dark, rose, heavy vignette) · `serious` (cold blue, desaturated)
  · `dark` (near-black drama). Each eases in/out over ~0.6 s; never more than one mood at a time.
- **Unbounded** Black for the big words (3D extrusion, tight tracking), **Plus Jakarta Sans** ExtraBold for captions,
  search bars and kickers. Accents: **gold** `accent` for key words and light, **ice** `accent2` for search UI, **red**
  extrusion for shock.

## Captions
- `type`: no box, white, 56 px (hook 64); characters type in as each word is spoken, a gold caret rides the end, key
  words in gold. Up to 4 words per page so sentences read like narration.
- Hidden automatically under a `Shout` / `StoryTitle` saying the same words and while a `SearchStack` bar types a
  spoken quote.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| Every beat | `MoodGrade` `{mood}` matching the line's emotion | one at a time, eased; script markers like [shocked] / [whispers] decide it |
| "Imagine…", setup | `mood: dream/warm` + slow push-in | first 2 s |
| Things floating above / around people | `SearchStack` (generic search bars above the head, queries typed; `max: 1` replaces, 3 stacks) | queries = spoken words; no real brand UI |
| Shock / reveal | `Shout` (extruded word, `tone: red`, `layer: "behind"` for text behind the speaker) + `mood: shock` + punch | 1–2 words, once or twice per video |
| Curiosity, fear, questions | `Floaters` (`?`, `!`, icons `eye`, `search`, `heart`, `lock`) drifting around the head with depth of field | 3–6 items, on their words |
| A list of feelings / hidden things | `SceneLens` (dark word cloud of spoken words, magnifier glides to each `focus` word on its cue) | 3–6 s, words spoken |
| The thesis line | `StoryTitle` `{kicker, text}` with a light arc + `mood: dark` (or `layer: "behind"`) | once |
| Turn / new chapter | layout cover `transition: "bloom"` (white light swell) | at the turn of the story |
| Last line / ending | `mood: dark`, slow push, typed question, final cover `"transition": "iris", "hold": true` | ends in black |

## 30–45 s beat recipe ("Bayangin…")
> A rhythm, not a template: keep the beat timing, but fill each beat from this video's concept (SKILL.md
> "Direct it first"). Swap any element for a custom scene or one borrowed from another style when the script has
> its own image, and never let two titles look alike.
1. **HOOK** dream/warm mood, push-in, the premise visual (search bars, floaters) on the key noun.
2. **SHOCK** shock mood + flash + Shout behind the speaker + punch.
3. **EXAMPLES** amused / whisper moods alternating, one SearchStack bar per example (`max: 1`).
4. **TURN** "tapi…" → serious mood, the energy drops; floaters of questions.
5. **DEPTH** SceneLens (bloom in / bloom out) on the list of hidden things.
6. **THESIS** StoryTitle with the light arc, dark mood.
7. **ENDING** quiet: whisper/dark mood, the premise again (search bars), last question typed, iris to black.

## Motion
Eased everything (quint/expo), no rubber springs. Shouts slam (scale 1.55 → 1, motion blur) then creep (4 % push);
floaters bob and turn in 3D; search bars rise out of blur and push older bars back; titles rise out of masks; the light
arc draws with a flare head. Cuts inside face beats: soft light leak or zoom, ~one per 4 s.

## Sound
`cinematic` bed (slow minor pad, heartbeat kick) at −16 LU, ducked under the voice. Impact on shouts and shock, riser
into the shock, shimmer on bloom covers and titles, typing on search bars, soft swooshes on mood changes.

## Never
Fake brand UI (no logos, no real search engine look) · emoji · moods flickering every second · more than two Shouts ·
shouting over a whisper line · captions inventing words (glossary only for spelling) · horror jump scares.

## Depth pack in this style (v3.7)
The depth tricks fit narration well: the picture lives on the narrator's head. (For the clean, bright AE-Reels version of
these tricks without mood grades, use DEPTH.)
- Shock word → `DepthText` `echo` (`tone: "danger"`) + camera `shake` + MoodGrade shock (instead of a second Shout).
- A loop or habit → `HeadRing` (`tone: "paper"`) or a `DepthText` `wall` with `ramp` and `scrollY` (it never stops).
- "Bukan X, tapi Y" / the thesis → `DepthText` `split` with two tones.
- Inner states → `Doodles`: `sparks` on "ternyata", `swirl` on "susah berhenti", `question` on "penasaran".
- Endless content → `FeedWall` behind the narrator as the signature, swipes on the spoken cue words.
- Premium pack (v3.9): `Glossy` objects float well in dark moods (`depth` high = dreamy); keep pills and checklists rare.
- Note pack (v3.10): a `HeadNote` as a case-file label ("MALAM ITU") in dark moods; avoid tape strips on dramatic beats.

## Pre-render checklist
- [ ] Every mood matches the line (read the script markers).
- [ ] Search bars and floaters never cover the eyes/mouth (they sit above / beside the head).
- [ ] The Shout reads behind the head on the stills (head covers only its lower part).
- [ ] The ending lands in black on the last word.

## Motion & finish (automatic from the config below)
Text `rise`. Cuts: `leak` / `zoom`, ~one per 4 s. Finish: grain 0.045, vignette 0.25. Rhythm ≤ 3.2 s.

```json style-config
{
  "id": "storyteller",
  "colors": {
    "bg": "#07080C",
    "paper": "#FFFFFF",
    "ink": "#0B0C10",
    "muted": "#9AA3B5",
    "soft": "#161A22",
    "line": "#2A303C",
    "accent": "#FFD23F",
    "accent2": "#7CE7FF",
    "danger": "#FF3B47",
    "grid": "rgba(255,255,255,0.03)",
    "glow1": "rgba(255,210,63,0.08)",
    "glow2": "rgba(124,231,255,0.06)"
  },
  "fonts": {
    "head": "PlusJakarta",
    "body": "PlusJakarta",
    "ui": "PlusJakarta",
    "display": "Unbounded",
    "displayWeight": 900,
    "serif": "PlayfairDisplay",
    "serifWeight": 500
  },
  "captions": {
    "variant": "type",
    "size": 56,
    "hookSize": 64,
    "weight": 800,
    "radius": 12,
    "maxWords": 4,
    "maxChars": 24,
    "hookMaxWords": 3,
    "hookMaxChars": 18,
    "maxPageDur": 2.0,
    "text": "#FFFFFF",
    "emph": "#FFD23F",
    "caret": "#FFD23F"
  },
  "grade": {
    "sat": 1.02,
    "con": 1.1,
    "bri": 1.0,
    "warm": 0.05,
    "rawSat": 0.5,
    "rawCon": 0.92,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.1,
    "close": 1.3,
    "jumpZoom": 1.04,
    "punch": 0.06,
    "breathe": 0.014,
    "drift": 2.5,
    "push": 0.05
  },
  "wipe": {
    "band": 125,
    "skew": 130
  },
  "sound": {
    "bed": "cinematic",
    "music_db": -16,
    "sfx_db": -8,
    "punch_sfx": "swoosh",
    "emph_pop": false
  },
  "cuts": {
    "fx": [
      "leak",
      "zoom"
    ],
    "minGap": 4.0,
    "dur": 0.26,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 3.2,
    "maxGraphicsPerMin": 22,
    "maxSfxPerSec": 1.4,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.045,
    "vignette": 0.25,
    "leak": ["#FFB45A", "#FF6A3D"]
  },
  "endcard": {
    "variant": "title"
  },
  "textFx": "rise",
  "defaults": {
    "sound_effects": "medium",
    "editing_intensity": "medium"
  }
}
```

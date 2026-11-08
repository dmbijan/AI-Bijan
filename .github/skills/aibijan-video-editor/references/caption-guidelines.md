# Caption guidelines

## Motion
Every look animates with real motion blur along the direction words travel (box: spring pop; clean: masked
rise; stroke: pop + lift on the active word; terminal: typing; glass: the page waits dimmed in a frosted pill and each word lights up as it is spoken; type: no box, each word's letters type in as it is spoken behind a caret). Captions hide automatically while a title or
end card shows the same words.

## Accuracy (non-negotiable)
- Captions come from the transcript. Never add words, never "improve" what was said, never translate unless asked.
- Fix only spelling of names/brands via `glossary` (e.g. Whisper writes "Jelauda", the speaker said "Claude").
  Mention every glossary change in the report.
- Keep the speaker's register (gue/lo, nggak, kayak) exactly as spoken. Punctuation from Whisper may be adjusted.
- Check `low_confidence` words in transcript.json; if a word is doubtful and matters, ask the user once.
- If transcription was impossible (models unavailable), ask for the script text and run `transcribe.py --script`.

## Timing
- Word timestamps come from CTC forced alignment (20 ms resolution). Pages lead the first word by 60 ms but never
  remove the previous page's last word before it is spoken (plan-edit enforces this; verify-output checks it).
- Pages break at sentence ends, at commas when sensible, at pauses > 0.35 s, at layout changes, and before a
  second emphasis chip. Max words/chars/duration per page come from the style.
- Hidden zones are for moments where a title shows the same word (wordmark). Keep them short.

## Look per style
| Style | Card | Case | Words per page | Active word | Emphasis |
|---|---|---|---|---|---|
| IMPACT | white card, soft shadow, Montserrat ExtraBold | UPPER | 1–3 (hook 1–2) | lime marker slides in | dark chip, lime text, optional icon, bump on the word |
| EDITORIAL | none - feathered cinema scrim behind the words | Sentence | 3–5 | thin sand underline | serif italic, warm white, larger |
| SYSTEM | dark UI card + `CC` tag, Geist Mono | Sentence | 2–4 | lime text, typing caret | blue chip with icon |
| CREATOR POP | none - thick black outline + hard shadow (`stroke`), page tilt | UPPER | 1–3 (hook 1–2) | word turns yellow + lifts | mint die-cut sticker box |
| MOGRAPH MIX | frosted dark glass pill (`glass`), Manrope ExtraBold white | as spoken | 1–3 | upcoming words dimmed, each lights up smoothly (eased, no bounce) | coral → amber gradient word |
| STORYTELLER | none - white Plus Jakarta Sans ExtraBold with a soft shadow, letters typed in (`type`), gold caret | as spoken | 2–4 (hook 1–3) | letters type in on the word | gold word |
| DEPTH | none - Inter Tight SemiBold white, soft shadow (`reel`), kept low and quiet | as spoken | 2–4 (hook 1–3) | each word arrives out of a blur with a short rise | Instrument Serif Italic in the pale accent, 1.24× |
| LUMEN | none - Outfit Bold white, soft shadow (`lumen`) | as spoken | 2-4 (hook 1-3) | each word rises out of a blur | luminous fire gradient with a glow |
| POSTER | none - Archivo ExtraBold white, soft shadow (`poster`) | as spoken | 1-3 (hook 1-2) | each word pops up (scale 0.86 → 1) | solid accent label, slight tilt |
| NOTED | none - Geist 560 white, soft shadow + a faint dark pool (`note`); ink on a light surface (zone `tone: "ink"`) | as spoken, **punctuation kept** | 3-6 (hook 2-4), up to 2.4 s | each word fades in out of a soft blur | yellow highlighter stroke drawn behind the word, word turns ink |
| raw | plain white text, black outline | as spoken | — | — | — (used only to show "before") |

## Emphasis
- Choose words that carry the message: the claim ("AI"), the feature named, numbers, the brand, the CTA verb.
- At most one per page and roughly one per 1.5–3 s; icons only when the icon literally depicts the word.
- Do not emphasise filler (yang, di, aja) or more than ~20 % of words.

## Placement
- Default bottom edge y = 1244 on 9:16 (inside Meta's safe zone), centred, max width 920 px.
- Move captions (zone `y`) only when a layout needs it (end card under a picture-in-picture), and never below 1248.
- Captions must not cover the mouth; adjust the `talk` framing or caption zone instead.

## Readability checklist
- ≥ 54 px glyph size on a 1080-px short side (IMPACT uses 66, hook 84).
- Contrast: ink on white card, white on dark card (SYSTEM, BUSINESS), white on footage only with a shadow (EDITORIAL) or a thick outline (CREATOR POP).
- No lime text on white, no blue text on dark olive/green footage.

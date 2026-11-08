# Directing a PANELVERSE film (VO → beat sheet → story.ts)

PANELVERSE has no fixed template. Each video is directed from its own voice-over: the pages, places, people, objects and comic devices are chosen and **invented for that script**. Two videos should never look like the same film with a different text. Work in this order.

## 1 · Understand the VO (write it down for yourself)
Read every line of `words.json`. In 4–6 lines: who, the situation, the **turn** ("padahal…", "tapi…", "ternyata…", "jadi…"), the punchline / feeling at the end, and the 3–5 words that carry the most weight (they get the strongest visual moments).

## 2 · Beat sheet (`beats` in story.ts — required, the lint checks it)
Split the VO into beats of **2–3.5 s** (one idea each). For every beat write: `at` (the word frame), `words`, `idea` (what the picture shows, one line) and `device` (how the comic tells it, from the menu below). Rules:
- **≥ 4 different devices** per video, never the same device twice in a row.
- The opening beat grabs in the first second (a close-up with action, a punch, a strange image) — never an empty establishing shot.
- The turn gets the biggest change of language (layout, palette, camera, pacing — e.g. chaos → still, night → day, page → full-bleed).
- The ending is a device of its own (freeze into a printed page, iris out, stamp, page reveal of all panels, punchline inset) — vary it between videos.

## 3 · Device menu (pick by what the beat *means*)
| Beat type in the VO | Devices that tell it |
|---|---|
| hook / question ("pernah ga…", "kenapa…?") | close-up + punch zoom; 3D question marks; thought balloon; shock lines |
| a message / notification / phone | phone screen insert (chat, notif, saldo, lock); balloon popping out of the phone; buzz arcs; `ring` sound |
| list / options / "tiga hal" | 3-up or 4-up page, panels slam on each item's word; strip3; grid4 with numbers |
| comparison / ekspektasi–realita / before–after | twoCol or diagonal split; same set in two palettes; slash transition |
| escalation / panic / overthinking | vertigo dolly zoom; speed lines; freeze-into-panel; tunnel stage; shaking panels |
| anger / conflict | burst stage + low angle; stomp + impact star; SfxText ("BRAK!"); impact frame |
| guilt / pressure / being judged | spotlight stage + high angle; shrinking (squash); balloons that shatter |
| loss / ending / "tamat" | panel shatter; cliff stage splitting; ink flood; halftone wipe to black |
| time / money / numbers | calendar, clock, money, wallet, atm, phone balance; big number lettering; coins / kaching sound |
| reveal / punchline ("padahal…", "ternyata…") | hard cut to calm daylight; locked-off symmetrical camera; caption box; freeze + inset of the other person |
| advice / CTA ("jadi…", "coba…") | page panel with the hero pointing; stamp ("WAJIB!", "JANGAN"); iris out |
| inner thoughts / imagination | zoom-through a thought cloud into a page; stage looks (paper, dots, void) |

## 4 · Variety rules (the lint enforces the hard ones)
- **Layouts:** ≥ 3 screen layouts per video (full-bleed, a page of 2–4 panels, an inset, a printed end page…). Do not run the same layout for more than ~4 s without a camera or panel move.
- **Transitions:** ≥ 3 different panel moves (slam, slide, iris, expand, collapse, fall, slash, shatter, zoom). Never the same transition three times in a row.
- **Places:** ≥ 2 different sets or stage looks. Remix presets every time (wall colour, items, time of day) — no two videos share an untouched preset.
- **Shots:** a new picture every 2.5–4 s; alternate shot sizes (wide / medium / close / insert) and camera moves (push, pull, dolly zoom, crane, orbit, whip, locked-off). One locked-off still shot is a strong device — use it once, on purpose.
- **FX:** mix kinds (lettering, bursts, speed lines, captions, stamps, shock lines, flash, impact frame, halftone wipe). Lettering: 1–3 words, at most one hit per 2.5 s, Indonesian spelled correctly, inside the safe zone (x 64–920, y 230–1450).
- **People:** choose looks for this story (LOOKS presets or new `look({...})`: hair, outfit, colours, build). Don't default to the example cast.
- **Palette:** every set gets a dominant pair from the six core colours; avoid the same pair in consecutive beats.

## 5 · Invent the elements this script needs (`elements.tsx` — at least one)
Ask: *which object, place or symbol would a comic artist draw for THIS text?* (a "SALDO Rp 0" ATM, a ringing alarm clock with legs, a kos room door with a number, a deadline monster made of sticky notes, a gerobak, a giant red "SEEN" stamp, a wedding invitation…). Build it from inked primitives in `src/pv-stories/<id>/elements.tsx` with `registerItems` ([new-elements.md](new-elements.md)), list its name in `story.elements`, and place it where it carries a beat (not as decoration in a corner). Check it on its own in a still before building the film around it.

## 6 · Time everything on words
Use the clock: `W.at("word")`, `W.at("dia", 2)` (2nd occurrence), `W.end("word")`, `W.s(seconds)`. Actions land on the **key word** (the verb/noun that names the action), not on the first word of the sentence; transitions land on the word that starts the new idea; loud hits sit in the VO's pauses (the ducking protects words, but a hit on a pause is stronger).

## 7 · Self-check before stills
- The film reads with the sound off; every VO line has something happening; no beat sits on silence > 3 s.
- Every lettering word is in the script's meaning; no full sentences on screen.
- `node scripts/pv-make.mjs src/pv-stories/<id>/story.ts --lint` passes; read the warnings and fix the ones that matter.

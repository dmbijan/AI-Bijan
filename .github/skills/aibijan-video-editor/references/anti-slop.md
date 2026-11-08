# Anti-slop: what makes an edit look AI-made, and what a real editor does instead

"Slop" is an edit that is technically fine but feels generated: effects with no reason, text everywhere,
sounds on everything, graphics that repeat what was said, the same move over and over. Viewers feel it in a
second and scroll. This skill's promise is the opposite: **every element on screen has a job, and the
person stays the star.**

`plan-edit.py` checks most of this automatically and prints `SLOP:` lines; `verify-output.py` reports them
again. **A plan with SLOP lines is not finished.** Fix the plan (or, in rare cases, explain in one line to the
user why a flagged choice is intentional).

## The test for every element
Before adding anything, answer: *what does this make clearer, at this exact word?* If the answer is "it looks
cool" or "the frame felt empty", don't add it — use a camera move instead.

## Visual slop → what to do instead

| Slop | Why it reads as AI | Instead | Auto-check |
|---|---|---|---|
| Title showing the same words the captions show at the same moment | double text = no editor made a decision | show the 1–3 keyword(s) big and hide the captions for that window (done automatically for KineticTitle/EndCard) | ✔ auto-hides |
| Long sentences as graphics | a wall of text nobody reads on a phone | condense to the 1–4 words that carry the idea | ✔ |
| Words in graphics the speaker never said | invented claims; breaks trust | use transcript words, or text the user wrote (`"copy": "user"`) | ✔ |
| Text over the eyes or mouth | covers the most important thing in the frame | above the head, beside the face, or a card/split layout for that beat | ✔ |
| Emphasis chips on 1 word in 3 | emphasis stops meaning anything | ≤ 20 % of words, about one per 2–3 s, only words that carry the message | ✔ |
| An icon on every chip | clip-art feeling | icon only when it literally depicts the word (scissors on "potong") | ✔ |
| Emoji as decoration | the #1 tell of generated content | never; use typography and real footage | ✔ |
| 3+ text graphics at once | no focal point | one focal point per frame | ✔ |
| Same device 4× in a row (badge, badge, badge…) | template feeling | vary: title → steps → counter → back to face | ✔ |
| Same layout 3× in a row | no rhythm | full → card/split → full | ✔ |
| Same cut transition 3× in a row | preset feeling | alternate, or leave clean jump cuts | ✔ |
| Graphics on screen for 0.5 s | unreadable flash | ≥ 0.6 s + 0.3 s/word (min 1.2 s), otherwise drop it | ✔ |
| Too many graphics per minute | the voice gets drowned | style limit (IMPACT 16/min, CREATOR POP 18/min, MOGRAPH MIX 26/min, STORYTELLER 22/min, SYSTEM 12/min, EDITORIAL 12/min) | ✔ |
| Card/split layout with nothing in the empty half | looks broken | put the graphic there (priority 1) or keep full frame | ✔ |
| Bursts, flashes, light leaks everywhere | cheap | Burst ≤ 1 (hook or CTA); flashes ≤ 3 per 10 s; leaks only in calm styles | ✔ |
| Shake, glitch, spin, RGB split, page-curl as decoration | the "free preset pack" look | eased camera moves with real motion blur; a `shake` only on THE impact word, a `glitch` only on a digital word or hard turn (≤ 2 of each per 30 s, ≥ 3 s apart) | ✔ hits |
| Type behind the head on every line, doodles on every sentence | the trick stops being special | depth moments carry the motif: ~one per 5–6 s at most, doodles ≥ 3 s apart, never a ring + echo + doodles in the same second | ✔ monotony / doodles |
| Glow on every word, three gradients in one frame | neon-sign clutter, nothing stands out | one glowing word per beat, one gradient per frame, the rest plain white | style rule |
| A colour field all video long, or a field the same colour as the speaker's clothes | the trick goes flat / the body melts into the background | poster beats 35-55 % of the runtime, 1.5-5 s each; a field colour that contrasts with the clothes | ✔ CONTRAST? |
| Coins with invented amounts, cards with photos the user never gave | made-up facts / stock-photo feeling | symbols only (Rp, %), numbers only when spoken; cards use the user's photos, their own frames or kit objects | ✔ grounding |
| A ring or wall of words nobody said ("WOW • AMAZING •") | filler typography | ring and wall words are spoken words (1–4) | ✔ grounding |
| Notes on every sentence, a label that never changes, strips holding sentences, a big word every few seconds | the margin turns into wallpaper, nothing is a note anymore | a `HeadNote` per section (retyped when the topic changes), 1–3 words per strip, 2–5 strips, an index card only for a real list, 1–2 `BigNote`s per video | ✔ note lint (length, count, reading time) |
| Labels the speaker never said ("TIPS PENTING", "WAJIB TAU") | invented framing | head notes and tags are spoken words or user copy | ✔ grounding |
| Somebody else's clips shown as "my results" | dishonest | `NoteClips` / `BRoll` only with the user's own media | rule |
| Every style looking the same (white card + lime) | no point of view | each style has its own caption look, motion language and finish | by design |
| Fake UI that looks like a real product | misleading | stylised, generic illustrations only | rule |

## Template slop (why two different videos end up looking the same)
| Slop | Why it reads as AI | Instead | Auto-check |
|---|---|---|---|
| Every face beat gets the same title in the same place | a preset, not a decision | titles only where the words deserve it; vary `layout`, `stack`, `fx`, height, or put the word behind the head | ✔ `same look` (3 identical) |
| One device all video long (8 HeroTitles, 6 Badges) | template feeling, the eye stops reading | at most `max(5, duration/8 s)` per type; give beats other devices | ✔ `monotony` |
| Scenes picked from the style's list instead of from the script | the same phones / prompt / checklist on every topic | find the script's own image (concept) and draw it: custom scene or a borrowed element | ✔ `NOTE: CONCEPT?` |
| Nothing only this video has | two buyers' videos look interchangeable | one signature moment: custom scene, word behind the head, Cutout, the user's b-roll | ✔ `NOTE: SIGNATURE?` |
| Identical transitions in every video | preset pack | covers vary per video automatically; pin them only on purpose | by design |

## Rhythm slop
| Slop | Instead | Auto-check |
|---|---|---|
| Nothing changes for 6 s on a talking head | something visual changes within the style's `maxStatic` (IMPACT 2.6 s … EDITORIAL 4.2 s): punch-in, layout change, graphic, cut transition | ✔ slow pushes inserted automatically, rest reported |
| Nothing moves in the first 1.5 s | a punch-in or the hook title in the first 1.5 s | ✔ hook push added automatically |
| Punch-in on every sentence | punch only on the strongest word of a beat (one per 4–6 s) | style config |
| Ums, "eh", "anu" left in | always cut (automatic). Keep a deliberate pause before a punchline. | ✔ automatic |
| A restarted sentence left in ("jadi hari ini aku— jadi hari ini aku mau…") | cut the abandoned attempt | ✔ `RETAKE?` suggestion, you confirm |

## Sound slop
| Slop | Instead | Auto-check |
|---|---|---|
| A sound on every word | one sound per meaningful visual event, never two within 0.25 s | ✔ density limit per style |
| SFX over a key spoken word | sounds sit in pauses or under transitions | judgment |
| Music louder than the voice | bed ~12–18 LU under voice and ducked while speaking | automatic |
| Royalty-free-sounding loop with no ending | procedural bed with a hit/ring-out on the last word (`music.hit_at`) | plan it |

## Copy slop (what you write to the user)
- Report what was actually done, with numbers ("dipotong 13 detik jeda + 2 'eh'"), not adjectives ("edit yang luar biasa!").
- Never claim the footage became 60 fps "smoother"; never claim AI interpolation that didn't happen.
- If something was skipped or approximated, say it in one plain line.

## Final look-through (do this on the QC sheet, every time)
1. Cover the captions with your thumb: does each graphic still make sense with the audio?
2. Is the face visible and un-covered in every frame where the person speaks?
3. Is there one obvious place to look in each frame?
4. Would a professional editor be comfortable putting their name on it?
5. Put it next to another video in the same style: does anything besides the face and the words tell them apart?

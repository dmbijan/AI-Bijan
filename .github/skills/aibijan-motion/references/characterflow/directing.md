# Directing a voice-over (transcript → story.json)

The director engine does blocking, acting curves, camera, sound and captions. Your job is the part a human director does: decide **who is where, and what they do on which spoken word**. Work in this order.

## 1 · Understand the VO
Read all of `words.json`. Write for yourself (not the buyer), in 3–6 lines:
- the situation (who, where, what goes wrong / what is learned),
- the turn (the "tapi…", "padahal…", "jadi…" moment),
- the ending feeling (relief, laugh, advice, warmth).

## 2 · Choose the place(s)
Pick the environment from the situation ([environments.md](environments.md) has the table): `campus`
(walking, meeting someone), `bedroom` (alone, night thoughts, phone in bed), `classroom` (school, exams),
`office` (work, boss, HR), `cafe` (friends, a date, nongkrong), `livingroom` (family, home). Time: `night` for
sleep / anxiety / late work, `day` otherwise, `sunset` for a warm ending (campus, classroom).
- One situation → one environment. A VO that jumps between situations or lists examples ("di rumah,
  sekolah, kantor…", "salah kirim chat… ditolak gebetan…") → `scenes`, one place per idea, each ≥ 2.5 s,
  cut on the word that names the new situation; come back to the opening place for the ending if it fits.
- Nothing fits (a hospital, a beach, a market): use the closest place and say so honestly in one line.

## 3 · Cast (1–2 people per place)
- One person: the "kamu/kita/aku" of the VO. Two people only when the VO has another person (teman, dia, orang lain, bos, ibu, pacar). With scenes the story may have up to 4 people (e.g. the hero, a friend, a parent, a boss), at most 2 in one scene.
- Looks ([characters.md](characters.md)): main person `raka`, `naya`, `dinda` or `sari` (hijab); others `bima`, `dimas`, `boss`, `ibu`, `ayah`, `adit` (pupil). Fit the place (a pupil in the classroom, a boss in the office, ibu/ayah at home); `outfit` for a hijab, a uniform colour or to tell two people apart. Vary looks between videos.
- Props: `phone` if anyone scrolls/chats/checks HP; `bag` if someone fiddles with a bag; `both` if both happen. Only give props that are used.

## 4 · Beats — one per idea, every 2–4 seconds
Go sentence by sentence. For each, pick the action that shows the line literally or emotionally ([actions.md](actions.md)) and cue it on the **word where the action should start** (usually the key verb/noun, not the first word of the sentence).
- Start strong: the first beat on the first 1–3 words (a walk, a think with a thought bubble, an overwhelmed, a surprised).
- Alternate body actions with face actions; avoid the same action twice in a row.
- Two people: give the other person reactions too (`notice`, `lookBack`, `embarrassed`, `smile`), use `eyeContact` for "mata kalian bertemu", `pass` for "lewat begitu saja", `meet` to end up face to face, `highFive`/`wave` for greetings.
- Advice part of a VO ("jadi…", "coba…", "mulai sekarang…"): `standUp` (if sitting) → `explain` → `point` → `proud`/`smile`/`calm`.
- Questions in the VO ("kenapa…?") → `think` or `confused` with a short **thought** (≤ 6 words) or an icon.
- Feelings: worry/pressure → `overwhelmed`; regret → `facepalm`/`sad`; joy → `happy`/`laugh`; relief → `calm`.
- Leave ≥ 1.2 s between two beats of the same person (shorter windows are merged by the camera).
- Seated people act from the seat (talk, think, phone, laugh…); `standUp` / `sitDown` only when the VO moves them.

## 5 · Extras (optional, use sparingly)
- `thought` bubbles: text (short, conversational, the character's inner voice), an `icon`, or a `vignette` (tiny scene inside the bubble). Max ~1 per 6 s.
- `highlights`: 3–6 key phrases from the VO, exactly as spoken — they light up in the captions.
- `cards`: a title card for a time jump ("5 DETIK KEMUDIAN…") or a chapter; 1–2 per video at most.
- Comedy (campus only): `board` = a pop-up sign that comments on the situation (title + 2 sides), `tumbleweed` = awkward silence gag right after an awkward moment.
- `mood`: the music baseline — `playful` (default, comedy/relatable), `warm` (wholesome), `tense` (pressure), `calm` (night, reflective). The music also follows the actions automatically.

## 6 · Self-check before building
- Every cue word is spoken in the VO (`"dia#1"` = the 2nd "dia"); dashes and punctuation dropped ("tiba-tiba" → `"tibatiba"`).
- Each line of the VO has something happening on screen; no beat sits on silence > 4 s.
- The story makes sense with the sound off.

Then build with `--stills` and look ([qa.md](qa.md)).

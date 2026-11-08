# Visual ideas: turning any script into pictures

The style decides how things look. This file decides **what to show**. Use it in "Direct it first" for every script,
whatever the topic. The answer is almost always a `SceneBuild` (the scene kit: figures, 48 objects, tags, bubbles,
bars, counters, charts, arrows, steps, crowds - see `creative-plan.md`), composed from JSON, no code.

## 1. Find the picture (2 minutes, before any JSON)
1. **Underline** the concrete nouns, the numbers, the verbs of change (naik, turun, hilang, muncul, pindah), the people
   (aku, kamu, dia, semua orang, pelanggan) and the "bayangin / gimana kalau" premise.
2. **Name the pattern** of each beat with the table below (comparison, number, money, time, process…).
3. **Literal before clever.** "Pesanan numpuk" → boxes piling up. "Privasi paling mahal" → a price tag on the word
   Privacy. The viewer must get it in half a second with the sound off.
4. **One motif for the whole video.** Pick the image that carries the premise (search bars above heads, a price tag, a
   growing plant) and let it return and evolve: introduce → apply to others → turn it on the viewer → callback at the
   end. Recurring motif = the video feels designed; a new random icon every beat = slop.
5. **Mute test.** Cover the captions: does each scene still say its line? If not, change the picture, not the size.

## 2. Pattern → picture → kit recipe
Recipes are starting points; mix kinds freely. `x, y` = centre on 1080×1920; keep text between y ≈ 330 and 1110.

| Script pattern (Indonesian cues) | Picture | Kit recipe |
|---|---|---|
| Premise / "bayangin", "gimana kalau" | the premise made literal on or around the speaker | face mode: `thing`/`bar`/`bubble` in `slot: above`; or scene: `figure` + `above` |
| Everyone / "semua orang", "banyak yang" | a crowd, all the same, then one stands out | `crowd` (`bars: true` or labels) + `highlight` one, camera zooms in |
| You and someone / "kamu ketemu", "pasangan", "pelanggan" | two people facing each other, something between them | two `figure`s (`accent` + `main`), `thing` heart / question / chat between, `above` on one |
| A number / "3 cara", "90%", "10 ribu orang" | the number big, plus the unit made visible | `counter` (count up on the word) + `thing`s repeated, or `meter` for %, `bars` for comparisons |
| Money / "mahal", "murah", "harga", "untung" | a price tag, falling coins, a wallet | `tag` (`hang`, `idle: swing`, `stamp` on the word), `thing` money/coin/wallet with `move: drop` |
| Comparison / "dibanding", "vs", "lebih" | a scale tipping, two cards side by side | `thing` scale with `tilt`, or two `card`s left/right with `thing` check / cross |
| Time / "cepat", "lama", "deadline", "tiap hari" | a clock racing, an hourglass, a calendar | `thing` clock / hourglass / calendar, `camera` zoom on the deadline |
| Growth / "naik", "laris", "berkembang" | bars rising, a plant growing, a rocket | `bars` with `ats` on each word, `thing` plant `move: grow`, chart, rocket `move: slide` up |
| Decline / "turun", "rugi", "capek" | chart going down, battery draining | `thing` chart `trend: down`, battery `level: 0.15` |
| Process / "caranya", "langkah", "pertama… kedua…" | numbered steps lighting up one by one | `steps` (`items[].at` on each spoken step) + one `thing` per step |
| Cause → effect / "karena", "jadinya" | A, an arrow, B | `thing` A left, `arrow` drawn on "jadinya", `thing` B right |
| Problem → solution / "masalahnya… solusinya" | warning → check, before → after | `thing` warning `until` the turn, then check `move: pop`; `frame` grade 0 vs 1 |
| Selling / "pesanan", "orderan", "pelanggan chat" | phone with chat, boxes, a truck leaving | `thing` phone + `bubble`s, `card` "Pesanan masuk" sliding, `thing` box `move: drop`, truck `move: slide` |
| Secret / "rahasia", "privasi", "diam-diam" | a lock, an eye watching, a magnifier | `thing` lock / eye, `SceneLens` over hidden words, door `open` |
| Idea / "tips", "ternyata", "kuncinya" | a bulb above the head | face mode: `thing` bulb `slot: above`, `idle: float` |
| Question / "kenapa", "bingung", "gimana" | question marks, a thought bubble, a shrug | `thing` question `slot: above-left`, `bubble style: thought`, `figure pose: shrug` |
| Risk / "bahaya", "hati-hati", "takut" | warning sign, bell ringing | `thing` warning / bell `move: slam`, Shout or MoodGrade in STORYTELLER |
| Win / "berhasil", "menang", "akhirnya" | trophy, a cheering figure, stars | `figure pose: cheer` + `thing` trophy / star `move: pop` |
| Place / "rumah", "keluar", "toko", "lokasi" | house, open door, a store, a pin | `thing` house / door `open` / store / pin |
| Learning / "belajar", "kelas", "tutorial" | a book opening, a play button, steps | `thing` book / play + `steps` |
| Content / "konten", "viral", "posting" | the speaker inside a phone, hearts, bell | `frame device: phone` (live footage), `thing` heart / fire / bell popping around it |
| Choice / "pilih", "mana yang" | two doors / two cards / a scale | two `thing` door, or two `card`s, `arrow` to the chosen one |
| A quote / "dia bilang", "katanya" | a speech bubble on a figure | `figure` + `bubble` (the spoken words), or `bar` typing it |
| Searching / "nyari", "googling", "nanya AI" | a search or prompt bar typing | `bar` (`text`, `typeAt`), `icon: "search" / "send" / "sparkle"` |
| Product features / a spoken list | one card per feature, sliding in on its word | 2–4 `card`s with `thing`, `move: slide`, `from` alternating; or SceneFeatures |
| CTA / "cek link", "follow", "di bawah" | a chip + arrow pointing down, the brand | `chip` + `arrow` down; EndCard |

Nothing in the table fits? Combine two rows (crowd + price tag = "everyone pays for it"), or write a custom component
(`references/custom-components.md`) for that one signature image.

### Depth pack: when the picture lives on the speaker's head (v3.7)

Some lines are best shown *on* the person instead of beside them - the premium Reels look. Pick these when the line is
about the speaker's own mind, a loop, a shock, or a word that deserves to be huge.

| Script pattern | Picture | Element |
|---|---|---|
| The shock word / "lah kok…", "ternyata segini", "gratis?" | the word slams in huge behind the head, echoes fan out, the frame shakes | `DepthText` `echo` + camera `shake` (+ MoodGrade shock in STORYTELLER) |
| A number or a short claim / "cuma 5 menit", "3 hari" | small lead word + huge number behind the head, rising out of masks | `DepthText` `stack` (`looks: outline, fill`) |
| A loop, a habit, "lagi dan lagi", "terus", "tiap hari", "nggak ada habisnya" | the words orbit the head forever / a wall of the phrase keeps sliding | `HeadRing` (1–4 words) or `DepthText` `wall` (`ramp` = it gets worse) |
| Endless content / "scroll", "feed", "video berikutnya", "satu lagi" | an endless wall of video cards behind the speaker, a card snaps up on every cue word | `FeedWall` (`frames_src: "auto"`, `swipes` on the cue words, `ramp`) |
| Two-part reveal / "bukan X, tapi Y", "sebelum… sesudah", "waktu kamu" | two words part from behind the head to the edges | `DepthText` `split` (`tones` two colours) |
| Realisation / "ternyata", "baru sadar", "oh iya" | three marker sparks pop at the side of the head | `Doodles` `sparks` |
| Hooked, dizzy, overwhelmed / "susah berhenti", "pusing", "kebanyakan" | a spiral or circling stars draw above the head | `Doodles` `swirl` / `stars` |
| Curious, confused / "penasaran", "kok bisa", "bingung" | a hand-drawn question mark beside the head | `Doodles` `question` (or Floaters `?` in 3D) |
| Idea / "kuncinya", "tips", "cara gampangnya" | a drawn bulb with rays above the head | `Doodles` `bulb` |
| This person, me / "aku dulu", "orang kayak aku" | a loose circle drawn around the head, an arrow with a spoken label | `Doodles` `circle` / `arrow` + `label` |
| Stress, chaos / "ribet", "stres", "deadline" | a scribble cloud above the head, sweat drops | `Doodles` `scribble` / `drops` |
| A digital or hard turn / "infinite scroll", "algoritma", "error" | one RGB-split glitch hit on the word | camera `glitch` |

Rules: one depth trick per beat (never a ring + an echo + doodles in the same second), the head should cover only the
lower part of type behind it, ring words must be spoken, and the signature is usually the wall / feed / ring that
carries the motif. Type behind the speaker reads best on plain walls; on a busy background use `FeedWall` (it replaces
the background) or a `Cutout`.

### Premium pack: when the line deserves a polished object (v3.9)

| Script pattern | Picture | Element |
|---|---|---|
| Money, profit, "untung", "omzet", "%" | glossy coins with the symbol floating beside the head | `Glossy` coin (`symbol: "Rp"` / `%`), 2-4, mixed depth |
| A result / yes / "langsung jadi", "berhasil", "gratis" | a pill confirming it with a check badge | `GlowPill` (`badge: "check"`) |
| A mistake / no / "jangan", "asal", "salah" | a red cross object or a pill with a cross | `Glossy` cross or `GlowPill` (`badge: "cross"`) |
| Bugs, problems multiplying | red crosses popping one after another around the head | `Glossy` cross × 2-3 |
| A theme, a world, "dunia X", "semua tentang" | icons of that world in an arc over the head | `BadgeOrbit` (Icons or kit objects) |
| Examples, "kayak X dan Y", products | cards flying in beside the head: the user's photos, their own frames, or kit objects | `SideCards` |
| A spoken list, "ada A, B, C", a recap | rows with check badges over the blurred frame | `Checklist` (2-4 rows) |
| A chapter, "pertama / kedua", a rule's name | a poster beat: colour field + two-tone title with echoes (POSTER), or a glowing number + kicker (LUMEN) | `Cutout bg: poster` + `DepthText` stack with `echo`; or `DepthText` stack `look: lumen` with `align` left/right alternating |
| A brand / product / tool name | the name glowing with a script word ("gratis", "pro") | `DepthText` stack: kicker + gradient name + `font: "script"` |

### Note pack: when the explanation belongs above the head (v3.10)

| Script pattern | Picture | Element |
|---|---|---|
| The topic of a section, a chapter, "nah sekarang…" | the topic typed above the head in mono, retyped when the topic changes | `HeadNote` (`mode: "new"`) |
| A list spoken in one breath ("A, B, C, D") | the label grows item by item: `A · B · C` | `HeadNote` items with `mode: "add"` |
| The hook word, the one word that carries the video | the word huge, highlighted like in a notebook, with a small margin note and an arrow | `BigNote` (`kickerPos: "right"`), behind the head when there is wall |
| Things named in a sentence (2–5): tools, genres, ingredients, names | strips of tape laid above the head on each word | `NoteTags` (tape), optional mono `label` ("CUMA DARI") |
| A question or the steps of "gimana caranya?" | outlined labels stacking beside the head | `NoteTags` `surface: "outline"`, `slot: "right"` |
| "Dulu X… tapi sekarang Y", "bukan X tapi Y" | X crossed out with a marker, Y laid as a yellow strip | `NoteTags` with `strikeAt` on X + `tone: "accent"` on Y, `pointer: true` |
| A real list or process ("ada 3 kelas", "prosesnya: A, B, C, D") | a ruled index card taped over the blurred frame, numbered lines typed in | `NoteBoard` (2–8 lines), `strikeAt` on the turn |
| Examples the user has media of ("dari horor, sejarah, sampai…", "ini hasilnya") | their clips taped as cards around the head, each labelled | `NoteClips` (1–3), or full-frame `BRoll` cuts with the `HeadNote` on top |
| The answer, the result, the CTA | one yellow strip with a hairline to the head | `NoteTags` `tone: "accent"` + `pointer: true` |

## 3. Composition rules (what makes a kit scene look designed)
- **One hero per frame, big**: hero object 320–460 px (figures 480–620 px tall), supporting objects 150–220 px, labels
  36–42 px. Build the composition around y ≈ 700–900 so it fills the space between the safe top (≈330) and the
  captions (≈1110). At most ~6 items on screen; leave a third of the frame empty.
- **Inside the frame**: keep every item ≥ 60 px from the edges, including after a camera zoom (zoom toward the hero,
  not toward an edge); bars above a figure slide inward automatically.
- **Build on the words**: each item's `at` is the word it illustrates; stagger 0.1–0.3 s; the hero arrives last.
- **Move with intent**: `drop` for weight (money, boxes, tags), `slide` for arrival/departure (people, trucks),
  `pop` for small news (hearts, checks, notifications), `grow` for growth, `slam` for one shock word, `rise` for
  everything else. `idle: float` on things in the air, `swing` on hanging tags, `pulse` on hearts.
- **Camera**: `camera: [{at: "w:23", zoom: 1.35, x, y}]` pushes into the hero on the punch word; one camera move per
  scene is enough.
- **Colour roles**: `accent` = the subject / the viewer / the thing that matters, `main` = everyone else, `soft` /
  `faint` = background crowd, `accent2` = the second party. Keep it to these.
- **Text**: graphics carry pictures; captions carry sentences. Labels are 1–3 spoken words.

## 4. Three scripts, three different videos (same style)

**"Orderan numpuk tiap hari, admin cuma satu"** (UMKM) - motif: boxes.
```json
{"type": "SceneBuild", "scene": true, "start": "w:3-0.05", "end": "w:12-0.08", "items": [
  {"kind": "thing", "name": "phone", "x": 540, "y": 620, "size": 300, "at": "w:3", "move": "rise"},
  {"kind": "bubble", "text": "orderan", "x": 760, "y": 470, "at": "w:3+0.3", "move": "pop", "tone": "accent"},
  {"kind": "thing", "name": "box", "x": 420, "y": 960, "size": 200, "at": "w:5", "move": "drop"},
  {"kind": "thing", "name": "box", "x": 640, "y": 960, "size": 200, "at": "w:5+0.25", "move": "drop"},
  {"kind": "thing", "name": "box", "x": 530, "y": 790, "size": 200, "at": "w:5+0.5", "move": "drop"},
  {"kind": "figure", "pose": "shrug", "x": 860, "y": 980, "size": 360, "at": "w:9", "move": "slide", "from": "right", "label": "admin"}],
 "camera": [{"at": "w:9", "zoom": 1.15, "x": 700, "y": 880}]}
```
**"Nabung 10 ribu sehari, setahun jadi 3,6 juta"** (finance tips) - motif: a growing plant of coins.
```json
{"type": "SceneBuild", "scene": true, "start": "w:0", "end": "w:11", "items": [
  {"kind": "thing", "name": "coin", "x": 540, "y": 520, "size": 180, "at": "w:1", "move": "drop", "label": "10 ribu"},
  {"kind": "thing", "name": "calendar", "x": 300, "y": 860, "size": 180, "at": "w:3", "move": "pop"},
  {"kind": "arrow", "from": [400, 860], "to": [660, 860], "at": "w:6", "tone": "accent"},
  {"kind": "thing", "name": "plant", "x": 800, "y": 860, "size": 260, "at": "w:6+0.3", "move": "grow"},
  {"kind": "counter", "value": 3.6, "decimals": 1, "suffix": " juta", "x": 540, "y": 1060, "size": 150, "at": "w:8", "countAt": "w:8", "countDur": 1.2}]}
```
**"Gue nggak setuju kalau kerja keras pasti sukses"** (opinion) - motif: a scale that will not tip.
```json
{"type": "SceneBuild", "scene": true, "start": "w:4", "end": "w:14", "items": [
  {"kind": "thing", "name": "scale", "x": 540, "y": 700, "size": 420, "tilt": 0, "at": "w:4", "move": "rise"},
  {"kind": "chip", "text": "kerja keras", "x": 330, "y": 930, "at": "w:6", "move": "pop", "tone": "ink"},
  {"kind": "chip", "text": "sukses", "x": 760, "y": 930, "at": "w:9", "move": "pop", "tone": "accent"},
  {"kind": "thing", "name": "question", "x": 540, "y": 420, "size": 150, "at": "w:11", "move": "pop", "idle": "float"}]}
```
(Every label is a spoken word; word refs point at the words that bring each item.)

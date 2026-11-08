# AI BIJAN MOGRAPH — named looks (v1.6 + v1.7)

The skill stays **adaptive**: every video gets its own storyboard. What v1.6/v1.7 add is eight **named looks**, each a complete
visual and sonic language you can commit to for one video: palette, type, world/camera grammar, signature moves,
captions, music bed and SFX. Six of them ship as code kits in the starter (`src/styles/`). Read the section of the look
you pick before building; it is the difference between "uses the colours" and "looks like the demo".

| Look | Pick it when the story is… | Typical script signals | Ships as |
|---|---|---|---|
| **MIDNIGHT** | a what-if simulation, money, privacy, apps, social systems, suspense | "Kalau besok…", "bayangin", balances, notifications, rules changing | v1.5 component kit + `theme.ts` (`C`) |
| **MINIATUR** | places, cities, environment, crowds, a policy that changes a whole area | streets, weather, neighbourhoods, "semua orang di…" | `IsoCity` + layered 2.5D (SKILL.md §6) |
| **ARSIP** | history, origins, myths vs facts, true stories, "siapa pelakunya?", records and dates | years, kings/founders, "katanya…", "ternyata", documents, cases | `src/styles/arsip/kit.tsx` |
| **BLUEPRINT** | how something works: machines, infrastructure, safety systems, technology internals | "cara kerjanya", parts, "pertama… kedua…", engineering numbers | `src/styles/blueprint/kit.tsx` |
| **SKETSA** | psychology, habits, feelings, social behaviour, studies about people, warm reflective topics | "pernah nggak…", "kenapa kita…", experiments, everyday moments | `src/styles/sketsa/kit.tsx` |
| **HIKAYAT** | legends, folklore, Nusantara history, origins of places, myth vs science | "dahulu kala", kingdoms, heroes, curses, "konon", lakes/mountains with a story | `src/styles/hikayat/kit.tsx` |
| **GEOMETRIK** | money, economics, business, statistics, rules of thumb, comparisons | percentages, "dibagi", "kali lipat", prices, growth, inflation | `src/styles/geometrik/kit.tsx` |
| **TERMINAL** | scams, data privacy, AI, hacking awareness, apps and systems behind the screen | OTP, password, akun, "di layar si…", login, data bocor | `src/styles/terminal/kit.tsx` |

**Rules (zero-prompt):**
- Pick the look yourself from the script. **Never ask "which style?"**. If the buyer wrote "pakai gaya ARSIP" / "style blueprint" / "gaya sketsa" (any spelling), that look wins.
- One look per video. You may borrow a building block from another look only after redrawing it in the chosen language (a chart in SKETSA is a hand-drawn `Pie`, not a clean SVG chart).
- When two looks fit equally, choose the one whose **signature move can show the payoff** (a stamp verdict → ARSIP, a mechanism locking → BLUEPRINT, a feeling made visible → SKETSA, a legend played out → HIKAYAT, a number that changes everything → GEOMETRIK, what happens on the other screen → TERMINAL). ARSIP vs HIKAYAT: an *investigation* of history (evidence, suspects, verdict) is ARSIP; a *told* legend or chronicle (characters, wonder, a moral) is HIKAYAT.
- Name the look in the handoff ("Gaya: ARSIP") and say why in one line. Do not announce it before the video exists.
- Labels: a what-if keeps `SIMULASI`; a factual piece uses a topic label instead (ARSIP: "BERKAS · FAKTA", BLUEPRINT: "CARA KERJA", SKETSA: "PSIKOLOGI", HIKAYAT: "LEGENDA" while the legend is told and "SAINS"/"SEJARAH" when facts take over, GEOMETRIK: "FAKTA & ANGKA" + "ILUSTRASI · BUKAN SARAN KEUANGAN" for money math, TERMINAL: "EDUKASI · KEAMANAN"). Money and security pieces are education, never advice or attack instructions. Facts still go through the accuracy gate: myths and history are where errors hide.
- **SFX density, every look:** about 25–45 cues per minute. Not every block, card or line needs a sound: give one to each scene entrance, each key reveal and each payoff, and let the music carry the rest (buyer tests with 54 cues in 44 s sounded busy).
- **Cue words carefully.** `at('jadi')` returns the *first* "jadi" in the VO (maybe "berubah jadi…" at 13 s, not "Jadi, mana…" at 62 s). For common words always use a two-word phrase (`at('jadi mana')`) or an occurrence index (`at('jadi', 1)`), and check the printed word list.
- **Never land on an empty frame.** When the camera arrives at a station, page or panel, its main object is already there. The words reveal *details*, not the whole station. At every moment, content covers most of the safe area (y 170–1460). A big bare cork board, empty blueprint grid or blank page reads as a broken frame: size objects up instead of leaving the lower half empty.
- Brand kit: the brand name/handle/logo may appear (a tab, a header, the end card), but the look's palette stays. Do not recolour ARSIP paper or BLUEPRINT blue with brand colours unless the buyer asks.

## Shared helpers — `src/styles/shared.tsx`

| Helper | Use |
|---|---|
| `useTime()` | `{t, frame, fps}` with `t` in seconds |
| `p(t, a, b)`, `pb` (overshoot), `pio` (in-out) | 0→1 progress between two times (seconds); the whole animation language is built from these |
| `camera(t, startPose, moves)` + `<World pose offset h>` | one world, one camera. `Move = {from, to, x?, y?, s?, r?}`; put moves in the **pauses** between spoken phrases. `(x, y)` lands at screen centre |
| `shake(t, impacts, amp)` | decaying shake after stamp/clamp impacts; pass as `offset` to `World` |
| `drawOn(q)` | spread on any SVG stroke to draw it on (`pathLength` normalised). It sets `pathLength`, `strokeDasharray`, `strokeDashoffset` and `opacity`: never pass those props on the same element too (TypeScript error TS2783). Put an extra opacity on a wrapping `<g>`, and for a dashed line that draws on, reveal it with a growing `clipPath` instead |
| `roughLine`, `roughEllipse`, `roughRect`, `spline` | hand-drawn geometry, seeded (deterministic) |
| `noise(freq, octaves, seed, size)` | tileable SVG noise as a CSS background (paper, desk, cork, grain) |
| `PAGES` / `pagesFor(words)` / `pageAt(t, pages)` | word-synced caption pages from `src/words.json`; every look's `Caption` takes `pages` |
| `<StyleFonts />` | registers the six style fonts; render it once next to `<FontFaces />` when a named look is used |

Fonts (all SIL OFL, licences in `public/fonts`): ARSIP `'Arsip Head'` Playfair Display 400–900 + `'Arsip Type'` Courier Prime 400/700 · BLUEPRINT `'BP Head'` Space Grotesk 300–700 + `'BP Mono'` IBM Plex Mono 500/600 · SKETSA `'Sketsa Head'` Caveat Brush + `'Sketsa Hand'` Patrick Hand · HIKAYAT `'Hik Head'` Cinzel + `'Hik Text'` Cormorant Garamond · GEOMETRIK `'Geo Head'` Archivo (variable width: `fontStretch` 62–125%) + `'Geo Text'` Inter · TERMINAL `'Term Head'` VT323 + `'Term Mono'` JetBrains Mono. Each look uses exactly its two fonts.

Preview the kits: `npm run style-preview` → `out/styles/sheet.jpg` (composition `StyleGallery`, never delivered).

---

## ARSIP — case file / documentary

**Look.** Papers on a dark wooden desk under a warm lamp (vignette), manila folders, newspaper front pages, sepia polaroids with tape, index cards, rubber stamps, red-marker circles and crosses, push-pins and red string on a cork wall. Palette `A`: desk `#1B1712`, paper `#EEE5D0`, card `#F5F0E3`, manila `#CFAE6E`, ink `#1E1C19`, red `#C22F26`, highlighter `#FFD54A`.

**Type.** Playfair Display 900 for headlines and names (newspaper voice); Courier Prime for every label, typed note and caption.

**World and camera.** One `World` holds a desk area and a tall cork wall. Group beats into **stations** (≈1300–1500 world px tall, content kept between screen y 170 and 1460). The camera pans between stations in VO pauses and pushes in slowly inside one. For a verdict, pull back over the whole wall (`s ≈ 0.24`) for about a second with a screen-space `Stamp` on top, then dive into the final station.

**Signature moves.** Headlines print line by line (`Reveal`); typed notes follow the voice (`TypeOn`); the key verdict word slams a `Stamp` with `shake`; `EvidenceTag` and yellow evidence markers point at the clue; a new suspect or link draws red string between `Pin`s; `Ring` / `MarkerX` draw on with the word they judge.

**Kit (`src/styles/arsip/kit.tsx`).**
- Surfaces: `Sheet` (paper with grain and aged edges), `Card` (drops in with `q`), `Tape`, `Pin`, `Polaroid` (taped photo, `label`), `TextBars` (fake newspaper columns).
- Type: `TypeOn` (typewriter with caret), `Reveal` (masked line).
- Marks: `Stamp` (`id` unique, `lines`, `at`; slams in, ink broken by noise, paper fill keeps it legible), `Ring`, `MarkerX`, `EvidenceTag`.
- Pictures: `Bust` (generic profile silhouettes: `helmet`, `beard`, `laurel`, `fringe` on a height chart), `Mugshot` (polaroid + bust + placard), `NameCard`, `CrowdPhoto` (halftone crowd), `CalendarPage` (wall calendar with day count, dashed missing days, evidence markers, circled day).
- `Caption`: torn paper strip, typed text, yellow highlighter on the spoken word (`top` default 1500).

**Sound.** `npm run style-sfx -- arsip` → `type`, `stamp`, `paper`, `scroll`, `pin`, `marker`, `string`, `bell`, `thud`, `shutter`, `tick`, `whoosh`. Map one-to-one: `TypeOn` → type, `Stamp` → stamp, sheet lands → paper, polaroid → shutter, pin → pin, `Ring`/`MarkerX` → marker, red string → string, a total/answer → bell, camera travel → whoosh. Bed: `npm run style-score -- arsip …`: noir pizzicato walking bass, clock ticks and vinyl crackle in D minor; `suspense/tense/dramatic` sections get eighth notes, `reflective/warm` drop the bass, a `hopeful` section resolves to major. Use `hopeful` for "kasus ditutup".

**Pitfalls (learned on the demo and in buyer tests).** Every station needs one big anchor (a polaroid, document or calendar 700–900 px wide) that is visible when the camera arrives: a header card alone looks empty. Not every card needs a sound: one `paper`/`shutter` per station entrance plus the stamps and typed notes, about 25–45 cues per minute, paper at gain 0.3–0.45. A stamp over a dark photo vanishes: put it on paper or rely on its paper fill. A stamp must not cover the words it judges: place it beside them. Card titles need `whiteSpace: 'nowrap'` and a size that fits. Captions sit at 1500, so keep station content above 1460 on screen. The whole-wall pull-back reads as a thin strip: keep it short. Silhouettes stay generic: never draw a real person's likeness.

## BLUEPRINT — technical drawing

**Look.** Blueprint paper `#0E3A6B` with a 36/180 px grid, white linework that draws itself, cyan secondary lines, **yellow for the part being explained**, red-orange for failure and warnings. A title-block header bar, numbered callouts, dimension lines, hatched sections, break marks, phantom (dashed) lines for hidden parts, and DETAIL panels.

**Type.** Space Grotesk 700 for big statements; IBM Plex Mono for every label, spec and caption.

**World and camera.** One big drawing (for example a 4–5k px tall section through the object), **detail `Panel`s beside it on the same sheet** (x 1180–2160 or negative x), and an archive sheet for history. The camera travels between them in pauses; a full pull-back (`s ≈ 0.26–0.3`) shows "the whole thing" and the summary checklist.

**Signature moves.** Draw-on in reading order; explode and re-assemble (`IsoBox` z offsets); a `Callout` per named part, synced to the word; `Dim` for numbers; `BreakMark` for failure; phantom lines to show what is hidden; a ✓ checklist at the end.

**Kit (`src/styles/blueprint/kit.tsx`).**
- Geometry: `isoAt(cx, cy, k)` (iso projection), `poly`, `seg`, `line(q, w, color)` (stroke props that draw on).
- Parts: `IsoBox` (filled faces, visible edges draw on, hidden edges dashed; `topInset` for keycap/frustum shapes), `Hatch` (concrete or section hatch that wipes in), `BreakMark`, `Figure` (architectural scale person).
- Annotation: `Typed`, `Callout` (dot, elbow, numbered balloon, typed label; `side` left/right), `Dim`, `Panel` (titled detail frame).
- `Caption`: mono box with crop marks, the spoken word yellow and underlined.

**Sound.** `npm run style-sfx -- blueprint` → `blip`, `scribe`, `confirm`, `drop`, `whir`, `clank`, `snap`, `chop`, `spring`, `alarm`, `whoosh`. Map: a line draws → scribe, a callout appears → blip, a spec confirmed → confirm, failure → snap or alarm, a mechanism locks → clank, a spin-up → whir. Bed: plucked arpeggio + soft kick/hat + sub bass (A minor → C major for `hopeful`); `suspense/tense` remove the kick and lift the arpeggio with ticks; add `styleHits` `{"type":"hit"}` on the payoff click and `{"type":"riser"}` before it.

**Pitfalls.** SVG text in the world scales with the camera: a title shown at a pull-back needs 120–150 px world size and must fade before you zoom in. Callout labels near the frame edge get cut, so keep elbows at x 300–900 when `s ≈ 1`. Isometric front faces hide the centre column: explode further or draw phantom lines. The header bar sits at y 96–158, so keep sheet titles below ~200 on screen. The mechanism must be real: use "biasanya/umumnya" where designs vary.

## SKETSA — living notebook

**Look.** Lined paper `#FBF7EA` (rules every 66 px, red margin), pen ink `#1F2433`, yellow highlighter swipes, blue/coral marker hatching, stick figures, hand-drawn bubbles and charts. **Every drawing layer boils** (`Boil` filter, new seed every 5 frames), and at frame 0 a **pencil underdrawing** (`PENCIL`) of the page's main shapes is already there; ink follows the voice.

**Type.** Caveat Brush for titles and exclamations (`Write` reveals them like handwriting); Patrick Hand for labels and captions.

**World and camera.** Mostly static pages: one page ≈ one idea (5–15 s). At an act change, the next page slides up over the last (0.55 s, `pio`, slight rotation, the old page darkens). Inside a page, scale or pull back groups with `Sc` instead of moving a camera.

**Signature moves.** Handwritten title + highlighter swipe; stick figures at 1.4–1.6× (the default 200 px figure is small on 1080×1920); thought bubbles that replay the problem; hand-drawn `Pie` comparisons; a `Spotlight` cone for attention/feelings; a pencil-to-ink reveal at the start.

**Kit (`src/styles/sketsa/kit.tsx`).**
- Page: `Page` (lined paper), `Boil` (filter: one unique `id` per page), `pen(q, width, color)` (ink stroke that draws on), `PENCIL`.
- Writing: `Write` (clip reveal, `anchor`), `Swipe` (highlighter).
- People and things: `Stick` (`style` from `HAIR`: spiky, bun, cap, long, curl, scarf; `pencil` for underdrawing; `wave`), `Sc` (scale a group about an anchor), `Hand` (pointing hand), `Cheeks` (blush + sweat), `Bed`, `Head` (small head that looks up "!" or down at a phone).
- Diagrams: `Bubble` (speech or `thought`), `Pie` (hatched wedge), `Spotlight` (lamp + cone, `power` to dim).
- `Caption`: handwriting on a paper wash with a highlighter on the spoken word (`top` default 1490).

**Sound.** `npm run style-sfx -- sketsa` → `scribble`, `flip`, `pop`, `squeak`, `boing`, `ding`, `chime`, `switch`, `bubble`, `tick`, `whoosh`. Map: drawing → scribble, page flip → flip, a doodle appears → pop, highlighter → squeak, a comic beat → boing, a reveal → ding, a warm ending → chime. Bed: lo-fi electric piano, brushes and a plucked melody in F; `playful/curious` get drums and melody, `reflective/warm` keep piano and pad, `tense` thins out to piano only.

**Pitfalls.** Doodles at default size leave pages empty: fill 60–70% of y 170–1460. Keep lamps, bubbles and labels clear of the page title (title baseline ~292). Put labels under figures, not above lamps. One `Boil` id per page, or two pages fight over one filter.

## MIDNIGHT and MINIATUR

These are the v1.5 looks, now named. **MIDNIGHT**: navy `#0D1233` / cream / yellow (`C` in `theme.ts`), Plus Jakarta Sans, the UI/money/chart component kit, `SIMULASI` badge, a dark editorial world with bright reveals. **MINIATUR**: the bright isometric miniature world (`IsoCity`, layered 2.5D, SKILL.md §6) with weather, crowds and maps. Both follow SKILL.md §3–§7 directly.

## Reference demos — `references/examples/`

Three complete episodes built with these kits: `ARSIP_Februari-Dirampok.tsx` (68 s, desk → cork wall with 4 suspects → verdict), `BLUEPRINT_Tali-Lift-Putus.tsx` (62 s, lift-shaft section + DETAIL A–E + 1854 archive sheet), `SKETSA_Lampu-Sorot.tsx` (54 s, five notebook pages), `HIKAYAT_Legenda-Danau-Toba.tsx` (69 s, the legend as wayang, then the volcano), `GEOMETRIK_Aturan-72.tsx` (55 s, the rule of 72 in blocks), `TERMINAL_Kode-OTP.tsx` (56 s, your phone vs the sender's terminal). Read them for **structure**: stations, camera moves in pauses, beats pinned to words, SFX cue lists. Do not reuse their stories, text or layouts.

## Building a named-look video (checklist)

1. Pick the look (table above) and read its section.
2. `npm run style-sfx -- <look>` once in the project.
3. In `Main.tsx`, render `<StyleFonts />` and build the scenes from the kit, timed with `at('phrase')`. Captions: the look's `<Caption t={t} pages={PAGES} />`, not `WordCaptions`.
4. Write `src/audio-plan.json` sections (mood + energy from the story) and `sfxCues` using `audio/sfx/<look>/<name>.wav`, then `npm run style-score -- <look> src/audio-plan.json public/audio/music/<name>.wav` (or `npm run score` if the generic bed suits the story better), set `musicFile`, `npm run auto-duck`.
5. QC on `Mograph` stills as usual. `style-preview` only checks the kit.

## HIKAYAT — shadow-puppet chronicle (v1.7)

**Look.** A lit **kelir** (screen) glowing amber from an oil lamp that flickers, a cloth weave texture, and **carved leather silhouettes** (wayang-like) whose perforations (*tatahan*) let the light through. Every puppet casts a soft second shadow, stands on a rod stuck into the banana-trunk stage at the bottom, and bobs gently as if a dalang holds it. The **gunungan** opens the play, sweeps across for scene changes and closes it. Everything written appears incised on **lontar** palm leaves. Palette `H`: glow `#F7C66C`/`#E08A36`, edge `#5E240C`, silhouette `#1B0D06`, leaf `#D9B678`, ember `#C2410F`. This is deliberately not the "textile + gold foil" look of other history channels: no fabric appliqué, no gold leaf.

**Type.** Cinzel (titles, names, numbers, incised on lontar) + Cormorant Garamond (labels, captions).

**World and camera.** No camera: the stage stays put, like a real kelir. Puppets enter and leave from the sides, scenery rises from below, and the gunungan wipes between acts. Darken the kelir (`Kelir dim`, cool `tint`) for night, storms and disasters; flash it warm for magic and explosions.

**Kit (`src/styles/hikayat/kit.tsx`).**
- Stage: `Kelir` (glow, weave, flicker, `dim`, `tint`), `Stage`, `HikDefs` (put once in every svg).
- Puppets: `Silhouette` (polygon `parts`, `perforate` step per part, `flip`, `rot`, `rod`, `extra` for solid ornaments), `GununganOrnament`, and a library of polygons: `GUNUNGAN`, `KING`, `THIEF` (also a commoner or fisherman), `WOMAN`, `CHILD`, `WOLF`, `FISH`, `HOUSE` (rumah adat), `MOUNTAINS`, `VOLCANO` ↔ `CALDERA` (same point count, so `lerpPts` morphs them), `ISLAND`, `SUN`, `QMARK`, `wave()` for water, `circlePts()`.
- Writing: `Lontar` (palm-leaf strip, `q` reveals it), `incised`, `lontarTitle()`, `lontarText()`, `Tally` (scratched counting marks).
- `Caption`: a lontar leaf with incised words, the spoken word in ember (`top` default 1488).

**Sound.** `npm run style-sfx -- hikayat` → `gong`, `kecrek`, `swoosh`, `kendang`, `chime`, `magic`, `water`, `splash`, `thunder`, `rain`, `flood`, `boom`, `rumble`. Bed: synthetic gamelan in slendro (saron, bonang, kenong, gong every 8 beats, kendang, suling). `reflective/warm` = saron + suling, `playful/curious` add bonang and kendang, `tense/dramatic` drop the melody for a low gong drone and kendang rolls; `styleHits` `hit` = a big gong.

**Pitfalls (learned on the demo).** Lontar text must be ONE line: use `lontarTitle`/`lontarText` (nowrap) and size the leaf to the text; a two-line leaf needs `h >= 150`, or the top line is cut. Perforations are generated on a lattice anchored at 0,0; keep that, or holes shimmer while a shape moves or morphs. Big scenery (mountains, house) goes behind the puppets at reduced opacity. The stage is never bare: when a beat has no character (a place name, "saudagar kaya"), show a prop or scenery puppet big in front instead of only the far mountains. Lontar swaps at the same spot: the old leaf must be fully gone (allow ≥ 0.3 s) before the next one appears, or the texts overprint ("BERDOA" over "…UR"). Mark the switch from legend to facts with the label (`LEGENDA` → `SAINS`).

## GEOMETRIK — Swiss/Bauhaus data (v1.7)

**Look.** Warm off-white page (`#F2EEE4`) on a strict 90 px grid, primary colour fields (red `#E5402E`, blue `#2149A6`, yellow `#F6C21C`, black `#121212`), **giant condensed numbers**, and shapes that *are* the data: a square is money, a row of blocks is years, a bar is a total, a red full-bleed flip is "the other way". Hard, snappy motion (`ps` easing), wipes and full-colour fields instead of camera moves.

**Type.** Archivo (variable: weight 900, `fontStretch` 62–100% for condensed headlines and numbers) + Inter (labels, captions).

**Kit (`src/styles/geometrik/kit.tsx`).** `GridPage`, `Big` (number/headline with `stretch`), `ps` (snappy progress), `Block` (colour block/circle that scales, slides from the left or up), `Abs` (positioned text), `label()` (tracked caps), `Caption` (black bar, the spoken word on yellow).

**Sound.** `npm run style-sfx -- geometrik` → `tick`, `slam`, `pop`, `click`, `swipe`, `wipe`, `flip`, `ding`, `rise`, `down`, `cut`, `thud`. Bed: marimba arpeggios with kick + clap in `bright` sections, a low pulse in `tense`, marimba only in `calm`.

**Pitfalls.** One idea per phase, cleared before the next (each phase owns the whole frame). Every phase needs one dominant shape covering roughly 40% or more of the frame (a giant number, a colour field, a big block or chart): a headline with one small square on an empty page reads as a broken frame. Numbers must be computed, not typed from memory. Label money math "ILUSTRASI · BUKAN SARAN KEUANGAN".

## TERMINAL — green-phosphor CRT (v1.7)

**Look.** A near-black CRT with scanlines, grain, vignette and dim hex rain; phosphor green `#3DFF8C` for text, amber `#FFB547` for warnings and codes, magenta `#FF3D7F` for alerts and the "other side". Bordered windows with title bars, commands typed with a block cursor, data as block-character bars (`█` / `░`), glitch slices on turns, ordered-dither pixel portraits, a radar for searching.

**Type.** VT323 (big pixel titles and numbers) + JetBrains Mono (everything else).

**Kit (`src/styles/terminal/kit.tsx`).** `Win` (window, `color`, `shake`), `Typed` (typing with cursor), `Bar` (block bar with missing cells), `PixelBust` (dithered portrait), `Phone` + `Bubble` (a terminal-styled phone and messages), `Lock` (open/closed padlock), `HexRain`, `Glitch`, `CRT` (render last), `glow()`, `Caption` (prompt line, the spoken word inverted).

**Sound.** `npm run style-sfx -- terminal` → `ting`, `msg`, `keys`, `key`, `blip`, `glitch`, `drone`, `ok`, `warn`, `lock`, `granted`, `drain`, `alarm`, `error`, `x`. Bed: dark synth, pulsing saw bass, square arpeggio and drums in `bright`, drone + ticks in `tense`.

**Pitfalls.** Synth transients push the true peak: keep SFX gains ≤ 0.6 and check `audio-qc` (the demo needed a limiter before mastering). Text in windows needs an explicit colour: an uncoloured label renders black on black. Security stories stay awareness-only: show what a code or password protects and the rule, never a working attack.

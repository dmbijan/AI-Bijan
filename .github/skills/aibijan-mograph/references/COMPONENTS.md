# AI BIJAN MOGRAPH — component kit and word timing

Ready-made, brand-neutral building blocks in the starter (`src/components/kit`, `src/components/WordCaptions.tsx`,
`src/lib/words.ts`). They make scenes faster and more consistent, but they are **parts, not scenes**: build an
original storyboard for every script and use a component only where it explains what the narrator says at that
moment. Never copy the `KitGallery` panels into a buyer's video.

## Conventions

- Import from `./components/kit` (paths relative to `src/`). Every component takes `t` = current time in seconds
  (`useCurrentFrame() / fps`) and start times such as `at`, `enterAt`, `changeAt` in seconds. No timers, no
  `Math.random` (use `seeded(n)`), so preview and render are identical.
- Canvas is 1080×1920. Unless noted, `x` is the **horizontal centre** and `y` the **top edge**. Keep text out of the
  bottom ~280 px and the top ~160 px.
- Brand-neutral: generic phone, bank and chat UI only. Never draw a real bank, wallet, marketplace or chat app, a real
  logo, or a realistic banknote. `Banknote` is deliberately stylised (no portraits, emblems or security features).
- Numbers: `formatRupiah(5000000)` → `Rp5.000.000`, `formatNumber(2.5, 1)` → `2,5`. Show only numbers from the script
  or clearly labelled simulations (`SimulasiBadge`).
- Preview the kit: `npm run kit-preview` → stills in `out/kit/` (composition `KitGallery`). Preview any composition
  with `npm run stills -- out/qc 1 2.5 --comp=<Id>`; with two or more times it also writes a labelled contact sheet
  `out/qc/sheet.jpg` — inspect that one image first.
- Sound effects: list them in `sfxCues` of `src/audio-plan.json`; `<SfxTrack />` in `Main.tsx` plays them.

## Word timing (`npm run align`)

```bash
npm run align -- --setup                                        # one time per machine/chat; repeat until MODEL READY
npm run align -- public/audio/voiceover.mp3 input/script.clean.txt
```

Creates a private Python environment (`.venv`, one time), downloads the ~350 MB alignment model once (cache shared
with AI BIJAN Video Editor) and writes `src/words.json` with the start/end of every script word (CTC forced alignment,
typically within 20–40 ms of the real speech). Outcomes:

| Output line | Meaning | What to do |
|---|---|---|
| `WORD TIMING READY` | verified word times | time scenes with `at()` and use `<WordCaptions>` |
| `WORD TIMING ESTIMATED` | model unavailable, proportional times | usable, but check sync by ear; tell the buyer once |
| `WORD TIMING NOT AVAILABLE` (exit 3) | no Python 3 / pip / network | keep beat-level timing; tell the buyer once; never block the video |

```tsx
import {at, endOf, hasWordTiming, timingIsVerified} from './lib/words';
const drop = at('lima ribu');            // seconds when "lima ribu" starts; throws if the phrase is not in the script
const calm = at('Tenang', 0, 9.5);       // with a fallback time when timing may be missing
const twistEnd = endOf('tidak hilang');
```

`<WordCaptions t={t} y={1470} />` shows 2–4 word pages that never cross a sentence end, highlights the spoken word and
dims the next ones. Props: `size` (54), `maxWords` (4), `highlight` colour, `box` (dark box, default) or plain text
with shadow, and `hidden={[[start, end], ...]}` to hide captions while a headline shows the same words. `Main.tsx`
switches to it automatically when `src/words.json` has words.

## Components

| Component | Use it for | Key props |
|---|---|---|
| `PhoneFrame` | anything that happens on a phone | `x`, `y`, `width` (520), `enterAt`, `tilt`, `dark`, children = screen |
| `BankAppScreen` | balance changes, payments, transfers (inside `PhoneFrame`) | `balanceFrom`, `balanceTo`, `changeAt`, `changeDur`, `note`, `transactions: [{label, amount, at, note}]`, `appName` (generic) |
| `NotificationCard` | a message or alert arriving | `at`, `until`, `y`, `app`, `title`, `body`, `time` |
| `ChatBubble` | conversations, reactions | `at`, `text`, `side` left/right, `x` (left edge, or right edge for right bubbles), `y`, `typing` seconds |
| `RupiahCounter` | the big number moment (Rp5.000.000 → Rp5.000) | `from`, `to`, `at`, `dur`, `y`, `size`, `showFrom` (struck-through old value) |
| `Banknote` | cash, wages, prices (stylised) | `value` (colour follows denomination), `x`/`y` = centre, `rotate`, `enterAt`, `width` |
| `PriceTag` | before/after prices | `at`, `x`, `y`, `label`, `value` |
| `BarChart` | comparing amounts over years or groups | `data: [{label, value, display?}]`, `growAt`, `stagger`, `highlight` index, `format`, `dark` |
| `LineChart` | trends that unfold over time | `points`, `labels`, `drawAt`, `dur`, `format`, `color`, `dark` |
| `PercentRing` | one share or probability | `value` 0–100, `at`, `x`/`y` = centre, `size`, `label` |
| `Thermometer` | weather, heat, cold | `from`, `to` (°C), `at`, `x`, `y`, `height`, `min`, `max` |
| `IndonesiaMap` | places, spread, routes across Indonesia | `y`, `width` (1000; height = 0.382 × width), `revealAt`, `pins: [{city, at, label, labelSide}]`, `route: {cities, at}`, `dark` |
| `KineticHeadline` | hooks and key statements | `text`, `at`, `y`, `size`, `highlight: ['word']`, `align` |
| `SimulasiBadge` | marking what-if or illustrative content | `text` (SIMULASI / WHAT IF / ILUSTRASI), `y`, `at` |
| `Callout` | pointing at the one thing that matters | `shape` circle/underline/arrow, `at`, `x`, `y`, `w`, `h`, `to: [x, y]` (arrow) — canvas coordinates |
| `PersonFigure` | everyday micro-stories (buyer, shop owner, viewer) | `pose` stand/wave/point/think/shock/phone/shrug/celebrate, `fromPose` + `poseAt` (smooth change), `mood` happy/neutral/surprised/worried, `hijab` colour, `skin`, `hair`, `shirt`, `flip` |
| `Warung` | street-level Indonesian commerce | `x`, `y`, `width`, `enterAt`, `colors`, `sign` |

`IndonesiaMap` cities (`CITIES`): Banda Aceh, Medan, Padang, Pekanbaru, Palembang, Jakarta, Bandung, Semarang,
Yogyakarta, Surabaya, Denpasar, Mataram, Kupang, Pontianak, Banjarmasin, Balikpapan, Nusantara (IKN), Samarinda,
Makassar, Palu, Manado, Ambon, Ternate, Sorong, Jayapura, Merauke. Other places: `pins: [{lonLat: [lon, lat], label}]`.
Put labels of eastern cities on the `left` and western ones on the `right` so they stay on screen. The outline comes
from Natural Earth (public domain).

## Example: a hook built from the kit

```tsx
const t = frame / 60;
const drop = at('lima ribu', 0, 2.4);
<>
  <SimulasiBadge t={t} y={170} />
  <PhoneFrame t={t} x={540} y={420} width={560}>
    <BankAppScreen t={t} balanceFrom={5000000} balanceTo={5000} changeAt={drop} note="Nilai tetap sama" noteAt={at('Tenang', 0, 10)} />
  </PhoneFrame>
  <WordCaptions t={t} />
</>
```

Vary the composition between beats (phone → map → chart → character), keep one focal point per frame, and check the
visual diversity gate in `SKILL.md`.

## Named-look kits (v1.6 + v1.7)

Six more kits live in `src/styles/` and pair with the named looks in `SKILL.md` §4A. Specs, props and pitfalls are in
**`references/STYLES.md`**; this is the map.

| Kit | Import | Parts |
| --- | --- | --- |
| shared | `./styles/shared` | `useTime`, `p`/`pb`/`pio`, `camera` + `World` + `shake`, `drawOn`, `roughLine`/`roughEllipse`/`roughRect`/`spline`, `noise`, `PAGES`/`pagesFor`/`pageAt`, `StyleFonts` |
| ARSIP | `./styles/arsip/kit` | `Sheet`, `Card`, `Tape`, `Pin`, `Polaroid`, `TypeOn`, `Reveal`, `Stamp`, `Ring`, `MarkerX`, `EvidenceTag`, `Bust`, `Mugshot`, `NameCard`, `CrowdPhoto`, `CalendarPage`, `TextBars`, `Caption` |
| BLUEPRINT | `./styles/blueprint/kit` | `isoAt`, `poly`, `seg`, `line`, `IsoBox`, `Hatch`, `BreakMark`, `Figure`, `Typed`, `Callout`, `Dim`, `Panel`, `Caption` |
| SKETSA | `./styles/sketsa/kit` | `Page`, `Boil`, `pen`, `PENCIL`, `Write`, `Swipe`, `Stick` (`HAIR`), `Sc`, `Hand`, `Cheeks`, `Bed`, `Head`, `Bubble`, `Pie`, `Spotlight`, `Caption` |
| HIKAYAT | `./styles/hikayat/kit` | `Kelir`, `Stage`, `HikDefs`, `Silhouette`, `GununganOrnament`, polygons (`GUNUNGAN`, `KING`, `THIEF`, `WOMAN`, `CHILD`, `WOLF`, `FISH`, `HOUSE`, `MOUNTAINS`, `VOLCANO`, `CALDERA`, `ISLAND`, `SUN`, `QMARK`), `wave`, `lerpPts`, `circlePts`, `Lontar`, `lontarTitle`, `lontarText`, `Tally`, `Caption` |
| GEOMETRIK | `./styles/geometrik/kit` | `GridPage`, `Big`, `ps`, `Block`, `Abs`, `label`, `Caption` |
| TERMINAL | `./styles/terminal/kit` | `Win`, `Typed`, `Bar`, `PixelBust`, `Phone`, `Bubble`, `Lock`, `HexRain`, `Glitch`, `CRT`, `glow`, `Caption` |

Same conventions as the component kit: time `t` in seconds, start times in seconds from `at('phrase')`, deterministic
(seeded) randomness, 1080×1920 canvas. SVG ids must stay unique (`Stamp id`, `Boil id`, `Hatch id`, `Pie id`, `Silhouette id`).
Preview: `npm run style-preview` → `out/styles/sheet.jpg` (`StyleGallery`, never delivered).

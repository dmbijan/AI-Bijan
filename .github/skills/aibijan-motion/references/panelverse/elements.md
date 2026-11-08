# Elements — what exists (and how to add your own)

See them: `node scripts/stills.mjs out/catalog 0,1,2,3,4,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24 PanelverseCatalog 0.5` renders the catalog pages (items, cast, expressions, sets, stage looks, layouts, FX) — look at them before writing a story. Story elements registered in `elements.tsx` appear in the catalog too.

## Library items (`set.items[].type` or prop `{ kind: "item", keys: [{ f, v: { type, pos, rot, scale, sw, sw2, text, on, n } }] }`)
Every item stands on y = 0 at its origin; wall items hang centred on their origin facing +z (back wall at z = −2.36). `sw`/`sw2` = palette swatches, `text`/`sub` = lettering on signs, `on` = a state (lit, ringing, open…), `n` = a count.

| item | what / options |
|---|---|
| `bed` | bed 3.4×0.7×2.2 (sw blanket, sw2 frame) |
| `sofa` | two-seat sofa 2.2×1.0×0.9, seat height 0.5 |
| `chair` | chair, seat height 0.5 (sw) |
| `stool` | round stool, seat height 0.5 |
| `table` | table 2.0×0.86×1.1 (sw top, sw2 legs); `on` = cloth drop to hide legs |
| `desk` | office desk 1.8×0.86×0.9 with drawer block |
| `counter` | shop / kitchen counter 2.6×1.0×0.7 (sw front, sw2 top) |
| `shelf` | bookshelf 1.2×2.0×0.4 with books |
| `wardrobe` | wardrobe / lemari 1.3×2.2×0.6 |
| `door` | wall door 1.0×2.3 (wall item; `on` = ajar) |
| `window` | wall window 1.3×1.5 (`on` = night sky with moon, else day sky with clouds; sw frame) |
| `frame` | wall picture frame 0.9×0.7 with a simple graphic (sw art colour); `text` = lettering instead |
| `poster` | wall poster 0.9×1.25 with star (sw) |
| `clock` | wall clock (t = seconds shown; n = hour hand position 0..12) |
| `calendar` | wall calendar page (text = big date, sub = month) |
| `sign` | standalone sign board with lettering (text, sub, sw board colour) on a post |
| `lamp` | desk lamp (stands on a table top) |
| `bulb` | hanging lamp (hangs down from its origin, 1.3 cord) |
| `plant` | potted plant 0.9 tall (sw pot) |
| `tv` | TV on a low cabinet (`on` = bright screen) |
| `fridge` | fridge 0.8×1.9×0.7 |
| `rug` | floor rug (flat, sw) |
| `laptop` | open laptop (`on` = screen lit) |
| `phone` | phone lying on a surface, screen up (text = notification text) |
| `book` | stack of n books |
| `mug` | mug (sw); `on` = steam |
| `plate` | plate of food (sw food colour: rice/noodle/bakso…) |
| `glass` | glass of iced drink with straw (sw drink) |
| `bottle` | sauce bottle (sw) |
| `money` | stack of n banknotes (sw note colour; text = value on top note) |
| `coin` | coin (stands upright) |
| `wallet` | wallet (`on` = flipped open and empty) |
| `bag` | shopping bag (sw, text = label) |
| `backpack` | backpack (sw) |
| `gift` | gift box (sw box, sw2 ribbon) |
| `envelope` | envelope / letter (stands upright; text = label) |
| `alarm` | alarm clock (`on` = ringing shake) |
| `trophy` | trophy |
| `umbrella` | open umbrella (sw) |
| `atm` | ATM / cash machine 0.9×1.8 (text = screen lettering) |
| `tree` | stylised tree 3.2 tall (sw foliage) |
| `bush` | bush 0.8 tall |
| `streetlamp` | street lamp 3.6 tall (`on` = lit) |
| `bench` | park bench 1.8 wide, seat 0.48 |
| `building` | building façade w×h (n = floors, sw wall, sw2 window, text = shop sign) |
| `car` | toy-like car 2.4 long (sw body) |
| `scooter` | motor scooter (parked) 1.7 long (sw body) |
| `question` | 3D question mark (sw) |
| `exclaim` | 3D exclamation mark (sw) |
| `star` | chunky comic star (sw) |
| `heart` | heart (sw) |
| `bolt` | lightning bolt (sw) |
| `arrow` | big comic arrow pointing up (rotate it; sw) |
| `flame` | stylised flame (sw outer; flickers) |
| `cloud` | puffy sky cloud (sw) |
| `rain` | rain cloud with falling drops (animated) |
| `sun` | comic sun with rays |
| `moon` | crescent moon |

## People — `cast`
`LOOKS.<name>` presets, or `look({ id, skin, hair, hairSw, top, topSw, innerSw, bottom, bottomSw, shoes, build, glasses, mustache, beard, tie, lashes, blush, browW })`:
- **skin** `skinA` (sawo matang) · `skinB` · `skinC` (light) · `skinD` (deep) — **hair** quiff · short · buzz · bob · long · ponytail · bun · curly · hijab · cap · bald (`hairSw` = hair, hijab or cap colour: hair, hairBrown, hairGrey or any swatch) — **top** tee · longsleeve · shirt · jacket · hoodie · tunic · dress — **bottom** pants · shorts · skirt — **build** kid · teen · adult · elder · big — **glasses** false or a swatch.

| preset | look |
|---|---|
| `raka` | id: "raka", skin: "skinA", hair: "quiff", top: "tee", topSw: "yellow", bottomSw: "blue", shoes: "red", browW: 1.1 |
| `dinda` | id: "dinda", skin: "skinB", hair: "hijab", hairSw: "teal", top: "tunic", topSw: "red", bottomSw: "navy", shoes: "white", lashes: true, browW: 0.85 |
| `sari` | id: "sari", skin: "skinC", hair: "bob", hairSw: "hair", top: "longsleeve", topSw: "teal", bottom: "skirt", bottomSw: "yellow", shoes: "ink", lashes: true, browW: 0.85 |
| `bima` | id: "bima", skin: "skinD", hair: "curly", top: "hoodie", topSw: "red", bottomSw: "dark", shoes: "white", browW: 1.15 |
| `ayah` | id: "ayah", skin: "skinA", hair: "short", top: "shirt", topSw: "sky", innerSw: "white", bottomSw: "dark", shoes: "brown", mustache: true, build: "big", browW: 1.2 |
| `ibu` | id: "ibu", skin: "skinB", hair: "hijab", hairSw: "pink", top: "dress", topSw: "purple", bottom: "skirt", bottomSw: "purple", shoes: "brown", lashes: true, browW: 0.85 |
| `bos` | id: "bos", skin: "skinC", hair: "short", hairSw: "hairGrey", top: "jacket", topSw: "navy", innerSw: "white", tie: true, bottomSw: "navy", shoes: "ink", glasses: "ink", build: "big", browW: 1.2 |
| `adik` | id: "adik", skin: "skinB", hair: "ponytail", top: "tee", topSw: "pink", bottom: "shorts", bottomSw: "blue", shoes: "yellow", build: "kid", lashes: true, blush: 0.4, browW: 0.8 |
| `nenek` | id: "nenek", skin: "skinB", hair: "bun", hairSw: "hairGrey", top: "tunic", topSw: "green", bottom: "skirt", bottomSw: "brown", shoes: "brown", glasses: "brown", build: "elder", lashes: true, browW: 0.8 |
| `ojol` | id: "ojol", skin: "skinD", hair: "cap", hairSw: "green", top: "jacket", topSw: "green", innerSw: "ink", bottomSw: "dark", shoes: "ink", browW: 1.1 |
| `kasir` | id: "kasir", skin: "skinC", hair: "ponytail", hairSw: "hairBrown", top: "shirt", topSw: "red", innerSw: "white", bottomSw: "dark", shoes: "ink", lashes: true, browW: 0.85 |
| `teman` | id: "teman", skin: "skinA", hair: "buzz", hairSw: "hairBrown", top: "jacket", topSw: "orange", innerSw: "white", bottomSw: "blue", shoes: "white", glasses: "ink", browW: 1.05 |

Make the cast fit the story (a boss in an office, ibu at home, a kid for a school memory) and vary looks between videos — change hair, outfit colours, build, glasses.

## Expressions — `EXPR.<name>` (then override any field)
`angry` · `bliss` · `calm` · `cold` · `concerned` · `confused` · `cry` · `determined` · `embarrassed` · `guilty` · `hopeful` · `laugh` · `love` · `neutral` · `sad` · `shocked` · `sleepy` · `smug` · `starry` · `worried` · `yum`

## Sets
`{ ...SET_PRESETS.<name>, wall: "teal", items: [...] }` — always remix (colours, items, time).

| preset | kind |
|---|---|
| `bedroomNight` | interior |
| `warung` | interior |
| `cafe` | interior |
| `office` | interior |
| `classroom` | interior |
| `kitchen` | interior |
| `livingroom` | interior |
| `street` | exterior |
| `park` | exterior |
| `shop` | interior |

**interior** `{ kind: "interior", floor, floorDots, wall, wallDots, wainscot, sideWall: none|left|right|both, items }` · **exterior** `{ kind: "exterior", time: day|sunset|night, ground, road, items }` · **stage** `{ kind: "stage", look, a, b, items }` with looks `burst` (rays, `spin`), `spotlight` (interrogation lamp), `split` (two-colour versus), `tunnel` (speed rings), `paper` (blank page), `dots` (halftone field), `cliff` (`gap`, `tilt`, `drop` + anchors `cliff-left` / `cliff-right` for actors), `void`.

## Story props (shot `props`, actor `torso` / `head` / `heldR` / `heldL`)
`item` · `phone` (screen: chat / notif / balance / lock) · `bubble` (`content`: text / squiggle / dots / icon ?, !, ?!, …, zzz, heart, Rp) · `shatterBubble` · `sign` (inked card with lettering) · `thoughtCloud` · `thoughtTrail` · `steam` · `wisps` · `heart` (crack / split) · `bowl` (bakso) · `spoon` · `hearts` · `buzz` · `impactStar`.

## Layouts, transitions, FX, sounds
Layouts: splash · page · twoUp · threeUp · fourUp · grid4 · twoCol · bigTop · bigBottom · strip3 · diagonal · inset(corner). Transitions: enter.cut / slam / slide / iris / slash, move.expand / collapse / fall / drift / zoom / irisOut / shatter. FX: impact · caption · burst · speed · flood · flash · sfxText · impactFrame · stamp · shock · halftoneWipe. Sounds: [sound.md](sound.md).

## Your own elements
Anything the script needs that is not here — build it in the story's `elements.tsx` ([new-elements.md](new-elements.md)). That is expected for every video.

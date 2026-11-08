# Making the story's own elements (`elements.tsx`)

Every PANELVERSE video gets at least one element designed for its script (the lint checks it). Build it in `src/pv-stories/<id>/elements.tsx` from the same inked, cel-shaded primitives as the library, so it matches the style exactly. Never load images, models or fonts from the internet.

```tsx
import { registerItems } from "../../kits/panelverse/engine/three/sets.tsx";
import { Toon } from "../../kits/panelverse/engine/three/Toon.tsx";
import { G, starShape, roundedRectShape } from "../../kits/panelverse/engine/three/geometries.ts";
import { Sign, Phone } from "../../kits/panelverse/engine/three/props.tsx";
import { LIB } from "../../kits/panelverse/engine/three/library.tsx"; // reuse items inside yours: LIB.mug.render({ f })

registerItems({
  struk: {                      // a long shopping receipt that unrolls (t = 0..1)
    desc: "receipt unrolling from a hand (t = 0..1)",
    render: ({ t = 1, text = "TOTAL Rp 487.000" }) => (
      <group>
        <Toon geo={G.roundedBox(0.36, 0.06, 0.06, 0.02)} sw="grey" outline={3} position={[0, 0, 0]} />
        <group scale={[1, Math.max(0.02, t), 1]}>
          <Sign text={text} font="letter" w={0.32} h={1.2} sw="white" position={[0, -0.62, 0]} depth={0.01} />
        </group>
      </group>
    ),
  },
});
```
Then list `"struk"` in `story.elements` and use it as a set item `{ type: "struk", pos, … }` or a keyed prop `{ kind: "item", keys: [{ f, v: { type: "struk", pos, t: 0 } }, { f: W.at("total"), v: { t: 1 }, ease: "out" }] }` (any field — `t`, `on`, `n`, `text`, `sw` — can be animated).

## Building blocks
- `<Toon geo sw mat outline position rotation scale />` — one mesh with cel shading, halftone and its own ink contour. `sw` = a palette swatch (`"red"`, `"teal"`, `"paper"`, `"metal"`, `"skinA"`…, see `theme/palette.ts`). `outline` = ink width in px (2–5; 0 for flat graphic details like pupils, lettering bars). `mat`: `{ flat: true }` (graphic fill, no shading), `pattern: 0.1–0.4` (all-over Ben-Day dots), `spec: 1, specSize: 0.03–0.08` (glossy highlight), `cut` (shadow amount: −1 none … 0.3 more), `side: "double"` (open shells), `dotOnly` (dots only: shadows, light beams, blush).
- Geometry (`G.*`): `sphere()`, `sphereLo()`, `cylinder(rTop, rBot, seg)` (unit height, centred), `limb(rTop, rBot)` (unit height from 0 to 1), `capsule(r, len)`, `cone(seg)` (`cone(3)` = triangle, `cone(4)` = diamond), `roundedBox(w, h, d, r)`, `torusArc(radius, tube, arc)`, `sphereCap(key, thetaStart, thetaLen)`, `lathe(key, [[r, y], …])` (bottom → top, vases, bowls, bottles), `extrude(key, () => shape, depth, bevel)` with `starShape(r1, r2, n)`, `roundedRectShape(w, h, r)` or your own `three` `Shape`, `tube(key, points, r)` (wires, steam, hair strands), `heart()`, `circle()`, `plane()`. Cached geometry needs a **unique key** per shape.
- Ready parts: `<Sign text sub w h sw font />` (inked card with lettering), `<Phone screen={…} />`, `LIB.<item>.render({ f, sw, on … })`.
- The render function receives `{ sw, sw2, text, sub, on, n, t, f }` (`f` = shot frame for idle motion such as wobble, flicker, steam).

## Rules
- **Scale:** a standing person ≈ 2.7 tall, head ≈ 0.8, hand ≈ 0.2, table top 0.86, door 2.3. Stand the element on y = 0 (wall items: centred, facing +z).
- **Shapes:** rounded, chunky, few parts (5–20 meshes). Smooth geometry gives clean ink; avoid razor-thin parts (< 0.015) — they flicker.
- **Layers:** never stack shells of nearly the same size (two lathes or spheres a few mm apart) — their ink hulls fight and flicker. Build layered things as separate bands / parts that touch, or scale the outer one ≥ 6 % larger.
- **No big near-white fills:** large flat areas in `"white"` or `#fff`-like colours filling > 12 % of the frame trip the "blown highlights" QA. Use `"paper"` for big surfaces (pages, screens, cards); keep `"white"` for small accents.
- **Colour:** the six core colours first (ink, paper, yellow, red, blue, teal), one or two accents. Lettering only via `Sign` (bundled comic fonts) and spelled in Indonesian correctly.
- **Meaning:** the element must carry a beat (the thing the VO talks about, a metaphor, a gag). Place it where the camera sees it, big enough to read on a phone.
- **Check it alone first:** register it, run `node scripts/stills.mjs out/catalog 0,1,2,3,4,5,6,7 PanelverseCatalog 0.5` (story elements appear on the last item pages) and look before building the film around it.
- Never edit `src/kits/panelverse/` for one video; everything specific lives in the story folder.

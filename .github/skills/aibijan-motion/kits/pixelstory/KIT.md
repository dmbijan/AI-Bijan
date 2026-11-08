# PIXELSTORY kit — cinematic 2.5D pixel art

`node SKILL_DIR/new-project.mjs [folder] --kit=pixelstory` copies the starter plus this kit's `src/` into the
project's `src/` and `public/` into `public/`. It adds `src/kits/pixelstory/` and four OFL fonts in `public/fonts/` (Anton, Instrument Serif,
Silkscreen, Space Grotesk; licences in `public/fonts/licenses/`). No WebGL: sets are pixel buffers relit on the
CPU and drawn as nearest-neighbour canvases, so it renders the same on any machine.

Every function is a pure function of the frame (no `Math.random`, CSS animation or timers). The engine counts
**film frames at 60 fps**: compute `const f = (frame / fps) * 60` and pass `f` everywhere, so the 30 fps
preview and the 60 fps final play identically. The stage is 1080 × 1920 with **8 screen px per art px**
(`PX`), i.e. 135 × 240 art px; wrap the scene in a 1080 × 1920 div scaled by `width / 1080` (see the example).

| Folder | Content |
|---|---|
| `core/` | `C` palette tokens and `RAMPS` (8-step hue-shifted material ramps: wood, plaster, paper, metal, books, leaf, sky, amberLamp, teal, dusk, mesa, stone…), `mixHex`, `rgba`; `ease.*` named by intent (camera, arrive, depart, lock, anticipate…), `ramp`, `keys`, `springFollow`, `onTwos`; seeded `rng`, `hash`, `noise1`, `fbm1`, `bayer` |
| `pixel/` | `sprite(rows)` from ASCII, `stamp`, `recolor`, `flipX`; `finish(fill, spec)` (sel-out outline + form shading from one upper-left key); `rasterize` with directional rims; `SpriteView`, `AlbedoView`, `PixelCanvas`, `PX` |
| `environment/` | `createAlbedo` + `painter` (put, rect, line, disc, ring, poly, shade) — paint a set once as material + value (+ facing, + emission); props: `plankWall`, `beam`, `post`, `brace`, `nightWindow`, `shelf`, `books`, `desk`, `monitor`, `deskLamp`, `mug`, `pothos`, `keyboard`, `sketchbooks`, `floor`, `rug`, `hangingLeaves`; `templateOf` / `compositeTemplate` / `TemplateAlbedoView` (a blueprint version of a set that comes alive behind a moving front) |
| `lighting/` | `relight` (point and cone lights quantised into the ramps with Bayer dither), `spriteLightsAt` / `spriteGainAt` (sprites receive the same lights), overlays `Glow`, `LightShaft`, `Vignette`, `Grain`, `Grade`, `Exposure` |
| `camera/` | `CinematicCamera` + `Layer z` (real parallax and depth-of-field blur per layer), `cameraAt` keyframes per channel, `drift`, `follow`, `project` |
| `typography/` | `useFonts()`, `FONT.{display, serif, pixel, sans}`; `MaskRise`, `LightSweep`, `PixelType`, `Counter`, `Crumble` |
| `particles/` | `DustMotes` (only inside a light cone), `Steam`, `Trail`, `Debris` |
| `character/spark.ts` | A small glowing spark sprite (an effect, sizes 0–4) |
| `example/` | `cat.ts` — an example character built with the character pattern; `PixelstoryExample` — an 8 s moonlit room (two lights, walking cat, dust in the shaft, dolly with depth of field, pixel type) |

## Register the example

```tsx
import { PixelstoryExample, PIXELSTORY_EXAMPLE_SECONDS } from "./kits/pixelstory/example/PixelstoryExample.tsx";

<Composition id="PixelstoryPreview" component={PixelstoryExample}
  durationInFrames={PIXELSTORY_EXAMPLE_SECONDS * preview.fps}
  fps={preview.fps} width={preview.width} height={preview.height} />
```

Then `node scripts/stills.mjs out/pixel 2s,5s,7.8s PixelstoryPreview`.

## Building a film

1. **Sets.** One `createAlbedo` per depth layer (back wall, floor, near foreground), painted once in a
   `useMemo` with the props and painter. Paint value (`val`) as material lightness and let lights decide the
   colour; add `shade()` contact shadows where surfaces meet; give emitters (`emit`) to windows and lamps.
2. **Lights.** A small list per frame: cones for windows and lamp shades, points for bulbs and pools of light.
   The same list relights every layer (`AlbedoView`) and every sprite (`spriteLightsAt`, `spriteGainAt`), so a
   moving light really moves through the room. Keep `ambient` low (0.1–0.2): darkness is part of the look.
3. **Characters.** Flat fills per part → `finish` with a material per fill (`base/shade/light/out`, outline a
   dark version of the material, never black) → memoised frames → a pose function (`catAt` in the example).
   Walk cycles on twos or fours; blinks every few seconds. Every film gets its own cast — the example cat is
   only the pattern.
4. **Camera.** Layers at real distances (`z` 700 near, 1000–1150 subject, 1500+ background); dolly `z`, pan
   `x/y`, `focus` on the subject layer, `aperture` 4–8. Put a character in the same layer as the floor it
   walks on so its feet stay planted during a dolly. Add `drift` for an operated feel.
5. **Atmosphere and type.** `LightShaft` + `DustMotes` only where a light motivates them; `Glow` on practicals;
   then `Grade`, `Vignette`, `Grain` over the whole frame. Type with `FONT.pixel` for pixel lines and
   `FONT.serif`/`FONT.display` for titles; left-anchor typewriter text so it does not slide while typing.

## Checks before showing a preview

- Pixels stay hard: sprites on the set's grid (`scale` = `PX`), no smooth scaling inside a layer.
- Every sprite reads against the set (rim from the nearest light, never black-on-black).
- Text sits clear of faces, windows and the bottom 20 % of the frame (platform UI).
- Stills at the start, the light change and the end; then a short clip to check walk cadence and flicker.

Known limits: lights do not cast shadows (paint them); relighting is per frame on the CPU, so very large
layers (> 200 × 300 art px) slow renders down; no rotation of sprites beyond flips.

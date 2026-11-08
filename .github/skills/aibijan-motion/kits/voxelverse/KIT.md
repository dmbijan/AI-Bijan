# VOXELVERSE kit — voxel miniature worlds

`node SKILL_DIR/new-project.mjs [folder] --kit=voxelverse` copies the starter plus this kit's `src/` into the project's `src/`. It adds `src/kits/voxelverse/`
and needs only `remotion`, `@remotion/three`, `@react-three/fiber` and `three` (already in the starter).

Time runs on **film frames at a 60 fps clock**: compute `const f = (frame / fps) * 60` once and pass `f` to
the camera, glow panes, rain and post, so the 30 fps preview and the 60 fps final play identically.
Scale is metres; the street centre is z = 0 and the plinth top is y = 0.

| Folder | Content |
|---|---|
| `lib/voxel.ts` | `Vox`: models authored as chamfered boxes (`box`, `span`, `rod`, `prism`, `prismAlong`) inside a transform stack (`at`), merged into one geometry per material class with per-box vertex colour and seeded `jitter` |
| `lib/kit.ts` | Architectural pieces: `brickWall` (running bond with clean openings), `slabWithHoles`, `windowFrame`, `sill`, `shrub`, `downpipe`, `herbs`, `ivy` |
| `lib/materials.ts` | Material classes by light response (`matte`, `satin`, `wet`, `metal`, `gloss`, `fabric`, `foliage`); instanced glow-pane material; `additive` halos; volumetric `makeConeMaterial` |
| `lib/components.tsx` | `VoxModel` (build once, shadows on), `GlowPanes` (every window/bulb as one instanced draw; each pane's `glow(f)` is a pure function) |
| `lib/textures.ts`, `lib/anim.ts`, `lib/palette.ts`, `lib/layers.ts` | Seeded canvas textures (`radialTex`, `streakTex`, `interiorAtlas` of lit interiors, `signTex`); `lookTarget`, `blink`, `hop`, `twoBoneIK`; palette `C` (copper, amber, teal, stone, cream, brick, wood, foliage); `FX_LAYER` |
| `world/Building.tsx` | Spec-driven buildings: `BuildingSpec` (width, depth, floors, brick/plaster/stone, window style ×6, roof ×5, shopfront ×7, extras: balconies, fire escape, shutters, AC, tank, plants, vent, chimney, clock tower); `buildingModel`, `buildingPanes`, `<Buildings list glow f/>` with a `GlowFn` deciding every window's light |
| `world/Plinth.tsx` | Diorama base: street, kerbs, paver walks, yards, zebra crossing, optional tram track, puddles, cut-away strata with pebbles and cable ducts, timber frame (`PlinthSpec`) |
| `world/Atmosphere.tsx` | `Sky` (dusk dome, `boost` to deepen), `Environment` (reflections), `Fog`, `DuskLight` (cool key with soft shadows + hemisphere) |
| `world/Rain.tsx` | World-anchored rain around the lens, diorama-wide rain for wides, ground ripples; `blocked(x,y,z)` keeps interiors dry |
| `camera/rig.ts`, `camera/Post.tsx` | Shots of timed keys (position, aim, fov) on Hermite splines with eased ends, focus on a world point, optional handheld drift → `cameraAt(shots, f)`; `CameraRig` + finishing chain (AO → sanitize → depth of field → bloom → tone map → grade); depth passes ignore `FX_LAYER` (rain, halos) |
| `core/` | `motion.ts` (`ease`, `ramp`, `keys`, `springFollow`…), `noise.ts` (seeded `rng`, `hash`, `fbm1`) |
| `example/` | `VoxelverseExample` — 8 s: a dusk street of seven buildings on its plinth, lamps switching on, windows lighting in a sweep, rain starting, one crane move from the walk up to the whole diorama |

## Register the example

```tsx
import { VoxelverseExample, VOXELVERSE_EXAMPLE_SECONDS } from "./kits/voxelverse/example/VoxelverseExample.tsx";

<Composition id="VoxelversePreview" component={VoxelverseExample}
  durationInFrames={VOXELVERSE_EXAMPLE_SECONDS * preview.fps}
  fps={preview.fps} width={preview.width} height={preview.height} />
```

Then `node scripts/stills.mjs out/voxel 0.3s,3.6s,5s,7.9s VoxelversePreview`.

## Building a world

1. **Layout first.** One module of world coordinates (plinth size, street, where each building stands,
   lamps, hero props) that every other module reads.
2. **Plinth and buildings.** `PlinthSpec` sized so buildings' depth fits inside it; one `BuildingSpec` per
   building — vary wall, window style, roof and shopfront so no two façades repeat. Place with
   `{ pos: [cx, 0, ±front], rotY: 0 | Math.PI }` (façade faces the street).
3. **Props and characters.** Author with `Vox` inside `VoxModel` builders (module-level functions — they
   are built once). Characters are voxel rigs: groups per body part posed from the frame (`twoBoneIK` for
   arms, `blink`, `hop`, `lookTarget`).
4. **Light as story.** `GlowFn` decides every window: a cue time, an order (a sweep, a wave, one room) and a
   switch-on with a flicker. Practical lights (`pointLight`) only where they matter; `Sky boost` and `Fog`
   follow the shot (thicker at street level, thinner for wides).
5. **Camera.** Few shots, each with 3–5 keys; the first and last keys ease. In 9:16 a street reads best
   at a 3/4 angle on one façade, or looking along the street from high up; finish wide enough to show the
   plinth's cut-away edge if the diorama is the payoff. Focus on the subject (`focus(f)`), aperture
   0.0004–0.002.
6. **Atmosphere last.** Rain (`amount`, `wide`), puddles, halos — after the composition works without them.

## Checks before showing a preview

- Stills at the opening, each light cue and the final hold; then a short clip for camera speed and rain.
- Nothing intersects (camera paths stay outside façades; props sit on walks, not in walls).
- Lit windows read as interiors (atlas), not flat cards; the wides still show readable silhouettes.

Known limits: one shadowed key light (practicals are unshadowed); rain is visual only (no wet-map changes
over time); very large worlds (> 60 × 60 m with full detail) slow the build step — keep far rows simple.

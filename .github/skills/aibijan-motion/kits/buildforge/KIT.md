# BUILDFORGE kit — structures assembled piece by piece

`node SKILL_DIR/new-project.mjs [folder] --kit=buildforge` copies the starter plus this kit's `src/` into the
project's `src/` and `scripts/` into `scripts/`. It adds `src/kits/buildforge/` and `scripts/buildforge-check.ts`; the example's labels use the
starter's bundled font (`src/core/type/fonts.ts`). Scale is metres.

| Folder | Content |
|---|---|
| `types.ts` | The vocabulary: a structure is a flat list of `Component`s — `id`, `phase`, `part` (one `solid` or many `pieces`), seat `at` + `rotY`, `material`, `method`, `rests` (what it bears on), `after` (procedural order), `key` (order inside the phase), `exit` (temporary works leave in that phase), `finish` (surface changes when a sweep passes), `ghost`, `shadow` |
| placement methods | `lower` (crane, rig single/sling/four, hook and cables lift away), `slide` (carried in along an offset), `fill` (rises in its form, optional curing), `spread` (pour front across a slab; geometry subdivided along the axis), `drive` (stakes), `draw` (string lines), `grow` (planting), `coat` (finish coat rising behind a front), `lay` (pieces one by one: blocks, boards, slats, pavers) |
| `assembly/schedule.ts` | `schedule(components, windows)` → per-component timeline from phase windows; `validate()`; `phaseEnd()` |
| `assembly/check.ts` | `checkSequence()` + `report()`: order, clashes between installed solids, finish before the end — no GPU |
| `assembly/motion.ts`, `render/Site.ts` | Pure per-frame state of every component (pose, wet concrete, coat front, blueprint ghost, crane rigging) and the `Site` that applies it |
| `world/createWorld.ts` | One call builds sky, sun with soft shadows, graded terrain, the site on its timeline, existing and scattered trees; `update(c, look)`; `sunLook()` |
| `environment/` | `Sky` (gradient sky + environment light), `Terrain` (`SiteSpec`: levelled site, excavation pit and depth, slab footprint, planting beds, gravel band; `TerrainState`: contour, grade, dig, backfill, lawn), `Vegetation` (`roundTree`, `columnTree`, `shrub`, `hedge`) |
| `geometry/` | `box`, `cast` (rounded precast edges), `panel`/`panelBoxes` (walls with openings), `masonry` (block courses as pieces), `merge`, `sub`; `glazing`, `pivotDoor`, `balustrade`; `noise`, seeded `rng` |
| `materials/` | `createLibrary(looks, sweeps)`: wet concrete, coat front, finish sweep, lamps, glass; procedural concrete/wood/gravel textures |
| `presets/daylight.ts` | Default `LOOKS` (concrete, precast, block, plaster, render, wood, plywood, steel, frame, glass, gravel, paver, foliage, lamp…), `SKY`, `FOG`, `GROUND`, `LEAF` |
| `camera/director.ts`, `motion/track.ts` | Spherical camera rig (`[clock, tx, ty, tz, azimuth°, elevation°, distance, fov°]`) sampled from C1 keyframes with `keys`/`sample`; `shotAt(rig, clock, lens)` |
| `render/Post.tsx` | `CameraRig` + finishing chain: MSAA → ground-truth AO → sanitize → depth of field → bloom → neutral tone map → grade |
| `labels/StageLabel.tsx` | Numbered stage caption + progress rail (frames; the example converts from seconds) |
| `example/` | A 6 × 4 m steel-and-timber garden pavilion (`pavilion.ts`), its 10 s sequence, terrain timing, stage labels and camera (`sequence.ts`), and `BuildforgeExample` |

## Register and check the example

```tsx
import { BuildforgeExample, BUILDFORGE_EXAMPLE_SECONDS } from "./kits/buildforge/example/BuildforgeExample.tsx";

<Composition id="BuildforgePreview" component={BuildforgeExample}
  durationInFrames={BUILDFORGE_EXAMPLE_SECONDS * preview.fps}
  fps={preview.fps} width={preview.width} height={preview.height} />
```

```
node --experimental-strip-types scripts/buildforge-check.ts
node scripts/stills.mjs out/build 1.7s,4.2s,5.6s,7.0s,9.9s BuildforgePreview
```

## New structure in 6 steps

1. **Plan** (`src/structures/<name>/plan.ts`): grid lines, levels, footprint, openings — one source of truth
   every component reads, so nothing drifts.
2. **Components** (`components.ts`): one function per construction stage returning `Component[]`, in the
   order a real crew builds (set-out → excavation → footings → slab → frame → floors → walls/roof →
   openings → finishes → landscape). Give every load-bearing element its `rests`; temporary works an `exit`.
   Rigid parts: build geometry in local coordinates with the seat at the centre of the base (see `elem` in
   the example). Repeated small parts: `pieces` + `lay`.
3. **Sequence** (`sequence.ts`): phase windows written in seconds (`start`, `end`, `dur` = one component's
   move), converted to the construction clock (30 units per second — the engine's motion constants assume
   it). Ground operations in `terrainAt(c)`; finish sweeps if any. For narrated films write the windows in
   voice-over seconds (offset from `npm run vo`) so each stage lands on the word that announces it.
4. **Check**: add the structure to `scripts/buildforge-check.ts` and run it until it prints OK. Fix order
   issues by moving windows, clashes by fixing geometry — never by loosening the check.
5. **World and camera**: `createWorld(gl, scene, { components, windows, site, terrainAt, trees, sunDir })`;
   camera keys on the clock: start wide and low enough to read the land, go high for excavation and the slab,
   orbit and rise with the frame, finish on a three-quarter hero view. At 9:16 keep the whole structure
   inside the narrow frame (distance ≈ 4–5 × the structure's width).
6. **Labels and brand**: `StageLabel` with 4–6 short stage names in the buyer's language; colours from the
   brand kit when it has one (`src/core/type/brand.ts`).

## Checks before showing a preview

- `buildforge-check` prints OK for every sequence.
- Stills at each stage: nothing floats, every element arrives by its method, temporary works leave, the crane
  rigging clears before the camera dwells.
- The last component is in place with at least half a second of hold before the end.

Known limits: the clash check is exact for rotations in multiples of 90° and uses the envelope otherwise;
one sun with shadows (keep `shadowSize` around the structure); interiors are not furnished by default; the
camera keys are written per film.

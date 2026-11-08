# WORLDFORGE kit — explorable outdoor worlds

`node SKILL_DIR/new-project.mjs [folder] --kit=worldforge` copies the starter plus this kit's `src/` into the project's `src/`. It adds `src/kits/worldforge/` (self-contained;
`track.ts` re-exports the core track).

| File | Content |
|---|---|
| `noise.ts` | seeded `mulberry32`, `hash2`, value noise, `fbm`, `ridged`, `smin`, helpers |
| `heightfield.ts` | grid axes (`expAxis`), `makeGrid`, `lookup`, `makeWorldHeight(near, far)`, `bakeSun`, `bakeAO`, `gridNormals` |
| `terrainMesh.ts` | `gridGeometry` with per-vertex colour/shade and seam skipping (near/far terrain without overlap) |
| `surface.ts` | terrain/rock materials with detail texture, `dustSkirt` |
| `Sky.tsx` | `SkyDome`, `useSkyEnvironment(sun, colors)` |
| `Stage.tsx` | outdoor stage: camera `Shot`, `Look`, sun `ShadowBox`, post chain — the outdoor counterpart of the studio |
| `geo.ts` | cached box/cyl/sphere/torus/lathe, struts, arches, geodesic frames |
| `plants.ts`, `path.ts`, `easing.ts`, `track.ts` | procedural plants, plan paths + travel, easing, keyframe tracks |
| `example/` | A 2 s ancient-fort technical example (`WorldforgeStarter`) — terrain + sky + primitive buildings |

Reference builds: Mars, Rome and an underwater-city film (1080×1920/60; the underwater atmosphere
was authored for that film). The example is technical, not art-directed. Not provided: forests, water, animals,
crowds, city-scale generators, night lighting — build and check them explicitly when a brief needs them.
Use one analytic `height(x, z)` for terrain, building pads and camera clearance. Put additive/volume
effects on `FX_LAYER`; the stage's NaN guard protects the frame from faulty custom shaders.

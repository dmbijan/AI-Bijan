# WORLDFORGE — Cinematic 3D world-building, style 06

**In this package:** the world engine in `kits/worldforge/` (terrain, sky, outdoor stage, geometry, plants, paths) with a 2 s ancient-fort technical example; see `kits/worldforge/KIT.md`. The reference films ("The First City on Mars", 87.5 s, and Rome), their scenes, audio and video are not included.

## Identity and best content uses

Create a *place the camera can actually explore*: a continuous three-dimensional landscape with its own atmosphere, architecture, scale cues and coherent spatial geography. Tell a story by moving through that place; do not substitute sliding images, flat depth cards, a static skybox or a generic rotating globe. Ideal prompts include original science visualizations, speculative futures, historical re-creations, landscape explainers and cinematic "what if" scenarios.

- **VISUAL LANGUAGE:** stylized realism, carefully sculpted terrain, nuanced surface albedo, readable landmarks, physically grounded buildings and props, consistent sun direction, soft aerial perspective, motivated camera paths, tactile close-ups and a final wide reveal.
- **BRIGHT BUT CINEMATIC:** prefer high-key daylight or sunset with readable midtones, even when the material palette is warm, dusty or subdued. A space establishing shot can be dark where physically motivated; don't let the entire film become dark. Avoid heavy neon, excessive fog, clipped highlights and crushed black terrain.
- **PROVE REAL DEPTH:** show changing parallax, terrain occlusion, correctly attached architecture, actual camera navigation and coherent object shadows. Never call a mere 2D texture pan "3D world-building".
- **WORLD-LEVEL CONSISTENCY:** a shared coordinate/layout specification governs terrain height, building pads, roads, animation paths and camera targets. Scale cues (vehicles, people, structures) must make spatial sense.
- **PURPOSEFUL LIFE:** moving vehicles, people, flora and particles should further the narration. A static landscape with a slow orbit is not a complete content-driven example.
- **DISTINCT FROM OTHER STYLES:** unlike VOXELVERSE it does not use block-built voxel geometry; unlike PAPERWORLD it is not layered paper; unlike INNERWORKS it examines a whole environment rather than a machine cutaway; unlike TIMEFORGE it does not focus on one object evolving across dated eras, and unlike the planned BUILDFORGE it is not primarily a component-by-component construction sequence.

Example *briefs*, not promised built-in templates: a Roman trading town at dawn; a future lunar settlement; what a primeval coastline might have looked like; an imagined city inside a crater. If depicting real science or history, verify claims and make speculative details explicit to the audience.

## Reusable technical recipe

1. **Inspect the buyer's project.** Use Remotion + Three.js / React Three Fiber with `ThreeCanvas` from `@remotion/three` when compatible. The exemplar used Remotion/@remotion/three 4.0.539, R3F 9.8.2, Three.js 0.186.1 and React 19.2.3; these are *observed versions*, not mandatory versions. Do not imply a preinstalled Mars renderer.
2. **Design the world before shots.** Establish dimensions/meters, up axis, site plan, horizon geography, key structures, paths, sun direction and the approximate viewer journey in one layout module. Place buildings at the same `height(x,z)` the terrain mesh uses, with leveled foundation pads where necessary.
3. **Author deterministic terrain.** A seeded height function can combine fBm, ridged multifractal noise, dunes, mesas, craters or terraces as appropriate to the new subject. Use one analytic height function for both visible geometry and camera clearance. Adapt albedo by slope, elevation and biome. A near/far grid with increasing spacing provides detail near the story and inexpensive distant landforms; avoid overlaps and z-fighting at the seam.
4. **Bake/static-light when useful.** The Mars source baked sun visibility and horizon ambient occlusion into per-vertex attributes to avoid unstable high-resolution terrain shadows. Keep props on a consistent fixed directional sun, use a fixed camera-area shadow frustum and restrained environmental reflection. A different sun/time-of-day may require rebaking; do not promise free real-time day/night changes.
5. **Build original local architecture and environment props.** Use reusable geometry factories (box, cylinder, torus, lathe, bevels, arches and merged struts); cache geometry and instance repeated rocks/plants. Give buildings foundations, trim and material differentiation. For historical worlds, design architecture from researched references; do not apply the white Mars-habitat model to every setting.
6. **Separate World from Director.** The world draws from a pure `WorldState`; a director function maps `time = frame / fps` to camera pose, target, FOV, focus, moving-asset poses and shot state. Build a 15–20 s preview and the narration-led film against the **same world** with potentially different director timelines.
7. **Move the camera with a reason.** Use a clear sequence such as distant establishing image → approach → world reveal → human-scale action → detail / consequence → wide payoff. For camera tracks, interpolate positions/targets with smooth curves; examine path length and speed relative to height. Make cuts during meaningful narration pauses, not arbitrarily every few seconds. Transitions may use a physically motivated dust/haze veil, but check occlusion and visibility at the switch.
8. **Use frame-derived animation only.** Seed random terrain/scatter, compute moving objects from absolute time, and render post-processing deterministically. If R3F's `useFrame` is needed to trigger a manual composer render, it must not increment animation state. Avoid `Math.random()` inside renders, clocks, live physics and browser-only autoplay.

## What actually transferred beyond Mars

**Demonstrated in both Mars and the ancient-fort starter:** seeded noise and heightfields; near/far terrain mesh; sun/AO shading; skydome and environment map with a different sky palette; fixed-sun staging/post-processing; primitive geometry kit; keyframed cinematic camera. The starter is only a two-second technical proof, not a sale-ready second film.

**Present in the Mars example but not demonstrated as generic:** rock scatter generator, wheel tracks, rover/trailer/astronaut rigs, procedural plants, planet shader, haze world-switching, synthesized ambience and some geometry helpers. These may require extraction and redesign for a new world.

**Not provided by the current implementation:** animated dinosaurs/other rigged animals, forest generators, water/rivers/oceans, crowd systems, city-scale LOD generators, robust GLTF character workflows, a fully tested night-lighting pipeline, or automated historical accuracy. Plan and implement these explicitly when requested; never claim they're already working.

## VO-led storytelling and buyer conversation

- Follow the master guided brief: ask the idea first when unknown, select WORLDFORGE quietly if a continuous 3D environment best fits, then **ask whether the video uses VO or only music/SFX**. If VO is selected, request the actual accessible MP3/WAV and inspect its duration and phrase/silence boundaries **before** locking shots.
- The exemplar had an approximately 82.7-second ElevenLabs Indonesian VO, with about 0.8 seconds of breathing room before speech and several seconds for the closing title; its complete film became 87.5 seconds. These values belong to that audio and **must not** be hard-coded into new videos.
- Write a per-shot story map: narration phrase/time, visual proof, landmark, camera intent and transition. Reveal details when they're mentioned; hold stillness for emotionally important lines. Preserve the original audio's speed and pitch. Add music/ambience only with the buyer's authorization and licensable sources.
- If a new narration is not ready, help the buyer write it but keep production timing provisional until a real audio file is provided. Do not ship or redistribute the example's ElevenLabs voice recording; its rights depend on the project owner's plan.

## Review / quality gates

1. Produce 3–5 seconds of lookdev including terrain detail, an important structure, shadows and atmosphere. Inspect stills at phone size before committing to many shots.
2. Render a 15–20 s lower-resolution vertical preview; inspect parallax, landscaping, camera orientation, prop-ground contact, silhouettes, frame edges and legibility. Get approval before extending to the long narrated timeline.
3. For constrained paths, automatically sample camera clearance above `height(x,z)`, obstruction/line of sight, speed jumps and terrain seam geometry. A shot with a clipped mountain or a camera underground must be fixed before final render.
4. Verify sun/shadow coherence, no distant terrain gaps, no transparent sorting artifacts, no instance shimmer, no black/frozen frames and no unintended camera cut discontinuities. Recheck all important events after changing the world layout.
5. Validate actual source asset provenance and licenses. Bundled OFL fonts do not license a buyer's music, 3D assets or ElevenLabs narration. Never copy a third-party render or clone a recognizable brand into an unlicensed production.
6. Confirm the encoded final MP4 dimensions, FPS, full duration and audio sync. Never claim loudness you have not measured on the delivered file.

**Observed performance, not a guarantee:** on an Apple M4 Pro, expect about 7 seconds of CPU terrain generation per render tab, a 600-frame preview in roughly 22 seconds, and a 5250-frame full render in roughly 2 minutes 18 seconds at concurrency 4. Hardware, renderer and world complexity change these costs substantially.

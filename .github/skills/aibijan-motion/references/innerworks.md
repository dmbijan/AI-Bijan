# INNERWORKS — Cinematic 3D cutaway explainers, style 05

**In this package:** style rules only, no mechanism-layout kit. Build on the shared core (`starter/src/core`). The reference film is an "Inside an Elevator" traction-lift cutaway (77 s, 1080×1920, 60 FPS, with voice-over); its source is not included.

## Identity and visual grammar

Reveal *how something works* inside a genuinely 3D miniature. Open an architectural shell or machine casing in meaningful physical layers; show internal components, their connections and their cause-and-effect motion. INNERWORKS is an explainer with cinematic production design, not a decorative exploded view, flat infographic or elevator template.

- **True 3D:** geometry with depth, cavity walls, component thickness, believable shadows and correct spatial relationships, rendered via Three.js / React Three Fiber inside Remotion.
- **Bright studio look:** off-white, sky-blue and restrained mint/coral/amber technical accents. The exemplar used background #F3F0EA, cab mint #6CCDAA, counterweight orange #EF7A43, motor blue #5EA7DC and sheave amber #F4B33F. Adapt to the subject; reserve saturated colors for explained components.
- **Tactile modeling:** subtly beveled casing/architecture in matte high-roughness finishes; stronger metallic reflections only on appropriate mechanical parts. Bevels should catch a soft key light.
- **Section language:** distinguish exposed cut faces from outside faces using quiet contrasting colors. Never leave raw empty interiors, z-fighting or floating panels.
- **Isometric cinematic camera:** slightly perspective-compressed three-quarter view (the exemplar used about 30-degree FOV). Vary yaw/pitch to keep important internals visible rather than orbiting constantly.
- **Purposeful technical labels:** project a visible 3D anchor into screen space and attach a short high-contrast label; keep phone-safe margins, noncrossing leaders and at most a few labels per beat. Use concise information cards only for quantities geometry alone cannot explain.
- **Narrative:** specific opening hook, exterior, motivated cutaway, exposed internal mechanism, causal motion, insight and reassembled hero shot.

The exemplar is a four-floor traction lift; do **not** reuse its exact building, geometry, labels, camera shot list, VO or premise for unrelated briefs.

## Reusable implementation architecture

The supplied production project separated **world components** from **time-based direction**:

1. Make one shared layout module for dimensions, axes, section plane, part attachment points, palette and all belt/cable/pipe paths. Geometry, labels and animation must use the same coordinates.
2. Build reusable bevelled primitives, per-face materials, lathed rims, spoked wheels, partial shells, tube paths and procedural UI where the new subject needs them. Do not build everything from undifferentiated boxes.
3. Generate a pure `WorldState` from frame/time: camera, removable-layer offsets, machine mechanism, annotations and displays. Render `World` from that state. One 20-second preview and the later long film can share geometry with different timelines.
4. Find a **single primary driver** for each machine (e.g. piston travel, rotor angle, flow or elevator-car height). Derive coupled positions, rotations and lengths from explicit constraints. In the traction-lift exemplar, cab height determines counterweight height, rope length and pulley angle; they are not independently animated.
5. Build machine-specific invariant tests over the timeline: e.g. constant cable/belt length, opposing displacement, gear ratios, door interlocks, bounds or flow conservation. Do not copy lift tests into a pump.
6. Use `useCurrentFrame()` / `useVideoConfig()` for deterministic animation. With 3D, use `ThreeCanvas` from `@remotion/three` with frame-derived poses, never independent `useFrame()`, uncontrolled CSS animation, live clocks or unseeded randomness.

**Observed demo stack, not a universal requirement:** Remotion / @remotion/three 4.0.539, React Three Fiber 9.8.2, Three.js 0.186.1, TypeScript and React. Inspect the buyer's existing package versions first. The elevator source is **not bundled with this installable skill** and must not be assumed available.

## Cutaway and exploded-view choreography

1. **Select a section plane.** Separate static internals from removable shell/facade slices. Parent every prop mounted to a removable slice; otherwise it will float when the slice moves.
2. **Pop, then lift:** first offset the layer slightly toward the camera to separate, then slide/lift with a restrained tilt. Stagger layers in an order that prevents collisions (roof/facade before inner shaft in the exemplar). Reverse the same curves and layer order for reassembly.
3. **Show cut edges** using material contrast; ensure mating faces do not overlap while intact. Complete the cutaway *before* the camera enters a close-up, so the flying shell does not block the subject.
4. **Explode only informative parts.** For example, remove half a motor casing to reveal winding inside; keep a pulley connected to its cable. Exploded motion must answer "what is inside?" rather than become decoration.
5. A demonstration used roughly 0.3 scene-unit forward pop followed by larger lift, small tilt and stagger. Treat these as **relative movement cues**, not literal distances for different scales.

## Camera and mechanistic storytelling

- Use a relevant macro or POV hook, then spatially explain the object with a continuous camera path when possible.
- Key camera targets, distances, yaw, pitch and FOV; interpolate with restrained easing. Precompute a critically damped follow path if smoothing is needed and ensure it is deterministic under parallel rendering.
- For tight interiors, prefer explicit collision-checked paths; aggressive smoothing can cut through a wall or door.
- Distinguish **movement arrows** (animated direction chevrons linked to true velocity) from **force vectors** (stationary, clearly labeled and shown on a single object). In the exemplar, confusing twin downward arrows were changed in the source to weight ↓ versus rope tension ↑ on a parked cab. Inspect the actual corrected render, not only the code.
- Drive any speed, load or pressure chart from the same state as the physical mechanism. Never tween a chart independently from the thing it claims to measure.
- After each major shot, the viewer should know: which part is this, what drives it, and what changes as it moves? Revise the shot if the answer is unclear.

## Audio-led timeline and QA

1. Follow the master VO checkpoint. If the buyer uses VO, obtain the actual accessible MP3/WAV *before* fixing shot durations. Inspect the duration and phrase/silence boundaries.
2. Map phrases to component reveals and actual motion. Cue on important words, not uniformly spaced shot durations. Never silently trim, accelerate or pitch-shift user narration.
3. Start with a 3–5-second high-risk lookdev, then a 15–20-second cutaway/mechanism preview. Inspect actual stills and resolve physical issues before a long narrated version.
4. Verify portrait safe areas, legibility, licensed assets/fonts, frame size and FPS. The demo generated geometry procedurally and bundled a licensed OFL font; these details do not license arbitrary assets or music for future buyers.
5. Test invariants **across each frame** where practical. Inspect all motion reversals, section intersections, door states, arrow meaning, label clipping, camera collisions and frame transitions. Check exported media for black/frozen frames and audio alignment.
6. Verify final MP4 metadata instead of trusting a planned composition. Every new machine needs its own rendering check.

## Common failure modes

| Failure | Remedy |
| --- | --- |
| Important mechanism hidden by casing | Redesign section plane/camera or reposition the mechanism with physical plausibility; do not mask with a label. |
| Cut material visible on a closed shell | Offset coplanar surfaces; show section materials only where cut planes are truly exposed. |
| Rope buried in pulley or disconnected during motion | Compute groove radius, tangents and wrapped paths from shared layout; derive motion from one driver. |
| Lobby props float after explosion | Parent props to their removable layer. |
| Doors or brakes act at impossible times | Derive all states from mechanism timeline and check interlocks over every frame. |
| Labels clip or leader lines cross | Project 3D anchors, scale for portrait, choose side by free space, inspect stills. |
| Camera travels through geometry | Check intermediate frames; use explicit paths in tight interiors. |
| Force arrows read as incorrect motion | Clearly label force direction, distinguish from velocity chevrons; inspect the encoded video. |
| Fonts or rendering depend on one machine | Bundle properly licensed fonts, declare dependencies and run from the project root. |
| VO becomes quieter after encoding | Measure exported loudness before applying a measured gain; do not assume one fixed gain for every source. |

## Lessons from a core-only build (desk fan)

Built in an empty project from `starter/` only (no INNERWORKS kit): thick lathe half-shells with section caps, pop-then-lift cutaway, one driver (rotor speed → shaft/blade angle → airflow chevrons), projected labels. A first pass typically fails in predictable ways: copper coils hidden inside the stator, labels colliding when shown together, blades occluding the motor in close-ups, and the lifted shell flying toward the camera. Fixes: lay internals out so explained parts are visible from the planned camera side, one label at a time, close-ups from the side opposite the moving parts, removed shells move away from the camera. Expect 1–2 look-dev iterations for a new machine.

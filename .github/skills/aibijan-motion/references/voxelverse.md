# VOXELVERSE — Cinematic handcrafted 3D miniature worlds

**In this package:** the voxel engine in `kits/voxelverse/` (chamfered-box modelling, architecture kit, spec-driven buildings with instanced glow windows, diorama plinth, dusk atmosphere, rain, Hermite camera shots, post chain) with an 8 s dusk-street example; see `kits/voxelverse/KIT.md`. Voxel characters are built per film with the kit's modelling and animation helpers.

## Identity

Original 3D voxel worlds that feel like hand-directed miniature films: real geometry, tactile materials, carefully composed environments, believable environmental activity, cinematic light and motivated character actions. Not a Minecraft copy, a raw Three.js demo, or a toy cube carousel.

## Visual grammar

- Real perspective depth and foreground/midground/background. Use authored shapes with varied silhouettes and detail density.
- Warm practical highlights, readable muted teal/cool fills, tasteful atmosphere and soft shadows. Small glowing features can drive story, not dominate every pixel.
- Distinct buildings with windows, gutters, poles, steps, signs, objects and subtly asymmetrical compositions. Use repeated pieces only when architecturally plausible.
- Characters have articulated voxel body parts and a clear silhouette; use anticipation, small purposeful gestures and reaction timing.
- Compose each location as a diorama in service of a scene. The world needs visual logic: pathways, entrances, windows, lights, and places for characters to act.
- Use filmic camera movement with clear staging, changing image scale and motivated reveals. Start and end on authored hero compositions.

## Production pipeline

1. Establish the central event and characters from the brief.
2. Design a location that communicates the story on a paused frame.
3. Build the geometry with React Three Fiber / Three.js under Remotion's `ThreeCanvas`; use InstancedMesh for suitable repetitions, avoiding thousands of wasteful draw calls.
4. Create material families and a deliberate key/fill/practical-light plan. Ensure frame-to-frame determinism.
5. Stage character paths and camera paths together with the spoken words or soundtrack beats.
6. Render an initial 3–5 second lookdev clip, inspect actual frames, then expand.

## Technique

- **Voxel modelling kit:** models authored as lists of *chamfered* boxes inside a transform stack, merged into one geometry per material class with per-box vertex colour. The small chamfer on every edge is what makes it read as a crafted miniature instead of default cubes.
- **Architectural pieces:** brick walls with openings, window frames, sills, planters in a local wall frame, placed into the layout.
- **Light as story:** outside an overcast dusk (cool key + hemisphere + environment map for wet reflections); inside practical lights — pendant, the monitor as an area light, a desk lamp that clicks on, a cool window fill.
- **FX layer:** additive light effects (cones, halos, rain, pulses) on a separate render layer so AO and depth of field ignore them.
- **Rain:** world-anchored streak volume wrapped around the lens (no swimming), ripples near the camera, culled inside buildings.
- **Camera:** shots as timed keys (position, aim, lens, focus) on time-based Hermite splines with Catmull–Rom tangents; pure `cameraAt(frame)`.
- **VO timing:** cues placed on measured narration (silencedetect phrase boundaries, word onsets from an RMS envelope).

## Suggested signatures

Ambient weather, glowing window interactions, one small memorable creature or object, observable cause-and-effect across a wider physical space, an earned miniature-world reveal.

The previous exemplar's elements — rainy city, programmer in a warmly lit room, orange robot, spark activating a world — are examples only. **Do not replicate this exact story for every user prompt.** Rebuild original spaces and actions according to the new brief.

## Hard bans

- Flat CSS layers pretending to be 3D.
- Hundreds of floating cubes as the whole scene.
- Generic orbiting camera, purposeless particle storm, excessive bloom.
- Black/crushed backgrounds that hide the voxel craft.
- Every building with the same silhouette and window layout.
- `useFrame()` time loops that render differently at different Remotion frames.
- Empty beauty shots without characters, mechanical interactions, or environmental story.

## Example briefs that suit this style

- A tiny programmer and robot trigger life across a rainy city, voiceover-led.
- A remote Antarctic research station wakes up to an unusual aurora, with a snow vehicle and moving machinery.

Every film must remain detailed, readable, original and fully 3D at 60 FPS.

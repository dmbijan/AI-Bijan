# PIXELSTORY — Cinematic character-driven pixel animation

**In this package:** the pixel engine in `kits/pixelstory/` (palette ramps, ASCII sprites with sel-out finish, painted albedo sets relit per frame, sprite lighting, layered pinhole 2.5D camera with depth of field, light shafts, dust, pixel typography, four OFL fonts) with an 8 s moonlit-room example; see `kits/pixelstory/KIT.md`. It draws with canvas, not WebGL. Every film designs its own cast with the character pattern in the kit.

## Identity

A miniature emotionally readable world combining authored pixel characters, deliberate 2.5D depth, rich environmental detail and film-style camera language. Think of a lovingly directed indie animation, not an 8-bit game screenshot or a generic pixel filter.

## Visual grammar

- Art direction: amber lamplight, copper/red practical glows, rich wood textures, restrained teal ambient contrast, layered atmospheric depth. Do not crush exposure.
- Characters: distinct original silhouettes, readable heads and eyes, limited but expressive frame-animated gestures, secondary follow-through. A curious orange companion robot is an optional recurring motif, **not required for every brief**.
- Locations: intimate studios, tiny storefronts, city blocks, science labs, or purpose-built worlds with handmade props, small asymmetries and environmental storytelling.
- Pixels: intentional pixel unit sizes and nearest-neighbor handling for sprite assets. Avoid inconsistent resolution, blurry upscaling, or a blanket pixelation filter over everything.
- Depth: separate architecture, characters, lighting accents and atmospherics into genuinely distinct depth planes. Deliberate parallax and camera tracks create cinematic space without faking random 3D cubes.
- Typography: sparse, editorial, integrated into story beats. Usually two short phrases or fewer; avoid bouncing-caption templates.

## Choreography

1. Start on a meaningful visual action in the first second.
2. Establish who or what matters through an action, not a wall of labels.
3. Introduce a shift of light, object, character motivation or small discovery.
4. Carry the change through a camera move or spatially continuous transition.
5. Finish with a reveal or visual emotional payoff.

Camera suggestions: multi-plane dolly-in, slow lateral reveal through foreground silhouettes, guided rack-focus simulation, carefully chosen 3/4 perspective; each move needs a specific subject.

## Implementation guidance

Use Remotion and React with layered original sprite artwork, procedural vector-to-pixel drawings, or authored assets. Build reusable `PixelActor`, `LayeredEnvironment`, `PracticalLight`, and `CinematicCamera2D` components when helpful. Frame-based sprite switches and deterministic animation only. Keep sprites crisp at output size. Sound can carry micro-actions: footsteps, typing, servo clicks, wind, interior room tone.

## What to avoid

- A flat 2D slide with a pixel filter.
- Sixteen identical tiles masquerading as a city.
- Smearing sprite pixels with heavy glow or blur.
- Characters idly bobbing for the whole video.
- Copied sprites or recognizable game IP.
- A cut every second without spatial continuity.

## Core rules

1. **Story before effects:** one-sentence logline and a 5-beat storyboard; every shot names subject, purpose, emotion, focal point and key light.
2. **Light is the author:** one named key light per shot; sets are relit per frame, never baked flat; ambient ≤ ~0.18 so darkness frames the set.
3. **Sharp sprite, soft world:** characters crisp on the focal plane; other layers at real depth get lens treatment (blur by distance, haze). Distant sprites use smaller pixels, not just a deeper layer.
4. **Act, don't slide:** pose changes with anticipation, overshoot, follow-through; give the emotional beat a close-up (face ≥ 40% of frame width) with a tracking eye-line.
5. **One accent with a meaning** (< 5% of the frame).
6. **Type belongs to the set:** in negative space, lit by the shot, occludable; compose per shot, not one centred stack.
7. **Transitions carry an object** (a light, a pixel, a screen); no flashes or black frames; cut on the peak.
8. **No stock clichés:** synthwave sun, perspective grid, nebula backdrops, sunburst rays.
9. Dither only around ramp steps; review frames from the MP4, not only stills; at least three stills QA rounds.

## Example briefs that suit this style

- A small programmer discovers a curious light in a warm workspace.
- A lone lighthouse keeper restores a coastal signal during a storm.

Whatever the brief, inspect a full render and check that palette, camera language, sprite craft and pacing remain recognizably PIXELSTORY.

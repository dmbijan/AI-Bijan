# PAPERWORLD — Cinematic handcrafted papercraft storytelling, style 04

**In this package:** the paper engine in `kits/paperworld/` (procedural paper stock, cut shapes, extruded cards, hinges and pop-up mechanisms, buildings, doors, a puppet character with its own colours, clock, miniature environment, stage with portals between worlds) with a 5 s paper-village example; see `kits/paperworld/KIT.md`. Check grain scale and sharpness at 1080×1920.

## Identity

Build an *actual layered paper diorama* that moves like a carefully art-directed short film. The viewer should sense cut edges, folded card, printed surfaces, tiny standing sets, practical light and physical space. This is dimensional papercraft, neither flat vector slides nor shiny 3D toys. Every scene must advance a story, idea or explanation.

The reference preview shows a small original child-like paper character exploring a pastel town with large arched doorways, a clock tower, corridors, a glowing portal, a handcrafted cat and a phone-shaped interface. These are **observations, not mandatory subjects or assets**. Invent different characters, locations and metaphors for unrelated briefs.

## Visual grammar from the reference preview

- **Materials:** warm ivory matte card with gentle visible grain; cut-paper faces in muted coral, terracotta, mustard yellow, mint, seafoam, cobalt and cream. Bright and friendly without oversaturation.
- **Layering:** separate paper silhouettes, stacked card with readable thickness, inset doors and windows, folded structural pieces. Expose edges or overlaps when forms turn.
- **Lighting:** broad warm daylight, legible ambient fill, short soft contact shadows at every layer junction; selective warm practical glows for reveals. Avoid specular plastic, mirror chrome or heavy neon.
- **Surfaces:** subtle irregular cut edges, restrained fiber grain, occasional crease and fold seams. Avoid noisy textures that shimmer or crawl during motion.
- **Sets:** handcrafted miniatures with dimensional arches, sidewalks, windows, clocks, posters and props specific to the story. Keep a coherent scale and navigable floor plan across shots.
- **Characters:** bold cutout silhouettes with layered hair, clothes and facial details. Animate purposeful steps, head turns, arm reach, pause and reaction. Keep limbs connected and intersections plausible.
- **Typography:** short readable phrases appear as printed paper signs or physical labels; hold long enough for phone viewing. Do not copy the exemplar's wording by default.
- **Camera:** motivated tracking behind or beside a subject, 3/4 miniature street view, carefully chosen overhead reveal and push-ins through doors. Keep spatial continuity; no perpetual orbits.
- **Mood:** whimsical and sincere with cause-and-effect action and one earned surprising image. Keep overall exposure luminous, not gloomy.

## Narrative and shot choreography

1. **Visual hook (first second):** show a specific object, action or obstacle immediately; avoid blank title intros.
2. **Establish:** locate the subject in a believable handmade world with clear foreground, midground and background.
3. **Pursuit:** move through connected spaces; let the viewer know what the subject wants and where it is headed.
4. **Mechanism:** make a paper-built prop genuinely do something: fold, open, rotate, rise, peel, slide or reveal. Motivate the camera move with this change.
5. **Payoff:** reveal a larger paper set, reaction, insight or transformation. Hold the final composition long enough to read.

Use a range of shot sizes: wide establishment, medium action, prop insert, final reveal. Prefer spatial continuity and matched cuts over disconnected montage. Time scenes to actual narration or musical beats, not arbitrary equal-duration slots.

## Technical production recipe

1. **Brief/storyboard:** define one sentence of purpose, 4–7 distinct shots, a layout, character/prop silhouettes, palette and the final hero image. Ask for truly needed input assets.
2. **Paper geometry:** use React + Remotion with Three.js/React Three Fiber in `@remotion/three` where depth and camera travel matter. Build cutouts with `ShapeGeometry` or shallow `ExtrudeGeometry` and small edge thickness; folded meshes for structures. Avoid every object being a basic box.
3. **Reusable shapes:** start from the kit (`Cut`, `Print`, `Hinge`, `PopUp`/`ShearPop`, `PaperBuilding`, `PaperDoor`, `PaperCharacter`, `PaperClock`, the miniature environment pieces and `Stage` portals) and add new pieces in the same pattern (printed signs, streets, props). Reuse construction logic, never the same story/world by default.
4. **Materials/shadows:** matte high-roughness surfaces; optional subtle original/licensed paper textures; slightly different front/edge tones; soft directional light and fill. Verify contact shadows and avoid repeated texture patterns. Seed procedural variation.
5. **Character acting:** frame-derived paths and articulated parts; small anticipation/action/reaction sequences. Stop or settle during important reveals instead of constantly bobbing.
6. **Camera:** define targets and depth of field with staging. Each cut should preserve a landmark, follow a subject or deliberately reorient the viewer.
7. **Sound:** understated steps, paper rustle, folds, knocks and soft ambience where useful. Music serves the narrative; never drown supplied voice-over.
8. **Lookdev/QA:** render a 3–5 second sample with character, doorway, visible edges and shadows. Inspect stills, then expand. A smaller proxy may be used for review; only claim a verified final 1080x1920, 60 FPS MP4 after actually rendering and checking it.

Drive time exclusively from `useCurrentFrame()`, `useVideoConfig()`, `spring()` and `interpolate()` or other deterministic frame-derived expressions. Use `ThreeCanvas` for 3D. Do not use an independent `useFrame()` clock, per-frame randomness, uncontrolled CSS animations or desynchronized media playback.

## Paper-specific quality gates

- Each hero object reads as paper via edge thickness, matte surface, seam/layering and contact shadow.
- No floating cutout pieces, disconnected puppet joints, geometry intersecting scenery or fused architecture.
- Sets have coherent doors, paths and scale; camera never reveals empty unfinished sides.
- Texture is stable at 60 FPS with no moire, shimmering grain or over-sharpening.
- Shadows create depth while pastel faces and character remain visible.
- Printed signs have correct spelling, readable size and sufficient hold time.
- The subject's action changes the scene; camera movement never substitutes for story.
- All external assets are original or licensed for the user's intended use.

## Hard bans

- Flat 2D icons sliding on a beige background and called 'papercraft'.
- Glossy plastic figures, heavy neon, mirror chrome or dark fog obscuring craft.
- Random extrusion without meaningful paper folds or stacked layers.
- Constant camera orbit, idle character bobbing, manic cuts or harsh depth-of-field blur.
- Copying the demo's child, cat, clock tower, doors, phone and exact sequence into every future film.
- Claiming a 540x960 preview is already a verified 1080x1920 final render.

# TIMEFORGE — Style 07: cinematic 3D object evolution

**In this package:** the object-evolution engine in `kits/timeforge/` (morph engine, subject contract, cut, overlay, example subject) on top of `starter/`; see `kits/timeforge/KIT.md`. Reference builds: a phone-evolution film (1990s → 2000s → 2010s → 2026 → an explicitly **speculative** 2100 concept, 110 s at 1080×1920/60) and a television subject (1960 → 1990 → 2010 → 2026). The phone subject, its cuts and its MP3 are not included.

## Visual identity and routing

Make **one materially continuous 3D subject evolve through eras**, with design details recognizable at each historical stage. Use real geometry with meaningful thickness, fillets, seams, buttons, lenses and internal parts. Retain a consistent stage and camera when possible. The changes happen *on the object*, not by cutting to a second unrelated model.

- **Choose for:** "evolusi kamera dari 1900 sampai sekarang", "perkembangan desain HP", "dari TV tabung ke TV modern", "bentuk konsol game dari masa ke masa", "sejarah desain pesawat".
- **Choose PRECISION instead** for a one-product ad, product exploded view or beauty shots without eras. Choose WORLDFORGE instead when the main subject is a whole historically populated city/environment, not one evolving object. Choose INNERWORKS for the workings *inside* one machine.
- **Look:** warm white, pearl, soft silver, pastel mint/cyan and subtle orange; glossy/matte premium physical materials on a clean bright studio stage; stable realistic contact shadows, restrained depth of field and specular softbox highlights. Avoid dark backgrounds and PowerPoint-like fades.
- **Typography:** one clear changing year (odometer), a brief descriptor and a minimal timeline rail; de-emphasize labels during tight detail shots. Show future-era designs as speculative, not confirmed forecasts.

## Guided buyer workflow

Follow `references/guided-brief.md`: answer warmly in Indonesian, **one high-value question per turn**, infer TIMEFORGE automatically from an obvious evolution brief, and do not mention internal release status unless asked. Confirm the necessary story only when missing: object and intended audience; approximate eras; factual present day versus imagined future. Prefer 3–5 meaningful eras. Do not burden the buyer with shader/React/CLI questions.

**Resolve VO before storyboard/timing.** Ask whether they will use an MP3/WAV narration or music/SFX only. If they already attached an accessible file, use it without asking again. For a requested VO, help draft a script if needed, then request the actual recorded audio. Inspect its real length and pauses (`ffprobe`, `silencedetect`, optional authorized word-level transcription); never assume length from script, secretly compress narration, invent a narrator or insert a sample ElevenLabs recording.

Offer a story map: a striking current object → physically motivated rewind to earliest era → 3–5 transformations → final present or clearly labeled speculative future. Each era should have one visually recognizable change and one reason it matters to the story. Land transforms on spoken dates; close-ups follow nouns in the narration. Show a concise concept and seek confirmation before substantial project work.

## Implement with Remotion + real 3D

The original exemplar used Remotion 4.0.539, `@remotion/three`, React Three Fiber 9.8.2, Three.js 0.186.1, React 19.2.3 and Node >=22.13. These are **observed versions**, not a universal dependency lock. Inspect the buyer's project, installed toolchain, licenses and disk budget before making changes. If no reusable TIMEFORGE source exists, build an original implementation using these principles, and honestly budget for modeling/art direction rather than presenting it as an instantly available engine.

1. **Define a single parameter schema for all eras.** For each dated state, provide all potential component transforms, dimensions and materials. Park not-yet-present parts inside the body, shrunken or recessed; do not make them appear abruptly or clip the stage.
2. **Build geometrically consistent components.** Morph a rounded slab through its vertex/shape parameters when topology is stable. If topology fundamentally changes, choreograph split-and-reassemble with a hidden seam/flash/reveal that preserves object identity. For lenses, antennas, wheels, handles and toggles, give each independent movement and credible attachment points. No crossfade-only replacement.
3. **Separate object, evolution and timeline.** A `Subject` abstraction supplies era parameters, era labels, geometries and footprint; a generic timeline samples `hold` or `morph` from the absolute Remotion frame; a `MorphDef` controls per-part staggering independent of the cut clock; a director places the transitions and camera poses. Rewind is the same physically valid morph sampled backward.
4. **Use purposeful stagger:** outgoing parts retract/dissolve into the surface *first*, the main form reshapes, then new era parts rise and lock in place. The reference phone film had an antenna retract, keypad sink, body flatten into glass, camera system assemble and speculative modular cuff split; these are examples of *mechanisms*, not components to reuse for every topic.
5. **Time-scan materials only where supported.** A cyan traveling highlight can reveal new paint/glass/metal across a stable mesh; the source's sweep shader expects a `tfPos` vertex attribute. On arbitrary meshes without it, use a simpler deliberate material transition rather than generating a flat cyan disc or wrong sorting.
6. **Direct continuous camera motion.** Use smooth, bounded camera tracks in genuine 3D with close-ups of era-specific changes; no sharp teleports or camera collision. Original subject used spherical pose `[target, azimuth, elevation, distance, fov]` on monotone-controlled Catmull–Rom tracks. For close-up changes, keep labels off the body; keep ~17% upper safe zone for year and ~9% lower zone for timeline when labels show. Treat reported timings (roughly >=1.4 s between close-ups; >=2.4 s for >150-degree orbit) as starting heuristics, not hard constraints.
7. **Keep frame rendering deterministic.** Compute pose, morphology, material state, camera and labels from `useCurrentFrame()`/`useVideoConfig()`, seeded static geometry and known data. Do not depend on wall-clock time, unseeded randomness, autoplay or state accumulated by frame ordering. WebGL renders may have tiny GPU noise even when animation state is exactly reproducible.

In a buyer project created from the starter, the engine lives in `src/kits/timeforge/` and shared modules in `src/core/`. Start a subject by copying `src/kits/timeforge/example/` to `src/subjects/<topic>/`. Render finals with the generic `npm run render:final` (no phone-specific scripts exist in the kit).

## Audio-aware edit and deliverables

- Analyze the supplied audio's real duration and meaningful phrase/word boundaries, then map each era/physical change to narration phrases. The exemplar used a 0.4 s initial VO offset; **recompute or choose for each new recording**, never hardcode it. Preview may be a representative 15–20 s extract without VO if the buyer specifically approves that workflow; do not pretend a silent excerpt validates final VO timing.
- Make a 3–5 s proof of high-risk modeling/morph, then a vertical **540x960/30 FPS preview**. Inspect stills for each era, a midpoint of each morph and moments of fast camera motion. Ask the buyer to approve visuals before a large final render.
- After approval, render 1080x1920/60 FPS (or the buyer's exact requested specs) and sync to the supplied MP3/WAV. If the current machine is resource-constrained, prefer bounded concurrency and restartable segments to avoid excessive RAM/swap. No requirement to finish in the exemplar's reported render time.
- For polished final audio, aim for clear speech with verified, non-clipping output. The exemplar used FFmpeg muxing, approx -14 LUFS with true peak below -1 dBFS; do not blindly normalize a track or claim loudness without measurement and consent. Use music/SFX only when licensed and approved.

## QA and limits

- Verify composition specifications and output with `ffprobe`; check audio sync against source with silence/phrase anchors; sample every era and mid-transition; scan for popping, mesh intersections, clipping, jitter, transparent sorting and unexpected year/descriptor changes. Re-render stills after edits and verify frame determinism.
- **Known limits:** camera keys are hand-directed per film; geometry topological changes need subject-specific choreography; the light stage and plinth are calibrated for hand-held objects around 5–20 world units, not vehicles or buildings; caption layout for years before 1000/BCE needs a visual check. The speculative concept color accent can briefly fall back to gray on hold; check that frame.
- **Licensing:** no narration, music or third-party models are bundled; never promise third-party model, music, font or voice rights.

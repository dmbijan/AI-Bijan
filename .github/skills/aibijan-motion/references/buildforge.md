# BUILDFORGE — Style 08: cinematic 3D construction / assembly

**In this package:** the construction engine in `kits/buildforge/` (components, placement methods, phase schedule, GPU-free order/clash check `scripts/buildforge-check.ts`, terrain grading and excavation, construction materials with wet concrete and finish sweeps, crane rigging, blueprint ghosts, world builder, stage labels) with a 10 s garden-pavilion example; see `kits/buildforge/KIT.md`. Reference film: a modern two-storey house built from a cleared plot through earthworks, foundation, frame, enclosure, finishing and landscaping (92.6 s, 1080×1920/60). The rules fit simple orthogonal structures; irregular or curved structures need extra care. No house models, video or narration are included.

## Which briefs belong here?

Automatically select BUILDFORGE for "proses pembangunan rumah", "dari lahan kosong sampai jadi", "bagaimana jembatan dibangun", "timelapse konstruksi gedung" or "bangunan dirakit satu per satu". Briefly describe the physical 3D construction look in buyer-friendly Bahasa Indonesia and move to the mandatory voice-over question; do not require the buyer to choose the internal style name.

**Distinct roles:** WORLDFORGE explores a complete world/inhabited environment, BUILDFORGE follows the assembly of a structure, INNERWORKS dissects how a completed machine operates, TIMEFORGE physically evolves an object through eras, and PRECISION presents one finished product. Buildings substantially different from an orthogonal house (bridges, towers, stadiums) need fresh subject-specific modeling and QA, not promises of an already working preset.

## Signature visual and narrative grammar

- **Real 3D construction.** Actual columns, beams, slabs, blocks, roof pieces, glazing, doors and landscape elements with coherent relative dimensions; not layers fading into a completed house.
- **Blueprint seats.** Before each element is installed, a restrained mint outline appears at its final placement. Each component must have a visible reason and physically meaningful motion for arrival.
- **Stage order:** (1) surveying/marking/leveling and excavation, (2) footings and poured foundation/slab, (3) columns/beams/floor/roof structure, (4) blockwork and walls/finishes/glazing, (5) site landscaping and complete hero reveal. Sequence dependencies must make sense on screen; don't place heavy walls in mid-air.
- **Installation actions:** `lower` from a crane with rigging, `slide` doors/windows into openings, `fill` foundations, `spread` slab concrete, `lay` bricks/slats/pavers, `drive` stakes, `draw` set-out lines, `coat` plaster surfaces and `grow` vegetation. A moving surface/front can show wet-to-cured concrete or raw-to-finished material, but mere opacity scaling is not sufficient for a real construction sequence.
- **Materials/palette:** high-key warm white, soft beige, light architectural concrete, natural wood, sky blue, green/mint grass, warm daylight and consistent soft shadows. Avoid dark neon, excessive glow, unsupported floating parts, choppy camera and overly smooth toy-like trees.
- **Camera/text:** motivated 3D parallax, readable overall construction geometry, concise close-ups of spoken materials, small stage number/name and five-stage rail. Aim slightly behind the building in vertical 9:16 to preserve sky/headroom and center/lower-frame the finished building; avoid labels over the facade.

## Buyer conversation and VO-first direction

Follow `references/guided-brief.md`: ask **one high-value question per turn** in friendly Indonesian, accept a complete prompt without repeated clarification, and keep engineering file names out of buyer-facing replies. If relevant, ask what is being constructed, the desired architecture, and the visually important stages, but infer sensible defaults when possible.

**Before planning scenes**, explicitly resolve whether the buyer will supply a narration file (MP3/WAV) or chooses music/SFX-only. When narration is chosen, ask for the actual accessible audio, inspect its real duration/pauses and align construction beats to speech. If they do not have a recording yet, help draft it first, then wait for the finished audio before locking timeline. Do not invent or redistribute a narrator by default. Recap the five beats and ask for one approval before substantial renders. Clearly frame results as **stylized educational construction visualization, not certified engineering or real-world building instructions**.

## Reusable implementation pattern if starting a new Remotion project

The reference build used Remotion 4.0.539, `@remotion/three`, React Three Fiber 9.8.2, Three.js 0.186.1, React 19.2.3, TypeScript 5.9.3 and Node >=22.13. These are observed versions, not a guaranteed installed toolchain. Inspect the current project, rights and resources, then ask permission for new installs. The kit keeps the same split: `src/kits/buildforge/{assembly,geometry,materials,environment,render,camera,labels,motion,world,presets}` is the general engine; each film adds its own `plan`, `components` and `sequence` modules plus its camera keys (copy the structure of `src/kits/buildforge/example/`, never its pavilion).

1. **Plan one dimensional coordinate system.** Define site dimensions, grid, levels, facade bays, support points and all openings once; use the same opening list for walls, window frames and coats. Make geometry genuinely 3D and self-consistent.
2. **Define components as data.** Give every component an ID, final seat/position, physical geometry, phase, material, arrival method, `rests` supports and optional `after` dependencies. Treat temporary stakes/formwork/spoil as removable only after their role is finished.
3. **Schedule then validate.** Give construction phases their own windows and stagger by support grid/span. A validation pass should catch duplicate IDs, missing supports, installation before a support exists, and removing temporary works while still loaded. The kit's `schedule`, `validate` and `checkSequence` do this; run `node --experimental-strip-types scripts/buildforge-check.ts` for every sequence (the reference house passed with 195 components/36 phases in preview and 203/37 in full). Collision checks covered orthogonal/box geometry, not curved bridges.
4. **Use physically legible moves.** Stage crane lifts in waves instead of a wall of hanging cables; limit simultaneous rigs and let hoist lines fade upward. For masonry, use individual courses, lintels, believable mortar and no clash with window openings. For coatings, animate a moving bottom-up finishing front. Terrain should show survey, excavation, fill and grass spread with geometric continuity.
5. **Keep motion frame-deterministic.** The source's construction clock was independent of video FPS: calculate each component state from frame, construction time, component settings and phase, not incremental simulation, CSS autoplay or unseeded randomness. `ThreeCanvas` and frame-driven R3F transforms should remain deterministic.
6. **Separate assembly clock from camera edit.** Preview may orbit in one connected camera path; the narrated full film may use deliberate hard cuts to match "kayu", "kaca", "beton", and other VO keywords. In the Dream Home demo a late **rewind/replay** deliberately returns to the empty site before re-revealing the house; if doing this in a new project, make its recap intent understandable so the scene doesn't resemble a missing-geometry bug.
7. **Use hand-directed camera tracks.** The exemplar used a spherical camera director with C1 tracks, high-key sky and shadows, restrained GTAO/bokeh/bloom. Hero distance and shadow area were tuned for a house ~13 m wide; do not reuse those measurements blindly for other structures.

## Rendering and quality gates

1. Measure the actual VO with `ffprobe` and `silencedetect` (or authorized transcription). Build stage timing around phrases, not a predicted number of words. The 0.5-second opening audio offset was specific to the exemplar and **must not become a fixed global default**.
2. After brief confirmation, render a 3–5-second high-risk lookdev. Then render a **540x960/30 FPS**, roughly 15–20-second preview and review stills per stage plus transitions. Obtain buyer approval before full-resolution rendering.
3. Render the approved final at **1080x1920/60 FPS MP4** unless requested otherwise. Honor the supplied VO's duration and pauses. Bounded concurrency/restartable segments are preferable to filling disk/swap during heavy 3D rendering; the exemplar used a segmented approach.
4. QA: actual file metadata, audio sync to supplied recording, non-clipping loudness, full-length render, clear blueprint outlines, no intersecting walls/windows, support order, camera clearance, readable overlays, no one-frame popping, no frozen/black frames, and stable materials/shadows. If the original source is present, run its `npm run typecheck` and `npm run check`; do not claim those checks ran when only this guidance ZIP is installed.

## Known limitations

- The assembly `check` pattern verifies a particular component schedule and orthogonal bounding-box decomposition; it is **not a structural engineering solver**, does not model real curing time, scaffolding, MEP or construction safety, and is not designed for irregular/angled/curved geometries.
- Demo-specific house dimensions, camera keys, terrain, site plants and the `render-full.sh` DreamHome composition are not generic for every building. No licensed sample VO/music/SFX is delivered to buyers.

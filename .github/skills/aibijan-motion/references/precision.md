# PRECISION — Premium 3D product cinematography, style 03

**In this package:** the shared core in `starter/src/core` (studio, post chain, keyframe tracks, easing) comes from this style's reference film AERO PRO, a fictional premium-earbuds film (24 s, 1080×1920, 60 FPS, original procedural geometry, textures, light and synthesized sound; no CAD models, image assets, samples or brands). There is no SDF/pebble geometry kit; build soft product forms from the core.

## Identity

Premium product cinematography: precision hard-surface modelling, truthful aluminium/glass/ceramic/silicone materials, a physically motivated macro camera, purposeful exploded views and seamless mechanical reassembly, restrained sound design. One product is the hero; every shot explains form, material or engineering.

## Act structure that worked (AERO PRO, adapt per product)

1. **First glimpse** — macro on a seam or shoulder with a highlight sweep, decisive pull back to the silhouette within ~1.5 s.
2. **Reveal** — slow three-quarter drift while the product turns; a mechanical event (magnet pop, hinge, status light).
3. **Release** — parts lift along their real axis first, then curve away; camera orbits with purpose.
4. **Engineering** — assembly axis tilted toward the lens; parts separate with even gaps; a few callouts pinned to silhouettes.
5. **Precision in motion** — one slow arc with falling elevation (true parallax).
6. **Reassembly** — inner parts seat first; glide → hover → magnetic snap; lid closes.
7. **Hero packshot** — lens defocus into a three-quarter composition; name + one line.

## Technique (from the AERO PRO source)

- **Studio:** procedural HDR light stage (PMREM softboxes, rim strips, graphite flags) as the only specular source (`softboxOnly`), bright cyclorama, soft key with contact shadows → `core/studio`.
- **Post:** MSAA → GTAO → bokeh DOF → restrained bloom → PBR Neutral → grade → `core/render/Post.tsx`.
- **Geometry:** signed-distance primitives with smooth booleans meshed by surface nets (organic shells), superquadric "pebble" shells cut at exact heights, filleted lathe profiles for turned parts. With the core only, use `Slab` (rounded hard-surface bodies) and `lathe` (turned parts); budget extra modelling for organic shapes.
- **Materials:** ceramic clearcoat, silicone sheen, anisotropic brushed metals, perforated grilles (`core/materials`).
- **Explode logic:** offsets derived from part extents with even gaps; the anchor part stays; every part moves further than the part it encloses; outer-first timing, reversed on reassembly — monotone ordering at every frame so parts cannot pass through each other.
- **Numeric gates:** a fit check (docked parts inside the cavity, internals inside the shell), a motion check (camera acceleration/jerk, lateral drift while docking = 0), screen-extent probes for edge clipping, hero-layout boxes in pixels.
- **Sound:** original synthesized score/SFX placed from the same timeline as the picture.

## Avoid

Copied branded designs or trade dress (the earbuds genre is crowded with a famous silhouette — change proportions, case shape and details decisively), impossible overlapping parts, chrome hiding crude geometry, purposeless orbits, callouts that cover the product, loudness below target (AERO PRO delivered −15.8 LUFS; the standard is −14).

## New products

Every new product (compact speaker, bottle, camera, ...) needs its own geometry and believable component mechanics, never a recoloured earbud.

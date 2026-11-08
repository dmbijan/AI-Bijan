# Art direction and render quality gates

## Non-negotiable visual standards

- The first frame is intentional and worth pausing on. No placeholder opening screen.
- Each shot has a subject, visual hierarchy, foreground/midground/background when applicable, and a narrative reason to exist.
- Materials and lighting explain physical form; do not hide basic geometry behind bloom, fog, gradients, or motion blur.
- The camera uses purposeful composition and restrained, physically legible motion. No perpetual orbit or purposeless zooms.
- Motion has anticipation and follow-through where appropriate; reactions occur because something happened.
- Typography appears only if needed and is clear on a phone display. No generic text-on-gradient montage.
- Transitions preserve visual or conceptual continuity; prevent sudden unexplained cut spam.
- Prefer readable midtones. Deep shadows may be used selectively, but the whole video must not be underexposed.
- For PAPERWORLD, check cut-edge thickness, stable matte paper grain, plausible overlap/contact shadows, coherent diorama scale and connected puppet limbs. For PRECISION, verify mechanical clearances and truthful materials. Do not apply the same material recipe to both.
- For INNERWORKS, verify meaningful true-3D cut surfaces, consistent connected mechanics, readable projected labels and unmistakable separation between force vectors and motion/direction arrows. Derive linked components and charts from the same machine state; enforce mechanism-specific physical invariants.
- Sound effects and music support the story and remain subordinate to VO where present.
- Avoid copying recognizable existing characters, games, or product trademarks.

- CINÉSTORY, CHARACTER FLOW and PANELVERSE have their own gates (`references/cinestory/qa.md`, `references/characterflow/qa.md`, `references/panelverse/qa.md`): preflight, automated checks and the visual review are required.
- For TIMEFORGE, verify parked parts never pop, per-era recognisable details, labels off during close-ups, readable year/descriptor safe zones. For WORLDFORGE, camera clearance above the terrain height function, coherent sun/shadows, no terrain seams. For BUILDFORGE, support order, no clashes, blueprint seats before installs. For CHARACTER FLOW, the acting person's face visible in every shot, actions readable, no camera inside trees/furniture/bedding, nobody blocking the lens, bubbles and captions never cut or over a face, 13/13 checks from `scripts/qa.mjs` and `out/<id>/qa/REVIEW.md` filled (see `references/characterflow/qa.md`).

## Global delivery standards

- Preview: 540×960 @ 30 FPS for every style (cheap, native-resolution 3D). Final: 1080×1920 @ 60 FPS H.264 yuv420p unless the buyer asks otherwise.
- Audio with VO: AAC 48 kHz, integrated loudness −14 LUFS (±0.5), true peak below −1 dBFS, measured on the delivered file (`scripts/deliver.mjs`). Sync verified by comparing silencedetect pauses of the delivered file and the source (shift = offset, ±1 frame).
- Motion: scan every frame of the MP4 for one-frame spikes (frame-difference > 3× its neighbours) and review the busiest seconds for camera speed.
- Compare look-dev and preview stills with `references/quality-bar/<style>.jpg` before presenting.

## Technical checks

1. Confirm exact dimensions, FPS, expected duration, audio sample timing, and scene bounds.
2. Confirm external assets and fonts are available and permitted for use; document their provenance if any are used.
3. Render a short lookdev segment before building the full film.
4. Inspect stills at start, quarter, middle, three-quarter and final; for an approximately 20-second film also inspect around 0, 2, 5, 8, 12, 16 and 20 seconds.
5. Check missing textures, WebGL errors, clipped silhouettes, flicker, black frames, text safe areas and exposure.
6. Render a low-resolution test with sound, then the final 1080x1920 / 60 FPS deliverable.
7. Report actual commands, outputs, and any failures. Never state a file exists without verifying it.

## Anti-slop diagnostic

If replacing the subject with a random primitive or replacing the story with a generic slogan leaves the video essentially unchanged, the design has not become specific enough. Return to the visual treatment and improve the story, animation purpose, and detail.

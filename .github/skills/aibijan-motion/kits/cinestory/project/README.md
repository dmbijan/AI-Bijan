# CINÉSTORY 3D project

Made from the AI BIJAN Motion skill, style 09 CINÉSTORY 3D (portrait 9:16 by default). Per video you write `stories/<id>/story.json`;
`node scripts/make.mjs stories/<id>/story.json --final` does the rest (plan → sound → render → QA).

- `src/director` — story.json → film plan (acting, cameras, light, grade, UI, sound cues)
- `src/cine` — character rig, light rig, post/grade, FX, motion-graphics kit, set-building kit
- `src/worlds` — `coast`, `kos`, and the `mind` studio
- `src/film` — the generic renderer · `scripts/` — transcribe, TTS, sound, render, QA

Docs: the skill folder's `references/cinestory.md`, `references/cinestory/` and `kits/cinestory/KIT.md`.

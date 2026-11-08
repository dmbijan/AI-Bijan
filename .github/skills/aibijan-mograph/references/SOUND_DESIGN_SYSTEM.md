# AI BIJAN MOGRAPH v1.3 — Sound Department

**Required on every narrated render.** These are five *internal production roles* handled in sequence by the same Claude session; they do not require spawning agents, separate accounts, prompts from the buyer, or additional subscriptions. No role may silently skip its acceptance criteria.

## Outcome contract

- Inputs: actual VO audio, cleaned narration script, timecoded visual beats. Optional buyer-owned music/FX.
- Outputs: `src/audio-plan.json`, music/ambience asset, timecoded SFX references, an audible VO-first final mix, and a measured `audio-qc` report. Preserve all original media.
- Buyer action stays: **attach VO + script → `/aibijan-mograph`**. No question about genre, number of SFX, BPM, or soundtrack unless rights/setup truly block production.
- **Sound continuity is not the same as loudness, and SFX quantity does not equal good sound design.** A video can have many valid SFX and still feel empty, with dozens of dead-air gaps, when there is no continuous bed; a continuous adaptive bed fixes that without changing VO timing. Do not reuse the source video's voice, copyrighted soundtrack, or brand assets in buyers' projects.

## Five internal roles / handoffs

1. **Sound Director — storytelling & emotional arc.** Break measured VO into phases: hook/curiosity → setup → escalation → twist → aftermath → CTA, adjusted to the particular script. Decide which moments deserve relief, silence, an impact, or warmth. Choose between playful, curious, suspense, dramatic, reflective, hopeful, neutral, cinematic, warm, and energetic. Identify where a recurring sonic motif helps recall. Produce a cue sheet with meaningful timestamps and intent. Avoid recycling the exact same track/mood plan in every video.
2. **Music Supervisor — score and provenance.** Pick a continuous, emotionally appropriate, properly licensed background music/ambient bed. Prefer buyer-supplied tracks with known permission; next use already-licensed assets with recorded provenance; next compose original audio (a usable procedural fallback is bundled as `npm run score`). **Never extract background music from reference MP4s** or assume royalty-free internet search results are cleared for redistribution. Keep the track's source/license in the cue sheet. If source rights are uncertain, use generated-original audio or flag the risk; no fabricated licensing claim.
3. **Foley & SFX Designer — illustrated reality.** Associate sound with *actual visible actions*: a message appears → typing/send; number drops → low tick; warning slams → tasteful impact; weather changes → atmospheric texture; visual swish → subtle whoosh. A cue should be audible at the right moment and improve comprehension or feeling. Prefer 8–20 **purposeful cues** in a ~60-second short over 40 random clicks. Vary tone and intensity; avoid loud effects on every cut and harsh comedic squeaks during sincere moments.
4. **Mixing & Mastering Engineer — VO first.** Leave the user's narration content, speed and word timing unchanged. Keep music as an underlay, typically perceived roughly 14–24 dB behind narration *as a starting point*, not a rigid volume rule. Use gain automation / speech-aware ducking to leave space under consonants; music can rise between sentences. Fade/crossfade scene transitions, manage low-end buildup, prevent masking of speech, center voice, use stereo width sparingly. Mix the SFX with headroom, then normalize the **complete combined audio** toward ~−14 LUFS integrated and ≤−1.5 dBTP. Never solve a dry video merely by normalizing an isolated VO louder.
5. **Audio QA Auditor — critical independent pass.** Inspect whether the music enters quickly, changes with the story, supports—not overwhelms—the voice, and closes cleanly. Run `npm run audio-qc -- <final.mp4> --plan src/audio-plan.json` and inspect reported silence, LUFS, peak, duration, and audio stream. **The QA script only detects signals**, not whether music is tasteful, rights are valid, or SFX sync sounds correct: audition the actual audio whenever possible. Investigate quiet gaps >0.25 s at −35 dBFS except deliberate planned pauses; don't treat a requirement of zero silence as an artistic rule. Remaster or rerender if needed, never overwrite a prior version.

## Music choice is an editorial decision

| Narrative | Good opening bed | Shift at twist | Useful accents | Avoid |
| --- | --- | --- | --- | --- |
| Relatable comedy / social what-if | bright pluck, light groove | thin, suspenseful drone | UI taps, soft comedic stabs | cartoon overload |
| Emotional or relationship | warm minimal keys/pad | intimate sparse tension | notification, soft heartbeat | slapstick whoosh |
| Disaster / science explainer | curious low-pulse ambience | ominous bass and controlled rise | weather, glass, deep impact | horror intensity throughout |
| Economy / money / policy | rhythmic, confident marimba/pulse | restrained urgency | cash, app UI, ticking | alarm on every data point |
| Educational / history | spacious documentary motif | discovery swell | page, architecture, mechanical Foley | epic trailer all the way |

The score may become nearly silent for an intentional reveal, but a narration-only dry track should **not** be a silent default. Protect special moments: e.g. a big word like “DIAM.” can receive an impact, then ~0.3–0.8 seconds of authored near-silence before the music returns. Exact times must come from the buyer's actual VO, never from this illustrative example.

## Authoring `src/audio-plan.json`

Create this file in the **new isolated buyer project** after measuring the actual narration. Choose original sections and silence markers for that story; never paste an earlier episode's times. Use this shape:

```json
{
  "durationSeconds": 10,
  "seed": 42,
  "sections": [
    {"start": 0, "end": 3.8, "mood": "playful", "energy": 0.45},
    {"start": 3.8, "end": 6.6, "mood": "suspense", "energy": 0.35},
    {"start": 6.6, "end": 7.2, "mood": "silence", "energy": 0},
    {"start": 7.2, "end": 10, "mood": "reflective", "energy": 0.4}
  ],
  "intentionalSilences": [
    {"start": 6.6, "end": 7.2, "reason": "revelation pause"}
  ],
  "sfxCues": [
    {"time": 1.2, "asset": "audio/sfx/pop.wav", "purpose": "message arrives", "gain": 0.2}
  ],
  "musicSource": "generated-original",
  "rightsNotes": "Generated locally without third-party samples"
}
```

For a *real* project replace all numbers with the actual VO and chosen beats; total sections should cover the full duration with no accidental time gaps. Supported fallback moods: `playful`, `curious`, `suspense`, `tense`, `dramatic`, `reflective`, `hopeful`, `warm`, `energetic`, `neutral`, and `silence`. Mix adjacent phases smoothly. Use `silence` only for narrative reasons. Store `sfxCues` as the auditable plan and actually implement them in the Remotion composition; **writing JSON alone produces no effects**.

## Production implementation

1. First look for verified rights-cleared music in buyer attachments/project. If suitable, copy it to the project's `public/audio/music/` and use it; record source rights. If missing, generate an original adaptive foundation with:

   `npm run score -- src/audio-plan.json public/audio/music/original-score.wav`
   With a named look (v1.6, `references/STYLES.md`), prefer its own bed and one-shots, both original and offline: `npm run style-score -- <arsip|blueprint|sketsa|hikayat|geometrik|terminal> src/audio-plan.json public/audio/music/<name>.wav` (moods drive the arrangement; optional `styleHits` `[{"time": s, "type": "hit"|"riser", "duration": s}]` add impacts) and `npm run style-sfx -- <look>` (writes `public/audio/sfx/<look>/*.wav`, listed with their purpose, for `sfxCues`).

   This is a **synthetic fallback**, with no external samples. It is deliberately restrained and may need additional layering/selection to reach a premium music taste. Never claim the procedural generator guarantees a professional licensed commercial composition.

2. Update `src/project.json` to set `musicFile` relative to `public` (example: `audio/music/original-score.wav`) and a conservative `musicVolume` (e.g. `0.24` on a 0–1 scale, then adjust based on monitoring). Generate speech-aware `musicAutomation` from the **dry input VO** with `npm run auto-duck -- <path-to-original-VO>`. This keeps the source VO untouched, backs up project metadata, and writes volume keyframes in Remotion frame positions. If custom automation already exists, inspect it before choosing `--replace`. Start music at/near frame 0 unless scripted otherwise; do not let endings fall into unexplained dead air.
3. Add SFX in the actual scene/score timeline using the bundled rights-cleared effects or freshly generated original effects. The starter's `<SfxTrack />` (mounted in `Main.tsx`; keep it when you rewrite the file) plays every `sfxCues` entry of `src/audio-plan.json` at its `time` with its `gain`, so the plan and the mix cannot drift apart. If a cue points to an asset that does not exist, replace it or generate it: do not leave a dangling reference. Limit transient effects so speech remains intelligible. Keep stems separate in the project for revisions.
4. Audition early hook, tense center, twist, final question, transitions and all deliberate silence. With no listening capability, report this as a limitation instead of stating “auditioned.” Compare to the buyer's requested aesthetic by intent; **do not copy/reference-rip the other video's music**.
5. Once finalized, set `needsSoundDesign` to `false`. The safe renderer **requires `musicFile`** for a normal video; for a truly deliberate music-free piece, set `scorelessReason` in `src/project.json` to a substantive explanation and document why the dry aesthetic serves the user. Render, then run audio QC with the plan. If a gap is flagged, inspect timeline; fix accidental empty space but preserve authored silence. Report source track, mix method, and the QC data honestly.

## Release blockers

- No audible music/ambience and no justified intentional-scoreless aesthetic decision.
- Music asset missing from the project or an external copyrighted file included without rights.
- Missing/unimplemented SFX cues claimed as completed.
- VO missing, words covered by FX, timing altered without user approval, or cut-off narration.
- Measured peak/clipping issues or numerous unintended sub-−35 dBFS gaps in a supposedly continuous bed.
- No actual final MP4, or render error disguised as a successful delivery.

# Audio guidelines

## The voice is the product
- Always keep the original voice. Never replace it with TTS, never generate an avatar, never time-stretch speech.
- Clean-up chain (sound.py): high-pass 75 Hz, −2 dB at 250 Hz (mud), +2.5 dB at 3.2 kHz (presence), compressor
  3:1 at −21 dB, limiter. It levels and clarifies; it does not change the voice's character.
- Cuts get 8 ms fades on both sides (no clicks). Video and audio are cut at the same frame-snapped times.

## Music
- Procedural beds generated in code (no samples, no licence risk): `impact` 118 BPM four-on-the-floor with plucks,
  `system` 110 BPM tech arps, `editorial` 92 BPM soft keys, `note` 86 BPM notebook groove (NOTED: maj7/9 chords, half-time
  kick, soft hats and plucks, light pad). Intro opens with a low-pass sweep.
- Level is set by **loudness (LUFS), not RMS**: the bed sits `music_db` LU under the voice (impact −12, system −13,
  editorial −14) and ducks another ~5 dB while the person speaks → roughly −17 LU under speech.
- `hit_at` ends the bed with a hit + chord ring-out (e.g. on "Let's go!"); `break_at` drops drums (use sparingly —
  the end card usually wants energy, not a break).
- If the user supplies music (`music.file` = the uploaded path), it replaces the procedural bed: decoded, looped
  if short, faded in/out, levelled by LUFS and ducked under the voice like the bed. The user is responsible for
  its licence - say so once if they upload a commercial song.

## SFX (automatic, from the plan)
| Trigger | Sound |
|---|---|
| wipe transition | whoosh (pre-rolled so the peak hits mid-wipe) |
| morph layout change | swoosh |
| Badge / Tile / FloatCard / chat reply | pop (pitch varied) |
| caption chip with icon (medium+) | soft pop |
| punch-in (medium+) | low impact −10 dB |
| typing in PromptBar / ChatPanel | typing ticks (medium+) + click on send |
| progress bar completes | ding |
| EndCard wordmark | impact + shimmer; button → pop |
| cut transition whip / push | whoosh (peak on the cut) |
| cut transition zoom / flash / leak | swoosh / low impact / shimmer |
| LowerThird, Callout | soft swoosh |
| Steps item | pop |
| Counter | short riser while counting + ding on landing |
| BRoll | swoosh (whoosh for a whip entrance) |
| HeadNote typing / board title / tag label | quiet typewriter ticks (medium+), a single tick on subtle |
| NoteTags strip laid | `paper` (dry slide + soft tap), pitch varied |
| NoteBoard card in / out | `page` (sheet swish); its lines type with quiet ticks |
| BigNote | soft swoosh as the word lands, `scribble` as the highlighter / circle is drawn |
| strikeAt | `scribble` (marker) |
| NoteClips card | swoosh, then `paper` for its label |
Levels: the SFX bus is set relative to the voice (`sfx_db`, impact −7 dB); individual cues add `gain_db`.
`sound_effects`: off (none) · subtle (layout moves + explicit only, ×0.8) · medium (default) · energetic (every emphasis, ×1.15).
Rule of thumb: one sound per meaningful visual event, never a sound with no visual cause. plan-edit flags SFX
density above the style limit (`rhythm.maxSfxPerSec`).

## Mastering
- Two-pass EBU R128 to **−14 LUFS integrated, ≤ −1 dBTP** (Reels/TikTok/YouTube normalise around there).
- verify-output.py checks loudness, true peak, audio presence and A/V sync (cross-correlation with the cut voice,
  tolerance ±20 ms).

## When something sounds wrong (you cannot listen — measure)
- Music inaudible/too loud: compare stem loudness with `sound.lufs_of()`; adjust `music.db` in creative.json.
- Harsh highs: check the band balance of the bed (`numpy.fft` band energy) — 10–20 kHz should be ≥ 25 dB under the lows.
- Voice too quiet vs music after mastering: lower `music.db` 2–3 dB (audio-only changes re-mux in seconds).

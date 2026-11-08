# Editing standards

What separates "rendered" from "edited well". Check every plan and every contact sheet against this list.

## Story first
1. Read the whole transcript before planning. Find: the hook line, each beat (one idea per beat), the proof or
   demo moment, the CTA. Most talking-head clips are hook → claim → proof/examples → twist/benefit → CTA.
2. One visual idea per beat, tied to the words being spoken *at that moment* (use word refs, not guessed seconds).
3. The first 2–3 s must change something on screen at least twice (transformation, punch-in, kinetic hook).
4. Keep the speaker's face on screen for most of the runtime; graphics support the voice, they don't replace it.
5. Do not reorder sentences unless asked. If the strongest hook is not first, suggest moving it — don't do it silently.

## Cutting
- Remove pauses longer than the intensity threshold (low 0.75 s, medium 0.38 s, high 0.26 s); keep ~0.1 s of air
  before and after words so breaths and consonants stay natural. Never cut inside a word.
- Filler sounds ("eh", "emm", "anu", "um", "uh") are cut automatically at every intensity; protect an intentional
  one with `keep_words`. Whisper often doesn't even transcribe them - the pause cutter removes those anyway.
- Retakes: plan-edit prints `RETAKE?` for every phrase that is restarted within a few seconds. Read the words; if it
  is a restart, add the range to `remove_words` and say so in the report. Never cut a deliberate repetition
  ("lagi, lagi, dan lagi").
- Every jump cut needs a reason to exist visually: a layout change, a zoom alternation, or a graphic entrance.
- Trim the tail before the speaker reaches for the camera or looks off-screen; end the footage just after the last word.
- `target_duration` only tightens pauses. If still too long, ask which sentence to drop — never decide alone.

## Framing and camera
- "talk" framing lifts the face so the chin sits above the caption card. Avoid captions over the mouth.
- Camera never sits perfectly still (the renderer adds a slow 1–2 % breathing push). Punch-ins are for the
  strongest word of a beat, not every sentence: about one per 4–6 s at medium intensity.
- No shake, no rotation, no zoom > 1.4× on 1080p sources (softness).

## Layout and safe zones (9:16 ads)
- Meta Reels/Stories ad guidance: keep text and logos out of the top 14 % (y < 270) and bottom 35 % (y > 1248) and
  6 % from the sides. Captions bottom edge default y = 1244. Footage and decorative shapes may extend anywhere.
- Feed placements crop 9:16 to 4:5 (y ≈ 285–1635): important visuals belong in that window.
- 16:9: keep text within 6 % margins and above the bottom 12 %. 1:1 / 4:5: 5 % margins.
- Three consecutive beats must not share the same layout. Alternate: full-frame → cards/split → full → end card.
- **Cards stay out of the caption band.** On 9:16 the captions occupy roughly y 1130–1244, so every card/split/pip
  `rect` bottom must be ≤ ~1120 px. Proven rects: face card under a top graphic `[140, 720, 800, 390]`; large
  single card `[130, 300, 820, 800]`; right-half card next to a panel `split` + `only: "right"`, `y` 280, `h` 836;
  end-card circle `pip` `[305, 285, 470, 470]`. plan-edit prints a `LINT:` line when a card runs into the band.
- Cardless (`clean`) captions get an automatic dark plate whenever a non-full layout shows the light backdrop.

## Graphics
- Every element has a job: show the thing being said (a waveform for "potong jeda", a chat for "lewat chat").
- One focal point per frame; at most one emphasis chip per caption page.
- Use the user's real material when possible (their waveform, their footage as cards/thumbnails).
- Illustrated UI must look generic and stylised. Never imitate a real product's interface or present it as a screenshot.
- Minimum text sizes on a 1080-px short side: captions ≥ 54 px, labels ≥ 28 px, titles ≥ 80 px.
- Contrast: text over footage is white/paper with a shadow, or sits on a chip/card. Dark (`ink`) text only on
  paper, cards or a light backdrop. Coloured text (red, blue) never directly on footage or skin.
- Captions keep ≥ 24 px clear of card edges and overlays; move the card (`rect`/`h`) rather than the captions.

## Transitions
- Morph (footage → card → full) is the default for layouts. The accent wipe is a signature move: use it once,
  for the biggest transformation. Hard cut + flash for "back to raw". No glitch, spin, page-curl or random presets.
- Jump cuts get the style's cut transitions automatically (zoom-through, whip, push, flash, leak - see
  motion-design.md), spaced and alternated; most cuts stay clean jump cuts with zoom alternation.

## Rhythm (what keeps people watching)
- Something visual changes at least every `rhythm.maxStatic` seconds (IMPACT 2.6 … EDITORIAL 4.2): a punch,
  a cut transition, a layout change, a graphic. The planner fills gaps with slow pushes and reports what's left.
- The first 1.5 s must move (hook punch-in or title). Automatic if you forget.
- Vary the device: face → title → face → list → face → number → CTA. Never the same graphic type 4× in a row.

## Faces
- Text never sits on the eyes or mouth while the face is full-frame (plan-edit checks with the tracked face).
  Put it above the head, beside the face, below the chin above the captions, or switch to a card/split layout.
- Overlays enter after the footage has started moving out of their way (stagger by 0.2–0.3 s).

## Review loop (mandatory)
1. Stills at 2 points per beat (after elements settle) → look → fix collisions, sizes, safe zones.
2. Motion strips around transitions (`ffmpeg -vf select=between(n,a,b),tile`) → fix ghosting/overlaps.
3. Render, run `verify-output.py`, look at the QC sheet. A PASS on technical checks does not replace looking.

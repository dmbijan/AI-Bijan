# What CHARACTER FLOW does — and does not

## Works (implemented and tested in renders)
- One VO → finished 9:16 1080×1920 60 fps MP4 (H.264 + AAC, −14 LUFS) with one story file.
- Automatic direction from the story: blocking, acting, props, thought bubbles, camera shots, captions with highlights, title cards, sound.
- 6 environments: campus (day, sunset), bedroom (night, day), classroom (day, sunset), office (day, night), café (day, night), living room (day, night).
- More than one place per video: `scenes` cut to another environment (and time of day, and people) on a spoken word; each place opens with a wide; sound changes with the room.
- 10 looks (incl. two in a hijab, a mother, a father, a school pupil) + per-video outfit colours / clothes / hair (hijab, long hair, skirt); 1–2 people per place, up to 4 per story with scenes; 9 blocking actions + 29 acting actions; 26 expressions.
- Walking with planted feet along the campus path; passing / meeting; turning in place; sitting down / standing up (bench, desk chairs, school desks, café chairs, sofa, armchair); lying / sitting up in bed; bedside lamp.
- Phone and sling-bag props with hand grips; the camera keeps a phone in the hand inside the frame.
- Thought bubbles: text, 10 icons, 3 mini-scene vignettes (icon bubbles grow in wide framings as far as the frame
  allows; in a close-up with no room above the head an icon is left out — the face carries the beat).
- Campus comedy: pop-up sign, tumbleweed.
- 18 camera shots chosen automatically (incl. 3 bed shots, a two-person room wide that fits the set) with occlusion-safe placement, also around the other person; per-beat override.
- Synthesized music (4 moods + adaptive sections), ambience per place (outdoor, room, classroom, office, café), footsteps, ~20 SFX, ducking under the VO.
- Word-timed captions placed away from faces.
- Story validation with plain-language errors (scene by scene); automated QA (13 checks incl. burnt highlights, frozen picture, loudness, phone readability, full contact sheet) + the visual review gate (REVIEW.md).
- Word timings: whisper.cpp (exact) or VO text alignment (≈0.3 s average error).
- Offline Indonesian TTS on macOS (with approval).

## Limited
- Only the six environments; any other place (hospital, market, beach, a car…) is a development task (see environments.md) — say so and use the closest one.
- Walking only along the straight campus path, from the start of a scene until stop/meet/pass; indoors people stand, turn, sit and stand up, but do not walk across the room (cut to them in the new position with a new scene instead).
- At most 2 people in one place (crowds, extras: no); up to 4 in a story with scenes.
- Lying in bed: face/gaze/bubble acting only (plus the bedside lamp); gestures and the phone need sitting up.
- No lip-sync: mouths move with expressions, not with the words.
- Without whisper.cpp the captions/acting can be ≈0.3–0.5 s early/late on some words.
- Camera shots are good defaults; some beats may still need a `shot` override after the stills check. Two people on seats far apart (sofa + armchair) may open on one of them from behind.
- Hijab and long hair are shells that move with the head (no cloth simulation).
- Tested on macOS Apple Silicon only; TTS is macOS-only.
- English or other-language VOs: the pipeline works (whisper `--lang=<code>`), only tested with Indonesian.

## Not available
Running, jumping, dancing, fighting, hugging, eating/drinking, writing, vehicles, animals, weather, water, real-person likenesses, logos/brands, garments beyond the outfit options, 16:9/1:1 output presets, more than 2 people in one place, dialogue between characters (no new voices are ever added), music from files.

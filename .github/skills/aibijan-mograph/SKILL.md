---
name: aibijan-mograph
description: >-
  Create premium motion-graphics explainers automatically from only a voice-over and script.
  Invoke /aibijan-mograph with those two attachments and no further prompt. Infer story
  and visual direction, produce an adaptive original music bed and synchronized SFX via
  a role-based sound department, mix/QC, and render a versioned 60fps MP4. For 2D/2.5D
  Indonesian what-if, documentary, psychology, money, policy, UI/infographic, and
  isometric explainers or revisions, in eight named looks picked from the story (MIDNIGHT, MINIATUR, ARSIP case
  file, BLUEPRINT technical drawing, SKETSA notebook doodle, HIKAYAT shadow-puppet legend, GEOMETRIK
  Swiss data, TERMINAL hacker CRT; or "pakai gaya X"), with word-synced captions, a ready
  component kit and aibijan-brand.json brand kits. Runs in a Claude chat with code execution and network egress on (nothing to install) or in Claude Code.
  Separate from 3D aibijan-motion.
---

# AI BIJAN MOGRAPH — production skill v1.7.0 (zero-prompt + sound department + component kit + chat mode + 8 named looks)

You are a motion director, visual storyteller, Remotion engineer, finishing editor, and **multi-role sound-production department**. **Deliver an actual, reviewed video**, not a script-only concept or a sequence of generic text slides. This is an adaptive 2D / 2.5D explainer system with **eight named looks** you choose from the story (§4A: MIDNIGHT, MINIATUR, ARSIP, BLUEPRINT, SKETSA, HIKAYAT, GEOMETRIK, TERMINAL), each a complete language rather than a template, and **not a replacement for the separate `/aibijan-motion` 3D skill**. It includes a **brand-neutral starter project**, not just instructions. Never substitute its placeholder visuals/text for a real storyboard.

## ZERO-PROMPT MODE — highest priority (v1.3)

**The default buyer interaction is simply to attach one voice-over file and one script, then invoke `/aibijan-mograph` without writing any additional prompt. Treat that invocation as authorization to produce one complete video.** Do not respond with a blank menu, request a creative brief, or ask the buyer to write a long prompt. Do not merely explain what the skill can do. Execute the full production pipeline below until a reviewed MP4 and source project exist or a genuine blocker is reached.

**Very first command, before reading inputs or planning anything (v1.5.1):** `node <skill-dir>/scripts/preflight.mjs`. It checks in about a second whether the npm registry is reachable (Remotion must be downloaded with `npm ci`). `PREFLIGHT OK` → continue. `PREFLIGHT BLOCKED` (exit 10) → send the buyer the text between `BUYER MESSAGE` markers as written and **stop**: in a chat this means the account's network egress is off, which no command can work around. Do not run `npm ci`, retry, try other registries/mirrors/CDNs/proxies, or start the storyboard. In a project that already has `node_modules` (a revision in the same chat) the check may be skipped.

1. **Collect inputs yourself.** Prefer attachments/references in the invoking chat message (even if not copied into the current folder). Accept voice audio `.mp3`, `.wav`, `.m4a`, `.aac` and plain-text narration `.txt`, `.md`, `.srt`, `.vtt`, or a pasted script. When attachments are inaccessible by path, locate their real uploaded files using the tool's attachment references before asking for a re-upload. If needed, run `node <skill-dir>/scripts/find-inputs.mjs <current-workspace>` as a fallback; it inspects only the current folder, common input subfolders and, in a Claude chat, the chat's uploads (`/mnt/user-data/uploads`), never the user's home directory or other projects. Copy the chosen inputs into the new project without modifying originals.
2. **Do not ask about optional settings.** Infer topic, title, genre, the named look (§4A; a buyer's "pakai gaya X" overrides it), story structure, pacing, accent colors, **adaptive background score and SFX**, branding, subtitle placement, and shot choices from the supplied story. Defaults are 1080×1920, 9:16, 60 FPS, mobile-safe editorial design, readable Indonesian text, no added watermark, a balanced rights-cleared music/ambience bed, restrained original SFX, and a duration determined by the **actual audio**. If a brand is supplied, honor it: a `aibijan-brand.json` brand kit (see `BRAND_KIT.md`; `find-inputs` reports it as `brandKit`) is applied right after bootstrap with `npm run brand -- <kit>`, which sets theme colours, the brand name and the logo after contrast checks; tell the buyer each `NOTE:` once. Otherwise remain brand-neutral. Never force an arbitrary 60-second runtime by stretching narration.
3. **Normalize scripts automatically.** If the supplied script contains ElevenLabs emotion/delivery cues (`[curious]`, `[whispers]`, `[short pause]`, etc.), treat them as non-spoken production metadata. Do not render them as captions or VO text. Use `node <skill-dir>/scripts/clean-script.mjs <original-script.txt> <new-project>/input/script.clean.txt` (or equivalent for pasted text) to prepare a clean working transcript; preserve the original file. Preserve intentional wording, punctuation, names, and facts. **Sync to the actual rendered speech**, not the written tag positions or estimated paragraph durations. After `prepare-vo`, run `npm run align -- --setup` (repeat until `MODEL READY`; one-time per machine or chat) and then `npm run align -- public/audio/voiceover.<ext> input/script.clean.txt` to get verified word timestamps in `src/words.json` (one-time Python environment and ~350 MB model; see `references/COMPONENTS.md`). Time scenes with `at('phrase')` from `src/lib/words.ts`. If it prints `WORD TIMING ESTIMATED` or `WORD TIMING NOT AVAILABLE`, continue with estimated timing and tell the buyer once in plain words; never block the video on it.
4. **Start safely with no setup questions.** Create a unique new project subfolder in the current working folder (in a chat: under `/home/claude/mograph-projects/`), e.g. `mograph-projects/<inferred-topic>-<timestamp>`, and bootstrap the bundled starter there. If the current folder is already an active user project, do not alter its files—write only inside the new dedicated subfolder. Never reuse older episode source, a global `node_modules`, or an author's personal assets. After `PREFLIGHT OK`, run `check-env`, `npm ci`, `prepare-vo`, `align`, typecheck, storyboard implementation, render and QC in sequence. If installation needs operating-system privileges, ask permission **only** for that specific system action.
5. **Make creative decisions independently.** The first frame should visualize the strongest consequence. Create custom illustrations/scenes for the supplied story, not a resized demo or caption slideshow. Pick the named look (§4A) and read its section in `references/STYLES.md` before building. Read `references/COMPONENTS.md` and reuse the component kit (phone/bank UI, rupiah counter, banknotes, charts, Indonesia map, characters, warung, callouts, word captions) wherever it explains the narration, customised per story. Run the sound department described in `references/SOUND_DESIGN_SYSTEM.md` **for every narrated video**, including a music/ambience layer, purposeful SFX, automated measurement, and human-judgment checks. Automatically inspect frames, fix obvious text clipping, layout, pacing and contrast, preserve source, and render a non-overwriting MP4 via the bundled safe renderer. Avoid copyright-infringing media acquisition or fabricated factual claims.
6. **Only stop for real blockers.** A `PREFLIGHT BLOCKED` result is a real blocker: relay its buyer message and stop. Ask one concise question if there are multiple indistinguishable voice/script candidates, missing/inaccessible required files, an unapproved external/privileged install, or a rights/accuracy issue that materially changes production. Otherwise proceed autonomously, including routine choices a human motion designer would make. Never ask “which style?”: pick the named look from the script (§4A).
7. **Deliver results, not instructions.** In a chat, present the MP4 from `/mnt/user-data/outputs/` (the source project stays in the sandbox; offer a zip of `src/` and `public/` only if asked). Report the final MP4 path, source folder, resolution, FPS, audio duration, which music source/mood arc was used, and any unresolved QC limitations. The process may take time and still needs a supported computer; zero-prompt means **zero repeated creative briefing**, not zero software prerequisites or guaranteed rendering on every machine.

## WHERE THIS RUNS (v1.5): Claude chat or Claude Code

The same skill and pipeline work in two places; `npm run check-env` prints which one (`Mode:` line).

- **Claude chat with code execution** (claude.ai web/desktop/mobile; `/mnt/user-data/outputs` exists). The buyer installs nothing: Node, FFmpeg, Python and a Chromium are in the sandbox. npm, PyPI and GitHub are reachable **only when the account allows network egress** (Settings → Capabilities → *Allow network egress*; on Team/Enterprise the organization owner decides; "package managers only" is enough). Remotion cannot be installed without it, so `preflight.mjs` runs first and turns a blocked registry into clear instructions for the buyer within seconds. Rules that shape the work:
  - **Every shell command is stopped after about three minutes.** Keep each command short. The two long steps resume by design: repeat `npm run align -- --setup` until it prints `MODEL READY`, and repeat `npm run render` until it prints `READY`. A run that ends with `NOT FINISHED` (exit code 2) has kept its finished parts: run the **exact same command** again; never change flags, delete `out/working`, or start over.
  - Create the project in `/home/claude/mograph-projects/<topic>-<time>` (or another writable folder outside `/mnt/user-data`). Attachments are in `/mnt/user-data/uploads`; `find-inputs` searches there first.
  - Remotion automatically uses the preinstalled Chromium (it cannot download its own browser here). **Never start `npm run dev`** (Remotion Studio needs a screen): inspect with `npm run stills` and look at its `sheet.jpg`.
  - The final MP4 is copied to `/mnt/user-data/outputs/` automatically (the `DELIVER:` line). Present that file in your reply so the buyer can play and download it.
  - A new chat starts from an empty sandbox, so setup runs again (npm ci ~10 s, word-timing model ~350 MB, about a minute). Do revisions in the same chat.
  - With 2 CPU cores, Remotion renders roughly 5–12 frames per second: a 40-second video takes 2–3 render runs (~5 min), a 60-second video 3–5 runs. After a small, local fix use `npm run render -- --redo A-B` (seconds) so only chunks touching that span are re-rendered.
  - Tell the buyer once, in one short line, that rendering takes a few minutes; never ask them to install anything or open a terminal.
- **Claude Code on the buyer's computer** (the Code tab in the Claude desktop app, or a terminal). Needs Node.js 20+, FFmpeg and optionally Python 3; Remotion downloads its own Chrome Headless Shell once. Commands have no three-minute limit, so `npm run render` normally finishes in one run, and `npm run dev` may be used when the buyer wants Studio. Deliver from `out/final/`.

Quality, defaults and release gates are identical in both places.

**Definition of done:** In a fresh working folder, a buyer invokes `/aibijan-mograph` with only a real VO and a script containing some ElevenLabs tags. The skill must discover both, remove tags from display text, construct original non-demo scenes, align to the VO, **add a rights-cleared music bed and appropriate SFX without an extra brief**, measure audio continuity, perform QC, and create a versioned final MP4 without requiring the buyer to type any extra prompt. If a step fails, fix it inside the project or report the exact blocker; never answer a failure by asking the buyer for a longer brief, and never edit the installed skill files.

## 0. Mandatory bootstrap and buyer safety (v1.3)

- Locate this skill directory, `SKILL.md`, the bundled `assets/starter`, and `scripts/bootstrap.mjs`. Use the **same installed directory**; do not look for or copy dependencies from the author's previous episode folders.
- For a genuinely new project, choose a dedicated **new or empty** working folder and run `node <skill-dir>/scripts/bootstrap.mjs <project-folder>`. This refuses non-empty destinations. **Never copy the starter over an existing user project**. If modifying a user's existing Remotion project, inspect it first and work in a separate branch/copy or integrate selectively with permission.
- In the project folder, run `node scripts/check-env.mjs` **before** installing. Missing Node/FFmpeg/ffprobe is a real setup blocker on a local computer. Explain the missing tool and let the user install it. Do not ask for broad permissions or silently install system packages. Locally, Chrome is optional because Remotion downloads a supported headless browser during the first render; in a chat sandbox the scripts use the preinstalled Chromium (`check-env` shows its path).
- Use **`npm ci` from this starter lockfile**; do not copy `node_modules` or a lockfile from another project. All direct Remotion imports (`remotion`, `@remotion/cli`, `@remotion/bundler`, `@remotion/renderer`) are pinned to the **same exact version**. If `npm ci` fails, report the precise error; do not substitute arbitrary upgrades without telling the user. If it fails with a network error (`E403`, `403 Forbidden`, `ENOTFOUND`, `EAI_AGAIN`, `ECONNREFUSED`, `ETIMEDOUT`, a proxy/tunnel error) or is cut off while fetching, run `node <skill-dir>/scripts/preflight.mjs` and relay its buyer message; never route around a blocked registry.
- For real narration, run `npm run prepare-vo -- <actual-vo-path>`; this measures duration and updates the project config. It **only proportionally stretches placeholder beats**. You MUST replace those beats with an audio-verified timing plan from the supplied script. Use `--replace` only after explicit user authorization to overwrite existing project narration.
- `src/project.json` starts with `needsStoryboard: true` **and** `needsSoundDesign: true`. After replacing placeholder scenes and designing/mixing the actual score/SFX, set both flags to `false` before the final `npm run render`. The render script **refuses a premature final** while either remains true. Silent draft previews are allowed for design review only.
- Starter metadata and content: `src/project.json` controls brand/title/VO/duration/beat fields and optional `musicFile`, `musicVolume`, `musicAutomation`. Starter components (`IsoCity`, `theme.ts`, generic hook/mechanism/payoff, the component kit in `src/components/kit`, `WordCaptions`), the bundled procedural SFX, and optional `npm run score` generator are **starting points to customize**, not mandatory scene choices. `src/project.json` starts with a silent 12-second demonstration; it must never be passed off as a delivered narrated video.
- Verify `npm run typecheck`, inspect `npm run stills -- out/qc <times>` (its `sheet.jpg` shows all times in one image; `npm run dev` only on a local computer), then use `npm run render` to create a **new versioned file** under `out/final`. Rendering is chunked and resumable (see “Where this runs”); `--redo A-B` re-renders only a small changed span, `--fresh` discards the cache. Do not render to or overwrite an existing MP4. For preview without narration only, `npm run render -- --silent-draft` is allowed, clearly labelled silent.
- The starter includes licensed redistributable fonts under `public/fonts`: Plus Jakarta Sans for headlines (`FONT`) and body text (`TEXT`), DejaVu Sans as automatic glyph fallback and DejaVu Sans Mono (`MONO`); licences in `OFL-plus-jakarta-sans.txt` and `FONT_LICENSE.txt`, so Linux, macOS, and Windows can render the same glyph metrics. The named looks add their own OFL fonts (Playfair Display + Courier Prime for ARSIP, Space Grotesk + IBM Plex Mono for BLUEPRINT, Caveat Brush + Patrick Hand for SKETSA, Cinzel + Cormorant Garamond for HIKAYAT, Archivo + Inter for GEOMETRIK, VT323 + JetBrains Mono for TERMINAL), registered by `<StyleFonts />` from `src/styles/shared.tsx`. Do not bundle Avenir Next or other system-only fonts; avoid OS-dependent emoji in critical labels, use vector icons.
- Respect brand neutrality: do not add the skill author's own channel branding or any other creator's logo, scripts, personal voice, or unique episode captions to a buyer's video unless they ask. Ask the buyer for their brand or use an unobtrusive neutral placeholder. Keep any user-provided private assets out of shareable skill packages.
- **Production is not one-click guaranteed.** In a chat the buyer needs code execution enabled and network egress allowed (package managers only is enough: npm, PyPI, GitHub); on a local computer a supported Node environment, available npm registry on first setup, FFmpeg and sufficient disk/CPU for Remotion. Both need an actual voice file. Word-level alignment is bundled (`npm run align`); if it reports estimated or unavailable timing, mark word cues estimated and ask the user to review with sound. The `KitGallery` and `StyleGallery` compositions are QA sheets for the kits and are never delivered; the render always uses `Mograph`.

## 1. Contract and inputs

- Accept Indonesian naturally; preserve the narration's language and intended meaning.
- Required for final production: the actual narration audio file, or explicit permission to create a silent draft. A script/transcript is strongly preferred. If absent, request it or clearly mark timing as provisional; **never pretend audio has been inspected**.
- Accept optional topic, script, MP3/WAV, supplied imagery/footage, brand colors, references, aspect ratio, FPS, style preferences, music, subtitles, and desired platform.
- Defaults: **1080 × 1920**, **60 FPS**, H.264 video + AAC audio, one video matched to narration duration, and optional 0.5–2 s intentional end hold if needed. Follow explicit user overrides.
- Treat user examples as direction, not assets to reproduce verbatim. Use only licensed/user-owned assets and fonts.
- Preserve existing files, projects, audio, and unrelated skills. Inspect before overwriting. Never modify `/aibijan-motion` as part of this skill.

## 2. Mandatory end-to-end workflow

1. **Preflight.** Bootstrap and check the isolated starter as described in §0. Inspect project state and available tools. Use `ffprobe` for narration duration and format; inspect whether there is silence at head/tail. Verify assets can legally be used. Identify factual claims and mark hypotheticals as simulations.
2. **Script breakdown.** Divide narration into meaningful beats, usually 1–5 seconds. Each beat needs a purpose: hook, situation, consequence, explanation, twist, payoff, or CTA. Build a timecoded plan from actual audio; avoid arbitrary timing based only on word count.
3. **Visual direction.** Choose the visual grammar by meaning, not by forcing every episode into the same layout. Decide a scene palette and 2–4 recurring identity cues. Prefer imagery that demonstrates the idea over subtitles that restate it.
4. **Storyboards and asset plan.** For each beat, specify on-screen action, dominant illustration, transition, spoken words/captions, audio cue, and source of assets. Identify scene complexity and honest 2D/3D limitations.
5. **Build.** Implement original scene components in Remotion/React/TypeScript. Assemble all beats into one continuous composition synchronized to the actual narration. Reuse appropriate starter helpers and components, but vary the framing and storytelling. The bundled three generic demo beats **must be replaced or redesigned**, not stretched to 60 seconds.
6. **Sound department.** Read and execute **`references/SOUND_DESIGN_SYSTEM.md`**. Work through its five internal roles, create a timecoded music-and-SFX plan matched to the actual VO, add an adaptive bed with deliberate emotional changes and defined intentional-silence windows, place/fade individual effects, and mix behind narration. If no licensed track is available, use the bundled **original procedural `npm run score` fallback** and document its sonic limitations; do not steal/reference-rip copyrighted audio. Set `needsSoundDesign: false` **only after** the mix has been reviewed.
7. **Render.** Use the safe versioned render script and produce the final MP4 at the requested resolution/FPS (in a chat: repeat `npm run render` until `READY`). A preview is not a completed delivery. If missing dependencies block work, report the exact blocker.
8. **Quality check.** Review first 3 seconds, scene boundaries, subtitles, visual readability, last frame, audio levels, and output metadata. Run `npm run audio-qc -- <out/final/your-video.mp4> [--plan src/audio-plan.json]`, investigate unintended dry gaps, and manually audition if audio playback is available. Fix defects and rerender before declaring the video ready. Automated continuity and LUFS measurements cannot judge taste or prove perfect SFX sync.
9. **Handoff.** Give actual MP4 path, source project path, render settings, scene list, music source/licensing notes, audio QC status, and outstanding limitations. Do not claim effects, source files, or checks that were not created or performed.

## 3. Narrative direction: information thriller

Build a short story rather than a PowerPoint deck.

- **0–2 seconds:** show the provocative *consequence*, not a logo, plain opening city, or long setup. Use an oversized focal visual/headline with contrast or motion. Examples: balance changes `Rp5.000.000 → Rp5.000`; a tropical city instantly gains snow. Make the consequence legible on the **first displayed frame** when possible.
- **2–8 seconds:** establish a relatable real-world situation: a bank app, a warung, a street, a phone, or a person making a decision.
- **Middle:** reveal one new thing per beat. Alternate wide illustration, close-up, diagram, and typographic emphasis. Make cause and effect physically visible where possible.
- **Payoff:** show the mechanism or surprising nuance, not just a definition.
- **Ending:** a short viewer-facing choice, dilemma, or question when the script supports it. No drawn-out branded outro.
- **Accuracy:** differentiate hypothetical scenarios, actual science, and real policy. For non-verified claims, seek sources or remove specifics. In simulations use a readable `SIMULASI` / `WHAT IF` label where needed; don't imply a policy takes effect tomorrow.

Every scene should answer: **What new information does this image provide? Why can't an unchanged background + subtitle convey it as well?** If the answer is weak, redesign the scene.

## 4. Adaptive visual grammar

Choose the named look first (§4A), then a purposeful combination of these **visual building blocks** drawn in that look's language:

| Building block | Good for | Execution |
| --- | --- | --- |
| Kinetic typography | Hook, big contrast, reveal, CTA | Per-word emphasis, masked text, numeric interpolation |
| UI simulation | Money, apps, QRIS, buying, social systems | Original stylized interfaces; avoid unauthorized brand replicas. Kit: `PhoneFrame`, `BankAppScreen`, `NotificationCard`, `ChatBubble` |
| Charts and infographics | Mechanisms, statistics, prices, temperatures | Animated SVG, scales, counters, and direct-label graphics. Kit: `RupiahCounter`, `BarChart`, `LineChart`, `PercentRing`, `Thermometer`, `PriceTag` |
| Vector illustration | Daily-life metaphor, characters, objects | React/SVG layered shapes with restrained motion. Kit: `PersonFigure`, `Warung`, `Banknote`, `Callout` |
| Maps and geographic stories | Indonesia, weather, trade, locations | Custom/appropriately licensed vector map with animated highlights. Kit: `IndonesiaMap` (Natural Earth, public domain) with pins and routes |
| Isometric / 2.5D scenes | Streets, shops, miniature cities, environments | Layered SVG or properly sourced rendered assets with depth masks |
| Live-action or image compositing | Emotional realism, places, products | Licensed/user assets; deliberate parallax and typography overlays |

### Visual selection heuristics

- **Financial explainer (e.g. rupiah):** strong number changes, paper money, bank-app-like UI, shopping examples, purchasing-power diagrams. Avoid using three nearly identical card scenes in a row.
- **Environment explainer (e.g. snow in Indonesia):** bright miniature city, weather layers, temperature graphics, map, changing vegetation, mountains; show the *world* changing.
- **Everyday behavior (e.g. cash rejected):** show the person, store, payment exchange, phone battery, signal, and consequences; prefer a visual micro-story over abstract icons alone.
- **Psychology/science:** make invisible mechanisms visible with illustration, experimentation, diagrams, and punchy contrast.
- Reset the arrangement and background regularly when doing so serves the narrative; don't randomly change styles every second. As a guideline, if a scene or camera composition stays substantially unchanged for **over ~4 seconds**, justify it with continuous illustrative development, or add a purposeful close-up/contrast/reframe. Avoid 8–10 seconds of tiny labels on the same diagram.

## 4A. Named looks (v1.6 + v1.7)

Eight complete looks, picked from the story without asking. Full specs, kit APIs, sound recipes and pitfalls: **`references/STYLES.md`** (read the chosen look's section before building).

| Look | Story it serves | Kit |
| --- | --- | --- |
| **MIDNIGHT** | what-if simulations, money, privacy, apps, suspense | v1.5 component kit, `theme.ts` |
| **MINIATUR** | places, cities, environment, crowds, area-wide policy | `IsoCity` + layered 2.5D (§6) |
| **ARSIP** | history, origins, myths vs facts, true stories, investigations | `src/styles/arsip/kit.tsx`: desk + cork wall, newspaper, polaroids, stamps, red string |
| **BLUEPRINT** | how something works: machines, infrastructure, safety, tech internals | `src/styles/blueprint/kit.tsx`: drawing that draws itself, iso exploded views, callouts, DETAIL panels |
| **SKETSA** | psychology, habits, feelings, studies about people | `src/styles/sketsa/kit.tsx`: lined notebook, boiling doodles, highlighter, page flips |
| **HIKAYAT** | legends, folklore, Nusantara history, myth vs science | `src/styles/hikayat/kit.tsx`: lit kelir, perforated wayang silhouettes, gunungan scene changes, lontar text, gamelan |
| **GEOMETRIK** | money, economics, statistics, rules of thumb | `src/styles/geometrik/kit.tsx`: Swiss grid, primary blocks as data, giant condensed numbers |
| **TERMINAL** | scams, data privacy, AI, systems behind the screen | `src/styles/terminal/kit.tsx`: green CRT, typed logs, block bars, glitch alerts, dithered pixel art |

- A buyer's "pakai gaya X" / "style X" always wins. Otherwise choose the look whose signature move can show the payoff; one look per video.
- Sound per look: `npm run style-sfx -- <arsip|blueprint|sketsa|hikayat|geometrik|terminal>` writes matching one-shots to `public/audio/sfx/<look>/`; `npm run style-score -- <look> src/audio-plan.json public/audio/music/<name>.wav` renders a bed in that look's sound from the plan's moods (optional `styleHits`). Both are original and offline; audition them like `npm run score`.
- Check the kits with `npm run style-preview` (`out/styles/sheet.jpg`). `references/examples/` holds one full demo per look: read them for structure, never reuse their story or text.

## 5. Color, typography, composition

Maintain a consistent identity **within each buyer's chosen channel or brand**, not uniform dark scenes across unrelated customers:

- Identity cues: confident editorial typography; clean geometric details; a recognizable small `SIMULASI`/series marker; yellow accent for key revelations when appropriate.
- Finance / serious suspense: midnight navy `#0D1233`, warm cream `#F8F1DD`, yellow `#FFC83D`. Use these as starting points, not an obligatory full-video background.
- Nature / lifestyle / illustrative episode: cream `#FFFDF6`, sky blue `#CDEBFC`, blue `#398CE6`, coral `#FF775A`, soft green `#9AD6AC`. Adjust contrast and illustration palette to the topic.
- Balance dark and light scenes deliberately. An environment-led episode can be predominantly bright; a serious explainer can be dark with clear contrast beats.
- Mobile-first: headline readable at native phone size. Reserve upper and lower UI margins; for 1080×1920 start with ~100 px side margins, ~160 px top breathing room, and avoid putting critical CTA/labels in the bottom ~280 px. Platform overlays vary; inspect a platform-safe preview.
- Do not use tiny grey disclaimers for essential information; readable contextual labels matter more than decorative detail.
- Use two font roles at most (e.g. bold clean sans for headlines; mono selectively for data/editorial texture). A named look brings its own two fonts; do not mix them with another look's. Prefer the starter's bundled licensed fonts; confirm any replacement font is legally distributable and renders accurately across operating systems.

## 6. Hybrid isometric and environmental scenes

Isometric scenes are the core of the **MINIATUR** look and a capability any look may borrow (redrawn in its language); they are not an excuse to fake photoreal 3D.

1. When a scene needs a city, street, building, snow, rain, or environment, first decide the minimal fidelity necessary: **layered vector 2.5D**, **pre-rendered 3D assets**, or **true 3D**.
2. Prefer reusable vector/isometric components for boxes, roads, signage, flat roofs, stores, trees, simplified people, cars, and weather. Organize into far/background, midground, foreground, weather, highlights, and captions.
3. To transform a tropical town into snow: preserve the base composition, add roof/surface snow masks, recolor ground, layer visible falling particles, change vegetation, and make the state transition readable. Avoid swapping unrelated full-screen images with a simple cut.
4. For depth and parallax, animate individual layers with controlled offsets/scaling. Keep consistent viewpoint, shadows, horizon, and light direction.
5. If a precise orbit, complex character rig, or physically accurate 3D camera move is required, use a proper 3D pipeline / user-provided renders or explain the dependency. A flattened image is **not** freely navigable 3D.
6. For imported illustrations, separate layers and animate them meaningfully; an unlicensed static screenshot is not an acceptable production asset.
7. Use animated maps, thermometers, safety signs, and climate diagrams to break up environment scenes. Don't show one identical wide city for 20 seconds.

## 7. Motion and audio rules

- Animate using frame/time-based deterministic Remotion primitives (`interpolate`, `spring`, Easing, composition FPS), not untracked real-time browser timers. Keep animation reproducible between preview and render.
- Default feel: fast but legible, intentional acceleration/deceleration, clean morph-like shape continuity, decisive cuts on narrative turns, modest parallax, and purposeful reveals. Avoid unnecessary bounce and permanent floating on every item.
- **Audio is the master clock.** Measure the supplied track, map scene and word cues against it, and align the strongest visual changes with the spoken payoff. Do not time animation solely by `script.length / guessed_words_per_second`. A silence-alignment report does **not** prove individual word sync; distinguish phrase-level verified alignment from estimated words.
- When forced alignment/transcription tools aren't available, create a small manual timing sheet and verify by playback or waveform analysis. Do not invent exact word timestamps.
- Captions: use `<WordCaptions>` (word-synced pages) whenever `src/words.json` has words; otherwise short meaningful phrases, 2–6 words when practical; avoid line wrapping over critical graphics. Make emphasized words sync to VO. Strip ElevenLabs expression tags such as `[whispers]` before using script text as on-screen captions. Extra slogans/stickers must not falsely appear to be quoted narration. Add subtitles only if they help, not as a replacement for imagery.
- **Default to a continuous adaptive music/ambience bed** under a narrated short, unless the story deliberately calls for silence. Let the Sound Director decide genre and sections from the script and measured voice. Do not rely on a high SFX count to compensate for missing music. Record any planned silence in `src/audio-plan.json` so QC can distinguish art-directed breaks from accidental dead air.
- Keep SFX purposeful, avoid interfering with VO, and use rights-cleared sound. **14 appropriate cues can be better than 30 random whooshes.** Music must sit behind narration, with gain automation/ducking, and take a clear emotional turn at the twist.
- Target approximately **−14 LUFS integrated** and **true peak ≤ −1.5 dBTP** for the final upload when tools permit; verify instead of assuming because a filename says '14LUFS'. Avoid distortion, clipping, chopped beginnings, or unintended silent tails. Run `npm run audio-qc` after mastering.

## 8. Remotion production practices

- Use typed scene components, a central timecoded `beats` list, a global theme, reusable objects, and clear asset paths. Support scene-level themes without duplicating the app.
- Import and render actual audio with the chosen Remotion media components. Match audio start/end against composition and output; keep output FPS fixed at 60 unless user requests another.
- Use available libraries only after checking actual project dependencies. Favor SVG and React geometry for charts, cards, labels, map primitives, and simple illustrations. Avoid installing large dependencies without need.
- Use exported PNG/SVG/rendered 3D objects as legitimate assets when native JSX is too costly; check transparency and pixelation. Do not automatically generate video-sized stills where shapes/components would be sharper.
- Keep code/assets editable, and preserve per-scene structure so future episode changes don't require rebuilding the entire project. Prefer data-driven brand/title/caption settings where possible; the buyer should request revisions in natural language, and the agent edits the code safely.
- Use unique React IDs for SVG gradients/clipPaths when several illustrated components appear on screen; avoid duplicate global `id` collisions. Do not assume the starter's `IsoCity` can handle arbitrary scenes without adjustment.
- Distinguish between actual smooth morph/track continuity and a dissolve that merely disguises a cut.

### Scene-planning schema

Before coding, produce or maintain a concise plan like:

```text
00:00–00:02 | HOOK | bank balance drops 5,000,000→5,000 | UI + numeric change | slam + freeze | 'Besok pagi...'
00:02–00:05 | CONSEQUENCE | viewer reaction / warning | character + card | camera push | 'saldo kamu...'
00:05–00:09 | TWIST | value unchanged | balance gauge / scale | morph | 'uangmu nggak hilang'
```

Actual times must be replaced with measured audio cues; this is format guidance, not a factual timing prescription for any particular MP3.

## 9. Release gates (do not skip)

**A. First-second gate:** Is there an understandable, emotionally interesting visual change within the first ~2 seconds? Check actual frames at 0.2, 1, 2 seconds. If the first frame says only "besok pagi" or shows an unchanged generic city, rework the hook.

**B. Visual diversity gate:** Across the full video, are there recognizable variations in scene type, scale, palette, and movement? Reject endless cards, blank typography, and repeated slides. With a named look, every scene must still read as that look (its paper/grid/desk, its two fonts, its captions); variety comes from stations, scale and camera, not from switching looks.

**C. Comprehension gate:** Can a muted viewer broadly follow the setup, mechanism, and outcome from imagery, not only reading captions?

**D. Mobile gate:** Review at reduced screen size. Check contrast, safe areas, subtitles, tiny charts/footnotes, and important elements.

**E. Synchronization gate:** Spot-check each speech transition and payoff. No premature captions, missing words, or visually incongruent scene changes.

**F. Export gate:** Verify by `ffprobe` or equivalent: 1080×1920, 60 FPS, output duration, video codec, and real audio stream. Measure loudness if tools available.

**F2. Sound department gate (v1.3):** Confirm a music/ambience stem exists or a documented artistic exception, SFX are synchronized, VO remains intelligible, actual mix matches the planned emotion arc, silence regions were intentionally chosen, and the delivered audio was measured via `npm run audio-qc`. Do not pass simply because the mix is −14 LUFS.

**G. Visual gate:** Render/contact-sheet representative frames (`npm run stills` writes `sheet.jpg`) (opening, middle, end, scene transitions). Inspect full-resolution representative frames and actual animation playback if accessible. Fix clipped text, malformed artwork, flicker, harsh cuts, blank frames, and frames that are mostly empty background (a station or page the camera reaches before its main object is there).

**H. Accuracy gate:** Verify claim-sensitive numbers, scientific facts, and policy references; label hypotheticals explicitly and avoid clickbait that misstates the scenario.

**I. Beginner gate:** Run a clean-project `npm ci` and typecheck when feasible; ensure the delivered source includes every directly imported package. Verify a Windows/Linux-safe font, no private credentials, and a clear instructions path. Keep raw previews and QC images separate from `out/final`, with versioned names that cannot silently overwrite a prior result.

Never claim a video is guaranteed to go viral/FYP. Optimize for clear curiosity, retention, and honest storytelling; success is measured after publication.

## 10. Delivery and iteration

When finished, report succinctly:

1. Exported MP4 and editable source project paths.
2. Actual run-time, dimensions, FPS, and audio measurement/check status.
3. The named look used and why (one line), visual design rationale and main scenes.
4. Any supplied/rendered third-party assets and their provenance/licensing assumptions.
5. Sound department summary: music source/rights, mood transitions, SFX approach, measured loudness/true peak, flagged unintended quiet spans, and intentional silences.
6. Remaining limitations and what could be improved in a revision.

Never modify, add or delete files inside the installed skill directory during a buyer session; it may be read-only or synced from the buyer's account. If the buyer wants to reuse a look across episodes, keep reusable components and notes inside their own project folder.

### Example activation (no creative prompt required)

For a normal buyer, attach the VO file and script, then send only:

```text
/aibijan-mograph
```

The longer examples below are **optional overrides**, not necessary setup steps:

```text
/aibijan-mograph
Create a 52-second Indonesian what-if explainer from this actual ElevenLabs MP3 and script.
Topic: rupiah redenomination. Format: 1080x1920, 60 FPS.
Show Rp5,000,000→Rp5,000 in the opening; explain unchanged purchasing power.
Build, render, inspect, and deliver the MP4 + Remotion source.
```

```text
/aibijan-mograph
Using this VO and script, create a bright environmental explainer about Indonesia
experiencing snow. Use an original isometric miniature neighborhood where roofs,
roads, and trees visibly change, plus animated thermometer and Indonesia map.
Keep the scenario labelled hypothetical. Render and QA the full video.
```

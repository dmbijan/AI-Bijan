# Premium talking-head direction — actionable scene design

Read this before planning any edit. The source talking head is always the protagonist. Motion graphics exist to
*clarify what the person is saying*, not to decorate dead space.

## Story-to-screen conversion

Read the fully aligned words (and the optional written script), and segment meaning beats:

| Beat | Spoken function | Frame / overlay | Motion | Don't do |
|---|---|---|---|---|
| 1. HOOK | surprising question or conflict | speaker face + one short line/graphic | decisive 0.25–0.45 s entry | a generic title sequence before the speaker |
| 2. CLAIM | establish what they argue | full frame + modest push-in | gentle face-anchored push | arbitrary blur/zoom for every word |
| 3. EXPLANATION | cause-and-effect | face in card OR a short `InsightCard`/`EvidenceCard` | morph from full frame, return to face | card just restates all captions |
| 4. EXAMPLE | contrast, proof, quote or scenario | correct real footage/asset, real waveform, or structured card | two-step reveal, one focus at a time | inventing stats, chart trends or documents |
| 5. PAYOFF | conclusion | full-frame expression, one highlighted phrase | held frame + restrained accent | fake dramatic revelation |
| 6. CTA | original request/offer | speaker back on screen + exact CTA | clean branded end card if necessary | changing what user sells |

Scene counts are guidance, not arbitrary cut quotas: target ~4–6 distinctly composed beats for a 25–35 s
advertisement, and allow important serious sentences to breathe. Clip boundaries and graphic entrances align to
real word timestamps. Prefer styles with conceptual *substance*: for example, if the presenter says 'tiga tahap',
show three true stages in a row; if they say 'sebelum/sesudah', show a genuine edit comparison from the same
recording; if they say 'lebih mahal', don't show a fake price unless a real price is given.

## Composition and movement

- Keep the face/eyes mostly inside the upper-middle safe area and do not let moving overlays cover mouth.
- Start with face in the first ~0.4 seconds. Put the actual spoken hook as typography in the next ~0.3–0.7 s.
- Motion should have acceleration/deceleration and visual continuity; one gesture can hand off to another
  component via 0.3–0.6 s morph rather than many unrelated hard cuts.
- For 60 FPS outputs, keep transitions continuous in seconds; do not double their speed merely because the
  frame count doubled. Titles, cards and camera interpolation render at 60 frame samples per second.
- Establish clear contrasts between framing, typography, and supporting graphic: full face → brief
  explanation card → face/card comparison → full face. Don't do only caption overlay for the entire video.
- Mobile 1080×1920: judge 360px-downscaled frame for readability. Avoid high-detail charts on small screens.
- Caption word matches the actual audio timeline and remains readable in bright/dark sections.

## Style-specific differentiation

Whatever the style, the concept decides the scenes (SKILL.md "Direct it first"): the style gives the language,
the script gives the pictures. The lines below describe each language, not a fixed set of graphics.

- **IMPACT**: selling. Anton slams, lime marker captions, one wipe for the transformation, a huge hook word behind
  the speaker, lists as tiles/steps, a hard CTA.
- **SYSTEM**: software doing the work. Terminal captions, prompts and chats that type in sync with the words,
  progress and "done" states only when spoken.
- **EDITORIAL**: mature and deliberate. Letterbox, filmic grade, serif title cards over a Scrim, a magazine-cover
  title behind the speaker, more breathing room, never two graphics at once.
- **CREATOR POP**: personality-first. Bold outlined captions, stickers around (never on) the face, a split-face
  before/after, a Cutout on the payoff - but not nonstop popping.
- **MOGRAPH MIX**: show, don't just tell, cut like a premium After Effects Reel. The face hooks and lands each point
  with a kinetic HeroTitle; between face beats the words become glass scenes in aurora light (3D phones, prompt window,
  feature checklist, cards, chat), entered with a gradient orb and left with sweeping bars. Everything eases (quint
  in/out, blur-to-sharp, masked text) - never bouncy. Scenes only illustrate what is being said, on the word it is said.
- **STORYTELLER**: direct it like a short film. The grade follows the emotion of every line (dream, warm, shock,
  whisper, serious, dark), the premise becomes something visible around the head (search bars, floating questions),
  the shock word slams in behind the speaker, the thesis gets a title with a light arc, and the ending goes quiet and
  dark into an iris to black. Respect the script's pauses: a dramatic pause stays a pause.

## Render quality review

Inspect stills at start, after each overlay settles, each layout transition, and the ending. Render a
0–6 s preview and watch it *at real time*. Fix any occluded face, stutter, unintelligible card, clipped text,
unreadable typography, poorly timed visual claim or crowded captions. At 60 FPS the export should be 60 FPS
in `ffprobe` and the MP4 should keep the real voice synchronized. If audio or visual correctness fails, don't
declare victory. If runtime can't finish a full render, report incomplete state instead of handing out a stub.

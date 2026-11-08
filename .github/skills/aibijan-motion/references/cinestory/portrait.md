# Portrait (9:16) — the vertical frame

CINÉSTORY is directed for a vertical phone screen by default (1080×1920 @ 60). Nothing is cropped from a landscape
film: every shot type has its own portrait framing, and the 2D layer has its own layout.

## Framings (automatic)
| shot | portrait framing |
|---|---|
| `ecu` | both eyes and the nose |
| `cu` | head and shoulders, eyes on the upper third |
| `medium` | waist up (the default reaction shot is `cu` in portrait: a face reads on a phone; a gesture with the hands — `think`, `shiver` — is framed waist up) |
| `mediumWide` · `wide` | the whole figure in its place, more depth than width, camera a little high |
| `track` · `lead` | from behind / in front, close to the line of the path; the way ahead above the figure |
| `ots` · `reveal` | well behind the shoulder: the shoulder is a dark edge low in frame, the thing seen fills the rest |
| `twoShot` | staggered in depth: past one person's shoulder onto the other's face (two people side by side do not fit) |
| `insert` | from above, hand + thing in the middle of the tall frame; a phone in use: over the shoulder onto its screen; a `target` the person looks at but does not hold (QR stand, sign, screen on a counter): on the eye line, close to its face |
| `establish` · `long` · places without people | the depth of the place (corridors, aisles, rows) with a wider vertical lens |

Every candidate framing is scored before use: the lens pushed by walls, the view to the subject / target blocked,
the subject outside the narrow frame, the subject far off centre, a surface right at the lens (a wall in a corridor,
an open door leaf) or in front of the person covering the picture. The best one is used; the preflight reports
`camera-crowded` / `subject-out-of-frame` when none is good — then give the people room (≥ 1 m around handling
marks), move a mark, or force another shot / side.

## Pacing
A static shot longer than 4.6 s is cut into a second framing in a VO pause (moving shots may run ~7 s; a forced face
framing or insert alternates too, `cu` / `insert` → `medium`). A reaction close-up comes only when the same face's last
close-up ended ≥ 4 s before (a `startle` / `freeze` keeps its own); the same face (or the same insert) in the same
framing twice in a row — also across a scene cut in the same place, and also when both are forced — becomes another
framing automatically. Force a shot only to fix something seen on the stills; forcing `cu` on every reaction makes the
film a row of faces. The director
warns (`⚠ pacing`) when the `mind` studio fills > 25 % of the film, one graphic object is the only thing on screen
for 4 shots in a row, or a person stays on screen > 9 s in a scene with ≤ 1 action — add reactions on the words.

## Light for a phone
The portrait grade opens the shadows and eases the vignette; in dark scenes a soft eye-light from the camera side
lights the face of the shot's subject (short reach: the room stays night). QA fails when more than 15 % of the shots
sink below a mean luma of 22, when a shot is practically black, or when more than 12 % of the middle of the
picture burns white for 0.5 s (a lamp in close-up, a pale screen glowing). Pale emissive screens and signs glow
just under white; floors (`tiles`, `vinyl`) are satin, so ceiling lights do not mirror into a white pool.

## Safe area and text
Kept clear: top 230 px (app bar), bottom 420 px (caption / buttons), right 150 px (button column). Inside it: tags
(`tl` `tr` `bl` `br` `c`), cards (top), lower thirds (above the captions), stamps, the clock, steps (stacked
vertically), callouts (led towards the side with room), title and end cards (wrapped), the disclaimer (top).
Captions: short word-timed lines (≤ 4 words) on a dark plate, the spoken word lit, centred in the lower third of the
safe area, hidden under the title / end card. Lower thirds sit on glass; the clock, titles and their subs carry a dark
edge — all read on a white floor, a bright sky or a shirt of the caption's colour. Once the end card shows, the
story's overlays are gone for good. Keep `ui` text short (≤ 28 characters a line). Titles, tags, cards, stamps and the
clock move off the subject's face automatically (below it when there is room, else above it; in a close-up that
fills the frame, to the top of the safe area over the hair). While a title / stamp / card / clock / steps or the title card is on screen
the director frames waist-up instead of a close-up (unless the shot is forced).

## Branding (the user's own, optional)
`aibijan-brand.json` in the project folder (or `stories/<id>/`) — the AI BIJAN brand kit:
```json
{ "brand": "Nama Channel", "handle": "@namachannel", "cta": "Ikuti untuk cerita berikutnya",
  "colors": { "accent": "#7fd8ff" }, "logo": "logo.png", "watermark": false, "intro": { "seconds": 1.2 } }
```
- outro: under the end card — logo, name, handle, CTA; the accent colour lights the spoken caption word;
- `watermark: true`: the logo small at the bottom right of the safe area, under the captions (portrait), top left in landscape;
- `intro`: the logo + name before the VO starts (needs `vo.offset` ≥ 0.9 s).
No kit → no branding. `"brand": false` in story.json switches a kit off for one film. Never put a channel name into
story text yourself.

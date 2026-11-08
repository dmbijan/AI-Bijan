# Characters

Stylized procedural 3D people (one rig, many people): sculpted heads, eyes with lids and lashes, brows,
lips, hair/caps/hijab/beards, clothes with woven or batik textures. Faces animate through brows, lids,
eyes, mouth shape and head; the mouth opens with the VO only for `talk` (no phoneme lip-sync).

## Looks
| look | who |
|---|---|
| `pemuda` | young man, dark blue hoodie, sandals |
| `pemuda-kaos` | young man, mustard t-shirt, messy hair |
| `pemudi` | young woman, long hair, sage jacket |
| `pemudi-hijab` | young woman, cream hijab, pink jacket |
| `bapak` | middle-aged man, batik shirt, glasses |
| `ibu` | middle-aged woman, navy hijab, batik jacket |
| `kakek` | old man, grey hair, white beard, glasses, cardigan |
| `anak` | child (~9), striped tee |
| `penjaga-mercusuar` | lighthouse keeper / fisherman: knit cap, grey beard, navy jacket |
| `perawat` | nurse: light hijab, teal scrubs |
| `dokter` | doctor: white coat, glasses |
| `pedagang` | market seller: mustard hijab, teal blouse |
| `pendaki` | hiker: beanie, orange jacket |

Run `npm run look:cast` to see them all (out/look/cast/_sheet.jpg).

## Customizing (`custom`)
Any look can be changed per story; the change holds for the whole film.
```json
{ "id": "rani", "look": "pemudi-hijab", "name": "Rani", "custom": { "hijab": "#3d5a80", "topColor": "#c9a227" } }
```
| field | values |
|---|---|
| `skin`, `hair`, `brow`, `lip`, `iris` | colours (`#rrggbb`) |
| `top` | `hoodie` · `tee` · `jacket`; `topColor`, `trim` colours; `batik` (ink colour → batik pattern) |
| `legs`, `sandal` | colours; `feet`: `sandal` · `shoe` |
| `hairStyle` | `crop` · `messy` · `long` · `bald` |
| `cap` | knit cap colour · `hijab` colour · `beard` colour · `glasses`: true |
| `scale` | body size (0.66 child … 0.95 tall man), `headScale` (1.05–1.22) |
| `face` | `{ jaw, cheek, nose, brow, chin }` 0…1 — face shape |

## Props (`"prop"` on the cast member)
| prop | shown | used by |
|---|---|---|
| `phone` | only while reading | `phone` (screen: `"phoneText": { "from": "Ibu", "text": "Sudah sampai?", "time": "23.47" }`) |
| `binoculars` | only while raised | `binoculars` |
| `lantern` | always, glowing (lights the scene around them) | `raise` lifts it |
| `flashlight` | always; casts a real light beam forward | `raise` aims it |
| `mug`, `book` | always, left hand | — |
A pen appears automatically for `write`.

## Faces
`neutral calm alert fear panic squint read sleep drowsy smile laugh sad think uneasy serious relief surprised curious`

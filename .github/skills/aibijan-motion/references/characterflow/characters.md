# Characters

One procedural rig (stylized, soft-plastic 3D look) shared by all looks: big expressive eyes with
lids/brows, blinks, eye darts, blush, mouth shapes; planted-foot walking with leg IK; arm IK for props
and gestures; breathing and weight shifts. All actions work for all looks.

| `look` | Who | Good as |
|---|---|---|
| `raka` | young man, swept dark hair, yellow T-shirt, blue jeans (socks indoors) | main "kamu/aku" (male) |
| `naya` | young woman, hair in a bun, coral hoodie, cream trousers | main "kamu/aku" (female) |
| `bima` | tall young man, curly hair, round glasses, sage hoodie, khakis | friend / the other person |
| `dimas` | young man, short crop, blue shirt, dark trousers | friend / classmate |
| `boss` | older man, grey hair, glasses, white shirt with dark-red tie | boss, lecturer, parent figure |
| `sari` | young woman in a hijab (pink), white tunic, long blue skirt | main "kamu/aku" (female, hijab), friend, colleague |
| `dinda` | young woman, shoulder-length hair, light-blue tee, jeans | main "kamu/aku" (female), friend, crush |
| `ibu` | mother in a green hijab, terracotta tunic, long skirt | mother, aunt, neighbour, teacher |
| `ayah` | father: greying hair, moustache, glasses, brown shirt | father, uncle, teacher, older colleague |
| `adit` | teenager / school student: white school shirt, grey trousers | pupil, younger sibling, junior |

Two people in one story should be two different looks.

## Outfit — the same look in other clothes / colours
`"outfit"` in the cast entry changes any of: `top` (colour), `topKind` (`tee` `shirt` `hoodie`), `sleeve`
(`short` `long`), `bottom` (colour), `bottomKind` (`pants` `shorts` `skirt`), `hair` (colour), `hairStyle`
(`swoop` `crop` `bun` `curly` `long` `hijab`), `scarf` (hijab colour), `glasses` (true / false), `skin`,
`shoes` (colour). Colours as `"#rrggbb"`. Examples:
```json
{ "id": "naya", "look": "naya", "outfit": { "hairStyle": "hijab", "scarf": "#3d5a80" } }
{ "id": "boss", "look": "boss", "outfit": { "top": "#2b3a55", "topKind": "shirt" } }
```
Use it to fit the story (school uniform, office shirt, a hijab) or to tell two people apart — not to
recreate a real person.

## Props
`prop` in the cast: `phone` (taken out of the pocket for `phone`, held in the right hand, screen lit),
`bag` (sling bag across the body, swung to the front and opened for `bag`), `both`.

## Expressions (for `"face"` overrides)
`neutral content happy sleepy asleep asleepSmile startled alert thinking worried anxious overwhelmed
dread exhale calm focused relieved embarrassed angry laughing sad talk confused surprised shy proud`

## Character proof sheet
The `CharacterFlowLook` composition renders every look, expression and gesture card:
`node scripts/stills.mjs out/look 0,6,12,18,24 CharacterFlowLook`.

## Not available
Custom faces/likenesses of real people, new body types or garments beyond the list above (dresses, jackets,
uniform badges), mouth lip-sync to the VO, hair/cloth simulation beyond the blanket (a hijab and long hair
move with the head, not by themselves).

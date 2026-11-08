# Sound (automatic)

`scripts/sound-story.ts` compiles the same plan as the picture and synthesises everything — no samples,
no third-party music, nothing to license. The VO is laid in untouched at `vo.offset`; everything else
ducks under speech (music −9 dB, ambience −5 dB, SFX −3 dB while the VO speaks). The final is mastered
to −14 LUFS, true peak ≤ −1 dBTP.

## Ambience per place (follows the cuts, cross-faded)
| place ambience | what you hear |
|---|---|
| `coast` | surf below the cliff, waves on the rocks, gusting wind, crickets at night, gulls by day, the lighthouse motor when `beam` is on |
| `gallery` | strong wind with a whistle on the railing, the sea far below, lamp motor + clockwork tick |
| `cottage` | room tone, muffled sea and wind, wall clock, oil-lamp hiss when `lamp` is on |
| `sea` | water lapping on the hull, open swell, the boat's generator when `boat` is on, gulls by day |
| `room` / `hall` / `outdoor` | room tone + faint crickets / long-hall air / night air + crickets (kos) |
| `studio` | airy neural shimmer |
| `hospital` | ventilation hum, distant monitor beeps, a far trolley |
| `market` | crowd murmur, distant calls, rustling plastic, a radio far away |
| `forest` | night insects, wind in the leaves, an owl now and then, twigs |

## Foley
Footsteps from the real walk/run cycles (on screen contacts), per surface: grass, wood (with creaks),
metal, tile/stone. Action sounds come automatically (see [actions.md](actions.md)); set changes too
(doors creak open / thud shut, lamps click, the lighthouse lamp clunks on, the sea light shimmers in,
the boat reveal swells).

## Score (one section per scene mood, D minor world resolving to D major)
| mood | music |
|---|---|
| `mystery` | low drone, sparse high bells and piano notes |
| `tense` | rising dissonant drone, sub pulses, ticking, a riser into the next scene |
| `calm` | soft pad, rare bells |
| `memory` | warm electric-piano chords |
| `told` | detuned pad, soft whooshes (a story told by someone) |
| `reveal` | major pad + rising bell arpeggio |
| `science` | light plucked arpeggio + pulse (explanations) |
| `warm` | D-major electric piano + kalimba sparkle (endings) |
| `cctv` | mains hum only |
The title card gets a low boom; `hit` adds a boom + whoosh; `quiet` drops the music (strategic silence);
`fade` cuts get a soft whoosh; the end card holds a resolving chord.

## Extra SFX names (`"sfx"` on a beat)
`knock` `door-open` `door-close` `slam` `click` `creak` `machine` `shimmer` `reveal` `hit` `heartbeat`
`rustle` `breath` `whoosh` `boom` `riser` `foghorn` `bell` `radio` `static` `wave-crash` `gull` `notif`
`ping` `tick` `thunder` `splash` `engine` `stamp` `wind-gust`

## Added for dynamic worlds
SFX names: `roll` (wheels on a floor), `beep` (monitor), `twig` (a twig snapping), `coins`, `bag` (plastic bag).
Doors of dynamic worlds creak open / thud shut on their own; pushing an object rolls automatically.

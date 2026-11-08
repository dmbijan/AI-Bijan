# Sound (automatic)

`scripts/sound-story.ts` (run by `make.mjs`) compiles the story exactly like the picture and builds
`public/audio/<id>-mix.wav` entirely from synthesis (no samples, no licensing issues):

- **Ambience** per environment and time (campus: open air, distant chatter, bird chirps; bedroom: low room tone + night crickets or daytime birds outside; crickets in awkward-silence moments).
- **Footsteps** from the real heel strikes of every walker and every turn; sneakers outdoors, socks indoors.
- **SFX per action** (pop, squeak, sparkle, boing, ding, slap, clap, zip, whoosh, heartbeat, breath, click…, see [actions.md](actions.md)), panned to where the person is.
- **Music**: an original adaptive bed; baseline from `mood` (`playful`, `warm`, `tense`, `calm`) and sections that follow the acting (sad/awkward/warm moments), nearly silent (with crickets) under awkward moments and the tumbleweed gag.
- **Mix**: everything is ducked under the voice (≈ −16 dB below the VO while speaking); the VO is never altered, cut or covered.
- **Master** (final): −14 LUFS integrated, −2 dBFS true-peak ceiling, AAC 48 kHz.

No new voices or dialogue are ever added. To change the music feel, change `mood`.

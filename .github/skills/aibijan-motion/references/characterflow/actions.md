# Action library

Every action works for every look (one shared rig: planted-foot walking with leg IK, arm IK, 5 performance channels — body, left arm, right arm, face, gaze — with blinks, breathing and eye darts on top). An action lasts until the same person's next beat; arm actions release the arm by themselves after a moment.

## Blocking — moves people

| Action | What happens | Needs |
|---|---|---|
| `walk` | walks along the path (natural speed, real footsteps); a later `walk` resumes speed after a slow-down | campus, person **starts** walking (first beat of the scene or `start: walk`) |
| `stop` | slows and stops; stands there afterwards (can still turn, act) | a walker |
| `pass` | both walk toward each other and pass at this word (whoosh), then stop a little later | campus, 2 people |
| `meet` | both walk toward each other and stop face to face at this word | campus, 2 people |
| `turn` | turns around 180° in place (feet re-plant) | standing |
| `sitDown` / `standUp` | sits on / stands up from the seat (bench, desk chair, school desk, café chair, sofa, armchair — the `start` seat, else the first seat) | person starts on a seat, `sit` or `stand` (not a walker) |
| `lieDown` / `sitUp` | lies down / sits up in bed | bedroom, start `lie` or `sitBed` |

Standing people who `wave`/`smile`/`talk`/`explain`/`point`/`highFive`/`laugh`/`happy`/`nod`/`shrug`/`proud` at someone behind them turn toward them first (automatic). While walking, acting actions play on top of the walk and slow it down where reading would be unclear (`think`, `phone`, `confused`, `notice`, …).

## Acting — plays on top of the body

| Action | What you see | Default shot | Sound |
|---|---|---|---|
| `idle` | settles, looks at target/ahead | medium | — |
| `talk` | conversational right-hand gestures | medium | — |
| `explain` | bigger explaining gestures | explain | pop |
| `point` | points at target (or forward) | explain | pop |
| `think` | hand to chin, eyes up; good with a `thought` | think (headroom) | bubble pop (with a thought) |
| `confused` | scratches head, "?" bubble | think | squeak |
| `happy` | bounce + big smile | reaction | sparkle |
| `laugh` | laughing shake | medium | sparkle |
| `sad` | slumps, looks down | close-up | — |
| `embarrassed` / `shy` | hand to neck, eyes away, blush | reaction / close-up | squeak |
| `surprised` | recoil, "!" bubble | reaction | boing |
| `wave` | waves at target | medium | high pop |
| `facepalm` | hand on face, slump | close-up | slap |
| `notice` | head snaps to target | over-the-shoulder | ding |
| `eyeContact` | both look at each other (2 people) | split close-ups | ding |
| `phone` | takes the phone out, reads/scrolls; puts it away when the hand is needed | medium | whoosh |
| `bag` | swings the bag to the front, opens it, rummages | medium | whoosh + zip |
| `lookBack` | looks back over the shoulder | close-up | pop |
| `highFive` | both high-five (2 people, near each other) | two-shot | clap + sparkle |
| `nod` / `shake` | nods yes / shakes no | close-up | — |
| `shrug` | palms-up shrug | medium | squeak |
| `overwhelmed` | head in hands, trembling, fast breath | medium | heartbeat |
| `calm` | hand on chest, long exhale | medium | breath |
| `smile` | warm smile at target | close-up | sparkle |
| `proud` | hands on hips | medium-wide | — |
| `lampOff` / `lampOn` | reaches to the bedside lamp, light changes | — | click |

While the phone is in hand, `think`/`confused`/`embarrassed`/`shy`/`calm` keep it (face and bubble only) instead of putting it away.

**Lying in bed:** acting is face, gaze and thought bubble only (arm/body gestures are for sitting or standing); `lampOff`/`lampOn` work lying down. `phone`/`bag` need `sitUp` first (the director stops with that message). For gestures in bed, `sitUp` first — sitting in bed allows all acting actions.

## Expressions
Each action brings its expression; override with `"face"`: `neutral content happy sleepy asleep asleepSmile startled alert thinking worried anxious overwhelmed dread exhale calm focused relieved embarrassed angry laughing sad talk confused surprised shy proud`.

## Not in this version
Running, jumping, dancing, carrying/handing objects other than phone and bag, eating/drinking, writing (exists only in a hand-built example, not in the director), hugging, fighting, lip-sync to the VO, walking on paths other than the main straight one, more than 2 people.

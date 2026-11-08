# Advanced: adding a world

Only when the buyer agrees that a new location is worth a longer job (several hours of building and
look-checking). Everything else in the engine stays untouched; a world is two files plus registry lines.

## The contract
1. `src/worlds/<id>/data.ts` exports a `WorldData` (see `src/worlds/types.ts`):
   - `places`: for each place — `kind` (interior/exterior), `floor`, `marks` (where people stand/sit/lie,
     with `yaw`, `seat`, `bed`), `pois` (things to look at), `box` (camera-safe space) and `solids`
     (furniture/walls the camera must avoid), `ground(x,z)` for terrain, `establish` hint, `view` POI,
     `ambience` (`room` `hall` `outdoor` `studio` or one of the coast ones), `surface`, `reverb`.
   - `times`, `props` (with `default`, `doc`, optional `sfx`), and `light(place, time, set, t)` returning
     the fixed light rig: hemisphere + moon/sun (shadowed, box fitted to the place) + 2 shadow spots +
     6 points. Keep the rig shape constant (three.js must never recompile).
2. `src/worlds/<id>/<Id>.tsx` exports the Set component `({ place, time, s, t, cam }) => JSX`, built from
   `src/cine/set/parts.tsx` (Slab, HoledWall, Door, Window, Bed, Desk, Chair, OilLamp, Shelf, WallClock),
   `src/cine/env/Sky.tsx` (SkyDome), `src/cine/env/Ocean.tsx`, `src/cine/FX.tsx` (Shaft, Dust, Glow, Beam).
   Everything must be a pure function of `t` and `s` (no clocks, no randomness without a seed).
3. Register it in `src/worlds/index.ts` (data) and `src/worlds/sets.tsx` (component), add lookdev views
   in `src/lookdev/views.ts` + a `world-<id>` composition in `src/Root.tsx`.

## Rules that keep it working
- Close every building (outer walls, roof) or the moon lights everything through the gaps.
- Big transparent volumes (beams, halos, mist) go on the FX layer (`Glow`, `Beam`, `Shaft` do this) —
  on the normal layer they break depth of field / ambient occlusion and can blank frames.
- Light that a story changes is a prop (`lamp`, `tubes`), read in both `light()` and the Set.
- Check with `node scripts/stills.mjs out/look/<id> 0,1,2,… world-<id>` from establishing, interior
  and night/day views, then with a short story (`--stills`) before using it in a real film.

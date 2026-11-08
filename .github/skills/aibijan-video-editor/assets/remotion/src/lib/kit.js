// Building blocks for per-video custom components (creative mode). Import everything from here so a custom file
// never depends on the internal layout of the components folder:
//
//   import React from 'react';
//   import {AbsoluteFill, SceneFrame, theme, dispFont, tw, qOut, prog, lerp, Icon, Chip, MaskWord} from '../../lib/kit.js';
//
// Contract: export const MyScene = ({E, t, o}) => <...>;  pure function of t (seconds on the edited timeline),
// o = the overlay from edit.json (times already resolved to seconds), E = the whole edit (E.style.colors, E.width...).
export {AbsoluteFill, Img, OffthreadVideo, staticFile} from 'remotion';
export {bezier, clamp, dirBlur, ease, lerp, prog, rand, sample, shutterBlur, vel} from './anim.js';
export {Icon, ICONS} from '../components/Icons.jsx';
export {faceOnCanvas} from '../components/Footage.jsx';
export {qOut, qIn, qInOut, cInOut, backOut, eOut, tw, fx, pick, rgba, pal, glass, theme, dispFont,
  AccentText, MaskWord, Chip, SceneFrame, LiveClip} from '../components/Scenes.jsx';
export {SceneBuild, renderItem, itemMotion, kitColors, FACE_SLOTS} from '../components/Kit.jsx';
export {THINGS, THING_NAMES} from '../components/Things.jsx';
// v3.7 depth pack: type behind the speaker, head rings, doodles, the feed wall, plus the type helpers they use
export {DepthText, HeadRing, Doodles, FeedWall, DOODLE_KINDS, measureText, typeLook, toneColor} from '../components/Depth.jsx';
// v3.10 note pack: mono labels typed above the head, paper tags, the paper board, clip cards around the head
export {HeadNote, NoteTags, NoteBoard, NoteClips, BigNote} from '../components/Notes.jsx';

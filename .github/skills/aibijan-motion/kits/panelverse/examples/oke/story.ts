// PANELVERSE example — "OKE" (format reference only: never copy its beats into another film).
// Every frame below is anchored to a spoken word through the clock, so the film re-times itself to
// any recording of script.txt.
import { WORDS } from "./words.ts";
import {
  story, clock, S, FULL, LAYOUTS, LOOKS, EXPR, SET_PRESETS, YELLOW, RED, PAPER,
  frameCam, camKey, dollyKeys, jitterKeys, panel, enter, move, fx, sfx,
  type ShotDef, type PoseKey, type ExprKey, type Pose, type V3, type Quad,
} from "../../kits/panelverse/director/kit.ts";

const W = clock(WORDS, { offset: 0.2 });
// anchors
const chat = W.at("chat"), oke = W.at("oke"), dalam = W.at("dalam"), otak = W.at("otakmu");
const tiga = W.at("tiga"), sken = W.at("skenario"), dia = W.at("dia"), marah = W.at("marah");
const kamu = W.at("kamu", 2), salah = W.at("salah"), ngomong = W.at("ngomong");
const atau = W.at("atau"), hubungan = W.at("hubungan"), kalian = W.at("kalian"), tamat = W.at("tamat");
const padahal = W.at("padahal"), dia2 = W.at("dia", 2), makan = W.at("makan"), bakso = W.at("bakso"), baksoEnd = W.end("bakso");
const END = W.voEnd + S(0.9);

const SIT: Partial<Pose> = { pelvis: [0, -0.36, 0], footL: [0.19, 0.08, 0.52], footR: [-0.19, 0.08, 0.5] };
const HOLD: Partial<Pose> = { handL: [0.17, 0.42, 0.36], handR: [-0.17, 0.42, 0.36], elbowL: [0.7, -0.6, -0.2], elbowR: [-0.7, -0.6, -0.2], twistL: -0.4, twistR: 0.4, fistL: 0.2, fistR: 0.2 };
const PHONE_POS: V3 = [0, 0.5, 0.42];
const PHONE_ROT: V3 = [1.1, Math.PI, 0];

// ── 1 · room: the message → freeze → dive into the thought ─────────────────────────────────────
const FACE: V3 = [0.06, 1.8, 0.1];
const DIR: V3 = [0.46, -0.18, 2.9];
const room: ShotDef = {
  start: 0,
  set: { ...SET_PRESETS.bedroomNight, items: (SET_PRESETS.bedroomNight.items ?? []).map((it) => (it.type === "clock" ? { ...it, keys: [{ f: 0, v: { t: 0 } }, { f: dalam, v: { t: dalam / 60 }, ease: "linear" }, { f: dalam + 1, v: { t: dalam / 60 } }] } : it)) },
  setKeys: [{ f: 0, v: { wall: "blue", wallDots: 0.2 } }, { f: dalam - 1, v: { wall: "blue", wallDots: 0.2 } }, { f: dalam, v: { wall: "navy", wallDots: 0.45 }, ease: "hold" }],
  bg: "#2674B9",
  light: [-0.6, 0.7, 0.55],
  ink: 1.2,
  camera: [
    { f: 0, v: { pos: [1.05, 1.25, 4.1], target: [0.05, 1.55, 0.1], fov: 34, roll: -0.03 } },
    { f: chat + 14, v: { pos: [0.72, 1.5, 3.95], target: [-0.02, 2.0, 0.3], fov: 36, roll: -0.02 }, ease: "inOut" },
    { f: oke - 18, v: { pos: [0.68, 1.52, 3.7], target: [0.04, 1.98, 0.3], fov: 35, roll: -0.01 }, ease: "inOut" },
    { f: oke, v: { pos: [0.67, 1.52, 3.66], target: [0.05, 1.97, 0.3], fov: 35, roll: -0.01 }, ease: "linear" },
    { f: oke + 9, v: { pos: [0.92, 1.76, 2.95], target: [0.44, 1.98, 0.56], fov: 20, roll: 0.05 }, ease: "expoOut" },
    { f: oke + 31, v: { pos: [0.92, 1.77, 2.92], target: [0.44, 1.99, 0.56], fov: 19.5, roll: 0.045 }, ease: "linear" },
    { f: dalam - 8, v: { pos: [FACE[0] + DIR[0], FACE[1] + DIR[1], FACE[2] + DIR[2]], target: FACE, fov: 30, roll: -0.02 }, ease: "inOut" },
    { f: dalam, v: { pos: [FACE[0] + DIR[0], FACE[1] + DIR[1], FACE[2] + DIR[2]], target: FACE, fov: 30, roll: -0.02 }, ease: "linear" },
    ...dollyKeys(dalam + 1, otak + 4, FACE, DIR, Math.hypot(...DIR), 30, 66, -0.02).slice(1),
    { f: otak + 34, v: { pos: [0.42, 2.35, 1.55], target: [0.62, 3.0, 0.1], fov: 54, roll: 0.03 }, ease: "inOut" },
    { f: tiga - 7, v: { pos: [0.62, 3.0, 0.72], target: [0.62, 3.0, 0.0], fov: 50, roll: 0.02 }, ease: "in" },
  ],
  shakes: [{ at: oke, dur: 22, amp: 0.03, seed: 2 }, { at: dalam, dur: 12, amp: 0.02, seed: 4 }],
  actors: [
    {
      cast: "raka",
      pose: [
        { f: 0, v: { pos: [0, 0, 0.05], ...SIT, ...HOLD, spine: [0.16, 0, 0], head: [0.3, 0.02, 0.02] } },
        ...jitterKeys(4, chat - 4, 5, (i) => ({ handL: [0.17, 0.42 + (i % 2 ? 0.018 : 0), 0.36] as V3, handR: [-0.17, 0.42 + (i % 2 ? 0 : 0.018), 0.36] as V3 })),
        { f: chat + 4, v: { handL: [0.17, 0.52, 0.4], handR: [-0.17, 0.52, 0.4], spine: [0.04, 0, 0], head: [0.16, 0.04, 0.05] }, ease: "outBack" },
        { f: oke - 50, v: { handL: [0.17, 0.47, 0.38], handR: [-0.17, 0.47, 0.38], spine: [0.2, 0, 0], head: [0.3, 0.08, 0.07] }, ease: "inOut" },
        { f: oke, v: { spine: [0.22, 0, 0], head: [0.32, 0.06, 0.07] } },
        { f: oke + 7, v: { spine: [-0.08, 0, 0], head: [0.06, 0, -0.04], shrug: 0.7 }, ease: "outBack" },
        { f: dalam - 10, v: { spine: [-0.02, 0, 0], head: [0.12, -0.08, -0.06], shrug: 0.4 }, ease: "inOut" },
        { f: dalam, v: { spine: [-0.02, 0, 0], head: [0.12, -0.08, -0.06], shrug: 0.4 } },
        { f: dalam + 8, v: { spine: [-0.1, 0, 0], head: [-0.02, 0.12, 0], shrug: 1, squash: 1.03 }, ease: "outBackBig" },
        { f: otak, v: { head: [-0.02, 0.12, 0] } },
        { f: otak + 18, v: { head: [-0.3, 0.22, 0.06], spine: [-0.14, 0.05, 0] }, ease: "inOut" },
      ] as PoseKey[],
      expr: [
        { f: 0, v: { ...EXPR.hopeful, look: [0, -0.85], mouth: "smile", mouthW: 0.9 } },
        { f: chat - 2, v: { ...EXPR.hopeful, look: [0, -0.85], mouth: "smile", mouthW: 0.9 } },
        { f: chat + 4, v: { ...EXPR.hopeful, look: [0, -0.8], mouth: "grin", mouthOpen: 0.45, mouthW: 0.95 }, ease: "outBack" },
        { f: oke - 50, v: { ...EXPR.hopeful, eyeSize: 1.08, look: [0.1, -0.8], mouth: "smile", mouthW: 1, mouthOpen: 0 } },
        { f: oke + 1, v: { ...EXPR.hopeful, eyeSize: 1.08, look: [0.1, -0.8] } },
        { f: oke + 9, v: { ...EXPR.concerned, eyeSize: 1.14, look: [0.05, -0.75] }, ease: "outBack" },
        { f: dalam - 12, v: { ...EXPR.worried, look: [0.15, -0.5] }, ease: "inOut" },
        { f: dalam, v: { ...EXPR.worried, look: [0.15, -0.5] } },
        { f: dalam + 8, v: { ...EXPR.panic, look: [0, 0] }, ease: "outBackBig" },
        { f: otak, v: { ...EXPR.panic, look: [0, 0] } },
        { f: otak + 16, v: { ...EXPR.panic, look: [0.55, 0.9] }, ease: "inOut" },
      ] as ExprKey[],
      torso: [
        {
          kind: "phone",
          keys: [
            { f: 0, v: { pos: PHONE_POS, rot: PHONE_ROT, screen: { kind: "chat", title: "Dinda", messages: [{ from: "me", scribble: 6, reveal: 0 }] } } },
            { f: chat, v: { screen: { kind: "chat", title: "Dinda", messages: [{ from: "me", scribble: 6, reveal: 0 }] } } },
            { f: chat + 10, v: { screen: { kind: "chat", title: "Dinda", messages: [{ from: "me", scribble: 6, reveal: 1 }] } } },
            { f: oke - 1, v: { screen: { kind: "chat", title: "Dinda", messages: [{ from: "me", scribble: 6, reveal: 1 }], typing: 40 } } },
            { f: oke, v: { screen: { kind: "chat", title: "Dinda", messages: [{ from: "me", scribble: 6, reveal: 1 }, { from: "them", text: "Oke", reveal: 1 }] } }, ease: "hold" },
            ...jitterKeys(oke - 18, oke - 2, 2, (i) => ({ rot: [PHONE_ROT[0], PHONE_ROT[1], i * 2 < 14 ? (i % 2 ? 1 : -1) * 0.055 : 0] as V3 })),
          ],
        },
        { kind: "buzz", from: oke - 18, to: oke - 4, keys: [{ f: 0, v: { pos: [0, 0.52, 0.5], rot: [-0.35, 0, 0] } }] },
      ],
    },
  ],
  props: [
    {
      kind: "bubble", from: chat, to: dalam - 8,
      keys: [
        { f: chat, v: { w: 0.55, h: 0.42, tail: "right", fill: YELLOW, content: { kind: "squiggle", lines: 5 }, pos: [0, 1.25, 0.5], rot: [0, 0.15, 0], scale: 0 } },
        { f: chat + 14, v: { pos: [-0.2, 2.5, 0.75], rot: [0, 0.15, 0.06], scale: 1 }, ease: "outBack" },
        { f: oke + 8, v: { pos: [-0.28, 2.58, 0.72], rot: [0, 0.15, 0.08], scale: 0.9 }, ease: "outBack" },
        { f: dalam - 8, v: { pos: [-0.5, 2.7, 0.3], scale: 0 }, ease: "inBack" },
      ],
    },
    {
      kind: "bubble", from: oke - 50, to: oke - 1,
      keys: [
        { f: oke - 50, v: { w: 0.3, h: 0.16, tail: "left", content: { kind: "dots", phase: 0 }, pos: [0.36, 1.62, 0.56], rot: [0, 0.2, 0], scale: 0 } },
        { f: oke - 42, v: { pos: [0.42, 1.74, 0.56], scale: 1, content: { kind: "dots", phase: 8 } }, ease: "outBack" },
        { f: oke - 1, v: { content: { kind: "dots", phase: 60 } }, ease: "linear" },
      ],
    },
    {
      kind: "bubble", from: oke, to: otak + 34,
      keys: [
        { f: oke, v: { w: 0.42, h: 0.25, tail: "left", content: { kind: "text", text: "OKE" }, pos: [0.42, 1.74, 0.56], rot: [0.05, 0.18, 0.08], scale: 0 } },
        { f: oke + 10, v: { pos: [0.44, 1.98, 0.57], rot: [0.05, 0.16, -0.04], scale: 1 }, ease: "outBackBig" },
        { f: oke + 31, v: { pos: [0.44, 1.99, 0.57] }, ease: "linear" },
        { f: dalam - 8, v: { pos: [0.3, 2.3, 0.45], rot: [0.05, 0.1, -0.06], scale: 0.9 }, ease: "inOut" },
        { f: otak + 34, v: { scale: 0 }, ease: "inBack" },
      ],
    },
    { kind: "thoughtTrail", from: otak, keys: [{ f: 0, v: { from: [0.25, 2.3, 0.15], to: [0.62, 2.92, 0.1], at: otak + 6 } }] },
    { kind: "thoughtCloud", from: otak, keys: [{ f: otak + 22, v: { pos: [0.62, 3.0, 0.1], scale: 0 } }, { f: otak + 36, v: { scale: 0.85 }, ease: "outBackBig" }, { f: tiga - 7, v: { scale: 1.35 }, ease: "inOut" }] },
  ],
};

// ── 2–4 · the three feared scenarios (one clock from the moment the page appears) ───────────────
const SC = tiga - 13;
const L = (g: number) => g - SC;
const AKIMBO: Partial<Pose> = { handL: [0.37, 0.12, 0.02], handR: [-0.37, 0.12, 0.02], elbowL: [1, 0.1, -0.35], elbowR: [-1, 0.1, -0.35], fistL: 1, fistR: 1, twistL: 0.6, twistR: -0.6 };
const rage: ShotDef = {
  start: SC,
  set: { kind: "stage", look: "burst", a: "red", b: "yellow" },
  bg: RED, light: [-0.45, 0.6, 0.8], ink: 1.15,
  camera: [
    camKey(0, frameCam([0, 1.95, 0], [0.1, -0.05, 1], 1.55, 0.235, 24)),
    camKey(L(dia - 8), frameCam([0, 1.9, 0], [0.16, -0.12, 1], 1.7, 0.235, 24), "inOut"),
    camKey(L(dia + 14), frameCam([0, 1.55, 0], [0.24, -0.34, 1], 3.5, 1, 34, 0.03), "inOut"),
    camKey(L(kamu + 8), frameCam([0, 1.7, 0], [0.26, -0.38, 1], 2.8, 1, 34, 0.06), "inOut"),
  ],
  shakes: [{ at: L(marah) + 2, dur: 18, amp: 0.07, seed: 3 }],
  actors: [{
    cast: "dinda",
    pose: [
      { f: 0, v: { ...AKIMBO, spine: [0.04, 0, 0], head: [0.06, -0.3, 0.05] } },
      ...jitterKeys(10, L(dia) - 10, 18, (i) => ({ head: [0.06, i % 2 ? -0.08 : -0.32, i % 2 ? -0.04 : 0.06] as V3 })),
      { f: L(dia) - 6, v: { head: [-0.02, 0, 0], spine: [0.02, 0, 0] }, ease: "outBack" },
      { f: L(dia) + 2, v: { squash: 0.93, pelvis: [0, -0.06, 0], footR: [-0.15, 0.42, 0.2], spine: [-0.1, 0, 0.04] }, ease: "out" },
      { f: L(marah) + 2, v: { squash: 1.08, pelvis: [0, 0, 0], footR: [-0.17, 0.08, 0.14], spine: [0.12, 0, 0], handR: [-0.5, 1.12, 0.36], elbowR: [-1, -0.4, 0.2], twistR: 0 }, ease: "in" },
      { f: L(marah) + 8, v: { squash: 0.96 }, ease: "out" },
      { f: L(marah) + 14, v: { squash: 1 }, ease: "inOut" },
      ...jitterKeys(L(marah) + 16, L(kamu + 12), 4, (i) => ({ handR: [-0.5 + (i % 2 ? 0.05 : -0.04), 1.12 + (i % 2 ? 0.04 : 0), 0.36] as V3 })),
    ] as PoseKey[],
    expr: [
      { f: 0, v: { ...EXPR.angry, mouth: "frown", mouthW: 0.9, mouthOpen: 0, tremble: 0.3 } },
      { f: L(dia) - 14, v: { ...EXPR.angry, mouth: "frown", mouthW: 0.9, mouthOpen: 0, tremble: 0.3 } },
      { f: L(dia) - 6, v: { ...EXPR.angry, mouth: "shout", mouthOpen: 0.7 }, ease: "outBack" },
      { f: L(marah) + 2, v: { ...EXPR.angry, mouth: "shout", mouthOpen: 1, eyeOpen: 0.7, tremble: 1 }, ease: "outBack" },
    ] as ExprKey[],
    head: [{ kind: "steam", keys: [{ f: 0, v: { pos: [0, 0.48, 0], count: 4, spread: 0.3, size: 0.11, rise: 0.75, period: 40, amount: 0.7 } }, { f: L(marah), v: { amount: 1.3, period: 26 } }] }],
  }],
  props: [{ kind: "impactStar", keys: [{ f: L(marah) + 1, v: { pos: [-0.18, 0.02, 0.3], scale: 0, rot: 0 } }, { f: L(marah) + 6, v: { scale: 1.15, rot: 0.2 }, ease: "outBackBig" }, { f: L(marah) + 24, v: { scale: 0, rot: 0.3 }, ease: "in" }] }],
};
const BALLOON: V3 = [0.42, 2.95, 0.25];
const interro: ShotDef = {
  start: SC,
  set: { kind: "stage", look: "spotlight", a: "night", b: "yellow" },
  bg: "#13284A", light: [0.1, 0.85, 0.55], ink: 1.15,
  camera: [
    camKey(0, frameCam([0, 1.6, 0], [0.12, 0.32, 1], 2.3, 0.21, 24)),
    camKey(L(dia), frameCam([0, 1.6, 0], [0.15, 0.34, 1], 2.1, 0.21, 24), "inOut"),
    camKey(L(kamu - 13), frameCam([0.12, 1.9, 0], [0.2, 0.42, 1], 4.0, 1, 34, -0.06)),
    camKey(L(ngomong), frameCam([0.14, 1.95, 0], [0.18, 0.4, 1], 3.4, 1, 34, -0.03), "inOut"),
    camKey(L(atau + 30), frameCam([0.1, 1.85, 0], [0.16, 0.45, 1], 3.0, 1, 34, -0.02), "inOut"),
  ],
  shakes: [{ at: L(ngomong) + 4, dur: 16, amp: 0.05, seed: 6 }],
  actors: [{
    cast: "raka",
    pose: [
      { f: 0, v: { handL: [0.1, 0.46, 0.33], handR: [-0.1, 0.46, 0.33], elbowL: [0.6, -0.8, 0], elbowR: [-0.6, -0.8, 0], fistL: 0.5, fistR: 0.5, spine: [0.14, 0, 0], head: [0.18, 0.25, 0], shrug: 0.5 } },
      ...jitterKeys(16, L(kamu) - 20, 30, (i) => ({ head: [0.18, i % 2 ? -0.28 : 0.28, i % 2 ? 0.05 : -0.05] as V3 })),
      { f: L(kamu) - 2, v: { head: [-0.38, 0, 0], spine: [0, 0, 0] }, ease: "outBack" },
      { f: L(ngomong) + 6, v: { handL: [0.07, 1.0, 0.46], handR: [-0.07, 1.0, 0.46], elbowL: [0.9, -0.5, 0.1], elbowR: [-0.9, -0.5, 0.1], fistL: 0, fistR: 0, twistL: -1.2, twistR: 1.2, head: [0.08, 0, 0], spine: [0.1, 0, 0], squash: 0.86, shrug: 1 }, ease: "outBack" },
      { f: L(ngomong) + 30, v: { squash: 0.9 }, ease: "inOut" },
    ] as PoseKey[],
    expr: [
      { f: 0, v: { ...EXPR.guilty, look: [0.6, -0.3] } },
      ...jitterKeys(16, L(kamu) - 20, 30, (i) => ({ look: [i % 2 ? -0.7 : 0.7, -0.3] as [number, number] })),
      { f: L(kamu) - 2, v: { ...EXPR.worried, look: [0, 1], eyeSize: 1.1 }, ease: "outBack" },
      { f: L(salah) + 2, v: { ...EXPR.panic, look: [0, 1] }, ease: "outBackBig" },
      { f: L(ngomong) + 6, v: { ...EXPR.panic, eyeMode: "shut", browIn: 1.4, browUp: 1, sweat: 1, tremble: 1 }, ease: "outBack" },
    ] as ExprKey[],
  }],
  props: [
    {
      kind: "bubble", from: L(kamu) - 6, to: L(ngomong) + 3,
      keys: [
        { f: L(kamu) - 6, v: { w: 0.85, h: 0.48, tail: "left", fill: YELLOW, content: { kind: "squiggle", lines: 3 }, pos: [0.25, 2.3, 0.25], rot: [-0.55, 0, 0.06], scale: 0 } },
        { f: L(kamu) + 4, v: { pos: BALLOON, scale: 1 }, ease: "outBackBig" },
        ...jitterKeys(L(salah) + 4, L(ngomong) + 2, 2, (i) => ({ rot: [-0.55, 0, (i % 2 ? 1 : -1) * 0.05] as V3 })),
      ],
    },
    { kind: "shatterBubble", from: L(ngomong) + 4, to: L(atau + 30), keys: [{ f: L(ngomong) + 4, v: { w: 0.85, h: 0.48, tail: "left", fill: YELLOW, content: { kind: "squiggle", lines: 3 }, pos: BALLOON, rot: [-0.55, 0, 0], scale: 1, t: 0 } }, { f: L(ngomong) + 40, v: { t: 1.4 }, ease: "linear" }] },
  ],
};
const cliff: ShotDef = {
  start: SC,
  set: { kind: "stage", look: "cliff", a: "navy" },
  setKeys: [
    { f: 0, v: { gap: 0, tilt: 0, drop: 0 } },
    { f: L(hubungan), v: { gap: 0 } },
    { f: L(kalian), v: { gap: 0.5 }, ease: "inOut" },
    { f: L(tamat), v: { gap: 0.62 }, ease: "linear" },
    { f: L(tamat) + 35, v: { gap: 3.4, tilt: 0.12, drop: 0.5 }, ease: "in" },
    { f: L(padahal) + 3, v: { gap: 5.0, tilt: 0.2, drop: 1.6 }, ease: "linear" },
  ],
  bg: "#1E3F6E", light: [-0.5, 0.8, 0.5], ink: 1.15,
  camera: [
    camKey(0, frameCam([0, 1.4, 0.15], [0.05, 0.18, 1], 3.2, 0.235, 26)),
    camKey(L(dia), frameCam([0, 1.4, 0.15], [0.1, 0.2, 1], 3.2, 0.235, 26), "inOut"),
    camKey(L(atau) - 5, frameCam([0, 1.45, 0.15], [-0.25, 0.28, 1], 4.3, 1, 34, 0.02)),
    camKey(L(tamat), frameCam([0, 1.4, 0.15], [-0.22, 0.32, 1], 4.0, 1, 34, 0.03), "inOut"),
    camKey(L(padahal), frameCam([0, 0.4, 0], [-0.15, 1.25, 1], 7.5, 1, 38, -0.03), "inOut"),
  ],
  shakes: [{ at: L(hubungan), dur: 40, amp: 0.02, seed: 9 }, { at: L(tamat), dur: 30, amp: 0.09, seed: 11 }],
  actors: [
    {
      cast: "raka", anchor: "cliff-left",
      pose: [
        { f: 0, v: { pos: [-0.85, 0, 1.0], rotY: 1.2, head: [0.05, 0.1, 0], handL: [0.4, 0.05, 0.12], handR: [-0.4, 0.05, 0.12] } },
        { f: L(hubungan), v: { spine: [0, 0, 0] } },
        { f: L(hubungan) + 16, v: { spine: [0.24, 0, 0], head: [-0.15, 0, 0], handL: [0.14, 0.88, 0.62], handR: [-0.14, 0.82, 0.6], elbowL: [0.6, -0.6, 0], elbowR: [-0.6, -0.6, 0], fistL: 0, fistR: 0, footR: [-0.15, 0.08, -0.25] }, ease: "outBack" },
        { f: L(tamat) + 6, v: { spine: [0.38, 0, 0], head: [0.05, 0, 0], squash: 0.92 }, ease: "outBack" },
      ] as PoseKey[],
      expr: [
        { f: 0, v: { ...EXPR.hopeful, look: [0.6, 0] } },
        { f: L(hubungan) - 6, v: { ...EXPR.hopeful, look: [0.6, 0] } },
        { f: L(hubungan), v: { ...EXPR.worried, look: [0.6, 0] }, ease: "outBack" },
        { f: L(tamat) - 4, v: { ...EXPR.worried, look: [0.6, 0] } },
        { f: L(tamat), v: { ...EXPR.devastated, look: [0.5, -0.2] }, ease: "outBackBig" },
      ] as ExprKey[],
    },
    {
      cast: "dinda", anchor: "cliff-right",
      pose: [
        { f: 0, v: { pos: [0.85, 0, -0.7], rotY: -0.85, head: [0.05, -0.1, 0] } },
        { f: L(hubungan) + 2, v: { rotY: -0.85 } },
        { f: L(kalian) - 2, v: { rotY: -3.5, head: [0.02, -0.8, -0.05], spine: [0, -0.2, 0] }, ease: "inOut" },
      ] as PoseKey[],
      expr: [{ f: 0, v: { ...EXPR.calm, look: [-0.6, 0] } }, { f: L(hubungan) + 2, v: { ...EXPR.cold }, ease: "inOut" }] as ExprKey[],
    },
  ],
  props: [{ kind: "heart", keys: [{ f: 0, v: { pos: [0.05, 2.15, 0.1], scale: 0.3, crack: 0, split: 0 } }, { f: L(kalian), v: { crack: 0 } }, { f: L(kalian) + 4, v: { crack: 1, scale: 0.33 }, ease: "outBack" }, { f: L(tamat), v: { split: 0 } }, { f: L(padahal) + 3, v: { split: 1.4 }, ease: "linear" }] }],
};

// ── 5 · reality: the warung ────────────────────────────────────────────────────────────────────
const R = (g: number) => g - padahal;
const warung: ShotDef = {
  start: padahal,
  freezeAt: R(baksoEnd + 1),
  set: { ...SET_PRESETS.warung, items: [...(SET_PRESETS.warung.items ?? []).filter((i) => i.type !== "frame"), { type: "gerobak", pos: [0.6, 0, -1.95], rot: -0.15, scale: 0.68 }] },
  bg: YELLOW, light: [0.5, 0.85, 0.6], ink: 1.15,
  camera: [
    { f: 0, v: { pos: [0, 2.05, 4.9], target: [0, 1.3, 0.1], fov: 32, roll: 0 } },
    { f: R(dia2), v: { pos: [0, 2.04, 4.85], target: [0, 1.3, 0.1], fov: 32, roll: 0 }, ease: "linear" },
    { f: R(baksoEnd + 1), v: { pos: [0, 1.78, 3.45], target: [0, 1.45, 0.1], fov: 30, roll: 0 }, ease: "inOut" },
  ],
  actors: [{
    cast: "dinda", shadow: false,
    pose: [
      { f: 0, v: { pos: [0, 0, -0.45], pelvis: [0, -0.42, 0], footL: [0.17, 0.08, 0.45], footR: [-0.17, 0.08, 0.45], spine: [0.06, 0, 0], head: [0.1, 0, 0.05], handR: [-0.13, 0.66, 0.46], elbowR: [-0.8, -0.7, -0.1], twistR: 0.3, fistR: 0.8, handL: [0.3, 0.38, 0.5], elbowL: [0.8, -0.6, -0.2], fistL: 0.3 } },
      { f: 60, v: { head: [0.12, 0.04, 0.08] }, ease: "inOut" },
      { f: R(dia2), v: { head: [0.1, 0, 0.04] }, ease: "inOut" },
      { f: R(makan), v: { handR: [-0.07, 0.78, 0.42], head: [0.02, 0, 0], spine: [0.1, 0, 0] }, ease: "inOut" },
      { f: R(makan) + 14, v: { handR: [-0.15, 0.52, 0.5], head: [0.04, 0.12, 0.14], spine: [0.02, 0, 0] }, ease: "outBack" },
      { f: R(baksoEnd), v: { head: [0, 0.16, 0.16] }, ease: "inOut" },
    ] as PoseKey[],
    expr: [
      { f: 0, v: { ...EXPR.calm, eyeOpen: 0.6, look: [0, -0.4], mouth: "o", mouthOpen: 0.25, mouthW: 0.55 } },
      { f: 70, v: { ...EXPR.calm, eyeOpen: 0.6, look: [0, -0.4], mouth: "o", mouthOpen: 0.3, mouthW: 0.55 } },
      { f: R(dia2), v: { ...EXPR.yum, look: [0, -0.3] }, ease: "inOut" },
      { f: R(makan) - 3, v: { ...EXPR.yum, look: [0, -0.3] } },
      { f: R(makan) + 2, v: { ...EXPR.bliss }, ease: "outBack" },
    ] as ExprKey[],
    heldR: { kind: "spoon", keys: [{ f: 0, v: { pos: [0, 0.02, 0.03], rot: [0, 0, 0], bakso: true } }, { f: R(makan) + 1, v: { bakso: true } }, { f: R(makan) + 2, v: { bakso: false }, ease: "hold" }] },
    holdRotR: [-0.35, 0, 0.35],
    head: [{ kind: "hearts", from: R(bakso) - 4, keys: [{ f: 0, v: { pos: [0.1, 0.55, 0.1], at: R(bakso) - 4 } }] }],
  }],
  props: [
    { kind: "bowl", keys: [{ f: 0, v: { pos: [0, 0.9, 0.42], balls: 4 } }] },
    { kind: "phone", keys: [{ f: 0, v: { pos: [0.46, 0.95, 0.92], rot: [-1.0, -0.3, 0], scale: 1.05, screen: { kind: "chat", title: "Raka", messages: [{ from: "them", scribble: 6 }, { from: "me", text: "Oke" }], read: true } } }] },
  ],
};
const inset: ShotDef = {
  start: baksoEnd - 12,
  freezeAt: 50,
  set: { ...SET_PRESETS.bedroomNight },
  bg: "#2674B9", light: [-0.6, 0.7, 0.55], ink: 1.1,
  camera: [camKey(0, frameCam([0.04, 1.8, 0.12], [0.3, -0.06, 1], 1.25, 0.215, 24, -0.03))],
  actors: [{
    cast: "raka",
    pose: [{ f: 0, v: { pos: [0, 0, 0.05], ...SIT, ...HOLD, spine: [0.18, 0, 0], head: [0.26, 0.04, 0.06], shrug: 0.8 } }] as PoseKey[],
    expr: [{ f: 0, v: { ...EXPR.panic, look: [0, -0.8], sweat: 1, tremble: 1 } }] as ExprKey[],
    torso: [{ kind: "phone", keys: [{ f: 0, v: { pos: PHONE_POS, rot: PHONE_ROT, screen: { kind: "chat", title: "Dinda", messages: [{ from: "me", scribble: 6 }, { from: "them", text: "Oke" }] } } }] }],
  }],
};

// ── page ───────────────────────────────────────────────────────────────────────────────────────
const P = LAYOUTS.threeUp();
const FREEZE: Quad = [[46, 196], [1034, 166], [1034, 1726], [46, 1762]];

export default story({
  id: "oke",
  title: "OKE",
  clock: W,
  durationInFrames: END,
  vo: { file: "audio/oke.mp3", offset: 0.2 },
  cast: { raka: LOOKS.raka, dinda: LOOKS.dinda },
  shots: { room, rage, interro, cliff, warung, inset },
  elements: ["gerobak"],
  beats: [
    { at: chat, words: "chat kamu cuma dibalas", idea: "his long scribble message floats up; typing dots", device: "3D chat balloons in the room" },
    { at: oke, words: "oke?", idea: "a tiny OKE pops out of the phone", device: "punch zoom + speed lines" },
    { at: dalam, words: "dalam sedetik", idea: "the world stops; his brain does not", device: "freeze into a tilted panel + vertigo dolly zoom" },
    { at: otak, words: "otakmu langsung bikin", idea: "thought puffs grow into a cloud we dive into", device: "zoom-through transition" },
    { at: tiga, words: "tiga skenario", idea: "three living panels slam on the syllables", device: "3-up page slam" },
    { at: dia, words: "dia marah", idea: "she stomps in a red burst world", device: "panel expand + low-angle hero" },
    { at: kamu, words: "kamu salah ngomong", idea: "his own words shatter above him", device: "diagonal split-screen slash" },
    { at: atau, words: "atau hubungan kalian tamat", idea: "the ground splits, the heart breaks", device: "panel shatter reveal + crane up" },
    { at: tamat, words: "tamat", idea: "the end of the world", device: "burst lettering + ink flood" },
    { at: padahal, words: "padahal", idea: "dead calm, daylight, still camera", device: "hard cut contrast + caption box" },
    { at: makan, words: "makan bakso", idea: "she bites, blissful; he still sweats", device: "freeze into a printed page + inset" },
  ],
  panels: [
    panel("room", 0, tiga - 6, enter.cut(0, FULL, 0), move.collapse(dalam, FREEZE, 6, -1.2, 12), move.drift(dalam + 6, otak + 10 - dalam, [0, 0], -0.8), move.expand(otak + 20, tiga - 6 - otak - 20 - 4)),
    panel("rage", tiga - 4, kamu + 8, enter.slam(tiga - 4, P[0]), move.expand(dia - 8, 22)),
    panel("interro", tiga + 8, dia + 12, enter.slam(tiga + 8, P[1], "right"), move.fall(dia - 10, 22, "down")),
    panel("cliff", sken + 4, dia + 12, enter.slam(sken + 4, P[2], "left"), move.fall(dia - 10, 22, "left", -14)),
    panel("cliff", atau - 11, padahal - 1, enter.cut(atau - 11, FULL, 0)),
    panel("interro", kamu - 13, atau + 25, enter.slash(kamu - 13, "right", 6), move.shatter(atau - 5, 30)),
    panel("warung", padahal, END, enter.cut(padahal, FULL, 0), move.collapse(baksoEnd + 1, LAYOUTS.page()[0], 14)),
    panel("inset", baksoEnd + 7, END, enter.slam(baksoEnd + 7, LAYOUTS.inset("br")[1], "right", 16, 10)),
  ],
  fx: [
    fx.speed(oke, oke + 34, { cx: 560, cy: 820, inner: 430, opacity: 0.9 }),
    fx.flash(dalam, 3),
    fx.speed(dalam + 4, otak + 4, { cx: 540, cy: 900, inner: 640, opacity: 0.55 }),
    fx.flash(tiga - 7, 3),
    fx.impact(dia, kamu - 2, "DIA MARAH?", { x: 520, y: 345, size: 150 }),
    fx.speed(marah + 2, marah + 28, { cx: 520, cy: 1100, inner: 620, opacity: 0.6 }),
    fx.impact(kamu + 4, atau - 8, "AKU SALAH?", { x: 540, y: 1400, size: 140, rot: 4, fill: PAPER }),
    fx.burst(tamat, padahal - tamat, { x: 540, y: 860, r: 470, seed: 4 }),
    fx.speed(tamat, tamat + 26, { cx: 540, cy: 860, inner: 520 }),
    fx.flood(tamat + 21, 42, padahal - 1),
    fx.impact(tamat + 1, padahal - 1, "TAMAT!", { x: 540, y: 860, size: 220, rot: -7, font: "title" }),
    fx.caption(padahal + 2, dia2 + 20, "TERNYATA...", { x: 64, y: 236, size: 88 }),
  ],
  sfx: [
    ...[6, 12, 17, 23, 28, 34, 40, 46].filter((f) => f < chat - 4).map((f, i) => sfx(i % 2 ? "tap2" : "tap", f, 0.12)),
    sfx("send", chat + 2, 0.2),
    sfx("blip", oke - 50, 0.16),
    sfx("buzz1", oke - 18, 0.26),
    sfx("ding", oke - 15, 0.24, { until: oke + 2 }),
    sfx("punchDry", oke + 2, 0.42, { duck: 0.4 }),
    sfx("freeze", dalam - 3, 0.42, { duck: 0.8 }),
    sfx("vertigo", dalam + 2, 0.15),
    sfx("bloop", otak + 6, 0.18), sfx("bloop", otak + 13, 0.18), sfx("bloop", otak + 20, 0.2),
    sfx("cloud", tiga - 35, 0.2),
    sfx("boom", marah + 2, 0.5, { duck: 0.6 }),
    sfx("shatter", ngomong + 4, 0.32), sfx("slap", ngomong + 5, 0.3),
    sfx("rumble", hubungan - 2, 0.36), sfx("crack", hubungan + 4, 0.28), sfx("crack", kalian + 2, 0.34),
    sfx("tamat", tamat, 0.55, { duck: 0.85, until: padahal - 1 }),
    sfx("scratch", padahal - 23, 0.4, { until: padahal - 1 }),
    sfx("room", padahal, 0.05, { duck: 0.4 }),
    sfx("blow", padahal + 50, 0.22),
    sfx("nom", makan + 2, 0.28), sfx("sparkle", bakso - 2, 0.15), sfx("chew", bakso + 2, 0.2), sfx("chew", bakso + 26, 0.18),
    sfx("stamp", baksoEnd + 10, 0.5),
  ],
  page: {
    cam: [
      { f: 0, v: { x: 0, y: 0, scale: 1, rot: 0, tiltX: 0, tiltY: 0 } },
      { f: tiga - 12, v: { x: 0, y: 0, scale: 1, rot: 0, tiltX: 0, tiltY: 0 } },
      { f: tiga - 11, ease: "hold", v: { x: 0, y: 30, scale: 1.06, rot: -1.2, tiltX: 5, tiltY: 0 } },
      { f: dia - 12, v: { x: 0, y: 0, scale: 1.0, rot: 0, tiltX: 0, tiltY: 0 }, ease: "inOut" },
    ],
    shakes: [
      { at: oke + 2, dur: 16, amp: 10 }, { at: tiga + 6, dur: 10, amp: 7 }, { at: tiga + 18, dur: 10, amp: 7 }, { at: sken + 14, dur: 10, amp: 7 },
      { at: marah + 2, dur: 18, amp: 13 }, { at: ngomong + 6, dur: 14, amp: 8 }, { at: tamat, dur: 30, amp: 14 },
    ],
  },
});

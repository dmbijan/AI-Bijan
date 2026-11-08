// REFERENCE DEMO (AI BIJAN MOGRAPH v1.6) — read for structure only: stations, camera moves, word-pinned beats, SFX cues.
// Do NOT copy this story, its text or its layout into a buyer video. Written in the author's demo workspace, so two
// imports differ from the starter: `makeCues(words)` ≈ the starter's `at()/endOf()` from src/lib/words.ts, and
// `EpisodeAudio` ≈ Main.tsx audio (musicFile + auto-duck) + <SfxTrack /> fed by src/audio-plan.json sfxCues.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {makeCues} from '../../lib/cues';
import {p, pagesFor, pb, rnd, StyleFonts, useTime} from '../../styles/shared';
import {Caption, CRT, Glitch, glow, HEAD, HexRain, MONO, T, Typed, Win} from '../../styles/terminal/kit';
import {EpisodeAudio, Sfx} from '../../styles/audio';
import wordsFile from './words.json';

// TERMINAL episode — "Kode OTP": the victim's phone vs the sender's terminal. Awareness only: it shows what an
// OTP protects and the one rule, never how to attack anything.

const C = makeCues(wordsFile);
const PAGES = pagesFor(C.words, 4, 22);
export const TERMINAL_DURATION = C.duration + 1.4;
const a = C.at;
const CODE = '482917';

const Lock: React.FC<{x: number; y: number; s?: number; open?: number; color?: string}> = ({x, y, s = 1, open = 0, color = T.g}) => (
  <g transform={`translate(${x} ${y}) scale(${s})`} style={{filter: `drop-shadow(0 0 8px ${color}99)`}}>
    <path d={`M -34 -10 L -34 -48 A 34 34 0 0 1 34 -48 L 34 ${-10 - 46 * open}`} fill="none" stroke={color} strokeWidth={12} transform={`rotate(${-28 * open} -34 -10)`} />
    <rect x={-52} y={-12} width={104} height={84} rx={10} fill={T.bg} stroke={color} strokeWidth={8} />
    <rect x={-6} y={14} width={12} height={30} rx={6} fill={color} />
  </g>
);

const Phone: React.FC<{q: number; children?: React.ReactNode; ting?: number}> = ({q, children, ting = 0}) => {
  if (q <= 0) return null;
  return (
    <div style={{position: 'absolute', left: 150, top: 200, width: 780, height: 1250, transform: `translateY(${(1 - q) * 300}px) scale(${0.9 + 0.1 * q})`, opacity: Math.min(1, q * 2)}}>
      {ting > 0 && [0, 1, 2].map((i) => {
        const k = (ting + i * 0.25) % 1;
        return <div key={i} style={{position: 'absolute', left: 390 - 340 * (0.6 + k), top: 120 - 340 * (0.6 + k) * 0.5, width: 680 * (0.6 + k), height: 340 * (0.6 + k), borderRadius: '50%', border: `3px solid ${T.g}`, opacity: (1 - k) * 0.6}} />;
      })}
      <div style={{position: 'absolute', inset: 0, borderRadius: 64, border: `4px solid ${T.g}`, background: `${T.bg}f5`, boxShadow: `0 0 40px ${T.g}55, inset 0 0 50px ${T.g}18`}}>
        <div style={{position: 'absolute', left: 310, top: 18, width: 160, height: 30, borderRadius: 15, border: `3px solid ${T.dim}`}} />
        <div style={{position: 'absolute', left: 40, right: 40, top: 64, display: 'flex', justifyContent: 'space-between', fontFamily: MONO, fontWeight: 700, fontSize: 24, color: T.dim}}>
          <span>06:31</span><span>▮▮▮ 4G ▮</span>
        </div>
        <div style={{position: 'absolute', left: 34, right: 34, top: 120, bottom: 40}}>{children}</div>
      </div>
    </div>
  );
};

const Bubble: React.FC<{q: number; from?: string; color?: string; children: React.ReactNode; dim?: number}> = ({q, from, color = T.g, children, dim = 0}) =>
  q > 0 ? (
    <div style={{marginBottom: 22, transform: `translateY(${(1 - q) * 30}px)`, opacity: Math.min(1, q * 2) * (1 - dim)}}>
      {from && <div style={{fontFamily: MONO, fontWeight: 700, fontSize: 24, color: T.dim, marginBottom: 8}}>{from}</div>}
      <div style={{border: `3px solid ${color}`, padding: '20px 22px', fontFamily: MONO, fontWeight: 600, fontSize: 38, lineHeight: 1.35, color, textShadow: glow(color), background: `${color}10`}}>{children}</div>
    </div>
  ) : null;

export const KodeOTPEpisode: React.FC = () => {
  const {t, frame} = useTime();
  const win = (from: number, to: number) => t >= from && t < to;
  const tTapi = a('tapi coba'), tDia = a('dia sedang'), tKodeOTP = a('kode otp'), tBegitu = a('begitu'), tDalam = a('dalam hitungan');
  const tPaket = a('paketnya'), tIngat = a('ingat'), tJadi = a('jadi kalau');
  const glitchAt = [tTapi - 0.05, a('di layar') - 0.05, a('terbuka') - 0.05, tDalam - 0.05, tPaket - 0.1, tIngat - 0.1];
  const glitch = glitchAt.some((g) => t >= g && t < g + 0.2);
  const shakeX = glitch ? (rnd(frame) - 0.5) * 34 : 0;
  const attacker = t >= a('di layar') - 0.05 && t < tPaket - 0.1;
  const head = attacker ? T.alert : T.g;
  const granted = p(t, a('terbuka') - 0.05, a('terbuka') + 0.1) * (1 - p(t, tDalam - 0.2, tDalam));
  const typedDigits = Math.max(0, Math.min(6, Math.floor((t - (a('kamu', 3) - 0.1)) / 0.22)));

  return (
    <AbsoluteFill style={{background: T.bg, overflow: 'hidden'}}>
      <StyleFonts />
      <EpisodeAudio vo="audio/vo/terminal.mp3" words={C.words} music="audio/music/terminal.wav" bed={0.5} duck={0.16} sfx={TERMINAL_SFX} />
      <HexRain t={t} opacity={attacker ? 0.3 : 0.16} />
      <div style={{position: 'absolute', inset: 0, transform: `translateX(${shakeX}px)`}}>
        <div style={{position: 'absolute', left: 72, right: 72, top: 96, display: 'flex', justifyContent: 'space-between', fontFamily: MONO, fontWeight: 700, fontSize: 24, color: head, textShadow: glow(head)}}>
          <span>{attacker ? 'pengirim@???:~$' : 'kamu@hp:~$'}</span><span style={{color: T.amber, textShadow: glow(T.amber)}}>● EDUKASI · KEAMANAN</span>
        </div>

        {/* S1 — the message on your phone */}
        {win(0, a('kelihatannya') - 0.1) && (
          <Phone q={pb(t, -0.4, 0.2)} ting={t < 1.6 ? (t * 1.4) % 1 : 0}>
            <div style={{fontFamily: HEAD, fontSize: 64, color: T.g, textShadow: glow(), marginBottom: 20}}>▸ 1 PESAN BARU</div>
            <Bubble q={pb(t, a('paket') - 0.15, a('paket') + 0.2)} from="+62 8xx-xxxx-xx31 · tidak dikenal">
              <Typed text="Paket kamu tertahan." t={t} start={a('paket') - 0.1} cps={30} cursor={false} />
            </Bubble>
            <Bubble q={pb(t, a('sebutkan') - 0.15, a('sebutkan') + 0.2)}>
              <Typed text="Sebutkan kode yang barusan dikirim ke HP kamu." t={t} start={a('sebutkan') - 0.1} cps={26} cursor={false} />
            </Bubble>
            <Bubble q={pb(t, a('kode') - 0.1, a('kode') + 0.3)} from="SMS · INFO-OTP" color={T.amber}>
              Kode OTP: <span style={{fontFamily: HEAD, fontSize: 70}}>{CODE}</span><br />JANGAN berikan ke siapa pun.
            </Bubble>
          </Phone>
        )}
        {/* S2 — just six digits */}
        {win(a('kelihatannya') - 0.1, tTapi - 0.05) && (
          <div style={{position: 'absolute', left: 0, right: 0, top: 640, textAlign: 'center'}}>
            <div style={{display: 'flex', justifyContent: 'center', gap: 18}}>
              {CODE.split('').map((d, i) => (
                <div key={i} style={{width: 132, height: 190, border: `4px solid ${T.amber}`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: HEAD, fontSize: 170, color: T.amber,
                  textShadow: glow(T.amber), transform: `translateY(${(1 - pb(t, a('kelihatannya') + i * 0.06, a('kelihatannya') + 0.3 + i * 0.06)) * 120}px)`, opacity: p(t, a('kelihatannya') + i * 0.06, a('kelihatannya') + 0.2 + i * 0.06)}}>{d}</div>
              ))}
            </div>
            <div style={{marginTop: 60, fontFamily: HEAD, fontSize: 110, color: T.g, textShadow: glow(), opacity: p(t, a('cuma') - 0.1, a('cuma') + 0.15)}}>
              CUMA <span style={{color: T.amber, textShadow: glow(T.amber), opacity: p(t, a('enam') - 0.1, a('enam') + 0.1)}}>6</span> ANGKA?
            </div>
          </div>
        )}
        {/* S3 — the other screen */}
        {win(tTapi - 0.05, tDia - 0.1) && (
          <div style={{position: 'absolute', left: 72, right: 72, top: 520, fontFamily: HEAD, color: T.alert, textShadow: glow(T.alert), textAlign: 'center'}}>
            <div style={{fontSize: 90, lineHeight: 1}}><Typed text="SEMENTARA ITU..." t={t} start={tTapi} cps={20} color={T.alert} /></div>
            <div style={{fontSize: 150, lineHeight: 1, marginTop: 30, opacity: p(t, a('di layar') - 0.1, a('di layar') + 0.1)}}>DI LAYAR</div>
            <div style={{fontSize: 150, lineHeight: 1, opacity: p(t, a('pengirim') - 0.1, a('pengirim') + 0.1)}}>SI PENGIRIM</div>
          </div>
        )}
        {/* S4 + S6 — the login form */}
        {(win(tDia - 0.1, tKodeOTP - 0.1) || win(tBegitu - 0.15, tDalam - 0.1)) && (
          <Win x={72} y={240} w={936} h={980} title="login · akun m-banking" q={p(t, tDia - 0.1, tDia + 0.2) || 1} color={T.alert}>
            <div style={{color: T.dim}}>AKUN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <Typed text="kamu" t={t} start={a('akunmu') - 0.1} cps={20} cursor={false} color={T.g} /></div>
            <div style={{color: T.dim}}>PERANGKAT: <Typed text="tidak dikenal" t={t} start={a('perangkat') - 0.1} cps={26} cursor={false} color={T.amber} /></div>
            <div style={{color: T.dim, marginTop: 18}}>PASSWORD : <span style={{color: T.g, textShadow: glow()}}>{'•'.repeat(Math.floor(10 * p(t, a('password-nya') - 0.1, a('dapat'))))}</span>{t > a('dapat') && <span style={{color: T.g, textShadow: glow()}}>  ✓</span>}</div>
            <div style={{marginTop: 50, fontFamily: HEAD, fontSize: 64, color: T.amber, textShadow: glow(T.amber), opacity: p(t, a('yang kurang') - 0.1, a('yang kurang') + 0.2)}}>KODE OTP</div>
            <div style={{display: 'flex', gap: 14, marginTop: 10, opacity: p(t, a('yang kurang') - 0.1, a('yang kurang') + 0.2)}}>
              {CODE.split('').map((d, i) => (
                <div key={i} style={{width: 110, height: 150, border: `3px solid ${i < typedDigits && t > tBegitu ? T.g : T.amber}`, display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontFamily: HEAD, fontSize: 120, color: T.g, textShadow: glow()}}>{t > tBegitu && i < typedDigits ? d : (Math.floor(t * 2.4) % 2 === 0 && i === 0 ? '_' : '')}</div>
              ))}
            </div>
            <div style={{marginTop: 30, color: T.amber, textShadow: glow(T.amber), opacity: t < tBegitu ? p(t, a('kode', 1) - 0.1, a('kode', 1) + 0.2) : 0}}>[ menunggu kode dari korban... ]</div>
            {granted > 0 && (
              <div style={{marginTop: 30, padding: '20px 0', textAlign: 'center', fontFamily: HEAD, fontSize: 130, color: T.bg, background: T.g, boxShadow: `0 0 50px ${T.g}`, opacity: granted}}>ACCESS GRANTED</div>
            )}
          </Win>
        )}
        {/* S5 — three locks, the last one is the OTP */}
        {win(tKodeOTP - 0.1, tBegitu - 0.15) && (
          <>
            <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
              {[{x: 230, l: 'AKUN', ok: true}, {x: 540, l: 'PASSWORD', ok: true}, {x: 850, l: 'OTP', ok: false}].map((k, i) => {
                const q = pb(t, tKodeOTP + i * 0.15, tKodeOTP + 0.35 + i * 0.15);
                const hot = i === 2 && t > a('kunci') - 0.1;
                return (
                  <g key={k.l} opacity={Math.min(1, q * 2)} transform={`translate(0 ${(1 - q) * 80})`}>
                    <Lock x={k.x} y={560} s={hot ? 1.6 : 1.2} open={k.ok ? 1 : 0} color={k.ok ? T.dim : T.amber} />
                    <text x={k.x} y={720} textAnchor="middle" fill={k.ok ? T.dim : T.amber} style={{fontFamily: MONO, fontWeight: 700, fontSize: 30}}>{k.l} {k.ok ? '✓' : ''}</text>
                  </g>
                );
              })}
            </svg>
            <div style={{position: 'absolute', left: 72, right: 72, top: 820, textAlign: 'center', fontFamily: HEAD, fontSize: 112, color: T.amber, textShadow: glow(T.amber), opacity: p(t, a('kunci') - 0.1, a('kunci') + 0.2)}}>KUNCI TERAKHIR</div>
            <Win x={140} y={1010} w={800} h={330} title="otp.info" q={p(t, a('sekali') - 0.2, a('sekali') + 0.2)}>
              <div style={{color: T.dim, fontSize: 36}}>sekali pakai&nbsp;: <span style={{color: T.amber, textShadow: glow(T.amber)}}>YA</span></div>
              <div style={{color: T.dim, fontSize: 36, opacity: p(t, a('membuktikan') - 0.1, a('membuktikan') + 0.2)}}>membuktikan : <Typed text="INI BENAR-BENAR KAMU" t={t} start={a('membuktikan')} cps={14} cursor={false} /></div>
            </Win>
          </>
        )}
        {/* S7 — minutes later */}
        {win(tDalam - 0.1, tPaket - 0.1) && (
          <Win x={72} y={240} w={936} h={1100} title="session · akun kamu" q={1} color={T.alert}>
            <div><Typed text="> transfer --saldo" t={t} start={tDalam} cps={30} color={T.alert} /></div>
            <div style={{fontFamily: HEAD, fontSize: 120, lineHeight: 1.05, color: T.g, textShadow: glow(), opacity: p(t, a('saldo') - 0.2, a('saldo'))}}>
              Rp {Math.round(8450000 * (1 - p(t, a('saldo'), a('pindah') + 0.4))).toLocaleString('id-ID')}
            </div>
            <div style={{marginTop: 20, opacity: p(t, a('nomor') - 0.1, a('nomor') + 0.1)}}><Typed text="> ganti nomor HP ....... OK" t={t} start={a('nomor') - 0.1} cps={34} color={T.alert} /></div>
            <div style={{opacity: p(t, a('dan akun') - 0.1, a('dan akun') + 0.1)}}><Typed text="> keluarkan perangkat lain ... OK" t={t} start={a('dan akun') - 0.1} cps={34} color={T.alert} /></div>
            <div style={{marginTop: 40, border: `3px solid ${T.alert}`, padding: 20, fontFamily: HEAD, fontSize: 80, lineHeight: 1, color: T.alert, textShadow: glow(T.alert), opacity: p(t, a('bukan') - 0.1, a('bukan') + 0.1)}}>
              PEMILIK AKUN: BUKAN KAMU
            </div>
          </Win>
        )}
        {/* S8 — the parcel never existed */}
        {win(tPaket - 0.1, tIngat - 0.1) && (
          <Phone q={1}>
            <Bubble q={1} from="+62 8xx-xxxx-xx31 · tidak dikenal" dim={0.5 * p(t, a('nggak') - 0.1, a('ada'))}>Paket kamu tertahan.</Bubble>
            <div style={{marginTop: 80, textAlign: 'center', fontFamily: HEAD, color: T.alert, textShadow: glow(T.alert), transform: `rotate(-6deg) scale(${pb(t, a('nggak') - 0.1, a('nggak') + 0.3)})`}}>
              <div style={{fontSize: 300, lineHeight: 0.9}}>404</div>
              <div style={{fontSize: 90}}>PAKET TIDAK ADA</div>
            </div>
          </Phone>
        )}
        {/* S9 — the rule */}
        {win(tIngat - 0.1, tJadi - 0.1) && (
          <Win x={72} y={230} w={936} h={1180} title="ATURAN #1" q={p(t, tIngat - 0.1, tIngat + 0.25)} color={T.amber}>
            <div style={{fontFamily: HEAD, fontSize: 130, lineHeight: 1, color: T.amber, textShadow: glow(T.amber)}}>INGAT INI.</div>
            {[{l: 'BANK', at: a('bank')}, {l: 'KURIR', at: a('kurir')}, {l: 'APLIKASI RESMI', at: a('aplikasi')}].map((r) => (
              <div key={r.l} style={{fontFamily: HEAD, fontSize: 110, lineHeight: 1.15, color: T.g, textShadow: glow(), opacity: p(t, r.at - 0.1, r.at + 0.1)}}>✗ {r.l}</div>
            ))}
            <div style={{marginTop: 16, fontSize: 40, lineHeight: 1.3, color: T.g, textShadow: glow(), opacity: p(t, a('nggak akan') - 0.1, a('nggak akan') + 0.2)}}>nggak akan pernah minta kode OTP kamu.</div>
            <div style={{marginTop: 50, background: T.alert, color: T.bg, padding: '24px 24px', fontFamily: HEAD, fontSize: 104, lineHeight: 1, boxShadow: `0 0 40px ${T.alert}`, opacity: p(t, a('siapa') - 0.1, a('siapa') + 0.2)}}>
              YANG MINTA = <span style={{opacity: p(t, a('dialah') - 0.1, a('dialah') + 0.15)}}>PENCURI</span>
            </div>
          </Win>
        )}
        {/* S10 — the code is yours */}
        {t >= tJadi - 0.1 && (
          <Phone q={pb(t, tJadi - 0.1, tJadi + 0.3)} ting={t > a('ting', 1) - 0.1 && t < a('ting', 1) + 1.4 ? ((t - a('ting', 1)) * 1.4) % 1 : 0}>
            <Bubble q={pb(t, a('ting', 1) - 0.1, a('ting', 1) + 0.25)} from="SMS · INFO-OTP" color={T.amber}>
              Kode OTP: <span style={{fontFamily: HEAD, fontSize: 70}}>••••••</span><br />JANGAN berikan ke siapa pun.
            </Bubble>
            <svg width={712} height={460} style={{display: 'block', margin: '30px auto 0'}}>
              <Lock x={356} y={250} s={2.6} open={0} color={T.g} />
            </svg>
            <div style={{textAlign: 'center', fontFamily: HEAD, fontSize: 96, lineHeight: 1, color: T.g, textShadow: glow(), opacity: p(t, a('kode', 5) - 0.1, a('kode', 5) + 0.2)}}>KODE INI<br />CUMA BUAT KAMU</div>
          </Phone>
        )}
      </div>
      <Glitch frame={frame} active={glitch} />
      <Caption t={t} pages={PAGES} />
      <CRT frame={frame} />
    </AbsoluteFill>
  );
};

const s = (t: number, src: string, vol = 0.55): Sfx => ({t, src: `audio/sfx/terminal/${src}.wav`, vol});
export const TERMINAL_SFX: Sfx[] = [
  s(0.0, 'ting', 0.7), s(a('paket') - 0.1, 'msg', 0.45), s(a('sebutkan') - 0.1, 'msg', 0.45), s(a('kode') - 0.1, 'ting', 0.55),
  s(a('kelihatannya') - 0.05, 'keys', 0.4), s(a('enam'), 'blip', 0.4),
  s(a('tapi coba') - 0.05, 'glitch', 0.6), s(a('di layar') - 0.05, 'glitch', 0.5), s(a('di layar'), 'drone', 0.45),
  s(a('akunmu') - 0.1, 'keys', 0.4), s(a('perangkat') - 0.1, 'keys', 0.35), s(a('password-nya') - 0.1, 'keys', 0.45), s(a('dapat'), 'ok', 0.45), s(a('yang kurang'), 'warn', 0.45),
  s(a('kode otp') - 0.1, 'blip', 0.4), s(a('kunci') - 0.1, 'lock', 0.55), s(a('sekali') - 0.2, 'blip', 0.4), s(a('membuktikan'), 'keys', 0.35),
  ...[0, 1, 2, 3, 4, 5].map((i) => s(a('kamu', 3) - 0.1 + i * 0.22, 'key', 0.45)), s(a('terbuka') - 0.05, 'granted', 0.75),
  s(a('dalam hitungan') - 0.05, 'glitch', 0.5), s(a('saldo'), 'drain', 0.6), s(a('nomor') - 0.1, 'keys', 0.4), s(a('dan akun') - 0.1, 'keys', 0.4), s(a('bukan') - 0.1, 'alarm', 0.55),
  s(a('paketnya') - 0.1, 'glitch', 0.45), s(a('nggak') - 0.1, 'error', 0.55),
  s(a('ingat') - 0.1, 'warn', 0.5), s(a('bank') - 0.05, 'x', 0.45), s(a('kurir') - 0.05, 'x', 0.45), s(a('aplikasi') - 0.05, 'x', 0.45), s(a('dialah') - 0.05, 'alarm', 0.6),
  s(a('jadi kalau') - 0.1, 'msg', 0.35), s(a('ting', 1) - 0.05, 'ting', 0.6), s(a('kode', 5) - 0.1, 'lock', 0.5),
];

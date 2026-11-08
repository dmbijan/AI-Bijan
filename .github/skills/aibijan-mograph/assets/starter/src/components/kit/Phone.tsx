import React from 'react';
import {C, TEXT, FONT} from '../../theme';
import {easeBack, formatRupiah, lerp, prog} from './util';

// Generic phone + app UI. Brand-neutral on purpose: never draw a real bank, wallet or chat app.

export const PhoneScale = React.createContext(1);

const StatusBar: React.FC<{dark: boolean}> = ({dark}) => {
  const s = React.useContext(PhoneScale);
  const ink = dark ? '#F5F7FB' : '#111318';
  return (
    <div style={{position: 'absolute', top: 0, left: 0, right: 0, height: 52 * s, display: 'flex', alignItems: 'center',
      justifyContent: 'space-between', padding: `0 ${34 * s}px`, fontFamily: TEXT, fontSize: 21 * s, fontWeight: 600, color: ink}}>
      <span>09:41</span>
      <span style={{display: 'flex', alignItems: 'flex-end', gap: 3 * s}}>
        {[0.4, 0.6, 0.8, 1].map((h, i) => <span key={i} style={{width: 5 * s, height: 16 * s * h, borderRadius: 2, background: ink}} />)}
        <span style={{marginLeft: 8 * s, width: 34 * s, height: 16 * s, borderRadius: 5 * s, border: `${2 * s}px solid ${ink}`, padding: 2 * s, boxSizing: 'border-box'}}>
          <span style={{display: 'block', width: '78%', height: '100%', borderRadius: 2 * s, background: ink}} />
        </span>
      </span>
    </div>
  );
};

/** A modern smartphone. `x` is the horizontal centre, `y` the top edge. Children render inside the screen. */
export const PhoneFrame: React.FC<{
  t: number; x: number; y: number; width?: number; enterAt?: number; tilt?: number; dark?: boolean; screen?: string;
  children?: React.ReactNode;
}> = ({t, x, y, width = 520, enterAt = 0, tilt = 0, dark = false, screen, children}) => {
  const h = width * 2.06, bez = width * 0.034, r = width * 0.15, s = width / 520;
  const p = prog(t, enterAt, 0.7);
  return (
    <div style={{position: 'absolute', left: x - width / 2, top: y, width, height: h, opacity: p, transformOrigin: '50% 60%',
      transform: `translateY(${(1 - p) * 140}px) rotate(${tilt * (0.4 + 0.6 * p)}deg) scale(${0.92 + 0.08 * p})`}}>
      <div style={{position: 'absolute', inset: 0, borderRadius: r, background: '#1b1d24',
        boxShadow: '0 40px 80px #0d123355, inset 0 0 0 3px #3a3e48'}} />
      <div style={{position: 'absolute', inset: bez, borderRadius: r - bez, background: screen ?? (dark ? '#0F1220' : '#F4F6FB'), overflow: 'hidden'}}>
        <PhoneScale.Provider value={s}>
          <StatusBar dark={dark} />
          <div style={{position: 'absolute', top: 62 * s, left: 0, right: 0, bottom: 0}}>{children}</div>
        </PhoneScale.Provider>
      </div>
      <div style={{position: 'absolute', top: bez + 14 * s, left: '50%', width: 150 * s, height: 40 * s, marginLeft: -75 * s,
        borderRadius: 999, background: '#0b0c10'}} />
    </div>
  );
};

const Icon: React.FC<{kind: 'transfer' | 'pay' | 'topup' | 'scan'; size: number; color: string}> = ({kind, size, color}) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth={2.2} strokeLinecap="round" strokeLinejoin="round">
    {kind === 'transfer' && <><path d="M7 17 17 7" /><path d="M9 7h8v8" /></>}
    {kind === 'pay' && <><rect x="5" y="3" width="14" height="18" rx="2" /><path d="M9 8h6M9 12h6M9 16h3" /></>}
    {kind === 'topup' && <><path d="M12 5v14M5 12h14" /></>}
    {kind === 'scan' && <><path d="M4 9V5h4M20 9V5h-4M4 15v4h4M20 15v4h-4" /><path d="M8 12h8" /></>}
  </svg>
);

export type Transaction = {label: string; amount: number; at?: number; note?: string};

/**
 * Generic banking app screen (use inside <PhoneFrame>). The balance counts from `balanceFrom` to `balanceTo`
 * starting at `changeAt`; it flashes red when it drops and green when it rises.
 */
export const BankAppScreen: React.FC<{
  t: number; balanceFrom: number; balanceTo?: number; changeAt?: number; changeDur?: number; appName?: string;
  accent?: string; note?: string; noteAt?: number; transactions?: Transaction[];
}> = ({t, balanceFrom, balanceTo, changeAt = 0, changeDur = 1.2, appName = 'Bank Kamu', accent = C.blue, note, noteAt, transactions = []}) => {
  const s = React.useContext(PhoneScale);
  const to = balanceTo ?? balanceFrom;
  const p = prog(t, changeAt, changeDur);
  const value = lerp(balanceFrom, to, p);
  const flash = to === balanceFrom ? 0 : prog(t, changeAt, 0.15) * (1 - prog(t, changeAt + changeDur, 0.8));
  const flashColor = to < balanceFrom ? '#FF8A8A' : '#9BF0B5';
  const notePop = note ? prog(t, noteAt ?? changeAt + changeDur, 0.45, easeBack) : 0;
  return (
    <div style={{position: 'absolute', inset: 0, padding: `${8 * s}px ${26 * s}px`, fontFamily: TEXT, color: '#121521'}}>
      <div style={{display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 20 * s}}>
        <div>
          <div style={{fontSize: 19 * s, color: '#6B7186'}}>Halo, selamat pagi</div>
          <div style={{fontFamily: FONT, fontSize: 30 * s, fontWeight: 800}}>{appName}</div>
        </div>
        <div style={{width: 54 * s, height: 54 * s, borderRadius: 999, background: `linear-gradient(135deg, ${C.coral}, ${C.yellow})`}} />
      </div>
      <div style={{borderRadius: 28 * s, padding: `${26 * s}px ${26 * s}px ${30 * s}px`, color: 'white',
        background: `linear-gradient(140deg, ${accent}, ${C.navy})`, boxShadow: `0 ${18 * s}px ${36 * s}px ${accent}55`}}>
        <div style={{fontSize: 20 * s, opacity: 0.85}}>Saldo rekening</div>
        <div style={{fontFamily: FONT, fontWeight: 800, fontSize: 52 * s, marginTop: 6 * s, fontVariantNumeric: 'tabular-nums',
          color: flash > 0.01 ? flashColor : 'white', textShadow: flash > 0.01 ? `0 0 ${24 * s * flash}px ${flashColor}` : 'none'}}>
          {formatRupiah(value)}
        </div>
        <div style={{fontSize: 18 * s, opacity: 0.75, marginTop: 8 * s, letterSpacing: 2 * s}}>•••• 2026</div>
      </div>
      {note && (
        <div style={{marginTop: 16 * s, display: 'flex', justifyContent: 'center', opacity: notePop, transform: `scale(${0.8 + 0.2 * notePop})`}}>
          <div style={{background: C.yellow, color: C.navy, fontFamily: FONT, fontWeight: 800, fontSize: 22 * s, padding: `${8 * s}px ${18 * s}px`, borderRadius: 999}}>{note}</div>
        </div>
      )}
      <div style={{display: 'flex', justifyContent: 'space-between', marginTop: 26 * s}}>
        {([['transfer', 'Transfer'], ['pay', 'Bayar'], ['topup', 'Isi saldo'], ['scan', 'Scan']] as const).map(([k, label]) => (
          <div key={k} style={{display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 * s, width: 96 * s}}>
            <div style={{width: 66 * s, height: 66 * s, borderRadius: 22 * s, background: 'white', display: 'flex', alignItems: 'center',
              justifyContent: 'center', boxShadow: '0 6px 16px #0d123314'}}><Icon kind={k} size={30 * s} color={accent} /></div>
            <div style={{fontSize: 17 * s, color: '#4A5065'}}>{label}</div>
          </div>
        ))}
      </div>
      <div style={{fontFamily: FONT, fontWeight: 800, fontSize: 24 * s, marginTop: 30 * s, marginBottom: 10 * s}}>Riwayat transaksi</div>
      {transactions.map((tx, i) => {
        const q = prog(t, tx.at ?? 0, 0.45);
        const neg = tx.amount < 0;
        return (
          <div key={i} style={{display: 'flex', alignItems: 'center', gap: 16 * s, padding: `${14 * s}px 0`, borderBottom: '1px solid #E3E6EF',
            opacity: q, transform: `translateX(${(1 - q) * 60 * s}px)`}}>
            <div style={{width: 46 * s, height: 46 * s, borderRadius: 14 * s, background: neg ? '#FFE3E3' : '#DDF7E6'}} />
            <div style={{flex: 1}}>
              <div style={{fontSize: 21 * s, fontWeight: 600}}>{tx.label}</div>
              {tx.note && <div style={{fontSize: 16 * s, color: '#7A8095'}}>{tx.note}</div>}
            </div>
            <div style={{fontFamily: FONT, fontWeight: 800, fontSize: 21 * s, color: neg ? '#D33A3A' : '#1E9B57'}}>
              {(neg ? '−' : '+') + formatRupiah(Math.abs(tx.amount))}
            </div>
          </div>
        );
      })}
    </div>
  );
};

/** Phone-style notification card that drops in at `at` (and leaves at `until`). `x` = centre, `y` = top. */
export const NotificationCard: React.FC<{
  t: number; at: number; until?: number; x?: number; y: number; width?: number; app?: string; title: string; body?: string;
  time?: string; iconColor?: string;
}> = ({t, at, until, x = 540, y, width = 900, app = 'Pesan', title, body, time = 'sekarang', iconColor = C.blue}) => {
  const p = prog(t, at, 0.5, easeBack);
  const out = until !== undefined ? prog(t, until, 0.35) : 0;
  if (t < at) return null;
  return (
    <div style={{position: 'absolute', left: x - width / 2, top: y, width, opacity: Math.min(1, p * 1.4) * (1 - out),
      transform: `translateY(${(1 - p) * -70 - out * 40}px) scale(${0.96 + 0.04 * p})`, borderRadius: 40, padding: '26px 30px',
      background: '#FFFFFFF2', boxShadow: '0 24px 60px #0d123333', fontFamily: TEXT, color: '#121521'}}>
      <div style={{display: 'flex', alignItems: 'center', gap: 16, marginBottom: 12}}>
        <div style={{width: 46, height: 46, borderRadius: 13, background: iconColor, color: 'white', display: 'flex', alignItems: 'center',
          justifyContent: 'center', fontFamily: FONT, fontWeight: 800, fontSize: 26}}>{app.slice(0, 1).toUpperCase()}</div>
        <div style={{fontSize: 26, color: '#5F6578', letterSpacing: 1, textTransform: 'uppercase', flex: 1}}>{app}</div>
        <div style={{fontSize: 24, color: '#8A90A3'}}>{time}</div>
      </div>
      <div style={{fontFamily: FONT, fontWeight: 800, fontSize: 36, lineHeight: 1.2}}>{title}</div>
      {body && <div style={{fontSize: 30, lineHeight: 1.3, color: '#40465A', marginTop: 6}}>{body}</div>}
    </div>
  );
};

/** Chat bubble with a typing indicator first. Left bubbles anchor at `x` (left edge), right bubbles at `x` (right edge). */
export const ChatBubble: React.FC<{
  t: number; at: number; text: string; side?: 'left' | 'right'; x: number; y: number; maxWidth?: number; typing?: number;
  color?: string; size?: number;
}> = ({t, at, text, side = 'left', x, y, maxWidth = 700, typing = 0.6, color, size = 40}) => {
  if (t < at) return null;
  const right = side === 'right';
  const bg = color ?? (right ? C.blue : '#FFFFFF');
  const fg = right ? 'white' : '#121521';
  const typingDone = t >= at + typing;
  const p = prog(t, typingDone ? at + typing : at, 0.35, easeBack);
  const pos: React.CSSProperties = right ? {right: 1080 - x} : {left: x};
  return (
    <div style={{position: 'absolute', top: y, ...pos, maxWidth, transform: `scale(${0.85 + 0.15 * p})`, opacity: Math.min(1, p * 1.5),
      transformOrigin: right ? '100% 100%' : '0% 100%', background: bg, color: fg, borderRadius: 34,
      borderBottomRightRadius: right ? 8 : 34, borderBottomLeftRadius: right ? 34 : 8, padding: typingDone ? '22px 30px' : '26px 30px',
      fontFamily: TEXT, fontSize: size, lineHeight: 1.3, boxShadow: '0 14px 30px #0d123326'}}>
      {typingDone ? text : (
        <div style={{display: 'flex', gap: 10}}>
          {[0, 1, 2].map((i) => (
            <span key={i} style={{width: 14, height: 14, borderRadius: 99, background: right ? '#ffffffcc' : '#9AA1B5',
              transform: `translateY(${Math.sin((t - at) * 9 - i * 0.9) * 5}px)`}} />
          ))}
        </div>
      )}
    </div>
  );
};

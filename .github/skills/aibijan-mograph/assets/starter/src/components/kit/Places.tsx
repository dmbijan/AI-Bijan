import React from 'react';
import {C, FONT} from '../../theme';
import {easeBack, prog, seeded} from './util';

/** Indonesian street stall (warung) with striped awning, stocked shelves and a counter. `x` = centre, `y` = top. */
export const Warung: React.FC<{t: number; x: number; y: number; width?: number; enterAt?: number; colors?: [string, string]; sign?: string}> =
  ({t, x, y, width = 700, enterAt = 0, colors = [C.coral, C.paper], sign = 'WARUNG'}) => {
    const s = width / 700;
    const p = prog(t, enterAt, 0.6, easeBack);
    const awn = prog(t, enterAt + 0.15, 0.55, easeBack);
    const stock = ['#E5484D', '#FFC83D', '#398CE6', '#9AD6AC', '#F28C38', '#7B61C9', '#FF775A', '#2E7D52'];
    return (
      <div style={{position: 'absolute', left: x - width / 2, top: y, width, height: 640 * s, opacity: Math.min(1, p * 1.3),
        transform: `scale(${0.88 + 0.12 * p})`, transformOrigin: '50% 100%'}}>
        <svg width={width} height={640 * s} viewBox="0 0 700 640" style={{overflow: 'visible'}}>
          <ellipse cx={350} cy={628} rx={330} ry={14} fill="#0d123322" />
          <rect x={56} y={132} width={588} height={300} fill="#F3E3C4" />
          {[206, 280, 354].map((sy, row) => (
            <g key={sy}>
              <rect x={86} y={sy} width={528} height={10} rx={3} fill="#8B5A3C" />
              {Array.from({length: 11}, (_, i) => {
                const r = seeded(row * 31 + i * 7.3);
                const h = 30 + r * 26, w = 26 + seeded(i + row * 9) * 14;
                const cx = 104 + i * 47;
                return r > 0.5
                  ? <rect key={i} x={cx - w / 2} y={sy - h} width={w} height={h} rx={6} fill={stock[(i + row * 3) % stock.length]} />
                  : <g key={i}><rect x={cx - 14} y={sy - h} width={28} height={h} rx={10} fill={stock[(i * 2 + row) % stock.length]} />
                      <rect x={cx - 10} y={sy - h - 8} width={20} height={10} rx={3} fill="#5E6779" /></g>;
              })}
            </g>
          ))}
          <line x1={70} y1={150} x2={630} y2={150} stroke="#5E6779" strokeWidth={2} />
          {Array.from({length: 12}, (_, i) => (
            <rect key={i} x={84 + i * 46} y={152} width={26} height={40} rx={4} fill={stock[(i * 5) % stock.length]}
              transform={`rotate(${(seeded(i * 3.1) - 0.5) * 10} ${97 + i * 46} 152)`} />
          ))}
          <rect x={30} y={404} width={640} height={28} rx={6} fill="#7A4A2E" />
          <rect x={44} y={432} width={612} height={190} fill="#A86B45" />
          {Array.from({length: 7}, (_, i) => <line key={i} x1={44 + i * 87} y1={432} x2={44 + i * 87} y2={622} stroke="#8E5737" strokeWidth={4} />)}
          <rect x={292} y={352} width={70} height={56} rx={14} fill="#ffffffb0" stroke="#ffffff" strokeWidth={3} />
          {[0, 1, 2, 3, 4].map((i) => <circle key={i} cx={306 + i * 11} cy={388 - (i % 2) * 10} r={7} fill={stock[i]} />)}
          <rect x={34} y={96} width={16} height={330} fill="#6B4630" />
          <rect x={650} y={96} width={16} height={330} fill="#6B4630" />
          <g transform={`translate(0 ${(1 - awn) * -50})`} opacity={Math.min(1, awn * 1.5)}>
            {Array.from({length: 11}, (_, i) => (
              <g key={i}>
                <rect x={20 + i * 60} y={58} width={60} height={64} fill={colors[i % 2]} />
                <path d={`M${20 + i * 60} 122 a30 30 0 0 0 60 0 Z`} fill={colors[i % 2]} />
              </g>
            ))}
            <rect x={14} y={50} width={672} height={14} rx={6} fill="#5E3B26" />
            <rect x={215} y={-6} width={270} height={64} rx={14} fill={C.navy} />
            <text x={350} y={39} textAnchor="middle" fontFamily={FONT} fontWeight={800} fontSize={36} letterSpacing={4} fill={C.cream}>{sign}</text>
          </g>
        </svg>
      </div>
    );
  };

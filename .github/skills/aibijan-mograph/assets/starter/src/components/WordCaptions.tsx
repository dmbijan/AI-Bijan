import React from 'react';
import {words as narrationWords, Word} from '../lib/words';
import {C, FONT, ramp} from '../theme';

// Word-synced captions from src/words.json (npm run align). Short pages that never cross a sentence end,
// with the spoken word highlighted. Deterministic: everything derives from the time `t` in seconds.

export type CaptionPage = {start: number; end: number; words: Word[]};

export const buildPages = (ws: Word[], maxWords = 4, maxChars = 26, gap = 0.45): CaptionPage[] => {
  const pages: CaptionPage[] = [];
  let cur: Word[] = [];
  const flush = () => {
    if (cur.length) pages.push({start: cur[0].start, end: cur[cur.length - 1].end, words: cur});
    cur = [];
  };
  for (const w of ws) {
    const prev = cur[cur.length - 1];
    const chars = cur.reduce((n, x) => n + x.word.length + 1, 0) + w.word.length;
    if (cur.length && (cur.length >= maxWords || chars > maxChars || (prev && w.start - prev.end > gap))) flush();
    cur.push(w);
    if (/[.!?…]["')\]]*$/.test(w.word)) flush();                  // never run a page across a sentence end
    else if (/[,;:]["')\]]*$/.test(w.word) && cur.length >= 3) flush(); // natural phrase break
  }
  flush();
  // a page stays until the next one starts, but never more than 0.6 s after its last word
  pages.forEach((p, i) => {
    const next = pages[i + 1];
    p.end = Math.min(next ? next.start : Infinity, p.end + 0.6);
  });
  return pages;
};

const display = (w: string) => w.replace(/[.,;:]+(["')\]]*)$/, '$1');

export const WordCaptions: React.FC<{
  t: number;
  words?: Word[];
  /** top edge of the caption block in px (1080×1920 canvas); keep it above the bottom ~280 px */
  y?: number;
  size?: number;
  maxWords?: number;
  /** colour of the spoken word */
  highlight?: string;
  /** dark rounded box behind the text (default) or plain text with a shadow */
  box?: boolean;
  /** time ranges [start, end] in seconds where captions must hide (e.g. under a headline with the same words) */
  hidden?: Array<[number, number]>;
}> = ({t, words = narrationWords, y = 1470, size = 54, maxWords = 4, highlight = C.yellow, box = true, hidden = []}) => {
  const pages = React.useMemo(() => buildPages(words, maxWords), [words, maxWords]);
  if (hidden.some(([a, b]) => t >= a && t < b)) return null;
  const page = pages.find((p) => t >= p.start - 0.08 && t < p.end);
  if (!page) return null;
  const pop = ramp(t, page.start - 0.08, page.start + 0.1);
  return (
    <div style={{position: 'absolute', top: y, left: 70, right: 70, display: 'flex', justifyContent: 'center',
      opacity: pop, transform: `translateY(${(1 - pop) * 18}px) scale(${0.96 + 0.04 * pop})`}}>
      <div style={{fontFamily: FONT, fontWeight: 800, fontSize: size, lineHeight: 1.18, textAlign: 'center',
        padding: box ? '20px 32px' : 0, borderRadius: 26, background: box ? '#070b22e6' : 'transparent',
        color: C.cream, textShadow: box ? 'none' : '0 4px 18px #000a', maxWidth: 940}}>
        {page.words.map((w, k) => {
          const next = page.words[k + 1];
          const active = t >= w.start && t < (next ? next.start : w.end + 0.25);
          return (
            <span key={w.i} style={{color: active ? highlight : undefined, opacity: t >= w.start - 0.02 || active ? 1 : 0.55}}>
              {display(w.word)}{k < page.words.length - 1 ? ' ' : ''}
            </span>
          );
        })}
      </div>
    </div>
  );
};

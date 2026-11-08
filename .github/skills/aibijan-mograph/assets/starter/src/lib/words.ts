// Word-level timing of the narration, written by `npm run align` into src/words.json.
// Times are seconds on the voice-over timeline (= composition seconds; the VO starts at frame 0).
import data from '../words.json';

export type Word = {i: number; word: string; start: number; end: number; conf?: number};
type WordFile = {method: string; duration?: number; words: Word[]};

export const wordFile = data as unknown as WordFile;
export const words: Word[] = wordFile.words ?? [];
/** True only for real forced alignment; "proportional" timing is an estimate. */
export const hasWordTiming = words.length > 0;
export const timingIsVerified = wordFile.method === 'ctc-forced-alignment';

const norm = (s: string) => s.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '');

const find = (phrase: string, nth: number): {first: Word; last: Word} | null => {
  const target = phrase.split(/\s+/).map(norm).filter(Boolean);
  if (target.length === 0) return null;
  let seen = 0;
  for (let i = 0; i + target.length <= words.length; i++) {
    if (target.every((tok, k) => norm(words[i + k].word) === tok)) {
      if (seen === nth) return {first: words[i], last: words[i + target.length - 1]};
      seen++;
    }
  }
  return null;
};

/**
 * Start time (s) of the nth occurrence of a spoken phrase, e.g. at('lima ribu').
 * Without a fallback it throws when the phrase is missing, so a typo never silently mistimes a scene.
 */
export const at = (phrase: string, nth = 0, fallback?: number): number => {
  const hit = find(phrase, nth);
  if (hit) return hit.first.start;
  if (fallback !== undefined) return fallback;
  throw new Error(`Phrase not found in src/words.json: "${phrase}" (occurrence ${nth})`);
};

/** End time (s) of the nth occurrence of a spoken phrase. Same rules as at(). */
export const endOf = (phrase: string, nth = 0, fallback?: number): number => {
  const hit = find(phrase, nth);
  if (hit) return hit.last.end;
  if (fallback !== undefined) return fallback;
  throw new Error(`Phrase not found in src/words.json: "${phrase}" (occurrence ${nth})`);
};

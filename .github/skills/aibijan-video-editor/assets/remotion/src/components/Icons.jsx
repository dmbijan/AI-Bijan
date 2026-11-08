// Minimal stroke icon set (24x24 grid, drawn for this skill — no third-party icon assets).
import React from 'react';

const P = {
  scissors: ['M6 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', 'M6 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', 'M20 4 8.1 15.9', 'M14.5 14.5 20 20', 'M8.1 8.1 12 12'],
  cc: ['M3 5h18v14H3z', 'M10.5 10.2A2.2 2.2 0 1 0 10.5 13.8', 'M17.5 10.2A2.2 2.2 0 1 0 17.5 13.8'],
  sparkle: ['M12 2.5l2.2 6.3 6.3 2.2-6.3 2.2L12 19.5l-2.2-6.3L3.5 11l6.3-2.2z', 'M19 3v4', 'M17 5h4'],
  zoom: ['M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z', 'M21 21l-5-5', 'M11 8v6', 'M8 11h6'],
  film: ['M3 4h18v16H3z', 'M7 4v16', 'M17 4v16', 'M3 8h4', 'M3 12h4', 'M3 16h4', 'M17 8h4', 'M17 12h4', 'M17 16h4'],
  sound: ['M4 9h4l5-4v14l-5-4H4z', 'M16.5 8.5a5 5 0 0 1 0 7', 'M19.5 5.5a9 9 0 0 1 0 13'],
  chat: ['M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.8A8 8 0 1 1 21 12z'],
  play: ['M7 4.5v15l12-7.5z'],
  file: ['M14 3H6v18h12V7z', 'M14 3v4h4', 'M9 13h6', 'M9 17h4'],
  check: ['M4.5 12.5l5 5 10-11'],
  send: ['M4 12l16-8-6 16-2.5-6.5z', 'M11.5 13.5 20 4'],
  arrowDown: ['M12 4v15', 'M5.5 12.5 12 19l6.5-6.5'],
  arrowRight: ['M4 12h15', 'M12.5 5.5 19 12l-6.5 6.5'],
  wand: ['M4 20 15 9', 'M14 4l1 2 2 1-2 1-1 2-1-2-2-1 2-1z', 'M19 11l.6 1.4 1.4.6-1.4.6L19 15l-.6-1.4-1.4-.6 1.4-.6z'],
  bolt: ['M13 2 4 14h7l-1 8 9-12h-7z'],
  layers: ['M12 3 2 8l10 5 10-5z', 'M2 13l10 5 10-5', 'M2 17.5l10 5 10-5'],
  wave: ['M3 12h2', 'M7 8v8', 'M11 5v14', 'M15 9v6', 'M19 7v10', 'M21 12h0'],
  rec: ['M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10z'],
  cursor: ['M5 3l14 7-6 2-2 6z'],
  refresh: ['M20 11a8 8 0 0 0-14.8-3.5', 'M4 4v4h4', 'M4 13a8 8 0 0 0 14.8 3.5', 'M20 20v-4h-4'],
  book: ['M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z', 'M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5'],
  eye: ['M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z'],
  lock: ['M5 11h14v10H5z', 'M8 11V7a4 4 0 0 1 8 0v4', 'M12 15v2'],
  heart: ['M12 20.5S3 15 3 8.8A4.8 4.8 0 0 1 12 6.4a4.8 4.8 0 0 1 9 2.4C21 15 12 20.5 12 20.5z'],
  search: ['M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z', 'M20.5 20.5 16 16'],
};

export const Icon = ({name, size = 32, color = 'currentColor', stroke = 2.2, fill = false, style}) => {
  const paths = P[name] || P.sparkle;
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" style={{display: 'block', flex: 'none', ...style}}
      fill="none" stroke={color} strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round">
      {paths.map((d, i) => <path key={i} d={d} fill={fill || name === 'play' || name === 'rec' ? color : 'none'} />)}
    </svg>
  );
};

export const ICONS = Object.keys(P);

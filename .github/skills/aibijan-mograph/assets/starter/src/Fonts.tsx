import React from 'react';
import {staticFile} from 'remotion';

// Bundled, licensed fonts (public/fonts). Render <FontFaces /> once at the top of every composition.
export const FontFaces: React.FC = () => (
  <style>{`
    @font-face {font-family:'Mograph Sans';src:url('${staticFile('fonts/PlusJakartaSans_700Bold.ttf')}') format('truetype');font-weight:700;}
    @font-face {font-family:'Mograph Sans';src:url('${staticFile('fonts/PlusJakartaSans_800ExtraBold.ttf')}') format('truetype');font-weight:800 900;}
    @font-face {font-family:'Mograph Text';src:url('${staticFile('fonts/PlusJakartaSans_500Medium.ttf')}') format('truetype');font-weight:400 600;}
    @font-face {font-family:'Mograph Fallback';src:url('${staticFile('fonts/DejaVuSans-Bold.ttf')}') format('truetype');font-weight:400 900;}
    @font-face {font-family:'Mograph Mono';src:url('${staticFile('fonts/DejaVuSansMono.ttf')}') format('truetype');font-weight:400 800;}
    body {word-spacing:0.06em;}
  `}</style>
);

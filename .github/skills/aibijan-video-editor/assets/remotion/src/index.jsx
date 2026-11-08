import React from 'react';
import {Composition, registerRoot, staticFile} from 'remotion';
import {loadFont} from '@remotion/fonts';
import {Main} from './Main.jsx';

// Fonts ship with the skill (SIL OFL). They are copied to public/fonts by render-video.py.
const FONTS = [
  // Reels-style families (v3): IMPACT Anton + Montserrat, SYSTEM Geist + Geist Mono, EDITORIAL Playfair Display + Poppins,
  // CREATOR POP TikTok Sans. DM Sans stays for the deliberately plain "raw" subtitle look.
  ['Anton', 'Anton_400Regular.woff2', '400'],
  ['Montserrat', 'Montserrat_500.woff2', '500'],
  ['Montserrat', 'Montserrat_700.woff2', '700'],
  ['Montserrat', 'Montserrat_800.woff2', '800'],
  ['Montserrat', 'Montserrat_900.woff2', '900'],
  ['Poppins', 'Poppins_500.woff2', '500'],
  ['Poppins', 'Poppins_600.woff2', '600'],
  ['Poppins', 'Poppins_700.woff2', '700'],
  ['Poppins', 'Poppins_800.woff2', '800'],
  ['PlayfairDisplay', 'PlayfairDisplay_400.woff2', '400'],
  ['PlayfairDisplay', 'PlayfairDisplay_500.woff2', '500'],
  ['PlayfairDisplay', 'PlayfairDisplay_600.woff2', '600'],
  ['PlayfairDisplay', 'PlayfairDisplay_400Italic.woff2', '400', 'italic'],
  ['PlayfairDisplay', 'PlayfairDisplay_500Italic.woff2', '500', 'italic'],
  ['PlayfairDisplay', 'PlayfairDisplay_600Italic.woff2', '600', 'italic'],
  ['TikTokSans', 'TikTokSans_500.woff2', '500'],
  ['TikTokSans', 'TikTokSans_700.woff2', '700'],
  ['TikTokSans', 'TikTokSans_800.woff2', '800'],
  ['TikTokSans', 'TikTokSans_900.woff2', '900'],
  // MOGRAPH MIX: Bricolage Grotesque (display) + Manrope (captions / UI)
  ['BricolageGrotesque', 'BricolageGrotesque_700.woff2', '700'],
  ['BricolageGrotesque', 'BricolageGrotesque_800.woff2', '800'],
  ['Manrope', 'Manrope_600.woff2', '600'],
  ['Manrope', 'Manrope_700.woff2', '700'],
  ['Manrope', 'Manrope_800.woff2', '800'],
  // STORYTELLER: Unbounded (display / 3D shouts) + Plus Jakarta Sans (typed captions / UI)
  ['Unbounded', 'Unbounded_700.woff2', '700'],
  ['Unbounded', 'Unbounded_800.woff2', '800'],
  ['Unbounded', 'Unbounded_900.woff2', '900'],
  ['PlusJakarta', 'PlusJakartaSans_600.woff2', '600'],
  ['PlusJakarta', 'PlusJakartaSans_700.woff2', '700'],
  ['PlusJakarta', 'PlusJakartaSans_800.woff2', '800'],
  ['Geist', 'Geist_Variable.woff2', '100 900'],
  ['GeistMono', 'GeistMono_Variable.woff2', '100 900'],
  ['DMSans', 'DMSans_500Medium.ttf', '500'],
  ['DMSans', 'DMSans_700Bold.ttf', '700'],
  ['SpaceGrotesk', 'SpaceGrotesk_500Medium.ttf', '500'],
  ['SpaceGrotesk', 'SpaceGrotesk_700Bold.ttf', '700'],
  ['PlusJakartaSans', 'PlusJakartaSans_800ExtraBold.ttf', '800'],
  // DEPTH (v3.8): Inter Tight (display type behind the speaker, captions) + Instrument Serif italic (accent words)
  ['InterTight', 'InterTight_600.woff2', '600'],
  ['InterTight', 'InterTight_700.woff2', '700'],
  ['InterTight', 'InterTight_800.woff2', '800'],
  ['InterTight', 'InterTight_900.woff2', '900'],
  ['InstrumentSerif', 'InstrumentSerif_400.woff2', '400'],
  ['InstrumentSerif', 'InstrumentSerif_400Italic.woff2', '400', 'italic'],
  // LUMEN (v3.9): Outfit (luminous gradient display + captions) + Ms Madi (handwritten script accent)
  ['Outfit', 'Outfit_600.woff2', '600'],
  ['Outfit', 'Outfit_700.woff2', '700'],
  ['Outfit', 'Outfit_800.woff2', '800'],
  ['Outfit', 'Outfit_900.woff2', '900'],
  ['MsMadi', 'MsMadi_400.woff2', '400'],
  // POSTER (v3.9): Archivo (heavy poster titles + captions)
  ['Archivo', 'Archivo_700.woff2', '700'],
  ['Archivo', 'Archivo_800.woff2', '800'],
  ['Archivo', 'Archivo_900.woff2', '900'],
];
for (const [family, file, weight, style] of FONTS) {
  loadFont({family, url: staticFile('fonts/' + file), weight, style: style || 'normal'});
}

const Root = () => (
  <Composition
    id="Edit"
    component={Main}
    durationInFrames={600}
    fps={60}
    width={1080}
    height={1920}
    defaultProps={{}}
    calculateMetadata={({props}) => ({
      durationInFrames: props.durationInFrames || 600,
      fps: props.fps || 60,
      width: props.width || 1080,
      height: props.height || 1920,
    })}
  />
);

registerRoot(Root);

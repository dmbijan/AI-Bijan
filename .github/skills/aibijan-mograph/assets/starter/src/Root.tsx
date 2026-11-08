import React from 'react';
import {Composition} from 'remotion';
import {Main} from './Main';
import {KitGallery} from './KitGallery';
import {StyleGallery} from './StyleGallery';
import project from './project.json';

export const Root: React.FC = () => (
  <>
  <Composition
    id="Mograph"
    component={Main}
    width={1080}
    height={1920}
    fps={60}
    durationInFrames={Math.max(60, Math.ceil(project.durationSeconds * 60))}
  />
  {/* Component kit preview / QA sheet: npm run stills with COMP=KitGallery. Never the delivered video. */}
  <Composition id="KitGallery" component={KitGallery} width={1080} height={1920} fps={60} durationInFrames={28 * 60} />
  {/* Named style kits (6 looks) QA sheet: npm run style-preview. Never the delivered video. */}
  <Composition id="StyleGallery" component={StyleGallery} width={1080} height={1920} fps={60} durationInFrames={24 * 60} />
  </>
);

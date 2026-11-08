import React from 'react';
import {AbsoluteFill, useCurrentFrame, useVideoConfig} from 'remotion';
import {C} from './theme';
import {FontFaces} from './Fonts';
import {
  BankAppScreen, Banknote, BarChart, Callout, ChatBubble, IndonesiaMap, KineticHeadline, LineChart, NotificationCard, PercentRing,
  PersonFigure, PhoneFrame, PriceTag, RupiahCounter, SimulasiBadge, Thermometer, Warung,
} from './components/kit';

// Visual QA sheet and living usage examples for the component kit (composition "KitGallery").
// Not a template for buyer videos: build original scenes, then reuse these pieces where they explain the story.

const PANEL = 4;
const Panel: React.FC<{i: number; t: number; bg: string; children: (lt: number) => React.ReactNode}> = ({i, t, bg, children}) =>
  t >= i * PANEL && t < (i + 1) * PANEL ? <AbsoluteFill style={{background: bg}}>{children(t - i * PANEL)}</AbsoluteFill> : null;

export const KitGallery: React.FC = () => {
  const frame = useCurrentFrame();
  const {fps} = useVideoConfig();
  const t = frame / fps;
  return (
    <AbsoluteFill style={{background: C.paper, overflow: 'hidden'}}>
      <FontFaces />
      <Panel i={0} t={t} bg={C.sky}>{(lt) => <>
        <NotificationCard t={lt} at={0.3} y={150} app="Bank Kamu" title="Saldo kamu berubah" body="Cek aplikasi untuk detailnya." />
        <PhoneFrame t={lt} x={540} y={520} width={560} enterAt={0}>
          <BankAppScreen t={lt} balanceFrom={5000000} balanceTo={5000} changeAt={1.2} note="Nilai tetap sama" transactions={[
            {label: 'Belanja warung', amount: -25, at: 2.4, note: 'Hari ini'}, {label: 'Gaji masuk', amount: 4500, at: 2.6, note: 'Kemarin'}]} />
        </PhoneFrame>
      </>}</Panel>
      <Panel i={1} t={t} bg={C.paper}>{(lt) => <>
        <SimulasiBadge t={lt} y={180} />
        <RupiahCounter t={lt} from={5000000} to={5000} at={0.6} y={300} showFrom />
        <Banknote t={lt} value={100000} x={420} y={900} rotate={-12} enterAt={0.2} />
        <Banknote t={lt} value={50000} x={600} y={960} rotate={6} enterAt={0.35} />
        <Banknote t={lt} value={20000} x={500} y={1080} rotate={-3} enterAt={0.5} />
        <PriceTag t={lt} at={1.6} x={330} y={1360} label="Kopi" value="Rp20.000" />
        <PriceTag t={lt} at={2.0} x={760} y={1360} label="Besok" value="Rp20" />
      </>}</Panel>
      <Panel i={2} t={t} bg={C.sky}>{(lt) => <>
        <KineticHeadline t={lt} at={0.1} y={250} text="Bagaimana kalau Indonesia turun salju?" highlight={['salju?']} size={84} />
        <IndonesiaMap t={lt} y={760} revealAt={0.3} pins={[
          {city: 'Medan', at: 1.4, labelSide: 'top'}, {city: 'Jakarta', at: 1.6, labelSide: 'bottom'}, {city: 'Makassar', at: 1.8, labelSide: 'top'},
          {city: 'Jayapura', at: 2.0, labelSide: 'left'}]} route={{cities: ['Jakarta', 'Makassar', 'Jayapura'], at: 2.2}} />
        <SimulasiBadge t={lt} text="WHAT IF" y={1300} at={0.4} />
      </>}</Panel>
      <Panel i={3} t={t} bg={C.navy}>{(lt) => <>
        <KineticHeadline t={lt} at={0} y={200} text="Harga naik tiap tahun" color={C.cream} highlight={['naik']} highlightColor={C.yellow} size={80} />
        <BarChart t={lt} y={420} dark highlight={3} format={(v) => `Rp${Math.round(v / 1000)}rb`} data={[
          {label: '2023', value: 12000}, {label: '2024', value: 14000}, {label: '2025', value: 16500}, {label: '2026', value: 20000}]} growAt={0.3} />
        <PercentRing t={lt} at={1.2} value={67} x={540} y={1450} size={340} dark label="naik dalam 3 tahun" />
      </>}</Panel>
      <Panel i={4} t={t} bg={C.paper}>{(lt) => <>
        <LineChart t={lt} y={200} points={[27, 27.5, 26, 18, 6, -2]} labels={['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun']} drawAt={0.2}
          format={(v) => `${v}°C`} color={C.blue} />
        <Thermometer t={lt} from={31} to={-2} at={0.8} x={420} y={900} height={800} />
        <SimulasiBadge t={lt} y={1780} />
      </>}</Panel>
      <Panel i={5} t={t} bg={C.cream}>{(lt) => <>
        <PersonFigure t={lt} x={300} y={700} pose={lt < 1.5 ? 'stand' : 'phone'} fromPose="stand" poseAt={1.2} mood={lt < 2 ? 'neutral' : 'surprised'} shirt={C.coral} />
        <PersonFigure t={lt} x={780} y={700} pose={lt < 1 ? 'stand' : lt < 2.5 ? 'wave' : 'think'} fromPose={lt < 2.5 ? 'stand' : 'wave'} poseAt={lt < 2.5 ? 0.8 : 2.5}
          mood="happy" hijab="#2E7D52" shirt={C.blue} skin="#B8805A" seed={2} />
        <ChatBubble t={lt} at={0.4} x={90} y={200} text="Uangku tinggal Rp5.000?!" />
        <ChatBubble t={lt} at={1.6} side="right" x={990} y={380} text="Tenang, nilainya tetap sama" />
      </>}</Panel>
      <Panel i={6} t={t} bg={C.sky}>{(lt) => <>
        <Warung t={lt} x={540} y={640} enterAt={0.1} />
        <PersonFigure t={lt} x={860} y={1000} height={560} pose={lt < 1.6 ? 'stand' : 'point'} fromPose="stand" poseAt={1.6} flip mood="happy" shirt={C.yellow} />
        <Callout t={lt} at={2.0} shape="circle" x={540} y={664} w={380} h={118} />
        <Callout t={lt} at={2.4} shape="arrow" x={300} y={1500} to={[420, 1180]} />
        <KineticHeadline t={lt} at={0.3} y={180} text="Warung tetap menerima tunai" size={72} highlight={['tunai']} />
      </>}</Panel>
    </AbsoluteFill>
  );
};

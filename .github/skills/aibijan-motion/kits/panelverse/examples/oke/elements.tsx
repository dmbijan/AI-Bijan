// Elements made for THIS script only (registered next to the library items).
import { registerItems } from "../../kits/panelverse/engine/three/sets.tsx";
import { Toon } from "../../kits/panelverse/engine/three/Toon.tsx";
import { G } from "../../kits/panelverse/engine/three/geometries.ts";
import { Sign } from "../../kits/panelverse/engine/three/props.tsx";

registerItems({
  gerobak: {
    desc: "bakso cart: glass cabinet on a red cart with two wheels and a BAKSO sign",
    render: ({ f }) => (
      <group>
        <Toon geo={G.roundedBox(1.6, 0.7, 0.8, 0.06)} sw="red" mat={{ pattern: 0.1 }} outline={4} position={[0, 0.75, 0]} />
        <Toon geo={G.roundedBox(1.5, 0.6, 0.7, 0.04)} sw="glass" mat={{ flat: true, pattern: 0.08 }} outline={3.5} position={[0, 1.42, 0]} />
        <Toon geo={G.roundedBox(1.7, 0.06, 0.9, 0.02)} sw="white" outline={3} position={[0, 1.75, 0]} />
        {[-1, 1].map((s) => (
          <Toon key={s} geo={G.cylinder(1, 1, 24)} sw="ink" outline={2.5} position={[s * 0.55, 0.28, 0.42]} rotation={[Math.PI / 2, 0, 0]} scale={[0.28, 0.06, 0.28]} />
        ))}
        {[0, 1, 2].map((i) => (
          <Toon key={`b${i}`} geo={G.sphere()} sw="bakso" outline={2.5} position={[-0.4 + i * 0.4, 1.3, 0.1]} scale={0.09} />
        ))}
        <Toon geo={G.tube("cartwisp", [[0, 0, 0], [0.04, 0.12, 0], [-0.03, 0.24, 0], [0.02, 0.36, 0]], 0.012)} sw="white" outline={2} position={[0.35, 1.8 + Math.sin(f * 0.08) * 0.02, 0.1]} />
        <Sign text="BAKSO" w={1.3} h={0.42} sw="yellow" position={[0, 2.15, 0.02]} depth={0.06} />
      </group>
    ),
  },
});

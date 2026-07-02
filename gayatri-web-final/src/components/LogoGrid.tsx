import { useEffect, useMemo, useRef, useState } from "react";
import gsap from "gsap";
import type { Brand } from "../data/brands";

type Direction = "horizontal" | "vertical";

const BOX_COUNT = 6;
const CYCLE_MS = 2600;

function LogoBox({ items, direction }: { items: Brand[]; direction: Direction }) {
  const elRef = useRef<HTMLDivElement>(null);
  const indexRef = useRef(0);
  const [brand, setBrand] = useState(items[0]);

  useEffect(() => {
    if (items.length < 2) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const el = elRef.current;
    if (!el) return;

    // horizontal: old slides out to the right, new enters from the left.
    // vertical: old slides down, new enters from above.
    const axis = direction === "horizontal" ? "x" : "y";
    const outTo = 56;
    const inFrom = -56;
    let timeoutId: number;

    function cycle() {
      gsap.to(el, {
        [axis]: outTo,
        opacity: 0,
        duration: 0.45,
        ease: "power3.in",
        onComplete: () => {
          indexRef.current = (indexRef.current + 1) % items.length;
          setBrand(items[indexRef.current]);
          gsap.fromTo(
            el,
            { [axis]: inFrom, opacity: 0 },
            { [axis]: 0, opacity: 1, duration: 0.6, ease: "back.out(1.5)" }
          );
        },
      });
      timeoutId = window.setTimeout(cycle, CYCLE_MS);
    }

    // All boxes share the same fire time, so every box transitions in sync.
    timeoutId = window.setTimeout(cycle, CYCLE_MS);
    return () => window.clearTimeout(timeoutId);
  }, [direction, items]);

  return (
    <div className="relative h-32 md:h-36 overflow-hidden rounded-xl border border-border bg-white shadow-md hover:shadow-xl transition-shadow duration-300 flex items-center justify-center">
      <div ref={elRef} className="absolute flex items-center justify-center px-8">
        {brand.logo ? (
          <img src={brand.logo} alt={brand.name} className="h-14 md:h-16 w-auto max-w-[190px] object-contain" />
        ) : (
          <span className="whitespace-nowrap text-xl font-bold text-slate-400">{brand.name}</span>
        )}
      </div>
    </div>
  );
}

export default function LogoGrid({ items }: { items: Brand[] }) {
  const buckets = useMemo(() => {
    // Pull consecutive pairs from a doubled, wrapped list so every box always
    // gets at least 2 distinct logos to cycle between, even when the brand
    // count isn't a clean multiple of BOX_COUNT * 2 (a lone leftover item
    // would otherwise land in a box by itself and never have anything to
    // transition to).
    const doubled = [...items, ...items];
    return Array.from({ length: BOX_COUNT }, (_, i) => {
      const a = doubled[(i * 2) % doubled.length];
      const b = doubled[(i * 2 + 1) % doubled.length];
      return a.name === b.name ? [a] : [a, b];
    });
  }, [items]);

  return (
    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-6">
      {buckets.map((bucket, i) => (
        <LogoBox
          key={i}
          items={bucket.length ? bucket : items}
          direction={i % 2 === 0 ? "horizontal" : "vertical"}
        />
      ))}
    </div>
  );
}

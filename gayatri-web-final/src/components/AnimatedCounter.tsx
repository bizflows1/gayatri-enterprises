import { useEffect, useRef, useState } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export default function AnimatedCounter({
  value,
  label,
  suffix = "",
}: {
  value: number;
  label: string;
  suffix?: string;
}) {
  const ref = useRef<HTMLDivElement>(null);
  const [count, setCount] = useState(0);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    const counter = { val: 0 };
    const trigger = ScrollTrigger.create({
      trigger: el,
      start: "top 85%",
      once: true,
      onEnter: () => {
        gsap.to(counter, {
          val: value,
          duration: 1.5,
          ease: "power2.out",
          onUpdate: () => setCount(Math.floor(counter.val)),
        });
      },
    });

    return () => trigger.kill();
  }, [value]);

  return (
    <div ref={ref}>
      <div className="text-4xl md:text-5xl font-mono font-semibold text-navy mb-2 tabular-nums">
        {count}
        {suffix}
      </div>
      <div className="text-xs uppercase tracking-widest text-slate font-semibold">{label}</div>
    </div>
  );
}

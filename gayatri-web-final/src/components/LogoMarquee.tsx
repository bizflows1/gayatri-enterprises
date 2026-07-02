import type { Brand } from "../data/brands";

interface LogoMarqueeProps {
  items: Brand[];
  /** Seconds for one full loop — lower is faster. */
  speed?: number;
}

export default function LogoMarquee({ items, speed = 30 }: LogoMarqueeProps) {
  return (
    <div
      className="relative w-full overflow-hidden"
      style={{
        maskImage: "linear-gradient(to right, transparent, black 10%, black 90%, transparent)",
        WebkitMaskImage: "linear-gradient(to right, transparent, black 10%, black 90%, transparent)",
      }}
    >
      <div
        className="logo-marquee-track flex w-max items-center hover:[animation-play-state:paused]"
        style={{ animationDuration: `${speed}s` }}
      >
        {[...items, ...items].map((item, i) => (
          <div key={`${item.name}-${i}`} className="flex h-20 shrink-0 items-center justify-center px-10">
            {item.logo ? (
              <img
                src={item.logo}
                alt={item.name}
                className="h-14 w-auto max-w-[190px] object-contain opacity-70 transition-opacity hover:opacity-100"
              />
            ) : (
              <span className="whitespace-nowrap text-2xl font-bold text-slate-400 transition-colors hover:text-navy">
                {item.name}
              </span>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}

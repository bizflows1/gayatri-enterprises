import { useEffect, useState } from "react";

// Real samples pulled from the catalog — the scan is dramatizing an actual
// claim (every batch is verified), not inventing a number that means nothing.
const SAMPLES = [
  { cas: "67-64-1", assay: 99.8 },
  { cas: "7647-01-0", assay: 99.5 },
  { cas: "7647-14-5", assay: 99.9 },
  { cas: "67-56-1", assay: 99.9 },
  { cas: "61-73-4", assay: 98.7 },
];

export default function VerificationReadout() {
  const [i, setI] = useState(0);
  const [locked, setLocked] = useState(false);

  useEffect(() => {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      setLocked(true);
      return;
    }
    let count = 0;
    const id = setInterval(() => {
      count++;
      setI((prev) => (prev + 1) % SAMPLES.length);
      if (count >= 9) {
        clearInterval(id);
        setLocked(true);
      }
    }, 90);
    return () => clearInterval(id);
  }, []);

  const sample = SAMPLES[i];

  return (
    <div className="flex items-center gap-3 text-xs font-mono uppercase tracking-widest mb-8 h-5">
      <span
        className={`w-2 h-2 rounded-full shrink-0 transition-colors duration-300 ${
          locked ? "bg-emerald-light" : "bg-white/40 animate-pulse"
        }`}
      />
      {locked ? (
        <span className="text-emerald-light tabular-nums">Authorised distributor &middot; ISO 9001:2015</span>
      ) : (
        <span className="text-white/45 tabular-nums">
          Scanning &middot; CAS {sample.cas} &middot; Assay {sample.assay.toFixed(1)}%
        </span>
      )}
    </div>
  );
}

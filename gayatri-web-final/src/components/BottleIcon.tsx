// Maps a pack-size string (500ml, 2.5L, 25kg, ...) to a litre/kg-equivalent
// score, then renders a bottle silhouette whose height reflects that score —
// so a 25L drum visibly reads bigger than a 500ml bottle in the same row.
function sizeToScore(size: string): number {
  const match = size.match(/^([\d.]+)\s*(ml|l|g|kg)$/i);
  if (!match) return 1;
  const num = parseFloat(match[1]);
  const unit = match[2].toLowerCase();
  if (unit === "ml") return num / 1000;
  if (unit === "g") return num / 1000;
  return num;
}

export default function BottleIcon({ size, className = "" }: { size: string; className?: string }) {
  const score = sizeToScore(size);
  const height = Math.max(14, Math.min(32, 9 + Math.sqrt(score) * 11));
  const width = height * 0.6;

  return (
    <svg
      viewBox="0 0 24 38"
      width={width}
      height={height}
      className={`shrink-0 ${className}`}
      fill="none"
      stroke="currentColor"
      strokeWidth="1.6"
      strokeLinejoin="round"
      strokeLinecap="round"
      aria-hidden="true"
    >
      <path d="M9 1.5h6v5l2.5 3.2V35a2 2 0 0 1-2 2h-9a2 2 0 0 1-2-2V9.7l2.5-3.2v-5Z" />
      <path d="M9 6.5h6" />
    </svg>
  );
}

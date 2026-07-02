import { useEffect, useRef, useState } from "react";

// Subtle per-character focus-in on text change — no glitch/scramble, just a
// clean staggered reveal befitting a clinical, professional tone.
// Characters are grouped by word (trailing punctuation included) so the
// browser can only wrap at real spaces, never mid-word or before a comma.
export default function ScrambleText({ text, className }: { text: string; className?: string }) {
  const [renderKey, setRenderKey] = useState(0);
  const [reduced, setReduced] = useState(false);
  const prevText = useRef(text);

  useEffect(() => {
    setReduced(window.matchMedia("(prefers-reduced-motion: reduce)").matches);
  }, []);

  useEffect(() => {
    if (prevText.current === text) return;
    prevText.current = text;
    setRenderKey((k) => k + 1);
  }, [text]);

  if (reduced) {
    return <span className={className}>{text}</span>;
  }

  const words = text.split(" ");
  let charIndex = 0;

  return (
    <span className={className}>
      {words.map((word, wi) => {
        const wordStart = charIndex;
        charIndex += word.length + 1;
        return (
          <span key={`${renderKey}-${wi}`}>
            <span className="inline-block whitespace-nowrap">
              {word.split("").map((ch, ci) => (
                <span key={ci} className="char-reveal" style={{ animationDelay: `${(wordStart + ci) * 24}ms` }}>
                  {ch}
                </span>
              ))}
            </span>
            {wi < words.length - 1 ? " " : ""}
          </span>
        );
      })}
    </span>
  );
}

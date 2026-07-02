export function openMailto(to: string, subject: string, lines: Array<[string, string]>) {
  const body = lines
    .filter(([, value]) => value && value.trim() !== "")
    .map(([label, value]) => `${label}: ${value}`)
    .join("\n");
  const url = `mailto:${to}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  window.location.href = url;
}

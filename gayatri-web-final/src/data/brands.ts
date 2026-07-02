export interface Brand {
  name: string;
  logo?: string | null;
}

export const brands: Brand[] = [
  { name: "ThermoFisher", logo: "/images/brands/thermofisher.svg" },
  { name: "Rankem", logo: "/images/brands/rankem.jpeg" },
  { name: "Whatman", logo: "/images/brands/whatman.jpeg" },
  { name: "Schott", logo: "/images/brands/schott.svg" },
  { name: "Borosil", logo: "/images/brands/borosil.svg" },
  { name: "Fisher Scientific", logo: "/images/brands/fisher-scientific.svg" },
  { name: "Merck", logo: "/images/brands/merck.svg" },
  { name: "Sigma-Aldrich", logo: "/images/brands/sigma-aldrich.jpg" },
  { name: "Tarsons", logo: "/images/brands/tarsons.jpg" },
  { name: "Himedia", logo: "/images/brands/himedia.jpeg" },
  { name: "Eppendorf", logo: "/images/brands/eppendorf.svg" },
  { name: "Qualigens", logo: "/images/brands/qualigens.png" },
];

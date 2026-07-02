export interface Product {
  id: number;
  cas: string;
  name: string;
  cat: string;
  brand: string;
  grade: string;
  packSizes: string[];
  batch: string;
  expiry: string;
  assay: number;
  /** Placeholder until the admin backend lets us upload real per-product photos. */
  image?: string;
}

export const categories = ["All", "Solvents", "Acids", "Salts", "Stains"];
export const productBrands = ["All", "Rankem", "Qualigens", "Himedia", "Merck"];

export const products: Product[] = [
  { id: 1, cas: "67-64-1", name: "Acetone AR/ACS", cat: "Solvents", brand: "Rankem", grade: "AR Grade", packSizes: ["500ml", "2.5L", "5L", "25L"], batch: "GE24S118", expiry: "03/2027", assay: 99.8, image: "/images/solvent_bottle.png" },
  { id: 2, cas: "7647-01-0", name: "Hydrochloric Acid 37%", cat: "Acids", brand: "Qualigens", grade: "AR Grade", packSizes: ["500ml", "2.5L", "5L"], batch: "GE24S204", expiry: "06/2027", assay: 99.5, image: "/images/acid_bottle.png" },
  { id: 3, cas: "61-73-4", name: "Methylene Blue", cat: "Stains", brand: "Himedia", grade: "Microscopy Grade", packSizes: ["25g", "100g"], batch: "GE24I061", expiry: "04/2027", assay: 98.7, image: "/images/stain_bottle.png" },
  { id: 4, cas: "17372-87-1", name: "Eosin Y (Yellowish)", cat: "Stains", brand: "Himedia", grade: "Microscopy Grade", packSizes: ["25g", "100g"], batch: "GE24I089", expiry: "07/2027", assay: 99.0, image: "/images/stain_bottle.png" },
  { id: 5, cas: "7647-14-5", name: "Sodium Chloride", cat: "Salts", brand: "Merck", grade: "AR Grade", packSizes: ["500g", "1kg", "5kg", "25kg"], batch: "GE24B112", expiry: "02/2028", assay: 99.9, image: "/images/salt_jar.png" },
  { id: 6, cas: "7664-93-9", name: "Sulphuric Acid 98%", cat: "Acids", brand: "Rankem", grade: "AR Grade", packSizes: ["500ml", "2.5L", "5L", "25L"], batch: "GE24S118", expiry: "03/2027", assay: 98.0, image: "/images/acid_bottle.png" },
  { id: 7, cas: "67-56-1", name: "Methanol", cat: "Solvents", brand: "Merck", grade: "HPLC Grade", packSizes: ["500ml", "2.5L", "25L"], batch: "GE24V442", expiry: "01/2027", assay: 99.9, image: "/images/solvent_bottle.png" },
  { id: 8, cas: "1310-58-3", name: "Potassium Hydroxide Pellets", cat: "Salts", brand: "Qualigens", grade: "LR Grade", packSizes: ["500g", "1kg", "25kg"], batch: "GE24B176", expiry: "08/2027", assay: 98.5, image: "/images/salt_jar.png" },
  { id: 9, cas: "64-17-5", name: "Ethanol Absolute", cat: "Solvents", brand: "Merck", grade: "AR Grade", packSizes: ["500ml", "2.5L", "5L"], batch: "GE24V587", expiry: "05/2027", assay: 99.9, image: "/images/solvent_bottle.png" },
  { id: 10, cas: "547-58-0", name: "Methyl Orange Indicator", cat: "Stains", brand: "Himedia", grade: "LR Grade", packSizes: ["25g", "100g"], batch: "GE24I073", expiry: "09/2027", assay: 98.9, image: "/images/stain_bottle.png" },
];

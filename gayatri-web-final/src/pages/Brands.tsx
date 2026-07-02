import { useEffect, useState } from "react";
import { motion } from "motion/react";
import { brands as staticBrands, type Brand } from "../data/brands";
import { api } from "../lib/api";

function useBrands(): Brand[] {
  const [brands, setBrands] = useState<Brand[]>(staticBrands);

  useEffect(() => {
    let cancelled = false;
    api.get<{ brands: Brand[] }>("/api/brands")
      .then((data) => { if (!cancelled && data.brands?.length) setBrands(data.brands); })
      .catch(() => {});
    return () => { cancelled = true; };
  }, []);

  return brands;
}

export default function Brands() {
  const brands = useBrands();

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-[90rem] mx-auto px-6 md:px-12">
        <motion.div
           initial={{ opacity: 0, y: 20 }}
           animate={{ opacity: 1, y: 0 }}
           transition={{ duration: 0.8, ease: "easeOut" }}
           className="max-w-3xl mb-16"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
             <div className="w-8 h-[1px] bg-emerald"></div>
             <span>OUR PARTNERS</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Authorised<br/>distributors.
          </h1>
          <p className="text-xl text-slate font-light leading-relaxed">
            We officially partner with the world's leading chemical and glassware manufacturers to bring trusted quality directly to your lab bench.
          </p>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, scale: 1.03 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 1 }}
          className="w-full aspect-[16/6] rounded-2xl overflow-hidden mb-16 relative"
        >
          <img
            src="/images/brands_banner.jpeg"
            alt="Distribution agreements and compliance documentation review"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-navy/40"></div>
          <div className="absolute inset-0 flex items-center px-8 md:px-16">
            <p className="text-white text-lg md:text-2xl font-heading font-medium max-w-xl leading-snug">
              Every partnership is backed by verified supply agreements and compliance audits.
            </p>
          </div>
        </motion.div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
          {brands.map((brand, i) => (
             <motion.div
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.05 }}
                key={brand.name}
                className="bg-soft-bg rounded-2xl h-32 flex items-center justify-center p-6 border border-navy/5 group hover:bg-white hover:shadow-lg hover:border-emerald/20 transition-all cursor-default"
             >
                {brand.logo ? (
                  <img src={brand.logo} alt={`${brand.name} logo`} className="max-h-12 max-w-full object-contain" />
                ) : (
                  <span className="font-heading font-bold text-xl text-slate-400 group-hover:text-navy transition-colors">
                    {brand.name}
                  </span>
                )}
             </motion.div>
          ))}
        </div>
      </div>
    </div>
  );
}

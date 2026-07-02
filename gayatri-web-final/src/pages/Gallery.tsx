import { useEffect, useRef, useState } from "react";
import { motion, AnimatePresence } from "motion/react";
import { Play, X, ChevronLeft, ChevronRight } from "lucide-react";

const images = [
  { title: "In-House Analytical Lab", category: "Laboratories", url: "/images/lab_analytical.jpeg" },
  { title: "Quality Control Bench", category: "Laboratories", url: "/images/stock_schott.jpeg" },
  { title: "Compliance & Batch Testing", category: "Certifications", url: "/images/stock_borosil.jpeg" },
  { title: "Precision Reagent Handling", category: "Products", url: "/images/gallery_7.jpeg" },
  { title: "Secure Packaging & Dispatch", category: "Logistics", url: "/images/stock_merck.jpeg" },
  { title: "Delivery Fleet", category: "Logistics", url: "/images/gallery_5.jpeg" },
  { title: "Cross-Country Distribution", category: "Logistics", url: "/images/gallery_6.jpeg" },
  { title: "Warehouse Stock Walkthrough", category: "Logistics", url: "/videos/warehouse_stock.mp4", video: true },
  { title: "Inventory & Stock Audit", category: "Logistics", url: "/images/stock_general.jpeg" },
  { title: "Client Consultation", category: "Company", url: "/images/brands_banner.jpeg" },
  { title: "Advanced Spectroscopy Lab", category: "Laboratories", url: "/images/lab_glassware_zoom.jpeg" },
];

const filters = ["All", "Laboratories", "Logistics", "Products", "Company", "Certifications"];

function GalleryTile({ item, onOpen }: { item: (typeof images)[number]; onOpen: () => void }) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const [playing, setPlaying] = useState(false);

  function toggleVideo() {
    const v = videoRef.current;
    if (!v) return;
    if (v.paused) v.play();
    else v.pause();
  }

  return (
    <div
      className="group relative rounded-2xl overflow-hidden aspect-[4/3] cursor-pointer bg-soft-bg"
      onClick={item.video ? toggleVideo : onOpen}
    >
      {item.video ? (
        <video
          ref={videoRef}
          src={item.url}
          muted
          loop
          playsInline
          preload="metadata"
          className="absolute inset-0 w-full h-full object-cover"
          onPlay={() => setPlaying(true)}
          onPause={() => setPlaying(false)}
        />
      ) : (
        <img src={item.url} alt={item.title} className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" />
      )}

      {item.video && !playing && (
        <div className="absolute inset-0 flex items-center justify-center bg-navy/30">
          <span className="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center text-navy">
            <Play className="w-5 h-5 ml-0.5" fill="currentColor" />
          </span>
        </div>
      )}

      <div className="absolute inset-0 bg-navy/0 group-hover:bg-navy/80 transition-colors duration-500 flex flex-col items-center justify-center p-6 text-center pointer-events-none">
        <span className="text-emerald-light text-xs font-bold uppercase tracking-widest mb-2 opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
          {item.category}
        </span>
        <h3 className="text-white font-heading font-medium text-xl opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 delay-75">
          {item.title}
        </h3>
      </div>
    </div>
  );
}

export default function Gallery() {
  const [active, setActive] = useState("All");
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);
  const visible = active === "All" ? images : images.filter((img) => img.category === active);

  function close() {
    setLightboxIndex(null);
  }

  function step(delta: number) {
    setLightboxIndex((i) => {
      if (i === null) return i;
      return (i + delta + visible.length) % visible.length;
    });
  }

  useEffect(() => {
    if (lightboxIndex === null) return;
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") close();
      if (e.key === "ArrowRight") step(1);
      if (e.key === "ArrowLeft") step(-1);
    }
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [lightboxIndex, visible.length]);

  const current = lightboxIndex !== null ? visible[lightboxIndex] : null;

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-[90rem] mx-auto px-6 md:px-12">
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="mb-16 max-w-3xl"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
            <div className="w-8 h-[1px] bg-emerald"></div>
            <span>FACILITY GALLERY</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Inside Gayatri.
          </h1>
          <p className="text-xl text-slate font-light">
            A look at our ISO 9001 certified labs, packaging floor, and logistics network.
          </p>
        </motion.div>

        <div className="flex gap-8 w-full overflow-x-auto pb-8 hide-scrollbar mb-12 thin-border-b">
          {filters.map((filter) => (
            <button
              key={filter}
              onClick={() => setActive(filter)}
              className={`flex-shrink-0 text-sm tracking-widest uppercase transition-colors relative pb-4 ${
                active === filter ? "text-navy font-semibold" : "text-slate hover:text-navy"
              }`}
            >
              {filter}
              {active === filter && (
                <motion.div layoutId="gallery-underline" className="absolute bottom-0 left-0 w-full h-[2px] bg-emerald" />
              )}
            </button>
          ))}
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {visible.map((img, i) => (
            <GalleryTile key={img.title} item={img} onOpen={() => setLightboxIndex(i)} />
          ))}
        </div>
      </div>

      <AnimatePresence>
        {current && !current.video && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] bg-navy/95 flex items-center justify-center p-6 md:p-16"
            onClick={close}
          >
            <button
              className="absolute top-6 right-6 w-12 h-12 rounded-full border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-navy transition-all"
              onClick={close}
              aria-label="Close"
            >
              <X size={20} />
            </button>

            <button
              className="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-navy transition-all"
              onClick={(e) => { e.stopPropagation(); step(-1); }}
              aria-label="Previous image"
            >
              <ChevronLeft size={20} />
            </button>
            <button
              className="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-navy transition-all"
              onClick={(e) => { e.stopPropagation(); step(1); }}
              aria-label="Next image"
            >
              <ChevronRight size={20} />
            </button>

            <motion.div
              key={current.url}
              initial={{ opacity: 0, scale: 0.97 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.3 }}
              className="max-w-5xl w-full"
              onClick={(e) => e.stopPropagation()}
            >
              <img src={current.url} alt={current.title} className="w-full max-h-[80vh] object-contain rounded-lg" />
              <div className="mt-4 text-center">
                <span className="text-emerald-light text-xs font-bold uppercase tracking-widest">{current.category}</span>
                <h3 className="text-white font-heading font-medium text-xl mt-1">{current.title}</h3>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

import { useEffect, useRef, useState, type MouseEvent, type FormEvent } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { motion, AnimatePresence } from "motion/react";
import { Link } from "react-router-dom";
import { ArrowRight, BadgeCheck, ChevronLeft, ChevronRight, ShieldCheck, Award, Truck, PackageCheck, Headphones, Layers, Star, CheckCircle2 } from "lucide-react";
import AnimatedCounter from "../components/AnimatedCounter";
import { brands } from "../data/brands";
import { products } from "../data/products";
import LogoGrid from "../components/LogoGrid";
import VerificationReadout from "../components/VerificationReadout";
import ScrambleText from "../components/ScrambleText";
import BottleIcon from "../components/BottleIcon";
import { api } from "../lib/api";

gsap.registerPlugin(ScrollTrigger);

const HERO_SLIDES = [
  {
    image: "/images/lab_analytical.jpeg",
    caption: "Analytical bench, in-house QC",
    line1: "Every batch,",
    line2: "verified.",
    body: "Tested under controlled conditions before it ever leaves our facility.",
  },
  {
    image: "/images/industry_pharma.jpeg",
    caption: "Pharma formulation support",
    line1: "Trusted by",
    line2: "pharma.",
    body: "Supplying GMP-aligned formulation and QC labs across the country.",
  },
  {
    image: "/images/stock_thermofisher.jpeg",
    caption: "Batch & expiry traceability",
    line1: "Certified,",
    line2: "always.",
    body: "ISO 9001:2015 systems and full batch traceability, standard on every order.",
  },
  {
    image: "/images/gallery_7.jpeg",
    caption: "Precision reagent handling",
    line1: "Precision",
    line2: "handling.",
    body: "From reagent prep to dispatch, accuracy is the only acceptable grade.",
  },
];

const STOCK_SLIDES = [
  { image: "/images/stock_thermofisher.jpeg", brand: "ThermoFisher", caption: "Reagents in stock, ready to dispatch" },
  { image: "/images/stock_merck.jpeg", brand: "Merck", caption: "Sealed cartons, batch-verified" },
  { image: "/images/stock_schott.jpeg", brand: "Schott", caption: "Glassware, warehoused locally" },
  { image: "/images/stock_borosil.jpeg", brand: "Borosil", caption: "Bulk inventory, pan-India delivery" },
  { image: "/images/stock_general.jpeg", brand: "Gayatri Enterprises", caption: "Ready to dispatch, pan-India" },
];

const REVIEW_MARQUEE_ROW_1 = [
  { quote: "Five years in, not a single batch has failed our incoming QC. Rare for a vendor at this scale.", name: "Dr. Ramesh Iyer", role: "Lab Director, ApexDiagnostics", rating: 5 },
  { quote: "CoA documentation is instant and audit-ready — saves our compliance team hours every quarter.", name: "Priya Nair", role: "QA Head, Veridian Pharma", rating: 5 },
  { quote: "48-hour dispatch on bulk orders, every single time. Our production line has never waited on them.", name: "Sanjay Mehta", role: "Procurement Manager, Apex Labs", rating: 5 },
  { quote: "One vendor for solvents, acids, and glassware. Cuts our supplier list down to almost nothing.", name: "Dr. Kavita Rao", role: "Senior Researcher, NIC", rating: 4 },
];

const REVIEW_MARQUEE_ROW_2 = [
  { quote: "Switched from three regional suppliers to just Gayatri — pricing and consistency both improved.", name: "Anita Deshmukh", role: "Purchase Head, BioCore Labs", rating: 5 },
  { quote: "Their technical team actually understands formulation chemistry. Queries get real answers, fast.", name: "Vikram Shah", role: "Formulation Lead, Solace Pharma", rating: 5 },
  { quote: "ISO documentation, batch traceability, clean packaging — every box ships audit-ready.", name: "Dr. Neha Kapoor", role: "Compliance Officer, MedAssure", rating: 4 },
  { quote: "Best turnaround we've had on AR-grade acids in this region, hands down.", name: "Rohit Bansal", role: "Plant Manager, ChemWorks Industries", rating: 5 },
];

function ReviewCard({ review }: { review: { quote: string; name: string; role: string; rating: number } }) {
  return (
    <div className="w-[380px] shrink-0 mx-3 bg-white/[0.04] border border-white/10 rounded-2xl p-7">
      <div className="flex gap-1 mb-4 text-emerald-light">
        {Array.from({ length: 5 }).map((_, i) => (
          <Star key={i} size={13} className={i < review.rating ? "fill-emerald-light" : "fill-transparent text-white/20"} />
        ))}
      </div>
      <p className="text-white/80 text-sm leading-relaxed mb-6">&ldquo;{review.quote}&rdquo;</p>
      <p className="text-white text-sm font-semibold">{review.name}</p>
      <p className="text-white/50 text-xs">{review.role}</p>
    </div>
  );
}

function ReviewForm() {
  const [open, setOpen] = useState(false);
  const [sent, setSent] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [rating, setRating] = useState(5);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSubmitting(true);
    const form = new FormData(e.currentTarget);
    try {
      await api.post("/api/reviews", {
        name: form.get("name"),
        designation: form.get("role") || null,
        rating,
        body: form.get("quote"),
      });
      setSent(true);
    } finally {
      setSubmitting(false);
    }
  }

  if (!open) {
    return (
      <button
        onClick={() => setOpen(true)}
        className="text-sm font-medium text-emerald-light border border-emerald-light/30 rounded-full px-6 py-3 hover:bg-emerald-light hover:text-navy transition-colors duration-300 shrink-0"
      >
        Share Your Experience
      </button>
    );
  }

  if (sent) {
    return (
      <div className="flex items-center gap-3 text-sm text-white/80 border border-white/10 rounded-2xl px-6 py-4 max-w-md">
        <CheckCircle2 className="w-5 h-5 text-emerald-light shrink-0" />
        Thank you — your review has been submitted and will appear here once approved.
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="bg-white/[0.04] border border-white/10 rounded-2xl p-6 space-y-4 max-w-md w-full">
      <div className="flex gap-1">
        {Array.from({ length: 5 }).map((_, i) => (
          <button key={i} type="button" onClick={() => setRating(i + 1)} aria-label={`Rate ${i + 1} out of 5`}>
            <Star size={18} className={i < rating ? "fill-emerald-light text-emerald-light" : "fill-transparent text-white/25"} />
          </button>
        ))}
      </div>
      <input name="name" required type="text" placeholder="Your name" className="w-full bg-transparent border-b border-white/15 py-2 text-sm text-white placeholder:text-white/40 focus:outline-none focus:border-emerald-light" />
      <input name="role" type="text" placeholder="Company / Role" className="w-full bg-transparent border-b border-white/15 py-2 text-sm text-white placeholder:text-white/40 focus:outline-none focus:border-emerald-light" />
      <textarea name="quote" required rows={3} placeholder="Tell us about your experience..." className="w-full bg-transparent border-b border-white/15 py-2 text-sm text-white placeholder:text-white/40 focus:outline-none focus:border-emerald-light resize-none" />
      <button type="submit" disabled={submitting} className="bg-emerald-light text-navy text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-full hover:bg-white transition-colors disabled:opacity-60">
        {submitting ? "Submitting…" : "Submit Review"}
      </button>
    </form>
  );
}

export default function Home() {
  const containerRef = useRef<HTMLDivElement>(null);
  const [activeSlide, setActiveSlide] = useState(0);
  const [stockSlide, setStockSlide] = useState(0);
  const [ripples, setRipples] = useState<{ id: number; x: number; y: number }[]>([]);
  const rippleId = useRef(0);

  function handleHeroClick(e: MouseEvent<HTMLElement>) {
    const target = e.target as HTMLElement;
    if (target.closest("button, a")) return; // don't ripple on actual controls
    const rect = e.currentTarget.getBoundingClientRect();
    const id = rippleId.current++;
    setRipples((prev) => [...prev, { id, x: e.clientX - rect.left, y: e.clientY - rect.top }]);
    setTimeout(() => setRipples((prev) => prev.filter((r) => r.id !== id)), 1200);
  }

  useEffect(() => {
    const timer = setInterval(() => {
      setActiveSlide((i) => (i + 1) % HERO_SLIDES.length);
    }, 4500);
    return () => clearInterval(timer);
  }, []);

  useEffect(() => {
    const timer = setInterval(() => {
      setStockSlide((i) => (i + 1) % STOCK_SLIDES.length);
    }, 3200);
    return () => clearInterval(timer);
  }, []);

  useEffect(() => {
    const ctx = gsap.context(() => {
      // Massive Text Scrub
      gsap.fromTo(".scrub-text span", 
        { opacity: 0.1 },
        {
          opacity: 1,
          stagger: 0.1,
          scrollTrigger: {
            trigger: ".scrub-text",
            start: "top 95%",
            end: "top 25%",
            scrub: true,
          }
        }
      );

      // Pinned Promise Section with Image Wipes — desktop only. The image
      // panel is hidden below md, so pinning/scrubbing on mobile would scroll-jack
      // the user through ~3000px with no visual payoff. matchMedia keeps the
      // mobile layout as a plain, normally-scrolling stack instead.
      ScrollTrigger.matchMedia({
        "(min-width: 768px)": function () {
          gsap.set([".promise-img-2", ".promise-img-3"], {
            clipPath: "polygon(0% 100%, 100% 100%, 100% 100%, 0% 100%)",
          });

          const promiseTl = gsap.timeline({
            scrollTrigger: {
              trigger: ".promise-wrapper",
              start: "top top",
              end: "+=3000",
              scrub: 1,
              pin: true,
            }
          });

          // Panel 1 -> 2
          promiseTl.to(".promise-text-1", { opacity: 0, y: -20, duration: 1 })
                   .to(".promise-img-2", { clipPath: "polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)", ease: "none", duration: 2 }, "<")
                   .fromTo(".promise-text-2", { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 1 }, "-=1");

          // Panel 2 -> 3
          promiseTl.to(".promise-text-2", { opacity: 0, y: -20, duration: 1 })
                   .to(".promise-img-3", { clipPath: "polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)", ease: "none", duration: 2 }, "<")
                   .fromTo(".promise-text-3", { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 1 }, "-=1");
        },
      });

      // Marquee
      gsap.to(".marquee-inner", {
        xPercent: -50,
        ease: "none",
        scrollTrigger: {
          trigger: ".marquee-section",
          start: "top bottom",
          end: "bottom top",
          scrub: 0.5,
        }
      });

      // Image Reveal
      gsap.to(".image-reveal-wrapper", {
        clipPath: "polygon(0 0, 100% 0, 100% 100%, 0 100%)",
        duration: 1.5,
        ease: "power4.out",
        scrollTrigger: {
          trigger: ".image-reveal-wrapper",
          start: "top 80%",
        }
      });

      // Why Choose Us — alternating slide-in, plays once, never reverses/loops
      gsap.utils.toArray<HTMLElement>(".why-item").forEach((item, i) => {
        gsap.fromTo(
          item,
          { x: i % 2 === 0 ? -80 : 80, opacity: 0 },
          {
            x: 0,
            opacity: 1,
            duration: 0.8,
            ease: "power3.out",
            scrollTrigger: {
              trigger: item,
              start: "top 88%",
              toggleActions: "play none none none",
            },
          }
        );
      });

      // Catalog rows — staggered fade/slide-in as each row enters view
      gsap.fromTo(".catalog-row",
        { opacity: 0, y: 40 },
        {
          opacity: 1,
          y: 0,
          duration: 0.8,
          ease: "power3.out",
          stagger: 0.15,
          scrollTrigger: {
            trigger: ".catalog-list",
            start: "top 80%",
          }
        }
      );

    }, containerRef);

    return () => ctx.revert();
  }, []);

  return (
    <div ref={containerRef} className="relative bg-white text-navy selection:bg-emerald/20 selection:text-navy">

      {/* 1. HERO - The Lightbox */}
      <section
        onClick={handleHeroClick}
        className="hero-section relative min-h-screen w-full overflow-hidden bg-navy flex items-center pt-24 pb-10 cursor-default"
      >
        {/* Ambient drifting gradient — alive even with no interaction */}
        <motion.div
          className="absolute inset-0 pointer-events-none motion-reduce:hidden"
          animate={{
            background: [
              "radial-gradient(900px circle at 15% 20%, rgba(27,122,82,0.10), transparent 55%)",
              "radial-gradient(900px circle at 85% 75%, rgba(27,122,82,0.10), transparent 55%)",
              "radial-gradient(900px circle at 25% 85%, rgba(27,122,82,0.10), transparent 55%)",
              "radial-gradient(900px circle at 15% 20%, rgba(27,122,82,0.10), transparent 55%)",
            ],
          }}
          transition={{ duration: 18, repeat: Infinity, ease: "easeInOut" }}
        />

        {/* Click-reactive glow ripples */}
        {ripples.map((r) => (
          <motion.div
            key={r.id}
            className="absolute rounded-full pointer-events-none"
            style={{
              left: r.x,
              top: r.y,
              width: 0,
              height: 0,
              background: "radial-gradient(circle, rgba(47,163,116,0.22), transparent 70%)",
              translateX: "-50%",
              translateY: "-50%",
            }}
            initial={{ width: 0, height: 0, opacity: 0.7 }}
            animate={{ width: 500, height: 500, opacity: 0 }}
            transition={{ duration: 1.1, ease: "easeOut" }}
          />
        ))}

        <div
          className="absolute inset-0 opacity-[0.05] pointer-events-none"
          style={{ backgroundImage: "linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px)", backgroundSize: "44px 44px" }}
        />

        {/* Faint molecular/hexagonal lattice, slowly rotating behind everything */}
        <div
          className="absolute -inset-[20%] opacity-[0.07] pointer-events-none animate-orbit-spin motion-reduce:hidden"
          style={{
            backgroundImage: "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='100' viewBox='0 0 56 100'%3E%3Cpath d='M28 0L56 16.5V49.5L28 66L0 49.5V16.5Z' fill='none' stroke='white' stroke-width='1'/%3E%3C/svg%3E\")",
            backgroundRepeat: "repeat",
          }}
        />

        {/* Orbital motif filling the otherwise-empty space behind the copy */}
        <svg viewBox="0 0 500 500" className="absolute -left-40 bottom-0 w-[620px] h-[620px] text-white/[0.05] pointer-events-none hidden md:block animate-orbit-spin">
          <ellipse cx="250" cy="250" rx="230" ry="110" fill="none" stroke="currentColor" strokeWidth="2" />
          <ellipse cx="250" cy="250" rx="230" ry="110" fill="none" stroke="currentColor" strokeWidth="2" transform="rotate(60 250 250)" />
          <ellipse cx="250" cy="250" rx="230" ry="110" fill="none" stroke="currentColor" strokeWidth="2" transform="rotate(120 250 250)" />
          <circle cx="250" cy="250" r="12" fill="currentColor" />
        </svg>

        {/* One-time full-bleed scan band on load — a visible glowing wash sweeps down, the page itself gets verified */}
        <motion.div
          aria-hidden
          className="absolute left-0 right-0 pointer-events-none z-20"
          style={{
            height: 220,
            background: "linear-gradient(to bottom, transparent, rgba(120,255,190,0.22) 60%, rgba(180,255,220,0.95) 96%, rgba(180,255,220,0.95) 100%)",
            boxShadow: "0 6px 60px 10px rgba(120,255,190,0.45)",
          }}
          initial={{ top: -220, opacity: 0 }}
          animate={{ top: ["-220px", "-220px", "calc(100% + 0px)", "calc(100% + 0px)"], opacity: [0, 1, 1, 0] }}
          transition={{ duration: 1.7, times: [0, 0.04, 0.82, 1], ease: "easeInOut" }}
        />

        <div className="relative z-10 max-w-[100rem] mx-auto pl-6 md:pl-12 pr-0 md:pr-12 w-full grid lg:grid-cols-[minmax(420px,0.56fr)_1.44fr] gap-8 items-center">
          {/* Left: thesis, shuffles with the image */}
          <div>
            <VerificationReadout />

            <h1 className="relative text-5xl md:text-6xl lg:text-7xl font-heading font-medium tracking-tight leading-[1.02] text-white min-h-[2.2em] md:min-h-[2.1em] lg:min-h-[3.2em]">
              <span className="absolute inset-0">
                <ScrambleText text={HERO_SLIDES[activeSlide].line1} className="block" />
                <ScrambleText text={HERO_SLIDES[activeSlide].line2} className="block text-emerald-light" />
              </span>
            </h1>

            <div className="relative mt-6 min-h-[3.5em] max-w-lg">
              <AnimatePresence mode="wait">
                <motion.p
                  key={activeSlide}
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  transition={{ duration: 0.3 }}
                  className="absolute inset-0 text-lg md:text-xl text-white/70 leading-relaxed font-light"
                >
                  {HERO_SLIDES[activeSlide].body}
                </motion.p>
              </AnimatePresence>
            </div>

            <div className="mt-6 flex flex-wrap items-center gap-4">
              <Link to="/products" className="hero-cta-pulse bg-emerald text-white px-8 py-4 rounded-full font-medium text-sm uppercase tracking-widest hover:bg-emerald-deep transition-all duration-300 flex items-center group">
                Explore Catalog
                <ArrowRight className="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </Link>
              <Link to="/online-order" className="border border-white/25 text-white px-8 py-4 rounded-full font-medium text-sm uppercase tracking-widest hover:bg-white hover:text-navy transition-all duration-300">
                Place an Order
              </Link>
            </div>
          </div>

          {/* Right: the lightbox */}
          <div className="relative mx-auto w-full max-w-none">
            <div className="relative w-full aspect-[4/3] lg:aspect-[16/10.6] max-h-[68vh] rounded-3xl overflow-hidden shadow-[0_40px_80px_-20px_rgba(0,0,0,0.5)] ring-1 ring-white/10">
              <AnimatePresence>
                <motion.img
                  key={activeSlide}
                  src={HERO_SLIDES[activeSlide].image}
                  alt={HERO_SLIDES[activeSlide].caption}
                  initial={{ opacity: 0, scale: 1.08 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0 }}
                  transition={{ duration: 1, ease: "easeOut" }}
                  className="absolute inset-0 w-full h-full object-cover"
                />
              </AnimatePresence>
              <div className="absolute inset-0 bg-gradient-to-t from-navy/30 via-transparent to-transparent" />
            </div>

            {/* Arrow navigation */}
            <div className="flex justify-center items-center gap-5 mt-6">
              <button
                onClick={() => setActiveSlide((i) => (i - 1 + HERO_SLIDES.length) % HERO_SLIDES.length)}
                aria-label="Previous slide"
                className="w-10 h-10 rounded-full border border-white/25 flex items-center justify-center text-white hover:bg-emerald hover:border-emerald transition-all duration-300"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>

              <div className="flex gap-2">
                {HERO_SLIDES.map((_, i) => (
                  <button
                    key={i}
                    onClick={() => setActiveSlide(i)}
                    aria-label={`Show slide ${i + 1}`}
                    className={`h-1.5 rounded-full transition-all duration-300 ${
                      activeSlide === i ? "w-6 bg-emerald-light" : "w-1.5 bg-white/25 hover:bg-white/50"
                    }`}
                  />
                ))}
              </div>

              <button
                onClick={() => setActiveSlide((i) => (i + 1) % HERO_SLIDES.length)}
                aria-label="Next slide"
                className="w-10 h-10 rounded-full border border-white/25 flex items-center justify-center text-white hover:bg-emerald hover:border-emerald transition-all duration-300"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* 2. OVERSIZED SCRUB TEXT */}
      <section className="scrub-section relative py-32 md:py-48 px-6 md:px-12 bg-white overflow-hidden">
        <div className="max-w-6xl mx-auto relative z-10">
          {/* Real logo mark, animating behind the copy — centered on the text block */}
          <span className="absolute inset-0 m-auto w-[110%] aspect-square pointer-events-none opacity-[0.22] motion-reduce:hidden">
            <video
              src="/videos/logo-rotating.mp4"
              autoPlay
              loop
              muted
              playsInline
              className="absolute inset-0 w-full h-full object-contain"
            />
          </span>

          <h2 className="scrub-text text-4xl md:text-6xl lg:text-7xl font-heading font-medium leading-[1.1] text-navy tracking-tight">
            {"We believe that in science, the margin for error is exactly zero. That is why every parameter, every batch, and every molecular structure we warehouse undergoes rigorous verification.".split(" ").map((word, i) => (
              <span key={i} className="inline-block mr-3 md:mr-4 xl:mr-5 mb-2">{word}</span>
            ))}
          </h2>
          <div className="mt-24 thin-border-t pt-8 flex justify-between items-center w-full max-w-sm">
             <span className="text-xs font-semibold uppercase tracking-widest text-slate">Est. 1998</span>
             <span className="text-xs font-semibold uppercase tracking-widest text-emerald">ISO 9001:2015</span>
          </div>
        </div>
      </section>

      {/* 3. THE PROMISE - PINNED SECTION */}
      <section className="promise-wrapper md:h-screen bg-navy text-white relative">
        {/* Desktop: pinned scroll-jacked panel + image-wipe transition */}
        <div className="hidden md:flex absolute inset-0">
          {/* Left: Text Content */}
          <div className="w-full md:w-1/2 flex items-center p-6 md:p-24 relative z-20">

             {/* Panel 1 */}
             <div className="promise-text-1 absolute max-w-lg pr-8">
                <span className="text-emerald-light font-mono text-base md:text-lg uppercase tracking-widest mb-6 block">01 / Purity</span>
                <h3 className="text-5xl md:text-6xl font-heading font-medium tracking-tight mb-6 text-white">Analytical<br/>Grade AR/CR.</h3>
                <p className="text-white/70 text-lg leading-relaxed">Uncomprimising formulation standards. Our solvent and acid lines are triple-distilled, trace-metal certified, and bottled under strict inert atmospheres.</p>
             </div>

             {/* Panel 2 */}
             <div className="promise-text-2 absolute max-w-lg pr-8 opacity-0 translate-y-8">
                <span className="text-emerald-light font-mono text-base md:text-lg uppercase tracking-widest mb-6 block">02 / Traceability</span>
                <h3 className="text-5xl md:text-6xl font-heading font-medium tracking-tight mb-6 text-white">End-to-End<br/>Audit Trails.</h3>
                <p className="text-white/70 text-lg leading-relaxed">Every bottle carries a distinct lot history. Instant access to Certificates of Analysis (CoA) and Material Safety Data Sheets (MSDS) via our digital portal.</p>
             </div>

             {/* Panel 3 */}
             <div className="promise-text-3 absolute max-w-lg pr-8 opacity-0 translate-y-8">
                <span className="text-emerald-light font-mono text-base md:text-lg uppercase tracking-widest mb-6 block">03 / Infrastructure</span>
                <h3 className="text-5xl md:text-6xl font-heading font-medium tracking-tight mb-6 text-white">Controlled<br/>Warehousing.</h3>
                <p className="text-white/70 text-lg leading-relaxed">Temperature-mapped, structurally segregated holding zones ensure that sensitive indicators and biochemical reagents remain perfectly stable until delivery.</p>
             </div>
          </div>

          {/* Right: Images */}
          <div className="w-1/2 h-full relative overflow-hidden">
             <div className="promise-img-1 absolute inset-0 z-10 isolate">
                <img src="/images/gallery_5.jpeg" className="w-full h-full object-cover mix-blend-luminosity opacity-80" alt="Lab 1" />
                <div className="absolute inset-0 bg-navy/20"></div>
             </div>

             <div className="promise-img-2 absolute inset-0 z-20 isolate">
                <img src="/images/stock_schott.jpeg" className="w-full h-full object-cover mix-blend-luminosity opacity-80" alt="Laboratory glassware shelving" />
                <div className="absolute inset-0 bg-emerald/10 mix-blend-color"></div>
             </div>

             <div className="promise-img-3 absolute inset-0 z-30 isolate">
                <img src="/images/gallery_6.jpeg" className="w-full h-full object-cover mix-blend-luminosity opacity-80" alt="Lab 3" />
                <div className="absolute inset-0 bg-navy/30"></div>
             </div>
          </div>
        </div>

        {/* Mobile: simple stacked panels, normal scroll, no pin/scroll-jack — there's no
            room for the image-wipe payoff on a narrow screen, so don't make users sit
            through a long pinned scroll for nothing. */}
        <div className="md:hidden">
          {[
            { tag: "01 / Purity", title: "Analytical Grade AR/CR.", body: "Uncomprimising formulation standards. Our solvent and acid lines are triple-distilled, trace-metal certified, and bottled under strict inert atmospheres.", img: "/images/gallery_5.jpeg" },
            { tag: "02 / Traceability", title: "End-to-End Audit Trails.", body: "Every bottle carries a distinct lot history. Instant access to Certificates of Analysis (CoA) and Material Safety Data Sheets (MSDS) via our digital portal.", img: "/images/stock_schott.jpeg" },
            { tag: "03 / Infrastructure", title: "Controlled Warehousing.", body: "Temperature-mapped, structurally segregated holding zones ensure that sensitive indicators and biochemical reagents remain perfectly stable until delivery.", img: "/images/gallery_6.jpeg" },
          ].map((panel) => (
            <div key={panel.tag} className="px-6 py-16 thin-border-b border-white/10">
              <div className="aspect-[4/3] rounded-2xl overflow-hidden mb-8 relative">
                <img src={panel.img} className="w-full h-full object-cover mix-blend-luminosity opacity-80" alt={panel.title} />
                <div className="absolute inset-0 bg-navy/20"></div>
              </div>
              <span className="text-emerald-light font-mono text-sm uppercase tracking-widest mb-4 block">{panel.tag}</span>
              <h3 className="text-3xl font-heading font-medium tracking-tight mb-4 text-white">{panel.title}</h3>
              <p className="text-white/70 leading-relaxed">{panel.body}</p>
            </div>
          ))}
        </div>
      </section>

      {/* 4. MARQUEE */}
      <section className="marquee-section py-8 md:py-10 bg-emerald text-navy overflow-hidden">
        <div className="marquee-inner flex whitespace-nowrap w-[200vw]">
           <h2 className="text-[7vw] font-heading font-medium tracking-tighter leading-none uppercase">
             PRECISION &nbsp;—&nbsp; PURITY &nbsp;—&nbsp; INNOVATION &nbsp;—&nbsp; PRECISION &nbsp;—&nbsp; PURITY &nbsp;—&nbsp; INNOVATION &nbsp;—&nbsp;
           </h2>
        </div>
      </section>

      {/* 4b. BRAND LOGO GRID */}
      <section className="py-20 bg-white border-b border-border overflow-hidden">
        <div className="max-w-[90rem] mx-auto px-6 md:px-12">
          <p className="text-center text-sm md:text-base font-mono text-slate uppercase tracking-widest mb-10">
            Authorised distributor for
          </p>
          <LogoGrid items={brands} />
        </div>
      </section>

      {/* 5. EDITORIAL PRODUCT PREVIEW */}
      <section className="py-32 bg-soft-bg relative">
        <div className="max-w-[90rem] mx-auto px-6 md:px-12">
          
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-24 gap-8">
             <h2 className="text-4xl md:text-6xl font-heading font-medium tracking-tight text-navy">
               The Catalog.
             </h2>
             <Link to="/products" className="flex items-center gap-2 text-sm font-semibold uppercase tracking-widest text-emerald hover:text-navy transition-colors group">
               View Full Index
               <ArrowRight size={16} className="group-hover:translate-x-2 transition-transform" />
             </Link>
          </div>

          <div className="catalog-list grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
             {[1, 2, 5, 3].map((id) => {
                const prod = products.find((p) => p.id === id)!;
                return (
                  <Link
                     to="/online-order"
                     key={prod.id}
                     className="catalog-row group bg-white rounded-2xl border border-border overflow-hidden flex flex-col justify-between hover:shadow-lg hover:border-emerald/30 transition-all duration-500"
                  >
                     <div className="h-40 overflow-hidden bg-soft-bg">
                        <img
                           src={prod.image}
                           alt={prod.name}
                           className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                        />
                     </div>

                     <div className="p-8 flex flex-col justify-between flex-1">
                        <div>
                           <span className="font-mono text-xs text-slate-400 uppercase tracking-widest">{prod.cas}</span>
                           <h3 className="text-2xl font-heading font-medium text-navy mt-3 mb-2 group-hover:text-emerald transition-colors duration-500">{prod.name}</h3>
                           <p className="text-sm text-slate">{prod.grade}</p>
                        </div>

                        <div className="mt-8 flex items-end justify-between">
                           <div className="flex flex-wrap gap-3">
                              {prod.packSizes.map((size) => (
                                 <div key={size} className="flex flex-col items-center gap-1">
                                    <BottleIcon size={size} className="text-emerald-deep" />
                                    <span className="text-[10px] font-semibold text-emerald-deep whitespace-nowrap">{size}</span>
                                 </div>
                              ))}
                           </div>
                           <div className="w-10 h-10 rounded-full border border-border flex items-center justify-center text-navy group-hover:bg-navy group-hover:text-white transition-all shrink-0">
                              <ArrowRight size={18} />
                           </div>
                        </div>
                     </div>
                  </Link>
                );
             })}
          </div>

        </div>
      </section>

      {/* 5b. REVIEWS MARQUEE */}
      <section className="py-28 bg-navy relative overflow-hidden">
        <div className="max-w-[90rem] mx-auto px-6 md:px-12 mb-16 flex flex-col md:flex-row md:items-end md:justify-between gap-8">
          <div>
            <div className="flex items-center gap-4 text-xs font-mono text-emerald-light uppercase tracking-widest mb-6">
              <div className="w-8 h-[1px] bg-emerald-light"></div>
              <span>Client Voices</span>
            </div>
            <h2 className="text-4xl md:text-5xl font-heading font-medium text-white tracking-tight max-w-xl">
              Trusted by labs that can't afford a bad batch.
            </h2>
          </div>
          <ReviewForm />
        </div>

        <div className="space-y-6">
          <div
            className="relative w-full overflow-hidden"
            style={{
              maskImage: "linear-gradient(to right, transparent, black 8%, black 92%, transparent)",
              WebkitMaskImage: "linear-gradient(to right, transparent, black 8%, black 92%, transparent)",
            }}
          >
            <div className="logo-marquee-track flex w-max hover:[animation-play-state:paused]" style={{ animationDuration: "38s" }}>
              {[...REVIEW_MARQUEE_ROW_1, ...REVIEW_MARQUEE_ROW_1].map((r, i) => (
                <ReviewCard key={i} review={r} />
              ))}
            </div>
          </div>

          <div
            className="relative w-full overflow-hidden"
            style={{
              maskImage: "linear-gradient(to right, transparent, black 8%, black 92%, transparent)",
              WebkitMaskImage: "linear-gradient(to right, transparent, black 8%, black 92%, transparent)",
            }}
          >
            <div className="logo-marquee-track flex w-max hover:[animation-play-state:paused]" style={{ animationDuration: "34s", animationDirection: "reverse" }}>
              {[...REVIEW_MARQUEE_ROW_2, ...REVIEW_MARQUEE_ROW_2].map((r, i) => (
                <ReviewCard key={i} review={r} />
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* 6. AWWWARDS STYLE IMAGE GRACE / FOOTER PRE-CTA */}
      <section className="py-32 bg-white relative overflow-hidden">
         <div className="max-w-[90rem] mx-auto px-6 md:px-12 relative z-10 flex flex-col md:flex-row items-center gap-16 md:gap-32">
            <div className="w-full md:w-1/2 aspect-[3/4] max-h-[65vh] relative overflow-hidden bg-soft-bg border border-border rounded-3xl will-change-transform image-reveal-wrapper group">
               <AnimatePresence mode="wait">
                 <motion.div
                   key={stockSlide}
                   initial={{ opacity: 0, scale: 1.06 }}
                   animate={{ opacity: 1, scale: 1 }}
                   exit={{ opacity: 0 }}
                   transition={{ duration: 0.7, ease: "easeOut" }}
                   className="absolute inset-0"
                 >
                   <img
                     src={STOCK_SLIDES[stockSlide].image}
                     alt={STOCK_SLIDES[stockSlide].caption}
                     className="w-full h-full object-cover"
                   />
                   <div className="absolute inset-0 bg-gradient-to-t from-navy/80 via-navy/10 to-transparent" />
                   <div className="absolute bottom-12 left-0 right-0 px-8 md:px-10 text-center">
                     <span className="text-[11px] font-bold text-emerald-light uppercase tracking-widest">{STOCK_SLIDES[stockSlide].brand}</span>
                     <p className="text-white text-lg font-heading font-medium mt-1">{STOCK_SLIDES[stockSlide].caption}</p>
                   </div>
                 </motion.div>
               </AnimatePresence>

               <div className="absolute top-8 left-0 right-0 flex justify-center gap-2 z-10 bg-navy/20 backdrop-blur-sm mx-auto w-fit px-3 py-2 rounded-full">
                 {STOCK_SLIDES.map((_, i) => (
                   <span
                     key={i}
                     className={`h-1.5 rounded-full transition-all duration-300 ${
                       stockSlide === i ? "w-6 bg-emerald" : "w-1.5 bg-white/40"
                     }`}
                   />
                 ))}
               </div>
            </div>
            
            <div className="w-full md:w-1/2">
               <div className="relative inline-flex items-center gap-2 px-5 py-2.5 mb-8 border-2 border-stamp/80 text-stamp text-xs font-mono font-semibold tracking-[0.18em] uppercase -rotate-2 rounded-[2px]">
                  <span className="absolute inset-[3px] border border-stamp/30 rounded-[1px] pointer-events-none" />
                  <BadgeCheck size={14} /> Authorised &middot; Verified
               </div>
               <h2 className="text-4xl md:text-5xl lg:text-6xl font-heading font-medium text-navy tracking-tight mb-8 leading-[1.1]">
                 Global brands, <br/>local reliability.
               </h2>
               <p className="text-xl text-slate leading-relaxed mb-12 max-w-lg">
                 We are the official procurement partners for ThermoFisher, Merck, and Schott. Zero counterfeits, 100% manufacturer warranty.
               </p>
               
               <div className="grid grid-cols-2 gap-8 thin-border-t pt-12">
                  <div>
                    <div className="text-4xl font-mono font-semibold text-navy mb-2 tabular-nums">10k+</div>
                    <div className="text-xs uppercase tracking-widest text-slate font-semibold">SKUs Available</div>
                  </div>
                  <div>
                    <div className="text-4xl font-mono font-semibold text-navy mb-2 tabular-nums">48h</div>
                    <div className="text-xs uppercase tracking-widest text-slate font-semibold">Max Dispatch Time</div>
                  </div>
               </div>
            </div>
         </div>
      </section>

      {/* 6b. WHY CHOOSE US */}
      <section className="py-32 bg-white">
        <div className="max-w-[90rem] mx-auto px-6 md:px-12">
          <div className="max-w-2xl mb-20">
            <span className="text-emerald font-mono text-sm uppercase tracking-widest mb-4 block">Why Gayatri</span>
            <h2 className="text-4xl md:text-5xl lg:text-6xl font-heading font-medium text-navy tracking-tight leading-[1.1]">
              Built for labs that can't afford to wait.
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-14">
            {[
              { icon: ShieldCheck, title: "Authorised Distributor", body: "Genuine, manufacturer-certified stock — zero counterfeits, full warranty on every order." },
              { icon: Award, title: "ISO 9001:2015 Certified", body: "Every batch tested and documented to international quality standards." },
              { icon: Truck, title: "Pan-India Logistics", body: "48-hour dispatch with temperature-controlled handling, nationwide." },
              { icon: PackageCheck, title: "Bulk Order Support", body: "Competitive pricing and dedicated account management for large orders." },
              { icon: Headphones, title: "Technical Expertise", body: "Direct access to our chemists for formulation and compliance queries." },
              { icon: Layers, title: "10,000+ SKUs", body: "One of the widest laboratory reagent catalogs in the region." },
            ].map(({ icon: Icon, title, body }) => (
              <div key={title} className="why-item flex flex-col gap-4">
                <div className="w-12 h-12 rounded-full bg-emerald-light flex items-center justify-center text-emerald">
                  <Icon size={22} strokeWidth={1.75} />
                </div>
                <h3 className="text-xl font-heading font-medium text-navy">{title}</h3>
                <p className="text-slate leading-relaxed">{body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* 7. STATS BAND */}
      <section className="py-24 bg-emerald-light">
        <div className="max-w-[90rem] mx-auto px-6 md:px-12 grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12">
          <AnimatedCounter value={25} suffix="+" label="Years Legacy" />
          <AnimatedCounter value={10000} suffix="+" label="SKUs in Stock" />
          <AnimatedCounter value={1200} suffix="+" label="Active Clients" />
          <div>
            <AnimatedCounter value={25} suffix="L" label="Max Pack Size" />
            <div className="flex items-end gap-2.5 mt-4">
              {["500ml", "2.5L", "5L", "25L"].map((size) => (
                <div key={size} className="flex flex-col items-center gap-1">
                  <BottleIcon size={size} className="text-emerald-deep" />
                  <span className="text-[9px] font-semibold text-emerald-deep/70 whitespace-nowrap">{size}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

    </div>
  );
}

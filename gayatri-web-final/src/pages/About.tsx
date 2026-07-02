import { useEffect, useRef } from "react";
import { motion } from "motion/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { ShieldCheck, Award } from "lucide-react";

gsap.registerPlugin(ScrollTrigger);

const MILESTONES = [
  {
    year: "1998",
    title: "Founded",
    body: "Begins distributing analytical-grade solvents and acids to research labs across the region.",
  },
  {
    year: "2004",
    title: "ISO 9001 Certified",
    body: "A Certificate of Analysis becomes standard documentation on every single batch shipped.",
  },
  {
    year: "2011",
    title: "Catalog Expansion",
    body: "Crosses 5,000 SKUs across solvents, acids, salts, and microscopy stains.",
  },
  {
    year: "2017",
    title: "Climate-Controlled Storage",
    body: "Cold-chain handling introduced for temperature-sensitive reagents and indicators.",
  },
  {
    year: "2023",
    title: "10,000+ SKUs",
    body: "Pan-India 48-hour dispatch network launched alongside the expanded catalog.",
  },
  {
    year: "Today",
    title: "1,200+ Active Clients",
    body: "Trusted by labs, pharma manufacturers, and industrial chemists nationwide.",
  },
];

export default function About() {
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.fromTo(
        ".timeline-fill",
        { scaleY: 0 },
        {
          scaleY: 1,
          ease: "none",
          scrollTrigger: {
            trigger: ".timeline-track",
            start: "top 60%",
            end: "bottom 80%",
            scrub: 0.5,
          },
        }
      );

      gsap.utils.toArray<HTMLElement>(".milestone-item").forEach((item) => {
        gsap.fromTo(
          item,
          { opacity: 0, y: 24 },
          {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: "power3.out",
            scrollTrigger: {
              trigger: item,
              start: "top 80%",
              toggleActions: "play none none none",
            },
          }
        );
      });

      gsap.utils.toArray<HTMLElement>(".value-item").forEach((item, i) => {
        gsap.fromTo(
          item,
          { opacity: 0, y: 30 },
          {
            opacity: 1,
            y: 0,
            duration: 0.7,
            delay: i * 0.12,
            ease: "power3.out",
            scrollTrigger: {
              trigger: ".value-grid",
              start: "top 82%",
              toggleActions: "play none none none",
            },
          }
        );
      });
    }, containerRef);

    return () => ctx.revert();
  }, []);

  return (
    <div ref={containerRef} className="bg-white text-navy min-h-screen pt-32 pb-24">
      <div className="max-w-[90rem] mx-auto px-6 md:px-12">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, ease: "easeOut" }}
          className="max-w-4xl mb-24 md:mb-32"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
             <div className="w-8 h-[1px] bg-emerald"></div>
             <span>OUR LEGACY &middot; EST. 1998</span>
          </div>
          <h1 className="text-5xl md:text-7xl lg:text-[6rem] font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Pioneering<br/>standard supply.
          </h1>
          <p className="text-xl md:text-2xl text-slate leading-relaxed font-light">
            Gayatri Enterprises has been the trusted backbone of rigorous analytical workflows, academic
            laboratories, and mass industrial synthesis for over twenty-five years.
          </p>
        </motion.div>

        <div className="w-full aspect-[21/9] bg-soft-bg mb-24 md:mb-32 overflow-hidden relative rounded-3xl">
           <motion.img
              initial={{ scale: 1.1 }}
              whileInView={{ scale: 1 }}
              transition={{ duration: 1.5, ease: "easeOut" }}
              viewport={{ once: true }}
              src="/images/lab_warehouse.jpeg"
              alt="Laboratory Warehouse"
              className="w-full h-full object-cover mix-blend-multiply opacity-90"
           />
        </div>

        <div className="grid md:grid-cols-2 gap-16 md:gap-32 mb-32 items-start">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
          >
            <h2 className="text-4xl md:text-5xl font-heading font-medium text-navy tracking-tight mb-8">Our legacy <br/>of accuracy.</h2>
            <div className="w-12 h-[1px] bg-emerald mb-8"></div>

            {/* Certification marks — same ink-stamp language used on the homepage */}
            <div className="flex flex-wrap gap-4">
              <div className="relative inline-flex items-center gap-2 px-4 py-2 border-2 border-stamp/80 text-stamp text-xs font-mono font-semibold tracking-[0.15em] uppercase -rotate-2 rounded-[2px]">
                <span className="absolute inset-[3px] border border-stamp/30 rounded-[1px] pointer-events-none" />
                <ShieldCheck size={13} /> ISO 9001:2015
              </div>
              <div className="relative inline-flex items-center gap-2 px-4 py-2 border-2 border-stamp/80 text-stamp text-xs font-mono font-semibold tracking-[0.15em] uppercase rotate-1 rounded-[2px]">
                <span className="absolute inset-[3px] border border-stamp/30 rounded-[1px] pointer-events-none" />
                <Award size={13} /> Authorised Distributor
              </div>
            </div>
          </motion.div>

          <motion.div
             initial={{ opacity: 0, y: 20 }}
             whileInView={{ opacity: 1, y: 0 }}
             viewport={{ once: true }}
             transition={{ duration: 0.8, delay: 0.2 }}
             className="text-lg text-slate leading-relaxed space-y-6"
          >
            <p>
              Founded on the principles of Swiss-like precision and relentless quality control, we operate one of the
              most tightly regulated warehousing models in the B2B chemical sector. Our inventory spans over 12,000 distinct SKUs,
              ensuring that researchers never face supply bottlenecks.
            </p>
            <p>
              We employ automated batch-tracking and climate-controlled storage to guarantee the integrity of sensitive reagents and stains. Every single product that leaves our facility is backed by verified Certificates of Analysis (CoA) and adheres rigidly to international purity parameters.
            </p>
          </motion.div>
        </div>

        {/* Milestone Timeline — a real sequence, so numbering/ordering actually carries information */}
        <div className="thin-border-t pt-24 mb-32">
          <h2 className="text-4xl md:text-5xl font-heading font-medium text-navy tracking-tight mb-20">
            Twenty-five years,<br />one batch at a time.
          </h2>

          <div className="timeline-track relative pl-10 md:pl-16">
            <div className="absolute left-0 md:left-2 top-0 bottom-0 w-[2px] bg-border" />
            <div
              className="timeline-fill absolute left-0 md:left-2 top-0 w-[2px] bg-emerald origin-top"
              style={{ height: "100%", transform: "scaleY(0)" }}
            />

            <div className="space-y-16 md:space-y-20">
              {MILESTONES.map((m) => (
                <div key={m.year} className="milestone-item relative grid md:grid-cols-[140px_1fr] gap-3 md:gap-12">
                  <span className="absolute -left-10 md:-left-16 top-1 w-3 h-3 rounded-full bg-emerald ring-4 ring-emerald-light" />
                  <span className="font-mono text-2xl md:text-3xl font-semibold text-navy tabular-nums">{m.year}</span>
                  <div>
                    <h3 className="text-2xl font-heading font-medium text-navy mb-2">{m.title}</h3>
                    <p className="text-slate text-lg leading-relaxed max-w-xl">{m.body}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Technical Values Section */}
        <div className="thin-border-t pt-24">
          <div className="value-grid grid grid-cols-1 md:grid-cols-3 gap-12">
            {[
               { num: "01", title: "Traceability", desc: "Batch-level tracking for every milligram." },
               { num: "02", title: "Scale", desc: "From 100mg standards to 200L drums." },
               { num: "03", title: "Velocity", desc: "Optimised logistics for rapid deployment." }
            ].map((val) => (
              <div key={val.num} className="value-item group">
                <span className="text-emerald font-mono text-sm mb-6 block">{val.num}.</span>
                <h3 className="text-3xl font-heading font-medium text-navy mb-4">{val.title}</h3>
                <p className="text-slate text-lg">{val.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

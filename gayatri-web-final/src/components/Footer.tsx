import { Link } from "react-router-dom";
import { ArrowRight } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { LogoLockup } from "./Logo";

gsap.registerPlugin(ScrollTrigger);

export default function Footer() {
  const wordmarkRef = useRef<HTMLSpanElement>(null);
  const headingRef = useRef<HTMLHeadingElement>(null);
  const [introPlaying, setIntroPlaying] = useState(false);

  useEffect(() => {
    if (!wordmarkRef.current) return;
    const tween = gsap.fromTo(
      wordmarkRef.current,
      { scale: 1 },
      {
        scale: 1.05,
        ease: "none",
        scrollTrigger: {
          trigger: wordmarkRef.current,
          start: "top bottom",
          end: "bottom top",
          scrub: true,
        },
      }
    );
    return () => {
      tween.scrollTrigger?.kill();
      tween.kill();
    };
  }, []);

  useEffect(() => {
    const el = headingRef.current;
    if (!el) return;
    let timeoutId: ReturnType<typeof setTimeout> | undefined;
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        observer.disconnect();
        setIntroPlaying(true);
        timeoutId = setTimeout(() => setIntroPlaying(false), 1700);
      },
      { threshold: 0.3 }
    );
    observer.observe(el);
    return () => {
      observer.disconnect();
      if (timeoutId) clearTimeout(timeoutId);
    };
  }, []);

  return (
    <footer className="bg-navy text-white pt-24 pb-0 overflow-hidden relative">
      <div className="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-emerald/50 to-transparent"></div>
      
      <div className="w-full px-6 md:px-12 mx-auto">
        {/* Massive Footer CTA */}
        <div className="mb-16 md:mb-20 grid lg:grid-cols-[1fr_auto] items-end gap-10">
          <div>
            <h2
              ref={headingRef}
              className={`wave-fill-heading ${introPlaying ? "is-revealing" : ""} text-[12vw] md:text-[10vw] font-heading font-medium leading-[0.85] tracking-tight mb-8 cursor-pointer w-fit`}
            >
              <span className="outline-layer">Let's build<br />it together.</span>
              <span className="fill-layer" aria-hidden="true">Let's build<br />it together.</span>
            </h2>
            <Link to="/contact" className="flex items-center gap-4 group cursor-pointer w-fit">
              <div className="w-12 h-12 rounded-full border border-white/20 flex items-center justify-center group-hover:bg-white group-hover:text-navy transition-all duration-300">
                <ArrowRight size={20} className="group-hover:-rotate-45 transition-transform duration-300" />
              </div>
              <span className="text-lg font-medium tracking-wide uppercase">Start a conversation</span>
            </Link>
          </div>
          <img
            src="/images/footer_cta.jpeg"
            alt="Gayatri Enterprises laboratory"
            className="hidden lg:block w-64 h-80 object-cover rounded-2xl border border-white/10 shadow-2xl"
          />
        </div>

        <div className="flex flex-col lg:flex-row lg:justify-between gap-12 mb-24 md:mb-32 thin-border-t border-white/10 pt-16">
          <div className="max-w-sm">
            <LogoLockup textColor="text-white" subColor="text-slate-400" className="mb-6" />
            <p className="text-slate-400 text-sm leading-relaxed mb-8">
              Pioneering precision supply. Supplying high-purity laboratory chemicals, analytical reagents, and verified standards to institutional researchers worldwide.
            </p>
          </div>

          <div className="flex flex-wrap gap-x-12 md:gap-x-16 gap-y-10">
            <div>
              <h4 className="text-xs uppercase tracking-widest font-semibold mb-6 text-slate-500">Company</h4>
              <ul className="space-y-4 text-sm text-slate-300">
                <li><Link to="/about" className="hover:text-white hover:pl-2 transition-all">About Us</Link></li>
                <li><Link to="/team" className="hover:text-white hover:pl-2 transition-all">Team</Link></li>
                <li><Link to="/gallery" className="hover:text-white hover:pl-2 transition-all">Gallery</Link></li>
                <li><Link to="/blog" className="hover:text-white hover:pl-2 transition-all">Insights</Link></li>
              </ul>
            </div>

            <div>
              <h4 className="text-xs uppercase tracking-widest font-semibold mb-6 text-slate-500">Ordering</h4>
              <ul className="space-y-4 text-sm text-slate-300">
                <li><Link to="/products" className="hover:text-white hover:pl-2 transition-all">Lab Chemicals</Link></li>
                <li><Link to="/brands" className="hover:text-white hover:pl-2 transition-all">Distributors</Link></li>
                <li><Link to="/bulk-order" className="hover:text-white hover:pl-2 transition-all">Bulk Order</Link></li>
                <li><Link to="/online-order" className="hover:text-white hover:pl-2 transition-all">Online Order</Link></li>
              </ul>
            </div>

            <div>
              <h4 className="text-xs uppercase tracking-widest font-semibold mb-6 text-slate-500">Contact</h4>
              <ul className="space-y-4 text-sm text-slate-300">
                <li><Link to="/contact" className="hover:text-white hover:pl-2 transition-all">Get in Touch</Link></li>
              </ul>
            </div>

            <div>
              <h4 className="text-xs uppercase tracking-widest font-semibold mb-6 text-slate-500">Legal</h4>
              <ul className="space-y-4 text-sm text-slate-300">
                <li><Link to="/privacy-policy" className="hover:text-white hover:pl-2 transition-all">Privacy Policy</Link></li>
                <li><Link to="/terms" className="hover:text-white hover:pl-2 transition-all">Terms</Link></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Massive Typographic Statement — outlined wordmark bleeding off the corner, touching the footer's bottom edge */}
      <div className="relative w-full h-[16vw] md:h-[11vw]">
        <span
          ref={wordmarkRef}
          aria-hidden="true"
          className="wave-fill-text absolute -bottom-[1.5vw] -right-[0.5vw] font-heading font-bold uppercase tracking-tighter leading-none text-[26vw] md:text-[19vw] whitespace-nowrap select-none"
        >
          GAYATRI
        </span>
        <p className="absolute bottom-3 left-6 md:left-12 text-[0.65rem] uppercase tracking-widest text-slate-500 font-medium">
          &copy; {new Date().getFullYear()} GAYATRI ENTERPRISES. ALL RIGHTS RESERVED.
        </p>
      </div>
    </footer>
  );
}

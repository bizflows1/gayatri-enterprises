import { Link, useLocation } from "react-router-dom";
import { Menu, X, User } from "lucide-react";
import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "motion/react";
import { LogoLockup } from "./Logo";
import { useAuth } from "../contexts/AuthContext";

export default function Navbar() {
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  const isHome = location.pathname === "/";
  const { user } = useAuth();

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 20);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const links = [
    { name: "Home", path: "/" },
    { name: "Products", path: "/products" },
    { name: "Brands", path: "/brands" },
    { name: "About", path: "/about" },
    { name: "Team", path: "/team" },
    { name: "Gallery", path: "/gallery" },
    { name: "Insights", path: "/blog" },
    { name: "Bulk Order", path: "/bulk-order" },
    { name: "Contact", path: "/contact" },
  ];

  const mobileLinks = [
    ...links,
    { name: "Online Order", path: "/online-order" },
    user ? { name: "My Account", path: "/account" } : { name: "Sign In", path: "/login" },
  ];

  const isLinkActive = (path: string) =>
    path === "/" ? location.pathname === "/" : location.pathname.startsWith(path);

  const isDarkText = scrolled || !isHome;
  const textColor = isDarkText ? 'text-navy' : 'text-white';
  const logoSubColor = isDarkText ? 'text-slate' : 'text-white/80';
  const linkColor = isDarkText ? 'text-navy hover:text-emerald' : 'text-white hover:text-emerald';
  const btnClass = isDarkText 
    ? 'bg-navy text-white hover:bg-emerald hover:text-white' 
    : 'bg-white text-navy hover:bg-emerald hover:text-white';

  return (
    <header className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ease-out ${scrolled ? "nav-glass py-4" : "bg-transparent py-6"}`}>
      <div className="w-full px-6 md:px-12">
        <div className="flex items-center justify-between">
          <LogoLockup 
            textColor={textColor} 
            subColor={logoSubColor} 
            onClick={() => setIsOpen(false)} 
            className="hover:opacity-70 transition-opacity" 
          />

          {/* Desktop Nav */}
          <nav className="hidden lg:flex items-center gap-5 xl:gap-7">
            {links.map((link) => {
              const active = isLinkActive(link.path);
              return (
                <Link
                  key={link.name}
                  to={link.path}
                  className={`text-xs uppercase tracking-[0.1em] font-medium transition-colors relative group whitespace-nowrap ${
                    active ? (isDarkText ? "text-navy" : "text-white") : linkColor
                  }`}
                >
                  {link.name}
                  {active ? (
                    <motion.span
                      layoutId="navbar-underline"
                      className="absolute -bottom-2 left-0 w-full h-[1px] bg-emerald"
                      transition={{ type: "spring", stiffness: 380, damping: 30 }}
                    />
                  ) : (
                    <span className="absolute -bottom-2 left-0 w-full h-[1px] bg-emerald scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100" />
                  )}
                </Link>
              );
            })}
            <Link
              to={user ? "/account" : "/login"}
              className={`flex items-center gap-1.5 text-xs uppercase tracking-[0.1em] font-medium transition-colors whitespace-nowrap ${linkColor}`}
            >
              <User size={14} /> {user ? "My Account" : "Sign In"}
            </Link>
            <Link
              to="/online-order"
              className={`text-xs uppercase tracking-widest px-5 py-3 rounded-full transition-colors duration-300 whitespace-nowrap ${btnClass}`}
            >
              Order Now
            </Link>
          </nav>

          {/* Mobile Menu Toggle */}
          <button className={`lg:hidden focus:outline-none transition-colors duration-300 ${textColor}`} onClick={() => setIsOpen(!isOpen)}>
            {isOpen ? <X size={24} strokeWidth={1.5} /> : <Menu size={24} strokeWidth={1.5} />}
          </button>
        </div>
      </div>

      {/* Mobile Nav */}
      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            className="absolute top-100 left-0 w-full bg-white lg:hidden border-b border-border overflow-hidden max-h-[80vh] overflow-y-auto"
          >
            <div className="flex flex-col px-6 py-8 space-y-6">
              {mobileLinks.map((link) => (
                <Link
                  key={link.name}
                  to={link.path}
                  className="font-heading text-2xl text-navy"
                  onClick={() => setIsOpen(false)}
                >
                  {link.name}
                </Link>
              ))}
              <div className="pt-6 border-t border-border">
                <Link
                  to="/online-order"
                  className="inline-block bg-navy text-white uppercase tracking-widest text-xs px-8 py-4 rounded-full"
                  onClick={() => setIsOpen(false)}
                >
                  Order Now
                </Link>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
}

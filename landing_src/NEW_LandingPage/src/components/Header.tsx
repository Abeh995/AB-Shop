import { AnimatePresence, motion, useScroll } from "framer-motion";
import { ArrowUpLeft, Menu, PackageSearch, Search, ShoppingBag } from "lucide-react";
import { useEffect, useState } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { navItems } from "../data/site";
import { faNum } from "../lib/format";
import { cn } from "../utils/cn";
import { MobileMenu } from "./MobileMenu";

/**
 * Header — clean navigation, blur on scroll, progress bar,
 * and desktop hover preview with dynamic builder configuration.
 */
export function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [hovered, setHovered] = useState<number | null>(null);
  const [menuOpen, setMenuOpen] = useState(false);
  const { cartCount, setCartOpen, openModal } = useShop();
  const { header: headerConfig } = useLandingConfig();
  const { scrollYProgress } = useScroll();
  const logoTitle = headerConfig?.logoTitle || "ای‌بی ساکس";
  const logoSubtitle = headerConfig?.logoSubtitle || "AB SOCKS";
  const rawLinks = headerConfig?.navLinks?.length ? headerConfig.navLinks : navItems;
  const links = rawLinks.filter((item: any) => item.enabled !== false);
  const showSearch = headerConfig?.searchEnabled !== false;
  const showTrack = headerConfig?.trackEnabled !== false;
  const showCart = headerConfig?.cartEnabled !== false;

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const go = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });

  return (
    <>
      <header
        id="header"
        data-lb-section="header"
        className={cn(
          "sticky top-0 z-50 transition-all duration-500",
          scrolled ? "bg-cream-50/85 shadow-[0_10px_30px_-18px_rgba(24,15,9,0.35)] backdrop-blur-xl" : "bg-cream-50/0"
        )}
        onMouseLeave={() => setHovered(null)}
      >
        {/* scroll progress bar */}
        <motion.div
          className="absolute inset-x-0 top-0 h-[3px] origin-right bg-gradient-to-l from-brand-500 via-brand-400 to-pop-400"
          style={{ scaleX: scrollYProgress }}
        />

        <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 px-3 sm:px-6 lg:h-[4.5rem]">
          {/* brand */}
          <button onClick={() => go("home")} className="group flex items-center gap-2.5">
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-coffee-900 text-cream-50 shadow-lg shadow-coffee-900/20 transition-transform duration-300 group-hover:-rotate-6 group-hover:bg-brand-500">
              <svg viewBox="0 0 120 120" className="h-6 w-6">
                <path
                  d="M46 14 H76 V58 C76 70 70 77 60 83 L40 95 C30 101 18 96 14 87 C10 78 14 66 24 60 L46 47 V14 Z"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="9"
                  strokeLinejoin="round"
                />
                <rect x="46" y="30" width="30" height="8" rx="4" fill="#e85d1f" />
              </svg>
            </span>
            <span className="text-start leading-tight">
              <span className="block text-sm font-black text-coffee-900 sm:text-base">{logoTitle}</span>
              <span className="block text-[9px] font-bold tracking-[0.3em] text-brand-600">{logoSubtitle}</span>
            </span>
          </button>

          {/* desktop nav */}
          <nav className="hidden items-center gap-1 lg:flex">
            {links.map((item, i) => (
              <button
                key={item.id}
                onMouseEnter={() => setHovered(i)}
                onFocus={() => setHovered(i)}
                onClick={() => go(item.target || item.id)}
                className={cn(
                  "relative rounded-full px-3.5 py-2 text-[13px] font-bold transition-colors",
                  hovered === i ? "text-brand-600" : "text-coffee-700 hover:text-coffee-950"
                )}
              >
                {item.label}
                {hovered === i && (
                  <motion.span
                    layoutId="nav-pill"
                    className="absolute inset-0 -z-10 rounded-full bg-coffee-900/[0.06]"
                    transition={{ type: "spring", stiffness: 400, damping: 30 }}
                  />
                )}
              </button>
            ))}
          </nav>

          {/* actions */}
          <div className="flex items-center gap-1.5 sm:gap-2">
            {showSearch && (
              <button
                onClick={() => {
                  go("catalog");
                  window.setTimeout(() => document.getElementById("catalog-search")?.focus(), 700);
                }}
                aria-label={headerConfig?.searchLabel || "جستجو"}
                className="hidden h-10 w-10 items-center justify-center rounded-full text-coffee-700 transition-colors hover:bg-coffee-900/5 hover:text-brand-600 sm:flex"
              >
                <Search className="h-5 w-5" />
              </button>
            )}
            {showTrack && (
              <button
                onClick={() => openModal("track")}
                aria-label="پیگیری سفارش"
                className="hidden h-10 w-10 items-center justify-center rounded-full text-coffee-700 transition-colors hover:bg-coffee-900/5 hover:text-brand-600 md:flex"
              >
                <PackageSearch className="h-5 w-5" />
              </button>
            )}
            {showCart && (
              <button
                onClick={() => setCartOpen(true)}
                aria-label="سبد خرید"
                className="relative flex h-10 w-10 items-center justify-center rounded-full text-coffee-800 transition-colors hover:bg-coffee-900/5 hover:text-brand-600"
              >
                <ShoppingBag className="h-5 w-5" />
                <AnimatePresence>
                  {cartCount > 0 && (
                    <motion.span
                      key={cartCount}
                      initial={{ scale: 0 }}
                      animate={{ scale: 1 }}
                      exit={{ scale: 0 }}
                      className="absolute -left-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-black text-white"
                    >
                      {faNum(cartCount)}
                    </motion.span>
                  )}
                </AnimatePresence>
              </button>
            )}
            <button
              onClick={() => setMenuOpen(true)}
              aria-label={headerConfig?.menuLabel || "منو"}
              className="flex h-10 w-10 items-center justify-center rounded-full bg-coffee-900 text-cream-50 transition-transform hover:bg-brand-500 active:scale-95 lg:hidden"
            >
              <Menu className="h-5 w-5" />
            </button>
          </div>
        </div>

        {/* desktop hover "explore" preview */}
        <AnimatePresence>
          {hovered !== null && links[hovered] && (
            <motion.div
              initial={{ opacity: 0, y: -8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.22 }}
              className="absolute inset-x-0 top-full hidden lg:block"
            >
              <div className="mx-auto max-w-7xl px-6">
                <button
                  onClick={() => go(links[hovered].target || links[hovered].id)}
                  className="group ml-auto flex w-fit items-center gap-3 rounded-b-2xl border border-t-0 border-coffee-900/10 bg-cream-50/95 px-5 py-3 text-xs shadow-xl backdrop-blur-xl"
                >
                  <span className="text-coffee-600">{links[hovered].desc}</span>
                  <span className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-white transition-transform group-hover:rotate-45">
                    <ArrowUpLeft className="h-3.5 w-3.5" />
                  </span>
                </button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </header>

      <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
    </>
  );
}

import { AnimatePresence, motion } from "framer-motion";
import { ArrowUpLeft, AtSign, Send, X } from "lucide-react";
import { useEffect } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { navItems } from "../data/site";
import { faNum } from "../lib/format";

/**
 * MobileMenu — MAX's animated hamburger experience:
 * full-screen coffee sheet with staggered oversized links.
 */
export function MobileMenu({ open, onClose }: { open: boolean; onClose: () => void }) {
  const { header } = useLandingConfig();
  useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [open]);

  const go = (id: string) => {
    onClose();
    window.setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" }), 380);
  };

  return (
    <AnimatePresence>
      {open && (
        <motion.div
          className="fixed inset-0 z-[90] flex flex-col bg-coffee-950 text-cream-50 lg:hidden"
          initial={{ clipPath: "circle(0% at 88% 5%)" }}
          animate={{ clipPath: "circle(140% at 88% 5%)" }}
          exit={{ clipPath: "circle(0% at 88% 5%)" }}
          transition={{ duration: 0.65, ease: [0.76, 0, 0.24, 1] }}
        >
          <div className="dot-grid-light pointer-events-none absolute inset-0 opacity-30" />
          <div className="flex h-16 items-center justify-between px-4">
            <span className="text-sm font-black tracking-widest text-brand-400">منوی ای‌بی ساکس</span>
            <button
              onClick={onClose}
              aria-label={header?.menuCloseLabel || "بستن منو"}
              className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 transition-colors hover:bg-brand-500"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          <nav className="flex flex-1 flex-col justify-center gap-1 px-7">
            {[{ id: "home", label: header?.homeLabel || "صفحه اصلی" }, ...navItems, { id: "faq", label: header?.faqLabel || "سوالات متداول" }].map(
              (item, i) => (
                <div key={item.id} className="overflow-hidden">
                  <motion.button
                    initial={{ y: "120%", rotate: 4 }}
                    animate={{ y: 0, rotate: 0 }}
                    transition={{ delay: 0.15 + i * 0.07, duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
                    onClick={() => go(item.id)}
                    className="group flex w-full items-center justify-between border-b border-white/10 py-4 text-start"
                  >
                    <span className="flex items-baseline gap-4">
                      <span className="text-xs font-bold text-brand-500">{faNum(String(i + 1).padStart(2, "0"))}</span>
                      <span className="text-2xl font-black text-cream-50 transition-colors group-hover:text-brand-400 sm:text-3xl">
                        {item.label}
                      </span>
                    </span>
                    <ArrowUpLeft className="h-6 w-6 text-cream-50/30 transition-all group-hover:rotate-45 group-hover:text-brand-400" />
                  </motion.button>
                </div>
              )
            )}
          </nav>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.7 }}
            className="flex items-center justify-between px-7 pb-10"
          >
            <span className="text-xs text-cream-100/50">هر قدم، یه اثر هنریه</span>
            <div className="flex gap-2">
              <span className="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-cream-100/70">
                <AtSign className="h-4.5 w-4.5" />
              </span>
              <span className="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-cream-100/70">
                <Send className="h-4.5 w-4.5" />
              </span>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}

import { motion } from "framer-motion";
import { Dices, House, Menu, Package, ShoppingBag } from "lucide-react";
import { useEffect, useState } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { useShop } from "../context/ShopContext";
import { faNum } from "../lib/format";
import { cn } from "../utils/cn";
import { MobileMenu } from "./MobileMenu";

/**
 * MobileDeck — the "liquid" bottom navigation deck (from mobile_liquid_deck.html):
 * floating frosted glass pill, spring-loaded liquid active indicator and a
 * raised center FAB that launches the lucky wheel.
 */
export function MobileDeck() {
  const [active, setActive] = useState("home");
  const [menuOpen, setMenuOpen] = useState(false);
  const { openModal, cartCount } = useShop();
  const { header } = useLandingConfig();

  const items = [
    { id: "home", label: header?.deckHomeLabel || "خانه", icon: House },
    { id: "catalog", label: header?.deckShopLabel || "فروشگاه", icon: ShoppingBag, badge: cartCount },
    { id: "fab" },
    { id: "pack", label: "باکس‌ساز", icon: Package },
    { id: "menu", label: header?.deckMenuLabel || "منو", icon: Menu },
  ] as const;

  // scroll-spy for the main anchors
  useEffect(() => {
    const ids = ["home", "catalog", "pack", "gallery", "size", "social", "story", "faq"];
    const onScroll = () => {
      const y = window.scrollY + window.innerHeight * 0.35;
      let current = "home";
      for (const id of ids) {
        const el = document.getElementById(id);
        if (el && el.offsetTop <= y) current = id;
      }
      const map: Record<string, string> = { gallery: "catalog", size: "catalog", story: "pack", faq: "menu", social: "pack" };
      setActive(map[current] ?? current);
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const go = (id: string) => {
    if (id === "menu") {
      setMenuOpen(true);
      return;
    }
    setActive(id);
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <>
      <nav className="fixed inset-x-0 bottom-0 z-40 px-3 pb-3 lg:hidden" style={{ paddingBottom: "max(0.75rem, env(safe-area-inset-bottom))" }}>
        <div className="relative mx-auto flex max-w-md items-stretch justify-between rounded-[1.9rem] border border-coffee-900/10 bg-cream-50/80 px-2 py-2 shadow-[0_18px_45px_-14px_rgba(24,15,9,0.45)] backdrop-blur-2xl">
          {items.map((item) => {
            if (item.id === "fab") {
              return (
                <div key="fab" className="relative flex w-16 justify-center">
                  <motion.button
                    whileTap={{ scale: 0.88 }}
                    onClick={() => openModal("wheel")}
                    aria-label={header?.deckWheelLabel || "گردونه شانس"}
                    className="absolute -top-7 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-xl shadow-brand-500/40 ring-4 ring-cream-50"
                  >
                    <span className="animate-ping-soft absolute inset-0 rounded-2xl bg-brand-500" />
                    <Dices className="relative h-6 w-6" />
                  </motion.button>
                  <span className="mt-7 pb-1 text-[10px] font-bold text-brand-600">گردونه</span>
                </div>
              );
            }
            const Icon = item.icon;
            const isActive = active === item.id;
            return (
              <button
                key={item.id}
                onClick={() => go(item.id)}
                className="relative flex w-16 flex-col items-center justify-center gap-0.5 rounded-3xl py-2"
                aria-label={item.label}
              >
                {isActive && (
                  <motion.span
                    layoutId="deck-liquid"
                    className="absolute inset-0 rounded-3xl bg-coffee-900/[0.07] ring-1 ring-brand-500/25"
                    transition={{ type: "spring", stiffness: 380, damping: 30 }}
                  />
                )}
                <span className="relative">
                  <Icon className={cn("h-5 w-5 transition-colors", isActive ? "text-brand-600" : "text-coffee-500")} />
                  {"badge" in item && item.badge ? (
                    <span className="absolute -left-2 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-500 px-1 text-[9px] font-black text-white">
                      {faNum(item.badge)}
                    </span>
                  ) : null}
                </span>
                <span className={cn("relative text-[10px] font-bold transition-colors", isActive ? "text-brand-600" : "text-coffee-500")}>
                  {item.label}
                </span>
              </button>
            );
          })}
        </div>
      </nav>
      <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
    </>
  );
}

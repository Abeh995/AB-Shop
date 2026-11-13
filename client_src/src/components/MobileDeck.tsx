import { motion } from "framer-motion";
import { House, LayoutGrid, Search, ShoppingBag, User } from "lucide-react";
import { useState } from "react";
import { useShop } from "../context/ShopContext";
import { faNum } from "../lib/format";
import { cn } from "../utils/cn";

/**
 * MobileDeck — 5-Hub Liquid Glass Dock navigation for mobile.
 * Implements the exact 5 Hubs from deckmobile_liquid.html:
 * 1- خانه 2- دسته‌بندی‌ها 3- سبد خرید 4- جستجو 5- حساب کاربری
 */
export function MobileDeck() {
  const [active, setActive] = useState("home");
  const { setCartOpen, cartCount } = useShop();

  const items = [
    { id: "home", label: "خانه", icon: House, href: "#home" },
    { id: "categories", label: "دسته‌ها", icon: LayoutGrid, href: "/categories" },
    { id: "cart", label: "سبد خرید", icon: ShoppingBag, badge: cartCount, isCart: true },
    { id: "search", label: "جستجو", icon: Search, href: "/search" },
    { id: "account", label: "حساب", icon: User, href: "/account" },
  ] as const;

  const handleClick = (item: (typeof items)[number]) => {
    setActive(item.id);
    if ("isCart" in item && item.isCart) {
      setCartOpen(true);
      return;
    }
    if (item.id === "home") {
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }
    if (item.href) {
      window.location.href = item.href;
    }
  };

  return (
    <nav
      className="fixed inset-x-0 bottom-0 z-40 px-3 pb-3 lg:hidden"
      style={{ paddingBottom: "max(0.75rem, env(safe-area-inset-bottom))" }}
      aria-label="ناوبری اصلی ۵ هاب"
    >
      <div className="relative mx-auto flex max-w-md items-center justify-between rounded-[2rem] border border-white/70 bg-white/85 px-2 py-1.5 shadow-[0_20px_40px_-10px_rgba(34,21,16,0.18)] backdrop-blur-2xl">
        {items.map((item) => {
          const Icon = item.icon;
          const isActive = active === item.id;
          return (
            <button
              key={item.id}
              onClick={() => handleClick(item)}
              className="relative flex flex-1 flex-col items-center justify-center py-2 transition-colors select-none"
              aria-label={item.label}
            >
              {isActive && (
                <motion.span
                  layoutId="deck-liquid-droplet"
                  className="absolute inset-x-1 inset-y-0.5 rounded-2xl bg-brand-500/15 border border-brand-500/25 shadow-sm"
                  transition={{ type: "spring", stiffness: 420, damping: 28 }}
                />
              )}
              <span className="relative">
                <Icon
                  className={cn(
                    "h-5 w-5 transition-transform duration-200",
                    isActive ? "scale-110 text-brand-600" : "text-coffee-600 hover:text-coffee-900"
                  )}
                  strokeWidth={isActive ? 2.5 : 2}
                />
                {"badge" in item && Boolean(item.badge) && item.badge! > 0 && (
                  <span className="absolute -left-2.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-500 px-1 text-[9px] font-black text-white shadow-md">
                    {faNum(item.badge!)}
                  </span>
                )}
              </span>
              <span
                className={cn(
                  "relative mt-0.5 text-[10px] font-bold transition-colors",
                  isActive ? "font-black text-brand-600" : "text-coffee-600"
                )}
              >
                {item.label}
              </span>
            </button>
          );
        })}
      </div>
    </nav>
  );
}

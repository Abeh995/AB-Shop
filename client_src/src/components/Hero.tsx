import { motion, useMotionValue, useScroll, useSpring, useTransform } from "framer-motion";
import {
  ArrowDown,
  Award,
  Feather,
  Footprints,
  Heart,
  Leaf,
  Package,
  Palette,
  ShieldCheck,
  ShoppingBag,
  Sparkles,
  Truck,
  Zap,
} from "lucide-react";
import { useRef } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { products } from "../data/products";
import { faPrice } from "../lib/format";

const heroProduct = products.find((p) => p.id === 9) || products[0];

const underlinePaths: Record<string, string> = {
  wavy: "M4 10 C 70 2 220 2 296 8",
  brush: "M2 11 C 50 3 150 1 298 9",
  loop: "M4 12 C 90 2 130 14 180 4 C 220 -2 270 8 296 10",
  arc: "M4 12 Q 150 0 296 12",
  straight: "M4 8 L 296 8",
};

const iconMap: Record<string, any> = {
  feather: Feather,
  palette: Palette,
  truck: Truck,
  leaf: Leaf,
  foot: Footprints,
  shield: ShieldCheck,
  heart: Heart,
  sparkles: Sparkles,
  zap: Zap,
  award: Award,
  package: Package,
};

/**
 * Hero — dynamic first screen:
 * mouse-parallax product stage, collection badge, floating trust chips,
 * rotating stamp, and editorial headline.
 */
export function Hero() {
  const { addToCart } = useShop();
  const { hero, catalog } = useLandingConfig();
  const stageRef = useRef<HTMLDivElement>(null);

  const intensity = hero.parallaxIntensity ?? 1.0;

  // mouse parallax
  const mx = useMotionValue(0.5);
  const my = useMotionValue(0.5);
  const smx = useSpring(mx, { stiffness: 60, damping: 16 });
  const smy = useSpring(my, { stiffness: 60, damping: 16 });
  const baseRotX = useTransform(smy, [0, 1], [6 * intensity, -6 * intensity]);
  const baseRotY = useTransform(smx, [0, 1], [-8 * intensity, 8 * intensity]);
  const moveX = useTransform(smx, [0, 1], [10 * intensity, -10 * intensity]);
  const moveY = useTransform(smy, [0, 1], [8 * intensity, -8 * intensity]);

  const rotX = hero.parallaxEnabled ? baseRotX : 0;
  const rotY = hero.parallaxEnabled ? baseRotY : 0;

  // scroll parallax for the giant background word
  const { scrollYProgress } = useScroll();
  const bgX = useTransform(scrollYProgress, [0, 0.2], ["0%", "14%"]);
  const artX = useTransform(scrollYProgress, [0, 0.25], [0, -60]);

  const onMouse = (e: React.MouseEvent) => {
    if (!hero.parallaxEnabled) return;
    const r = stageRef.current?.getBoundingClientRect();
    if (!r) return;
    mx.set((e.clientX - r.left) / r.width);
    my.set((e.clientY - r.top) / r.height);
  };

  const activeUnderlinePath =
    hero.underlinePreset === "custom"
      ? hero.customUnderlinePath || underlinePaths.wavy
      : underlinePaths[hero.underlinePreset] || underlinePaths.wavy;

  const StampIcon = (hero.stampIcon && iconMap[hero.stampIcon.toLowerCase()]) || Feather;

  const defaultMiniStats = [
    { icon: "feather", title: "۸۵٪ نخ‌پنبه", subtitle: "ضدتعریق وحساسیت" },
    { icon: "palette", title: "طرح بافته‌شده", subtitle: "تضمین ثبات رنگ" },
    { icon: "truck", title: "ارسال سریع", subtitle: "پک کادویی + استیکر" },
  ];

  const miniStats = hero.miniTrustStats && hero.miniTrustStats.length > 0 ? hero.miniTrustStats : defaultMiniStats;

  return (
    <section id="home" data-lb-section="hero" className="relative overflow-hidden bg-cream-50">
      {/* ambient */}
      <div
        className="dot-grid pointer-events-none absolute inset-0"
        style={{ opacity: hero.dotGridOpacity ?? 0.6 }}
      />
      <div className="pointer-events-none absolute -left-32 top-10 h-96 w-96 rounded-full bg-brand-500/15 blur-3xl" />
      <div className="pointer-events-none absolute -right-24 bottom-8 h-80 w-80 rounded-full bg-pop-400/20 blur-3xl" />

      {/* giant scrolling outline word */}
      <motion.div
        aria-hidden
        style={{ x: bgX }}
        className="text-stroke-coffee pointer-events-none absolute -bottom-4 left-0 select-none whitespace-nowrap text-[22vw] font-black leading-none opacity-[0.08]"
        dir="ltr"
      >
        {hero.marqueeText || "AB SOCKS — AB SOCKS"}
      </motion.div>

      <div className="relative mx-auto grid max-w-7xl items-center gap-10 px-4 pb-20 pt-10 sm:px-6 lg:grid-cols-12 lg:gap-8 lg:pb-28 lg:pt-16">
        {/* copy */}
        <div className="lg:col-span-7">
          <motion.div
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.15, duration: 0.6 }}
            data-lb-field="hero.badgeText"
            className="inline-flex items-center gap-2.5 rounded-full border border-coffee-900/10 bg-white/80 px-4 py-2 shadow-sm backdrop-blur"
          >
            <span className="relative flex h-2.5 w-2.5">
              <span className="animate-ping-soft absolute inline-flex h-full w-full rounded-full bg-brand-500" />
              <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-brand-500" />
            </span>
            <span className="text-xs font-extrabold text-coffee-800 sm:text-sm">
              {hero.badgeText || "کالکشن جدید ۲۰۲۵ ای‌بی‌ساکس"}
            </span>
            <span
              className="rounded-full bg-coffee-900 px-2.5 py-0.5 text-[10px] font-black tracking-widest text-cream-50"
              dir="ltr"
            >
              {hero.badgeTag || "NEW DROP"}
            </span>
          </motion.div>

          <motion.h1
            data-lb-field="hero.headlineMain"
            initial={{ opacity: 0, y: 36 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.28, duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
            className="mt-6 text-[2.6rem] font-black leading-[1.15] tracking-tight text-coffee-900 sm:text-6xl lg:text-[4.6rem]"
          >
            {hero.headlineMain || "هر قدم،"}
            <br />
            <span className="relative inline-block text-brand-600">
              {hero.headlineHighlight || "یه اثر هنریه"}
              <svg
                viewBox="0 0 300 14"
                fill="none"
                className="absolute -bottom-2 right-0 w-full"
                style={{ color: hero.underlineColor || "#f97316" }}
                preserveAspectRatio="none"
              >
                <motion.path
                  d={activeUnderlinePath}
                  stroke="currentColor"
                  strokeWidth="7"
                  strokeLinecap="round"
                  initial={{ pathLength: 0 }}
                  animate={{ pathLength: 1 }}
                  transition={{ delay: 0.9, duration: 0.7, ease: "easeOut" }}
                />
              </svg>
            </span>
          </motion.h1>

          <motion.p
            data-lb-field="hero.subtitle"
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.45, duration: 0.6 }}
            className="mt-6 max-w-xl text-sm leading-[1.9] text-coffee-600 sm:text-base whitespace-pre-line"
          >
            {hero.subtitle}
          </motion.p>

          <motion.div
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.58, duration: 0.6 }}
            className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center"
          >
            <button
              onClick={() => {
                const target = hero.ctaPrimaryLink?.startsWith("#")
                  ? document.getElementById(hero.ctaPrimaryLink.substring(1))
                  : null;
                if (target) target.scrollIntoView({ behavior: "smooth" });
                else if (hero.ctaPrimaryLink) window.location.href = hero.ctaPrimaryLink;
              }}
              className="group inline-flex items-center justify-center gap-2.5 rounded-2xl bg-coffee-900 px-7 py-4 text-sm font-extrabold text-cream-50 shadow-xl shadow-coffee-900/25 transition-all hover:-translate-y-0.5 hover:bg-brand-600 active:scale-[0.98]"
            >
              <ShoppingBag className="h-5 w-5" />
              {hero.ctaPrimaryLabel || "دیدن گالری محصولات"}
              <ArrowDown className="h-4 w-4 transition-transform group-hover:translate-y-1" />
            </button>
            {(hero.ctaSecondaryEnabled ?? true) && (
              <button
                onClick={() => {
                  const target = hero.ctaSecondaryLink?.startsWith("#")
                    ? document.getElementById(hero.ctaSecondaryLink.substring(1))
                    : null;
                  if (target) target.scrollIntoView({ behavior: "smooth" });
                  else if (hero.ctaSecondaryLink) window.location.href = hero.ctaSecondaryLink;
                }}
                className="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-coffee-900/15 bg-white/70 px-6 py-4 text-sm font-extrabold text-coffee-900 backdrop-blur transition-all hover:border-brand-500 hover:text-brand-600 active:scale-[0.98]"
              >
                <Package className="h-5 w-5" />
                {hero.ctaSecondaryLabel || "باکس‌ساز اختصاصی (تا ۲۰٪ تخفیف)"}
              </button>
            )}
          </motion.div>

          {/* mini trust stats */}
          <motion.div
            data-lb-field="hero.miniTrustStats"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.75 }}
            className="mt-10 grid max-w-xl grid-cols-3 gap-3 border-t border-coffee-900/10 pt-6"
          >
            {miniStats.map((x, i) => {
              const StatIcon = iconMap[x.icon?.toLowerCase()] || Feather;
              return (
                <div key={x.title + i} className="flex items-center gap-2.5">
                  <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-500/12 text-brand-600">
                    <StatIcon className="h-5 w-5" />
                  </span>
                  <span>
                    <span className="block text-xs font-black text-coffee-900 sm:text-sm">{x.title}</span>
                    <span className="block text-[10px] text-coffee-500 sm:text-xs">{x.subtitle}</span>
                  </span>
                </div>
              );
            })}
          </motion.div>
        </div>

        {/* reactive product stage */}
        <div className="lg:col-span-5">
          {(hero.showFeaturedCard ?? true) && (
            <motion.div
              ref={stageRef}
              data-lb-field="hero.productStage"
              onMouseMove={onMouse}
              onMouseLeave={() => {
                mx.set(0.5);
                my.set(0.5);
              }}
              initial={{ opacity: 0, scale: 0.94 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 0.4, duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
              style={{ perspective: 900 }}
              className="group relative mx-auto max-w-md"
            >
              <motion.div
                style={{ rotateX: rotX, rotateY: rotY }}
                className="relative overflow-hidden rounded-[2.5rem] border border-coffee-900/10 bg-gradient-to-br from-cream-100 via-amber-50 to-brand-100 shadow-2xl shadow-coffee-900/20"
              >
                <div className="dot-grid absolute inset-0 opacity-50" />
                <div className="flex items-center justify-between p-5">
                  <span className="rounded-full bg-coffee-900 px-3.5 py-1.5 text-[11px] font-extrabold text-cream-50">
                    {hero.productBadge || "قلم‌موی نارنجی پرنده"}
                  </span>
                  <span className="text-[10px] font-black tracking-[0.25em] text-brand-600" dir="ltr">
                    {hero.productTag || "ART PIECE 01"}
                  </span>
                </div>
                <div className="relative mx-4 overflow-hidden rounded-[1.8rem]">
                  <img
                    src={hero.productImage || "/assets/img/landing/hero-sock.jpg"}
                    alt={hero.productBadge || "جوراب قلم‌موی نارنجی پرنده"}
                    className="animate-float-y aspect-square w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                  />
                </div>
                <div className="flex items-center justify-between gap-3 p-5">
                  <div>
                    <p className="text-[11px] font-bold text-coffee-500">
                      {hero.productCollection || "مجموعه آثار دست‌باف"}
                    </p>
                    <p className="mt-0.5 text-xl font-black text-coffee-900">
                      {faPrice(hero.productPrice || 148_000)} <span className="text-xs font-bold text-coffee-500">تومان</span>
                    </p>
                  </div>
                  <button
                    onClick={() =>
                      addToCart({
                        name: (catalog?.productNameTemplate || "جوراب {title}").replace("{title}", hero.productBadge || heroProduct.title),
                        price: hero.productPrice || heroProduct.price,
                        productId: heroProduct.id,
                        colorIndex: 0,
                        color: heroProduct.colors[0]?.name || "پیش‌فرض",
                      })
                    }
                    className="flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-brand-500/30 transition-transform hover:scale-105 active:scale-95"
                  >
                    <ShoppingBag className="h-4 w-4" />
                    {hero.productButtonText || "افزودن به سبد"}
                  </button>
                </div>
              </motion.div>

              {/* floating live chips */}
              {(hero.trustChips || []).map((chip, i) => (
                <motion.div
                  key={chip + i}
                  style={{ x: moveX, y: i === 0 ? moveY : undefined }}
                  className={`absolute ${i === 0 ? "-right-3 top-16 sm:-right-8" : "-left-3 bottom-32 sm:-left-8"}`}
                >
                  <motion.div
                    animate={{ y: [0, -10, 0] }}
                    transition={{ duration: 3.2 + i, repeat: Infinity, ease: "easeInOut", delay: i * 0.8 }}
                    className="flex items-center gap-2 rounded-2xl border border-coffee-900/10 bg-white/90 px-3.5 py-2.5 text-[11px] font-extrabold text-coffee-800 shadow-xl backdrop-blur sm:text-xs"
                  >
                    <span className="relative flex h-2 w-2">
                      <span className="animate-ping-soft absolute h-full w-full rounded-full bg-emerald-500" />
                      <span className="relative h-2 w-2 rounded-full bg-emerald-500" />
                    </span>
                    {chip}
                  </motion.div>
                </motion.div>
              ))}

              {/* rotating stamp */}
              {(hero.stampEnabled ?? true) && (
                <motion.div style={{ x: artX }} className="absolute -bottom-8 left-6 hidden sm:block">
                  <div className="animate-spin-slow relative h-24 w-24">
                    <svg viewBox="0 0 100 100" className="h-full w-full">
                      <defs>
                        <path id="stamp-circle" d="M50,50 m-38,0 a38,38 0 1,1 76,0 a38,38 0 1,1 -76,0" />
                      </defs>
                      <text fontSize="10.5" fontWeight="800" fill="#221510" letterSpacing="1">
                        <textPath href="#stamp-circle">
                          {hero.stampText ? `${hero.stampText} • ` : "AB SOCKS • هر قدم یه اثر هنریه • "}
                        </textPath>
                      </text>
                    </svg>
                    <span className="absolute inset-0 m-auto flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white">
                      <StampIcon className="h-4.5 w-4.5" />
                    </span>
                  </div>
                </motion.div>
              )}
            </motion.div>
          )}
        </div>
      </div>
    </section>
  );
}

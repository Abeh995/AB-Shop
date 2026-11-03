import { motion, useScroll, useTransform } from "framer-motion";
import { Asterisk, Dices, Wand2 } from "lucide-react";
import { useRef } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { galleryImages as defaultGalleryImages } from "../data/site";
import { Marquee, Reveal } from "./primitives";

/**
 * MotionGallery — dark chapter with dynamic background hue shifts,
 * custom angled marquees, film strip, and interactive CTA prompts.
 */
export function MotionGallery() {
  const ref = useRef<HTMLElement>(null);
  const { openModal } = useShop();
  const { visualEffects, motionGallery } = useLandingConfig();
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start end", "end start"] });

  const colors =
    visualEffects?.motionGalleryColors && visualEffects.motionGalleryColors.length === 5
      ? visualEffects.motionGalleryColors
      : ["#221510", "#3d2113", "#243022", "#2c1a24", "#221510"];

  const backgroundColor = useTransform(
    scrollYProgress,
    [0, 0.3, 0.6, 0.85, 1],
    colors
  );
  const stripX = useTransform(scrollYProgress, [0.05, 0.9], ["3%", "-26%"]);

  const angle = visualEffects?.motionGalleryAngle ?? -2;
  const images = motionGallery?.images?.length ? motionGallery.images : defaultGalleryImages;

  return (
    <motion.section id="gallery" data-lb-section="motionGallery" ref={ref} style={{ backgroundColor }} className="relative overflow-hidden py-20 sm:py-28">
      <div className="dot-grid-light pointer-events-none absolute inset-0 opacity-20" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6">
        <Reveal className="mx-auto max-w-2xl text-center">
          <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-extrabold text-brand-300">
            <Asterisk className="h-3.5 w-3.5" />
            {motionGallery?.eyebrow || "نمایشگاه کوچکِ قدم‌های شما"}
          </span>
          <h2 className="mt-4 text-3xl font-black text-cream-50 sm:text-5xl">
            {motionGallery?.title || "گالری حرکت"}
          </h2>
          <p className="mt-4 text-sm leading-relaxed text-cream-100/60 sm:text-base">
            {motionGallery?.desc || "جوراب قرار نیست یکجا بنشیند؛ اسکرول کن تا قطره‌رنگ کارگاه هم با تو حرکت کند و استایل‌های واقعی مشتری‌ها را ببینی."}
          </p>
        </Reveal>
      </div>

      {/* angled big-text marquee */}
      <div className="relative mt-14" style={{ transform: `rotate(${angle}deg)` }}>
        <Marquee speed={visualEffects?.motionGallerySpeed || 30}>
          {Array.from({ length: 5 }).map((_, i) => (
            <span key={i} className="flex items-baseline gap-6 px-6" dir="ltr">
              <span className="text-stroke-cream whitespace-nowrap text-6xl font-black leading-none sm:text-8xl">
                {motionGallery?.marqueeSubText || "MOTION GALLERY"}
              </span>
              <span className="whitespace-nowrap text-6xl font-black leading-none text-brand-500 sm:text-8xl">
                {motionGallery?.marqueeText || "گالری حرکت"}
              </span>
            </span>
          ))}
        </Marquee>
      </div>

      {/* scroll-driven film strip */}
      <div className="relative mt-12">
        <motion.div style={{ x: stripX }} className="flex w-max gap-5 px-6" dir="ltr">
          {[...images, ...images.slice(0, 3)].map((g, i) => (
            <div key={i} className="group relative w-72 overflow-hidden rounded-3xl sm:w-80">
              <img
                src={g.img}
                alt={g.caption}
                className="aspect-[4/4.8] w-full object-cover transition-transform duration-700 group-hover:scale-105"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-coffee-950/80 via-transparent to-transparent opacity-80" />
              <div className="absolute bottom-4 right-4 left-4 flex items-center justify-between text-cream-50" dir="rtl">
                <span className="text-xs font-black sm:text-sm">{g.caption}</span>
                <span className="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold backdrop-blur">
                  #ABStyle
                </span>
              </div>
            </div>
          ))}
        </motion.div>
      </div>

      {/* quick CTA to interactive tools */}
      <div className="relative mt-12 flex flex-wrap items-center justify-center gap-3 px-4">
        <button
          onClick={() => openModal("style")}
          className="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-5 py-3 text-xs font-extrabold text-cream-50 backdrop-blur transition-all hover:bg-brand-500 hover:text-white sm:text-sm"
        >
          <Wand2 className="h-4 w-4 text-brand-400" />
          {motionGallery?.ctaStyleQuizText || "استایل‌ساز هوشمند: سلیقه‌ات را کشف کن"}
        </button>
        <button
          onClick={() => openModal("wheel")}
          className="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-transparent px-5 py-3 text-xs font-extrabold text-cream-50 transition-all hover:border-brand-400 hover:text-brand-400 sm:text-sm"
        >
          <Dices className="h-4 w-4 text-pop-400" />
          {motionGallery?.ctaLuckyWheelText || "گردونه شانس: کد تخفیف ببر"}
        </button>
      </div>
    </motion.section>
  );
}

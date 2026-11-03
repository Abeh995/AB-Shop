import { ArrowDown, Feather, Gift, RefreshCcw, ShieldCheck, Sparkles, Truck, ArrowLeft, Heart, Zap, Award } from "lucide-react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { Marquee, Reveal } from "./primitives";

const icons: Record<string, any> = {
  feather: Feather,
  refresh: RefreshCcw,
  truck: Truck,
  gift: Gift,
  shield: ShieldCheck,
  sparkles: Sparkles,
  heart: Heart,
  zap: Zap,
  award: Award,
};

/**
 * TrustBar — trust strip with icons and horizontal scrolling banner.
 */
export function TrustBar() {
  const { trustBar } = useLandingConfig();
  if (trustBar.enabled === false) return null;

  const activeItems = (trustBar.items || []).filter((t) => t.active !== false);

  return (
    <section id="trustbar" data-lb-section="trustBar" className="relative border-y border-coffee-900/10 bg-cream-100/80">
      <div className="mx-auto grid max-w-7xl grid-cols-2 gap-x-4 gap-y-6 px-4 py-10 sm:px-6 md:grid-cols-5 md:py-12">
        {activeItems.map((t, i) => {
          const Icon = icons[t.icon] || Feather;
          return (
            <Reveal key={t.title + i} delay={i * 0.08} className={i === 4 ? "col-span-2 md:col-span-1" : ""}>
              <div className="group flex items-start gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-coffee-900/10 bg-white text-brand-600 shadow-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:bg-brand-500 group-hover:text-white">
                  <Icon className="h-5 w-5" strokeWidth={2.2} />
                </span>
                <span>
                  <span className="block text-sm font-black text-coffee-900">{t.title}</span>
                  <span className="mt-0.5 block text-xs text-coffee-500">{t.desc}</span>
                </span>
              </div>
            </Reveal>
          );
        })}
      </div>

      {/* HSB — horizontal scrolling banner */}
      <div className="bg-coffee-950 py-3.5 text-cream-50">
        <Marquee speed={trustBar.marqueeSpeed || 20}>
          {Array.from({ length: 6 }).map((_, i) => (
            <span key={i} className="flex items-center gap-3 px-6 text-sm font-extrabold sm:text-base">
              <ArrowDown className="h-4 w-4 text-brand-400" />
              {trustBar.marqueeText || "ادامه بده، پایین‌تر قشنگ‌تره"}
              <Sparkles className="h-4 w-4 text-pop-400" />
              <span className="text-cream-50/30" dir="ltr">
                {trustBar.marqueeSubText || "KEEP SCROLLING"}
              </span>
              <ArrowLeft className="h-4 w-4 text-brand-400/60" />
            </span>
          ))}
        </Marquee>
      </div>
    </section>
  );
}

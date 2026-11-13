import { Award, Check, Heart, MoveLeft, Quote, RefreshCcw, ShieldCheck, Sparkles } from "lucide-react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { Marquee, Reveal } from "./primitives";

const stampIcons: Record<string, any> = {
  refresh: RefreshCcw,
  shield: ShieldCheck,
  heart: Heart,
  sparkles: Sparkles,
  award: Award,
};

/**
 * PromiseBox — guarantee panel with dynamic editorial copy, image, CTA, and rotating stamp.
 */
export function PromiseBox() {
  const { editorial } = useLandingConfig();
  const box = editorial.promiseBox;

  const StampIcon = (box.stampIcon && stampIcons[box.stampIcon.toLowerCase()]) || RefreshCcw;

  const handleCta = () => {
    const link = box.ctaLink || "#catalog";
    if (link.startsWith("#")) {
      document.getElementById(link.substring(1))?.scrollIntoView({ behavior: "smooth" });
    } else {
      window.location.href = link;
    }
  };

  const defaultBullets = [
    "۷ روز تعویض یا بازگشت وجه، بدون هیچ سؤالی",
    "هزینه‌ی ارسال مرجوعی هم با ما",
    "ضمانت دوام بافت تا ۱۲ ماه",
  ];
  const bullets = box.bullets && box.bullets.length > 0 ? box.bullets : defaultBullets;

  if (box.enabled === false) return null;

  return (
    <section id="promise" data-lb-section="promiseBox" className="relative overflow-hidden bg-cream-50 py-16 sm:py-24">
      <div className="mx-auto max-w-7xl px-3 sm:px-6">
        <Reveal>
          <div className="relative -rotate-1 rounded-[2.5rem] bg-coffee-900 shadow-[0_35px_80px_-30px_rgba(24,15,9,0.6)] sm:rounded-[3rem]">
            {/* dashed ticket edge */}
            <div
              className="absolute inset-y-6 left-8 hidden w-px lg:block"
              style={{ backgroundImage: "linear-gradient(to bottom, rgba(251,247,238,.35) 45%, transparent 45%)", backgroundSize: "1px 14px" }}
            />
            <div className="dot-grid-light pointer-events-none absolute inset-0 rounded-[inherit] opacity-25" />
            <div className="pointer-events-none absolute -left-20 -top-24 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl" />

            <div className="relative grid items-center gap-10 p-7 sm:p-12 lg:grid-cols-12 lg:p-16">
              {/* text */}
              <div className="lg:col-span-7 lg:pr-10">
                <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-extrabold text-brand-300">
                  <Quote className="h-3.5 w-3.5" />
                  {box.eyebrow || "ضمانتِ امضاشده‌ی ای‌بی ساکس"}
                </span>
                <h2 className="mt-5 text-3xl font-black leading-[1.3] text-cream-50 sm:text-5xl">
                  {box.title || "قولِ دوست‌داشتن، تضمینی"}
                </h2>
                <p className="mt-5 max-w-lg text-sm leading-[2] text-cream-100/70 sm:text-base">
                  {box.body || "ما اینقدر به بافتمون مطمئنیم که قسمت ماجراجوییش با ماست: جوراب‌ها رو بپوش، بشور، قدم بزن… اگر حس نکردی بهترین جورابیه که تا حالا داشتی، بدون حتی یه سؤال پسش می‌گیریم."}
                </p>
                <ul className="mt-7 max-w-md space-y-3 text-sm font-bold text-cream-100">
                  {bullets.map((line) => (
                    <li key={line} className="flex items-center gap-3">
                      <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-500 text-white">
                        <Check className="h-3.5 w-3.5" strokeWidth={3} />
                      </span>
                      {line}
                    </li>
                  ))}
                </ul>
                <button
                  onClick={handleCta}
                  className="group mt-9 inline-flex items-center gap-2.5 rounded-2xl bg-cream-50 px-6 py-3.5 text-sm font-extrabold text-coffee-900 transition-all hover:-translate-y-0.5 hover:bg-brand-500 hover:text-white"
                >
                  {box.ctaLabel || "خرید با خیال راحت"}
                  <MoveLeft className="h-4.5 w-4.5 transition-transform group-hover:-translate-x-1" />
                </button>
              </div>

              {/* visual */}
              <div className="relative lg:col-span-5 lg:pl-6">
                <div className="relative rotate-2 overflow-hidden rounded-[2rem] border-4 border-cream-50/90 shadow-2xl transition-transform duration-500 hover:rotate-0">
                  <img
                    src={box.image || "/assets/img/landing/gift-box.jpg"}
                    alt={editorial.promiseBox?.giftImageAlt || "باکس هدیه ای‌بی ساکس"}
                    className="aspect-[4/3.2] w-full object-cover"
                  />
                </div>

                {/* rotating guarantee stamp */}
                <div className="absolute -bottom-8 left-2 sm:-left-4">
                  <div className="animate-spin-slow relative h-28 w-28 drop-shadow-2xl">
                    <svg viewBox="0 0 100 100" className="h-full w-full">
                      <circle cx="50" cy="50" r="49" fill="#e85d1f" />
                      <defs>
                        <path id="promise-stamp" d="M50,50 m-36,0 a36,36 0 1,1 72,0 a36,36 0 1,1 -72,0" />
                      </defs>
                      <text fontSize="10" fontWeight="900" fill="#fbf7ee" letterSpacing="1.5">
                        <textPath href="#promise-stamp">
                          {box.stampText ? `${box.stampText} • ` : "۷ روز ضمانت تعویض • بدون سؤال • "}
                        </textPath>
                      </text>
                    </svg>
                    <span className="absolute inset-0 m-auto flex h-11 w-11 items-center justify-center rounded-full bg-coffee-900 text-cream-50">
                      <StampIcon className="h-5 w-5" />
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Reveal>
      </div>

      {/* thin strip under the dark panel */}
      <div className="mt-10 opacity-90">
        <Marquee speed={26} className="text-coffee-400">
          {Array.from({ length: 8 }).map((_, i) => (
            <span key={i} className="flex items-center gap-4 px-5 text-xs font-black tracking-wide">
              {box.bottomMarquee ? (
                <span>{box.bottomMarquee}</span>
              ) : (
                <>
                  <span>تعویض آسان</span>
                  <span className="text-brand-500">✺</span>
                  <span>بازگشت وجه کامل</span>
                  <span className="text-brand-500">✺</span>
                  <span>ضمانت دوام بافت</span>
                </>
              )}
              <span className="text-brand-500">✺</span>
            </span>
          ))}
        </Marquee>
      </div>
    </section>
  );
}

import { Asterisk, MapPin, MoveLeft, Scissors } from "lucide-react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { marqueeStory as defaultMarqueeStory, storyStats as defaultStoryStats } from "../data/site";
import { Marquee, Reveal } from "./primitives";

/**
 * Story — workshop story section with angled marquees, stats, and customizable badges.
 */
export function Story() {
  const { editorial } = useLandingConfig();
  const story = editorial?.story;

  const marqueeText = story?.marquee || defaultMarqueeStory;
  const marqueeSub = story?.marqueeSub || "HAND-KNITTED WITH LOVE";
  const stats = story?.stats?.length ? story.stats : defaultStoryStats;
  const paragraphs = story?.paragraphs?.length
    ? story.paragraphs
    : [
        "ای‌بی ساکس با یک دستگاه بافت دست‌دوم و یک باور ساده شروع شد: «جوراب، مچاله‌شده‌ترین تکه‌ی استایل نباید باشد.» اولین جفت قلم‌موی نارنجی را در یک اتاقِ هشت‌متری با نخ‌های رنگیِ بازار بافتیم و همان روز فهمیدیم چه بازی‌ای شروع شده.",
        "امروز، کنار هر دستگاه، هنوز یک جفت چشم و یک جفت دست هست که تک‌تک جفت‌ها را ور می‌دارد؛ پنجه‌ها را با دست می‌دوزد و رنگ‌ها را زیر نور آفتاب چک می‌کند. این کُندی، عیب نیست — امضای ماست.",
      ];

  const handleCta = () => {
    const link = story?.ctaLink || "#gallery";
    if (link.startsWith("#")) {
      document.getElementById(link.substring(1))?.scrollIntoView({ behavior: "smooth" });
    } else {
      window.location.href = link;
    }
  };

  return (
    <section id="story" data-lb-section="story" className="relative overflow-hidden bg-cream-100 py-16 sm:py-24">
      {/* angled HSB floating on the section */}
      <div className="pointer-events-none absolute inset-x-[-2%] top-6 -rotate-2 bg-brand-500 py-2.5 text-white shadow-lg sm:top-10">
        <Marquee speed={24}>
          {Array.from({ length: 8 }).map((_, i) => (
            <span key={i} className="flex items-center gap-2 px-6 text-xs font-black tracking-wide sm:text-sm">
              {marqueeText}
              <Asterisk className="h-4 w-4" />
              <span dir="ltr" className="text-white/60">{marqueeSub}</span>
              <Asterisk className="h-4 w-4" />
            </span>
          ))}
        </Marquee>
      </div>
      <div className="pointer-events-none absolute inset-x-[-2%] top-16 rotate-1 bg-coffee-900/90 py-1 sm:top-20" />

      <div className="relative mx-auto grid max-w-7xl items-center gap-12 px-4 pt-20 sm:px-6 lg:grid-cols-12">
        {/* imagery */}
        <Reveal className="lg:col-span-6">
          <div className="relative mx-auto max-w-lg">
            <div className="rotate-2 overflow-hidden rounded-[2.5rem] border-8 border-white shadow-2xl">
              <img
                src={story?.image || "/assets/img/landing/story.jpg"}
                alt={editorial.story?.imageAlt || "کارگاه بافندگی ای‌بی ساکس"}
                className="aspect-[4/4.6] w-full object-cover"
              />
            </div>
            <div className="absolute -bottom-6 -right-3 flex -rotate-3 items-center gap-2.5 rounded-2xl bg-coffee-900 px-4 py-3 text-cream-50 shadow-xl sm:-right-8">
              <MapPin className="h-4.5 w-4.5 text-brand-400" />
              <div>
                <p className="text-[11px] font-black">{story?.locationTitle || "کارگاه ای‌بی — تهران، بازار بزرگ"}</p>
                <p className="text-[10px] text-cream-100/60">{story?.locationSubtitle || "از پاییز ۱۳۹۸، قدم به قدم با شما"}</p>
              </div>
            </div>
            <div className="absolute -left-3 top-8 flex -rotate-6 items-center gap-2 rounded-full bg-white px-3.5 py-2 shadow-xl sm:-left-8">
              <Scissors className="h-4 w-4 text-brand-500" />
              <span className="text-[11px] font-extrabold text-coffee-800">
                {story?.craftTitle || "دوخت نهایی با دست"}
              </span>
            </div>
          </div>
        </Reveal>

        {/* copy */}
        <div className="lg:col-span-6">
          <Reveal>
            <span className="text-sm font-extrabold tracking-wider text-brand-600">
              {story?.eyebrow || "داستان ما"}
            </span>
            <h2 className="mt-3 text-3xl font-black leading-[1.35] text-coffee-900 sm:text-5xl whitespace-pre-line">
              {story?.title || "از یک دستگاهِ بافتِ قدیمی\nتا قلبِ استایلِ شما"}
            </h2>
          </Reveal>
          <Reveal delay={0.12}>
            <div className="mt-6 max-w-xl space-y-4 text-sm leading-[2] text-coffee-600 sm:text-[15px]">
              {paragraphs.map((p, pIdx) => (
                <p key={pIdx}>{p}</p>
              ))}
            </div>
          </Reveal>

          <Reveal delay={0.2}>
            <div className="mt-9 grid max-w-md grid-cols-3 gap-3">
              {stats.map((s) => (
                <div key={s.label} className="rounded-2xl border border-coffee-900/10 bg-white p-4 text-center shadow-sm">
                  <p className="text-2xl font-black text-brand-600 sm:text-3xl">{s.value}</p>
                  <p className="mt-1 text-[11px] font-bold text-coffee-500">{s.label}</p>
                </div>
              ))}
            </div>
          </Reveal>

          <Reveal delay={0.28}>
            <button
              onClick={handleCta}
              className="group mt-9 inline-flex items-center gap-2.5 rounded-2xl bg-coffee-900 px-6 py-3.5 text-sm font-extrabold text-cream-50 transition-all hover:-translate-y-0.5 hover:bg-brand-500"
            >
              {story?.ctaLabel || "دیدن جوراب‌ها در حرکت"}
              <MoveLeft className="h-4.5 w-4.5 transition-transform group-hover:-translate-x-1" />
            </button>
          </Reveal>
        </div>
      </div>
    </section>
  );
}

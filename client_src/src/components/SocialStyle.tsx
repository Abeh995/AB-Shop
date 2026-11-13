import { AtSign, BadgePercent, Camera, Copy, Hash, MoveLeft } from "lucide-react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { contact, socialPosts as defaultSocialPosts } from "../data/site";
import { Marquee, Reveal, SectionHeading } from "./primitives";

/**
 * SocialStyle — UGC engine:
 * story your socks, tag us, get discount code with dynamic builder bindings.
 */
export function SocialStyle() {
  const { toast } = useShop();
  const { socialStyle } = useLandingConfig();

  if (socialStyle && socialStyle.enabled === false) return null;

  const posts = socialStyle?.posts?.length ? socialStyle.posts : defaultSocialPosts;

  const copyTag = async () => {
    try {
      await navigator.clipboard.writeText(`@${contact.instagram}`);
      toast((socialStyle?.tagCopiedToast || "تگ @{handle} کپی شد — منتظر استوری‌تیم!").replace("{handle}", contact.instagram));
    } catch {
      toast((socialStyle?.tagPrompt || "ما را تگ کن: @{handle}").replace("{handle}", contact.instagram), "info");
    }
  };

  return (
    <section id="social" data-lb-section="socialStyle" className="relative overflow-hidden bg-coffee-950 py-16 text-cream-50 sm:py-24">
      <div className="dot-grid-light pointer-events-none absolute inset-0 opacity-20" />
      <div className="pointer-events-none absolute -left-28 top-10 h-80 w-80 rounded-full bg-brand-500/20 blur-3xl" />

      {/* background hashtag marquee */}
      <div className="pointer-events-none absolute inset-x-0 top-8 opacity-[0.07]" dir="ltr">
        <Marquee speed={40}>
          {Array.from({ length: 4 }).map((_, i) => (
            <span key={i} className="px-8 text-8xl font-black" dir="ltr">
              {socialStyle?.title || "#ABSOCKS_STYLE"}
            </span>
          ))}
        </Marquee>
      </div>

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6">
        <div className="grid items-end gap-8 lg:grid-cols-12">
          <div className="lg:col-span-5">
            <SectionHeading
        field="socialStyle.title"
              dark
              center={false}
              eyebrow={socialStyle?.eyebrow || "کمپین مشتری‌های واقعی"}
              title={socialStyle?.title || "#ABSOCKS_STYLE"}
              desc={socialStyle?.desc || "استایلت با جوراب ای‌بی ساکس، عضو بعدی دیوار شهرت کارگاهی است."}
            />
            <Reveal className="-mt-2">
              <div className="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                <h3 className="flex items-center gap-2 text-base font-black text-pop-400">
                  <BadgePercent className="h-5 w-5" />
                  {socialStyle?.boxTitle || "عکس پات رو با جوراب ای‌بی ساکس استوری کن!"}
                </h3>
                <p className="mt-2 text-xs leading-[2] text-cream-100/70 sm:text-sm">
                  {socialStyle?.boxDesc || (
                    <>
                      ما رو تگ کن و یک <strong className="text-cream-50">کد تخفیف ۳۰٪ بدون محدودیت</strong> به‌عنوان هدیه در دایرکت دریافت کن. هر جمعه، قشنگ‌ترین استایل روی صفحه‌ی کارگاه بازنشر می‌شود.
                    </>
                  )}
                </p>
                <div className="mt-4 flex flex-col gap-2.5 sm:flex-row">
                  <button
                    onClick={copyTag}
                    className="flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-xs font-extrabold text-white transition-all hover:bg-brand-600 active:scale-[0.98]"
                  >
                    <AtSign className="h-4.5 w-4.5" />
                    {socialStyle?.tagButtonLabel || (socialStyle?.tagCopyLabel || "کپی تگ @{handle}").replace("{handle}", contact.instagram)}
                    <Copy className="h-3.5 w-3.5 opacity-70" />
                  </button>
                  <span className="flex items-center justify-center gap-1.5 text-[11px] font-bold text-cream-100/50">
                    <Camera className="h-4 w-4" />
                    {socialStyle?.storyCountNote || "بیش از ۱٬۲۰۰ استوری تا امروز"}
                  </span>
                </div>
              </div>
            </Reveal>
          </div>

          {/* ugc grid */}
          <div className="grid auto-rows-[120px] grid-cols-3 gap-2.5 sm:auto-rows-[150px] sm:gap-4 lg:col-span-7">
            {posts.map((p, i) => (
              <Reveal key={p.img + i} delay={i * 0.07} className={i === 0 ? "col-span-2 row-span-2 h-full" : "h-full"}>
                <figure className="group relative h-full overflow-hidden rounded-2xl border border-white/10 sm:rounded-3xl">
                  <img
                    src={p.img}
                    alt={(socialStyle?.postAlt || "استایل {handle}").replace("{handle}", p.handle)}
                    loading="lazy"
                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-coffee-950/70 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                  <figcaption className="absolute bottom-3 right-3 translate-y-3 text-xs font-extrabold text-white opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                    {p.handle}
                  </figcaption>
                </figure>
              </Reveal>
            ))}
            {/* join tile */}
            <Reveal delay={0.3} className="h-full">
              <button
                onClick={copyTag}
                className="group flex h-full w-full flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-brand-500/50 bg-brand-500/10 p-4 transition-colors hover:bg-brand-500/20 sm:rounded-3xl"
              >
                <Hash className="h-6 w-6 text-brand-400 transition-transform group-hover:rotate-12" />
                <span className="text-[11px] font-black text-brand-300">
                  {socialStyle?.joinTileText || "تو هم بیا تو گالری"}
                </span>
                <MoveLeft className="h-4 w-4 text-brand-400 transition-transform group-hover:-translate-x-1" />
              </button>
            </Reveal>
          </div>
        </div>
      </div>
    </section>
  );
}

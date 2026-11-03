import { Footprints, Info, Ruler, Scaling } from "lucide-react";
import { useState } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { products } from "../data/products";
import { faDigits, faNum } from "../lib/format";
import { ProductArt } from "./ProductArt";
import { Reveal, SectionHeading } from "./primitives";

function footCm(eu: number): string {
  const cm = Math.round((eu * 0.667 - 1.5) * 2) / 2;
  return faDigits(cm.toFixed(1));
}

/**
 * SizeGuide — smart size converter (EU slider → foot cm → recommended AB size)
 * with dynamic builder configuration.
 */
export function SizeGuide() {
  const { sizeGuide } = useLandingConfig();
  const [eu, setEu] = useState(42);

  if (sizeGuide && sizeGuide.enabled === false) return null;

  const rec = eu <= 39 ? "۳۶-۴۰" : "۴۱-۴۶";
  const defaultSmallTip = "با ۳٪ الاستینِ بافت، سایز ۳۶-۴۰ دقیقا قالب پای تو می‌شیند و دور مچ جمع نمی‌شود.";
  const defaultLargeTip = "سایز ۴۱-۴۶ با کشباف نرم خودش را از قوزک تا ساق به فرم پای تو می‌چسباند — بدون اثر کش.";
  const tip = eu <= 39 ? (sizeGuide?.smallTip || defaultSmallTip) : (sizeGuide?.largeTip || defaultLargeTip);
  const demo = products.find((p) => p.id === 9) || products[0];

  return (
    <section id="size" data-lb-section="sizeGuide" className="bg-cream-100/70 py-16 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6">
        <SectionHeading
        field="sizeGuide.title"
          eyebrow={sizeGuide?.eyebrow || "راهنمای هوشمند سایز ای‌بی ساکس"}
          title={sizeGuide?.title || "سایز دقیق پات رو در ۳ ثانیه پیدا کن!"}
          desc={sizeGuide?.desc || "شماره‌ی کفش اروپایی (EU) خودت رو با اسلایدر انتخاب کن تا طول کف پا و سایز پیشنهادی ما را لحظه‌ای ببینی."}
        />

        <Reveal>
          <div className="grid items-stretch gap-6 overflow-hidden rounded-[2.5rem] border border-coffee-900/10 bg-white p-5 shadow-sm sm:p-10 lg:grid-cols-12">
            {/* controls */}
            <div className="flex flex-col gap-6 lg:col-span-7">
              <div className="rounded-3xl border border-coffee-900/10 bg-cream-50 p-5 sm:p-6">
                <div className="mb-4 flex items-center justify-between">
                  <span className="flex items-center gap-2 text-sm font-extrabold text-coffee-800">
                    <Footprints className="h-5 w-5 text-brand-500" />
                    شماره کفش شما (EU):
                  </span>
                  <span className="text-3xl font-black tabular-nums text-brand-600">{faNum(eu)}</span>
                </div>
                <input
                  type="range"
                  min={35}
                  max={46}
                  value={eu}
                  onChange={(e) => setEu(Number(e.target.value))}
                  className="range-brand w-full"
                  aria-label={sizeGuide?.shoeSizeInputLabel || "شماره کفش"}
                />
                <div className="mt-3 flex justify-between text-[11px] font-bold text-coffee-400">
                  {[35, 38, 40, 42, 44, 46].map((n) => (
                    <span key={n}>{faNum(n)}</span>
                  ))}
                </div>
              </div>

              <div className="flex flex-wrap items-center gap-2">
                <span className="text-xs font-bold text-coffee-500">انتخاب سریع:</span>
                {[37, 39, 42, 44].map((n) => (
                  <button
                    key={n}
                    onClick={() => setEu(n)}
                    className={`rounded-xl px-3.5 py-2 text-xs font-extrabold transition-all ${eu === n ? "bg-coffee-900 text-cream-50" : "bg-cream-100 text-coffee-600 hover:bg-brand-100"}`}
                  >
                    سایز {faNum(n)}
                  </button>
                ))}
              </div>

              {/* how to measure */}
              <div className="rounded-3xl bg-cream-50 p-5 text-xs leading-relaxed text-coffee-600 sm:p-6">
                <p className="mb-3 flex items-center gap-2 font-black text-coffee-900">
                  <Ruler className="h-4.5 w-4.5 text-brand-500" />
                  اگر شماره‌ی EU کفشت را نمی‌دانی:
                </p>
                <ol className="grid gap-2.5 sm:grid-cols-3">
                  {(sizeGuide?.steps?.length
                    ? sizeGuide.steps
                    : [
                        "پات را روی کاغذ بگذار و دور آن خط بکش",
                        "فاصله‌ی پاشنه تا نوک بزرگ‌ترین انگشت را اندازه بگیر",
                        "عدد سانتی‌متر را با جدول نتیجه مقایسه کن",
                      ]).map(
                    (s, i) => (
                      <li key={s} className="flex items-start gap-2">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-500/15 text-[11px] font-black text-brand-600">
                          {faNum(i + 1)}
                        </span>
                        {s}
                      </li>
                    )
                  )}
                </ol>
              </div>
            </div>

            {/* live result card */}
            <div className="relative overflow-hidden rounded-3xl bg-coffee-900 p-6 text-cream-50 sm:p-7 lg:col-span-5">
              <div className="dot-grid-light absolute inset-0 opacity-20" />
              <ProductArt product={demo} colorIndex={0} className="absolute -bottom-8 -left-6 w-36 rotate-12 opacity-25" />

              <div className="relative flex h-full flex-col justify-between">
                <div>
                  <span className="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-1 text-xs font-black text-pop-400">
                    <Scaling className="h-3.5 w-3.5" />
                    نتیجه‌ی تحلیلِ سایز کارگاه
                  </span>

                  <div className="mt-6">
                    <p className="text-xs text-cream-100/60">طول تخمینی کف پا:</p>
                    <p className="mt-1 text-3xl font-black text-cream-50">
                      {footCm(eu)} <span className="text-base text-cream-100/60">سانتی‌متر</span>
                    </p>
                  </div>

                  <div className="mt-5 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <p className="text-xs text-cream-100/60">سایز پیشنهادی ای‌بی ساکس:</p>
                    <p className="mt-1 text-2xl font-black text-brand-400">سایز {rec}</p>
                    <p className="mt-2 text-xs leading-relaxed text-cream-100/70">{tip}</p>
                  </div>
                </div>

                <div className="mt-6 flex items-center gap-2 border-t border-white/10 pt-4 text-[11px] text-cream-100/50">
                  <Info className="h-4 w-4 shrink-0 text-brand-400" />
                  <span>تمام جوراب‌ها قبل از ارسال، با شستشوی صنعتی ضدآب‌رفت شده‌اند.</span>
                </div>
              </div>
            </div>
          </div>
        </Reveal>
      </div>
    </section>
  );
}

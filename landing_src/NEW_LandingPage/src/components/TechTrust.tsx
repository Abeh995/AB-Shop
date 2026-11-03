import { BadgeCheck, Check, CreditCard, Footprints, Landmark, Leaf, Palette, ShieldCheck, X } from "lucide-react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { useShop } from "../context/ShopContext";
import { comparisonRows as defaultComparisonRows, techCards as defaultTechCards } from "../data/site";
import { Reveal, SectionHeading } from "./primitives";

const cardIcons: Record<string, any> = {
  leaf: Leaf,
  foot: Footprints,
  palette: Palette,
};

const badgeIcons: Record<string, any> = {
  shield: ShieldCheck,
  badge: BadgeCheck,
  card: CreditCard,
  bank: Landmark,
};

/**
 * TechTrust — feature cards, comparison table, and trust badges with dynamic builder bindings.
 */
export function TechTrust() {
  const { toast } = useShop();
  const { editorial } = useLandingConfig();
  const tech = editorial.techTrust;
  if (tech.enabled === false) return null;

  const cards = tech.techCards && tech.techCards.length > 0 ? tech.techCards : defaultTechCards;
  const rows = tech.comparisonRows && tech.comparisonRows.length > 0 ? tech.comparisonRows : defaultComparisonRows;

  const defaultBadges = [
    { icon: "shield", title: "اینماد رسمی", sub: "دو ستاره — مركز توسعه تجارت الكترونیكی", msg: "نماد اعتماد الکترونیکی (اینماد) فروشگاه ای‌بی ساکس فعال و معتبر است." },
    { icon: "badge", title: "ساماندهی", sub: "نشان ملی ثبت رسانه‌های دیجیتال", msg: "نشان ملی ثبت رسانه‌های دیجیتال ثبت و تایید شده است." },
    { icon: "card", title: "درگاه امن شتاب", sub: "پرداخت رمزنگاری‌شده بانکی", msg: "پرداخت‌ها فقط از طریق درگاه رسمی شتاب انجام می‌شود؛ هیچ شماره‌کارتی ذخیره نمی‌کنیم." },
    { icon: "bank", title: "عضو اتحادیه", sub: "تولیدکنندگان منسوجات و پوشاک", msg: "ای‌بی ساکس عضو رسمی اتحادیه‌ی تولیدکنندگان منسوجات است." },
  ];

  const badges = tech.trustBadges && tech.trustBadges.length > 0 ? tech.trustBadges : defaultBadges;
  const tableHeaders = {
    label: tech.tableHeaders?.label || "جزئیات فنی",
    ab: tech.tableHeaders?.ab || "ای‌بی ساکس",
    other: tech.tableHeaders?.other || tech.otherColumnLabel || "جوراب‌های معمولی",
  };

  return (
    <section id="tech" data-lb-section="techTrust" className="bg-cream-50 py-16 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6">
        <SectionHeading
        field="editorial.techTrust.title"
          eyebrow={tech.eyebrow || "تفاوت در جزئیات فنی"}
          title={tech.title || "چرا ای‌بی ساکس انتخاب اول استریت‌پوش‌هاست؟"}
          desc={tech.desc || "همه‌چیز به چیزی برمی‌گردد که با چشم دیده نمی‌شود: نخ، دوخت و وسواس کارگاه."}
        />

        {/* feature cards */}
        <div className="mb-14 grid gap-5 md:grid-cols-3">
          {cards.map((c, i) => {
            const Icon = cardIcons[c.icon] || Leaf;
            return (
              <Reveal key={c.title || i} delay={i * 0.1}>
                <div className="group h-full rounded-[2rem] border border-coffee-900/10 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl sm:p-7">
                  <span className="flex h-13 w-13 items-center justify-center rounded-2xl bg-brand-500/12 text-brand-600 transition-all duration-300 group-hover:rotate-6 group-hover:bg-brand-500 group-hover:text-white">
                    <Icon className="h-6 w-6" strokeWidth={2.1} />
                  </span>
                  <h3 className="mt-4 text-lg font-black text-coffee-900">{c.title}</h3>
                  <p className="mt-2 text-xs leading-[1.9] text-coffee-600 sm:text-sm">{c.desc}</p>
                </div>
              </Reveal>
            );
          })}
        </div>

        {/* comparison */}
        <Reveal>
          <div className="overflow-hidden rounded-[2rem] border border-coffee-900/10 shadow-sm">
            <div className="grid grid-cols-[1.2fr_1.4fr_1.4fr] bg-coffee-900 px-4 py-3.5 text-[11px] font-black text-cream-50 sm:px-6 sm:text-sm">
              <span>{tableHeaders.label}</span>
              <span className="flex items-center gap-1.5 text-pop-400">
                <span className="h-2 w-2 rounded-full bg-pop-400" />
                {tableHeaders.ab}
              </span>
              <span className="text-cream-100/50">{tableHeaders.other}</span>
            </div>
            {rows.map((r, i) => (
              <div
                key={r.label}
                className={`grid grid-cols-[1.2fr_1.4fr_1.4fr] px-4 py-3.5 text-[11px] sm:px-6 sm:text-[13px] ${i % 2 === 0 ? "bg-white" : "bg-cream-100/60"}`}
              >
                <span className="font-extrabold text-coffee-900">{r.label}</span>
                <span className="flex items-center gap-1.5 font-bold text-coffee-700">
                  <Check className="h-4 w-4 shrink-0 text-emerald-600" strokeWidth={3} />
                  {r.ab}
                </span>
                <span className="flex items-center gap-1.5 text-coffee-400">
                  <X className="h-4 w-4 shrink-0 text-red-400" strokeWidth={3} />
                  {r.other}
                </span>
              </div>
            ))}
          </div>
        </Reveal>

        {/* official trust badges */}
        <Reveal delay={0.15} className="mt-14">
          <p className="mb-5 text-center text-sm font-black text-coffee-800">نمادهای اعتماد و مجوزهای رسمی</p>
          <div className="mx-auto grid max-w-4xl grid-cols-2 gap-3 lg:grid-cols-4">
            {badges.map((b, i) => {
              const Icon = badgeIcons[b.icon?.toLowerCase()] || ShieldCheck;
              return (
                <button
                  key={b.title || i}
                  onClick={() => toast(b.msg, "info")}
                  className="group flex flex-col items-center gap-2.5 rounded-3xl border border-coffee-900/10 bg-white p-5 text-center transition-all hover:-translate-y-1 hover:border-brand-500/40 hover:shadow-xl"
                >
                  <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-coffee-900/[0.04] text-coffee-600 transition-colors group-hover:bg-brand-500/15 group-hover:text-brand-600">
                    <Icon className="h-6 w-6" strokeWidth={1.8} />
                  </span>
                  <span className="text-xs font-black text-coffee-900">{b.title}</span>
                  <span className="text-[10px] leading-relaxed text-coffee-500">{b.sub}</span>
                </button>
              );
            })}
          </div>
        </Reveal>
      </div>
    </section>
  );
}

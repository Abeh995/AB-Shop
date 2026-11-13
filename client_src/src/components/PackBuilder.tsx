import { AnimatePresence, motion } from "framer-motion";
import { Check, Dices, Eraser, Gift, NotebookPen, Package, Plus, ShoppingBag, Sparkles, Truck, X } from "lucide-react";
import { useMemo, useState } from "react";
import { useShop } from "../context/ShopContext";
import { products, type Product } from "../data/products";
import { useLandingConfig } from "../context/LandingConfigContext";
import { packBoxOptions as defaultBoxOptions, packSizeOptions as defaultSizeOptions, packTypes as defaultPackTypes } from "../data/site";
import { faNum, faPrice } from "../lib/format";
import { cn } from "../utils/cn";
import { ProductArt } from "./ProductArt";
import { Reveal, SectionHeading } from "./primitives";

interface Chosen {
  product: Product;
  colorIndex: number;
}

/**
 * PackBuilder — 3 clean steps:
 * choose a pack type → tap products into the live box →
 * gift-note + summarized custom pack → add to cart.
 */
export function PackBuilder() {
  const { addToCart, toast } = useShop();
  const { packBuilder } = useLandingConfig();

  const tiers = packBuilder?.tiers?.length ? packBuilder.tiers : defaultPackTypes;
  const boxOptions = packBuilder?.boxOptions?.length ? packBuilder.boxOptions : defaultBoxOptions;
  const sizeOptions = packBuilder?.sizeOptions?.length ? packBuilder.sizeOptions : defaultSizeOptions;

  const [typeId, setTypeId] = useState(tiers[1]?.id || tiers[0]?.id || "week");
  const [chosen, setChosen] = useState<Chosen[]>([]);
  const [note, setNote] = useState("");
  const [size, setSize] = useState(sizeOptions[1] || sizeOptions[0] || "سایز ۴۱ تا ۴۶");
  const [box, setBox] = useState(boxOptions[0] || "باکس کرافت مینیمال");

  if (packBuilder?.enabled === false) return null;

  const type = tiers.find((t) => t.id === typeId) || tiers[0];
  const socks = useMemo(() => products.filter((p) => p.art === "sock"), []);
  const complete = chosen.length === type.count;

  const sum = chosen.reduce((s, c) => s + c.product.price, 0);
  const saving = Math.round((sum * type.discount) / 100);
  const final = sum - saving;

  const toggle = (product: Product) => {
    setChosen((c) => {
      const idx = c.findIndex((x) => x.product.id === product.id);
      if (idx >= 0) return c.filter((_, i) => i !== idx);
      if (c.length >= type.count) {
        toast((packBuilder.capacityText || "باکس {tier} فقط {count} جفت جا دارد").replace("{tier}", type.title).replace("{count}", faNum(type.count)), "info");
        return c;
      }
      return [...c, { product, colorIndex: product.rank % product.colors.length }];
    });
  };

  const autoFill = () => {
    const shuffled = [...socks].sort((a, b) => a.rank - b.rank).concat([...socks].sort(() => Math.random() - 0.5)).slice(0, type.count);
    setChosen(shuffled.map((product) => ({ product, colorIndex: 0 })));
    toast((packBuilder.trayText || "{count} پرفروش داخل باکس چیده شد").replace("{count}", faNum(type.count)));
  };

  const submit = () => {
    addToCart({
      name: (packBuilder.summaryText || "{tier} ({count} جفتی)").replace("{tier}", type.title).replace("{count}", faNum(type.count)),
      price: final,
      isPack: true,
      productIds: chosen.map((c) => c.product.id),
      note: `${chosen.map((c) => c.product.title).join("، ")} — ${size} — ${box}${note ? " " + (packBuilder.notePrefix || "— کارت: «{note}»").replace("{note}", note) : ""}`,
    });
    setChosen([]);
    setNote("");
  };

  return (
    <section id="pack" data-lb-section="packBuilder" className="relative overflow-hidden bg-cream-100/70 py-16 sm:py-24">
      <div className="pointer-events-none absolute -right-32 top-24 h-96 w-96 rounded-full bg-brand-500/10 blur-3xl" />
      <div className="pointer-events-none absolute -left-32 bottom-24 h-96 w-96 rounded-full bg-pop-400/15 blur-3xl" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6">
        <SectionHeading
        field="packBuilder.title"
          eyebrow={packBuilder?.eyebrow || "باکس‌ساز اختصاصی"}
          title={packBuilder?.title || "پک خودت را بچین، تخفیفش را بردار"}
          desc={
            packBuilder?.desc ||
            "٣ مدل باکس با ۳ سطح تخفیف؛ محصولات را به «باکس انتخابی» بینداز، کارت تبریک دست‌نویس رایگان بنویس و خلاصه‌ی پک اختصاصی‌ات را زنده ببین."
          }
        />

        {/* step 1 — pack types */}
        <Reveal className="mb-8 grid gap-4 sm:grid-cols-3">
          {tiers.map((t) => (
            <button
              key={t.id}
              onClick={() => {
                setTypeId(t.id);
                setChosen([]);
              }}
              className={cn(
                "relative rounded-3xl border-2 p-5 text-start transition-all",
                typeId === t.id
                  ? "border-brand-500 bg-white shadow-xl shadow-brand-500/10"
                  : "border-coffee-900/10 bg-white/60 hover:border-brand-300"
              )}
            >
              {t.featured && (
                <span className="absolute -top-3 right-5 rounded-full bg-coffee-900 px-3 py-1 text-[10px] font-black text-cream-50">
                  پرطرفدارترین
                </span>
              )}
              <div className="flex items-center justify-between">
                <span className={cn("flex h-11 w-11 items-center justify-center rounded-2xl", typeId === t.id ? "bg-brand-500 text-white" : "bg-cream-200 text-coffee-500")}>
                  <Package className="h-5 w-5" />
                </span>
                <span className="rounded-full bg-brand-50 px-3 py-1 text-xs font-black text-brand-600">{faNum(t.discount)}٪ تخفیف</span>
              </div>
              <h3 className="mt-3 text-lg font-black text-coffee-900">{t.title}</h3>
              <p className="mt-0.5 text-xs text-coffee-500">{t.tagline}</p>
              <ul className="mt-3 space-y-1.5">
                {t.perks.map((p) => (
                  <li key={p} className="flex items-center gap-1.5 text-[11px] font-bold text-coffee-600">
                    <Check className="h-3.5 w-3.5 text-emerald-600" strokeWidth={3} />
                    {p}
                  </li>
                ))}
              </ul>
              <p className="mt-3 text-[11px] font-extrabold text-coffee-400">ظرفیت: {faNum(t.count)} جفت</p>
            </button>
          ))}
        </Reveal>

        {/* mobile sticky mini progress */}
        <div className="sticky top-[4.25rem] z-30 mb-5 flex items-center justify-between gap-2 rounded-2xl border border-brand-500/30 bg-coffee-950/95 p-3 shadow-xl backdrop-blur lg:hidden">
          <span className="flex items-center gap-2 text-xs font-extrabold text-pop-400">
            <Package className="h-4 w-4" />
            {faNum(chosen.length)} از {faNum(type.count)} انتخاب شده
          </span>
          <a href="#pack-summary" className="shrink-0 rounded-xl bg-pop-400 px-3 py-1.5 text-[11px] font-black text-coffee-950">
            خلاصه پک ↓
          </a>
        </div>

        <div className="grid items-start gap-6 lg:grid-cols-12">
          {/* step 2 — product picking + live box */}
          <div className="lg:col-span-7">
            <div className="rounded-3xl border border-coffee-900/10 bg-white p-5 shadow-sm sm:p-6">
              <div className="mb-4 flex items-center justify-between">
                <h3 className="flex items-center gap-2 text-sm font-black text-coffee-900 sm:text-base">
                  <span className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-xs font-black text-white">۱</span>
                  {packBuilder?.step1Heading || "روی محصولات بزن تا وارد باکس شوند:"}
                </h3>
                <button onClick={autoFill} className="flex items-center gap-1.5 rounded-xl bg-coffee-900 px-3.5 py-2 text-[11px] font-extrabold text-cream-50 transition-colors hover:bg-brand-500">
                  <Dices className="h-4 w-4" />
                  {packBuilder?.autoFillLabel || "چیدمان هوشمند تصادفی"}
                </button>
              </div>

              {/* live selected box */}
              <div className="mb-5 rounded-2xl border-2 border-dashed border-brand-500/40 bg-brand-50/40 p-3.5">
                <p className="mb-2.5 flex items-center gap-1.5 text-[11px] font-black text-brand-700">
                  <Gift className="h-4 w-4" />
                  باکس انتخابی: <span className="text-coffee-500">({faNum(chosen.length)} از {faNum(type.count)})</span>
                </p>
                <div className={cn("grid gap-2.5", type.count <= 3 ? "grid-cols-3" : type.count === 4 ? "grid-cols-4" : "grid-cols-5")}>
                  {Array.from({ length: type.count }).map((_, i) => {
                    const c = chosen[i];
                    return (
                      <motion.div
                        key={c ? c.product.id : `empty-${i}`}
                        layout
                        initial={{ scale: 0.8, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        className={cn(
                          "relative flex aspect-square flex-col items-center justify-center rounded-2xl border p-1",
                          c ? "border-coffee-900/10 bg-white shadow-sm" : "border-dashed border-coffee-900/15 bg-cream-50/60"
                        )}
                      >
                        {c ? (
                          <>
                            <ProductArt product={c.product} colorIndex={c.colorIndex} className="h-full w-full" />
                            <span className="absolute inset-x-1 bottom-1 truncate rounded-lg bg-coffee-950/85 px-1 py-0.5 text-center text-[8.5px] font-black text-cream-50">
                              {c.product.title}
                            </span>
                            <button
                              onClick={() => toggle(c.product)}
                              aria-label={packBuilder.removeLabel || "حذف"}
                              className="absolute -left-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow-md transition-transform hover:scale-110"
                            >
                              <X className="h-3 w-3" />
                            </button>
                          </>
                        ) : (
                          <div className="flex flex-col items-center gap-1 text-coffee-300">
                            <Plus className="h-5 w-5" />
                            <span className="text-[10px] font-bold">جای خالی {faNum(i + 1)}</span>
                          </div>
                        )}
                      </motion.div>
                    );
                  })}
                </div>
              </div>

              {/* product grid to pick from */}
              <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                {socks.map((p) => {
                  const isPicked = chosen.some((c) => c.product.id === p.id);
                  return (
                    <button
                      key={p.id}
                      onClick={() => toggle(p)}
                      className={cn(
                        "group relative flex flex-col rounded-2xl border p-2.5 text-start transition-all",
                        isPicked
                          ? "border-brand-500 bg-brand-50/60 shadow-md ring-2 ring-brand-500/20"
                          : "border-coffee-900/10 bg-cream-50/60 hover:border-brand-400 hover:bg-white"
                      )}
                    >
                      {isPicked && (
                        <span className="absolute -top-2 -right-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-white shadow-md">
                          <Check className="h-3.5 w-3.5" strokeWidth={3} />
                        </span>
                      )}
                      <div className="relative aspect-square w-full overflow-hidden rounded-xl bg-white p-2">
                        <ProductArt product={p} colorIndex={p.rank % p.colors.length} className="h-full w-full" />
                      </div>
                      <div className="mt-2">
                        <p className="truncate text-xs font-black text-coffee-900">{p.title}</p>
                        <p className="mt-0.5 text-[11px] font-extrabold text-brand-600">{faPrice(p.price)} تومان</p>
                      </div>
                    </button>
                  );
                })}
              </div>
            </div>
          </div>

          {/* step 3 — options + summary */}
          <div id="pack-summary" className="lg:col-span-5">
            <div className="sticky top-24 space-y-4">
              {/* options card */}
              <div className="rounded-3xl border border-coffee-900/10 bg-white p-5 shadow-sm sm:p-6">
                <h3 className="mb-4 flex items-center gap-2 text-sm font-black text-coffee-900 sm:text-base">
                  <span className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-xs font-black text-white">۲</span>
                  شخصی‌سازی نهایی پک
                </h3>

                {/* size */}
                <div className="mb-4">
                  <label className="mb-1.5 block text-xs font-bold text-coffee-600">سایز جوراب‌های داخل پک:</label>
                  <div className="grid grid-cols-2 gap-2">
                    {sizeOptions.map((s) => (
                      <button
                        key={s}
                        onClick={() => setSize(s)}
                        className={cn(
                          "rounded-xl border py-2.5 text-center text-xs font-extrabold transition-all",
                          size === s
                            ? "border-brand-500 bg-brand-500 text-white shadow-md"
                            : "border-coffee-900/10 bg-cream-50 text-coffee-700 hover:border-brand-300"
                        )}
                      >
                        {s}
                      </button>
                    ))}
                  </div>
                </div>

                {/* box model */}
                <div className="mb-4">
                  <label className="mb-1.5 block text-xs font-bold text-coffee-600">طرح بسته‌بندی پک:</label>
                  <div className="grid grid-cols-2 gap-2">
                    {boxOptions.map((b) => (
                      <button
                        key={b}
                        onClick={() => setBox(b)}
                        className={cn(
                          "rounded-xl border py-2.5 text-center text-xs font-extrabold transition-all",
                          box === b
                            ? "border-brand-500 bg-brand-500 text-white shadow-md"
                            : "border-coffee-900/10 bg-cream-50 text-coffee-700 hover:border-brand-300"
                        )}
                      >
                        {b}
                      </button>
                    ))}
                  </div>
                </div>

                {/* handwritten card note */}
                <div>
                  <div className="mb-1.5 flex items-center justify-between">
                    <label className="flex items-center gap-1.5 text-xs font-bold text-coffee-600">
                      <NotebookPen className="h-4 w-4 text-brand-500" />
                      متن کارت تبریک دست‌نویس (رایگان):
                    </label>
                    {note && (
                      <button onClick={() => setNote("")} className="flex items-center gap-1 text-[10px] text-coffee-400 hover:text-red-500">
                        <Eraser className="h-3 w-3" />
                        پاک کردن
                      </button>
                    )}
                  </div>
                  <textarea
                    value={note}
                    onChange={(e) => setNote(e.target.value)}
                    rows={2}
                    maxLength={140}
                    placeholder={packBuilder.notePlaceholder || "مثلا: تولدت مبارک رفیق؛ همیشه خوش‌قدم باشی!"}
                    className="w-full rounded-2xl border border-coffee-900/10 bg-cream-50 p-3 text-xs font-bold text-coffee-900 outline-none transition-all placeholder:font-normal placeholder:text-coffee-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15"
                  />
                  <div className="mt-1 flex justify-between text-[10px] text-coffee-400">
                    <span>دست‌خط هنری روی کارت پستی کارگاه</span>
                    <span>{faNum(note.length)}/۱۴۰</span>
                  </div>
                </div>
              </div>

              {/* summary checkout box */}
              <div className="rounded-3xl bg-coffee-900 p-5 text-cream-50 shadow-xl sm:p-6">
                <div className="flex items-center justify-between border-b border-white/10 pb-4">
                  <div>
                    <h4 className="text-base font-black text-white">{type.title}</h4>
                    <p className="text-xs text-cream-100/60">
                      {faNum(chosen.length)} از {faNum(type.count)} جفت انتخاب شده
                    </p>
                  </div>
                  <span className="rounded-full bg-brand-500 px-3 py-1 text-xs font-black text-white">
                    {faNum(type.discount)}٪ تخفیف پک
                  </span>
                </div>

                <div className="my-4 space-y-2 text-xs text-cream-100/70">
                  <div className="flex justify-between">
                    <span>مجموع قیمت تک‌تک:</span>
                    <span className="font-bold">{faPrice(sum)} تومان</span>
                  </div>
                  <div className="flex justify-between text-pop-400">
                    <span>سود شما از این پک:</span>
                    <span className="font-black">− {faPrice(saving)} تومان</span>
                  </div>
                  <div className="flex justify-between border-t border-white/10 pt-2 text-sm font-black text-white">
                    <span>مبلغ نهایی پک:</span>
                    <span className="text-base text-brand-400">{faPrice(final)} تومان</span>
                  </div>
                </div>

                <button
                  onClick={submit}
                  disabled={!complete}
                  className="group flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-500 py-4 text-sm font-black text-white shadow-xl shadow-brand-500/30 transition-all hover:bg-brand-600 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-white/15 disabled:text-cream-100/40 disabled:shadow-none"
                >
                  <ShoppingBag className="h-5 w-5" />
                  {complete
                    ? packBuilder.addButtonText || "افزودن پک اختصاصی به سبد خرید"
                    : (packBuilder.remainingText || "هنوز {count} جفت دیگر انتخاب کن").replace("{count}", faNum(type.count - chosen.length))}
                </button>

                <p className="mt-3 flex items-center justify-center gap-1.5 text-center text-[10.5px] text-cream-100/50">
                  <Truck className="h-3.5 w-3.5 text-brand-400" />
                  ارسال رایگان + بسته‌بندی هدیه برای تمام پک‌های اختصاصی
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

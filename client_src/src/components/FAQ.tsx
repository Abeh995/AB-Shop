import { AnimatePresence, motion } from "framer-motion";
import { Clock, Headset, MessageCircle, Minus, Phone, Plus } from "lucide-react";
import { useState } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { useShop } from "../context/ShopContext";
import { contact, faqs as defaultFaqs } from "../data/site";
import { cn } from "../utils/cn";
import { Reveal, SectionHeading } from "./primitives";

/**
 * FAQ + support box with dynamic questions, answers, and contact box settings.
 */
export function FAQ() {
  const [open, setOpen] = useState<number>(0);
  const { toast } = useShop();
  const { editorial } = useLandingConfig();
  const faqData = editorial.faq;
  const items = faqData.items && faqData.items.length > 0 ? faqData.items : defaultFaqs;
  const support = faqData.supportBox;

  return (
    <section id="faq" data-lb-section="faq" className="bg-cream-100/70 py-16 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6">
        <SectionHeading
        field="editorial.faq.title"
          eyebrow={faqData.eyebrow || "سوالات متداول خریداران"}
          title={faqData.title || "هر چیزی که لازمه قبل از خرید بدونی"}
        />

        <div className="mx-auto max-w-3xl space-y-3">
          {items.map((f, i) => {
            const isOpen = open === i;
            return (
              <Reveal key={f.q + i} delay={i * 0.05}>
                <div
                  className={cn(
                    "overflow-hidden rounded-3xl border transition-colors",
                    isOpen ? "border-brand-500/40 bg-white shadow-lg" : "border-coffee-900/10 bg-white/70"
                  )}
                >
                  <button
                    onClick={() => setOpen(isOpen ? -1 : i)}
                    className="flex w-full items-center justify-between gap-3 px-5 py-4 text-start sm:px-6"
                  >
                    <span className="flex items-center gap-3 text-xs font-black text-coffee-900 sm:text-sm">
                      <span className={cn("text-[11px]", isOpen ? "text-brand-600" : "text-coffee-400")}>
                        {String(i + 1).padStart(2, "0").replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[Number(d)])}
                      </span>
                      {f.q}
                    </span>
                    <span
                      className={cn(
                        "flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-colors",
                        isOpen ? "bg-brand-500 text-white" : "bg-coffee-900/5 text-coffee-600"
                      )}
                    >
                      {isOpen ? <Minus className="h-4 w-4" /> : <Plus className="h-4 w-4" />}
                    </span>
                  </button>
                  <AnimatePresence initial={false}>
                    {isOpen && (
                      <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: "auto", opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
                      >
                        <p className="border-t border-coffee-900/5 px-5 pb-5 pt-3.5 text-xs leading-[2] text-coffee-600 sm:px-6 sm:text-sm">
                          {f.a}
                        </p>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              </Reveal>
            );
          })}
        </div>

        {/* support box */}
        {support?.enabled !== false && (
          <Reveal delay={0.2} className="mx-auto mt-12 max-w-3xl">
            <div className="relative -rotate-1 overflow-hidden rounded-[2.25rem] bg-coffee-900 p-6 text-cream-50 shadow-2xl transition-transform hover:rotate-0 sm:p-9">
              <div className="dot-grid-light pointer-events-none absolute inset-0 opacity-20" />
              <div className="pointer-events-none absolute -left-16 -top-16 h-52 w-52 rounded-full bg-brand-500/25 blur-3xl" />
              <div className="relative flex flex-col items-center gap-5 text-center sm:flex-row sm:text-start">
                <span className="flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-brand-500 text-white shadow-lg shadow-brand-500/30">
                  <Headset className="h-8 w-8" strokeWidth={1.8} />
                </span>
                <div className="flex-1">
                  <h3 className="text-lg font-black sm:text-xl">
                    {support?.title || "پاسخ سوالتان را پیدا نکردید؟"}
                  </h3>
                  <p className="mt-1.5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-cream-100/60 sm:justify-start">
                    <span className="flex items-center gap-1">
                      <Clock className="h-3.5 w-3.5" />
                      {support?.hours || contact.hours}
                    </span>
                    <span className="hidden text-white/25 sm:inline">|</span>
                    <span>{support?.responseTime || "میانگین پاسخ‌گویی: زیر ۱۰ دقیقه"}</span>
                  </p>
                </div>
                <div className="flex shrink-0 flex-col gap-2.5 sm:flex-row sm:items-center">
                  <button
                    onClick={() => toast(support?.chatUnavailableText || "چت آنلاین به‌زودی فعال می‌شود؛ فعلا در اینستاگرام در خدمتیم", "info")}
                    className="flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-xs font-extrabold text-white transition-all hover:bg-brand-600 active:scale-[0.97]"
                  >
                    <MessageCircle className="h-4.5 w-4.5" />
                    {support?.chatBtnText || "چت آنلاین"}
                  </button>
                  <a
                    href={`tel:${support?.phone || contact.phone}`}
                    className="flex items-center justify-center gap-2 rounded-2xl border-2 border-cream-50/20 px-5 py-3 text-xs font-extrabold text-cream-50 transition-colors hover:border-pop-400 hover:text-pop-400"
                  >
                    <Phone className="h-4.5 w-4.5" />
                    <span dir="ltr">{support?.phone || contact.phone}</span>
                  </a>
                </div>
              </div>
            </div>
          </Reveal>
        )}
      </div>
    </section>
  );
}

import { AnimatePresence, motion } from "framer-motion";
import { ArrowRight, Dumbbell, Footprints, Moon, Palette, RefreshCcw, ShoppingBag, Shuffle, Sparkles, Sun, Wand2, Zap } from "lucide-react";
import { useMemo, useState } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { products, type Product } from "../data/products";
import { faPrice } from "../lib/format";
import { ProductArt } from "./ProductArt";
import { Modal } from "./primitives";

const quizIcons: Record<string, any> = {
  Sun, Dumbbell, Moon, Zap, Sparkles, Palette, Shuffle, Footprints
};

const defaultSteps: { title: string; options: { label: string; tag: string; icon: string }[] }[] = [
  {
    title: "بیشتر کجا می‌پوشی؟",
    options: [
      { label: "هرروزه و دانشگاه", tag: "روزمره", icon: "Sun" },
      { label: "ورزش و باشگاه", tag: "ورزش", icon: "Dumbbell" },
      { label: "مهمانی و شب", tag: "شب", icon: "Moon" },
      { label: "استایل خیابانی", tag: "استریت", icon: "Zap" },
    ],
  },
  {
    title: "حال‌وهوای رنگی‌ات؟",
    options: [
      { label: "خنثی و مینیمال", tag: "مینیمال", icon: "Sparkles" },
      { label: "تیره و کلاسیک", tag: "تیره", icon: "Moon" },
      { label: "رنگی و شاد", tag: "رنگی", icon: "Palette" },
      { label: "سورپرایزم کن", tag: "", icon: "Shuffle" },
    ],
  },
  {
    title: "کفشت معمولاً چیه؟",
    options: [
      { label: "کتونی سفید", tag: "کتونی", icon: "Footprints" },
      { label: "رانینگ", tag: "دویدن", icon: "Dumbbell" },
      { label: "بوت", tag: "بوت", icon: "Footprints" },
      { label: "اسنیکرز خاص", tag: "اسنیکرز", icon: "Zap" },
    ],
  },
];

function recommend(tags: string[]): Product {
  const pool = products.filter((p) => p.art === "sock");
  let best = pool[0];
  let bestScore = -1;
  for (const p of pool) {
    const score = tags.filter((t) => p.tags.includes(t)).length * 10 - p.rank;
    if (score > bestScore) {
      bestScore = score;
      best = p;
    }
  }
  return best;
}

/** Smart Style Maker — dynamic style quiz with recommendations and cart dispatch. */
export function StyleMaker() {
  const { modal, closeModal, openModal, addToCart } = useShop();
  const { styleMaker } = useLandingConfig();
  const [step, setStep] = useState(0);
  const [answers, setAnswers] = useState<string[]>([]);
  const [result, setResult] = useState<Product | null>(null);

  if (styleMaker && styleMaker.enabled === false) return null;

  const steps = styleMaker?.steps?.length ? styleMaker.steps : defaultSteps;
  const open = modal.name === "style";

  const reset = () => {
    setStep(0);
    setAnswers([]);
    setResult(null);
  };

  const pick = (tag: string) => {
    const next = [...answers, tag].filter(Boolean);
    if (tag === "") {
      const pool = products.filter((p) => p.art === "sock");
      setResult(pool[Math.floor(Math.random() * pool.length)]);
      return;
    }
    if (step < steps.length - 1) {
      setAnswers(next);
      setStep(step + 1);
    } else {
      setAnswers(next);
      setResult(recommend(next));
    }
  };

  const reasons = useMemo(() => {
    if (!result) return "";
    const hits = answers.filter((t) => result.tags.includes(t));
    return hits.length
      ? (styleMaker?.reasonTemplate || "چون گفتی «{tags}» — و این دقیقا حال‌وهوای «{title}» است.")
          .replace("{tags}", hits.join("» و «"))
          .replace("{title}", result.title)
      : styleMaker?.workshopNote || "انتخاب سرهماهنگی کارگاه برای استایل تو.";
  }, [result, answers]);

  return (
    <Modal open={open} onClose={closeModal} size="sm">
      <div className="p-6 sm:p-8">
        <div className="mb-1 flex items-center gap-2">
          <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-white">
            <Wand2 className="h-5 w-5" />
          </span>
          <div>
            <h3 className="text-lg font-black text-coffee-900">
              {styleMaker?.title || "استایل‌ساز هوشمند ای‌بی ساکس"}
            </h3>
            <p className="text-[11px] text-coffee-500">
              {styleMaker?.subtitle || "سه ضربه تا جورابِ سرنوشت‌ساز"}
            </p>
          </div>
        </div>

        {/* progress */}
        <div className="mb-6 mt-4 flex gap-1.5">
          {steps.map((_, i) => (
            <span key={i} className={`h-1.5 flex-1 rounded-full transition-colors ${result || i <= step ? "bg-brand-500" : "bg-cream-200"}`} />
          ))}
        </div>

        <AnimatePresence mode="wait">
          {!result ? (
            <motion.div
              key={step}
              initial={{ opacity: 0, x: 40 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -40 }}
              transition={{ duration: 0.3 }}
            >
              <p className="mb-4 text-sm font-extrabold text-coffee-800">{steps[step].title}</p>
              <div className="grid grid-cols-2 gap-2.5">
                {steps[step].options.map((o) => {
                  const Icon = (typeof o.icon === "string" ? quizIcons[o.icon] : o.icon) || Sparkles;
                  return (
                    <button
                      key={o.label}
                      onClick={() => pick(o.tag)}
                      className="group flex flex-col items-start gap-2.5 rounded-2xl border-2 border-coffee-900/10 bg-cream-50 p-4 text-start transition-all hover:border-brand-500 hover:bg-brand-50 active:scale-[0.97]"
                    >
                      <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-coffee-900/[0.06] text-coffee-600 transition-colors group-hover:bg-brand-500 group-hover:text-white">
                        <Icon className="h-4.5 w-4.5" />
                      </span>
                      <span className="text-xs font-extrabold text-coffee-800">{o.label}</span>
                    </button>
                  );
                })}
              </div>
              {step > 0 && (
                <button
                  onClick={() => {
                    setStep(step - 1);
                    setAnswers(answers.slice(0, -1));
                  }}
                  className="mt-4 flex items-center gap-1 text-xs font-bold text-coffee-500 hover:text-brand-600"
                >
                  <ArrowRight className="h-3.5 w-3.5" />
                  برگرد به سؤال قبل
                </button>
              )}
            </motion.div>
          ) : (
            <motion.div key="result" initial={{ opacity: 0, scale: 0.92 }} animate={{ opacity: 1, scale: 1 }} className="text-center">
              <div className="dot-grid relative mx-auto max-w-[200px] rounded-3xl bg-cream-100 p-5">
                <ProductArt product={result} colorIndex={0} className="w-full drop-shadow-xl" />
              </div>
              <h4 className="mt-5 text-xl font-black text-coffee-900">
                {styleMaker?.resultTitle ? `${styleMaker.resultTitle}: ${result.title}` : result.title}
              </h4>
              <p className="mt-1.5 text-xs leading-relaxed text-coffee-500">{reasons}</p>
              <p className="mt-2 text-lg font-black text-brand-600">
                {faPrice(result.price)} <span className="text-xs text-coffee-500">تومان</span>
              </p>
              <div className="mt-5 flex flex-col gap-2.5">
                <button
                  onClick={() => {
                    addToCart({
                      name: `جوراب ${result.title}`,
                      price: result.price,
                      productId: result.id,
                      colorIndex: 0,
                      color: result.colors[0].name,
                      size: result.sizes[0],
                    });
                    closeModal();
                    reset();
                  }}
                  className="flex items-center justify-center gap-2 rounded-2xl bg-coffee-900 py-3.5 text-sm font-extrabold text-cream-50 transition-colors hover:bg-brand-500"
                >
                  <ShoppingBag className="h-4.5 w-4.5" />
                  {styleMaker?.resultAddToCart || "افزودن به سبد"}
                </button>
                <div className="flex gap-2.5">
                  <button
                    onClick={() => {
                      const id = result.id;
                      closeModal();
                      reset();
                      window.setTimeout(() => openModal("product", { productId: id }), 250);
                    }}
                    className="flex-1 rounded-2xl border-2 border-coffee-900/15 py-3 text-xs font-extrabold text-coffee-800 transition-colors hover:border-brand-500 hover:text-brand-600"
                  >
                    جزئیات بیشتر
                  </button>
                  <button
                    onClick={reset}
                    className="flex flex-1 items-center justify-center gap-1.5 rounded-2xl border-2 border-coffee-900/15 py-3 text-xs font-extrabold text-coffee-800 transition-colors hover:border-brand-500 hover:text-brand-600"
                  >
                    <RefreshCcw className="h-3.5 w-3.5" />
                    از اول
                  </button>
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </Modal>
  );
}

import { AnimatePresence, motion } from "framer-motion";
import { Check, Minus, Plus, ShieldCheck, ShoppingBag, Star, Truck } from "lucide-react";
import { useEffect, useMemo, useRef, useState } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { useShop } from "../context/ShopContext";
import { products } from "../data/products";
import { faNum, faPrice } from "../lib/format";
import { cn } from "../utils/cn";
import { ProductArt } from "./ProductArt";
import { Modal } from "./primitives";

/**
 * ProductModal — tap a product anywhere to open its detailed card:
 * live color, size, quantity and instant add-to-cart.
 */
export function ProductModal() {
  const cfg: any = useLandingConfig().catalog;
  const { modal, closeModal, addToCart } = useShop();
  const found = useMemo(
    () => products.find((p) => p.id === modal.payload?.productId) ?? null,
    [modal.payload?.productId]
  );
  // keep the last product alive during the exit animation
  const lastRef = useRef(found);
  if (found) lastRef.current = found;
  const product = found ?? lastRef.current;

  const [colorIndex, setColorIndex] = useState(0);
  const [size, setSize] = useState<string | null>(null);
  const [qty, setQty] = useState(1);

  useEffect(() => {
    if (modal.name === "product" && found) {
      setColorIndex(Math.min(modal.payload?.colorIndex ?? 0, found.colors.length - 1));
      setSize(found.sizes[0]);
      setQty(1);
    }
  }, [modal.name, modal.payload?.colorIndex, found]);

  const open = modal.name === "product" && !!product;
  if (!product) return <Modal open={false} onClose={closeModal}>{null}</Modal>;

  const cw = product.colors[colorIndex];
  const discount = product.oldPrice ? Math.round((1 - product.price / product.oldPrice) * 100) : 0;

  return (
    <Modal open={open} onClose={closeModal} size="lg">
      <div className="grid sm:grid-cols-2">
        {/* art side */}
        <div className="relative flex min-h-64 items-center justify-center overflow-hidden bg-cream-100 p-8 sm:min-h-full">
          <div className="dot-grid absolute inset-0 opacity-50" />
          <AnimatePresence mode="wait">
            <motion.div
              key={colorIndex}
              initial={{ opacity: 0, scale: 0.85, rotate: -6 }}
              animate={{ opacity: 1, scale: 1, rotate: 0 }}
              exit={{ opacity: 0, scale: 0.9, rotate: 6 }}
              transition={{ duration: 0.35 }}
              className="relative w-full max-w-[240px]"
            >
              <ProductArt product={product} colorIndex={colorIndex} className="w-full drop-shadow-2xl" />
            </motion.div>
          </AnimatePresence>
          <span className="absolute right-4 top-4 rounded-full bg-coffee-900 px-3 py-1.5 text-[10px] font-extrabold text-cream-50">
            {product.badge}
          </span>
          {discount > 0 && (
            <span className="absolute bottom-4 left-4 rounded-full bg-brand-500 px-3 py-1.5 text-xs font-black text-white shadow-lg">
              {faNum(discount)}٪ تخفیف
            </span>
          )}
        </div>

        {/* detail side */}
        <div className="flex flex-col gap-4 p-6 sm:p-7">
          <div>
            <p className="text-[11px] font-bold text-brand-600">{product.categoryLabel}</p>
            <h3 className="mt-1 text-2xl font-black text-coffee-900">{product.title}</h3>
            <div className="mt-2 flex items-center gap-2 text-xs text-coffee-500">
              <span className="flex items-center gap-1">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star key={i} className={cn("h-3.5 w-3.5", i < Math.round(product.rating) ? "fill-pop-500 text-pop-500" : "text-cream-300")} />
                ))}
              </span>
              <b>{faNum(product.rating)}</b> ( {faNum(product.reviews)} دیدگاه )
            </div>
          </div>

          <p className="text-xs leading-[1.9] text-coffee-600">{product.desc}</p>

          <ul className="space-y-1.5">
            {product.features.map((f) => (
              <li key={f} className="flex items-center gap-2 text-xs font-bold text-coffee-700">
                <Check className="h-4 w-4 text-emerald-600" strokeWidth={3} />
                {f}
              </li>
            ))}
          </ul>

          {/* color */}
          <div>
            <p className="mb-2 text-xs font-extrabold text-coffee-800">
              رنگ: <span className="text-brand-600">{cw.name}</span>
            </p>
            <div className="flex gap-2">
              {product.colors.map((c, i) => (
                <button
                  key={c.name}
                  onClick={() => setColorIndex(i)}
                  aria-label={c.name}
                  title={c.name}
                  className={cn(
                    "h-8 w-8 rounded-full border-[3px] transition-all",
                    i === colorIndex ? "scale-110 border-coffee-900 shadow-lg" : "border-white shadow hover:scale-105"
                  )}
                  style={{ backgroundColor: c.hex }}
                />
              ))}
            </div>
          </div>

          {/* size + qty */}
          <div className="flex flex-wrap items-center gap-3">
            <div className="flex gap-1.5">
              {product.sizes.map((s) => (
                <button
                  key={s}
                  onClick={() => setSize(s)}
                  className={cn(
                    "rounded-xl px-3.5 py-2 text-xs font-extrabold transition-all",
                    size === s ? "bg-coffee-900 text-cream-50 shadow-lg" : "bg-cream-100 text-coffee-600 hover:bg-brand-100"
                  )}
                >
                  {s}
                </button>
              ))}
            </div>
            <div className="ms-auto flex items-center gap-1 rounded-xl bg-cream-100 p-1">
              <button onClick={() => setQty((q) => Math.min(9, q + 1))} className="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-white" aria-label="افزایش">
                <Plus className="h-4 w-4" />
              </button>
              <span className="w-6 text-center text-sm font-black">{faNum(qty)}</span>
              <button onClick={() => setQty((q) => Math.max(1, q - 1))} className="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-white" aria-label="کاهش">
                <Minus className="h-4 w-4" />
              </button>
            </div>
          </div>

          {/* price + cta */}
          <div className="mt-auto border-t border-coffee-900/10 pt-4">
            <div className="flex items-center justify-between">
              <div>
                {product.oldPrice && <p className="text-xs text-coffee-400 line-through">{faPrice(product.oldPrice)} تومان</p>}
                <p className="text-2xl font-black text-brand-600">
                  {faPrice(product.price)} <span className="text-xs font-bold text-coffee-500">تومان</span>
                </p>
              </div>
              <button
                onClick={() => {
                  addToCart(
                    { name: (cfg?.productNameTemplate || "جوراب {title}").replace("{title}", product.title), price: product.price, productId: product.id, colorIndex, color: cw.name, size: size ?? undefined },
                    qty
                  );
                  closeModal();
                }}
                className="flex items-center gap-2 rounded-2xl bg-coffee-900 px-6 py-3.5 text-sm font-extrabold text-cream-50 shadow-xl transition-all hover:bg-brand-500 active:scale-95"
              >
                <ShoppingBag className="h-4.5 w-4.5" />
                افزودن به سبد
              </button>
            </div>
            <div className="mt-3 flex items-center justify-between rounded-xl bg-cream-100 px-3 py-2 text-[10px] font-bold text-coffee-500">
              <span className="flex items-center gap-1">
                <Truck className="h-3.5 w-3.5 text-brand-500" /> ارسال ۲۴-۷۲ ساعته به سراسر ایران
              </span>
              <span className="flex items-center gap-1">
                <ShieldCheck className="h-3.5 w-3.5 text-emerald-600" /> ۷ روز تعویض بدون سؤال
              </span>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  );
}

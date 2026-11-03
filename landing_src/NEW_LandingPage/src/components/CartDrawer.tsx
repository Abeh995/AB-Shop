import { AnimatePresence, motion } from "framer-motion";
import { Gift, Minus, Plus, ShoppingBag, Trash2, Truck, X } from "lucide-react";
import { useEffect } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { products } from "../data/products";
import { DISCOUNT_CODE } from "../data/site";
import { faNum, faPrice, FREE_SHIPPING_THRESHOLD } from "../lib/format";
import { ProductArt } from "./ProductArt";

/** CartDrawer — dynamic sliding drawer with free-shipping progress and configurable texts. */
export function CartDrawer() {
  const { cart, cartOpen, setCartOpen, cartTotal, cartCount, updateQty, removeFromCart, toast } = useShop();
  const { cartDrawer } = useLandingConfig();

  useEffect(() => {
    if (!cartOpen) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [cartOpen]);

  const threshold = cartDrawer?.freeShippingThreshold || FREE_SHIPPING_THRESHOLD;
  const discountCode = cartDrawer?.discountCode || DISCOUNT_CODE;
  const progress = Math.min(100, (cartTotal / threshold) * 100);
  const remaining = threshold - cartTotal;

  return (
    <AnimatePresence>
      {cartOpen && (
        <motion.div className="fixed inset-0 z-[85]" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
          <div className="absolute inset-0 bg-coffee-950/65 backdrop-blur-sm" onClick={() => setCartOpen(false)} />
          <motion.aside
            initial={{ x: "-100%" }}
            animate={{ x: 0 }}
            exit={{ x: "-100%" }}
            transition={{ type: "spring", damping: 30, stiffness: 300 }}
            className="absolute left-0 top-0 flex h-full w-full max-w-md flex-col bg-cream-50 shadow-2xl"
          >
            {/* head */}
            <div className="flex items-center justify-between border-b border-coffee-900/10 p-5">
              <div>
                <h3 className="text-lg font-black">{cartDrawer?.title || "سبد خرید"}</h3>
                <p className="text-xs text-coffee-500">{faNum(cartCount)} کالا در سبد موجود است</p>
              </div>
              <button
                onClick={() => setCartOpen(false)}
                aria-label={cartDrawer.closeLabel || "بستن"}
                className="flex h-10 w-10 items-center justify-center rounded-full bg-coffee-900/5 transition-colors hover:bg-brand-500 hover:text-white"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            {/* free shipping bar */}
            <div className="border-b border-coffee-900/10 bg-cream-100/70 p-4">
              <div className="mb-2 flex items-center justify-between text-[11px] font-bold">
                <span className="flex items-center gap-1.5 text-coffee-700">
                  <Truck className="h-4 w-4 text-brand-500" />
                  {remaining > 0
                    ? (cartDrawer.freeShippingRemaining || "{amount} تومان تا ارسال رایگان!").replace("{amount}", faPrice(remaining))
                    : cartDrawer.freeShippingReached || "ارسال سفارش شما رایگان شد!"}
                </span>
                <span className="text-brand-600">{faNum(Math.round(progress))}٪</span>
              </div>
              <div className="h-2 overflow-hidden rounded-full bg-cream-300">
                <motion.div
                  className="h-full rounded-full bg-gradient-to-l from-brand-500 to-emerald-500"
                  animate={{ width: `${progress}%` }}
                  transition={{ type: "spring", damping: 25 }}
                />
              </div>
            </div>

            {/* items */}
            <div className="flex-1 space-y-3 overflow-y-auto p-4">
              {cart.length === 0 && (
                <div className="flex h-full flex-col items-center justify-center gap-3 text-center">
                  <span className="flex h-16 w-16 items-center justify-center rounded-3xl bg-cream-200 text-coffee-400">
                    <ShoppingBag className="h-7 w-7" />
                  </span>
                  <p className="text-sm font-bold text-coffee-600">{cartDrawer?.emptyTitle || "سبدت فعلا خالیه…"}</p>
                  <button
                    onClick={() => {
                      setCartOpen(false);
                      document.getElementById("catalog")?.scrollIntoView({ behavior: "smooth" });
                    }}
                    className="rounded-full bg-coffee-900 px-5 py-2.5 text-xs font-extrabold text-cream-50 transition-colors hover:bg-brand-500"
                  >
                    {cartDrawer?.emptyButtonText || "بریم گالری محصولات"}
                  </button>
                </div>
              )}

              {cart.map((item) => {
                const product = products.find((p) => p.id === item.productId);
                return (
                  <motion.div
                    layout
                    key={item.key}
                    initial={{ opacity: 0, y: 12 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, x: -40 }}
                    className="flex items-center gap-3 rounded-2xl border border-coffee-900/10 bg-white p-3"
                  >
                    <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-cream-100">
                      {product ? (
                        <ProductArt product={product} colorIndex={item.colorIndex ?? 0} className="h-14 w-14" />
                      ) : (
                        <Gift className="h-7 w-7 text-brand-500" />
                      )}
                    </div>
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-xs font-extrabold text-coffee-900">{item.name}</p>
                      <p className="mt-0.5 text-[10px] text-coffee-500">
                        {[item.color && `${cartDrawer.colorPrefix || "رنگ"}: ${item.color}`, item.size && `${cartDrawer.sizePrefix || "سایز"}: ${item.size}`].filter(Boolean).join(" · ") || item.note?.slice(0, 40)}
                      </p>
                      <p className="mt-1 text-xs font-black text-brand-600">{faPrice(item.price)} تومان</p>
                    </div>
                    <div className="flex flex-col items-center gap-1.5">
                      <div className="flex items-center gap-1 rounded-full bg-cream-100 p-0.5">
                        <button onClick={() => updateQty(item.key, 1)} className="flex h-6 w-6 items-center justify-center rounded-full text-coffee-700 hover:bg-white" aria-label={cartDrawer.increaseLabel || "افزایش"}>
                          <Plus className="h-3.5 w-3.5" />
                        </button>
                        <span className="w-4 text-center text-xs font-black">{faNum(item.qty)}</span>
                        <button onClick={() => updateQty(item.key, -1)} className="flex h-6 w-6 items-center justify-center rounded-full text-coffee-700 hover:bg-white" aria-label={cartDrawer.decreaseLabel || "کاهش"}>
                          <Minus className="h-3.5 w-3.5" />
                        </button>
                      </div>
                      <button onClick={() => removeFromCart(item.key)} className="text-coffee-400 transition-colors hover:text-red-500" aria-label={cartDrawer.removeLabel || "حذف"}>
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </motion.div>
                );
              })}
            </div>

            {/* footer */}
            {cart.length > 0 && (
              <div className="space-y-3 border-t border-coffee-900/10 bg-white p-4">
                <p className="rounded-xl bg-brand-50 px-3 py-2 text-center text-[11px] font-bold text-brand-700">
                  {cartDrawer?.announcementText ? (
                    cartDrawer.announcementText.replace("{code}", discountCode)
                  ) : (
                    <>
                      کد تخفیف <span dir="ltr" className="font-extrabold">{discountCode}</span> را در مرحله‌ی پرداخت وارد کنید
                    </>
                  )}
                </p>
                <div className="flex items-center justify-between text-sm font-black">
                  <span>مبلغ نهایی:</span>
                  <span className="text-brand-600">{faPrice(cartTotal)} تومان</span>
                </div>
                <a
                  href="/checkout"
                  className="block w-full rounded-2xl bg-coffee-900 py-4 text-center text-sm font-extrabold text-cream-50 transition-all hover:bg-brand-500 active:scale-[0.98]"
                >
                  {cartDrawer?.checkoutButtonText || "تکمیل خرید و پرداخت"}
                </a>
              </div>
            )}
          </motion.aside>
        </motion.div>
      )}
    </AnimatePresence>
  );
}

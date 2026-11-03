import { motion } from "framer-motion";
import { Star } from "lucide-react";
import { useState } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { useShop } from "../context/ShopContext";
import type { Product } from "../data/products";
import { faNum, faPrice } from "../lib/format";
import { cn } from "../utils/cn";
import { ProductArt } from "./ProductArt";

export type CardLayout = "comfort" | "dense" | "list";

/**
 * ProductCard — MAX's hover language (art zooms, exploring text slides
 * in) wrapped around ARGON's live colorway switcher.
 */
export function ProductCard({ product, layout = "comfort" }: { product: Product; layout?: CardLayout }) {
  const cfg: any = useLandingConfig().catalog;
  const [colorIndex, setColorIndex] = useState(0);
  const { addToCart, openModal } = useShop();
  const colors = product.colors?.length ? product.colors : [{ name: "پیش‌فرض", hex: "#e85d1f", secondary: "#f6efe0" }];
  const cw = colors[colorIndex] || colors[0];
  const sizes = product.sizes?.length ? product.sizes : ["۳۶-۴۰", "۴۱-۴۶"];

  const quickAdd = (e: React.MouseEvent) => {
    e.stopPropagation();
    addToCart({
      name: (cfg?.productNameTemplate || "جوراب {title}").replace("{title}", product.title),
      price: product.price,
      productId: product.id,
      colorIndex,
      color: cw.name,
      size: sizes[0],
    });
  };
  const openDetail = () => openModal("product", { productId: product.id, colorIndex });

  const swatches = (
    <div className="flex items-center gap-1.5">
      {colors.map((c, i) => (
        <button
          key={c.name}
          onClick={(e) => {
            e.stopPropagation();
            setColorIndex(i);
          }}
          onMouseEnter={() => setColorIndex(i)}
          aria-label={c.name}
          title={c.name}
          className={cn(
            "h-5 w-5 rounded-full border-2 transition-all",
            i === colorIndex ? "scale-110 border-coffee-900 shadow" : "border-white shadow-sm hover:scale-110"
          )}
          style={{ backgroundColor: c.hex }}
        />
      ))}
      <span className="ms-1 text-[10px] font-bold text-coffee-500">{cw.name}</span>
    </div>
  );

  /* ---------------- list layout ---------------- */
  if (layout === "list") {
    return (
      <motion.article
        layout
        onClick={openDetail}
        className="group grid cursor-pointer grid-cols-[112px_1fr] items-center gap-4 rounded-3xl border border-coffee-900/10 bg-white p-3 shadow-sm transition-shadow hover:shadow-xl sm:grid-cols-[150px_1fr_auto]"
      >
        <div className="relative overflow-hidden rounded-2xl bg-cream-100 p-2">
          <ProductArt product={product} colorIndex={colorIndex} className="h-24 w-full transition-transform duration-500 group-hover:scale-110 sm:h-32" />
          {product.isNew && (
            <span className="absolute right-1.5 top-1.5 rounded-full bg-brand-500 px-2 py-0.5 text-[9px] font-black text-white">جدید</span>
          )}
        </div>
        <div className="min-w-0">
          <p className="text-[10px] font-bold text-brand-600">{product.categoryLabel}</p>
          <h3 className="mt-0.5 truncate text-sm font-black text-coffee-900 sm:text-base">{product.title}</h3>
          <p className="mt-1 hidden text-[11px] leading-relaxed text-coffee-500 sm:line-clamp-1">{product.desc}</p>
          <div className="mt-2">{swatches}</div>
        </div>
        <div className="col-span-2 flex items-center justify-between gap-3 sm:col-span-1 sm:flex-col sm:items-end">
          <div className="text-end">
            {product.oldPrice && <p className="text-[10px] text-coffee-400 line-through">{faPrice(product.oldPrice)}</p>}
            <p className="text-sm font-black text-coffee-900 sm:text-base">
              {faPrice(product.price)} <span className="text-[10px] font-bold text-coffee-500">تومان</span>
            </p>
          </div>
          <button
            onClick={quickAdd}
            className="rounded-xl bg-coffee-900 px-4 py-2.5 text-[11px] font-extrabold text-cream-50 transition-all hover:bg-brand-500 active:scale-95"
          >
            افزودن به سبد
          </button>
        </div>
      </motion.article>
    );
  }

  /* ---------------- grid layouts ---------------- */
  const dense = layout === "dense";
  return (
    <motion.article
      layout
      onClick={openDetail}
      className="group relative flex cursor-pointer flex-col overflow-hidden rounded-[1.75rem] border border-coffee-900/10 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl"
    >
      {/* art stage */}
      <div className="relative overflow-hidden bg-cream-100 p-4" style={{ aspectRatio: dense ? "1/0.92" : "1/0.98" }}>
        <div className="dot-grid absolute inset-0 opacity-40" />
        <ProductArt
          product={product}
          colorIndex={colorIndex}
          className="relative mx-auto h-full w-full max-w-[220px] transition-transform duration-500 ease-out group-hover:scale-110 group-hover:-rotate-3"
        />

        <span className="absolute right-3 top-3 rounded-full bg-coffee-900/90 px-2.5 py-1 text-[10px] font-extrabold text-cream-50 backdrop-blur">
          {product.badge}
        </span>
        {product.rating >= 4.9 && (
          <span className="absolute left-3 top-3 flex items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-[10px] font-black text-coffee-800 shadow-sm">
            <Star className="h-3 w-3 fill-pop-500 text-pop-500" />
            {faNum(product.rating)}
          </span>
        )}

        {/* hover exploring overlay (MAX) */}
        <div className="absolute inset-x-3 bottom-3 translate-y-[130%] transition-transform duration-400 ease-out group-hover:translate-y-0">
          <div className="flex items-center justify-between rounded-2xl bg-coffee-950/92 px-4 py-3 text-cream-50 backdrop-blur-md">
            <div>
              <p className="text-[11px] font-black">مشاهده سریع</p>
              <p className="text-[10px] text-cream-100/60">{product.features[0]}</p>
            </div>
            <span className="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-lg font-black text-white">+</span>
          </div>
        </div>
      </div>

      {/* body */}
      <div className={cn("flex flex-1 flex-col", dense ? "gap-1.5 p-3" : "gap-2 p-4")}>
        <div className="flex items-center justify-between">
          <span className="text-[10px] font-bold text-brand-600">{product.categoryLabel}</span>
          <span className="text-[10px] text-coffee-400">{faNum(product.reviews)} دیدگاه</span>
        </div>
        <h3 className={cn("font-black text-coffee-900", dense ? "text-sm" : "text-base")}>{product.title}</h3>
        {!dense && swatches}
        <div className="mt-auto flex items-end justify-between pt-1">
          <div>
            {product.oldPrice && <p className="text-[10px] leading-none text-coffee-400 line-through">{faPrice(product.oldPrice)}</p>}
            <p className={cn("font-black text-coffee-900", dense ? "text-sm" : "text-lg")}>
              {faPrice(product.price)} <span className="text-[10px] font-bold text-coffee-500">تومان</span>
            </p>
          </div>
          <button
            onClick={quickAdd}
            aria-label="افزودن به سبد"
            className="rounded-xl bg-coffee-900 px-3.5 py-2.5 text-[11px] font-extrabold text-cream-50 transition-all hover:bg-brand-500 active:scale-95 max-lg:hidden lg:opacity-0 lg:transition-all lg:group-hover:opacity-100"
          >
            + سبد
          </button>
        </div>
      </div>
    </motion.article>
  );
}

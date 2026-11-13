import { AnimatePresence, motion } from "framer-motion";
import { Grid2x2, LayoutGrid, List, PackageSearch, Search, SlidersHorizontal, X } from "lucide-react";
import { useMemo, useState, useEffect } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { categories, products, type CategoryId } from "../data/products";
import { faNum } from "../lib/format";
import { cn } from "../utils/cn";
import { ProductCard, type CardLayout } from "./ProductCard";
import { SectionHeading } from "./primitives";

type SortId = "popular" | "newest" | "cheap" | "expensive";
const sortOptionOrder: { id: SortId; key: "sortPopular" | "sortNewest" | "sortCheap" | "sortExpensive"; label: string }[] = [
  { id: "popular", key: "sortPopular", label: "پرفروش‌ترین" },
  { id: "newest", key: "sortNewest", label: "جدیدترین" },
  { id: "cheap", key: "sortCheap", label: "ارزان‌ترین" },
  { id: "expensive", key: "sortExpensive", label: "گران‌ترین" },
];

/**
 * Catalog — storefront grid with dynamic headings, search, sort, and layout options.
 */
export function Catalog() {
  const { catalog: catalogConfig } = useLandingConfig();
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState<CategoryId>("all");
  const [sort, setSort] = useState<SortId>("popular");
  const [layout, setLayout] = useState<CardLayout>(catalogConfig.defaultLayout || "comfort");

  useEffect(() => {
    if (catalogConfig.defaultLayout) {
      setLayout(catalogConfig.defaultLayout);
    }
  }, [catalogConfig.defaultLayout]);

  const counts = useMemo(() => {
    const c: Record<string, number> = { all: products.length };
    for (const p of products) c[p.category] = (c[p.category] ?? 0) + 1;
    return c;
  }, []);

  const filtered = useMemo(() => {
    let list = products.filter(
      (p) =>
        (category === "all" || p.category === category) &&
        (query.trim() === "" || `${p.title} ${p.categoryLabel} ${p.desc}`.includes(query.trim()))
    );
    switch (sort) {
      case "popular":
        list = [...list].sort((a, b) => a.rank - b.rank);
        break;
      case "newest":
        list = [...list].sort((a, b) => Number(b.isNew) - Number(a.isNew));
        break;
      case "cheap":
        list = [...list].sort((a, b) => a.price - b.price);
        break;
      case "expensive":
        list = [...list].sort((a, b) => b.price - a.price);
    }
    return list;
  }, [query, category, sort]);

  const layoutBtns: { id: CardLayout; icon: typeof LayoutGrid; label: string }[] = [
    { id: "comfort", icon: Grid2x2, label: catalogConfig.viewComfortLabel || "نمای بزرگ" },
    { id: "dense", icon: LayoutGrid, label: catalogConfig.viewDenseLabel || "نمای فشرده" },
    { id: "list", icon: List, label: catalogConfig.viewListLabel || "نمای لیستی" },
  ];

  const showSearch = catalogConfig.showSearch !== false;
  const showSort = catalogConfig.showSort !== false;

  return (
    <section id="catalog" data-lb-section="catalog" className="relative bg-cream-50 py-16 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6">
        <SectionHeading
        field="catalog.title"
          eyebrow={catalogConfig.eyebrow || "گالری محصولات"}
          title={catalogConfig.title || "کاتالوگ محصولات — رنگش را خودت عوض کن"}
          desc={
            catalogConfig.desc ||
            "روی هر کارت می‌توانی رنگ‌بندی طرح را به‌صورت زنده تغییر بدهی، جزئیات را با یک کلیک ببینی و مستقیم به سبد یا باکس اضافه کنی."
          }
        />

        {/* toolbar */}
        <div className="mb-8 rounded-3xl border border-coffee-900/10 bg-white/80 p-4 shadow-sm backdrop-blur">
          <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
            {/* search */}
            {showSearch && (
              <div className="relative flex-1">
                <Search className="absolute right-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-coffee-400" />
                <input
                  id="catalog-search"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  placeholder={catalogConfig.searchPlaceholder || "جستجو بین طرح‌ها… مثلا «نئون» یا «اسپرت»"}
                  className="w-full rounded-2xl border border-coffee-900/10 bg-cream-50 py-3 pl-10 pr-10 text-sm font-bold outline-none transition-all placeholder:font-normal placeholder:text-coffee-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15"
                />
                {query && (
                  <button
                    onClick={() => setQuery("")}
                    aria-label={catalogConfig.clearSearchText || "پاک کردن جستجو"}
                    className="absolute left-3 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full bg-coffee-900/5 text-coffee-500 hover:bg-brand-500 hover:text-white"
                  >
                    <X className="h-3.5 w-3.5" />
                  </button>
                )}
              </div>
            )}

            <div className="flex items-center gap-2.5">
              {/* sort */}
              {showSort && (
                <label className="relative flex flex-1 items-center gap-2 lg:flex-none">
                  <SlidersHorizontal className="pointer-events-none absolute right-3 h-4 w-4 text-coffee-400" />
                  <select
                    value={sort}
                    onChange={(e) => setSort(e.target.value as SortId)}
                    className="w-full cursor-pointer appearance-none rounded-2xl border border-coffee-900/10 bg-cream-50 py-3 pl-4 pr-9 text-xs font-extrabold outline-none focus:border-brand-500 lg:w-44"
                  >
                    {sortOptionOrder.map((o) => (
                      <option key={o.id} value={o.id}>
                        {(catalogConfig as any)[o.key] || o.label}
                      </option>
                    ))}
                  </select>
                </label>
              )}

              {/* layout */}
              <div className="flex rounded-2xl border border-coffee-900/10 bg-cream-50 p-1">
                {layoutBtns.map((b) => (
                  <button
                    key={b.id}
                    onClick={() => setLayout(b.id)}
                    title={b.label}
                    aria-label={b.label}
                    className={cn(
                      "flex h-9 w-9 items-center justify-center rounded-xl transition-all",
                      layout === b.id ? "bg-coffee-900 text-cream-50 shadow" : "text-coffee-400 hover:text-coffee-800"
                    )}
                  >
                    <b.icon className="h-4 w-4" />
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* categories */}
          <div className="no-scrollbar mt-4 flex items-center gap-2 overflow-x-auto border-t border-coffee-900/10 pt-4">
            {categories.map((c) => (
              <button
                key={c.id}
                onClick={() => setCategory(c.id)}
                className={cn(
                  "flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-xs font-extrabold transition-all",
                  category === c.id
                    ? "bg-coffee-900 text-cream-50 shadow-lg shadow-coffee-900/20"
                    : "bg-cream-100 text-coffee-600 hover:bg-brand-100 hover:text-brand-700"
                )}
              >
                {c.label}
                <span
                  className={cn(
                    "rounded-full px-1.5 text-[10px]",
                    category === c.id ? "bg-white/20 text-cream-50" : "bg-coffee-900/10 text-coffee-500"
                  )}
                >
                  {faNum(counts[c.id] ?? 0)}
                </span>
              </button>
            ))}
          </div>
        </div>

        {/* search status */}
        <AnimatePresence>
          {query && (
            <motion.div
              initial={{ opacity: 0, y: -8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              className="mb-6 flex items-center justify-between rounded-2xl border border-brand-500/25 bg-brand-50 px-4 py-3 text-xs font-bold text-brand-800"
            >
              <span className="flex items-center gap-2">
                <PackageSearch className="h-4 w-4" />
                نتایج جستجو برای: <span className="font-black underline">«{query}»</span> — {faNum(filtered.length)} طرح
              </span>
              <button onClick={() => setQuery("")} className="flex items-center gap-1 text-brand-700 hover:underline">
                حذف فیلتر <X className="h-3.5 w-3.5" />
              </button>
            </motion.div>
          )}
        </AnimatePresence>

        {/* grid */}
        {filtered.length > 0 ? (
          <motion.div
            layout
            className={cn(
              "grid gap-4 sm:gap-6",
              layout === "comfort" && "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3",
              layout === "dense" && "grid-cols-2 lg:grid-cols-4",
              layout === "list" && "grid-cols-1"
            )}
          >
            {filtered.map((p) => (
              <ProductCard key={p.id} product={p} layout={layout} />
            ))}
          </motion.div>
        ) : (
          <div className="flex flex-col items-center gap-4 rounded-3xl border-2 border-dashed border-coffee-900/15 py-16 text-center">
            <span className="flex h-16 w-16 items-center justify-center rounded-3xl bg-cream-200 text-coffee-400">
              <PackageSearch className="h-7 w-7" />
            </span>
            <p className="text-sm font-bold text-coffee-600">
              {catalogConfig.emptySearchText || "طراحی با این مشخصات پیدا نشد…"}
            </p>
            <button
              onClick={() => {
                setQuery("");
                setCategory("all");
              }}
              className="rounded-full bg-coffee-900 px-5 py-2.5 text-xs font-extrabold text-cream-50 transition-colors hover:bg-brand-500"
            >
              {catalogConfig.resetSearchText || "نمایش همه‌ی طرح‌ها"}
            </button>
          </div>
        )}

        <p className="mt-8 text-center text-[11px] font-bold text-coffee-400">
          نمایش {faNum(filtered.length)} طرح از {faNum(products.length)} اثر کارگاه
        </p>
      </div>
    </section>
  );
}

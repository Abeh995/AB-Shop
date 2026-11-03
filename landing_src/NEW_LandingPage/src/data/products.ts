export type CategoryId = "all" | "crew" | "sport" | "neon" | "accessory";
export type PatternType = "checker" | "flame" | "wave" | "sport" | "stripes" | "dots" | "minimal";
export type ArtType = "sock" | "beanie" | "pin";

export interface Colorway {
  name: string;
  hex: string;
  /** optional secondary tone used for pattern/panels */
  secondary?: string;
}

export interface Product {
  id: number;
  title: string;
  category: Exclude<CategoryId, "all">;
  categoryLabel: string;
  price: number;
  oldPrice: number | null;
  badge: string;
  rating: number;
  reviews: number;
  rank: number; // popularity rank (1 = best seller)
  isNew: boolean;
  art: ArtType;
  pattern: PatternType;
  colors: Colorway[];
  sizes: string[];
  features: string[];
  tags: string[]; // used by the smart style maker
  desc: string;
  image?: string;
  slug?: string;
}

declare global {
  interface Window {
    __AB_INITIAL_DATA__?: {
      products?: Partial<Product>[];
      categories?: { id: CategoryId; label: string }[];
      cartCount?: number;
      csrfToken?: string;
      store?: Record<string, string>;
    };
  }
}

const defaultCategories: { id: CategoryId; label: string }[] = [
  { id: "all", label: "همه" },
  { id: "crew", label: "ساق‌بلند طرح‌دار" },
  { id: "sport", label: "اسپرت و کالج" },
  { id: "neon", label: "نئون و شب‌نما" },
  { id: "accessory", label: "اکسسوری" },
];

export const categories: { id: CategoryId; label: string }[] =
  typeof window !== "undefined" && window.__AB_INITIAL_DATA__?.categories?.length
    ? window.__AB_INITIAL_DATA__.categories
    : defaultCategories;

const initialProducts: Product[] = [
  {
    id: 9,
    title: "قلم‌موی نارنجی پرنده",
    category: "crew",
    categoryLabel: "ساق‌بلند طرح‌دار",
    price: 148_000,
    oldPrice: 172_000,
    badge: "انتخاب کارگاه",
    rating: 5.0,
    reviews: 321,
    rank: 0,
    isNew: true,
    art: "sock",
    pattern: "flame",
    colors: [
      { name: "نارنجی قلم‌مو", hex: "#e85d1f", secondary: "#f6efe0" },
      { name: "خاکِ سوخته", hex: "#563c28", secondary: "#e9c46a" },
      { name: "کرم بوم", hex: "#e0d0ae", secondary: "#e85d1f" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶"],
    features: ["ضربه‌های قلم‌موی دست‌باف", "امضای کارگاه روی پاشنه", "نخ‌پنبه‌ی درجه‌یک"],
    tags: ["هنری", "استریت", "رنگی", "کتونی"],
    desc: "قطعه‌ی امضادار کارگاه؛ هر جفت با نظم کمی متفاوت بافته می‌شود، مثل یک اثر هنری واقعی.",
  },
  {
    id: 1,
    title: "شطرنجی نئون",
    category: "neon",
    categoryLabel: "نئون و شب‌نما",
    price: 148_000,
    oldPrice: 185_000,
    badge: "۲۰٪ تخفیف",
    rating: 4.9,
    reviews: 164,
    rank: 1,
    isNew: true,
    art: "sock",
    pattern: "checker",
    colors: [
      { name: "بنفش دیپ", hex: "#5b21b6", secondary: "#e9c46a" },
      { name: "نارنجی مرجانی", hex: "#e85d1f", secondary: "#221510" },
      { name: "مشکی متال", hex: "#1e293b", secondary: "#e9c46a" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶"],
    features: ["۸۵٪ نخ‌پنبه شانه‌شده", "پنجه بدون درز", "طرح بافته‌شده بدون چاپ"],
    tags: ["شب", "مهمانی", "رنگی", "کتونی"],
    desc: "محبوب‌ترین طرح ای‌بی ساکس؛ شطرنجی پررنگ که زیر نور شهر هم می‌درخشد.",
  },
  {
    id: 2,
    title: "استریت فلیم",
    category: "crew",
    categoryLabel: "ساق‌بلند طرح‌دار",
    price: 152_000,
    oldPrice: 180_000,
    badge: "پرفروش‌ترین",
    rating: 5.0,
    reviews: 210,
    rank: 2,
    isNew: true,
    art: "sock",
    pattern: "flame",
    colors: [
      { name: "نارنجی آتشین", hex: "#e85d1f", secondary: "#f6bc8f" },
      { name: "مشکی نئون", hex: "#0f172a", secondary: "#ee7634" },
      { name: "بنفش الکتریک", hex: "#7c3aed", secondary: "#f6bc8f" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶"],
    features: ["شعله‌های بافته‌شده", "مچ بدون اثر", "ضدتعریق"],
    tags: ["استریت", "خیابان", "رنگی", "اسنیکرز"],
    desc: "شعله‌های کلاسیک استریت با بافت نخ رنگی؛ دوست‌داشتنی‌ترین همراه کتونی‌های سفید.",
  },
  {
    id: 3,
    title: "موج رترو",
    category: "crew",
    categoryLabel: "ساق‌بلند طرح‌دار",
    price: 138_000,
    oldPrice: 165_000,
    badge: "کالکشن جدید",
    rating: 4.8,
    reviews: 94,
    rank: 4,
    isNew: true,
    art: "sock",
    pattern: "wave",
    colors: [
      { name: "آبی اقیانوسی", hex: "#0284c7", secondary: "#f6efe0" },
      { name: "سبز زمردی", hex: "#059669", secondary: "#f6efe0" },
      { name: "سرخابی پاپ", hex: "#db2777", secondary: "#f6efe0" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶", "فری‌سایز"],
    features: ["الگوی موج دست‌طراحی", "کشسانی ۴ طرفه", "ثبات رنگ بالا"],
    tags: ["روزمره", "آرام", "رنگی", "بوت"],
    desc: "موج‌های نرم رترو، الهام‌گرفته از پوسترهای دهه‌ی شصت؛ ظریف اما پرقدرت.",
  },
  {
    id: 4,
    title: "رانینگ پرو",
    category: "sport",
    categoryLabel: "اسپرت و کالج",
    price: 165_000,
    oldPrice: null,
    badge: "کفی حوله‌ای",
    rating: 4.9,
    reviews: 188,
    rank: 3,
    isNew: false,
    art: "sock",
    pattern: "sport",
    colors: [
      { name: "مشکی کربن", hex: "#18181b", secondary: "#e85d1f" },
      { name: "آبی الکتریک", hex: "#2563eb", secondary: "#f6efe0" },
      { name: "خاکستری تیتانیوم", hex: "#475569", secondary: "#e9c46a" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶"],
    features: ["کفی حوله‌ای ضربه‌گیر", "توری تنفس روی ساق", "آچیل تقویت‌شده"],
    tags: ["ورزش", "دویدن", "رانینگ", "تیره"],
    desc: "مهندسی‌شده برای دویدن‌های طولانی؛ کفی حوله‌ای و قوس پا برای حداکثر تثبیت.",
  },
  {
    id: 5,
    title: "مینیمال خطی",
    category: "sport",
    categoryLabel: "اسپرت و کالج",
    price: 118_000,
    oldPrice: 140_000,
    badge: "ضد لغزش پاشنه",
    rating: 4.7,
    reviews: 72,
    rank: 6,
    isNew: false,
    art: "sock",
    pattern: "minimal",
    colors: [
      { name: "سرمه‌ای کلاسیک", hex: "#1e3a8a", secondary: "#e85d1f" },
      { name: "سبز زیتونی", hex: "#0d9488", secondary: "#f6efe0" },
      { name: "ذغالی", hex: "#334155", secondary: "#e9c46a" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶"],
    features: ["سیلیکون ضدلغزش پاشنه", "برش پایین مچ", "نخ سوپر-نرم"],
    tags: ["روزمره", "مینیمال", "خنثی", "کالج"],
    desc: "برای روزهایی که می‌خواهی سادگی حرف بزند؛ یک خط، یک رنگ، هزار حالت.",
  },
  {
    id: 6,
    title: "سایبر دات",
    category: "neon",
    categoryLabel: "نئون و شب‌نما",
    price: 158_000,
    oldPrice: 190_000,
    badge: "نخ شبرنگ",
    rating: 4.9,
    reviews: 119,
    rank: 5,
    isNew: true,
    art: "sock",
    pattern: "dots",
    colors: [
      { name: "بنفش سایبر", hex: "#4c1d95", secondary: "#e9c46a" },
      { name: "مشکی مات", hex: "#090d16", secondary: "#e85d1f" },
      { name: "نارنجی نئون", hex: "#ea580c", secondary: "#221510" },
    ],
    sizes: ["۳۶-۴۰", "۴۱-۴۶", "فری‌سایز"],
    features: ["نقطه‌های شبرنگ بافته‌شده", "شارژ نوری ۳۰ دقیقه‌ای", "بافت فشرده‌ی ساق"],
    tags: ["شب", "مهمانی", "نئون", "استریت"],
    desc: "در تاریکی بدرخش؛ نقاط شبرنگی که با کمی نور شارژ می‌شوند.",
  },
  {
    id: 7,
    title: "پین اسمایلی استریت",
    category: "accessory",
    categoryLabel: "اکسسوری",
    price: 89_000,
    oldPrice: 115_000,
    badge: "مناسب کتانی و کوله",
    rating: 4.8,
    reviews: 63,
    rank: 8,
    isNew: true,
    art: "pin",
    pattern: "minimal",
    colors: [
      { name: "نارنجی مرجانی", hex: "#e85d1f", secondary: "#f6efe0" },
      { name: "بنفش دیپ", hex: "#5b21b6", secondary: "#e9c46a" },
      { name: "مشکی طلایی", hex: "#1e293b", secondary: "#e9c46a" },
    ],
    sizes: ["فری‌سایز"],
    features: ["آلیاژ ضدحساسیت", "قفل پروانه‌ای فلزی", "لعاب دستی"],
    tags: ["اکسسوری", "هدیه", "کوچک"],
    desc: "امضای کوچک ای‌بی ساکس روی بند کتونی یا بند کوله‌ات.",
  },
  {
    id: 8,
    title: "کلاه فیشرمن ای‌بی",
    category: "accessory",
    categoryLabel: "اکسسوری",
    price: 285_000,
    oldPrice: 330_000,
    badge: "گلدوزی برجسته",
    rating: 4.9,
    reviews: 105,
    rank: 7,
    isNew: true,
    art: "beanie",
    pattern: "stripes",
    colors: [
      { name: "نارنجی استریت", hex: "#e85d1f", secondary: "#291a12" },
      { name: "مشکی ذغالی", hex: "#1e293b", secondary: "#ee7634" },
      { name: "کرم بافت", hex: "#e0d0ae", secondary: "#412c1d" },
    ],
    sizes: ["فری‌سایز"],
    features: ["بافت درشت فیشرمن", "گلدوزی برجسته AB", "لبه‌ی تاشونده"],
    tags: ["زمستان", "استریت", "اکسسوری"],
    desc: "ست کامل جوراب‌های ساق‌بلند؛ بافت دستی درشت با امضای گلدوزی‌شده‌ی ای‌بی.",
  },
];

export function getMergedProducts(): Product[] {
  if (typeof window === "undefined" || !window.__AB_INITIAL_DATA__?.products?.length) {
    return initialProducts;
  }
  const serverList = window.__AB_INITIAL_DATA__.products;
  const merged: Product[] = [];
  const usedIds = new Set<number>();

  for (const s of serverList) {
    if (!s.id) continue;
    usedIds.add(s.id);
    const existing = initialProducts.find((p) => p.id === s.id);
    if (existing) {
      merged.push({
        ...existing,
        title: s.title || existing.title,
        price: s.price ?? existing.price,
        oldPrice: s.oldPrice ?? existing.oldPrice,
        image: s.image || existing.image,
        slug: s.slug || existing.slug,
      });
    } else {
      merged.push({
        id: s.id,
        title: s.title || "جوراب اختصاصی AB",
        category: (s.category as Exclude<CategoryId, "all">) || "crew",
        categoryLabel: s.categoryLabel || "ساق‌بلند طرح‌دار",
        price: s.price || 120_000,
        oldPrice: s.oldPrice ?? null,
        badge: s.badge || "پیشنهاد ویژه",
        rating: s.rating ?? 5.0,
        reviews: s.reviews ?? 42,
        rank: s.rank ?? merged.length + 1,
        isNew: s.isNew ?? true,
        art: (s.art as ArtType) || "sock",
        pattern: (s.pattern as PatternType) || "checker",
        colors: s.colors?.length
          ? s.colors
          : [
              { name: "نارنجی اصلی", hex: "#e85d1f", secondary: "#f6efe0" },
              { name: "مشکی ذغالی", hex: "#221510", secondary: "#e85d1f" },
            ],
        sizes: s.sizes?.length ? s.sizes : ["۳۶-۴۰", "۴۱-۴۶"],
        features: s.features?.length ? s.features : ["۱۰۰٪ نخ‌پنبه شانه‌شده", "پنجه بدون درز"],
        tags: s.tags?.length ? s.tags : ["روزمره", "کتونی"],
        desc: s.desc || "طراحی و بافت باکیفیت کارگاه ای‌بی ساکس.",
        image: s.image,
        slug: s.slug,
      });
    }
  }

  for (const p of initialProducts) {
    if (!usedIds.has(p.id)) {
      merged.push(p);
    }
  }

  return merged;
}

export const products: Product[] = getMergedProducts();

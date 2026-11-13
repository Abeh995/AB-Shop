/* ---------------------------------------------------------------
 * Static content of the storefront. All copy uses the merged tone:
 * artisanal/editorial (MAX) + conversion-focused (ARGON).
 * --------------------------------------------------------------- */

export const navItems: { id: string; label: string; desc: string }[] = [
  { id: "catalog", label: "کاتالوگ", desc: "گالری محصولات با امکان تغییر رنگ زنده روی کارت هر طرح" },
  { id: "pack", label: "باکس‌ساز", desc: "پک اختصاصی خودت رو بچین و تا ۲۰٪ تخفیف بگیر" },
  { id: "story", label: "داستان ما", desc: "از یک کارگاه کوچک بافندگی تا قلب استایل شما" },
  { id: "gallery", label: "گالری حرکت", desc: "جوراب‌ها در حرکت؛ نگاهی به استایل واقعی مشتری‌ها" },
  { id: "size", label: "راهنمای سایز", desc: "سایز دقیق پات رو در ۳ ثانیه پیدا کن" },
];

/* Hero trust chips (ARGON "live boxes" copy) */
export const heroChips = ["۱۰۰٪ نخ ارگانیک", "بدون پرز و رنگ‌دهی"];

/* Trust bar (MAX copy) */
export const trustItems: { icon: "feather" | "refresh" | "truck" | "gift" | "shield"; title: string; desc: string }[] = [
  { icon: "feather", title: "۱۰۰٪ نخ‌پنبه", desc: "ضدحساسیت و نرم" },
  { icon: "refresh", title: "۷ روز تعویض", desc: "بدونِ ذره‌ای سؤال" },
  { icon: "truck", title: "ارسالِ سراسری", desc: "به تمامِ نقاطِ ایران" },
  { icon: "gift", title: "بسته‌بندیِ هدیه", desc: "آماده‌ی کادو دادن" },
  { icon: "shield", title: "پرداخت امن", desc: "درگاه رسمی شتاب" },
];

export const marqueeBottom = "ادامه بده، پایین‌تر قشنگ‌تره";
export const marqueeStory = "با عشق بافته شده";
export const marqueeGallery = "گالری حرکت";

/* Pack builder types */
export interface PackType {
  id: string;
  title: string;
  count: number;
  discount: number; // percent
  tagline: string;
  perks: string[];
  featured?: boolean;
}

export const packTypes: PackType[] = [
  {
    id: "daily",
    title: "پک روزمره",
    count: 3,
    discount: 10,
    tagline: "سه جفت برای آغاز هفته",
    perks: ["۱۰٪ تخفیف باندل", "بسته‌بندی کرافت"],
  },
  {
    id: "week",
    title: "پک هفته",
    count: 5,
    discount: 15,
    tagline: "هر روز هفته، یه حال‌وهوا",
    perks: ["۱۵٪ تخفیف باندل", "ارسال رایگان", "استیکر سورپرایز"],
    featured: true,
  },
  {
    id: "gift",
    title: "پک هدیه لاکچری",
    count: 4,
    discount: 20,
    tagline: "برای کسی که دوسش داری",
    perks: ["۲۰٪ تخفیف باندل", "باکس چوبی + روبان", "کارت تبریک دست‌نویس رایگان"],
  },
];

export const packBoxOptions = [
  "باکس کرافت مینیمال + روبان نارنجی",
  "باکس استریت + پک استیکر",
  "باکس کادویی تولد + کارت پستال",
];
export const packSizeOptions = ["سایز ۳۶ تا ۴۰", "سایز ۴۱ تا ۴۶", "فری‌سایز مخلوط"];

/* Lucky wheel prizes (8 slices) */
export interface WheelPrize {
  label: string;
  code: string | null;
}
export const wheelPrizes: WheelPrize[] = [
  { label: "۱۰٪ تخفیف", code: "ABSPIN10" },
  { label: "پوچ! دوباره شانست رو امتحان کن فردا", code: null },
  { label: "ارسال رایگان", code: "ABFREEPOST" },
  { label: "۱۵٪ تخفیف", code: "ABSPIN15" },
  { label: "جوراب رایگان با پک هدیه", code: "ABGIFT1" },
  { label: "۵٪ تخفیف", code: "ABSPIN5" },
  { label: "کارت هدیه ۵۰ هزارتایی", code: "ABCARD50" },
  { label: "۲۵٪ تخفیف شگفت‌انگیز", code: "ABSPIN25" },
];

/* FAQ */
export const faqs: { q: string; a: string }[] = [
  {
    q: "جوراب‌ها بعد از شستشو آب می‌روند یا پرز می‌دهند؟",
    a: "خیر؛ به دلیل استفاده از نخ‌پنبه شانه‌شده (Combed Cotton) و شستشوی اولیه صنعتی، در صورت شستشو با آب ۳۰ درجه هیچ آب‌رفت یا پرزدهی نخواهید دید. خشک کردن در سایه توصیه می‌شود.",
  },
  {
    q: "چطور از تخفیف باکس‌ساز استفاده کنم؟",
    a: "کافی‌ست نوع پک را انتخاب کنید و محصولات دلخواه را به باکس اضافه کنید؛ تخفیف به‌صورت خودکار روی مبلغ نهایی اعمال می‌شود و نیازی به کد نیست.",
  },
  {
    q: "شرایط تعویض ۷ روزه چیست؟",
    a: "تا ۷ روز پس از تحویل، اگر جوراب را استفاده نکرده باشید، بدون هیچ سؤالی تعویض یا مرجوع می‌شود. هزینه ارسال مرجوعی با ماست.",
  },
  {
    q: "ارسال به شهرستان چقدر طول می‌کشد؟",
    a: "سفارش‌های تهران معمولاً همان روز تا ۲۴ ساعت و سفارش‌های شهرستان با پست پیشتاز ۲ تا ۴ روز کاری می‌رسند. کد رهگیری بلافاصله برایتان پیامک می‌شود.",
  },
  {
    q: "بین سایز ۳۶-۴۰ و ۴۱-۴۶ کدام را انتخاب کنم؟",
    a: "از «راهنمای هوشمند سایز» بالای صفحه استفاده کنید؛ با وارد کردن شماره کفش اروپایی‌تان، سایز دقیق پیشنهاد می‌شود. ۳٪ الاستین بافت باعث می‌شود جوراب کاملاً قالب پا بگیرد.",
  },
];

/* Technical details (ARGON "تفاوت در جزئیات فنی") */
export const techCards: { icon: "leaf" | "foot" | "palette"; title: string; desc: string }[] = [
  {
    icon: "leaf",
    title: "۸۵٪ نخ‌پنبه سوپر شانه‌شده",
    desc: "الیاف طبیعی تنفس‌پذیر که مانع تعریق و بوی نامطبوع در استفاده طولانی روزانه و ورزشی می‌شود.",
  },
  {
    icon: "foot",
    title: "پنجه بدون درز (Seamless Toe)",
    desc: "حذف دوخت برجسته روی انگشتان برای راحتی صددرصدی داخل کتانی‌های تنگ و کفش‌های روزمره.",
  },
  {
    icon: "palette",
    title: "طرح‌های بافته‌شده ماندگار",
    desc: "برخلاف جوراب‌های چاپی که با کش آمدن سفید می‌شوند، تمام طرح‌های ما با نخ رنگی بافته شده‌اند.",
  },
];

export const comparisonRows: { label: string; ab: string; other: string }[] = [
  { label: "پنجه‌ی جوراب", ab: "دوخت دستی بدون درز", other: "دوخت برجسته‌ی صنعتی" },
  { label: "الیاف", ab: "۸۵٪ پنبه شانه‌شده + ۳٪ الاستین", other: "پلی‌استر ارزان" },
  { label: "ثبات رنگ", ab: "نخ رنگی بافته‌شده", other: "چاپ سطحی پاک‌شونده" },
  { label: "مچ", ab: "کشباف نرم بدون اثر", other: "کش تنگ و آزاردهنده" },
  { label: "ضمانت", ab: "۷ روز تعویض بی‌قیدوشرط", other: "بدون ضمانت" },
];

/* Social UGC */
export const socialPosts: { img: string; handle: string }[] = [
  {
    img: "https://images.pexels.com/photos/18054296/pexels-photo-18054296.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    handle: "@setareh.wears",
  },
  {
    img: "https://images.pexels.com/photos/38950161/pexels-photo-38950161.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    handle: "@pooya.st",
  },
  {
    img: "https://images.pexels.com/photos/11919823/pexels-photo-11919823.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    handle: "@nikan.daily",
  },
  {
    img: "https://images.pexels.com/photos/12201670/pexels-photo-12201670.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    handle: "@mahsa.fit",
  },
  {
    img: "https://images.pexels.com/photos/4827260/pexels-photo-4827260.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=940",
    handle: "@arta.style",
  },
];

/* Motion gallery */
export const galleryImages: { img: string; caption: string }[] = [
  {
    img: "https://images.pexels.com/photos/10535908/pexels-photo-10535908.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
    caption: "کنار آب، لبه‌ی تابستان",
  },
  {
    img: "https://images.pexels.com/photos/8260232/pexels-photo-8260232.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
    caption: "سه نفره، سه حال‌وهوا",
  },
  {
    img: "https://images.pexels.com/photos/4826711/pexels-photo-4826711.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=627&w=1200",
    caption: "جمعه‌ی خونه، جورابِ رنگی",
  },
  {
    img: "https://images.pexels.com/photos/12739960/pexels-photo-12739960.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    caption: "استریت یعنی این",
  },
  {
    img: "https://images.pexels.com/photos/5741063/pexels-photo-5741063.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940",
    caption: "دو قدم جلوتر از روال",
  },
];

/* Story stats */
export const storyStats: { value: string; label: string }[] = [
  { value: "۹", label: "رنگ طبیعیِ ثابت" },
  { value: "۱۲", label: "مرحله‌ی کنترل کیفیت" },
  { value: "۴٬۸۰۰+", label: "پای خوشحال تا امروز" },
];

/* Contact */
export const contact = {
  phone: "۰۲۱-۹۱۰۰۸۴۲۰",
  hours: "شنبه تا پنجشنبه، ۹ صبح تا ۶ عصر",
  instagram: "absocks.ir",
};

export const DISCOUNT_CODE = "ABSOCKS20";

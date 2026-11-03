import React, { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import {
  contact,
  faqs,
  galleryImages,
  heroChips,
  marqueeBottom,
  marqueeStory,
  packBoxOptions,
  packSizeOptions,
  packTypes,
  socialPosts,
  storyStats,
  techCards,
  comparisonRows,
  trustItems,
  wheelPrizes,
  DISCOUNT_CODE,
} from "../data/site";

export interface LandingCustomizerConfig {
  version: string;
  topStrip: {
    enabled: boolean;
    announcement: string;
    freeShippingThreshold: number;
    couponBadge: string;
    giftPackagingText: string;
    trackOrderText: string;
    copyToast?: string;
    codeLabel?: string;
    copyHint?: string;
  };
  header: {
    logoTitle: string;
    logoSubtitle: string;
    searchEnabled: boolean;
    trackEnabled: boolean;
    cartEnabled: boolean;
    menuLabel?: string;
    menuCloseLabel?: string;
    homeLabel?: string;
    deckHomeLabel?: string;
    deckMenuLabel?: string;
    searchLabel?: string;
    faqLabel?: string;
    deckShopLabel?: string;
    deckWheelLabel?: string;
    navLinks: Array<{ id: string; label: string; desc: string; enabled?: boolean; target?: string }>;
  };
  hero: {
    badgeText: string;
    badgeTag: string;
    headlineMain: string;
    headlineHighlight: string;
    underlinePreset: "wavy" | "brush" | "loop" | "arc" | "straight" | "custom";
    customUnderlinePath?: string;
    underlineColor: string;
    subtitle: string;
    ctaPrimaryLabel: string;
    ctaPrimaryLink: string;
    ctaSecondaryLabel: string;
    ctaSecondaryLink: string;
    ctaSecondaryEnabled?: boolean;
    marqueeText: string;
    parallaxEnabled: boolean;
    parallaxIntensity: number;
    dotGridOpacity: number;
    trustChips: string[];
    productBadge?: string;
    productTag?: string;
    productImage?: string;
    productCollection?: string;
    productPrice?: number;
    productButtonText?: string;
    showFeaturedCard?: boolean;
    stampText?: string;
    stampIcon?: string;
    stampEnabled?: boolean;
    miniTrustStats?: Array<{ icon: string; title: string; subtitle: string }>;
  };
  trustBar: {
    enabled: boolean;
    items: Array<{
      id: string;
      icon: "feather" | "refresh" | "truck" | "gift" | "shield" | "sparkles" | "heart" | "zap" | "award";
      title: string;
      desc: string;
      active: boolean;
    }>;
    marqueeText: string;
    marqueeSubText?: string;
    marqueeSpeed: number;
  };
  catalog: {
    eyebrow?: string;
    title?: string;
    desc?: string;
    searchPlaceholder?: string;
    defaultLayout: "comfort" | "dense" | "list";
    showSearch: boolean;
    showSort: boolean;
    discountBadgeThreshold: number;
    defaultColorIndex: number;
    categoryTabsOrder: string[];
    emptySearchText?: string;
    resetSearchText?: string;
    sortPopular?: string;
    sortNewest?: string;
    sortCheap?: string;
    sortExpensive?: string;
    viewComfortLabel?: string;
    viewDenseLabel?: string;
    viewListLabel?: string;
    clearSearchText?: string;
    productNameTemplate?: string;
  };
  visualEffects: {
    motionGalleryColors: string[];
    motionGallerySpeed: number;
    motionGalleryAngle: number;
    dotGridDensity: "low" | "medium" | "high" | "disabled";
    ambientGlowEnabled: boolean;
    animationStyle: "spring" | "smooth";
  };
  motionGallery: {
    eyebrow?: string;
    title?: string;
    desc?: string;
    marqueeText?: string;
    marqueeSubText?: string;
    ctaStyleQuizText?: string;
    ctaLuckyWheelText?: string;
    images?: Array<{ img: string; caption: string }>;
  };
  packBuilder: {
    enabled: boolean;
    eyebrow?: string;
    title?: string;
    desc?: string;
    autoFillLabel?: string;
    step1Heading?: string;
    tiers: Array<{
      id: string;
      title: string;
      count: number;
      discount: number;
      tagline: string;
      perks: string[];
      featured?: boolean;
    }>;
    boxOptions: string[];
    sizeOptions: string[];
    capacityText?: string;
    trayText?: string;
    summaryText?: string;
    notePrefix?: string;
    notePlaceholder?: string;
    addButtonText?: string;
    remainingText?: string;
    removeLabel?: string;
  };
  socialStyle: {
    enabled: boolean;
    eyebrow?: string;
    title?: string;
    desc?: string;
    boxTitle?: string;
    boxDesc?: string;
    tagButtonLabel?: string;
    storyCountNote?: string;
    joinTileText?: string;
    posts?: Array<{ img: string; handle: string }>;
    tagCopiedToast?: string;
    tagPrompt?: string;
    tagCopyLabel?: string;
    postAlt?: string;
  };
  sizeGuide: {
    enabled: boolean;
    eyebrow?: string;
    title?: string;
    desc?: string;
    smallTip?: string;
    largeTip?: string;
    tableRows?: Array<{ eu: string; cm: string; ab: string }>;
    steps?: string[];
    shoeSizeInputLabel?: string;
  };
  luckyWheel: {
    enabled: boolean;
    title?: string;
    subtitle?: string;
    slices: Array<{
      label: string;
      code: string | null;
      color: string;
      weight: number;
    }>;
    spinButtonLabel: string;
    cooldownHours: number;
    winTitle?: string;
    winCopyBtn?: string;
    copiedToast?: string;
    codeLabel?: string;
    spinningText?: string;
    spinAgainText?: string;
  };
  styleMaker: {
    enabled: boolean;
    title?: string;
    subtitle?: string;
    resultTitle?: string;
    resultAddToCart?: string;
    reasonTemplate?: string;
    workshopNote?: string;
    steps: Array<{
      title: string;
      options: Array<{
        label: string;
        tag: string;
        icon: string;
      }>;
    }>;
  };
  trackModal: {
    title?: string;
    subtitle?: string;
    inputPlaceholder?: string;
    searchButtonText?: string;
    timelineSteps: Array<{
      label: string;
      desc: string;
      icon: string;
      state: "done" | "active" | "next";
    }>;
    supportPhone: string;
    inputHint?: string;
  };
  cartDrawer: {
    title?: string;
    freeShippingThreshold: number;
    announcementText: string;
    discountCode: string;
    emptyTitle?: string;
    emptyButtonText?: string;
    checkoutButtonText?: string;
    closeLabel?: string;
    freeShippingRemaining?: string;
    freeShippingReached?: string;
    colorPrefix?: string;
    sizePrefix?: string;
    increaseLabel?: string;
    decreaseLabel?: string;
    removeLabel?: string;
  };
  editorial: {
    story: {
      marquee: string;
      marqueeSub?: string;
      eyebrow: string;
      title: string;
      image?: string;
      locationTitle?: string;
      locationSubtitle?: string;
      craftTitle?: string;
      ctaLabel?: string;
      ctaLink?: string;
      paragraphs: string[];
      stats: Array<{ value: string; label: string }>;
    };
    promiseBox: {
      enabled?: boolean;
      eyebrow: string;
      title: string;
      body: string;
      bullets: string[];
      stampText: string;
      stampIcon?: string;
    stampEnabled?: boolean;
      image?: string;
      ctaLabel?: string;
      ctaLink?: string;
      bottomMarquee?: string;
    };
    techTrust: {
      enabled?: boolean;
      eyebrow: string;
      title: string;
      desc: string;
      tableHeaders?: { label: string; ab: string; other: string };
      techCards: Array<{ icon: "leaf" | "foot" | "palette"; title: string; desc: string }>;
      comparisonRows: Array<{ label: string; ab: string; other: string }>;
      trustBadges?: Array<{ icon: string; title: string; sub: string; msg: string }>;
      otherColumnLabel?: string;
    };
    faq: {
      eyebrow: string;
      title: string;
      items: Array<{ q: string; a: string }>;
      supportBox?: {
        enabled?: boolean;
        title: string;
        hours: string;
        responseTime: string;
        chatBtnText: string;
        phone: string;
        chatUnavailableText?: string;
      };
    };
    footer: {
      tagline: string;
      brandTitle?: string;
      brandSubtitle?: string;
      socialLinks: { instagram?: string; telegram?: string };
      copyright: string;
      sloganEnglish?: string;
      newsletterTitle?: string;
      newsletterPlaceholder?: string;
      newsletterBtnText?: string;
      legalTitle?: string;
      legalSub?: string;
      legalText?: string;
      gatewayLabel?: string;
      secureTitle?: string;
      secureDesc?: string;
      unionLabel?: string;
      unionSub?: string;
      unionText?: string;
      instagramLabel?: string;
      telegramLabel?: string;
      newsletterNote?: string;
      trustTitle?: string;
      trustSub?: string;
      trustText?: string;
      copyrightText?: string;
      /* design controls — layout */
      layoutPreset?: string;
      contentWidth?: number;
      align?: string;
      columnGap?: number;
      paddingTop?: number;
      paddingBottom?: number;
      showTopDivider?: boolean;
      bottomAlign?: string;
      /* design controls — appearance */
      backgroundStyle?: string;
      backgroundFrom?: string;
      backgroundTo?: string;
      textColor?: string;
      mutedOpacity?: number;
      accentColor?: string;
      buttonColor?: string;
      borderColor?: string;
      dividerOpacity?: number;
      cardRadius?: number;
      decorStyle?: string;
      /* design controls — blocks */
      showBrand?: boolean;
      showSocial?: boolean;
      showLinks?: boolean;
      showNewsletter?: boolean;
      showBadges?: boolean;
      showSlogan?: boolean;
      showCopyright?: boolean;
      showBackToTop?: boolean;
      logoSize?: number;
      socialShape?: string;
      socialSize?: number;
      badgeLayout?: string;
      badgeStyle?: string;
      newsletterStyle?: string;
      /* link columns */
      col1Enabled?: boolean;
      col1Title?: string;
      col1Links?: string[];
      col2Enabled?: boolean;
      col2Title?: string;
      col2Links?: string[];
      col3Enabled?: boolean;
      col3Title?: string;
      col3Links?: string[];
      /* trust badges */
      badge1Enabled?: boolean;
      badge1Icon?: string;
      badge2Enabled?: boolean;
      badge2Icon?: string;
      badge3Enabled?: boolean;
      badge3Icon?: string;
      badge4Enabled?: boolean;
      badge4Icon?: string;
      backToTopText?: string;
    };
  };
  seo?: { title: string; description: string; ogImage?: string };
  sectionOrder: Array<{
    id: string;
    label: string;
    enabled: boolean;
  }>;
}

export const defaultLandingConfig: LandingCustomizerConfig = {
  version: "1.39.1",
  topStrip: {
    enabled: true,
    announcement: "ارسال رایگان سفارش‌های بالای ۵۰۰ هزار تومان",
    freeShippingThreshold: 500000,
    couponBadge: "ABSOCKS20",
    giftPackagingText: "بسته‌بندی هدیه رایگان",
    trackOrderText: "پیگیری سفارش",
  },
  header: {
    logoTitle: "ای‌بی ساکس",
    logoSubtitle: "AB SOCKS",
    searchEnabled: true,
    trackEnabled: true,
    cartEnabled: true,
    navLinks: [
      { id: "catalog", label: "کاتالوگ", desc: "گالری محصولات با امکان تغییر رنگ زنده روی کارت هر طرح" },
      { id: "pack", label: "باکس‌ساز", desc: "پک اختصاصی خودت رو بچین و تا ۲۰٪ تخفیف بگیر" },
      { id: "story", label: "داستان ما", desc: "از یک کارگاه کوچک بافندگی تا قلب استایل شما" },
      { id: "gallery", label: "گالری حرکت", desc: "جوراب‌ها در حرکت؛ نگاهی به استایل واقعی مشتری‌ها" },
      { id: "size", label: "راهنمای سایز", desc: "سایز دقیق پات رو در ۳ ثانیه پیدا کن" },
    ],
  },
  hero: {
    badgeText: "کالکشن جدید ۲۰۲۵ ای‌بی‌ساکس",
    badgeTag: "NEW DROP",
    headlineMain: "هر قدم،",
    headlineHighlight: "یه اثر هنریه",
    underlinePreset: "wavy",
    customUnderlinePath: "M4 10 C 70 2 220 2 296 8",
    underlineColor: "#f97316",
    subtitle:
      "در کارگاه ای‌بی ساکس هر جفت جوراب با ۸۵٪ نخ‌پنبه‌ی شانه‌شده، پنجه‌ی بدون درز و طرح‌هایی که با نخ رنگی «بافته» شده‌اند به دنیا می‌آید؛ چون باور داریم استایل از قدم اول ساخته می‌شود.",
    ctaPrimaryLabel: "دیدن گالری محصولات",
    ctaPrimaryLink: "#catalog",
    ctaSecondaryLabel: "باکس‌ساز اختصاصی (تا ۲۰٪ تخفیف)",
    ctaSecondaryLink: "#pack",
    marqueeText: "AB SOCKS — AB SOCKS",
    parallaxEnabled: true,
    parallaxIntensity: 1.0,
    dotGridOpacity: 0.6,
    trustChips: heroChips,
    productBadge: "قلم‌موی نارنجی پرنده",
    productTag: "ART PIECE 01",
    productImage: "/assets/img/landing/hero-sock.jpg",
    productCollection: "مجموعه آثار دست‌باف",
    productPrice: 148000,
    productButtonText: "افزودن به سبد",
    stampText: "AB SOCKS • هر قدم یه اثر هنریه •",
    stampIcon: "Feather",
    miniTrustStats: [
      { icon: "Feather", title: "۸۵٪ نخ‌پنبه", subtitle: "ضدتعریق وحساسیت" },
      { icon: "Palette", title: "طرح بافته‌شده", subtitle: "تضمین ثبات رنگ" },
      { icon: "Truck", title: "ارسال سریع", subtitle: "پک کادویی + استیکر" },
    ],
  },
  trustBar: {
    enabled: true,
    items: trustItems.map((t, idx) => ({
      id: "tb" + idx,
      icon: t.icon,
      title: t.title,
      desc: t.desc,
      active: true,
    })),
    marqueeText: marqueeBottom,
    marqueeSubText: "KEEP SCROLLING",
    marqueeSpeed: 20,
  },
  catalog: {
    eyebrow: "گالری محصولات",
    title: "کاتالوگ محصولات — رنگش را خودت عوض کن",
    desc: "روی هر کارت می‌توانی رنگ‌بندی طرح را به‌صورت زنده تغییر بدهی، جزئیات را با یک کلیک ببینی و مستقیم به سبد یا باکس اضافه کنی.",
    searchPlaceholder: "جستجو بین طرح‌ها… مثلا «نئون» یا «اسپرت»",
    defaultLayout: "comfort",
    showSearch: true,
    showSort: true,
    discountBadgeThreshold: 10,
    defaultColorIndex: 0,
    categoryTabsOrder: ["all", "crew", "sport", "neon", "accessory"],
    emptySearchText: "طراحی با این مشخصات پیدا نشد…",
    resetSearchText: "نمایش همه‌ی طرح‌ها",
  },
  visualEffects: {
    motionGalleryColors: ["#221510", "#3d2113", "#243022", "#2c1a24", "#221510"],
    motionGallerySpeed: 30,
    motionGalleryAngle: -2,
    dotGridDensity: "medium",
    ambientGlowEnabled: true,
    animationStyle: "spring",
  },
  motionGallery: {
    eyebrow: "نمایشگاه کوچکِ قدم‌های شما",
    title: "گالری حرکت",
    desc: "جوراب قرار نیست یکجا بنشیند؛ اسکرول کن تا قطره‌رنگ کارگاه هم با تو حرکت کند و استایل‌های واقعی مشتری‌ها را ببینی.",
    marqueeText: "گالری حرکت",
    marqueeSubText: "MOTION GALLERY",
    ctaStyleQuizText: "استایل‌ساز هوشمند: سلیقه‌ات را کشف کن",
    ctaLuckyWheelText: "چرخاندن گردونه شانس",
    images: galleryImages,
  },
  packBuilder: {
    enabled: true,
    eyebrow: "باکس‌ساز اختصاصی",
    title: "پک خودت را بچین، تخفیفش را بردار",
    desc: "٣ مدل باکس با ۳ سطح تخفیف؛ محصولات را به «باکس انتخابی» بینداز، کارت تبریک دست‌نویس رایگان بنویس و خلاصه‌ی پک اختصاصی‌ات را زنده ببین.",
    autoFillLabel: "چیدمان هوشمند تصادفی",
    step1Heading: "روی محصولات بزن تا وارد باکس شوند:",
    tiers: packTypes,
    boxOptions: packBoxOptions,
    sizeOptions: packSizeOptions,
  },
  socialStyle: {
    enabled: true,
    eyebrow: "کمپین مشتری‌های واقعی",
    title: "#ABSOCKS_STYLE",
    desc: "استایلت با جوراب ای‌بی ساکس، عضو بعدی دیوار شهرت کارگاهی است.",
    boxTitle: "عکس پات رو با جوراب ای‌بی ساکس استوری کن!",
    boxDesc: "ما رو تگ کن و یک کد تخفیف ۳۰٪ بدون محدودیت به‌عنوان هدیه در دایرکت دریافت کن. هر جمعه، قشنگ‌ترین استایل روی صفحه‌ی کارگاه بازنشر می‌شود.",
    tagButtonLabel: "کپی تگ @absocks.ir",
    storyCountNote: "بیش از ۱٬۲۰۰ استوری تا امروز",
    joinTileText: "تو هم بیا تو گالری",
    posts: socialPosts,
  },
  sizeGuide: {
    enabled: true,
    eyebrow: "راهنمای هوشمند سایز ای‌بی ساکس",
    title: "سایز دقیق پات رو در ۳ ثانیه پیدا کن!",
    desc: "شماره‌ی کفش اروپایی (EU) خودت رو با اسلایدر انتخاب کن تا طول کف پا و سایز پیشنهادی ما را لحظه‌ای ببینی.",
    smallTip: "با ۳٪ الاستینِ بافت، سایز ۳۶-۴۰ دقیقا قالب پای تو می‌شیند و دور مچ جمع نمی‌شود.",
    largeTip: "سایز ۴۱-۴۶ با کشباف نرم خودش را از قوزک تا ساق به فرم پای تو می‌چسباند — بدون اثر کش.",
    tableRows: [
      { eu: "۳۵ تا ۳۸", cm: "۲۲ - ۲۴", ab: "۳۶-۴۰" },
      { eu: "۳۹ تا ۴۰", cm: "۲۴.۵ - ۲۵.۵", ab: "۳۶-۴۰" },
      { eu: "۴۱ تا ۴۳", cm: "۲۶ - ۲۷.۵", ab: "۴۱-۴۶" },
      { eu: "۴۴ تا ۴۶", cm: "۲۸ - ۲۹.۵", ab: "۴۱-۴۶" },
    ],
  },
  luckyWheel: {
    enabled: true,
    title: "گردونه شانس ای‌بی‌ساکس",
    subtitle: "بچرخون و روی سفارش امروزت تخفیف یا جایزه بگیر!",
    slices: wheelPrizes.map((p, i) => ({
      label: p.label,
      code: p.code,
      color: ["#e85d1f", "#f6efe0", "#412c1d", "#fbdcc3"][i % 4],
      weight: [4, 2, 4, 4, 2, 4, 1, 1][i] ?? 3,
    })),
    spinButtonLabel: "گردونه شانس رو بچرخون!",
    cooldownHours: 24,
    winTitle: "مبارکه!",
    winCopyBtn: "کپی کد تخفیف",
  },
  styleMaker: {
    enabled: true,
    title: "استایل‌ساز هوشمند",
    subtitle: "در ۳ سوال سلیقه‌ات را کشف کن و جوراب پیشنهادی‌ات را ببین",
    resultTitle: "طرح پیشنهادی هوش کارگاه برای استایل تو:",
    resultAddToCart: "افزودن به سبد",
    steps: [
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
    ],
  },
  trackModal: {
    title: "پیگیری سفارش",
    subtitle: "کدی که پیامک شده را این‌جا وارد کن",
    inputPlaceholder: "مثلا: AB-۸۴۲۱",
    searchButtonText: "استعلام وضعیت",
    timelineSteps: [
      { label: "ثبت سفارش", desc: "سفارش‌ات در کارگاه ثبت شد", icon: "ShoppingBag", state: "done" },
      { label: "بسته‌بندی هنری", desc: "کارت دست‌نویس نوشته شد و باکس چیده شد", icon: "Scissors", state: "done" },
      { label: "تحویل به پست", desc: "مرسوله به پست پیشتاز تحویل داده شد", icon: "Send", state: "active" },
      { label: "در راهِ شهر تو", desc: "مرسوله در مسیر است", icon: "Truck", state: "next" },
      { label: "تحویل و لبخند", desc: "به دستت می‌رسد", icon: "CheckCircle2", state: "next" },
    ],
    supportPhone: contact.phone,
  },
  cartDrawer: {
    title: "سبد خرید",
    freeShippingThreshold: 500000,
    announcementText: "ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان",
    discountCode: DISCOUNT_CODE,
    emptyTitle: "سبدت فعلا خالیه…",
    emptyButtonText: "بریم گالری محصولات",
    checkoutButtonText: "تکمیل خرید و پرداخت",
  },
  editorial: {
    story: {
      marquee: marqueeStory,
      marqueeSub: "HAND-KNITTED WITH LOVE",
      eyebrow: "داستان ما",
      title: "از یک دستگاهِ بافتِ قدیمی\nتا قلبِ استایلِ شما",
      image: "/assets/img/landing/story.jpg",
      locationTitle: "کارگاه ای‌بی — تهران، بازار بزرگ",
      locationSubtitle: "از پاییز ۱۳۹۸، قدم به قدم با شما",
      craftTitle: "دوخت نهایی با دست",
      ctaLabel: "دیدن جوراب‌ها در حرکت",
      ctaLink: "#gallery",
      paragraphs: [
        "ای‌بی ساکس با یک دستگاه بافت دست‌دوم و یک باور ساده شروع شد: «جوراب، مچاله‌شده‌ترین تکه‌ی استایل نباید باشد.» اولین جفت قلم‌موی نارنجی را در یک اتاقِ هشت‌متری با نخ‌های رنگیِ بازار بافتیم و همان روز فهمیدیم چه بازی‌ای شروع شده.",
        "امروز، کنار هر دستگاه، هنوز یک جفت چشم و یک جفت دست هست که تک‌تک جفت‌ها را ور می‌دارد؛ پنجه‌ها را با دست می‌دوزد و رنگ‌ها را زیر نور آفتاب چک می‌کند. این کُندی، عیب نیست — امضای ماست.",
      ],
      stats: storyStats,
    },
    promiseBox: {
      eyebrow: "ضمانتِ امضاشده‌ی ای‌بی ساکس",
      title: "قولِ دوست‌داشتن، تضمینی",
      body: "ما اینقدر به بافتمون مطمئنیم که قسمت ماجراجوییش با ماست: جوراب‌ها رو بپوش، بشور، قدم بزن… اگر حس نکردی بهترین جورابیه که تا حالا داشتی، بدون حتی یه سؤال پسش می‌گیریم.",
      bullets: [
        "۷ روز تعویض یا بازگشت وجه، بدون هیچ سؤالی",
        "هزینه‌ی ارسال مرجوعی هم با ما",
        "ضمانت دوام بافت تا ۱۲ ماه",
      ],
      stampText: "۷ روز ضمانت تعویض • بدون سؤال •",
      stampIcon: "RefreshCcw",
      image: "/assets/img/landing/gift-box.jpg",
      ctaLabel: "خرید با خیال راحت",
      ctaLink: "#catalog",
      bottomMarquee: "تعویض آسان ✺ بازگشت وجه کامل ✺ ضمانت دوام بافت",
    },
    techTrust: {
      eyebrow: "تفاوت در جزئیات فنی",
      title: "چرا ای‌بی ساکس انتخاب اول استریت‌پوش‌هاست؟",
      desc: "همه‌چیز به چیزی برمی‌گردد که با چشم دیده نمی‌شود: نخ، دوخت و وسواس کارگاه.",
      tableHeaders: {
        label: "جزئیات فنی",
        ab: "جوراب ای‌بی ساکس",
        other: "سایر جوراب‌های بازار",
      },
      techCards: techCards,
      comparisonRows: comparisonRows,
      trustBadges: [
        { icon: "ShieldCheck", title: "اینماد رسمی", sub: "دو ستاره — مركز توسعه تجارت الكترونیكی", msg: "نماد اعتماد الکترونیکی (اینماد) فروشگاه ای‌بی ساکس فعال و معتبر است." },
        { icon: "BadgeCheck", title: "ساماندهی", sub: "نشان ملی ثبت رسانه‌های دیجیتال", msg: "نشان ملی ثبت رسانه‌های دیجیتال ثبت و تایید شده است." },
        { icon: "CreditCard", title: "درگاه امن شتاب", sub: "پرداخت رمزنگاری‌شده بانکی", msg: "پرداخت‌ها فقط از طریق درگاه رسمی شتاب انجام می‌شود؛ هیچ شماره‌کارتی ذخیره نمی‌کنیم." },
        { icon: "Landmark", title: "عضو اتحادیه", sub: "تولیدکنندگان منسوجات و پوشاک", msg: "ای‌بی ساکس عضو رسمی اتحادیه‌ی تولیدکنندگان منسوجات است." },
      ],
    },
    faq: {
      eyebrow: "سوالات متداول خریداران",
      title: "هر چیزی که لازمه قبل از خرید بدونی",
      items: faqs,
      supportBox: {
        title: "پاسخ سوالتان را پیدا نکردید؟",
        hours: contact.hours,
        responseTime: "میانگین پاسخ‌گویی: زیر ۱۰ دقیقه",
        chatBtnText: "چت آنلاین",
        phone: contact.phone,
      },
    },
    footer: {
      tagline:
        "کارگاه بافندگی و فروشگاه آنلاین جوراب‌های طرح‌دار، اسپرت و اکسسوری استریت‌استایل؛ جایی که هر جفت، یک اثر کوچکِ قابل پوشیدن است. ارسال سریع به سراسر ایران با ضمانت دوام بافت.",
      brandTitle: "ای‌بی ساکس",
      brandSubtitle: "AB SOCKS",
      socialLinks: { instagram: contact.instagram, telegram: "absocks_ir" },
      copyright: "تمامی حقوق برای فروشگاه و کارگاه بافندگی ای‌بی ساکس محفوظ است.",
      sloganEnglish: "EVERY STEP IS A PIECE OF ART",
      newsletterTitle: "خبرنامه‌ی قدم‌به‌قدم",
      newsletterPlaceholder: "شماره موبایل یا ایمیل",
      newsletterBtnText: "عضویت",
    },
  },
  sectionOrder: [
    { id: "hero", label: "بخش اصلی (Hero)", enabled: true },
    { id: "trustBar", label: "نوار اعتماد و مزایا (Trust Bar)", enabled: true },
    { id: "catalog", label: "کاتالوگ و گالری محصولات", enabled: true },
    { id: "promiseBox", label: "باکس ضمانت و بازگشت (Promise)", enabled: true },
    { id: "motionGallery", label: "گالری حرکت (Motion Gallery)", enabled: true },
    { id: "story", label: "داستان کارگاه (Story)", enabled: true },
    { id: "packBuilder", label: "باکس‌ساز هوشمند (Pack Builder)", enabled: true },
    { id: "socialStyle", label: "دیوار استایل و شبکه‌ها (Social)", enabled: true },
    { id: "sizeGuide", label: "راهنمای سایز هوشمند", enabled: true },
    { id: "techTrust", label: "جزئیات فنی و مقایسه (Tech Trust)", enabled: true },
    { id: "faq", label: "سوالات متداول (FAQ)", enabled: true },
  ],
};

function deepMerge(target: any, source: any): any {
  if (!source || typeof source !== "object") return target;
  const output = { ...target };
  for (const key of Object.keys(source)) {
    if (source[key] instanceof Array) {
      output[key] = source[key];
    } else if (source[key] && typeof source[key] === "object") {
      output[key] = deepMerge(target[key] || {}, source[key]);
    } else {
      output[key] = source[key];
    }
  }
  return output;
}

const LandingConfigContext = createContext<LandingCustomizerConfig>(defaultLandingConfig);

// ── v1.38.0 ────────────────────────────────────────────────────────────────
// Copy that used to be hardcoded inside the components. Every visible string of
// the storefront now resolves from this config, so the Studio can edit it.
Object.assign(defaultLandingConfig.catalog, {
  productNameTemplate: "جوراب {title}",
  sortPopular: "پرفروش‌ترین",
  sortNewest: "جدیدترین",
  sortCheap: "ارزان‌ترین",
  sortExpensive: "گران‌ترین",
  viewComfortLabel: "نمای بزرگ",
  viewDenseLabel: "نمای فشرده",
  viewListLabel: "نمای لیستی",
  clearSearchText: "پاک کردن جستجو",
});

Object.assign(defaultLandingConfig.cartDrawer, {
  closeLabel: "بستن",
  freeShippingRemaining: "{amount} تومان تا ارسال رایگان!",
  freeShippingReached: "ارسال سفارش شما رایگان شد!",
  colorPrefix: "رنگ",
  sizePrefix: "سایز",
  increaseLabel: "افزایش",
  decreaseLabel: "کاهش",
  removeLabel: "حذف",
});

Object.assign(defaultLandingConfig.packBuilder, {
  capacityText: "باکس {tier} فقط {count} جفت جا دارد",
  trayText: "{count} پرفروش داخل باکس چیده شد",
  summaryText: "{tier} ({count} جفتی)",
  notePrefix: "— کارت: «{note}»",
  notePlaceholder: "مثلا: تولدت مبارک رفیق؛ همیشه خوش‌قدم باشی!",
  addButtonText: "افزودن پک اختصاصی به سبد خرید",
  remainingText: "هنوز {count} جفت دیگر انتخاب کن",
  removeLabel: "حذف",
});

Object.assign(defaultLandingConfig.sizeGuide, {
  steps: [
    "پات را روی کاغذ بگذار و دور آن خط بکش",
    "فاصله‌ی پاشنه تا نوک بزرگ‌ترین انگشت را اندازه بگیر",
    "عدد سانتی‌متر را با جدول نتیجه مقایسه کن",
  ],
  shoeSizeInputLabel: "شماره کفش",
});

Object.assign(defaultLandingConfig.styleMaker, {
  reasonTemplate: "چون گفتی «{tags}» — و این دقیقا حال‌وهوای «{title}» است.",
  workshopNote: "انتخاب سرهماهنگی کارگاه برای استایل تو.",
});

Object.assign(defaultLandingConfig.topStrip, {
  copyToast: "کد تخفیف {code} کپی شد",
  codeLabel: "کد تخفیف: {code}",
  copyHint: "کلیک برای کپی کد تخفیف",
});

Object.assign(defaultLandingConfig.socialStyle, {
  tagCopiedToast: "تگ @{handle} کپی شد — منتظر استوری‌تیم!",
  tagPrompt: "ما را تگ کن: @{handle}",
  tagCopyLabel: "کپی تگ @{handle}",
  postAlt: "استایل {handle}",
});

Object.assign(defaultLandingConfig.luckyWheel, {
  copiedToast: "کد جایزه کپی شد — موقع پرداخت واردش کن",
  codeLabel: "کد جایزه: {code}",
  spinningText: "در حال چرخش…",
  spinAgainText: "چرخاندن دوباره",
});

Object.assign(defaultLandingConfig.trackModal, {
  inputHint: "شماره‌ی پیگیری را وارد کن",
});

Object.assign(defaultLandingConfig.header, {
  menuLabel: "منو",
  menuCloseLabel: "بستن منو",
  homeLabel: "صفحه اصلی",
  deckHomeLabel: "خانه",
  deckMenuLabel: "منو",
  searchLabel: "جستجو",
  faqLabel: "سوالات متداول",
  deckShopLabel: "فروشگاه",
  deckWheelLabel: "گردونه شانس",
});

defaultLandingConfig.editorial.techTrust.otherColumnLabel = "جوراب‌های معمولی";
Object.assign(defaultLandingConfig.editorial.story as any, {
  imageAlt: "کارگاه بافندگی ای‌بی ساکس",
});
Object.assign(defaultLandingConfig.editorial.promiseBox as any, {
  giftImageAlt: "باکس هدیه ای‌بی ساکس",
});
defaultLandingConfig.editorial.faq.supportBox = {
  ...defaultLandingConfig.editorial.faq.supportBox,
  chatUnavailableText: "چت آنلاین به‌زودی فعال می‌شود؛ فعلا در اینستاگرام در خدمتیم",
} as any;

Object.assign(defaultLandingConfig.editorial.footer, {
  legalTitle: "ساماندهی",
  legalSub: "نشان ملی ثبت",
  legalText: "نشان ملی ثبت رسانه‌های دیجیتال (ساماندهی) ثبت و تایید شده است.",
  gatewayLabel: "درگاه شتاب",
  secureTitle: "پرداخت ۱۰۰٪ امن",
  secureDesc: "پرداخت‌ها از طریق درگاه امن بانکی شتاب انجام می‌شوند.",
  unionLabel: "اتحادیه‌ی منسوجات",
  unionSub: "عضو رسمی",
  unionText: "ای‌بی ساکس عضو رسمی اتحادیه‌ی تولیدکنندگان منسوجات است.",
  instagramLabel: "اینستاگرام",
  telegramLabel: "تلگرام",
  newsletterNote: "به خبرنامه‌ی ای‌بی ساکس پیوستی! کد هدیه‌ی عضویت برات پیامک می‌شه.",
  trustTitle: "اینماد رسمی",
  trustSub: "اعتماد الکترونیکی",
  trustText: "نماد اعتماد الکترونیکی (اینماد) فروشگاه ای‌بی ساکس فعال و معتبر است.",
  copyrightText: "© ۱۴۰۴ ای‌بی ساکس ({handle}) — تمامی حقوق محفوظ است.",
  // ── design controls ──────────────────────────────────────────────────────
  layoutPreset: "three",
  contentWidth: 1280,
  align: "start",
  columnGap: 40,
  paddingTop: 56,
  paddingBottom: 32,
  showTopDivider: true,
  bottomAlign: "split",
  backgroundStyle: "solid",
  backgroundFrom: "#180f09",
  backgroundTo: "#221510",
  textColor: "#f6efe0",
  mutedOpacity: 62,
  accentColor: "#ee7634",
  buttonColor: "#e85d1f",
  borderColor: "#f6efe0",
  dividerOpacity: 12,
  cardRadius: 16,
  decorStyle: "none",
  showBrand: true,
  showSocial: true,
  showLinks: true,
  showNewsletter: true,
  showBadges: true,
  showSlogan: true,
  showCopyright: true,
  showBackToTop: true,
  logoSize: 40,
  socialShape: "circle",
  socialSize: 40,
  badgeLayout: "grid-2",
  badgeStyle: "soft",
  newsletterStyle: "pill",
  col1Enabled: true,
  col1Title: "بخش‌های فروشگاه",
  col1Links: ["کاتالوگ|#catalog", "باکس‌ساز|#pack", "داستان ما|#story", "گالری حرکت|#gallery"],
  col2Enabled: true,
  col2Title: "خدمات مشتریان",
  col2Links: [
    "راهنمای هوشمند سایز|#size",
    "سوالات متداول|#faq",
    "تعویض ۷ روزه بی‌قیدوشرط|",
    "ساعت پاسخگویی: ۹ صبح تا ۶ عصر|",
  ],
  col3Enabled: false,
  col3Title: "شبکه‌های اجتماعی",
  col3Links: [],
  badge1Enabled: true,
  badge1Icon: "shield",
  badge2Enabled: true,
  badge2Icon: "badge",
  badge3Enabled: true,
  badge3Icon: "card",
  badge4Enabled: true,
  badge4Icon: "bank",
  backToTopText: "برگرد بالا",
});

export function LandingConfigProvider({ children }: { children: ReactNode }) {
  const [config, setConfig] = useState<LandingCustomizerConfig>(() => {
    if (typeof window !== "undefined" && (window as any).__AB_INITIAL_DATA__?.landingConfig) {
      return deepMerge(defaultLandingConfig, (window as any).__AB_INITIAL_DATA__.landingConfig);
    }
    return defaultLandingConfig;
  });

  useEffect(() => {
    const handleMessage = (e: MessageEvent) => {
      if (e.data && e.data.type === "AB_LANDING_CONFIG_UPDATE" && e.data.config) {
        setConfig((prev) => deepMerge(prev, e.data.config));
      }
    };
    window.addEventListener("message", handleMessage);
    return () => window.removeEventListener("message", handleMessage);
  }, []);

  return <LandingConfigContext.Provider value={config}>{children}</LandingConfigContext.Provider>;
}

export function useLandingConfig(): LandingCustomizerConfig {
  return useContext(LandingConfigContext);
}

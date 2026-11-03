import { AnimatePresence } from "framer-motion";
import { Dices } from "lucide-react";
import React, { useEffect, useState } from "react";
import { LandingConfigProvider, useLandingConfig } from "./context/LandingConfigContext";
import { ShopProvider, useShop } from "./context/ShopContext";

import { CartDrawer } from "./components/CartDrawer";
import { Catalog } from "./components/Catalog";
import { FAQ } from "./components/FAQ";
import { Footer } from "./components/Footer";
import { Header } from "./components/Header";
import { Hero } from "./components/Hero";
import { Loader } from "./components/Loader";
import { LuckyWheel } from "./components/LuckyWheel";
import { MobileDeck } from "./components/MobileDeck";
import { MotionGallery } from "./components/MotionGallery";
import { PackBuilder } from "./components/PackBuilder";
import { ProductModal } from "./components/ProductModal";
import { PromiseBox } from "./components/PromiseBox";
import { SizeGuide } from "./components/SizeGuide";
import { SocialStyle } from "./components/SocialStyle";
import { Story } from "./components/Story";
import { StyleMaker } from "./components/StyleMaker";
import { TechTrust } from "./components/TechTrust";
import { Toaster } from "./components/Toaster";
import { TopStrip } from "./components/TopStrip";
import { TrackModal } from "./components/TrackModal";
import { TrustBar } from "./components/TrustBar";
import { LandingPreviewController } from "./components/LandingPreviewController";

const SECTION_COMPONENTS: Record<string, React.ReactNode> = {
  hero: <Hero key="hero" />,
  trustBar: <TrustBar key="trustBar" />,
  catalog: <Catalog key="catalog" />,
  promiseBox: <PromiseBox key="promiseBox" />,
  motionGallery: <MotionGallery key="motionGallery" />,
  story: <Story key="story" />,
  packBuilder: <PackBuilder key="packBuilder" />,
  socialStyle: <SocialStyle key="socialStyle" />,
  sizeGuide: <SizeGuide key="sizeGuide" />,
  techTrust: <TechTrust key="techTrust" />,
  faq: <FAQ key="faq" />,
};

function StorefrontMain() {
  const { sectionOrder } = useLandingConfig();

  if (!sectionOrder || sectionOrder.length === 0) {
    return (
      <main>
        <Hero />
        <TrustBar />
        <Catalog />
        <PromiseBox />
        <MotionGallery />
        <Story />
        <PackBuilder />
        <SocialStyle />
        <SizeGuide />
        <TechTrust />
        <FAQ />
      </main>
    );
  }

  return (
    <main>
      {sectionOrder
        .filter((sec) => sec.enabled)
        .map((sec) => SECTION_COMPONENTS[sec.id] || null)}
    </main>
  );
}

/**
 * AB SOCKS — merged storefront.
 * Section rhythm alternates bright (cream) and dark (coffee) chapters —
 * MAX's trademark loop — while every ARGON conversion feature lives in
 * its own reusable component.
 */
export default function App() {
  // The Studio preview iframe must show content immediately; the 2.3s brand
  // loader is only for real visitors.
  const isPreview = Boolean((window as any).__AB_INITIAL_DATA__?.previewMode);
  const [loading, setLoading] = useState(!isPreview);

  useEffect(() => {
    if (isPreview) return;
    const t = window.setTimeout(() => setLoading(false), 2350);
    return () => window.clearTimeout(t);
  }, [isPreview]);

  return (
    <LandingConfigProvider>
      <ShopProvider>
        <AnimatePresence>{loading && <Loader />}</AnimatePresence>

        <TopStrip />
        <Header />

        <StorefrontMain />

        <Footer />
        <MobileDeck />
        <WheelFab />
        <CartDrawer />

        {/* modals */}
        <ProductModal />
        <StyleMaker />
        <LuckyWheel />
        <TrackModal />
        <Toaster />
        <LandingPreviewController />
      </ShopProvider>
    </LandingConfigProvider>
  );
}

/** Desktop floating launcher for the lucky wheel. */
function WheelFab() {
  const { openModal } = useShop();
  return (
    <button
      onClick={() => openModal("wheel")}
      aria-label="گردونه شانس"
      className="group fixed bottom-6 left-6 z-40 hidden items-center gap-2.5 lg:flex"
    >
      <span className="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-xl shadow-brand-500/35 transition-transform hover:scale-105 active:scale-95">
        <span className="animate-ping-soft absolute inset-0 rounded-2xl bg-brand-500" />
        <Dices className="relative h-6 w-6" />
      </span>
      <span className="pointer-events-none max-w-0 overflow-hidden whitespace-nowrap text-xs font-extrabold text-coffee-900 opacity-0 transition-all duration-300 group-hover:max-w-32 group-hover:opacity-100">
        گردونه شانس رو بچرخون!
      </span>
    </button>
  );
}

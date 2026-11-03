import React, { useEffect, useRef, useState } from "react";
import { useShop } from "../context/ShopContext";

const SECTION_TITLES: Record<string, string> = {
  header: "نوار اعلان و منوی هدر",
  topStrip: "نوار مشکی اعلان بالای صفحه",
  hero: "بخش اصلی هیرو (Hero)",
  trustBar: "نوار اعتماد و مزایا (Trust Bar)",
  catalog: "کاتالوگ و چیدمان محصولات",
  packBuilder: "باکس‌ساز هوشمند (Pack Builder)",
  motionGallery: "گالری حرکت و جلوه‌های بصری",
  story: "داستان کارگاه (Story)",
  promiseBox: "باکس ضمانت و قول کارگاه",
  techTrust: "جزئیات فنی و مقایسه (Tech Trust)",
  socialStyle: "دیوار استایل کاربران (#ABSOCKS)",
  sizeGuide: "راهنمای سایز هوشمند",
  faq: "سوالات متداول (FAQ)",
  footer: "پاورقی و اطلاعات برند (Footer)",
};

const SECTION_MAP: Record<string, string> = {
  home: "hero",
  hero: "hero",
  trustbar: "trustBar",
  trustBar: "trustBar",
  catalog: "catalog",
  pack: "packBuilder",
  packBuilder: "packBuilder",
  gallery: "motionGallery",
  motionGallery: "motionGallery",
  story: "story",
  promise: "promiseBox",
  promiseBox: "promiseBox",
  tech: "techTrust",
  techTrust: "techTrust",
  faq: "faq",
  social: "socialStyle",
  socialStyle: "socialStyle",
  size: "sizeGuide",
  sizeGuide: "sizeGuide",
  footer: "footer",
  header: "header",
  topstrip: "topStrip",
  topStrip: "topStrip",
};

/**
 * Live Customizer Preview Controller:
 * Handles bidirectional postMessage communication:
 * 1. 3-second visual pulse & smooth scroll on sidebar section/element clicks.
 * 2. Visual "Select Mode" (Click-to-Edit cursor tracking and element picker).
 */
export function LandingPreviewController() {
  const { openModal, setCartOpen } = useShop();
  const [selectMode, setSelectMode] = useState(false);
  const [hoverLabel, setHoverLabel] = useState<string | null>(null);
  const [badgePos, setBadgePos] = useState({ x: 0, y: 0 });
  const highlightTimeoutRef = useRef<any>(null);
  const currentHoverElRef = useRef<HTMLElement | null>(null);

  // 1. Listen for postMessages from parent window
  useEffect(() => {
    const handleMessage = (e: MessageEvent) => {
      if (!e.data || typeof e.data !== "object") return;

      // Handle Select Mode toggle
      if (e.data.type === "AB_SET_SELECT_MODE") {
        setSelectMode(Boolean(e.data.enabled));
        if (!e.data.enabled && currentHoverElRef.current) {
          currentHoverElRef.current.classList.remove("lb-select-hover-target");
          currentHoverElRef.current = null;
          setHoverLabel(null);
        }
      }

      // Handle Scroll & Highlight
      if (e.data.type === "AB_PREVIEW_SCROLL_TO") {
        const { sectionId, fieldKey } = e.data;
        if (!sectionId) return;

        // Modal triggers
        if (sectionId === "luckyWheel" || sectionId === "wheel") {
          openModal("wheel");
          return;
        }
        if (sectionId === "styleMaker" || sectionId === "style") {
          openModal("style");
          return;
        }
        if (sectionId === "trackModal" || sectionId === "track") {
          openModal("track");
          return;
        }
        if (sectionId === "cartDrawer" || sectionId === "cart") {
          setCartOpen(true);
          return;
        }

        // DOM lookup for target section/element
        let targetEl: HTMLElement | null = null;
        if (fieldKey) {
          targetEl = document.querySelector(`[data-lb-field="${fieldKey}"]`);
        }
        if (!targetEl) {
          targetEl =
            document.querySelector(`[data-lb-section="${sectionId}"]`) ||
            document.getElementById(sectionId) ||
            (sectionId === "hero" ? document.getElementById("home") : null) ||
            (sectionId === "trustBar" ? document.getElementById("trustbar") : null) ||
            (sectionId === "packBuilder" ? document.getElementById("pack") : null) ||
            (sectionId === "motionGallery" ? document.getElementById("gallery") : null) ||
            (sectionId === "socialStyle" ? document.getElementById("social") : null) ||
            (sectionId === "sizeGuide" ? document.getElementById("size") : null) ||
            (sectionId === "techTrust" ? document.getElementById("tech") : null);
        }

        if (targetEl) {
          targetEl.scrollIntoView({ behavior: "smooth", block: "center" });

          // Reset and trigger 3-second highlight pulse reliably every time
          if (highlightTimeoutRef.current) {
            clearTimeout(highlightTimeoutRef.current);
            highlightTimeoutRef.current = null;
          }
          document.querySelectorAll(".lb-preview-highlight-pulse").forEach((node) => {
            node.classList.remove("lb-preview-highlight-pulse");
          });

          // Force reflow
          void targetEl.offsetWidth;

          targetEl.classList.add("lb-preview-highlight-pulse");
          highlightTimeoutRef.current = setTimeout(() => {
            targetEl?.classList.remove("lb-preview-highlight-pulse");
            highlightTimeoutRef.current = null;
          }, 3000);
        }
      }
    };

    window.addEventListener("message", handleMessage);
    return () => window.removeEventListener("message", handleMessage);
  }, [openModal, setCartOpen]);

  // 2. Select Mode Cursor, Hover & Click Interception
  useEffect(() => {
    if (!selectMode) {
      document.body.classList.remove("lb-select-mode-active");
      return;
    }

    document.body.classList.add("lb-select-mode-active");

    const onMouseMove = (e: MouseEvent) => {
      setBadgePos({ x: e.clientX, y: e.clientY });

      const elUnderCursor = document.elementFromPoint(e.clientX, e.clientY);
      const sectionEl = elUnderCursor?.closest<HTMLElement>(
        "[data-lb-section], section[id], footer[id], header[id], div[id='topstrip']"
      );

      if (sectionEl !== currentHoverElRef.current) {
        if (currentHoverElRef.current) {
          currentHoverElRef.current.classList.remove("lb-select-hover-target");
        }
        if (sectionEl) {
          sectionEl.classList.add("lb-select-hover-target");
          currentHoverElRef.current = sectionEl;

          const rawId = sectionEl.getAttribute("data-lb-section") || sectionEl.id || "";
          const mappedKey = SECTION_MAP[rawId] || rawId;
          const title = SECTION_TITLES[mappedKey] || rawId;
          setHoverLabel(title);
        } else {
          currentHoverElRef.current = null;
          setHoverLabel(null);
        }
      }
    };

    const onClick = (e: MouseEvent) => {
      e.preventDefault();
      e.stopPropagation();

      const elUnderCursor = document.elementFromPoint(e.clientX, e.clientY);
      const sectionEl = elUnderCursor?.closest<HTMLElement>(
        "[data-lb-section], section[id], footer[id], header[id], div[id='topstrip']"
      );

      if (sectionEl) {
        const rawId = sectionEl.getAttribute("data-lb-section") || sectionEl.id || "";
        const mappedSection = SECTION_MAP[rawId] || rawId;
        const fieldEl = elUnderCursor?.closest<HTMLElement>("[data-lb-field]");
        const fieldKey = fieldEl?.getAttribute("data-lb-field") || null;

        // Post back to parent window
        window.parent.postMessage(
          {
            type: "AB_ELEMENT_SELECTED",
            section: mappedSection,
            field: fieldKey,
          },
          "*"
        );
      }
    };

    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") {
        setSelectMode(false);
        if (currentHoverElRef.current) {
          currentHoverElRef.current.classList.remove("lb-select-hover-target");
          currentHoverElRef.current = null;
        }
        setHoverLabel(null);
        window.parent.postMessage({ type: "AB_SET_SELECT_MODE", enabled: false }, "*");
      }
    };

    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("click", onClick, true); // Capture phase
    window.addEventListener("keydown", onKeyDown);

    return () => {
      document.body.classList.remove("lb-select-mode-active");
      if (currentHoverElRef.current) {
        currentHoverElRef.current.classList.remove("lb-select-hover-target");
        currentHoverElRef.current = null;
      }
      setHoverLabel(null);
      window.removeEventListener("mousemove", onMouseMove);
      window.removeEventListener("click", onClick, true);
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [selectMode]);

  if (!selectMode || !hoverLabel) return null;

  return (
    <div
      className="lb-select-hover-badge"
      style={{
        left: `${badgePos.x}px`,
        top: `${badgePos.y}px`,
      }}
    >
      <span>🎯 کلیک برای ویرایش: {hoverLabel}</span>
    </div>
  );
}


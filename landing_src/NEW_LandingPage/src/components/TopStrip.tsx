import { Copy, Gift, PackageSearch, TicketPercent, Truck } from "lucide-react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { DISCOUNT_CODE } from "../data/site";

/**
 * TopStrip — announcement bar with dynamic config, coupon chip,
 * free-shipping note, order tracking, and gift packaging.
 */
export function TopStrip() {
  const { toast, openModal } = useShop();
  const { topStrip } = useLandingConfig();

  if (topStrip && topStrip.enabled === false) {
    return null;
  }

  const discountCode = topStrip?.couponBadge || DISCOUNT_CODE;
  const announcement = topStrip?.announcement || "ارسال رایگان سفارش‌های بالای ۵۰۰ هزار تومان";
  const giftText = topStrip?.giftPackagingText || "بسته‌بندی هدیه رایگان";
  const trackText = topStrip?.trackOrderText || "پیگیری سفارش";

  const copyCode = async () => {
    try {
      await navigator.clipboard.writeText(discountCode);
      toast((topStrip?.copyToast || "کد تخفیف {code} کپی شد").replace("{code}", discountCode));
    } catch {
      toast((topStrip?.codeLabel || "کد تخفیف: {code}").replace("{code}", discountCode), "info");
    }
  };

  return (
    <div id="topstrip" data-lb-section="topStrip" className="bg-coffee-950 text-cream-100">
      <div className="mx-auto flex h-9 max-w-7xl items-center justify-between gap-3 px-3 text-[11px] font-semibold sm:px-6 sm:text-xs">
        <div className="flex min-w-0 items-center gap-2">
          <span className="hidden items-center gap-1.5 text-cream-100/80 sm:flex">
            <Truck className="h-3.5 w-3.5 text-brand-400" />
            {announcement}
          </span>
          <span className="hidden text-white/25 sm:inline">|</span>
          <button
            onClick={copyCode}
            className="group flex items-center gap-1.5 rounded-md border border-white/15 bg-white/10 px-2 py-0.5 font-extrabold tracking-wider transition-colors hover:bg-brand-500"
            title={topStrip?.copyHint || "کلیک برای کپی کد تخفیف"}
          >
            <TicketPercent className="h-3.5 w-3.5" />
            <span dir="ltr">{discountCode}</span>
            <Copy className="h-3 w-3 opacity-70 transition-transform group-hover:scale-110" />
          </button>
        </div>

        <div className="flex items-center gap-3 sm:gap-4">
          <span className="hidden items-center gap-1.5 text-cream-100/70 md:flex">
            <Gift className="h-3.5 w-3.5 text-brand-400" />
            {giftText}
          </span>
          <span className="flex items-center gap-1 text-cream-100/80 sm:hidden">
            <Truck className="h-3.5 w-3.5 text-brand-400" />
            {announcement}
          </span>
          <button
            onClick={() => openModal("track")}
            className="flex items-center gap-1 underline decoration-brand-500 decoration-2 underline-offset-4 transition-colors hover:text-white"
          >
            <PackageSearch className="h-3.5 w-3.5" />
            {trackText}
          </button>
        </div>
      </div>
    </div>
  );
}

import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, Circle, Loader2, PackageSearch, Scissors, Send, ShoppingBag, Truck } from "lucide-react";
import { useState } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { Modal } from "./primitives";

const iconMap: Record<string, any> = {
  bag: ShoppingBag,
  scissors: Scissors,
  send: Send,
  truck: Truck,
  check: CheckCircle2,
};

const defaultSteps = [
  { label: "ثبت سفارش", desc: "سفارش‌ات در کارگاه ثبت شد", icon: "bag", state: "done" as const },
  { label: "بسته‌بندی هنری", desc: "کارت دست‌نویس نوشته شد و باکس چیده شد", icon: "scissors", state: "done" as const },
  { label: "تحویل به پست", desc: "مرسوله به پست پیشتاز تحویل داده شد", icon: "send", state: "active" as const },
  { label: "در راهِ شهر تو", desc: "مرسوله در مسیر است", icon: "truck", state: "next" as const },
  { label: "تحویل و لبخند", desc: "به دستت می‌رسد", icon: "check", state: "next" as const },
];

/** TrackModal — dynamic order-tracking entry point with timeline. */
export function TrackModal() {
  const { modal, closeModal, toast } = useShop();
  const { trackModal } = useLandingConfig();
  const open = modal.name === "track";
  const [code, setCode] = useState("");
  const [result, setResult] = useState(false);
  const [busy, setBusy] = useState(false);

  const steps = trackModal?.timelineSteps?.length ? trackModal.timelineSteps : defaultSteps;

  const search = () => {
    if (!code.trim()) {
      toast(trackModal?.inputHint || "شماره‌ی پیگیری را وارد کن", "info");
      return;
    }
    setBusy(true);
    window.setTimeout(() => {
      setBusy(false);
      setResult(true);
    }, 900);
  };

  return (
    <Modal open={open} onClose={closeModal} size="sm">
      <div className="p-6 sm:p-8">
        <div className="flex items-center gap-2.5">
          <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-coffee-900 text-cream-50">
            <PackageSearch className="h-5 w-5" />
          </span>
          <div>
            <h3 className="text-lg font-black text-coffee-900">
              {trackModal?.title || "پیگیری سفارش"}
            </h3>
            <p className="text-[11px] text-coffee-500">
              {trackModal?.subtitle || "کدی که پیامک شده را این‌جا وارد کن"}
            </p>
          </div>
        </div>

        <div className="mt-5 flex gap-2">
          <input
            value={code}
            onChange={(e) => setCode(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && search()}
            placeholder={trackModal?.inputPlaceholder || "مثلا: AB-۸۴۲۱"}
            className="w-full rounded-2xl border border-coffee-900/10 bg-cream-100 px-4 py-3 text-sm font-bold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15"
          />
          <button
            onClick={search}
            disabled={busy}
            className="flex shrink-0 items-center gap-1.5 rounded-2xl bg-coffee-900 px-5 py-3 text-xs font-extrabold text-cream-50 transition-colors hover:bg-brand-500 disabled:opacity-70"
          >
            {busy ? <Loader2 className="h-4 w-4 animate-spin" /> : (trackModal?.searchButtonText || "رهگیری")}
          </button>
        </div>

        <AnimatePresence>
          {result && (
            <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: "auto" }} exit={{ opacity: 0, height: 0 }} className="overflow-hidden">
              <ol className="mt-6 space-y-0.5">
                {steps.map((s, i) => (
                  <motion.li
                    key={s.label + i}
                    initial={{ opacity: 0, x: 24 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.12 }}
                    className="relative flex gap-3 pb-5 last:pb-0"
                  >
                    {i < steps.length - 1 && (
                      <span className={`absolute right-[13px] top-7 h-full w-0.5 ${s.state === "done" ? "bg-emerald-500" : "bg-cream-300"}`} />
                    )}
                    <span
                      className={`relative flex h-7 w-7 shrink-0 items-center justify-center rounded-full ${
                        s.state === "done"
                          ? "bg-emerald-500 text-white"
                          : s.state === "active"
                            ? "bg-brand-500 text-white"
                            : "bg-cream-200 text-coffee-400"
                      }`}
                    >
                      {s.state === "done" ? <CheckCircle2 className="h-4 w-4" /> : s.state === "active" ? <Loader2 className="h-4 w-4 animate-spin" /> : <Circle className="h-3 w-3" />}
                    </span>
                    <div>
                      <p className={`text-xs font-black ${s.state === "active" ? "text-brand-600" : s.state === "done" ? "text-coffee-900" : "text-coffee-400"}`}>
                        {s.label}
                        {s.state === "active" && <span className="ms-2 rounded-full bg-brand-50 px-2 py-0.5 text-[9px] text-brand-600">وضعیت فعلی</span>}
                      </p>
                      <p className="mt-0.5 text-[10.5px] text-coffee-500">{s.desc}</p>
                    </div>
                  </motion.li>
                ))}
              </ol>
              <p className="mt-5 rounded-xl bg-cream-100 px-3.5 py-2.5 text-[10.5px] leading-relaxed text-coffee-500">
                نمایش نمونه — درگاه واقعی رهگیری پست به‌زودی مستقیماً به سامانه‌ی ۲۴ ساعته‌ی پست متصل می‌شود.
              </p>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </Modal>
  );
}

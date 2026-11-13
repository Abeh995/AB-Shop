import { motion } from "framer-motion";
import { Copy, Dices, PartyPopper, RotateCw } from "lucide-react";
import { useState } from "react";
import { useShop } from "../context/ShopContext";
import { useLandingConfig } from "../context/LandingConfigContext";
import { wheelPrizes as defaultWheelPrizes } from "../data/site";
import { Modal } from "./primitives";

/** LuckyWheel — dynamic wheel of fortune with weighted prizes and copy-able codes. */
export function LuckyWheel() {
  const { modal, closeModal, toast } = useShop();
  const { luckyWheel } = useLandingConfig();
  const open = modal.name === "wheel";
  const [rotation, setRotation] = useState(0);
  const [spinning, setSpinning] = useState(false);

  const slices = luckyWheel?.slices?.length
    ? luckyWheel.slices
    : defaultWheelPrizes.map((p, i) => ({
        label: p.label,
        code: p.code,
        color: ["#e85d1f", "#f6efe0", "#412c1d", "#fbdcc3"][i % 4],
        weight: [4, 2, 4, 4, 2, 4, 1, 1][i] ?? 3,
      }));

  const SLICE = 360 / slices.length;
  const [won, setWon] = useState<(typeof slices)[number] | null>(null);

  if (luckyWheel && luckyWheel.enabled === false) return null;

  const spin = () => {
    if (spinning) return;
    setWon(null);
    setSpinning(true);
    const weights = slices.map((s) => s.weight || 1);
    const total = weights.reduce((a, b) => a + b, 0);
    let r = Math.random() * total;
    let idx = 0;
    for (let i = 0; i < weights.length; i++) {
      r -= weights[i];
      if (r <= 0) {
        idx = i;
        break;
      }
    }
    const target = (360 - (idx * SLICE + SLICE / 2)) % 360;
    setRotation((cur) => cur - (cur % 360) + 360 * 6 + target);
    window.setTimeout(() => {
      setSpinning(false);
      setWon(slices[idx]);
    }, 4600);
  };

  const copyCode = async (code: string) => {
    try {
      await navigator.clipboard.writeText(code);
      toast(luckyWheel?.copiedToast || "کد جایزه کپی شد — موقع پرداخت واردش کن");
    } catch {
      toast((luckyWheel?.codeLabel || "کد جایزه: {code}").replace("{code}", String(code)), "info");
    }
  };

  const conic = slices
    .map((s, i) => `${s.color} ${i * SLICE}deg ${(i + 1) * SLICE}deg`)
    .join(", ");

  return (
    <Modal open={open} onClose={closeModal} size="sm" tone="dark">
      <div className="p-6 text-center sm:p-8">
        <span className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/30">
          <Dices className="h-6 w-6" />
        </span>
        <h3 className="text-xl font-black text-cream-50 sm:text-2xl">
          {luckyWheel?.title || "گردونه شانس ای‌بی‌ساکس"}
        </h3>
        <p className="mt-1 text-xs text-cream-100/60">
          {luckyWheel?.subtitle || "بچرخون و روی سفارش امروزت تخفیف یا جایزه بگیر!"}
        </p>

        {/* wheel container */}
        <div className="relative mx-auto my-7 h-64 w-64 select-none sm:h-72 sm:w-72">
          {/* pointer */}
          <div className="absolute -top-3 left-1/2 z-20 -translate-x-1/2 drop-shadow-lg">
            <div className="h-0 w-0 border-x-8 border-t-[18px] border-x-transparent border-t-brand-500" />
          </div>

          {/* outer ring */}
          <div className="absolute inset-0 rounded-full border-8 border-coffee-800 shadow-2xl" />

          {/* spinning disc */}
          <motion.div
            animate={{ rotate: rotation }}
            transition={{ duration: 4.6, ease: [0.15, 0.9, 0.2, 1] }}
            style={{ background: `conic-gradient(${conic})` }}
            className="relative h-full w-full rounded-full"
          >
            {slices.map((p, i) => {
              const deg = i * SLICE + SLICE / 2;
              const isDark = i % 2 === 0;
              return (
                <div
                  key={i}
                  style={{ transform: `rotate(${deg}deg)` }}
                  className="absolute inset-0 flex items-start justify-center pt-3"
                >
                  <span
                    style={{ transform: "rotate(90deg) translateY(-8px)" }}
                    className={`max-w-[75px] truncate text-[10px] font-black ${isDark ? "text-cream-50" : "text-coffee-950"}`}
                  >
                    {p.label}
                  </span>
                </div>
              );
            })}
          </motion.div>

          {/* center cap */}
          <button
            onClick={spin}
            disabled={spinning}
            className="group absolute inset-0 m-auto flex h-16 w-16 items-center justify-center rounded-full border-4 border-coffee-950 bg-brand-500 text-white shadow-xl transition-transform hover:scale-105 active:scale-95 disabled:opacity-80"
          >
            <RotateCw className={`h-6 w-6 ${spinning ? "animate-spin" : "group-hover:rotate-45"} transition-transform`} />
          </button>
        </div>

        {/* won banner */}
        {won && (
          <div className="rounded-2xl border border-pop-400/40 bg-pop-400/10 p-4 text-center">
            <p className="flex items-center justify-center gap-1.5 text-xs font-black text-pop-300">
              <PartyPopper className="h-4 w-4 text-pop-400" />
              {luckyWheel?.winTitle || "جایزه‌ات:"} {won.label}
            </p>
            {won.code && (
              <div className="mt-3 flex items-center justify-center gap-2">
                <code className="rounded-xl bg-coffee-950 px-3 py-1.5 font-mono text-xs font-black text-cream-50" dir="ltr">
                  {won.code}
                </code>
                <button
                  onClick={() => copyCode(won.code!)}
                  className="flex items-center gap-1 rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-extrabold text-white transition-colors hover:bg-brand-600"
                >
                  <Copy className="h-3.5 w-3.5" />
                  {luckyWheel?.winCopyBtn || "کپی کد"}
                </button>
              </div>
            )}
          </div>
        )}

        <button
          onClick={spin}
          disabled={spinning}
          className="mt-4 w-full rounded-2xl bg-brand-500 py-3.5 text-sm font-black text-white shadow-xl shadow-brand-500/25 transition-transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-60"
        >
          {spinning
            ? luckyWheel?.spinningText || "در حال چرخش…"
            : luckyWheel?.spinAgainText || luckyWheel?.spinButtonLabel || "چرخاندن دوباره"}
        </button>
      </div>
    </Modal>
  );
}

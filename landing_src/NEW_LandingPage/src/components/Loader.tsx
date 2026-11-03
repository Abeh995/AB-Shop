import { motion } from "framer-motion";
import { useEffect, useState } from "react";
import { faNum } from "../lib/format";

/**
 * Loader — MAX's signature loading page.
 * Sock monogram draws itself while the counter climbs; the whole
 * sheet then slides up like an atelier curtain.
 */
export function Loader() {
  const [n, setN] = useState(0);

  useEffect(() => {
    const t0 = performance.now();
    let raf = 0;
    const tick = (t: number) => {
      const p = Math.min(1, (t - t0) / 1700);
      setN(Math.round(p * 100));
      if (p < 1) raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, []);

  const letters = "ای‌بی‌ساکس".split("");

  return (
    <motion.div
      className="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-cream-50"
      exit={{ y: "-100%", borderBottomLeftRadius: "50% 12%", borderBottomRightRadius: "50% 12%" }}
      transition={{ duration: 0.85, ease: [0.76, 0, 0.24, 1] }}
    >
      <div className="dot-grid absolute inset-0 opacity-40" />

      <div className="relative flex flex-col items-center">
        {/* drawing sock monogram */}
        <svg viewBox="0 0 120 120" className="h-28 w-28 sm:h-32 sm:w-32">
          <motion.path
            d="M46 14 H76 V58 C76 70 70 77 60 83 L40 95 C30 101 18 96 14 87 C10 78 14 66 24 60 L46 47 V14 Z"
            fill="none"
            stroke="#221510"
            strokeWidth="4"
            strokeLinejoin="round"
            initial={{ pathLength: 0 }}
            animate={{ pathLength: 1 }}
            transition={{ duration: 1.4, ease: "easeInOut" }}
          />
          <motion.rect
            x="46"
            y="30"
            width="30"
            height="6"
            rx="3"
            fill="#e85d1f"
            initial={{ scaleX: 0 }}
            animate={{ scaleX: 1 }}
            transition={{ delay: 1.2, duration: 0.5 }}
            style={{ transformOrigin: "right center" }}
          />
          <motion.circle
            cx="30"
            cy="76"
            r="6"
            fill="#e85d1f"
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ delay: 1.45, type: "spring", stiffness: 300, damping: 12 }}
          />
        </svg>

        {/* brand word */}
        <div className="mt-4 flex overflow-hidden text-3xl font-black tracking-tight text-coffee-900 sm:text-4xl">
          {letters.map((l, i) => (
            <motion.span
              key={i}
              initial={{ y: "110%" }}
              animate={{ y: 0 }}
              transition={{ delay: 0.5 + i * 0.06, duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
            >
              {l}
            </motion.span>
          ))}
        </div>
        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 1.2 }}
          className="mt-2 text-xs font-bold tracking-[0.35em] text-brand-600"
        >
          AB SOCKS — هر قدم یک اثر
        </motion.p>

        {/* progress */}
        <div className="mt-8 w-52">
          <div className="mb-2 flex items-center justify-between text-[11px] font-bold text-coffee-500">
            <span>در حال آماده‌سازی گالری…</span>
            <span className="tabular-nums">{faNum(n)}٪</span>
          </div>
          <div className="h-1.5 overflow-hidden rounded-full bg-cream-300">
            <motion.div
              className="h-full rounded-full bg-brand-500"
              style={{ width: `${n}%` }}
              transition={{ ease: "linear" }}
            />
          </div>
        </div>
      </div>
    </motion.div>
  );
}

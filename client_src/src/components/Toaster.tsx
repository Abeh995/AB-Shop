import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, Info } from "lucide-react";
import { useShop } from "../context/ShopContext";

/** Tiny toast stack wired to the shop context. */
export function Toaster() {
  const { toasts } = useShop();
  return (
    <div className="pointer-events-none fixed bottom-24 left-1/2 z-[95] flex w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 flex-col items-center gap-2 lg:bottom-8">
      <AnimatePresence>
        {toasts.map((t) => (
          <motion.div
            key={t.id}
            layout
            initial={{ opacity: 0, y: 24, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 12, scale: 0.95 }}
            className="flex w-full items-center gap-2.5 rounded-2xl bg-coffee-950/95 px-4 py-3 text-xs font-bold text-cream-50 shadow-2xl backdrop-blur"
          >
            {t.tone === "ok" ? (
              <CheckCircle2 className="h-4.5 w-4.5 shrink-0 text-emerald-400" />
            ) : (
              <Info className="h-4.5 w-4.5 shrink-0 text-brand-400" />
            )}
            {t.msg}
          </motion.div>
        ))}
      </AnimatePresence>
    </div>
  );
}

import { AnimatePresence, motion } from "framer-motion";
import { X } from "lucide-react";
import { useEffect, type ReactNode } from "react";
import { cn } from "../utils/cn";

/* ------------------------------------------------------------------ */
/* Marquee — reusable horizontal scrolling banner (HSB)                */
/* ------------------------------------------------------------------ */
export function Marquee({
  children,
  speed = 22,
  reverse = false,
  className,
  pauseOnHover = false,
}: {
  children: ReactNode;
  speed?: number;
  reverse?: boolean;
  className?: string;
  pauseOnHover?: boolean;
}) {
  return (
    <div className={cn("overflow-hidden", className)} dir="ltr">
      <div
        className={cn(
          "animate-marquee-x flex w-max",
          reverse && "[animation-direction:reverse]",
          pauseOnHover && "hover:[animation-play-state:paused]"
        )}
        style={{ animationDuration: `${speed}s` }}
      >
        <div className="flex shrink-0 items-center">{children}</div>
        <div className="flex shrink-0 items-center" aria-hidden="true">
          {children}
        </div>
      </div>
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Reveal — shared scroll-in animation                                 */
/* ------------------------------------------------------------------ */
export function Reveal({
  children,
  delay = 0,
  y = 30,
  className,
}: {
  children: ReactNode;
  delay?: number;
  y?: number;
  className?: string;
}) {
  return (
    <motion.div
      initial={{ opacity: 0, y }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-60px" }}
      transition={{ duration: 0.75, delay, ease: [0.22, 1, 0.36, 1] }}
      className={className}
    >
      {children}
    </motion.div>
  );
}

/* ------------------------------------------------------------------ */
/* Modal — shared overlay shell used by all modals & drawers           */
/* ------------------------------------------------------------------ */
export function Modal({
  open,
  onClose,
  children,
  size = "md",
  tone = "light",
}: {
  open: boolean;
  onClose: () => void;
  children: ReactNode;
  size?: "sm" | "md" | "lg";
  tone?: "light" | "dark";
}) {
  useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && onClose();
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = prev;
      window.removeEventListener("keydown", onKey);
    };
  }, [open, onClose]);

  const maxW = size === "sm" ? "max-w-md" : size === "md" ? "max-w-lg" : "max-w-3xl";

  return (
    <AnimatePresence>
      {open && (
        <motion.div
          className="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-5"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
        >
          <div className="absolute inset-0 bg-coffee-950/70 backdrop-blur-sm" onClick={onClose} />
          <motion.div
            initial={{ opacity: 0, y: 60, scale: 0.98 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 50, scale: 0.98 }}
            transition={{ type: "spring", damping: 28, stiffness: 320 }}
            className={cn(
              "relative max-h-[92vh] w-full overflow-y-auto no-scrollbar rounded-t-[2rem] shadow-2xl sm:rounded-[2rem]",
              maxW,
              tone === "light" ? "bg-cream-50 text-coffee-900" : "bg-coffee-900 text-cream-50"
            )}
          >
            <button
              onClick={onClose}
              aria-label="بستن"
              className={cn(
                "absolute start-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full transition-colors",
                tone === "light"
                  ? "bg-coffee-900/5 text-coffee-800 hover:bg-brand-500 hover:text-white"
                  : "bg-white/10 text-cream-100 hover:bg-brand-500"
              )}
            >
              <X className="h-5 w-5" />
            </button>
            {children}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}

/* ------------------------------------------------------------------ */
/* SectionHeading — consistent eyebrow + title for sections            */
/* ------------------------------------------------------------------ */
export function SectionHeading({
  eyebrow,
  title,
  desc,
  dark = false,
  center = true,
  field,
}: {
  eyebrow: string;
  title: string;
  desc?: string;
  dark?: boolean;
  center?: boolean;
  /** Landing Studio binding: "section.title" so click-to-edit can jump here. */
  field?: string;
}) {
  return (
    <Reveal className={cn("mb-10 max-w-2xl sm:mb-14", center && "mx-auto text-center")}>
      <span
        className={cn(
          "inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-extrabold",
          dark ? "bg-white/10 text-brand-300" : "bg-brand-500/10 text-brand-600"
        )}
      >
        <span className="relative flex h-2 w-2">
          <span className="animate-ping-soft absolute inline-flex h-full w-full rounded-full bg-brand-500" />
          <span className="relative inline-flex h-2 w-2 rounded-full bg-brand-500" />
        </span>
        {eyebrow}
      </span>
      <h2
        data-lb-field={field}
        className={cn(
          "mt-4 text-3xl font-black leading-[1.3] sm:text-4xl lg:text-[2.75rem]",
          dark ? "text-cream-50" : "text-coffee-900"
        )}
      >
        {title}
      </h2>
      {desc && (
        <p className={cn("mt-4 text-sm leading-relaxed sm:text-base", dark ? "text-cream-100/70" : "text-coffee-600")}>
          {desc}
        </p>
      )}
    </Reveal>
  );
}

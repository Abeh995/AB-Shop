import { ArrowUp, AtSign, BadgeCheck, CreditCard, Landmark, Send, ShieldCheck } from "lucide-react";
import { useState } from "react";
import { useLandingConfig } from "../context/LandingConfigContext";
import { useShop } from "../context/ShopContext";
import { contact } from "../data/site";

const badgeIcons: Record<string, any> = {
  shield: ShieldCheck,
  badge: BadgeCheck,
  card: CreditCard,
  bank: Landmark,
};

/** #rrggbb (or #rgb) → rgba() with the given opacity percentage. */
function withAlpha(hex: string, pct: number): string {
  const raw = String(hex || "#ffffff").trim().replace("#", "");
  const full = raw.length === 3 ? raw.split("").map((c) => c + c).join("") : raw;
  const n = parseInt(full || "ffffff", 16);
  const r = (n >> 16) & 255;
  const g = (n >> 8) & 255;
  const b = n & 255;
  const a = Math.max(0, Math.min(100, Number.isFinite(pct) ? pct : 100)) / 100;
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

/** "برچسب|#target" → { label, target }. An empty target renders as plain text. */
function parseLinks(list: unknown): Array<{ label: string; target: string }> {
  if (!Array.isArray(list)) return [];
  return list
    .map((raw) => {
      const [label, target] = String(raw || "").split("|");
      return { label: (label || "").trim(), target: (target || "").trim() };
    })
    .filter((link) => link.label.length > 0);
}

/**
 * Footer — every block, colour, spacing value and link column is driven by
 * `editorial.footer` so the Landing Studio owns the whole design of the section.
 */
export function Footer() {
  const { toast } = useShop();
  const { editorial } = useLandingConfig();
  const ftr: any = editorial.footer;
  const [email, setEmail] = useState("");

  // ── design tokens (all editable) ──────────────────────────────────────────
  const backgroundFrom = ftr.backgroundFrom || "#180f09";
  const backgroundTo = ftr.backgroundTo || "#221510";
  const textColor = ftr.textColor || "#f6efe0";
  const accent = ftr.accentColor || "#ee7634";
  const button = ftr.buttonColor || "#e85d1f";
  const border = ftr.borderColor || "#f6efe0";
  const mutedPct = Number(ftr.mutedOpacity ?? 62);
  const dividerAlpha = Math.max(0, Math.min(100, Number(ftr.dividerOpacity ?? 12))) / 100;
  const muted = withAlpha(textColor, mutedPct);
  const faint = withAlpha(textColor, Math.max(0, mutedPct - 22));
  const hairline = withAlpha(border, Math.round(dividerAlpha * 100));
  const radius = Number(ftr.cardRadius ?? 16);

  const layout = ftr.layoutPreset || "three";
  const center = (ftr.align || "start") === "center";
  const contentWidth = Number(ftr.contentWidth ?? 1280);
  const columnGap = Number(ftr.columnGap ?? 40);
  const paddingTop = Number(ftr.paddingTop ?? 56);
  const paddingBottom = Number(ftr.paddingBottom ?? 32);
  const bottomAlign = ftr.bottomAlign || "split";
  const decor = ftr.decorStyle || "none";
  const badgeLayout = ftr.badgeLayout || "grid-2";
  const badgeStyle = ftr.badgeStyle || "soft";
  const socialShape = ftr.socialShape || "circle";
  const socialSize = Number(ftr.socialSize ?? 40);
  const logoSize = Number(ftr.logoSize ?? 40);
  const newsletterStyle = ftr.newsletterStyle || "pill";

  const showBrand = ftr.showBrand !== false;
  const showSocial = ftr.showSocial !== false;
  const showLinks = ftr.showLinks !== false;
  const showNewsletter = ftr.showNewsletter !== false;
  const showBadges = ftr.showBadges !== false;
  const showSlogan = ftr.showSlogan !== false;
  const showCopyright = ftr.showCopyright !== false;
  const showBackToTop = ftr.showBackToTop !== false;

  const background =
    ftr.backgroundStyle === "gradient"
      ? `linear-gradient(165deg, ${backgroundFrom} 0%, ${backgroundTo} 100%)`
      : ftr.backgroundStyle === "mesh"
        ? `radial-gradient(900px 320px at 85% -10%, ${withAlpha(backgroundTo, 95)} 0%, transparent 62%), radial-gradient(700px 260px at 10% 110%, ${withAlpha(accent, 12)} 0%, transparent 60%), ${backgroundFrom}`
        : backgroundFrom;

  const gridTemplateColumns =
    layout === "stacked" ? "1fr" : layout === "two" ? "1fr 1fr" : "1.15fr 1.2fr 1fr";

  // ── content ──────────────────────────────────────────────────────────────
  const badges = [
    {
      on: ftr.badge1Enabled !== false,
      icon: ftr.badge1Icon || "shield",
      title: ftr.trustTitle || "اینماد رسمی",
      sub: ftr.trustSub || "اعتماد الکترونیکی",
      msg: ftr.trustText || "نماد اعتماد الکترونیکی (اینماد) فروشگاه ای‌بی ساکس فعال و معتبر است.",
    },
    {
      on: ftr.badge2Enabled !== false,
      icon: ftr.badge2Icon || "badge",
      title: ftr.legalTitle || "ساماندهی",
      sub: ftr.legalSub || "نشان ملی ثبت",
      msg: ftr.legalText || "نشان ملی ثبت رسانه‌های دیجیتال (ساماندهی) ثبت و تایید شده است.",
    },
    {
      on: ftr.badge3Enabled !== false,
      icon: ftr.badge3Icon || "card",
      title: ftr.gatewayLabel || "درگاه شتاب",
      sub: ftr.secureTitle || "پرداخت ۱۰۰٪ امن",
      msg: ftr.secureDesc || "پرداخت‌ها از طریق درگاه امن بانکی شتاب انجام می‌شوند.",
    },
    {
      on: ftr.badge4Enabled !== false,
      icon: ftr.badge4Icon || "bank",
      title: ftr.unionLabel || "اتحادیه‌ی منسوجات",
      sub: ftr.unionSub || "عضو رسمی",
      msg: ftr.unionText || "ای‌بی ساکس عضو رسمی اتحادیه‌ی تولیدکنندگان منسوجات است.",
    },
  ].filter((b) => b.on);

  const columns = [
    {
      on: ftr.col1Enabled !== false,
      title: ftr.col1Title || "بخش‌های فروشگاه",
      links: parseLinks(
        ftr.col1Links || ["کاتالوگ|#catalog", "باکس‌ساز|#pack", "داستان ما|#story", "گالری حرکت|#gallery"]
      ),
    },
    {
      on: ftr.col2Enabled !== false,
      title: ftr.col2Title || "خدمات مشتریان",
      links: parseLinks(
        ftr.col2Links || [
          "راهنمای هوشمند سایز|#size",
          "سوالات متداول|#faq",
          "تعویض ۷ روزه بی‌قیدوشرط|",
          `ساعت پاسخگویی: ${contact.hours}|`,
        ]
      ),
    },
    {
      on: ftr.col3Enabled === true,
      title: ftr.col3Title || "شبکه‌های اجتماعی",
      links: parseLinks(ftr.col3Links || []),
    },
  ].filter((c) => c.on);

  const socialBtn: React.CSSProperties = {
    width: socialSize,
    height: socialSize,
    borderRadius: socialShape === "square" ? Math.round(socialSize / 4) : 999,
    border: `1px solid ${hairline}`,
    color: textColor,
    transition: "border-color .2s, color .2s",
  };

  return (
    <footer
      id="footer"
      data-lb-section="footer"
      style={{ background, color: textColor, position: "relative", overflow: "hidden" }}
    >
      {decor === "dots" && (
        <div
          aria-hidden
          style={{
            position: "absolute",
            inset: 0,
            backgroundImage: `radial-gradient(${withAlpha(accent, 45)} 1px, transparent 1px)`,
            backgroundSize: "22px 22px",
            opacity: 0.16,
            pointerEvents: "none",
          }}
        />
      )}
      {decor === "glow" && (
        <div
          aria-hidden
          style={{
            position: "absolute",
            inset: "-10% 0 auto 0",
            height: "60%",
            background: `radial-gradient(60% 100% at 50% 0%, ${withAlpha(accent, 26)} 0%, transparent 70%)`,
            pointerEvents: "none",
          }}
        />
      )}

      <div
        style={{
          position: "relative",
          margin: "0 auto",
          maxWidth: contentWidth,
          padding: `${paddingTop}px 16px ${paddingBottom}px`,
          textAlign: center ? "center" : "start",
        }}
      >
        <div
          style={{
            display: "grid",
            gridTemplateColumns,
            gap: columnGap,
            paddingBottom: ftr.showTopDivider === false ? 0 : 40,
            borderBottom: ftr.showTopDivider === false ? "none" : `1px solid ${hairline}`,
            justifyItems: center ? "center" : "stretch",
          }}
        >
          {/* ── brand ─────────────────────────────────────────────────────── */}
          {showBrand && (
            <div style={{ display: "flex", flexDirection: "column", gap: 16, alignItems: center ? "center" : "flex-start" }}>
              <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                <span
                  style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    width: logoSize,
                    height: logoSize,
                    borderRadius: Math.round(logoSize / 2.5),
                    background: accent,
                    color: "#ffffff",
                  }}
                >
                  <svg viewBox="0 0 120 120" style={{ width: logoSize * 0.6, height: logoSize * 0.6 }}>
                    <path
                      d="M46 14 H76 V58 C76 70 70 77 60 83 L40 95 C30 101 18 96 14 87 C10 78 14 66 24 60 L46 47 V14 Z"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="9"
                      strokeLinejoin="round"
                    />
                  </svg>
                </span>
                <span style={{ fontSize: 18, fontWeight: 900, color: textColor }}>
                  {ftr.brandTitle || "ای‌بی ساکس"}{" "}
                  <span style={{ color: accent }}>/</span>{" "}
                  <span style={{ fontSize: 13, fontWeight: 700, letterSpacing: "0.25em" }} dir="ltr">
                    {ftr.brandSubtitle || "AB SOCKS"}
                  </span>
                </span>
              </div>
              <p style={{ maxWidth: 380, fontSize: 13, lineHeight: 1.9, color: muted, margin: 0 }}>
                {ftr.tagline ||
                  "کارگاه بافندگی و فروشگاه آنلاین جوراب‌های طرح‌دار، اسپرت و اکسسوری استریت‌استایل؛ جایی که هر جفت، یک اثر کوچکِ قابل پوشیدن است. ارسال سریع به سراسر ایران با ضمانت دوام بافت."}
              </p>
              {showSocial && (
                <div style={{ display: "flex", gap: 8 }}>
                  <a
                    href={ftr.socialLinks?.instagram || `https://instagram.com/${contact.instagram}`}
                    target="_blank"
                    rel="noreferrer"
                    aria-label={ftr.instagramLabel || "اینستاگرام"}
                    style={{ ...socialBtn, display: "flex", alignItems: "center", justifyContent: "center" }}
                  >
                    <AtSign style={{ width: socialSize * 0.45, height: socialSize * 0.45 }} />
                  </a>
                  <a
                    href={ftr.socialLinks?.telegram || `https://t.me/${contact.telegram}`}
                    target="_blank"
                    rel="noreferrer"
                    aria-label={ftr.telegramLabel || "تلگرام"}
                    style={{ ...socialBtn, display: "flex", alignItems: "center", justifyContent: "center" }}
                  >
                    <Send style={{ width: socialSize * 0.45, height: socialSize * 0.45 }} />
                  </a>
                </div>
              )}
            </div>
          )}

          {/* ── link columns ──────────────────────────────────────────────── */}
          {showLinks && columns.length > 0 && (
            <div
              style={{
                display: "grid",
                gridTemplateColumns: `repeat(${Math.min(columns.length, 3)}, minmax(0, 1fr))`,
                gap: 24,
                fontSize: 13,
                justifyItems: center ? "center" : "start",
              }}
            >
              {columns.map((col) => (
                <div key={col.title} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  <h4 style={{ margin: 0, fontSize: 13, fontWeight: 900, color: textColor }}>{col.title}</h4>
                  <ul style={{ listStyle: "none", margin: 0, padding: 0, display: "flex", flexDirection: "column", gap: 10, color: muted }}>
                    {col.links.map((link, i) => (
                      <li key={`${col.title}-${i}`}>
                        {link.target.startsWith("#") ? (
                          <button
                            type="button"
                            onClick={() =>
                              document.getElementById(link.target.slice(1))?.scrollIntoView({ behavior: "smooth" })
                            }
                            style={{ background: "none", border: 0, padding: 0, color: "inherit", cursor: "pointer", font: "inherit" }}
                            className="ab-footer-link"
                          >
                            {link.label}
                          </button>
                        ) : link.target ? (
                          <a href={link.target} style={{ color: "inherit", textDecoration: "none" }}>
                            {link.label}
                          </a>
                        ) : (
                          <span style={{ color: faint }}>{link.label}</span>
                        )}
                      </li>
                    ))}
                  </ul>
                </div>
              ))}
            </div>
          )}

          {/* ── newsletter + trust badges ─────────────────────────────────── */}
          {(showNewsletter || showBadges) && (
            <div style={{ display: "flex", flexDirection: "column", gap: 18, width: "100%" }}>
              {showNewsletter && (
                <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                  <h4 style={{ margin: 0, fontSize: 13, fontWeight: 900, color: textColor }}>
                    {ftr.newsletterTitle || "خبرنامه‌ی قدم‌به‌قدم"}
                  </h4>
                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      if (!email.trim()) return;
                      toast(
                        ftr.newsletterNote ||
                          "به خبرنامه‌ی ای‌بی ساکس پیوستی! کد هدیه‌ی عضویت برات پیامک می‌شه."
                      );
                      setEmail("");
                    }}
                    style={{
                      display: "flex",
                      alignItems: "center",
                      gap: 4,
                      padding: 4,
                      borderRadius: newsletterStyle === "card" ? radius : 999,
                      border: `1px solid ${hairline}`,
                      background: withAlpha(textColor, 6),
                      overflow: "hidden",
                    }}
                  >
                    <input
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder={ftr.newsletterPlaceholder || "شماره موبایل یا ایمیل"}
                      style={{
                        flex: "1 1 auto",
                        minWidth: 0,
                        background: "transparent",
                        border: 0,
                        outline: "none",
                        padding: "0 12px",
                        fontSize: 12,
                        color: textColor,
                        fontFamily: "inherit",
                      }}
                    />
                    <button
                      type="submit"
                      style={{
                        flex: "0 0 auto",
                        borderRadius: newsletterStyle === "card" ? Math.max(4, radius - 4) : 999,
                        background: button,
                        color: "#ffffff",
                        border: 0,
                        padding: "9px 16px",
                        fontSize: 12,
                        fontWeight: 800,
                        cursor: "pointer",
                        fontFamily: "inherit",
                      }}
                    >
                      {ftr.newsletterBtnText || "عضویت"}
                    </button>
                  </form>
                </div>
              )}

              {showBadges && badges.length > 0 && (
                <div
                  style={{
                    display: "grid",
                    gridTemplateColumns:
                      badgeLayout === "grid-1" ? "1fr" : badgeLayout === "row" ? `repeat(${badges.length}, minmax(0,1fr))` : "1fr 1fr",
                    gap: 10,
                  }}
                >
                  {badges.map((b) => {
                    const Icon = badgeIcons[String(b.icon).toLowerCase()] || ShieldCheck;
                    const solid = badgeStyle === "solid";
                    return (
                      <button
                        key={b.title}
                        type="button"
                        onClick={() => toast(b.msg, "info")}
                        style={{
                          display: "flex",
                          alignItems: "center",
                          gap: 10,
                          padding: "10px 12px",
                          borderRadius: radius,
                          border: `1px solid ${solid ? "transparent" : hairline}`,
                          background: solid ? withAlpha(accent, 14) : withAlpha(textColor, 6),
                          color: textColor,
                          cursor: "pointer",
                          textAlign: "start",
                          fontFamily: "inherit",
                        }}
                      >
                        <span
                          style={{
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            width: 34,
                            height: 34,
                            flex: "0 0 auto",
                            borderRadius: Math.max(6, radius - 4),
                            background: withAlpha(accent, 16),
                            color: accent,
                          }}
                        >
                          <Icon style={{ width: 18, height: 18 }} />
                        </span>
                        <span style={{ minWidth: 0 }}>
                          <span style={{ display: "block", fontSize: 11, fontWeight: 800, color: textColor }}>
                            {b.title}
                          </span>
                          <span style={{ display: "block", fontSize: 9.5, color: muted }}>{b.sub}</span>
                        </span>
                      </button>
                    );
                  })}
                </div>
              )}
            </div>
          )}
        </div>

        {/* ── bottom bar ──────────────────────────────────────────────────── */}
        <div
          style={{
            display: "flex",
            flexDirection: bottomAlign === "center" ? "column" : "row",
            alignItems: "center",
            justifyContent: bottomAlign === "center" ? "center" : "space-between",
            gap: 12,
            paddingTop: 24,
            fontSize: 11,
            color: faint,
            flexWrap: "wrap",
          }}
        >
          {showCopyright && (
            <p style={{ margin: 0 }}>
              {ftr.copyright ||
                (ftr.copyrightText || "© ۱۴۰۴ ای‌بی ساکس ({handle}) — تمامی حقوق محفوظ است.").replace(
                  "{handle}",
                  contact.instagram
                )}
            </p>
          )}
          {showSlogan && (
            <p style={{ margin: 0, letterSpacing: "0.25em" }} dir="ltr">
              {ftr.sloganEnglish || "EVERY STEP IS A PIECE OF ART"}
            </p>
          )}
          {showBackToTop && (
            <button
              type="button"
              onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
              style={{
                display: "flex",
                alignItems: "center",
                gap: 6,
                padding: "8px 14px",
                borderRadius: 999,
                border: `1px solid ${hairline}`,
                background: "transparent",
                color: textColor,
                fontWeight: 700,
                fontSize: 11,
                cursor: "pointer",
                fontFamily: "inherit",
              }}
            >
              {ftr.backToTopText || "برگرد بالا"} <ArrowUp style={{ width: 14, height: 14 }} />
            </button>
          )}
        </div>
      </div>
    </footer>
  );
}

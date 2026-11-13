import { useId } from "react";
import type { Product } from "../data/products";

/**
 * ProductArt — the "live color" SVG engine.
 * Renders every catalog item as a vector object so colors can morph
 * live (the ARGON live-customizer idea) without any image assets.
 */
export function ProductArt({
  product,
  colorIndex = 0,
  className,
}: {
  product: Product;
  colorIndex?: number;
  className?: string;
}) {
  if (product.image) {
    const src = product.image.startsWith("/") || product.image.startsWith("http")
      ? product.image
      : `/uploads/${product.image}`;
    return (
      <img
        src={src}
        alt={product.title}
        className={className ? `${className} object-contain` : "object-contain"}
        loading="lazy"
      />
    );
  }

  const uid = useId().replace(/[:]/g, "");
  const colors = product.colors?.length ? product.colors : [{ name: "پیش‌فرض", hex: "#e85d1f", secondary: "#f6efe0" }];
  const cw = colors[Math.min(colorIndex, colors.length - 1)];
  const primary = cw.hex;
  const secondary = cw.secondary ?? "#f6efe0";
  const fillStyle = { transition: "fill .5s ease, stroke .5s ease" } as const;
  const clipId = `clip-${uid}`;
  const shadeId = `shade-${uid}`;

  const sockBody =
    "M46 14 H76 V58 C76 70 70 77 60 83 L40 95 C30 101 18 96 14 87 C10 78 14 66 24 60 L46 47 V14 Z";

  const pattern = (() => {
    switch (product.pattern) {
      case "checker":
        return (
          <g style={fillStyle}>
            {[0, 1, 2].map((r) =>
              [0, 1, 2, 3].map((c) => (
                <rect
                  key={`${r}${c}`}
                  x={46 + c * 8}
                  y={26 + r * 9}
                  width={8}
                  height={9}
                  fill={(r + c) % 2 === 0 ? secondary : primary}
                  style={fillStyle}
                />
              ))
            )}
          </g>
        );
      case "flame":
        return (
          <path
            d="M52 66 C48 58 56 54 54 46 C60 52 66 48 64 38 C72 50 76 60 68 68 C62 73 55 71 52 66 Z"
            fill={secondary}
            style={fillStyle}
          />
        );
      case "wave":
        return (
          <g stroke={secondary} strokeWidth={4} fill="none" strokeLinecap="round" style={fillStyle}>
            <path d="M44 34 Q54 28 64 34 T84 34" />
            <path d="M44 46 Q54 40 64 46 T84 46" />
            <path d="M44 58 Q54 52 64 58 T80 60" />
          </g>
        );
      case "sport":
        return (
          <g style={fillStyle}>
            <path d="M46 40 L78 70 L78 58 L46 28 Z" fill={secondary} style={fillStyle} />
            <path d="M46 26 L78 56 L78 50 L46 20 Z" fill="#ffffff" opacity={0.85} />
          </g>
        );
      case "dots":
        return (
          <g style={fillStyle}>
            <circle cx={55} cy={34} r={4.5} fill={secondary} style={fillStyle} />
            <circle cx={67} cy={46} r={4.5} fill="#ffffff" />
            <circle cx={55} cy={58} r={4.5} fill={secondary} style={fillStyle} />
            <circle cx={30} cy={72} r={4.5} fill="#ffffff" />
          </g>
        );
      case "stripes":
        return (
          <g style={fillStyle}>
            <rect x={18} y={30} width={86} height={7} fill={secondary} style={fillStyle} />
            <rect x={14} y={42} width={90} height={7} fill={secondary} opacity={0.7} style={fillStyle} />
          </g>
        );
      default:
        return (
          <rect x={46} y={30} width={30} height={5} rx={2.5} fill={secondary} style={fillStyle} />
        );
    }
  })();

  if (product.art === "beanie") {
    return (
      <svg viewBox="0 0 120 120" className={className} role="img" aria-label={product.title}>
        <ellipse cx="60" cy="108" rx="34" ry="6" fill="#221510" opacity="0.12" />
        <path
          d="M28 78 C28 44 42 26 60 26 C78 26 92 44 92 78 L92 82 L28 82 Z"
          fill={primary}
          style={fillStyle}
        />
        <path d="M60 26 C54 20 62 14 66 18 C70 14 78 20 72 26" fill={secondary} style={fillStyle} />
        {[38, 48, 58, 68, 78].map((x) => (
          <path key={x} d={`M${x} 34 C${x - 3} 50 ${x - 3} 64 ${x} 78`} stroke="#221510" strokeWidth={2} opacity={0.12} fill="none" />
        ))}
        <rect x="24" y="78" width="72" height="20" rx="8" fill={secondary} style={fillStyle} />
        {[32, 44, 56, 68, 80, 90].map((x) => (
          <path key={x} d={`M${x} 80 V96`} stroke="#221510" strokeWidth={2.5} opacity={0.18} />
        ))}
        <text x="60" y="93" textAnchor="middle" fontSize="9" fontWeight="900" fill={primary} style={fillStyle}>
          AB
        </text>
      </svg>
    );
  }

  if (product.art === "pin") {
    return (
      <svg viewBox="0 0 120 120" className={className} role="img" aria-label={product.title}>
        <ellipse cx="60" cy="108" rx="30" ry="6" fill="#221510" opacity="0.12" />
        <circle cx="60" cy="58" r="34" fill={primary} style={fillStyle} />
        <circle cx="60" cy="58" r="34" fill="none" stroke={secondary} strokeWidth="5" style={fillStyle} />
        <circle cx="48" cy="50" r="5" fill="#221510" />
        <circle cx="72" cy="50" r="5" fill="#221510" />
        <path d="M44 66 Q60 80 76 66" stroke="#221510" strokeWidth="5" strokeLinecap="round" fill="none" />
      </svg>
    );
  }

  return (
    <svg viewBox="0 0 120 120" className={className} role="img" aria-label={product.title}>
      <defs>
        <clipPath id={clipId}>
          <path d={sockBody} />
        </clipPath>
        <linearGradient id={shadeId} x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stopColor="#000000" stopOpacity="0" />
          <stop offset="100%" stopColor="#000000" stopOpacity="0.22" />
        </linearGradient>
      </defs>
      <ellipse cx="52" cy="108" rx="36" ry="6" fill="#221510" opacity="0.12" />

      {/* cuff */}
      <rect x="46" y="14" width="30" height="10" rx="2" fill={secondary} stroke="#221510" strokeWidth="3" style={fillStyle} />
      {[52, 60, 68].map((x) => (
        <path key={x} d={`M${x} 16 V22`} stroke="#221510" strokeWidth="1.6" opacity="0.25" />
      ))}

      {/* body */}
      <path d={sockBody} fill={primary} stroke="#221510" strokeWidth="3.2" strokeLinejoin="round" style={fillStyle} />

      <g clipPath={`url(#${clipId})`}>
        {pattern}
        {/* heel */}
        <path d="M76 56 C66 56 60 63 62 73 L70 68 C74 65 76 61 76 56 Z" fill={secondary} style={fillStyle} />
        {/* toe */}
        <path d="M24 60 C14 66 10 78 14 87 C17 94 25 98 33 97 L19 65 C20 63 22 61 24 60 Z" fill={secondary} style={fillStyle} />
        <path d={sockBody} fill={`url(#${shadeId})`} />
      </g>

      {/* soft highlight */}
      <path d="M50 20 C54 36 54 44 50 50" stroke="#ffffff" strokeWidth="4" strokeLinecap="round" opacity="0.35" />
      <path d={sockBody} fill="none" stroke="#221510" strokeWidth="3.2" strokeLinejoin="round" />
    </svg>
  );
}

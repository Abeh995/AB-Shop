import React from "react";

export interface CategoryPageProps {
  categorySlug?: string;
}

/**
 * AB-Socks Customer Storefront — Category Archive Page (Scaffolded)
 * Ready for React / Glassmorphism implementation.
 */
export default function CategoryPage({ categorySlug }: CategoryPageProps) {
  return (
    <div className="min-h-screen bg-cream-50 text-coffee-950 p-6" dir="rtl">
      <div className="max-w-6xl mx-auto backdrop-blur-md bg-white/40 border border-white/60 rounded-3xl p-8 shadow-xl">
        <h1 className="text-3xl font-black mb-4">آرشیو دسته‌بندی</h1>
        <p className="text-coffee-700">اسکفولد اولیه کاتالوگ دسته‌بندی: {categorySlug || "همه"}</p>
      </div>
    </div>
  );
}


/** Format a number with Persian digits and thousand separators. */
export const faNum = (n: number | string): string => Number(n).toLocaleString("fa-IR");

/** Format a toman price with Persian digits. */
export const faPrice = (n: number): string => Number(n).toLocaleString("fa-IR");

/** Convert latin digits in a string to Persian digits. */
export const faDigits = (s: string | number): string =>
  String(s).replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[Number(d)]);

export const FREE_SHIPPING_THRESHOLD = 500_000;

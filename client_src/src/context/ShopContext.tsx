import { createContext, useCallback, useContext, useMemo, useRef, useState, type ReactNode } from "react";

export type ModalName = "product" | "style" | "wheel" | "track" | null;

export interface ModalState {
  name: ModalName;
  payload?: { productId?: number; colorIndex?: number };
}

export interface CartItem {
  key: string;
  name: string;
  price: number;
  qty: number;
  productId?: number;
  productIds?: number[];
  variantId?: number;
  colorIndex?: number;
  color?: string;
  size?: string;
  image?: string;
  note?: string;
  isPack?: boolean;
}

interface Toast {
  id: number;
  msg: string;
  tone: "ok" | "info";
}

interface ShopContextValue {
  cart: CartItem[];
  addToCart: (item: Omit<CartItem, "key" | "qty">, qty?: number) => void;
  removeFromCart: (key: string) => void;
  updateQty: (key: string, delta: number) => void;
  cartCount: number;
  cartTotal: number;
  cartOpen: boolean;
  setCartOpen: (v: boolean) => void;
  modal: ModalState;
  openModal: (name: Exclude<ModalName, null>, payload?: ModalState["payload"]) => void;
  closeModal: () => void;
  toasts: Toast[];
  toast: (msg: string, tone?: Toast["tone"]) => void;
}

const ShopContext = createContext<ShopContextValue | null>(null);

export function ShopProvider({ children }: { children: ReactNode }) {
  const [cart, setCart] = useState<CartItem[]>(() => {
    if (typeof window !== "undefined" && window.__AB_INITIAL_DATA__?.cartItems?.length) {
      return window.__AB_INITIAL_DATA__.cartItems;
    }
    return [];
  });
  const [cartOpen, setCartOpen] = useState(false);
  const [modal, setModal] = useState<ModalState>({ name: null });
  const [toasts, setToasts] = useState<Toast[]>([]);
  const toastId = useRef(0);

  const toast = useCallback((msg: string, tone: Toast["tone"] = "ok") => {
    const id = ++toastId.current;
    setToasts((t) => [...t.slice(-2), { id, msg, tone }]);
    window.setTimeout(() => setToasts((t) => t.filter((x) => x.id !== id)), 3200);
  }, []);

  const [serverCartCount, setServerCartCount] = useState<number>(() => {
    return (typeof window !== "undefined" && window.__AB_INITIAL_DATA__?.cartCount) || 0;
  });

  const addToCart = useCallback(
    (item: Omit<CartItem, "key" | "qty">, qty = 1) => {
      const key = item.isPack
        ? `pack-${Date.now()}`
        : (item.variantId ? `${item.productId}-${item.variantId}` : `p-${item.productId}-${item.color ?? ""}-${item.size ?? ""}`);

      setCart((c) => {
        const existing = c.find((x) => x.key === key);
        if (existing) return c.map((x) => (x.key === key ? { ...x, qty: x.qty + qty } : x));
        return [...c, { ...item, key, qty }];
      });
      toast(`${item.name} به سبد اضافه شد`);

      const csrf = (typeof window !== "undefined" && window.__AB_INITIAL_DATA__?.csrfToken) || "";
      if (csrf) {
        const fd = new FormData();
        fd.append("csrf_token", csrf);

        if (item.isPack) {
          fd.append("name", item.name);
          fd.append("price", String(item.price));
          fd.append("note", item.note || "");
          if (item.productIds?.length) {
            fd.append("product_ids", JSON.stringify(item.productIds));
          }
          fetch("/ajax/cart_pack_add.php", { method: "POST", body: fd })
            .then((r) => r.json())
            .then((res) => {
              if (res.ok && typeof res.cartCount === "number") {
                setServerCartCount(res.cartCount);
              }
            })
            .catch(() => {});
        } else if (item.productId) {
          fd.append("product_id", String(item.productId));
          if (item.variantId) {
            fd.append("variant_id", String(item.variantId));
          }
          fd.append("qty", String(qty));
          fetch("/ajax/cart_add.php", { method: "POST", body: fd })
            .then((r) => r.json())
            .then((res) => {
              if (res.ok && typeof res.cartCount === "number") {
                setServerCartCount(res.cartCount);
              } else if (res.message) {
                toast(res.message, "info");
              }
            })
            .catch(() => {});
        }
      }
    },
    [toast]
  );

  const removeFromCart = useCallback((key: string) => {
    setCart((c) => c.filter((x) => x.key !== key));
    const csrf = (typeof window !== "undefined" && window.__AB_INITIAL_DATA__?.csrfToken) || "";
    if (csrf) {
      const fd = new FormData();
      fd.append("csrf_token", csrf);
      fd.append("key", key);
      fd.append("ajax", "1");
      fetch("/ajax/cart_remove.php", { method: "POST", body: fd })
        .then((r) => r.json())
        .then((res) => {
          if (res.ok && typeof res.cartCount === "number") {
            setServerCartCount(res.cartCount);
          }
        })
        .catch(() => {});
    }
  }, []);

  const updateQty = useCallback((key: string, delta: number) => {
    let nextQty = 0;
    setCart((c) => {
      const target = c.find((x) => x.key === key);
      nextQty = target ? Math.max(0, target.qty + delta) : 0;
      if (nextQty <= 0) return c.filter((x) => x.key !== key);
      return c.map((x) => (x.key === key ? { ...x, qty: nextQty } : x));
    });

    const csrf = (typeof window !== "undefined" && window.__AB_INITIAL_DATA__?.csrfToken) || "";
    if (csrf) {
      const fd = new FormData();
      fd.append("csrf_token", csrf);
      fd.append("key", key);
      fd.append("ajax", "1");
      if (nextQty > 0) {
        fd.append("qty", String(nextQty));
        fetch("/ajax/cart_update.php", { method: "POST", body: fd })
          .then((r) => r.json())
          .then((res) => {
            if (res.ok && typeof res.cartCount === "number") {
              setServerCartCount(res.cartCount);
            }
          })
          .catch(() => {});
      } else {
        fetch("/ajax/cart_remove.php", { method: "POST", body: fd })
          .then((r) => r.json())
          .then((res) => {
            if (res.ok && typeof res.cartCount === "number") {
              setServerCartCount(res.cartCount);
            }
          })
          .catch(() => {});
      }
    }
  }, []);

  const openModal = useCallback(
    (name: Exclude<ModalName, null>, payload?: ModalState["payload"]) => setModal({ name, payload }),
    []
  );
  const closeModal = useCallback(() => setModal({ name: null }), []);

  const localCartCount = useMemo(() => cart.reduce((s, x) => s + x.qty, 0), [cart]);
  const cartCount = Math.max(localCartCount, serverCartCount);
  const cartTotal = useMemo(() => cart.reduce((s, x) => s + x.qty * x.price, 0), [cart]);

  const value: ShopContextValue = {
    cart,
    addToCart,
    removeFromCart,
    updateQty,
    cartCount,
    cartTotal,
    cartOpen,
    setCartOpen,
    modal,
    openModal,
    closeModal,
    toasts,
    toast,
  };

  return <ShopContext.Provider value={value}>{children}</ShopContext.Provider>;
}

export function useShop(): ShopContextValue {
  const ctx = useContext(ShopContext);
  if (!ctx) throw new Error("useShop must be used inside ShopProvider");
  return ctx;
}

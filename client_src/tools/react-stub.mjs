export const createContext = (v) => ({ __ctx: v });
export const useContext = () => ({});
export const useEffect = () => {};
export const useState = (v) => [typeof v === 'function' ? v() : v, () => {}];
export const useMemo = (f) => f();
export const useCallback = (f) => f;
export const useRef = (v) => ({ current: v });
export default { createContext, useContext, useEffect, useState };

import path from "path";
import { fileURLToPath } from "url";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "src"),
    },
  },
  build: {
    outDir: path.resolve(__dirname, "../assets/dist/landing"),
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: "landing.bundle.js",
        chunkFileNames: "chunks/[name].js",
        assetFileNames: "landing.bundle.[ext]",
      },
    },
  },
});

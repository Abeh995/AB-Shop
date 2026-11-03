import * as esbuild from "esbuild";
import path from "path";
import { fileURLToPath } from "url";
import { createRequire } from "module";

const dir = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(dir, "..");
const require = createRequire(import.meta.url);

await esbuild.build({
  entryPoints: [path.join(root, "src/context/LandingConfigContext.tsx")],
  bundle: true,
  platform: "node",
  format: "cjs",
  outfile: "/tmp/landing-ctx.cjs",
  alias: {
    react: path.join(dir, "react-stub.mjs"),
    "react/jsx-runtime": path.join(dir, "jsx-stub.mjs"),
  },
  logLevel: "error",
});

const mod = require("/tmp/landing-ctx.cjs");
process.stdout.write(JSON.stringify(mod.defaultLandingConfig, null, 2) + "\n");

import { build } from 'esbuild';
import fs from 'node:fs';
const out = await build({
  stdin: {
    contents: "export { defaultLandingConfig } from './src/context/LandingConfigContext.tsx';",
    resolveDir: process.cwd(), loader: 'tsx',
  },
  bundle: true, write: false, format: 'esm', platform: 'node', external: ['react'],
  logLevel: 'silent',
});
fs.writeFileSync('./.cfg.mjs', out.outputFiles[0].text);
const mod = await import('./.cfg.mjs');
console.log(JSON.stringify(mod.defaultLandingConfig, null, 2));
fs.unlinkSync('./.cfg.mjs');

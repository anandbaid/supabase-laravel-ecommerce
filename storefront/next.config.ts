import path from "node:path";
import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // The storefront lives inside the Laravel repo, which has its own
  // package-lock.json; pin the workspace root to this folder.
  turbopack: { root: path.join(__dirname) },
  outputFileTracingRoot: path.join(__dirname),
};

export default nextConfig;

import Link from "next/link";
import { PackageSearch } from "lucide-react";

export default function NotFound() {
  return (
    <div className="mx-auto max-w-lg px-4 py-24 text-center">
      <PackageSearch className="mx-auto mb-4 h-12 w-12 text-gray-300" />
      <h1 className="mb-2 text-2xl font-bold">We couldn&apos;t find that page</h1>
      <p className="mb-6 text-gray-500">It may have been moved, or the product is no longer available.</p>
      <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white">Browse the shop</Link>
    </div>
  );
}

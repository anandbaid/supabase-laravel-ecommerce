"use client";

import { useEffect } from "react";

export default function ErrorPage({ error, retry }: { error: Error & { digest?: string }; retry: () => void }) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <div className="mx-auto max-w-lg px-4 py-24 text-center">
      <h1 className="mb-2 text-2xl font-bold">Something went wrong</h1>
      <p className="mb-6 text-gray-500">We couldn&apos;t load this page. Please try again.</p>
      <button onClick={() => retry()} className="rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white">Try again</button>
    </div>
  );
}

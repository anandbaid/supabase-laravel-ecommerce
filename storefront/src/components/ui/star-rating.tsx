const STAR =
  "M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z";

export function StarPath() {
  return <path d={STAR} />;
}

/** Five grey stars with a clipped amber layer on top, so 4.5 renders exactly. */
export function StarRating({ rating, size = "h-4 w-4", className = "" }: { rating: number; size?: string; className?: string }) {
  const value = Math.max(0, Math.min(5, rating));
  const stars = Array.from({ length: 5 }, (_, i) => (
    <svg key={i} className={`${size} shrink-0 fill-current`} viewBox="0 0 20 20">
      <StarPath />
    </svg>
  ));
  return (
    <span className={`relative inline-flex align-middle ${className}`} role="img" aria-label={`${value.toFixed(1)} out of 5 stars`}>
      <span className="inline-flex text-gray-200" aria-hidden>{stars}</span>
      <span className="absolute inset-y-0 left-0 flex overflow-hidden text-amber-400" style={{ width: `${(value / 5) * 100}%` }} aria-hidden>
        {stars}
      </span>
    </span>
  );
}

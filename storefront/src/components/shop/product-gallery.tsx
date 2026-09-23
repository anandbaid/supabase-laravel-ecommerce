"use client";

import { useRef, useState } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

export function ProductGallery({ images, name, inStock }: { images: string[]; name: string; inStock: boolean }) {
  const [index, setIndex] = useState(0);
  const [zoom, setZoom] = useState<{ x: number; y: number } | null>(null);
  const viewer = useRef<HTMLDivElement>(null);
  const count = images.length;
  const show = (i: number) => setIndex(((i % count) + count) % count);

  return (
    <div className="flex flex-col-reverse gap-4 md:flex-row">
      {count > 1 && (
        <div className="flex shrink-0 gap-2 overflow-x-auto pb-1 md:max-h-[420px] md:w-16 md:flex-col md:overflow-y-auto">
          {images.map((url, i) => (
            <button
              key={url}
              type="button"
              onClick={() => show(i)}
              aria-label={`View image ${i + 1}`}
              className={`h-16 w-16 shrink-0 overflow-hidden rounded-lg border-2 bg-gray-50 ${i === index ? "border-blue-600" : "border-transparent"}`}
            >
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={url} alt="" className="h-full w-full object-cover" />
            </button>
          ))}
        </div>
      )}

      <div className="relative min-w-0 flex-1">
        <div
          ref={viewer}
          className="relative aspect-square cursor-zoom-in select-none overflow-hidden rounded-xl bg-gray-50"
          onMouseMove={(e) => {
            if (window.matchMedia("(hover: none)").matches || !viewer.current) return;
            const r = viewer.current.getBoundingClientRect();
            setZoom({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 });
          }}
          onMouseLeave={() => setZoom(null)}
        >
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src={images[index]}
            alt={name}
            className="h-full w-full object-contain p-6 transition-transform duration-150 ease-out"
            style={zoom ? { transform: "scale(2)", transformOrigin: `${zoom.x}% ${zoom.y}%` } : undefined}
          />
          <span className={`absolute left-3 top-3 rounded-md px-2.5 py-1 text-xs font-medium text-white ${inStock ? "bg-green-600" : "bg-red-600"}`}>
            {inStock ? "In Stock" : "Out of Stock"}
          </span>
          <div className="pointer-events-none absolute bottom-2 right-2 rounded-full bg-black/50 px-2 py-1 text-[10px] text-white opacity-80">
            Hover to zoom
          </div>
        </div>
        {count > 1 && (
          <>
            <button
              type="button"
              onClick={() => show(index - 1)}
              aria-label="Previous image"
              className="absolute left-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/90 text-gray-600 shadow hover:bg-white"
            >
              <ChevronLeft className="h-4 w-4" />
            </button>
            <button
              type="button"
              onClick={() => show(index + 1)}
              aria-label="Next image"
              className="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/90 text-gray-600 shadow hover:bg-white"
            >
              <ChevronRight className="h-4 w-4" />
            </button>
          </>
        )}
      </div>
    </div>
  );
}

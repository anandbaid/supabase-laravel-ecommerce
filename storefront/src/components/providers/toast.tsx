"use client";

import { createContext, useCallback, useContext, useState, type ReactNode } from "react";
import { CircleCheck, Info, TriangleAlert, X } from "lucide-react";

type ToastType = "success" | "error" | "info";
type Toast = { id: number; message: string; type: ToastType };
type ShowToast = (message: string, type?: ToastType, durationMs?: number) => void;

const ToastContext = createContext<ShowToast>(() => {});

export const useToast = () => useContext(ToastContext);

const STYLES: Record<ToastType, string> = {
  success: "bg-emerald-50 border-emerald-500 text-emerald-900",
  error: "bg-red-50 border-red-500 text-red-900",
  info: "bg-blue-50 border-blue-500 text-blue-900",
};

const ICONS = { success: CircleCheck, error: TriangleAlert, info: Info };

let nextId = 1;

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const dismiss = useCallback((id: number) => setToasts((all) => all.filter((t) => t.id !== id)), []);

  const show = useCallback<ShowToast>(
    (message, type = "info", durationMs = 5000) => {
      const id = nextId++;
      setToasts((all) => [...all.slice(-3), { id, message, type }]);
      window.setTimeout(() => dismiss(id), durationMs);
    },
    [dismiss],
  );

  return (
    <ToastContext.Provider value={show}>
      {children}
      <div className="fixed right-4 top-20 z-[9999] flex max-w-[22rem] flex-col gap-2" aria-live="polite">
        {toasts.map((t) => {
          const Icon = ICONS[t.type];
          return (
            <div
              key={t.id}
              role="status"
              className={`toast-in flex items-start gap-2 rounded-lg border-l-4 px-4 py-3 text-sm shadow-lg ${STYLES[t.type]}`}
            >
              <Icon className="mt-0.5 h-4 w-4 shrink-0" aria-hidden />
              <span className="flex-1 break-words">{t.message}</span>
              <button onClick={() => dismiss(t.id)} aria-label="Dismiss" className="opacity-60 hover:opacity-100">
                <X className="h-4 w-4" />
              </button>
            </div>
          );
        })}
      </div>
    </ToastContext.Provider>
  );
}

export function money(amount: number | string | null | undefined): string {
  const n = Number(amount ?? 0);
  return "$" + n.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** 8.50 -> "8.5", 8.00 -> "8" (for percentages and thresholds shown in copy). */
export function trimNumber(n: number): string {
  return String(Number(n.toFixed(2)));
}

export function plural(word: string, count: number): string {
  return count === 1 ? word : `${word}s`;
}

export function formatDate(iso: string | null | undefined, withTime = false): string {
  if (!iso) return "";
  const d = new Date(iso);
  const date = d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
  if (!withTime) return date;
  const time = d.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit" });
  return `${date}, ${time}`;
}

export function capitalize(s: string): string {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
}

const STATUS_COLORS: Record<string, string> = {
  delivered: "bg-green-100 text-green-700",
  processing: "bg-blue-100 text-blue-700",
  pending: "bg-yellow-100 text-yellow-700",
  shipped: "bg-indigo-100 text-indigo-700",
  cancelled: "bg-red-100 text-red-700",
};

const PAYMENT_COLORS: Record<string, string> = {
  paid: "bg-green-100 text-green-700",
  failed: "bg-red-100 text-red-700",
  refunded: "bg-purple-100 text-purple-700",
};

const RETURN_COLORS: Record<string, string> = {
  requested: "bg-yellow-100 text-yellow-700",
  approved: "bg-blue-100 text-blue-700",
  rejected: "bg-red-100 text-red-700",
  refunded: "bg-purple-100 text-purple-700",
};

export const statusColor = (s: string) => STATUS_COLORS[s] ?? "bg-gray-100 text-gray-700";
export const paymentStatusColor = (s: string) => PAYMENT_COLORS[s] ?? "bg-yellow-100 text-yellow-700";
export const returnStatusColor = (s: string) => RETURN_COLORS[s] ?? "bg-gray-100 text-gray-700";

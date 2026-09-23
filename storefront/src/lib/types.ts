// JSON shapes returned by the Laravel storefront API (routes/api.php).

export type CategoryRef = { id: number; name: string; slug: string };

export type Category = CategoryRef & {
  description: string | null;
  image: string;
  parent_id: number | null;
  children?: Category[];
};

export type Product = {
  id: number;
  name: string;
  slug: string;
  price: number;
  discount_price: number | null;
  final_price: number;
  discount_percent: number | null;
  stock: number;
  max_qty: number;
  is_featured: boolean;
  image: string;
  image_small: string;
  category?: CategoryRef | null;
};

export type ProductDetail = Product & {
  description: string | null;
  sku: string;
  gallery: string[];
};

export type ReviewSummary = {
  average: number;
  count: number;
  breakdown: Record<string, { count: number; percent: number }>;
};

export type Review = {
  id: number;
  rating: number;
  title: string | null;
  body: string;
  is_verified_purchase: boolean;
  created_at: string;
  user: { id: number; name: string };
};

export type PaginationMeta = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
};

export type Paginated<T> = { data: T[]; meta: PaginationMeta };

export type User = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  address: string | null;
  is_admin: boolean;
  created_at: string | null;
};

export type Settings = {
  tax_rate: number;
  shipping_fee: number;
  free_shipping_threshold: number;
  max_qty_per_item: number;
  currency: string;
};

export type Address = {
  id: number;
  label: string;
  full_name: string;
  phone: string;
  line1: string;
  line2: string | null;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  is_default: boolean;
  formatted: string;
};

export type OrderItem = {
  id: number;
  product_id: number | null;
  product_name: string;
  product_slug: string | null;
  image: string | null;
  price: number;
  quantity: number;
  subtotal: number;
};

export type Order = {
  order_number: string;
  status: "pending" | "processing" | "shipped" | "delivered" | "cancelled" | string;
  payment_method: "cod" | "card" | string;
  payment_status: "unpaid" | "paid" | "failed" | "refunded" | string;
  payment_status_label: string;
  customer_name: string;
  customer_email: string;
  customer_phone: string | null;
  company_name: string | null;
  billing_address: string | null;
  shipping_address: string;
  notes: string | null;
  subtotal: number;
  discount_amount: number;
  coupon_code: string | null;
  tax_rate: number;
  tax_amount: number;
  shipping_amount: number;
  total: number;
  created_at: string;
  delivered_at: string | null;
  cancelled_at: string | null;
  cancellation_reason: string | null;
  return_status: string | null;
  return_status_label: string;
  return_reason: string | null;
  return_requested_at: string | null;
  return_window_expires_at: string | null;
  refund_amount: number | null;
  refunded_at: string | null;
  can_cancel: boolean;
  can_request_return: boolean;
  items_count?: number;
  items?: OrderItem[];
};

export type CartLine = { product: Product; qty: number; subtotal: number };

export type CartQuote = {
  items: CartLine[];
  item_count: number;
  subtotal: number;
  coupon: { code: string; type: string; value: number } | null;
  coupon_error: string | null;
  discount: number;
  tax_rate: number;
  tax_amount: number;
  shipping: number;
  free_shipping_threshold: number;
  free_shipping_remaining: number;
  total: number;
  notices: { product_id: number; message: string }[];
};

/** Result of a server action, returned to client components. */
export type ActionResult<T = null> =
  | { ok: true; message?: string; data: T }
  | { ok: false; message: string; errors?: Record<string, string[]>; status?: number };

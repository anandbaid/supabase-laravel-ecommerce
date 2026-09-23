# Storefront (Next.js)

The customer-facing shop, rebuilt in Next.js. The Laravel app in the parent
folder keeps doing everything else: it owns the database, business rules,
Stripe and the admin panel, and exposes a JSON API at `/api/v1` for this app.

```
Browser ──► Next.js storefront ──(server-side, Bearer token)──► Laravel /api/v1 ──► Supabase Postgres
                                                                   │
Admin ─────────────────────────────► Laravel Blade admin (/admin) ─┘
Stripe ─── webhook ────────────────► Laravel /stripe/webhook (unchanged)
```

- **The browser never calls Laravel directly.** Pages are Server Components
  and every change goes through a Server Action, both calling Laravel from the
  Next.js server. No CORS setup is needed.
- **Accounts are Supabase Auth accounts, same as the Blade site.** Customers
  can sign in on either one with the same email and password. Laravel returns
  the Supabase session tokens, and the storefront keeps them in httpOnly
  cookies (`ls_session`, `ls_refresh`). `src/proxy.ts` swaps them for fresh
  ones shortly before they expire.
- **The cart lives in the browser** (localStorage), so guests can shop
  without an account. Every price, stock limit, coupon, tax and shipping
  amount comes from Laravel (`POST /api/v1/cart/quote`). The order itself is
  created by `POST /api/v1/checkout`, which checks all of it again.
- **Card payments** use the same Stripe Checkout redirect as the Blade site.
  After paying, Stripe sends the customer back to
  `/checkout/success/{order}` on this storefront, and the existing webhook
  marks the order paid.

## Environment

Copy `.env.example` to `.env.local` (local) or set the variables on your host:

| Variable | Required | What it is |
| --- | --- | --- |
| `LARAVEL_API_URL` | yes | Laravel API base including `/api/v1`, e.g. `https://supabaseecom.onrender.com/api/v1` |
| `NEXT_PUBLIC_ADMIN_URL` | no | Admin panel link shown to admin accounts |
| `NEXT_PUBLIC_SUPABASE_URL`, `NEXT_PUBLIC_SUPABASE_ANON_KEY` | no | Live stock on product pages via Supabase Realtime (anon key only) |
| `NEXT_PUBLIC_SITE_NAME` | no | Store name (default "Let's Shop") |

On the **Laravel** side, add one variable:

| Variable | What it is |
| --- | --- |
| `FRONTEND_URL` | This storefront's public URL, e.g. `https://shop.example.com`. Stripe sends customers back here after card payment. |

## Run locally

Requires Node.js 20.9+.

```bash
cd storefront
npm install
cp .env.example .env.local   # point LARAVEL_API_URL at your Laravel app
npm run dev                  # http://localhost:3000
```

Other scripts: `npm run build`, `npm start`, `npm run lint`.

## Deploy

**Vercel:** import the repo and set **Root Directory** to `storefront`. Add
the environment variables and deploy.

**Render (Web Service):** set Root Directory to `storefront`,
Build Command to `npm ci && npm run build`, Start Command to
`npm start -- -p $PORT`, and add the environment variables.

The Laravel service keeps running where it is. Deploy the updated Laravel
code (it adds the `/api/v1` routes) and set `FRONTEND_URL` on it.

## Where things are

| Path | Contents |
| --- | --- |
| `src/app/` | Routes: home, shop, product, cart, checkout, login/register, account (orders, wishlist, addresses, profile), static pages |
| `src/app/actions/` | Server Actions (auth, cart quote, checkout, account changes) |
| `src/lib/api.ts` | Server-side client for the Laravel API |
| `src/proxy.ts` | Session refresh and login redirect for `/account` and `/checkout` |
| `src/components/providers/` | Cart (localStorage), session/wishlist, toasts |

The Laravel side of the API is `routes/api.php` and
`app/Http/Controllers/Api/`. Its tests are in `tests/Feature/Api/`.

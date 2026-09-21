# Switching Image Storage to Supabase Storage (S3-compatible)

Your product/category image uploads now go to **Supabase Storage** instead of local disk,
using Laravel's built-in S3 driver (Supabase Storage speaks the S3 API).

## What changed in this update

- `config/filesystems.php` — the `s3` disk now defaults `use_path_style_endpoint` to `true`
  (required for Supabase's S3 endpoint).
- `.env` — added `AWS_*` variables pointing at your Supabase project's S3 endpoint, and set
  `FILESYSTEM_DISK=s3` as the default.
- `app/Models/Product.php` and `app/Models/Category.php` — `imageUrl()` now builds the URL via
  `Storage::disk('s3')->url(...)` instead of the local `storage/` symlink path.
- `app/Http/Controllers/Admin/ProductController.php` and `CategoryController.php` — uploads and
  deletes now use `Storage::disk('s3')` instead of `Storage::disk('public')`.

## Steps you need to do

### 1. Install the S3 driver package

Laravel doesn't bundle the S3 adapter by default — run this in your project folder:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### 2. Create a Storage bucket in Supabase

In your Supabase project dashboard:
1. Go to **Storage** → **New bucket**.
2. Name it `shopease` (or whatever you want — just match `AWS_BUCKET` in `.env`).
3. Toggle it **Public** (so product images are viewable without signed URLs). If you'd rather
   keep it private, say so and I'll switch the app to generate signed/temporary URLs instead.

### 3. Get your S3-compatible access keys

In Supabase: **Project Settings → Storage → S3 Connection** (sometimes under
**Settings → API → Storage S3 credentials**, naming varies slightly by dashboard version).
Generate an **Access Key ID** and **Secret Access Key** there — these are separate from your
database password and your `anon`/`service_role` API keys.

### 4. Fill in your `.env`

Replace the placeholders with your real values:

```
AWS_ACCESS_KEY_ID=your_actual_access_key_id
AWS_SECRET_ACCESS_KEY=your_actual_secret_access_key
AWS_DEFAULT_REGION=ap-southeast-2          # match your Supabase project's region
AWS_BUCKET=shopease                        # the bucket name you created
AWS_ENDPOINT=https://<project-ref>.supabase.co/storage/v1/s3
AWS_URL=https://<project-ref>.supabase.co/storage/v1/object/public/shopease
AWS_USE_PATH_STYLE_ENDPOINT=true

FILESYSTEM_DISK=s3
```

Your `<project-ref>` is the same one in your `DB_HOST` — in your case it's already been filled
in as `shyyqqpvbjcjtugcyhyd`, so just swap in your real access key and secret.

> Note: `AWS_URL` includes `/object/public/<bucket>` — that's Supabase's public-read URL
> pattern, not a generic S3 pattern. It only works if the bucket is public (step 2). If your
> bucket is private, this URL won't resolve and you'd need signed URLs instead.

### 5. Clear config cache and test

```bash
php artisan config:clear
php artisan serve
```

Go to `/admin/products/create`, upload an image, save. Then check:
- **Supabase dashboard → Storage → shopease bucket** — the file should appear under a
  `products/` folder.
- The product's image should render correctly on the storefront (it's now being pulled from
  Supabase's CDN, not your server).

### 6. You can remove the local storage symlink dependency

Since images no longer live in `storage/app/public`, `php artisan storage:link` is no longer
required for product/category images. (It's harmless to leave in place — nothing else in this
app currently relies on the `public` disk — but you don't need to re-run it after a fresh clone
anymore.)

## Rolling back to local storage

If you ever want to go back to local disk storage, it's a two-line revert:
- In `.env`, set `FILESYSTEM_DISK=local` and change nothing else.
- In the two `imageUrl()` methods and the two admin controllers, swap `'s3'` back to `'public'`.

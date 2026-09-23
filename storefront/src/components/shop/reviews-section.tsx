"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import { BadgeCheck, LogIn, MessageSquareText, PenLine, Trash2 } from "lucide-react";
import { deleteReview, saveReview } from "@/app/actions/shop";
import { useSession } from "@/components/providers/session";
import { useToast } from "@/components/providers/toast";
import { Pagination } from "@/components/ui/pagination";
import { StarPath, StarRating } from "@/components/ui/star-rating";
import { formatDate, plural } from "@/lib/format";
import type { Paginated, Review, ReviewSummary } from "@/lib/types";

const LABELS = ["", "Poor", "Fair", "Good", "Very good", "Excellent"];

export function ReviewsSection({
  slug,
  summary,
  reviews,
  canReview,
  myReview,
}: {
  slug: string;
  summary: ReviewSummary;
  reviews: Paginated<Review> | null;
  canReview: boolean;
  myReview: Review | null;
}) {
  const { user } = useSession();
  const pathname = usePathname();
  const [formOpen, setFormOpen] = useState(Boolean(myReview));

  return (
    <section id="reviews" className="mt-8 scroll-mt-24 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
      <div className="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 className="text-xl font-bold text-slate-900">Customer Reviews</h2>
          <p className="mt-0.5 text-sm text-gray-500">
            {summary.count
              ? `What ${summary.count} ${plural("customer", summary.count)} think about this product`
              : "Be the first to share what you think"}
          </p>
        </div>
        {user ? (
          canReview && (
            <button
              type="button"
              onClick={() => setFormOpen(true)}
              className="inline-flex items-center gap-2 rounded-lg border border-blue-600 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50"
            >
              <PenLine className="h-4 w-4" /> {myReview ? "Edit your review" : "Write a review"}
            </button>
          )
        ) : (
          <Link
            href={`/login?next=${encodeURIComponent(pathname + "#reviews")}`}
            className="inline-flex items-center gap-2 rounded-lg border border-blue-600 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50"
          >
            <LogIn className="h-4 w-4" /> Log in to write a review
          </Link>
        )}
      </div>

      <div className="grid gap-8 lg:grid-cols-[280px_1fr]">
        <div className="h-fit rounded-xl bg-slate-50 p-5">
          {summary.count ? (
            <>
              <div className="flex items-center gap-4">
                <div className="text-5xl font-bold leading-none text-slate-900">{summary.average.toFixed(1)}</div>
                <div>
                  <StarRating rating={summary.average} size="h-5 w-5" />
                  <div className="mt-1 text-xs text-gray-500">Based on {summary.count} {plural("review", summary.count)}</div>
                </div>
              </div>
              <div className="mt-5 space-y-2">
                {[5, 4, 3, 2, 1].map((star) => {
                  const row = summary.breakdown[star] ?? { count: 0, percent: 0 };
                  return (
                    <div key={star} className="flex items-center gap-2 text-xs text-gray-600">
                      <span className="w-3 text-right">{star}</span>
                      <svg className="h-3 w-3 shrink-0 fill-amber-400" viewBox="0 0 20 20" aria-hidden><StarPath /></svg>
                      <div className="h-2 flex-1 overflow-hidden rounded-full bg-gray-200">
                        <div className="h-full rounded-full bg-blue-600" style={{ width: `${row.percent}%` }} />
                      </div>
                      <span className="w-9 text-right tabular-nums">{row.percent}%</span>
                    </div>
                  );
                })}
              </div>
            </>
          ) : (
            <div className="py-4 text-center">
              <div className="text-4xl font-bold leading-none text-gray-300">–</div>
              <StarRating rating={0} size="h-5 w-5" className="mt-3" />
              <p className="mt-2 text-xs text-gray-500">No ratings yet</p>
            </div>
          )}
        </div>

        <div className="min-w-0">
          {canReview && formOpen && (
            <ReviewForm slug={slug} myReview={myReview} onCancel={myReview ? undefined : () => setFormOpen(false)} />
          )}

          {reviews && reviews.data.length > 0 ? (
            <>
              {reviews.data.map((review, i) => (
                <ReviewItem key={review.id} review={review} first={i === 0} />
              ))}
              <div className="mt-4">
                <Pagination meta={reviews.meta} basePath={pathname} pageParam="reviews_page" />
              </div>
            </>
          ) : (
            <div className="py-10 text-center text-gray-500">
              <MessageSquareText className="mx-auto mb-2 h-8 w-8 text-gray-300" />
              <p className="text-sm">No reviews yet. Bought this? Tell others what you think.</p>
            </div>
          )}
        </div>
      </div>
    </section>
  );
}

function ReviewForm({ slug, myReview, onCancel }: { slug: string; myReview: Review | null; onCancel?: () => void }) {
  const router = useRouter();
  const toast = useToast();
  const [rating, setRating] = useState(myReview?.rating ?? 0);
  const [hover, setHover] = useState(0);
  const [title, setTitle] = useState(myReview?.title ?? "");
  const [body, setBody] = useState(myReview?.body ?? "");
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [pending, start] = useTransition();
  const shown = hover || rating;

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    start(async () => {
      const res = await saveReview(slug, { rating, title, body });
      if (!res.ok) {
        setErrors(res.errors ?? {});
        if (!res.errors) toast(res.message, "error");
        return;
      }
      setErrors({});
      toast(res.message ?? "Saved.", "success");
      router.refresh();
    });
  };

  const field = "mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500";

  return (
    <form onSubmit={submit} className="mb-6 rounded-xl border border-gray-200 p-5">
      <h3 className="mb-1 font-semibold text-slate-900">{myReview ? "Edit your review" : "Write a review"}</h3>
      <p className="mb-4 text-xs text-gray-500">Your name and rating will be shown publicly.</p>

      <span className="mb-1 block text-sm font-medium" id="rating-label">Your rating</span>
      <div className="mb-1 flex items-center gap-1" role="radiogroup" aria-labelledby="rating-label" onMouseLeave={() => setHover(0)}>
        {[1, 2, 3, 4, 5].map((v) => (
          <button
            key={v}
            type="button"
            role="radio"
            aria-checked={rating === v}
            aria-label={`${v} ${plural("star", v)}`}
            onMouseEnter={() => setHover(v)}
            onFocus={() => setHover(v)}
            onBlur={() => setHover(0)}
            onClick={() => setRating(v)}
            className={`rounded p-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 ${v <= shown ? "text-amber-400" : "text-gray-300"}`}
          >
            <svg className="h-8 w-8 fill-current" viewBox="0 0 20 20"><StarPath /></svg>
          </button>
        ))}
        <span className="ml-2 text-sm text-gray-500">{LABELS[shown]}</span>
      </div>
      {errors.rating && <p className="mt-1 text-xs text-red-600">{errors.rating[0]}</p>}

      <label className="mt-4 block text-sm font-medium">
        Headline <span className="font-normal text-gray-400">(optional)</span>
        <input value={title} onChange={(e) => setTitle(e.target.value)} maxLength={120} placeholder="Sum it up in a few words" className={field} />
      </label>
      {errors.title && <p className="mt-1 text-xs text-red-600">{errors.title[0]}</p>}

      <label className="mt-4 block text-sm font-medium">
        Your review
        <textarea value={body} onChange={(e) => setBody(e.target.value)} rows={4} maxLength={2000} placeholder="What did you like or dislike? How was the quality?" className={field} />
      </label>
      {errors.body && <p className="mt-1 text-xs text-red-600">{errors.body[0]}</p>}

      <div className="mt-4 flex items-center gap-3">
        <button disabled={pending} className="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-70">
          {pending ? "Saving…" : myReview ? "Update review" : "Post review"}
        </button>
        {onCancel && (
          <button type="button" onClick={onCancel} className="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
        )}
      </div>
    </form>
  );
}

function ReviewItem({ review, first }: { review: Review; first: boolean }) {
  const { user } = useSession();
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();
  const mine = user?.id === review.user.id;
  const canDelete = mine || user?.is_admin;

  return (
    <article className={`py-5 ${first ? "" : "border-t border-gray-100"}`}>
      <div className="flex items-start gap-3">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-700" aria-hidden>
          {(review.user.name || "?").charAt(0).toUpperCase()}
        </div>
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
            <span className="text-sm font-medium text-slate-900">{review.user.name}</span>
            {review.is_verified_purchase && (
              <span className="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700">
                <BadgeCheck className="h-3 w-3" /> Verified buyer
              </span>
            )}
            <span className="ml-auto text-xs text-gray-400">{formatDate(review.created_at)}</span>
          </div>
          <div className="mt-1"><StarRating rating={review.rating} /></div>
          {review.title && <h3 className="mt-2 text-sm font-semibold text-slate-900">{review.title}</h3>}
          <p className="mt-1 whitespace-pre-line break-words text-sm text-gray-600">{review.body}</p>
          {canDelete && (
            <button
              type="button"
              disabled={pending}
              onClick={() => {
                if (!window.confirm("Delete this review?")) return;
                start(async () => {
                  const res = await deleteReview(review.id);
                  toast(res.ok ? (res.message ?? "Review deleted.") : res.message, res.ok ? "success" : "error");
                  if (res.ok) router.refresh();
                });
              }}
              className="mt-2 inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700"
            >
              <Trash2 className="h-3 w-3" /> Delete{mine ? " my review" : ""}
            </button>
          )}
        </div>
      </div>
    </article>
  );
}

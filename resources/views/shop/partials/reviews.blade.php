{{-- Customer reviews: summary, list, and write/edit form. Expects: $product, $reviewSummary, $reviews, $canReview, $myReview --}}
<section id="reviews" class="scroll-mt-24 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mt-8">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Customer Reviews</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                @if($reviewSummary['count'])
                    What {{ $reviewSummary['count'] }} {{ Str::plural('customer', $reviewSummary['count']) }} think about this product
                @else
                    Be the first to share what you think
                @endif
            </p>
        </div>
        @auth
            @if($canReview)
                <a href="#review-form" data-open-review-form class="inline-flex items-center gap-2 border border-blue-600 text-blue-600 hover:bg-blue-50 text-sm font-medium px-4 py-2 rounded-lg">
                    <i data-lucide="pen-line" class="w-4 h-4"></i> {{ $myReview ? 'Edit your review' : 'Write a review' }}
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 border border-blue-600 text-blue-600 hover:bg-blue-50 text-sm font-medium px-4 py-2 rounded-lg">
                <i data-lucide="log-in" class="w-4 h-4"></i> Log in to write a review
            </a>
        @endauth
    </div>

    <div class="grid lg:grid-cols-[280px_1fr] gap-8">
        {{-- Summary --}}
        <div class="bg-slate-50 rounded-xl p-5 h-fit">
            @if($reviewSummary['count'])
                <div class="flex items-center gap-4">
                    <div class="text-5xl font-bold text-slate-900 leading-none">{{ number_format($reviewSummary['average'], 1) }}</div>
                    <div>
                        <x-star-rating :rating="$reviewSummary['average']" size="w-5 h-5" />
                        <div class="text-xs text-gray-500 mt-1">Based on {{ $reviewSummary['count'] }} {{ Str::plural('review', $reviewSummary['count']) }}</div>
                    </div>
                </div>
                <div class="mt-5 space-y-2">
                    @foreach($reviewSummary['breakdown'] as $star => $row)
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-3 text-right">{{ $star }}</span>
                            <svg class="w-3 h-3 fill-amber-400 shrink-0" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600 rounded-full" style="width: {{ $row['percent'] }}%"></div>
                            </div>
                            <span class="w-9 text-right tabular-nums">{{ $row['percent'] }}%</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <div class="text-4xl font-bold text-gray-300 leading-none">–</div>
                    <x-star-rating :rating="0" size="w-5 h-5" class="mt-3" />
                    <p class="text-xs text-gray-500 mt-2">No ratings yet</p>
                </div>
            @endif
        </div>

        {{-- List + form --}}
        <div class="min-w-0">
            @auth
                @if($canReview)
                    <form id="review-form" action="{{ route('reviews.store', $product->slug) }}" method="POST"
                          class="scroll-mt-24 border border-gray-200 rounded-xl p-5 mb-6 {{ ($myReview || $errors->hasAny(['rating', 'title', 'body'])) ? '' : 'hidden' }}">
                        @csrf
                        <h3 class="font-semibold text-slate-900 mb-1">{{ $myReview ? 'Edit your review' : 'Write a review' }}</h3>
                        <p class="text-xs text-gray-500 mb-4">Your name and rating will be shown publicly.</p>

                        <label class="text-sm font-medium block mb-1" id="rating-label">Your rating</label>
                        <div class="flex items-center gap-1 mb-1" role="radiogroup" aria-labelledby="rating-label" id="rating-stars" data-initial="{{ old('rating', $myReview->rating ?? 0) }}">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" data-value="{{ $i }}" role="radio" aria-checked="false" aria-label="{{ $i }} {{ Str::plural('star', $i) }}"
                                        class="rating-star p-0.5 text-gray-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </button>
                            @endfor
                            <span id="rating-text" class="ml-2 text-sm text-gray-500"></span>
                        </div>
                        <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', $myReview->rating ?? '') }}">
                        @error('rating')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

                        <div class="mt-4">
                            <label for="review-title" class="text-sm font-medium">Headline <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input id="review-title" type="text" name="title" maxlength="120" value="{{ old('title', $myReview->title ?? '') }}" placeholder="Sum it up in a few words"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="mt-4">
                            <label for="review-body" class="text-sm font-medium">Your review</label>
                            <textarea id="review-body" name="body" rows="4" maxlength="2000" placeholder="What did you like or dislike? How was the quality?"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('body', $myReview->body ?? '') }}</textarea>
                            @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="mt-4 flex items-center gap-3">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg">{{ $myReview ? 'Update review' : 'Post review' }}</button>
                            @unless($myReview)
                                <button type="button" data-close-review-form class="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                            @endunless
                        </div>
                    </form>
                @endif
            @endauth

            @forelse($reviews as $review)
                <article class="py-5 {{ $loop->first ? '' : 'border-t border-gray-100' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-semibold flex items-center justify-center shrink-0" aria-hidden="true">
                            {{ strtoupper(mb_substr($review->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="font-medium text-sm text-slate-900">{{ $review->user->name ?? 'Former customer' }}</span>
                                @if($review->is_verified_purchase)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                        <i data-lucide="badge-check" class="w-3 h-3"></i> Verified buyer
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400 ml-auto">{{ $review->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="mt-1"><x-star-rating :rating="$review->rating" /></div>
                            @if($review->title)
                                <h3 class="font-semibold text-sm text-slate-900 mt-2">{{ $review->title }}</h3>
                            @endif
                            <p class="text-sm text-gray-600 mt-1 whitespace-pre-line break-words">{{ $review->body }}</p>

                            @auth
                                @if(auth()->id() === $review->user_id || auth()->user()->isAdmin())
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this review?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500 hover:text-red-700 inline-flex items-center gap-1">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i> Delete{{ auth()->id() === $review->user_id ? ' my review' : '' }}
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                </article>
            @empty
                <div class="text-center py-10 text-gray-500">
                    <i data-lucide="message-square-text" class="w-8 h-8 mx-auto text-gray-300 mb-2"></i>
                    <p class="text-sm">No reviews yet. Bought this? Tell others what you think.</p>
                </div>
            @endforelse

            @if($reviews->hasPages())
                <div class="mt-4">{{ $reviews->links() }}</div>
            @endif
        </div>
    </div>
</section>

<script>
    (function () {
        var form = document.getElementById('review-form');
        var group = document.getElementById('rating-stars');
        if (!group) return;

        var input = document.getElementById('rating-input');
        var text = document.getElementById('rating-text');
        var stars = group.querySelectorAll('.rating-star');
        var labels = ['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'];
        var value = parseInt(group.dataset.initial, 10) || 0;

        function paint(n) {
            stars.forEach(function (btn) {
                var v = parseInt(btn.dataset.value, 10);
                btn.classList.toggle('text-amber-400', v <= n);
                btn.classList.toggle('text-gray-300', v > n);
                btn.setAttribute('aria-checked', v === value ? 'true' : 'false');
            });
            text.textContent = labels[n] || '';
        }

        stars.forEach(function (btn) {
            btn.addEventListener('mouseenter', function () { paint(parseInt(btn.dataset.value, 10)); });
            btn.addEventListener('focus', function () { paint(parseInt(btn.dataset.value, 10)); });
            btn.addEventListener('click', function () {
                value = parseInt(btn.dataset.value, 10);
                input.value = value;
                paint(value);
            });
        });
        group.addEventListener('mouseleave', function () { paint(value); });
        group.addEventListener('focusout', function () { paint(value); });
        paint(value);

        function openForm() {
            form.classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var body = document.getElementById('review-body');
            if (body && !value) { stars[0].focus(); }
        }
        document.querySelectorAll('[data-open-review-form]').forEach(function (a) {
            a.addEventListener('click', function (e) { e.preventDefault(); openForm(); });
        });
        var close = document.querySelector('[data-close-review-form]');
        if (close) close.addEventListener('click', function () { form.classList.add('hidden'); });
    })();
</script>

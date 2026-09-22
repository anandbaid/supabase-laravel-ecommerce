{{-- Renders a "Recently Viewed" strip from localStorage. Works for guests
     too, since it never touches the server — each product page just pushes
     a snapshot of itself into localStorage on view (see shop/show.blade.php),
     and this component reads that list back out on any page that includes it. --}}
<div id="recently-viewed-section" class="max-w-7xl mx-auto px-4 py-8 hidden">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
        <i data-lucide="history" class="w-5 h-5 text-blue-600"></i> Recently Viewed
    </h2>
    <div id="recently-viewed-list" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"></div>
</div>

<script>
(function () {
    function renderRecentlyViewed() {
        var section = document.getElementById('recently-viewed-section');
        var list = document.getElementById('recently-viewed-list');
        if (!section || !list) return;

        var items;
        try {
            items = JSON.parse(localStorage.getItem('recently_viewed') || '[]');
        } catch (e) {
            items = [];
        }

        var excludeId = window.__currentProductId || null;
        items = items.filter(function (p) { return p.id !== excludeId; });

        if (!items.length) {
            section.classList.add('hidden');
            return;
        }

        list.innerHTML = '';
        items.slice(0, 6).forEach(function (p) {
            var card = document.createElement('a');
            card.href = p.url;
            card.className = 'bg-white rounded-xl shadow-sm hover:shadow-md transition p-4 block';
            card.innerHTML =
                '<div class="h-24 flex items-center justify-center mb-2">' +
                    '<img src="' + p.image + '" alt="' + p.name + '" class="max-h-24 object-contain">' +
                '</div>' +
                '<div class="text-xs font-medium text-gray-800 line-clamp-2">' + p.name + '</div>' +
                '<div class="text-blue-600 text-sm font-semibold mt-1">$' + p.price + '</div>';
            list.appendChild(card);
        });

        section.classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', renderRecentlyViewed);
})();
</script>

{{-- Supabase Realtime client. Requires Realtime replication enabled on the
     `orders`, `order_items`, `products` and `categories` tables in the
     Supabase dashboard (Database > Replication). Uses only the public anon
     key, safe to expose to the browser. --}}
@if(config('services.supabase.url') && config('services.supabase.anon_key'))
<script type="module">
    import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm';

    const supabaseUrl = @json(config('services.supabase.url'));
    const supabaseAnonKey = @json(config('services.supabase.anon_key'));
    const currentUserEmail = @json(auth()->user()->email ?? null);
    const isAdmin = @json(auth()->check() && auth()->user()->isAdmin());

    let supabase;
    try {
        supabase = createClient(supabaseUrl, supabaseAnonKey);
    } catch (e) {
        console.error('Supabase Realtime client failed to initialize:', e);
    }

    function safeToast(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    }

    function updateStockBadge(productId, stock) {
        document.querySelectorAll('[data-stock-for="' + productId + '"]').forEach(function (el) {
            el.textContent = stock;
        });
        document.querySelectorAll('[data-in-stock-for="' + productId + '"]').forEach(function (el) {
            el.textContent = stock > 0 ? 'in stock' : 'Out of stock';
        });
    }

    function capitalize(s) {
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
    }

    function updateOrderStatusBadge(orderNumber, status, paymentStatus) {
        document.querySelectorAll('[data-order-status-for="' + orderNumber + '"]').forEach(function (el) {
            if (status) el.textContent = capitalize(status);
        });
        document.querySelectorAll('[data-payment-status-for="' + orderNumber + '"]').forEach(function (el) {
            if (paymentStatus) el.textContent = capitalize(paymentStatus);
        });
    }

    if (supabase) {
        try {
            // Live order updates: relevant to admins (all orders) and to the
            // customer who owns the order (matched by email, since we don't
            // expose a Supabase-authenticated realtime channel per user here).
            supabase
                .channel('public:orders')
                .on('postgres_changes', { event: '*', schema: 'public', table: 'orders' }, function (payload) {
                    const row = payload.new || payload.old || {};
                    const isOwnOrder = currentUserEmail && row.customer_email === currentUserEmail;

                    if (isAdmin) {
                        if (payload.eventType === 'INSERT') {
                            safeToast('New order received: ' + (row.order_number || ''), 'success');
                        } else if (payload.eventType === 'UPDATE') {
                            safeToast('Order ' + (row.order_number || '') + ' updated: ' + (row.status || ''), 'info');
                        }
                    } else if (isOwnOrder && payload.eventType === 'UPDATE') {
                        safeToast('Your order ' + (row.order_number || '') + ' is now "' + (row.status || '') + '"', 'info');
                    }

                    if (row.order_number) {
                        updateOrderStatusBadge(row.order_number, row.status, row.payment_status);
                    }
                })
                .subscribe(function (status) {
                    if (status === 'CHANNEL_ERROR') {
                        console.error('Supabase Realtime: orders channel failed to subscribe.');
                    }
                });

            // Live product/stock updates: useful on shop and admin product pages.
            supabase
                .channel('public:products')
                .on('postgres_changes', { event: 'UPDATE', schema: 'public', table: 'products' }, function (payload) {
                    const row = payload.new || {};
                    if (row.id !== undefined && row.stock !== undefined) {
                        updateStockBadge(row.id, row.stock);
                    }
                    if (isAdmin && row.stock !== undefined && row.stock <= 5) {
                        safeToast((row.name || 'A product') + ' is low on stock (' + row.stock + ' left)', 'error');
                    }
                })
                .on('postgres_changes', { event: 'INSERT', schema: 'public', table: 'products' }, function (payload) {
                    if (isAdmin) {
                        safeToast('New product added: ' + (payload.new?.name || ''), 'success');
                    }
                })
                .subscribe(function (status) {
                    if (status === 'CHANNEL_ERROR') {
                        console.error('Supabase Realtime: products channel failed to subscribe.');
                    }
                });
        } catch (e) {
            console.error('Supabase Realtime subscription failed:', e);
        }
    }
</script>
@endif

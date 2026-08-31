import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Laravel Reverb speaks the Pusher protocol, so laravel-echo's "reverb"
// broadcaster (or "pusher" pointed at the Reverb host) both work.
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

/**
 * Subscribe the storefront to a variant's live stock badge.
 * Used on the PDP so "In Stock / Low Stock / Out of Stock" updates without a reload.
 */
window.watchStock = function watchStock(sku, onUpdate) {
    return window.Echo.channel(`stock.${sku}`).listen('.StockAdjusted', onUpdate);
};

/**
 * Subscribe a logged-in customer to their order's live tracking timeline.
 */
window.watchOrder = function watchOrder(orderId, onUpdate) {
    return window.Echo.private(`order.${orderId}`).listen('.OrderStatusUpdated', onUpdate);
};

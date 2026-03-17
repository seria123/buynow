// resources/js/cart.js
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const cart = ref([]);
const cartCount = ref(0);
const isLoggedIn = ref(false);
const isLoading = ref(false);

// Promo code state
const appliedPromo = ref(null);
const promoDiscount = ref(0);

const DEBUG_CART = true;

// Computed total amount
const cartTotal = computed(() => {
  const total = parseFloat(
    cart.value.reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 0)), 0).toFixed(2)
  );
  // Apply promo discount if any
  return Math.max(0, total - promoDiscount.value);
});

// -----------------------------
// CSRF helper
// -----------------------------
const getCsrfToken = async () => {
  // Meta tag
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta?.content) return meta.content;

  // Cookie fallback
  const cookieMatch = document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='));
  if (cookieMatch) return decodeURIComponent(cookieMatch.split('=')[1]);

  // Request Sanctum CSRF
  await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
  const cookieMatch2 = document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='));
  if (cookieMatch2) return decodeURIComponent(cookieMatch2.split('=')[1]);

  console.warn('[Cart] No CSRF token found');
  return null;
};

// -----------------------------
// Debug fetch wrapper
// -----------------------------
const debugFetch = async (url, options = {}) => {
  if (DEBUG_CART) console.debug('[Cart] Fetch', url, options);
  const res = await fetch(url, options);
  if (DEBUG_CART) console.debug('[Cart] Response', url, res.status);
  return res;
};

// -----------------------------
// Auth status
// -----------------------------
const checkAuthStatus = async () => {
  try {
    const response = await fetch('/api/user', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    });
    isLoggedIn.value = response.ok;
  } catch {
    isLoggedIn.value = false;
  }
};

// -----------------------------
// Load cart from backend
// -----------------------------
const loadCart = async () => {
  try {
    const res = await fetch('/cart', {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
    });
    const data = await res.json();
    cart.value = data.cart || [];
    cartCount.value = data.cart_count || 0;
    
    // Load promo from session if available
    if (data.applied_promo) {
      appliedPromo.value = data.applied_promo;
      promoDiscount.value = data.promo_discount || 0;
    } else {
      appliedPromo.value = null;
      promoDiscount.value = 0;
    }
  } catch {
    cart.value = [];
    cartCount.value = 0;
    appliedPromo.value = null;
    promoDiscount.value = 0;
  }
};

// -----------------------------
// Cart actions
// ---// cart.js
const addToCart = async (productSlug, quantity = 1, variantId = null) => {
  const csrf = await getCsrfToken();
  if (!csrf) throw new Error('No CSRF token');

  const payload = { product_slug: productSlug, quantity }; // <-- send slug
  if (variantId) payload.variant_id = variantId;

  const res = await debugFetch('/cart/add', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({ message: 'Failed to add to cart' }));
    console.error('[Cart] Add to cart failed:', error);
    throw new Error(error.message || 'Failed to add to cart');
  }

  await loadCart();
};

const removeFromCart = async (productId) => {
  const csrf = await getCsrfToken();
  await debugFetch(`/cart/remove/${productId}`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': csrf || '',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
  await loadCart();
};

const clearCart = async () => {
  const csrf = await getCsrfToken();
  await debugFetch('/cart/clear', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': csrf || '',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
  cart.value = [];
  cartCount.value = 0;
};

  const checkout = async () => {
        isLoading.value = true;
        try {
            // Post checkout
            router.post(route('checkout'), {}, {
                onFinish: () => {
                    isLoading.value = false;
                    cart.value = [];
                    cartCount.value = 0;
                    cartTotal.value = 0;

                    // Automatically go to Orders page
                    router.visit(route('orders.index'));
                }
            });
        } catch (e) {
            isLoading.value = false;
            console.error(e);
        }
    };


// -----------------------------
// Export composable
// -----------------------------
export const useCart = () => ({
  cart,
  cartCount,
  cartTotal,
  isLoggedIn,
  isLoading,
  appliedPromo,
  promoDiscount,
  loadCart,
  checkAuthStatus,
  addToCart,
  removeFromCart,
  clearCart,
  checkout,
});
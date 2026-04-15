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

// Backend totals (NEVER calculate in JS)
const subtotal = ref(0);
const discount = ref(0);
const shipping = ref(0.00);
const total = ref(0);
const freeShippingEligible = ref(false);
const automaticPromo = ref(null);
const automaticDiscount = ref(0);
const automaticPromotions = ref([]);

const DEBUG_CART = true;

// Computed total amount - USE BACKEND VALUE ONLY
const cartTotal = computed(() => total.value);

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
    
    // Use backend totals (NEVER calculate in JS)
    subtotal.value = data.subtotal || 0;
    discount.value = data.promo_discount || data.automatic_discount || 0;
    shipping.value = data.free_shipping_eligible ? 0 : 0.00;
    total.value = Math.max(0, subtotal.value - discount.value + shipping.value);
    freeShippingEligible.value = data.free_shipping_eligible || false;
    automaticPromo.value = data.automatic_promo || null;
    automaticDiscount.value = data.automatic_discount || 0;
    automaticPromotions.value = data.automatic_promotions || [];
    
    // Load promo from session if available
    if (data.applied_promo_code) {
      appliedPromo.value = data.applied_promo_code;
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
    subtotal.value = 0;
    discount.value = 0;
    shipping.value = 0.00;
    total.value = 0;
    freeShippingEligible.value = false;
    automaticPromo.value = null;
    automaticDiscount.value = 0;
    automaticPromotions.value = [];
  }
};

// -----------------------------
// Cart actions
// ---// cart.js
const addToCart = async (productSlug, quantity = 1, variantId = null) => {
  const csrf = await getCsrfToken();
  if (!csrf) throw new Error('No CSRF token');

  const payload = { product_slug: productSlug, quantity }; // <-- send slug
  if (variantId !== null && variantId !== undefined) payload.variant_id = parseInt(variantId, 10);

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

// Update cart item quantity
const updateCartQuantity = async (productId, quantity, variantId = null) => {
  const csrf = await getCsrfToken();
  if (!csrf) throw new Error('No CSRF token');

  const payload = { product_id: productId, quantity };
  if (variantId !== null && variantId !== undefined) payload.variant_id = parseInt(variantId, 10);

  const res = await debugFetch('/cart/update-quantity', {
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
    const error = await res.json().catch(() => ({ message: 'Failed to update quantity' }));
    console.error('[Cart] Update quantity failed:', error);
    throw new Error(error.message || 'Failed to update quantity');
  }

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
  appliedPromo.value = null;
  promoDiscount.value = 0;
};

// Apply promo code
const applyPromoCode = async (code) => {
  const csrf = await getCsrfToken();
  if (!csrf) throw new Error('No CSRF token');

  const subtotal = cart.value.reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 0)), 0);

  const res = await debugFetch('/cart/apply-promo', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ code, order_total: subtotal }),
  });

  const data = await res.json();

  if (!res.ok || !data.valid) {
    throw new Error(data.message || 'Invalid promo code');
  }

  appliedPromo.value = data.promotion_code;
  promoDiscount.value = data.discount_amount;

  return data;
};

// Remove promo code
const removePromoCode = async () => {
  const csrf = await getCsrfToken();
  if (!csrf) throw new Error('No CSRF token');

  await debugFetch('/cart/remove-promo', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
  });

  appliedPromo.value = null;
  promoDiscount.value = 0;
};

  const checkout = async () => {
        isLoading.value = true;
        try {
            // Post checkout - backend will redirect to success page
            router.post(route('checkout'), {}, {
                onFinish: () => {
                    isLoading.value = false;
                    // Clear local cart state
                    cart.value = [];
                    cartCount.value = 0;
                    subtotal.value = 0;
                    discount.value = 0;
                    shipping.value = 0.00;
                    total.value = 0;
                    freeShippingEligible.value = false;
                    automaticPromo.value = null;
                    automaticDiscount.value = 0;
                    automaticPromotions.value = [];
                    appliedPromo.value = null;
                    promoDiscount.value = 0;
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
  subtotal,
  discount,
  shipping,
  total,
  freeShippingEligible,
  automaticPromo,
  automaticDiscount,
  automaticPromotions,
  loadCart,
  checkAuthStatus,
  addToCart,
  removeFromCart,
  updateCartQuantity,
  clearCart,
  checkout,
  applyPromoCode,
  removePromoCode,
});
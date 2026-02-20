import { ref, computed, watch, onMounted } from 'vue';

const cart = ref([]);
const cartCount = ref(0);

const isCartOpen = ref(false);
const showCheckoutSuccess = ref(false);
const isLoggedIn = ref(false);
const isLoading = ref(false);

const GUEST_CART_KEY = 'guestCart';
const DEBUG_CART = true;


const cartTotal = computed(() => {
  return parseFloat(
    cart.value
      .reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 0)), 0)
      .toFixed(2)
  );
});

// Helper to obtain CSRF token. Falls back to Sanctum cookie if meta tag missing.
const getCsrfToken = async () => {
  // Try 1: meta tag
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta && meta.content) {
    if (DEBUG_CART) console.debug('[Cart] CSRF from meta tag:', meta.content.substring(0, 10) + '...');
    return meta.content;
  }

  // Try 2: XSRF cookie
  const cookieMatch = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='));
  if (cookieMatch) {
    const token = decodeURIComponent(cookieMatch.split('=')[1]);
    if (DEBUG_CART) console.debug('[Cart] CSRF from cookie:', token.substring(0, 10) + '...');
    return token;
  }

  // Try 3: Request Sanctum CSRF cookie
  if (DEBUG_CART) console.debug('[Cart] Requesting Sanctum CSRF cookie...');
  try {
    await debugFetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
  } catch (e) {
    console.error('[Cart] Failed to fetch Sanctum CSRF:', e);
  }

  const cookieMatch2 = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='));
  if (cookieMatch2) {
    const token = decodeURIComponent(cookieMatch2.split('=')[1]);
    if (DEBUG_CART) console.debug('[Cart] CSRF from Sanctum cookie:', token.substring(0, 10) + '...');
    return token;
  }

  if (DEBUG_CART) console.warn('[Cart] No CSRF token found anywhere');
  return null;
};

// Debug helper: logs request/response details when DEBUG_CART is true
const debugFetch = async (url, options = {}) => {
  if (DEBUG_CART) {
    try {
      console.debug('[Cart] request', { url, options });
    } catch (e) {}
  }

  const res = await fetch(url, options);

  if (DEBUG_CART) {
    try {
      console.debug('[Cart] response', { url, status: res.status });
      if (!res.ok) {
        const text = await res.clone().text().catch(() => null);
        console.debug('[Cart] response body', { url, status: res.status, body: text });
      }
    } catch (e) {}
  }

  return res;
};




// Check if user is logged in
const checkAuthStatus = async () => {
  try {
    const response = await fetch('/api/user', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    });

    isLoggedIn.value = response.ok;
  } catch (error) {
    isLoggedIn.value = false;
  }
};
// -----------------------------
// Load cart from backend
// -----------------------------
const loadCart = async () => {
  try {
    const response = await fetch('/cart', {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    cart.value = data.cart;
    cartCount.value = data.cart_count;
  } catch (e) {
    cart.value = [];
    cartCount.value = 0;
  }
};



const toggleCart = () => {
  isCartOpen.value = !isCartOpen.value;
};

// -----------------------------
// Add item to cart
// -----------------------------
const addToCart = async (productId, quantity = 1) => {
  const csrf = await getCsrfToken();

  if (DEBUG_CART) {
    console.debug('[Cart] addToCart called', { productId, quantity, csrf: csrf ? 'present' : 'missing' });
  }

  if (!csrf) {
    console.error('[Cart] No CSRF token available. Cannot add to cart.');
    return;
  }

  try {
    const response = await debugFetch('/cart/add', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-XSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        product_id: productId,
        quantity: quantity,
      }),
    });

    if (!response.ok) {
      const errorBody = await response.text().catch(() => null);
      console.error('[Cart] Failed to add to cart', { status: response.status, body: errorBody });
      return;
    }

    await loadCart();
    if (DEBUG_CART) {
      console.debug('[Cart] Item added successfully');
    }
  } catch (err) {
    console.error('[Cart] Error adding to cart:', err);
  }
};

// -----------------------------
// Remove item from cart
// -----------------------------
const removeFromCart = async (productId) => {
  const csrf = await getCsrfToken();

  try {
    await fetch(`/cart/remove/${productId}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrf || '',
        'X-XSRF-TOKEN': csrf || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    await loadCart();
  } catch (err) {
    console.error('Failed to remove from cart:', err);
  }
};

// -----------------------------
// Clear all items
// -----------------------------
const clearCart = async () => {
  const csrf = await getCsrfToken();

  try {
    await fetch('/cart/clear', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrf || '',
        'X-XSRF-TOKEN': csrf || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    cart.value = [];
    cartCount.value = 0;
  } catch (err) {
    console.error('Failed to clear cart:', err);
  }
};

// -----------------------------
// Checkout
// -----------------------------
const checkout = async () => {
  const csrf = await getCsrfToken();

  try {
    const res = await fetch('/cart/checkout', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': csrf || '',
        'X-XSRF-TOKEN': csrf || '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });

    if (res.ok) {
      cart.value = [];
      cartCount.value = 0;
    } else if (res.status === 419) {
      console.error('Checkout failed with 419 (CSRF). Try refreshing CSRF cookie.');
    }
  } catch (err) {
    console.error('Checkout failed:', err);
  }
};

export const useCart = () => ({
  cart,
  cartCount,
  cartTotal,
  isLoggedIn,
  loadCart,
  checkAuthStatus,
  addToCart,
  removeFromCart,
  clearCart,
  checkout,

});

<script setup>
import { onMounted, ref } from 'vue';
import { useCart } from '../../cart.js';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import MainLayout from '../Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';

import { router, Link } from '@inertiajs/vue3';

import "vue3-toastify/dist/index.css";

// Use the cart composable
const { cart, cartCount, cartTotal, isLoading, loadCart, addToCart, removeFromCart, updateCartQuantity, clearCart, checkout, appliedPromo, promoDiscount, subtotal, discount, shipping, total, freeShippingEligible, automaticPromo, automaticDiscount, automaticPromotions } = useCart();
const loadingCheckout = ref(false);

// Load cart when page mounts
onMounted(() => {
    loadCart();
});

// Increment/decrement quantity
const updateQuantity = async (item, increment = true) => {
    const newQuantity = increment ? item.quantity + 1 : item.quantity - 1;
    if (newQuantity < 1) return;
    
    await updateCartQuantity(item.product_id, newQuantity, item.variant_id);
};

// Remove a single item
const removeItem = async (item) => {
    await removeFromCart(item.product_id);
};

// Clear entire cart
const clearAll = async () => {
    await clearCart();
};


const handleCheckout = async () => {
    loadingCheckout.value = true;

    await router.post(route('checkout'), {}, {
        onFinish: () => loadingCheckout.value = false
    });
};

// Continue shopping button handler
const continueShopping = () => {
    router.visit('/products');
};

// Promo code handling
const promoCode = ref('');
const promoError = ref('');
const promoSuccess = ref('');
const applyingPromo = ref(false);

// Use appliedPromo from cart composable

const applyPromoCode = async () => {
    if (!promoCode.value.trim()) return;
    
    applyingPromo.value = true;
    promoError.value = '';
    promoSuccess.value = '';
    
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/cart/apply-promo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                code: promoCode.value.trim().toUpperCase(),
                order_total: cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
            }),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.valid) {
            promoSuccess.value = `Promo code applied! You save KSh ${data.discount_amount}`;
            // Load cart to get updated promo info from session
            await loadCart();
        } else {
            promoError.value = data.message || 'Invalid promo code';
        }
    } catch (e) {
        promoError.value = 'Failed to apply promo code';
    } finally {
        applyingPromo.value = false;
    }
};

const removePromoCode = async () => {
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        await fetch('/cart/remove-promo', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin'
        });
        promoCode.value = '';
        promoSuccess.value = '';
        await loadCart();
    } catch (e) {
        console.error('Failed to remove promo:', e);
    }
};
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <Head title="BuyNow | Cart" />

      <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Your Cart ({{ cartCount }})</h1>

        <!-- Empty cart -->
        <div v-if="cart.length === 0" class="text-gray-500">
          Your cart is empty.
        </div>

        <!-- Cart items -->
        <div v-else class="space-y-4">
          <div v-for="item in cart" :key="item.id" class="flex items-center justify-between bg-white dark:bg-zinc-800 p-4 rounded shadow">

            <!-- Product info -->
            <div class="flex-1">
              <div class="font-semibold text-lg">{{ item.name }}</div>
              <div class="text-yellow-600 font-bold">KES {{ item.price }}</div>
            </div>

            <!-- Quantity controls -->
            <div class="flex items-center gap-2">
              <button @click="updateQuantity(item, false)" class="px-2 py-1 bg-gray-200 dark:bg-zinc-700 rounded">-</button>
              <span>{{ item.quantity }}</span>
              <button @click="updateQuantity(item, true)" class="px-2 py-1 bg-gray-200 dark:bg-zinc-700 rounded">+</button>
            </div>

            <!-- Remove button -->
            <button @click="removeItem(item)" class="text-red-500 hover:underline">Remove</button>
          </div>

          <!-- Cart total -->
          <div class="flex flex-col gap-2 mt-6 bg-gray-100 dark:bg-zinc-900 p-4 rounded shadow">
            <div class="flex justify-between items-center">
              <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
              <span class="font-semibold">KES {{ subtotal.toLocaleString() }}</span>
            </div>
            
            <!-- Discount (promo code or automatic) -->
            <div v-if="discount > 0" class="flex justify-between items-center text-green-600">
              <span>Discount {{ appliedPromo ? `(${appliedPromo})` : '' }}:</span>
              <span class="font-semibold">-KES {{ discount.toLocaleString() }}</span>
            </div>
            
            <!-- Shipping -->
            <div class="flex justify-between items-center">
              <span class="text-gray-600 dark:text-gray-400">Shipping:</span>
              <span class="font-semibold" :class="{ 'text-green-600': freeShippingEligible }">
                {{ freeShippingEligible ? 'FREE' : `KES ${shipping.toLocaleString()}` }}
              </span>
            </div>
            
            <!-- Total -->
            <div class="flex justify-between items-center text-lg font-bold border-t pt-2">
              <span>Total:</span>
              <span>KES {{ total.toLocaleString() }}</span>
            </div>
            
            <div class="flex gap-2 mt-2">
              <button @click="clearAll" :disabled="isLoading" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 disabled:opacity-50">
                {{ isLoading ? 'Loading...' : 'Clear Cart' }}
              </button>
              <button @click="router.post(route('checkout'))" class="bg-yellow-500 text-white px-4 py-2 rounded">
                Checkout
              </button>
            </div>
          </div>

          <!-- Promo Code Section -->
          <div v-if="!appliedPromo" class="mt-4 bg-white dark:bg-zinc-800 p-4 rounded shadow">
            <h3 class="font-semibold mb-2">Have a promo code?</h3>
            <div class="flex gap-2">
              <input 
                v-model="promoCode"
                type="text" 
                placeholder="Enter promo code"
                class="flex-1 px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg dark:bg-zinc-700"
                @keyup.enter="applyPromoCode"
              >
              <button 
                @click="applyPromoCode" 
                :disabled="applyingPromo || !promoCode.trim()"
                class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 disabled:opacity-50"
              >
                {{ applyingPromo ? 'Applying...' : 'Apply' }}
              </button>
            </div>
            <p v-if="promoError" class="text-red-500 text-sm mt-2">{{ promoError }}</p>
            <p v-if="promoSuccess" class="text-green-500 text-sm mt-2">{{ promoSuccess }}</p>
          </div>
          <div v-else class="mt-4 bg-green-50 dark:bg-green-900/20 p-4 rounded border border-green-200 dark:border-green-800">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-green-700 dark:text-green-400 font-semibold">Promo code applied!</p>
                <p class="text-sm text-green-600 dark:text-green-500">{{ appliedPromo }}</p>
              </div>
              <button @click="removePromoCode" class="text-red-500 hover:text-red-700 text-sm">
                Remove
              </button>
            </div>
          </div>
          
          <!-- Promo UX Messages -->
          <div v-if="discount > 0" class="mt-4 bg-green-50 dark:bg-green-900/20 p-4 rounded border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🎉</span>
              <div>
                <p class="text-green-700 dark:text-green-400 font-semibold">
                  You saved KSh {{ discount.toLocaleString() }}!
                </p>
                <p v-if="appliedPromo" class="text-sm text-green-600 dark:text-green-500">
                  Using code: {{ appliedPromo }}
                </p>
                <p v-else-if="automaticPromo" class="text-sm text-green-600 dark:text-green-500">
                  🔥 {{ automaticPromo.name }} Applied: {{ automaticPromo.description || 'Best deal applied automatically' }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Free shipping threshold message -->
          <div v-if="!freeShippingEligible && subtotal > 0" class="mt-4 bg-blue-50 dark:bg-blue-900/20 p-4 rounded border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🚚</span>
              <div>
                <p class="text-blue-700 dark:text-blue-400 font-semibold">
                  Add KSh {{ (1000 - subtotal).toLocaleString() }} more for free shipping!
                </p>
                <p class="text-sm text-blue-600 dark:text-blue-500">
                  Free shipping on orders over KSh 1,000
                </p>
              </div>
            </div>
          </div>
          
          <!-- Free shipping eligible message -->
          <div v-if="freeShippingEligible" class="mt-4 bg-green-50 dark:bg-green-900/20 p-4 rounded border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2">
              <span class="text-2xl">✅</span>
              <div>
                <p class="text-green-700 dark:text-green-400 font-semibold">
                  You qualify for FREE shipping!
                </p>
              </div>
            </div>
          </div>
          
          <!-- Best deal applied automatically -->
          <div v-if="automaticPromo && !appliedPromo" class="mt-4 bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🔥</span>
              <div>
                <p class="text-yellow-700 dark:text-yellow-400 font-semibold">
                  Best deal applied automatically!
                </p>
                <p class="text-sm text-yellow-600 dark:text-yellow-500">
                  {{ automaticPromo.name }}: {{ automaticPromo.description || 'Discount applied' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Continue Shopping button below -->
      <div class="mt-2 container mx-auto p-4">
        <button @click="continueShopping" class="text-gray px-4 py-2 rounded hover:bg-gray-200 dark:hover:bg-zinc-700">
          Continue Shopping >>
        </button>
      </div>

    </ProfileLayout>
  </MainLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCart } from '../../cart.js';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import MainLayout from '../Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import { toast } from "vue3-toastify";
import { Inertia } from '@inertiajs/inertia';
import "vue3-toastify/dist/index.css";

// Use the cart composable
const { cart, cartCount, cartTotal, isLoading, loadCart, addToCart, removeFromCart, clearCart, checkout } = useCart();

// Load cart when page mounts
onMounted(() => {
    loadCart();
});

// Increment/decrement quantity
const updateQuantity = async (item, increment = true) => {
    if (!increment && item.quantity <= 1) return;
    await addToCart(item.product_id, increment ? 1 : -1);
};

// Remove a single item
const removeItem = async (item) => {
    await removeFromCart(item.product_id);
};

// Clear entire cart
const clearAll = async () => {
    await clearCart();
};

// Checkout handler
const handleCheckout = async () => {
    try {
        await checkout();
        toast.success('Checkout Successful!', { autoClose: 3000,position: 'bottom-left', });
    } catch (err) {
        console.error(err);
        toast.error('Checkout failed. Please try again.', { autoClose: 3000,position: 'bottom-left', });
    }
};

// Continue shopping button handler
const continueShopping = () => {
    Inertia.visit('/products');
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
          <div class="flex justify-between items-center mt-6 bg-gray-100 dark:bg-zinc-900 p-4 rounded shadow">
            <div class="text-lg font-bold">Total: KES {{ cartTotal }}</div>
            <div class="flex gap-2">
              <button @click="clearAll" :disabled="isLoading" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 disabled:opacity-50">
                {{ isLoading ? 'Loading...' : 'Clear Cart' }}
              </button>
              <button @click="handleCheckout" :disabled="isLoading" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-green-600 disabled:opacity-50">
                {{ isLoading ? 'Processing...' : 'Checkout' }}
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Continue Shopping button below -->
 <div class="mt-2">
  <button @click="continueShopping" class="text-gray px-4 py-2 rounded hover:bg-gray-200 dark:hover:bg-zinc-700">
    Continue Shopping >>
  </button>
</div>


    </ProfileLayout>
  </MainLayout>
</template>

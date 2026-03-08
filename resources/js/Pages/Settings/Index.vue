<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({})
  }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = ref({ ...props.settings });
const showDeleteModal = ref(false);
const deletePassword = ref('');
const deleteError = ref('');
const saving = ref(false);
const deleting = ref(false);

function saveSettings() {
  saving.value = true;
  router.post('/profile/settings', form.value, {
    onFinish: () => saving.value = false,
  });
}

function confirmDelete() {
  if (!deletePassword.value) {
    deleteError.value = 'Please enter your password.';
    return;
  }
  deleting.value = true;
  router.delete('/profile/settings/account', {
    data: { password: deletePassword.value },
    onError: (errors) => {
      deleteError.value = errors.password || 'Something went wrong.';
      deleting.value = false;
    },
  });
}
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <div class="py-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Settings</h1>

        <!-- Flash -->
        <div v-if="flash.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
          {{ flash.success }}
        </div>

        <form @submit.prevent="saveSettings" class="space-y-8">

          <!-- Notification Preferences -->
          <section class="bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-gray-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              Notification Preferences
            </h2>
            <div class="space-y-4">
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">Email Notifications</p>
                  <p class="text-xs text-gray-500">Receive notifications via email</p>
                </div>
                <input type="checkbox" v-model="form.email_notifications" class="toggle-checkbox w-10 h-5 accent-yellow-400"/>
              </label>
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">SMS Notifications</p>
                  <p class="text-xs text-gray-500">Receive notifications via SMS</p>
                </div>
                <input type="checkbox" v-model="form.sms_notifications" class="w-10 h-5 accent-yellow-400"/>
              </label>
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">Order Updates</p>
                  <p class="text-xs text-gray-500">Get updates on your order status</p>
                </div>
                <input type="checkbox" v-model="form.order_updates" class="w-10 h-5 accent-yellow-400"/>
              </label>
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">Promotional Emails</p>
                  <p class="text-xs text-gray-500">Receive deals, discounts and offers</p>
                </div>
                <input type="checkbox" v-model="form.promo_emails" class="w-10 h-5 accent-yellow-400"/>
              </label>
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">Newsletter</p>
                  <p class="text-xs text-gray-500">Subscribe to our weekly newsletter</p>
                </div>
                <input type="checkbox" v-model="form.newsletter" class="w-10 h-5 accent-yellow-400"/>
              </label>
            </div>
          </section>

          <!-- Language & Currency -->
          <section class="bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-gray-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
              </svg>
              Language & Currency
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
                <select v-model="form.language"
                  class="w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 outline-none">
                  <option value="en">English</option>
                  <option value="sw">Swahili</option>
                  <option value="fr">French</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                <select v-model="form.currency"
                  class="w-full rounded-xl border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 outline-none">
                  <option value="KES">KES – Kenyan Shilling</option>
                  <option value="USD">USD – US Dollar</option>
                  <option value="EUR">EUR – Euro</option>
                  <option value="GBP">GBP – British Pound</option>
                </select>
              </div>
            </div>
          </section>

          <!-- Privacy & Security Info -->
          <section class="bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-6 border border-gray-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
              Privacy & Security
            </h2>
            <div class="space-y-3 text-sm">
              <a href="/profile/security"
                class="flex items-center justify-between px-4 py-3 bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 hover:border-yellow-400 transition group">
                <span class="font-medium text-gray-800 dark:text-gray-200">Change Password</span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-yellow-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </a>
              <a href="/profile/security"
                class="flex items-center justify-between px-4 py-3 bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 hover:border-yellow-400 transition group">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">Two-Factor Authentication</p>
                  <p class="text-xs text-gray-500">
                    <span v-if="settings.two_factor" class="text-green-600 font-semibold">Enabled</span>
                    <span v-else class="text-red-500 font-semibold">Disabled</span>
                  </p>
                </div>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-yellow-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </a>
              <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600">
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">Data & Privacy</p>
                  <p class="text-xs text-gray-500">Your data is encrypted and protected</p>
                </div>
                <span class="text-green-600 text-xs font-bold bg-green-100 dark:bg-green-900 px-2 py-0.5 rounded-full">Secured</span>
              </div>
            </div>
          </section>

          <!-- Save Button -->
          <div class="flex justify-end">
            <button type="submit"
              :disabled="saving"
              class="bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 text-black font-semibold px-8 py-2.5 rounded-xl transition flex items-center gap-2">
              <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              {{ saving ? 'Saving…' : 'Save Settings' }}
            </button>
          </div>

        </form>

        <!-- Danger Zone -->
        <section class="mt-10 bg-red-50 dark:bg-red-950/20 rounded-2xl p-6 border border-red-200 dark:border-red-800">
          <h2 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            Danger Zone
          </h2>
          <p class="text-sm text-red-600 dark:text-red-400 mb-4">
            Permanently delete your account. This action cannot be undone. All your data, orders, and wishlist will be erased.
          </p>
          <button @click="showDeleteModal = true"
            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-xl text-sm transition">
            Delete My Account
          </button>
        </section>
      </div>

      <!-- Delete Account Modal -->
      <Teleport to="body">
        <div v-if="showDeleteModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-red-600 mb-2">Delete Account</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              This is irreversible. Enter your password to confirm account deletion.
            </p>
            <input
              v-model="deletePassword"
              type="password"
              placeholder="Enter your password"
              class="w-full border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-red-400 mb-2"
            />
            <p v-if="deleteError" class="text-red-500 text-xs mb-2">{{ deleteError }}</p>
            <div class="flex gap-3 mt-4">
              <button @click="showDeleteModal = false; deletePassword = ''; deleteError = ''"
                class="flex-1 border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 font-semibold py-2 rounded-xl text-sm hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                Cancel
              </button>
              <button @click="confirmDelete"
                :disabled="deleting"
                class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold py-2 rounded-xl text-sm transition">
                {{ deleting ? 'Deleting…' : 'Yes, Delete' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </ProfileLayout>
  </MainLayout>
</template>

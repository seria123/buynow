<script setup>
import { ref, computed } from 'vue';
import { useForm,usePage,Head} from '@inertiajs/vue3';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import MainLayout from '../Layouts/MainLayout.vue';


const page = usePage();
const user = page.props.auth?.user;

const showSecurity = ref(true);

// Toast
const showToast = ref(false);
const toastMessage = ref('');

const triggerToast = (msg) => {
  toastMessage.value = msg;
  showToast.value = true;
  setTimeout(() => { showToast.value = false; }, 3000);
};

// Password form
const passwordForm = useForm({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

// Toggle
const showCurrentPass = ref(false);
const showNewPass = ref(false);
const showConfirmPass = ref(false);

// Password validation
const password = computed(() => passwordForm.new_password || '');

const rules = {
  length: computed(() => password.value.length >= 8),
  lower: computed(() => /[a-z]/.test(password.value)),
  upper: computed(() => /[A-Z]/.test(password.value)),
  number: computed(() => /[0-9]/.test(password.value)),
  symbol: computed(() => /[@$!%*#?&]/.test(password.value)),
};

const isValidPassword = computed(() => {
  return (
    rules.length.value &&
    rules.lower.value &&
    rules.upper.value &&
    rules.number.value &&
    rules.symbol.value &&
    passwordForm.new_password === passwordForm.new_password_confirmation
  );
});

// Submit
const changePassword = () => {
  if (!isValidPassword.value) return;

  passwordForm.post('/profile/password', {
    onSuccess: () => {
      triggerToast('Password changed successfully!');
      passwordForm.reset();
    },
    onError: () => triggerToast('Error changing password.')
  });
};
</script>

<template>
<MainLayout>
  <ProfileLayout>
     <Head title="BuyNow | Security" />

    <div class="max-w-2xl mx-auto space-y-6 pb-20 px-4 sm:px-6">

      <h1 class="text-3xl font-black">Security Settings</h1>

      <!-- Toast -->
      <div
        v-if="showToast"
        class="fixed bottom-6 right-6 bg-black text-white px-6 py-3 rounded-xl shadow-lg z-50"
      >
        {{ toastMessage }}
      </div>

    <!-- Security Panel -->
        <div class="mt-12">
            <button @click="showSecurity = !showSecurity" 
            class="w-full flex items-center justify-between p-6 bg-gray-100 dark:bg-zinc-800/50 rounded-3xl transition-colors hover:bg-gray-200 dark:hover:bg-zinc-800">
        <div class="text-left flex items-center gap-3">
            <div class="p-2 bg-yellow-400/10 rounded-lg">
                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white">Security & Password</h3>
                <p class="text-xs text-gray-500">Keep your account secure with a strong password.</p>
            </div>
        </div>
        <span class="text-gray-400 transition-transform duration-300" :class="showSecurity ? 'rotate-180' : ''">▼</span>
    </button>

            <div v-if="showSecurity" class="mt-6 p-8 border border-gray-100 dark:border-zinc-800 rounded-3xl space-y-6 max-w-2xl mx-auto bg-white dark:bg-zinc-900/50 shadow-sm">
                
                <!-- Current Password Field -->
                <div class="relative">
                    <label :class="labelStyle">Current Password</label>
                    <div class="relative">
                        <input v-model="passwordForm.current_password" 
                               :type="showCurrentPass ? 'text' : 'password'" 
                               :class="inputStyle" 
                               class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                               placeholder="Enter current password" />
                        <button type="button" 
                                @click="showCurrentPass = !showCurrentPass"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-500 transition-colors">
                            <svg v-if="!showCurrentPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.965 9.965 0 013.073-4.35M6.4 6.4c.88-1.58 2.5-2.9 5.6-2.9 4.5 0 8.3 3 9.5 7-.5 1.7-1.7 3.2-3.2 4.3M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- New & Confirm Password Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="relative">
                        <label :class="labelStyle">New Password</label>
                        <div class="relative">
                            <input v-model="passwordForm.new_password" 
                                   :type="showNewPass ? 'text' : 'password'" 
                                   :class="inputStyle" 
                                   class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                                   placeholder="Min 8 characters" />
                            <button type="button" 
                                    @click="showNewPass = !showNewPass"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-500 transition-colors">
                                <svg v-if="!showNewPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.965 9.965 0 013.073-4.35M6.4 6.4c.88-1.58 2.5-2.9 5.6-2.9 4.5 0 8.3 3 9.5 7-.5 1.7-1.7 3.2-3.2 4.3M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="relative">
                        <label :class="labelStyle">Confirm New</label>
                        <div class="relative">
                            <input v-model="passwordForm.new_password_confirmation" 
                                   :type="showConfirmPass ? 'text' : 'password'" 
                                   :class="inputStyle" 
                                   class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                                   placeholder="Repeat new password" />
                            <button type="button" 
                                    @click="showConfirmPass = !showConfirmPass"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-500 transition-colors">
                                <svg v-if="!showConfirmPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.965 9.965 0 013.073-4.35M6.4 6.4c.88-1.58 2.5-2.9 5.6-2.9 4.5 0 8.3 3 9.5 7-.5 1.7-1.7 3.2-3.2 4.3M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

               <!-- Password Requirements -->
  <div class="text-sm text-gray-500 dark:text-gray-300 mt-2">
    <p class="font-medium mb-1">Password Requirements</p>
    <ul class="list-disc list-inside text-xs space-y-1">
      <li>At least 8 characters long</li>
      <li>Include uppercase and lowercase letters</li>
      <li>Include at least one number and one symbol</li>
    </ul>
  </div>

 
     <div class="mt-4">
    <button 
  @click="changePassword" 
  :disabled="!isValidPassword || passwordForm.processing"
  class="w-full mt-3 font-semibold py-2 px-6 rounded-xl transition-all duration-300"
  :class="isValidPassword
    ? 'bg-yellow-400 text-black hover:bg-yellow-500'
    : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
>
  {{ passwordForm.processing ? 'Changing Password...' : 'Change Password' }}
</button>
            </div>
        </div>
    </div>
    </div>
  
  </ProfileLayout>
</MainLayout>
</template>

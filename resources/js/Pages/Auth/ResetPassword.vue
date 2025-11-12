<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../Layouts/AuthLayout.vue';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        required: true,
    },
});

const showPassword = ref(false);
const showConfirmation = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Buynow | Reset Password" />
    <AuthLayout>
        <div class="w-full max-w-md mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create a new password</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Use a strong, unique password to keep your account secure.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email address
                    </label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="email"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        :class="{ 'border-red-500': form.errors.email }" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New password
                    </label>
                    <div class="relative">
                        <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password"
                            placeholder="Enter a new password"
                            class="w-full px-4 py-2 pr-12 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                            :class="{ 'border-red-500': form.errors.password }" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm new password
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" v-model="form.password_confirmation"
                            :type="showConfirmation ? 'text' : 'password'" required autocomplete="new-password"
                            placeholder="Re-enter your new password"
                            class="w-full px-4 py-2 pr-12 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                            :class="{ 'border-red-500': form.errors.password_confirmation }" />
                        <button type="button" @click="showConfirmation = !showConfirmation"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                            <svg v-if="!showConfirmation" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-500">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-semibold py-2 px-6 rounded-xl shadow-lg shadow-yellow-400/30 hover:shadow-yellow-400/50 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]">
                    <span v-if="!form.processing">Reset password</span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Updating...
                    </span>
                </button>

                <div class="text-center">
                    <Link href="/login"
                        class="text-sm font-medium text-yellow-500 hover:text-yellow-400 transition-colors">
                    Return to sign in
                    </Link>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>


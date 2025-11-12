<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '../Layouts/AuthLayout.vue';

const page = usePage();
const status = computed(() => page.props.flash?.status);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Buynow | Forgot Password" />
    <AuthLayout>
        <div class="w-full max-w-md mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Forgot Password</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Enter the email you use for Buynow and we'll send you a link to reset your password.
                </p>
            </div>

            <div v-if="status"
                class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center space-x-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-green-800 dark:text-green-300 font-medium">
                    {{ status }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email address
                    </label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        :class="{ 'border-red-500': form.errors.email }" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                        {{ form.errors.email }}
                    </p>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-semibold py-2 px-6 rounded-xl shadow-lg shadow-yellow-400/30 hover:shadow-yellow-400/50 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]">
                    <span v-if="!form.processing">Email Reset Link</span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Sending...
                    </span>
                </button>

                <div class="text-center">
                    <Link href="/login"
                        class="text-sm font-medium text-yellow-500 hover:text-yellow-400 transition-colors">
                    Back to sign in
                    </Link>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>


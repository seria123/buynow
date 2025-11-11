<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthLayout from '../Layouts/AuthLayout.vue';

const page = usePage();
const status = computed(() => page.props.flash?.status);

const form = useForm({});

const resending = ref(false);
const resent = ref(computed(() => status.value === 'verification-link-sent'));

const resendEmail = () => {
    resending.value = true;
    form.post('/email/verification-notification', {
        onFinish: () => {
            resending.value = false;
            resent.value = true;
            setTimeout(() => resent.value = false, 5000);
        },
        onError: () => {
            resending.value = false;
        },
    });
};
</script>

<template>

    <Head title="Buynow | Email Verification" />
    <AuthLayout>
        <div class="w-full max-w-md mx-auto">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Verify Your Email
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Thanks for signing up! Please check your email for a verification link to activate your account.
                </p>
            </div>

            <!-- Enhanced Animated Illustration -->
            <div class="flex justify-center mb-8">
                <div class="relative flex items-center justify-center" style="height: 120px;">
                    <!-- Animated rounded rectangle background with lower opacity -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 h-24 bg-yellow-400 rounded-2xl opacity-10 animate-ping"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-36 h-20 bg-yellow-400 rounded-xl opacity-15 animate-pulse"></div>
                    </div>

                    <!-- Larger Envelope icon -->
                    <svg class="w-28 h-28 text-yellow-400 relative z-10" fill="none" viewBox="0 0 64 64">
                        <rect x="12" y="20" width="40" height="24" rx="12" fill="currentColor" opacity="0.15" />
                        <path d="M12 20l20 16 20-16" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <rect x="12" y="20" width="40" height="24" rx="12" stroke="currentColor" stroke-width="2" />
                        <circle cx="48" cy="24" r="4" fill="#EF4444" stroke="white" stroke-width="2" />
                    </svg>
                </div>
            </div>

            <!-- Info Card -->
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="text-sm text-yellow-800 dark:text-yellow-300">
                        <p class="font-semibold mb-1">Check your inbox</p>
                        <p>We've sent a verification link to your email address. Click the link to activate your
                            account.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <!-- Success message -->
                <div v-if="resent"
                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center space-x-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm text-green-800 dark:text-green-300 font-medium">
                        Verification email sent successfully!
                    </p>
                </div>

                <!-- Resend button -->
                <button type="button" @click="resendEmail" :disabled="form.processing || resending || resent"
                    class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed text-black font-semibold py-3 px-6 rounded-xl shadow-lg shadow-yellow-400/30 hover:shadow-yellow-400/50 disabled:shadow-none transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] disabled:transform-none flex items-center justify-center space-x-2">
                    <svg v-if="form.processing || resending" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ (form.processing || resending) ? 'Sending...' : resent ? 'Email Sent!' : 'Resend Verification Email' }}</span>
                </button>

                <!-- Help text -->
                <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>Didn't receive the email? Check your spam folder or try resending.</p>
                </div>

                <!-- Sign in link -->
                <div class="text-center pt-2">
                    <p class="text-gray-600 dark:text-gray-400">
                        Already verified?
                        <Link href="/login"
                            class="text-yellow-400 hover:text-yellow-500 font-semibold transition-colors duration-200 hover:underline">
                        Sign In
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Additional help -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Need help?
                    <a href="/support" class="text-yellow-400 hover:text-yellow-500 font-medium transition-colors">
                        Contact Support
                    </a>
                </p>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
@keyframes ping {

    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 0.3;
    }

    50% {
        opacity: 0.5;
    }
}
</style>
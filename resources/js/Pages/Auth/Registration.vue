<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthLayout from '../Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: ''
});

const showConfirmPassword = computed(() => form.password.length > 0);

const showPassword = ref(false);
const showConfirmPasswordField = ref(false);

const submit = () => {
    form.post('/register', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            // Redirect to email verification page after successful registration
            router.visit('/email/verify', {
                method: 'get',
                preserveState: false,
                preserveScroll: false,
            });
        },
    });
};
</script>

<template>

    <Head title="Buynow | Register" />
    <AuthLayout>
        <div class="w-full max-w-md mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create Account</h1>
                <p class="text-gray-600 dark:text-gray-400">Join us to start shopping</p>
            </div>

            <!-- Social Login Buttons -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <a :href="route('auth.google')" title="Continue with Google"
                    class="flex items-center justify-center p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-yellow-400 dark:hover:border-yellow-400 transition-all duration-300 group">
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                </a>

                <a :href="route('auth.facebook')" title="Continue with Facebook"
                    class="flex items-center justify-center p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-yellow-400 dark:hover:border-yellow-400 transition-all duration-300 group">
                    <svg class="w-6 h-6 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                </a>
            </div>

            <!-- Divider -->
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white dark:bg-zinc-900 text-gray-500 dark:text-gray-400 font-medium">Or
                        continue with email</span>
                </div>
            </div>

            <!-- Registration Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Full Name <span class="text-yellow-400">*</span>
                    </label>
                    <input id="name" v-model="form.name" type="text" required
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        placeholder="Enter your full name" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address <span class="text-yellow-400">*</span>
                    </label>
                    <input id="email" v-model="form.email" type="email" required
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        placeholder="Enter your email" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                </div>

                <!-- Username Field (Optional) -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Username <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <input id="username" v-model="form.username" type="text"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        placeholder="Choose a username" />
                    <p v-if="form.errors.username" class="mt-1 text-sm text-red-500">{{ form.errors.username }}</p>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password <span class="text-yellow-400">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required
                            class="w-full px-4 py-2 pr-12 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                            placeholder="Create a password" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
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
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                </div>

                <!-- Confirm Password Field (appears when password has value) -->
                <transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                    <div v-if="showConfirmPassword">
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirm Password <span class="text-yellow-400">*</span>
                        </label>
                        <div class="relative">
                            <input id="password_confirmation" v-model="form.password_confirmation"
                                :type="showConfirmPasswordField ? 'text' : 'password'" required
                                class="w-full px-4 py-2 pr-12 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                                placeholder="Confirm your password" />
                            <button type="button" @click="showConfirmPasswordField = !showConfirmPasswordField"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition-colors">
                                <svg v-if="!showConfirmPasswordField" class="w-5 h-5" fill="none" stroke="currentColor"
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
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-500">{{
                            form.errors.password_confirmation }}</p>
                    </div>
                </transition>

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing"
                    class="w-full mt-3 bg-linear-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-semibold py-2 px-6 rounded-xl shadow-lg shadow-yellow-400/30 hover:shadow-yellow-400/50 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]">
                    <span v-if="!form.processing">Create Account</span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Creating Account...
                    </span>
                </button>

                <!-- Login Link -->
                <div class="text-center mt-6">
                    <p class="text-gray-600 dark:text-gray-400">
                        Already have an account?
                        <Link href="/login"
                            class="text-yellow-400 hover:text-yellow-500 font-semibold transition-colors">
                        Sign In
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
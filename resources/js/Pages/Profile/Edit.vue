<script setup>
import { ref, computed } from 'vue';
import { usePage, useForm,Head } from '@inertiajs/vue3';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import MainLayout from '../Layouts/MainLayout.vue';

const page = usePage();
const user = page.props.auth?.user;

// --- STYLE CLASSES ---
const labelStyle = 'block text-sm font-semibold text-gray-900 dark:text-gray-300 mb-2';
const inputStyle = 'w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300';
const sectionWrapper = 'grid grid-cols-1 lg:grid-cols-3 gap-6 border-b border-gray-200 dark:border-gray-700 pb-8';

// --- TOAST STATE ---
const showToast = ref(false);
const toastMessage = ref('');

const triggerToast = (msg) => {
  toastMessage.value = msg;
  showToast.value = true;
  setTimeout(() => { showToast.value = false; }, 3000);
};

// Use Inertia useForm for reactive form + easy submission
const form = useForm({
  name: user?.name || '',
  username: user?.username || '',
  email: user?.email || '',
  phone: user?.phone || '',
  avatar: null, // file upload
});



// Avatar preview
const avatarPreview = ref(user?.avatar || null);
const defaultAvatar = computed(() => `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name)}&background=random`);

// Handle file upload
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  form.avatar = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    avatarPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Save changes
// Update Save changes
const saveProfile = () => { // Changed from saveChanges
  form.post('/profile/update', {
    
    onSuccess: () => triggerToast('Profile updated successfully!'),
    onError: (errors) => {
      console.log(errors);
      triggerToast('Error updating profile.');
    }
  });
};


// Cancel (reset form)
const cancel = () => {
  form.reset({
    name: user?.name,
    username: user?.username,
    email: user?.email,
    phone: user?.phone,
    avatar: null,
  });
  avatarPreview.value = user?.avatar || null;
};


</script>

<template>
 <MainLayout>
  <ProfileLayout>
     <Head title="BuyNow | Edit Profile" />

    <div class="max-w-5xl mx-auto space-y-4 pb-20 px-4 sm:px-6">
        <div class="pb-6">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Profile Settings</h1>
            <p class="text-gray-500 text-sm">Manage your public identity and contact information.</p>
        </div>
          <!-- Toast Notification -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 bg-black text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all"
    >
      {{ toastMessage }}
    </div>

        <!-- Identity Section -->
        <div :class="sectionWrapper">
            <div class="space-y-1">
                <h3 class="font-bold text-gray-900 dark:text-white">Public Identity</h3>
                <p class="text-xs text-gray-500">This info will be visible to vendors when you place an order.</p>
            </div>
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center gap-6">
                    <div class="relative group cursor-pointer" @click="$refs.fileInput.click()">
                        <img :src="avatarPreview || `https://ui-avatars.com/api/?name=${user.name}`" 
                             class="w-24 h-24 rounded-2xl object-cover border-2 border-yellow-400 shadow-xl shadow-yellow-400/10" />
                        <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="text-white text-[10px] font-bold uppercase">Change</span>
                        </div>
                        <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" />
                    </div>
                    <div>
                        <p class="text-sm font-bold dark:text-white">Profile Photo</p>
                        <p class="text-xs text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label :class="labelStyle">Display Name</label>
                        <input v-model="form.name" type="text" :class="inputStyle" 
                        class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        placeholder="Enter your full name" />

                    </div>
                    <div>
                        <label :class="labelStyle">Username</label>
                        <input v-model="form.username" type="text" :class="inputStyle" 
                        class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                        placeholder="@username" />
                    </div>
                </div>

                
            </div>
        </div>

        <!-- Contact Section -->
        <div :class="sectionWrapper">
            <div class="space-y-1">
                <h3 class="font-bold text-gray-900 dark:text-white">Contact Details</h3>
                <p class="text-xs text-gray-500">Used for shipping notifications and order receipts.</p>
            </div>
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label :class="labelStyle">Email Address</label>
                    <input v-model="form.email" type="email" :class="inputStyle" 
                    class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                    placeholder="email@example.com" />
                </div>
                <div>
                    <label :class="labelStyle">Phone Number</label>
                    <input v-model="form.phone_number" type="tel" :class="inputStyle" 
                    class="w-full px-4 py-2 pr-10 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-300"
                    placeholder="+1 (555) 000-0000" />
                </div>
            </div>
        </div>

        <!-- Profile Save Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-6">
            <button type="button" @click="cancel" class="text-sm font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                Cancel
            </button>
            <button @click="saveProfile" 
                class="w-full sm:w-auto bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black font-semibold py-3 px-10 rounded-xl shadow-lg shadow-yellow-400/30 hover:shadow-yellow-400/50 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                Save Profile Changes
            </button>
        </div>

    </div>
     </ProfileLayout>
</MainLayout>
</template>
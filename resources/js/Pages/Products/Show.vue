<script setup>
import { ref, computed, onMounted, toRaw, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import { useCart } from '../../cart.js';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import axios from 'axios';

const props = defineProps({
  product: Object
});

const page = usePage();

// JSON-LD from controller
const jsonLd = computed(() => page.props.jsonLd);

// Inject JSON-LD into head
onMounted(() => {
  if (jsonLd.value) {
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify(jsonLd.value);
    document.head.appendChild(script);
  }
});

// Debug: Log props on mount
onMounted(() => {
  console.log('Show.vue - page.props on mount:', toRaw(page.props));
  console.log('Show.vue - props.product on mount:', toRaw(props.product));
});

const isWishlistLoading = ref(false);
const quantity = ref(1);
const selectedImageIndex = ref(0);

// Ensure main image is set on mount
onMounted(() => {
  // Make sure we have valid image index after mount
  if (productImages.value.length > 0) {
    selectedImageIndex.value = 0;
  }
});

// Variant selection state
const selectedVariantId = ref(null);

// Get the selected variant object
const selectedVariant = computed(() => {
  const p = getProductData();
  if (!p?.variants || selectedVariantId.value === null) return null;
  return p.variants.find(v => v.id === selectedVariantId.value);
});

// Current price - use variant price if selected
const currentPrice = computed(() => {
  if (selectedVariant.value?.price !== undefined && selectedVariant.value?.price !== null) {
    return selectedVariant.value.price;
  }
  return getProductData()?.price ?? 0;
});

// Current compare price - use variant compare price if selected
const currentComparePrice = computed(() => {
  if (selectedVariant.value?.compare_price !== undefined && selectedVariant.value?.compare_price !== null) {
    return selectedVariant.value.compare_price;
  }
  return getProductData()?.compare_price ?? null;
});

// Current stock - use variant stock if selected
const currentStock = computed(() => {
  if (selectedVariant.value?.stats?.total_stock !== undefined) {
    return selectedVariant.value.stats.total_stock;
  }
  return getProductData()?.stats?.total_stock ?? 0;
});

// Select a variant
const selectVariant = (variantId) => {
  selectedVariantId.value = variantId;
  // Reset quantity when variant changes
  quantity.value = 1;
};

// Rating state
const isRatingSubmitting = ref(false);
const selectedRating = ref(0);
const ratingComment = ref('');
const isCommentSubmitting = ref(false);
const newComment = ref('');

const { addToCart, checkAuthStatus, isLoggedIn } = useCart();

// Also check auth status from page props (more reliable)
const isUserLoggedIn = computed(() => {
  return page.props.auth?.user || isLoggedIn.value;
});

// Try to get product from page.props using $page in a different way
// In Inertia, the props are available via page.props but might need to access differently
function findProductInPageProps() {
  const raw = toRaw(page.props);
  console.log('All page.props keys:', Object.keys(raw));
  
  // Specifically check page.props.product
  console.log('page.props.product raw:', raw.product);
  console.log('page.props.product id:', raw.product?.id);
  console.log('page.props.product name:', raw.product?.name);
  
  // Look for any property that has id and name
  for (const [key, value] of Object.entries(raw)) {
    if (value && typeof value === 'object' && value.id && value.name) {
      console.log('Found product in key:', key);
      return value;
    }
  }
  
  return null;
}

// Get product data when needed (not computed to avoid early evaluation)
function getProductData() {
  console.log('getProductData - props.product:', toRaw(props.product));
  console.log('getProductData - page.props.product:', toRaw(page.props.product));
  
  // First try props.product
  if (props.product?.id) {
    console.log('Using props.product');
    return props.product;
  }
  
  // Then try page.props.product
  if (page.props.product?.id) {
    console.log('Using page.props.product');
    return page.props.product;
  }
  
  // Try to find in page.props
  console.log('Trying findProductInPageProps');
  return findProductInPageProps();
}

// Computed product for script access (named differently to avoid conflict with prop)
const productData = computed(() => getProductData());

console.log('Show.vue - computed product:', toRaw(productData.value));

// Get all available images for the product - use helper function
const productImages = computed(() => {
  // Get product from props or page props - directly access to ensure we have data
  const product = props.product || page.props.product;
  const p = product || getProductData();
  
  console.log('productImages - product:', p);
  console.log('productImages - p.images:', p?.images);
  console.log('productImages - p.all_images:', p?.all_images);
  console.log('productImages - p.thumbnail_url:', p?.thumbnail_url);
  
  // If a variant is selected, check for variant images first
  if (selectedVariant.value?.images && Array.isArray(selectedVariant.value.images) && selectedVariant.value.images.length > 0) {
    console.log('Using variant images:', selectedVariant.value.images);
    return selectedVariant.value.images;
  }
  // Check if variant has its own thumbnail_url
  if (selectedVariant.value?.thumbnail_url) {
    console.log('Using variant thumbnail_url');
    return [{ url: selectedVariant.value.thumbnail_url, thumb_url: selectedVariant.value.thumbnail_url }];
  }
  
  // Check for main product images array - try common property names
  const imagesProp = p?.images || p?.all_images || p?.product_images;
  if (imagesProp && Array.isArray(imagesProp) && imagesProp.length > 0) {
    console.log('Using main product images:', imagesProp);
    return imagesProp;
  }
  
  // If no images array, check thumbnail_url
  if (p?.thumbnail_url) {
    console.log('Using main product thumbnail_url');
    return [{ url: p.thumbnail_url, thumb_url: p.thumbnail_url }];
  }
  
  // Try to get images from media relationship or other common patterns
  if (p?.media && Array.isArray(p.media) && p.media.length > 0) {
    return p.media.map(m => ({ url: m.original_url, thumb_url: m.thumbnail_url || m.url }));
  }
  
  console.log('No images found');
  // Final fallback - return empty array
  return [];
});

// Watch for variant changes to reset image index
watch(selectedVariantId, () => {
  selectedImageIndex.value = 0;
});

// Get image URL from various possible properties
const getImageUrl = (img) => {
  if (!img) return null;
  
  // Handle case where img might be a string (direct URL)
  if (typeof img === 'string') {
    return img.trim() || null;
  }
  
  // Try common image URL properties
  const urlProps = ['url', 'thumb_url', 'thumbnail_url', 'src', 'image', 'image_url', 'path'];
  for (const prop of urlProps) {
    if (img[prop] && typeof img[prop] === 'string' && img[prop].trim() !== '') {
      return img[prop];
    }
  }
  
  return null;
};

// Current main image (can be changed by clicking thumbnails)
// Always default to first image if selectedIndex is out of bounds
const mainImage = computed(() => {
  // Get product directly - try multiple sources
  const product = props.product || page.props.product || getProductData();
  
  // Get images from productImages
  const images = productImages.value;
  
  // First try: use selected image from productImages
  if (images && images.length > 0) {
    const index = selectedImageIndex.value < images.length ? selectedImageIndex.value : 0;
    const currentImg = images[index];
    const imgUrl = getImageUrl(currentImg);
    if (imgUrl) return imgUrl;
  }
  
  // Second try: variant thumbnail
  if (selectedVariant.value?.thumbnail_url) {
    return selectedVariant.value.thumbnail_url;
  }
  
  // Third try: product thumbnail_url
  if (product?.thumbnail_url) {
    return product.thumbnail_url;
  }
  
  // Return placeholder as final fallback
  return fallbackImage;
});

// Simple gray background SVG as fallback for missing images
const fallbackImage = '/images/placeholder.svg';

// Get thumbnail for thumbnails section - show raw url for debugging
const getThumbnailUrl = (img, index) => {
  // Show all available properties for debugging
  console.log('Thumbnail ' + index + ':', JSON.stringify(img));
  
  // Handle case where img might be a string (direct URL)
  if (typeof img === 'string' && img.trim() !== '') {
    return img;
  }
  
  // Check for various possible URL properties
  if (img) {
    // Try common image URL properties
    const urlProps = ['url', 'thumb_url', 'thumbnail_url', 'src', 'image', 'image_url', 'path'];
    for (const prop of urlProps) {
      if (img[prop] && typeof img[prop] === 'string' && img[prop].trim() !== '') {
        return img[prop];
      }
    }
  }
  
  // Return placeholder as final fallback
  console.log('Thumbnail ' + index + ' using placeholder');
  return '/images/placeholder.svg';
};

// Fallback main image - always returns first available image for initial display
const fallbackMainImage = computed(() => {
  if (productImages.value.length > 0) {
    return getImageUrl(productImages.value[0]) || fallbackImage;
  }
  return fallbackImage;
});

// Select image from gallery
const selectImage = (index) => {
  selectedImageIndex.value = index;
};

// Check auth status on mount
onMounted(async () => {
  await checkAuthStatus();
});

// Format price in KES
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '—';
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    maximumFractionDigits: 0,
  }).format(Number(value));
};

// Calculate discount percentage
const discountPercentage = computed(() => {
  if (currentPrice.value && currentComparePrice.value) {
    return Math.round(((currentPrice.value - currentComparePrice.value) / currentPrice.value) * 100);
  }
  return 0;
});

// Add to wishlist handler - add to wishlist and redirect to wishlist page
const addToWishlist = () => {
  if (!isLoggedIn.value) {
    router.get('/login', {}, { 
      preserveScroll: true,
      data: { redirect: window.location.href }
    });
    return;
  }
  
  // Get product data
  const p = getProductData();
  const productId = p?.id;
  const productSlug = p?.slug;
  
  // Use ID if available, otherwise use slug
  if (productId) {
    router.post(`/profile/wishlist/${productId}`, {}, {
      onSuccess: () => {
        router.visit('/profile/wishlist');
      },
      onError: () => {
        toast.error('Failed to add to wishlist', { position: 'bottom-left', autoClose: 3000 });
      }
    });
  } else if (productSlug) {
    // Use slug-based route
    router.post(`/profile/wishlist-by-slug/${productSlug}`, {}, {
      onSuccess: () => {
        router.visit('/profile/wishlist');
      },
      onError: () => {
        toast.error('Failed to add to wishlist', { position: 'bottom-left', autoClose: 3000 });
      }
    });
  } else {
    toast.error('Product not found', { position: 'bottom-left', autoClose: 3000 });
  }
};

// Add to cart handler
const handleAddToCart = async () => {
  // Get product using helper function
  const p = getProductData();
  console.log('Product for cart:', toRaw(p));
  
  // Get product slug from props
  let productSlug = p?.slug;
  
  // If no slug, try to get from URL
  if (!productSlug) {
    const pathParts = window.location.pathname.split('/');
    productSlug = pathParts[pathParts.length - 1];
  }

  if (!productSlug) {
    toast.error('Cannot find product', { position: 'bottom-left', autoClose: 3000 });
    return;
  }

  // Check stock before adding
  if (currentStock.value <= 0) {
    toast.error('This product is out of stock', { position: 'bottom-left', autoClose: 3000 });
    return;
  }
  
  try {
    // Send product_slug to backend (addToCart expects slug)
    await addToCart(productSlug, quantity.value, selectedVariantId.value);
    toast.success('Product added to cart!', { position: 'bottom-left', autoClose: 2000 });
  } catch (err) {
    toast.error('Failed to add product to cart', { position: 'bottom-left', autoClose: 3000 });
  }
};

// Go back to product listing
const goBack = () => router.get('/products', {}, { preserveScroll: true });

// Submit rating
const submitRating = async () => {
  if (!isUserLoggedIn.value) {
    router.get('/login', {}, { 
      preserveScroll: true,
      data: { redirect: window.location.href }
    });
    return;
  }
  
  if (selectedRating.value < 1 || selectedRating.value > 5) {
    toast.error('Please select a rating', { position: 'bottom-left', autoClose: 3000 });
    return;
  }
  
  isRatingSubmitting.value = true;
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || 
                 document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1];
    
    const response = await axios.post(`/products/${props.product.id}/rating`, {
      rating: selectedRating.value,
      comment: ratingComment.value,
    }, {
      headers: {
        'X-CSRF-TOKEN': csrf || '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });
    
    toast.success('Rating submitted!', { position: 'bottom-left', autoClose: 2000 });
    
    // Refresh the page to show updated ratings
    router.reload({ only: ['product'] });
  } catch (error) {
    console.error('Rating error:', error);
    toast.error('Failed to submit rating', { position: 'bottom-left', autoClose: 3000 });
  } finally {
    isRatingSubmitting.value = false;
  }
};

// Submit comment
const submitComment = async () => {
  if (!isUserLoggedIn.value) {
    router.get('/login', {}, { 
      preserveScroll: true,
      data: { redirect: window.location.href }
    });
    return;
  }
  
  if (!newComment.value.trim()) {
    toast.error('Please enter a comment', { position: 'bottom-left', autoClose: 3000 });
    return;
  }
  
  isCommentSubmitting.value = true;
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || 
                 document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1];
    
    const response = await axios.post(`/products/${props.product.id}/comment`, {
      comment: newComment.value,
    }, {
      headers: {
        'X-CSRF-TOKEN': csrf || '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });
    
    toast.success('Comment added!', { position: 'bottom-left', autoClose: 2000 });
    newComment.value = '';
    
    // Refresh the page to show updated comments
    router.reload({ only: ['product'] });
  } catch (error) {
    console.error('Comment error:', error);
    toast.error('Failed to add comment', { position: 'bottom-left', autoClose: 3000 });
  } finally {
    isCommentSubmitting.value = false;
  }
};

// Format date
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-KE', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};
</script>

<template>
  <MainLayout>
    <Head>
      <Title>{{ product?.name ?? 'Product Details' }} | Buynow</Title>
      <meta name="description" :content="product?.short_description || product?.description || 'Buy ' + (product?.name || 'this product') + ' at Buynow Kenya. Best price with M-Pesa payment available.'" />
      <meta name="keywords" :content="product?.name + ', ' + (product?.category?.name || '') + ', buy online, Kenya, electronics'" />
      
      <!-- Open Graph -->
      <meta property="og:type" content="product" />
      <meta property="og:title" :content="(product?.name || 'Product') + ' | Buynow'" />
      <meta property="og:description" :content="product?.short_description || 'Buy ' + (product?.name || 'this product') + ' at Buynow'" />
      <meta property="og:image" :content="product?.thumbnail_url || '/images/og-image.png'" />
      <meta property="og:url" :content="$page.url" />
      <meta property="product:price:amount" :content="String(currentPrice || 0)" />
      <meta property="product:price:currency" content="KES" />
      <meta property="product:availability" :content="(currentStock || 0) > 0 ? 'in stock' : 'out of stock'" />
      
      <!-- Twitter Card -->
      <meta name="twitter:card" content="product" />
      <meta name="twitter:title" :content="(product?.name || 'Product') + ' | Buynow'" />
      <meta name="twitter:description" :content="product?.short_description || 'Buy ' + (product?.name || 'this product') + ' at Buynow'" />
      <meta name="twitter:image" :content="product?.thumbnail_url || '/images/og-image.png'" />
      
      <!-- Canonical URL -->
      <link rel="canonical" :content="$page.url" />
    </Head>

    <div class="container mx-auto py-6 px-2 md:px-4">
      <!-- Back Button -->
      <button @click="goBack" class="mb-4 text-sm text-gray-600 hover:text-yellow-500 flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Products
      </button>

      <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
        <!-- Product Image -->
        <div class="lg:w-1/2">
          <div class="relative bg-white rounded-lg border border-gray-200 overflow-hidden">
            <!-- Discount Badge -->
            <div v-if="discountPercentage > 0" class="absolute top-0 left-0 z-10 bg-yellow-400 text-black text-sm font-bold px-3 py-1 rounded-br-lg">
              -{{ discountPercentage }}%
            </div>
            
            <!-- Main Product Image -->
            <img
              v-if="mainImage"
              :src="mainImage"
              :alt="product?.name"
              class="w-full h-[300px] md:h-[400px] lg:h-[500px] object-contain bg-white"
              @error="(e) => e.target.src = '/images/placeholder.svg'"
            />
            <div v-else class="w-full h-[300px] md:h-[400px] lg:h-[500px] bg-gray-100 flex items-center justify-center">
              <img src="/images/placeholder.svg" alt="No image available" class="w-32 h-32 object-contain opacity-50" />
            </div>
          </div>

          <!-- Product Gallery / Thumbnails -->
          <div v-if="productImages.length >= 1" class="mt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Images</h3>
            <div class="flex gap-2 overflow-x-auto pb-2">
              <div 
                v-for="(img, index) in productImages" 
                :key="index"
                @click="selectImage(index)"
                class="w-20 h-20 flex-shrink-0 rounded-lg border-2 overflow-hidden cursor-pointer hover:border-yellow-400 transition-colors bg-gray-100"
                :class="selectedImageIndex === index ? 'border-yellow-500 ring-2 ring-yellow-500 ring-opacity-50' : 'border-gray-200'"
              >
                <img 
                  :src="getThumbnailUrl(img, index)" 
                  :alt="product?.name + ' image ' + (index + 1)" 
                  class="w-full h-full object-cover"
                  @error="(e) => e.target.src = '/images/placeholder.svg'"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="lg:w-1/2 flex flex-col gap-4">
          <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ product?.name }}</h1>

          <div class="text-sm text-gray-500 dark:text-gray-400">
            <span>Category: </span>
            <a :href="`/products?category=${product?.category?.slug}`" class="text-yellow-600 hover:underline">
              {{ product?.category?.name ?? 'Uncategorized' }}
            </a>
            <span class="mx-2">|</span>
            <span>Brand: </span>
            <a :href="`/products?brands[]=${product?.brand?.id}`" class="text-yellow-600 hover:underline">
              {{ product?.brand?.name ?? 'No Brand' }}
            </a>
          </div>

          <!-- Price Section -->
          <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
            <div class="flex items-baseline gap-3">
              <span class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatCurrency(currentPrice) }}
              </span>
              <span v-if="currentComparePrice" class="text-lg text-gray-400 line-through">
                {{ formatCurrency(currentComparePrice) }}
              </span>
            </div>
            <div v-if="discountPercentage > 0" class="text-green-600 text-sm font-medium mt-1">
              You save {{ discountPercentage }}%
            </div>
          </div>

          <!-- Stock Status -->
          <div v-if="currentStock > 0" class="flex items-center gap-2 text-green-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">In Stock ({{ currentStock }} available)</span>
          </div>
          <div v-else class="flex items-center gap-2 text-red-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">Out of Stock</span>
          </div>

          <!-- Star Rating -->
          <div class="flex items-center gap-2">
            <div class="flex items-center cursor-pointer" @click="selectedRating = selectedRating > 0 ? 0 : 5">
              <svg v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= (product?.rating || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <span class="text-sm text-gray-500">({{ product?.rating_count || 0 }} reviews)</span>
          </div>

          <!-- Shipping Fee -->
          <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span>Shipping: </span>
            <span class="font-medium" :class="product?.shipping_fee == 0 ? 'text-green-600' : ''">
              {{ product?.shipping_fee == 0 ? 'Free' : formatCurrency(product?.shipping_fee) }}
            </span>
          </div>

          <!-- Variants -->
          <div v-if="product?.variants?.length > 0" class="py-3 border-t border-b border-gray-200 dark:border-zinc-700">
            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Select Variant:</h3>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variant in product.variants"
                :key="variant.id"
                @click="selectVariant(variant.id)"
                class="px-3 py-1.5 border rounded text-sm transition-colors"
                :class="selectedVariantId === variant.id 
                  ? 'border-yellow-500 bg-yellow-400 text-black font-medium' 
                  : 'border-gray-300 dark:border-zinc-600 hover:border-yellow-400 hover:text-yellow-600'"
              >
                {{ variant.variantOptions?.map(v => v.value).join(' / ') || variant.name }}
              </button>
            </div>
          </div>

          <!-- Quantity and Actions -->
          <div class="mt-2 space-y-4">
            <!-- Quantity Selector -->
            <div class="flex items-center gap-4">
              <span class="text-gray-700 dark:text-gray-300 font-medium">Quantity:</span>
              <div class="flex items-center border border-gray-300 dark:border-zinc-600 rounded">
                <button 
                  @click="quantity > 1 && quantity--" 
                  class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors"
                  :disabled="quantity <= 1"
                >
                  -
                </button>
                <input
                  type="number"
                  v-model.number="quantity"
                  min="1"
                  :max="currentStock || 1"
                  class="w-16 text-center border-x border-gray-300 dark:border-zinc-600 py-2 focus:outline-none"
                />
                <button 
                  @click="quantity < (currentStock || 1) && quantity++" 
                  class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors"
                  :disabled="quantity >= (currentStock || 1)"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Add to Cart and Buy Now Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
              <button
                @click="handleAddToCart"
                class="flex-1 py-3 px-6 bg-yellow-400 text-black font-bold rounded hover:bg-yellow-500 transition-colors flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                ADD TO CART
              </button>
            </div>

            <!-- Wishlist -->
            <button
              @click="addToWishlist"
              :disabled="isWishlistLoading"
              class="w-full py-3 px-6 border-2 font-bold rounded transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
              :class="isInWishlist 
                ? 'border-red-500 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20' 
                : 'border-yellow-400 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-zinc-800'"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                :fill="isInWishlist ? 'currentColor' : 'none'"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
              <template v-if="!isLoggedIn">LOGIN TO ADD TO WISHLIST</template>
              <template v-else-if="isInWishlist">REMOVE FROM WISHLIST</template>
              <template v-else>ADD TO WISHLIST</template>
            </button>
          </div>

          <!-- Delivery Info -->
          <div class="mt-4 p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg space-y-2">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
              <span>Free delivery on orders above KSh 1,000</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Quality assured</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>7 day return policy</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Product Description</h2>
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
          <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
            {{ product?.description ?? product?.short_description ?? 'No description available.' }}
          </p>
        </div>
      </div>

      <!-- Rating Section -->
      <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Rate this Product</h2>
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
          <!-- Current Rating Display -->
          <div class="flex items-center gap-2 mb-4">
            <div class="flex items-center">
              <svg v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= (product?.rating || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <span class="text-sm text-gray-500">({{ product?.rating_count || 0 }} reviews)</span>
          </div>

          <!-- Rating Form (only show if user is logged in) -->
          <div v-if="isUserLoggedIn" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Click to rate:</p>
            <div class="flex items-center gap-1">
              <button
                v-for="star in 5"
                :key="star"
                @click="selectedRating = star"
                class="p-1 transition-transform hover:scale-110"
              >
                <svg class="w-8 h-8" :class="star <= selectedRating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </button>
            </div>
            <div v-if="selectedRating > 0">
              <textarea
                v-model="ratingComment"
                rows="2"
                placeholder="Add a comment (optional)"
                class="w-full border border-gray-300 dark:border-zinc-600 rounded-lg px-4 py-2 text-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none"
              ></textarea>
              <button
                @click="submitRating"
                :disabled="isRatingSubmitting"
                class="mt-2 py-2 px-4 bg-yellow-400 text-black font-bold rounded hover:bg-yellow-500 transition-colors disabled:opacity-50"
              >
                {{ isRatingSubmitting ? 'Submitting...' : 'Submit Rating' }}
              </button>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500">
            <a href="/login" class="text-yellow-600 hover:underline">Login</a> to rate this product
          </div>

          <!-- Existing Ratings -->
          <div v-if="product?.ratings?.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Customer Reviews</h3>
            <div class="space-y-4">
              <div v-for="rating in product.ratings" :key="rating.id" class="border-b border-gray-100 dark:border-zinc-700 pb-4 last:border-0">
                <div class="flex items-center gap-2 mb-1">
                  <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-xs font-bold text-black">
                    {{ rating.user?.name?.charAt(0).toUpperCase() || 'U' }}
                  </div>
                  <span class="font-medium text-gray-900 dark:text-white">{{ rating.user?.name || 'Anonymous' }}</span>
                  <span class="text-gray-400 text-sm">{{ formatDate(rating.created_at) }}</span>
                </div>
                <div class="flex items-center mb-1">
                  <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <p v-if="rating.comment" class="text-gray-600 dark:text-gray-400 text-sm">{{ rating.comment }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Comment Section -->
      <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Customer Comments</h2>
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
          <!-- Comment Form -->
          <div v-if="isUserLoggedIn" class="mb-6">
            <textarea
              v-model="newComment"
              rows="3"
              placeholder="Write a comment about this product..."
              class="w-full border border-gray-300 dark:border-zinc-600 rounded-lg px-4 py-2 text-sm bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none"
            ></textarea>
            <button
              @click="submitComment"
              :disabled="isCommentSubmitting || !newComment.trim()"
              class="mt-2 py-2 px-4 bg-yellow-400 text-black font-bold rounded hover:bg-yellow-500 transition-colors disabled:opacity-50"
            >
              {{ isCommentSubmitting ? 'Posting...' : 'Post Comment' }}
            </button>
          </div>
          <div v-else class="mb-6 text-sm text-gray-500">
            <a href="/login" class="text-yellow-600 hover:underline">Login</a> to post a comment
          </div>

          <!-- Existing Comments -->
          <div v-if="product?.comments?.length > 0" class="space-y-4">
            <div v-for="comment in product.comments" :key="comment.id" class="border-b border-gray-100 dark:border-zinc-700 pb-4 last:border-0">
              <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-xs font-bold text-black">
                  {{ comment.user?.name?.charAt(0).toUpperCase() || 'U' }}
                </div>
                <span class="font-medium text-gray-900 dark:text-white">{{ comment.user?.name || 'Anonymous' }}</span>
                <span class="text-gray-400 text-sm">{{ formatDate(comment.created_at) }}</span>
              </div>
              <p class="text-gray-600 dark:text-gray-400 text-sm">{{ comment.comment }}</p>
            </div>
          </div>
          <div v-else class="text-gray-500 text-sm text-center py-4">
            No comments yet. Be the first to comment!
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
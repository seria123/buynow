<script setup>
import { computed, onMounted } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Buynow - Premium Electronics & Gadgets in Kenya'
  },
  description: {
    type: String,
    default: 'Shop premium electronics, gadgets, smartphones, laptops and more at Buynow Kenya. Best prices, fast delivery.'
  },
  image: {
    type: String,
    default: '/images/og-image.png'
  },
  url: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'website'
  },
  price: {
    type: Number,
    default: null
  },
  priceCurrency: {
    type: String,
    default: 'KES'
  },
  availability: {
    type: String,
    default: 'https://schema.org/InStock'
  },
  brand: {
    type: String,
    default: ''
  },
  sku: {
    type: String,
    default: ''
  },
});

// Build full URL
const fullUrl = computed(() => {
  const baseUrl = window.location.origin;
  return props.url ? `${baseUrl}${props.url}` : window.location.href;
});

// Full image URL
const fullImage = computed(() => {
  const baseUrl = window.location.origin;
  if (props.image.startsWith('http')) return props.image;
  return `${baseUrl}${props.image}`;
});

// JSON-LD for products
const jsonLd = computed(() => {
  if (props.type !== 'product') return null;
  
  const productData = {
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": props.title,
    "image": fullImage.value,
    "description": props.description,
    "brand": {
      "@type": "Brand",
      "name": props.brand || 'Buynow'
    },
    "sku": props.sku,
    "offers": {
      "@type": "Offer",
      "url": fullUrl.value,
      "priceCurrency": props.priceCurrency,
      "price": props.price,
      "availability": props.availability,
      "itemCondition": "https://schema.org/NewCondition"
    }
  };
  
  return JSON.stringify(productData);
});

// Inject JSON-LD using onMounted to avoid Vue template warning
onMounted(() => {
  if (jsonLd.value) {
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.text = jsonLd.value;
    document.head.appendChild(script);
  }
});
</script>

<template>
  <Head>
    <!-- Title -->
    <Title>{{ title }}</Title>
    
    <!-- Meta Tags -->
    <meta name="description" :content="description" />
    <meta name="author" content="Buynow Kenya" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" :href="fullUrl" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" :content="type" />
    <meta property="og:site_name" content="Buynow" />
    <meta property="og:title" :content="title" />
    <meta property="og:description" :content="description" />
    <meta property="og:image" :content="fullImage" />
    <meta property="og:url" :content="fullUrl" />
    <meta property="og:locale" content="en_KE" />
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="title" />
    <meta name="twitter:description" :content="description" />
    <meta name="twitter:image" :content="fullImage" />
    <meta name="twitter:site" content="@buynow" />
    <meta name="twitter:creator" content="@buynow" />
  </Head>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/landing_page/navbar.vue';
import Footer from '@/Components/landing_page/footer.vue';
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#f7f5ee] antialiased font-sans">
    
    <Navbar />
    <main class="flex-grow w-full pt-[60px] md:pt-[68px]">
      <slot />
    </main>

    <Footer />

  </div>
</template>
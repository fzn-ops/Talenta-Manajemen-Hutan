<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Navbar from '@/Components/landing_page/navbar.vue';
import Footer from '@/Components/landing_page/footer.vue';
</script>

<template>
  <div class="w-full min-h-screen flex flex-col justify-between bg-[#4b857a] text-white font-poppins">
    <Navbar />
    <slot />
    <Footer />
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/Layouts/landing_page/main.vue';
import HeroSection from '@/Components/landing_page/HeroSection.vue';
</script>

<template>
  <MainLayout>
    <HeroSection />
  </MainLayout>
</template>
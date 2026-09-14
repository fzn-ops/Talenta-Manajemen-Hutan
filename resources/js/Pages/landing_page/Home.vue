<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/Layouts/landing_page/main.vue';
</script>

<template>
  <MainLayout>
    <div class="w-full min-h-screen flex flex-col justify-between bg-white text-[#4b857a] font-poppins">
      <div class="flex-1 flex flex-col items-center justify-center px-4 py-8">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 text-center">Selamat Datang di Talenta</h1>
        <p class="text-lg md:text-xl text-center mb-8">Website teman berkembangmu</p>
        <Link href="/login" class="bg-[#4f8073] text-white rounded-full px-6 py-2.5 text-sm font-bold hover:scale-105 hover:shadow-md transition-all duration-200">
          Masuk
        </Link>
      </div>
    </div>
  </MainLayout>
</template>
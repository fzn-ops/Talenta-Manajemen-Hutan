<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import MainLayout from '@/Layouts/landing_page/main.vue';
import HeroSection from '@/Components/landing_page/HeroSection.vue';
import FeatureSection from '@/Components/landing_page/FeatureSection.vue';
import ActivitySection from '@/Components/landing_page/Activity.vue';

/* // 1. Buat variabel reaktif untuk melacak posisi scroll
const scrollY = ref(0);

// 2. Fungsi untuk mengupdate nilai scroll
const handleScroll = () => {
  scrollY.value = window.scrollY;
};

// 3. Pasang pendengar (listener) saat komponen dimuat
onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

// 4. Bersihkan memori saat pindah halaman
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
}); */
</script>

<template>
  <MainLayout>
    <HeroSection />
    <div class="relative w-full z-20 overflow-x-hidden overflow-x-clip">
      <img 
        src="/assets/icons/icon_daun_kiri.svg" 
        alt="Daun Kiri" 
        class="absolute left-0 -top-16 z-30 w-32 drop-shadow-lg pointer-events-none md:-top-24 md:w-32 lg:w-48"
      />
    <FeatureSection />
      <img 
        src="/assets/icons/icon_daun_kanan.svg" 
        alt="Daun Kanan" 
        class="absolute -right-6 -bottom-10 z-10 w-32 pointer-events-none drop-shadow-lg sm:-right-8 sm:-bottom-32 sm:w-32 md:-right-12 md:-bottom-20 md:w-48 lg:-right-16 lg:-bottom-24 lg:w-60"
      />
    </div>
    <ActivitySection />
  </MainLayout>
</template>


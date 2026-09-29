<script setup>
import { computed } from 'vue';

const row1 = [
  'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?q=80&w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=800&auto=format&fit=crop',
];

// Gambar online dari Unsplash (Baris 2: Gerak Kiri ke Kanan)
const row2 = [
  'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?q=80&w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=800&auto=format&fit=crop',
];

// Kita gandakan array-nya (duplicate) biar looping animasinya nggak patah/kosong di ujung
const loopedRow1 = computed(() => [...row1, ...row1]);
const loopedRow2 = computed(() => [...row2, ...row2]);
</script>

<style scoped>
/* Animasi dari Kanan ke Kiri */
.animate-scroll-left {
  animation: scroll-left 35s linear infinite;
}

/* Animasi dari Kiri ke Kanan */
.animate-scroll-right {
  animation: scroll-right 35s linear infinite;
}

@keyframes scroll-left {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(calc(-50% - 0.5 * 1rem)); /* 1rem adalah gap/jarak antar gambar */
  }
}

@keyframes scroll-right {
  0% {
    transform: translateX(calc(-50% - 0.5 * 1rem));
  }
  100% {
    transform: translateX(0);
  }
}
</style>

<template>
  <!-- Background hijau gelap menyesuaikan gambar -->
  <section class="relative w-full overflow-hidden bg-[#2a5c4b] py-8 lg:py-12">
    
    <!-- WRAPPER GAMBAR BERJALAN -->
    <div class="flex flex-col gap-4 sm:gap-6">
      
      <!-- BARIS 1 (Kanan ke Kiri) -->
      <!-- 'w-max' penting agar lebarnya menyesuaikan total panjang gambar -->
      <div class="flex w-max animate-scroll-left gap-4 sm:gap-6">
        <div 
          v-for="(img, index) in loopedRow1" 
          :key="'row1-' + index"
          class="h-40 w-64 flex-shrink-0 overflow-hidden rounded-xl sm:h-56 sm:w-80 lg:h-64 lg:w-[26rem]"
        >
          <img 
            :src="img" 
            alt="Galeri Mahasiswa" 
            class="h-full w-full object-cover brightness-[0.4] transition-all duration-300 hover:brightness-75"
          />
        </div>
      </div>

      <!-- BARIS 2 (Kiri ke Kanan) -->
      <div class="flex w-max animate-scroll-right gap-4 sm:gap-6">
        <div 
          v-for="(img, index) in loopedRow2" 
          :key="'row2-' + index"
          class="h-40 w-64 flex-shrink-0 overflow-hidden rounded-xl sm:h-56 sm:w-80 lg:h-64 lg:w-[26rem]"
        >
          <img 
            :src="img" 
            alt="Galeri Mahasiswa" 
            class="h-full w-full object-cover brightness-[0.4] transition-all duration-300 hover:brightness-75"
          />
        </div>
      </div>

    </div>

    <!-- OVERLAY TEKS QUOTE DI TENGAH -->
    <div class="absolute inset-0 z-10 flex flex-col items-center justify-center p-6 text-center pointer-events-none">
      <div class="max-w-4xl px-4">
        <h2 class="text-xl font-bold leading-snug text-white drop-shadow-lg sm:text-3xl md:text-4xl lg:text-[2.5rem] lg:leading-[1.3]">
          “Keberhasilan bukanlah milik orang yang pintar. Keberhasilan adalah kepunyaan mereka yang senantiasa berusaha.”
        </h2>
        <p class="mt-4 text-sm font-bold italic text-white drop-shadow-md sm:text-base lg:text-xl lg:mt-6">
          — B.J. Habibie
        </p>
      </div>
    </div>

  </section>
</template>


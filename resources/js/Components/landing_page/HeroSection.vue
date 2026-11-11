<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  }
});

// Gunakan tanda tanya (?) dan (?? 0) untuk pengaman
const stats = computed(() => [
  { value: props.data?.profesional ?? 0, label: 'Profesional' },
  { value: props.data?.bisnis ?? 0, label: 'Bisnis' },
  { value: props.data?.birokrat ?? 0, label: 'Birokrat' },
  { value: props.data?.akademisi ?? 0, label: 'Akademisi' },
  { value: props.data?.total ?? 0, label: 'Total' },
]);
</script>

<template>
  <section class="relative w-full overflow-hidden bg-[#fcfcfb] py-12 lg:py-24 font-poppins text-[#152c5b]">
    
    <!-- ========================================== -->
    <!-- ORNAMEN DEKORASI                           -->
    <!-- ========================================== -->
    <svg class="absolute -bottom-24 -right-16 w-[350px] h-[350px] opacity-40 pointer-events-none" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="50" cy="50" r="48" stroke="#152c5b" stroke-width="0.5" stroke-dasharray="2 3" fill="none" />
    </svg>

    <svg class="absolute top-10 left-[5%] w-8 h-8 text-[#7bb896] pointer-events-none md:left-[8%]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>

    <svg class="absolute top-[25%] left-[48%] w-7 h-7 text-[#7bb896] pointer-events-none lg:left-[51%]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>

    <svg class="absolute bottom-12 left-[12%] w-6 h-6 text-[#7bb896] pointer-events-none" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>


    <!-- ========================================== -->
    <!-- KONTEN UTAMA                               -->
    <!-- ========================================== -->
    <div class="relative z-10 mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-8 px-6 lg:flex-row lg:gap-8 xl:px-10">
      
      <!-- GAMBAR (Di Mobile order-1 [Atas], Di Desktop lg:order-2 [Kanan]) -->
      <div class="order-1 flex w-full justify-center lg:order-2 lg:w-[45%] lg:justify-end">
        <div class="relative flex w-full max-w-[520px] aspect-[4/3.2] items-center justify-center overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] bg-[#e6efeb] shadow-lg">
          
          <span class="absolute text-sm font-bold text-[#7bb896]/60">
            [ Tempat Gambar Ilustrasi ]
          </span>

          <img 
            src="assets/images/hero_picture.svg" 
            alt="Ilustrasi Petualang Talenta" 
            class="relative z-10 h-full w-full object-cover transition-transform duration-500"
            @error="(e) => e.target.style.display = 'none'"
          />
        </div>
      </div>

      <!-- TEKS & STATISTIK (Di Mobile order-2 [Bawah], Di Desktop lg:order-1 [Kiri]) -->
      <div class="order-2 flex w-full flex-col items-start lg:order-1 lg:w-[55%]">
        
        <!-- Judul -->
        <h2 class="mb-4 text-xl font-extrabold leading-[1.2] text-[#152c5b] sm:text-2xl md:text-3xl lg:mb-6 lg:text-[48px] lg:leading-[1.15] lg:tracking-tight">
          Mulai Perjalanan Pengembangan Dirimu Bersama Talenta!
        </h2>

        <!-- Deskripsi -->
        <div class="mb-8 space-y-4 pr-0 text-[13px] font-medium leading-relaxed text-[#475569] sm:text-sm lg:mb-10 lg:space-y-5 lg:pr-10">
          <p>
            Temukan berbagai peluang menarik untuk mengembangkan potensi,
            memperluas wawasan, dan mendapatkan pengalaman baru melalui
            beragam kegiatan yang tersedia di Talenta.
          </p>
          <p>
            Mulai dari kategori Profesional, Bisnis, Birokrat, hingga Akademisi,
            ada banyak kesempatan untuk mengeksplorasi minat, mengasah kemampuan,
            dan membangun relasi. Yuk, jelajahi Talenta dan temukan kesempatan
            yang tepat untuk perjalanan pengembangan dirimu!
          </p>
        </div>

<div class="mb-8 flex w-full max-w-[500px] items-center justify-between lg:mb-12">
  
  <template v-for="(stat, index) in stats" :key="stat.label">
    <!-- Item Statistik -->
    <div class="flex flex-col items-center justify-center transition-transform hover:-translate-y-1">
      
      <!-- ANGKA: Menggunakan warna Oranye/Peach dari ilustrasi -->
      <span class="text-4xl font-black text-[#f47663] md:text-[42px] leading-none">
        {{ stat.value }}
      </span>
      
      <!-- LABEL: Uppercase dan direnggangkan (tracking-widest) -->
      <span class="mt-2 text-[9px] font-bold uppercase tracking-widest text-gray-400 sm:text-[10px]">
        {{ stat.label }}
      </span>
    </div>

    <!-- Garis Pemisah (Sama seperti punya Anda, tapi sedikit lebih pendek/halus) -->
    <div v-if="index !== stats.length - 1" class="h-8 w-[1px] bg-gray-200"></div>
  </template>

</div>

        <!-- Tombol -->
        <Link 
          href="#selengkapnya" 
          class="mx-auto flex w-full items-center justify-center rounded-full bg-[#152c5b] px-8 py-3 text-xs font-bold tracking-wide text-white transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-lg sm:mx-0 sm:w-fit sm:px-9 sm:py-3.5 sm:text-sm"
        >
          Selengkapnya
        </Link>
      </div>

    </div>
  </section>
</template>
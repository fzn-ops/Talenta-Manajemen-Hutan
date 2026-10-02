<script setup>
import { Link } from '@inertiajs/vue3';

</script>
<template>
  <section class="relative flex w-full flex-col items-center justify-center overflow-hidden bg-[#fcfcfb] pt-24 pb-48 sm:pt-32 sm:pb-64 lg:pt-40 lg:pb-72 font-sans">

    <!-- ========================================== -->
    <!-- ORNAMEN BINTANG                            -->
    <!-- ========================================== -->
    <svg class="absolute left-[12%] top-[25%] w-6 h-6 animate-pulse text-[#7bb896] pointer-events-none md:left-[15%] lg:left-[18%] lg:w-8 lg:h-8" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>

    <svg class="absolute right-[10%] top-[35%] w-9 h-9 animate-pulse text-[#7bb896] pointer-events-none md:right-[15%] lg:right-[18%] lg:w-12 lg:h-12" style="animation-delay: 0.5s;" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>


    <!-- ========================================== -->
    <!-- KONTEN: JUDUL                              -->
    <!-- ========================================== -->
    <h2 class="relative z-20 mb-8 px-4 text-center text-3xl font-extrabold tracking-tight text-[#152c5b] sm:mb-10 sm:text-4xl md:text-5xl lg:text-[54px]">
      Sudah siap melatih talentamu?
    </h2>


    <!-- ========================================== -->
    <!-- WADAH KHUSUS TOMBOL & PESAWAT              -->
    <!-- ========================================== -->
    <div class="relative flex w-full max-w-5xl justify-center">
        <Link 
            href="/login" 
            class="relative z-20 inline-flex items-center justify-center rounded-full bg-[#152c5b] px-10 py-3.5 text-sm font-bold tracking-wide text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-lg sm:px-12 sm:py-4 sm:text-base">
            Aku Siap
        </Link>

        <div class="absolute left-1/2 top-1/2 z-10 w-[450px] -translate-x-[60%] translate-y-[45%] pointer-events-none flex justify-center sm:w-[800px] sm:-translate-x-[55%] sm:translate-y-[10%] md:w-[1000px] lg:w-[1300px] lg:-translate-x-[64%]">
            <img 
                src="/assets/images/cta_pic.svg" 
                alt="Dekorasi Pesawat Kertas" 
                class="w-full h-auto object-contain opacity-95"
            />
        </div>

    </div>

  </section>
</template>
<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
});

const title = computed(() => {
    switch (props.status) {
        case 403: return 'Tidak Memiliki Akses';
        case 404: return 'Halaman Tidak Ditemukan';
        case 500: return 'Kesalahan Server';
        case 503: return 'Layanan Pemeliharaan';
        default: return 'Terjadi Kesalahan';
    }
});

const description = computed(() => {
    switch (props.status) {
        case 403: return 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini atau tautan sudah kedaluwarsa.';
        case 404: return 'Maaf, halaman atau alamat yang Anda tuju tidak ditemukan di sistem kami.';
        case 500: return 'Terjadi kendala pada server kami. Tim kami sedang berupaya memperbaikinya.';
        case 503: return 'Situs sedang dalam pemeliharaan rutin. Silakan coba beberapa saat lagi.';
        default: return props.message || 'Terjadi kesalahan yang tidak terduga.';
    }
});
</script>

<template>
  <Head :title="`${status} - ${title}`" />

  <!-- Background hijau penuh layar dengan ornamen konsisten -->
  <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#4b857a] font-sans">

    <!-- Ornamen SVG Kiri Atas -->
    <div class="pointer-events-none absolute -left-6 -top-6 z-0 sm:left-0 sm:top-0">
      <img src="/assets/images/icon_daun_login_kiri.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-58">
    </div>

    <!-- Ornamen SVG Kanan Atas -->
    <div class="pointer-events-none absolute -right-5 top-4 z-0 sm:right-4 sm:top-8 md:right-1 md:top-1">
      <img src="/assets/images/garis_bintang_login_kanan.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[24rem]">
    </div>

    <!-- Ornamen SVG Kiri Bawah -->
    <div class="pointer-events-none absolute bottom-4 -left-2 z-0 sm:bottom-8 sm:left-4 md:bottom-1 md:left-1">
      <img src="/assets/images/garis_bintang_login_kiri.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[24rem]">
    </div>

    <!-- Ornamen SVG Kanan Bawah -->
    <div class="pointer-events-none absolute -bottom-6 -right-6 z-0 sm:bottom-0 sm:right-0">
      <img src="/assets/images/icon_daun_login_kanan.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-48">
    </div>

    <!-- KARTU ERROR DINAMIS -->
    <div class="relative z-10 mx-4 w-full max-w-[420px] rounded-2xl bg-[#fcfcfb] p-8 text-center shadow-2xl sm:p-10">
      
      <!-- Ikon Peringatan -->
      <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-500 border border-red-100 shadow-sm">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>

      <!-- Kode Status (403, 404, dll) -->
      <h1 class="text-3xl font-extrabold text-[#152c5b]">
        {{ status }}
      </h1>
      <!-- Judul Dinamis -->
      <h2 class="mt-1 text-lg font-bold text-[#152c5b]">
        {{ title }}
      </h2>

      <!-- Deskripsi Pesan Dinamis -->
      <p class="mt-3 text-[13.5px] font-medium leading-relaxed text-[#5c6b89]">
        {{ description }}
      </p>

      <!-- Tombol Aksi Kembali -->
      <div class="mt-8 flex flex-col gap-3">
        <Link 
          href="/" 
          class="w-full rounded-full bg-[#4b857a] py-3 text-[15px] font-bold text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#3b6b62] hover:shadow-lg focus:outline-none text-center block"
        >
          Kembali ke Halaman Utama
        </Link>
      </div>

    </div>

  </div>
</template>
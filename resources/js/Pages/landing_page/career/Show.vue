<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';

// ==========================================
// DATA MOCKUP (Bisa diganti dari Props Backend)
// ==========================================
const props = defineProps({
    karirDetail:Object
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const handleShare = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: props.karirDetail.posisi,
        text: `Lowongan Kerja: ${props.karirDetail.posisi} di ${props.karirDetail.nama_instansi}`,
        url: window.location.href, 
      });
    } catch (error) {
      if (error.name !== 'AbortError') {
        console.error('Gagal membagikan:', error);
      }
    }
  } else {
    try {
      await navigator.clipboard.writeText(window.location.href);
      alert('Link lowongan berhasil disalin ke clipboard!');
    } catch (err) {
      console.error('Gagal menyalin link', err);
    }
  }
};

</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] pt-12 pb-16 font-sans text-[#152c5b] lg:pt-24 lg:pb-24">
      
      <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 md:px-10">
        
        <!-- ========================================== -->
        <!-- BREADCRUMB NAVIGATION                      -->
        <!-- ========================================== -->
        <nav class="mb-6 flex items-center text-sm font-bold sm:mb-8 sm:text-base pb-5">
          <Link href="/careers" class="text-[#152c5b] transition-colors hover:text-gray-600">
            Karir
          </Link>
          <span class="mx-2 text-gray-400">/</span>
          <span class="text-[#4b857a] underline decoration-2 underline-offset-4">
            Carpenter Intern
          </span>
        </nav>


        <!-- ========================================== -->
        <!-- HEADER SECTION (Logo, Info, Action)        -->
        <!-- ========================================== -->
        <div class="mb-10 flex flex-col items-center gap-6 md:flex-row md:items-start md:gap-8">
          
          <!-- Kiri: Logo Perusahaan -->
          <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#d9d9d9] sm:h-32 sm:w-32 md:h-40 md:w-40">
            <img v-if="karirDetail?.logo" :src="karirDetail?.logo" :alt="karirDetail?.company" class="h-full w-full object-cover" />
          </div>

          <!-- Tengah & Kanan: Info dan Tombol -->
          <div class="flex w-full flex-1 flex-col md:flex-row md:justify-between">
            
            <!-- Tengah: Informasi Pekerjaan (Semua Rata Tengah di Mobile) -->
            <div class="flex flex-col items-center text-center md:items-start md:text-left">
              <h1 class="mb-2 text-3xl font-extrabold tracking-tight text-[#152c5b] sm:text-4xl md:text-[34px] leading-tight">
                {{ karirDetail?.posisi }}
              </h1>
              <p class="mb-1 text-base font-bold text-[#152c5b]">
                {{ karirDetail?.nama_instansi }}
              </p>
              <p class="mb-3 text-[14px] font-medium text-[#152c5b]/75">
                {{ karirDetail?.kategori }}
              </p>
              
              <!-- PERBAIKAN LOKASI: Ikon SVG dibikin inline-block supaya menyatu dengan text-center -->
              <div class="text-[14px] font-medium text-[#152c5b]/80 px-2 sm:px-0">
                <p class="leading-relaxed">
                  <svg class="mb-1 mr-1 inline-block h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"></path>
                  </svg>
                  {{ karirDetail?.lokasi }}
                </p>
              </div>
            </div>

            <!-- Kanan: Deadline & Tombol Action -->
            <div class="mt-6 flex w-full flex-col gap-5 border-t border-gray-200 pt-6 md:mt-0 md:w-auto md:justify-between md:border-none md:pt-2 md:items-end">
              
              <!-- PERBAIKAN DEADLINE: Ditambah justify-center untuk mobile agar senada -->
              <div class="flex items-center justify-center gap-2.5 text-[14.5px] font-bold text-[#152c5b] sm:text-sm md:justify-end">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                </svg>
                <span>Deadline : {{ formatDate(karirDetail?.deadline) }}</span>
              </div>
              
              <!-- Tombol Daftar & Share -->
              <div class="flex w-full items-center gap-3 md:w-auto">
                <a 
                :href="karirDetail.tautan"
                :target="_blank"
                rel="noopener noreferrer"
                class="flex-1 rounded-full bg-[#152c5b] px-8 py-3 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md md:flex-none">
                  Daftar
                </a>
                <button 
                @click="handleShare"
                type="button" 
                class="flex h-[44px] w-[44px] shrink-0 items-center justify-center rounded-full bg-[#152c5b] text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md">
                    <img 
                        src="/assets/icons/share_icon.svg" 
                        alt="Share" 
                        class="h-5 w-5 object-contain"
                      />
                </button>
              </div>

            </div>

          </div>
        </div>


        <!-- ========================================== -->
        <!-- CONTENT CARDS (Deskripsi & Kualifikasi)    -->
        <!-- ========================================== -->
        <div class="flex flex-col gap-5 sm:gap-6">
          
          <!-- Card 1: Deskripsi Pekerjaan -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
            <h2 class="mb-4 text-xl font-extrabold text-[#152c5b] sm:mb-5 sm:text-[26px]">
              Deskripsi Pekerjaan
            </h2>
            <div class="space-y-4 text-[14px] leading-relaxed text-[#5c6b89] sm:text-[15px]">
              <p>
                {{ karirDetail?.deskripsi }}
              </p>
            </div>
          </div>

          <!-- Card 2: Kualifikasi -->
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
            <h2 class="mb-4 text-xl font-extrabold text-[#152c5b] sm:mb-5 sm:text-[26px]">
              Kualifikasi
            </h2>
            <div class="space-y-4 text-[14px] leading-relaxed text-[#5c6b89] sm:text-[15px]">
              <p>
                {{ karirDetail?.kualifikasi }}
              </p>
            </div>
          </div>

        </div>

      </div>
    </div>
  </MainLayout>
</template>
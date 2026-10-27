<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';
import Mahasiswa from '@/Pages/dashboard/admin/Mahasiswa.vue';

// Menerima props 'aktivitasDetail' yang dikirim dari ActivityController Laravel
const props = defineProps({
  aktivitasDetail: {
    type: Object,
    required: true
  }
});

// Helper function untuk mengambil URL gambar (sama seperti di halaman utama)
const getImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop';
  if (path.startsWith('http')) return path;
  return `/${path}`; // Sesuaikan dengan disk penyimpanan Laravel kamu
};

// Fungsi untuk membedakan warna berdasarkan nama kategori
const getTagColor = (tag) => {
  const name = typeof tag === 'object' ? (tag.name || tag.nama) : tag;
  
  switch (name?.toLowerCase()) {
    case 'profesional':
      return 'text-[#4b857a]'; 
    case 'bisnis':
      return 'text-yellow-600'; 
    case 'birokrat':
      return 'text-red-600';   
    case 'akademisi':
      return 'text-blue-600';  
    default:
      return 'text-gray-600';  
  }
};

const truncatedTitle = computed(() => {
  const title = props.aktivitasDetail?.judul || '';
  const maxLength = 30; 
  
  if (title.length > maxLength) {
    return title.substring(0, maxLength) + '...';
  }
  return title;
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] pt-10 pb-16 font-sans text-[#152c5b] lg:pt-24 lg:pb-24">
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10">
        
        <!-- ========================================== -->
        <!-- BREADCRUMB NAVIGATION                      -->
        <!-- ========================================== -->
        <nav class="mb-6 flex items-center text-sm font-bold sm:text-base">
          <Link :href="route('activities')" class="text-[#152c5b] transition-colors hover:text-gray-600">
            Aktivitas
          </Link>

          <span class="mx-2 text-gray-400">/</span>

          <span class="text-[#4b857a] underline decoration-2 underline-offset-4" :title="aktivitasDetail?.judul">
            {{ truncatedTitle }}
          </span>
        </nav>


        <!-- ========================================== -->
        <!-- MAIN CONTENT (GRID LAYOUT)                 -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12">
          
          <!-- KIRI: BAGIAN GALERI (Memakan 5 kolom di desktop) -->
          <div class="flex flex-col gap-4 lg:col-span-5">
            
            <!-- Gambar Utama -->
            <div class="relative w-full overflow-hidden rounded-xl bg-gray-200 aspect-[3/4] sm:aspect-[4/5] lg:aspect-[3/4]">
              <img 
                :src="getImageUrl(aktivitasDetail?.gambar_aktivitas?.find(g => g.utama == 1 || g.utama === true)?.gambar || aktivitasDetail?.gambar_aktivitas?.[0]?.gambar)" 
                :alt="aktivitasDetail?.judul" 
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 hover:scale-105"
              />
              
              <!-- Badge Jenis di Kiri Atas -->
              <span 
                v-if="aktivitasDetail?.jenis"
                :class="[
                  'absolute left-4 top-4 rounded-full px-3 py-1.5 text-xs font-bold shadow-sm capitalize text-white z-10',
                  aktivitasDetail?.jenis?.toLowerCase() === 'lomba' ? 'bg-amber-600' : 'bg-[#4b857a]'
                ]"
              >
                {{ aktivitasDetail?.jenis }}
              </span>

              <!-- Badge Partisipan di Kanan Atas -->
              <div class="absolute right-4 top-4 flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-gray-700 shadow-sm z-10">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                {{ aktivitasDetail?.partisipan }}
              </div>
            </div>

            <!-- Thumbnail (3 Kotak Sejajar) -->
            <div class="grid grid-cols-3 gap-4">
              <div 
                v-for="(thumb, index) in aktivitasDetail.gambar_aktivitas?.gambar" 
                :key="index"
                class="aspect-[4/5] w-full cursor-pointer overflow-hidden rounded-lg bg-[#e2e2e2] transition-colors hover:bg-gray-300"
              >
                <!-- Kalau nanti ada gambar thumbnail, pasang img tag di sini -->
                <img v-if="thumb" :src="thumb" class="h-full w-full object-cover" />
              </div>
            </div>
          </div>


          <!-- KANAN: BAGIAN INFORMASI (Memakan 7 kolom di desktop) -->
          <div class="flex flex-col lg:col-span-7">
            
            <!-- Judul & Deadline -->
            <div class="mb-4 flex flex-col items-start justify-between gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center lg:border-none lg:pb-0">
              <h1 class="text-2xl font-extrabold tracking-tight text-[#152c5b] sm:text-3xl lg:text-[32px]">
                {{ aktivitasDetail?.judul }}
              </h1>
              
              <!-- Deadline Badge -->
              <div class="flex shrink-0 items-center gap-2 text-sm font-semibold text-[#152c5b]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Deadline : {{ aktivitasDetail?.deadline }}
              </div>
            </div>

            <!-- Tags Kategori -->
            <div class="mb-8 flex flex-wrap items-center gap-2 text-sm font-bold sm:text-base">
              <template v-for="(tag, index) in aktivitasDetail?.kategori" :key="index">
                <span :class="getTagColor(tag)">
                  {{ typeof tag === 'object' ? (tag.name || tag.nama) : tag }}
                </span>
                
                <!-- Garis Pemisah (Kecuali item terakhir) -->
                <span v-if="index < (aktivitasDetail?.kategori?.length || 0) - 1" class="text-gray-300">|</span>
              </template>
            </div>

            <!-- Deskripsi Paragraf -->
            <div class="mb-10 space-y-5 text-[15px] leading-relaxed text-[#5c6b89] sm:text-base">
              <p class="whitespace-pre-line">
                {{ aktivitasDetail?.deskripsi }}
              </p>
            </div>

            <!-- Tombol Aksi (Terdorong ke bawah/kanan) -->
            <div class="mt-auto flex flex-wrap items-center justify-start gap-3 sm:justify-end sm:gap-4">
              <a 
                :href="aktivitasDetail.link_pendaftaran" 
                class="rounded-full bg-[#152c5b] px-8 py-3 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md sm:px-10"
              >
                Link Daftar
              </a>
              
              <Link 
                :href="route('mahasiswa.aktivitas.pendaftaran', aktivitasDetail?.id)"
                type="button"
                class="rounded-full bg-[#152c5b] px-8 py-3 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md sm:px-10"
              >
                Ikuti
            </Link>
              
              <!-- Tombol Share (Bundar) -->
              <button 
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-[#152c5b] text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md"
              >
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
      
    </div>
  </MainLayout>
</template>
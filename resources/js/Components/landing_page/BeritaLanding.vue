<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    beritaData: {
        type: [Object, Array],
        default: () => []
    }
});

const beritaList = computed(() => {
    if (!props.beritaData) return [];
    if (props.beritaData.data && Array.isArray(props.beritaData.data)) return props.beritaData.data;
    if (Array.isArray(props.beritaData)) return props.beritaData;
    return [];
});

// Berita Utama (Item Pertama)
const mainNews = computed(() => {
    const items = beritaList.value;
    if (items && items.length > 0) {
        return items[0];
    }
    return null;
});

// Daftar Berita Samping (Sisa 3 Item Berikutnya)
const listNews = computed(() => {
    const items = beritaList.value;
    if (items && items.length > 1) {
        return items.slice(1, 4); 
    }
    return [];
});

// Fungsi helper untuk merubah format tanggal
const formatDate = (dateString) => {
    if (!dateString) return '';
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <!-- 
    Section Utama 
    Dibikin relative supaya elemen daun (absolute) tertahan di dalam area section ini.
    Ada pb-32 (padding-bottom) buat ngasih space longgar di bawah biar tombol nggak ketutupan daun.
  -->
  <section class="relative w-full overflow-hidden bg-[#fafafa] py-5 pb-32 lg:py-5 lg:pb-24 font-sans text-[#152c5b]">
    
    <!-- ========================================== -->
    <!-- ASSETS DAUN DEKORASI                       -->
    <!-- ========================================== -->
    <!-- Daun Kiri Bawah -->
    <img 
      src="/assets/icons/icon_daun_kiri_berita.svg" 
      alt="Ornamen Daun Kiri" 
      class="pointer-events-none absolute bottom-0 left-0 w-32 -translate-x-4 translate-y-12 opacity-90 sm:w-48 lg:-translate-x-8 lg:translate-y-24 lg:w-64"
    />
    
    <!-- Daun Kanan Bawah -->
    <img 
      src="/assets/icons/icon_daun_kanan_berita.svg" 
      alt="Ornamen Daun Kanan" 
      class="pointer-events-none absolute bottom-0 right-0 w-40 translate-x-4 translate-y-12 opacity-90 sm:w-56 lg:translate-x-8 lg:translate-y-24 lg:w-72"
    />
    <!-- ========================================== -->

    <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      
      <!-- Judul Section -->
      <h2 class="mb-10 text-center text-3xl font-extrabold text-[#152c5b] sm:mb-14 sm:text-4xl">
        Berita Manajemen Hutan
      </h2>

      <!-- KONDISI 1: JIKA BERITA KOSONG -->
      <div v-if="beritaList.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100 max-w-xl mx-auto">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6"></path>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-[#152c5b] mb-1">Belum Ada Berita</h3>
          <p class="text-slate-500 text-sm">Informasi dan pengumuman terbaru akan segera diunggah di sini.</p>
      </div>

      <!-- KONDISI 2: JIKA BERITA ADA (Bungkus semua grid dan tombol di dalam v-else) -->
      <div v-else>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
          
          <!-- KOLOM KIRI: Berita Utama -->
          <Link 
          :href="route('news.detail',mainNews.id)"
          class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-shadow hover:shadow-md">
              <div class="relative w-full pb-[45%]">
                  <div class="absolute inset-0 p-4">
                    <img 
                      :src="mainNews.foto || 'https://via.placeholder.com/800x450'" 
                      :alt="mainNews.judul" 
                      class="h-full w-full object-cover rounded-xl"
                    />
                  </div>
              </div>
              <div class="flex flex-1 flex-col p-6">
                <span class="mb-2 text-xs font-semibold text-gray-500 sm:text-sm">
                  {{ formatDate(mainNews.created_at) }}
                </span>
                <h3 class="mb-3 text-xl font-bold leading-snug text-[#152c5b] sm:text-2xl line-clamp-2">
                  {{ mainNews.judul }}
                </h3>
                <!-- Menggunakan v-html agar tag <p> dari database ter-render rapi -->
                <div v-html="mainNews.deskripsi" class="text-[13px] leading-relaxed text-gray-600 sm:text-sm line-clamp-3"></div>
              </div>
            </Link>

          <!-- KOLOM KANAN: List Berita -->
          <div class="flex flex-col gap-4">
            <Link
              :href="route('news.detail', news.id)"
              v-for="news in listNews" 
              :key="news.id"
              class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-center sm:h-[140px]"
            >
              <!-- Gambar Thumbnail -->
              <div class="relative w-full pb-[40%] sm:w-[35%] sm:pb-0 sm:h-32">
                  <div class="absolute inset-0 p-3">
                      <img 
                          v-if="news.foto"
                          :src="news.foto" 
                          :alt="news.judul" 
                          class="h-full w-full object-cover rounded-xl"
                      />
                  </div>
              </div>
              
              <!-- Konten List -->
              <div class="flex w-full flex-col justify-center p-4 sm:w-[65%]">
                <span class="mb-1 text-[11px] font-semibold text-gray-500 sm:text-xs">
                  {{ formatDate(news.created_at) }}
                </span>
                <h3 class="mb-1 text-sm font-bold leading-tight text-[#152c5b] line-clamp-2 sm:text-base">
                  {{ news.judul }}
                </h3>
                <!-- Menggunakan v-html untuk list samping -->
                <div v-html="news.deskripsi" class="text-[11px] leading-relaxed text-gray-600 line-clamp-2 sm:text-xs"></div>
              </div>
            </Link>
          </div>

        </div>

        <!-- Tombol Lebih Banyak (Sekarang ikut di dalam v-else) -->
        <div class="mt-12 flex justify-center lg:mt-16">
          <Link 
            href="/news" 
            class="group inline-flex items-center gap-2 rounded-full bg-[#152c5b] px-7 py-3 text-sm font-semibold text-white transition-all duration-300 hover:bg-[#0f1f43] hover:shadow-lg sm:px-8 sm:py-3.5"
          >
            Lebih Banyak
            <!-- Icon Panah -->
            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </Link>
        </div>

      </div>

    </div>
  </section>
</template>
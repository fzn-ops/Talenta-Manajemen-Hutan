<script setup>
import { Link } from '@inertiajs/vue3'; // Hapus kalau nggak pakai Inertia, ganti tag <Link> jadi <a>

// Mock Data Berita biar kode template di bawah lebih bersih
const mainNews = {
  image: 'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=800&auto=format&fit=crop',
  date: '10 Juni 2029',
  title: 'DMNH IPB Kenalkan Praktik Perhutanan Sosial',
  description: 'Departemen Manajemen Hutan, Fakultas Kehutanan dan Lingkungan IPB University menyelenggarakan International Summer Course on Social Forestry 2029 pada 22-26 Juni 2029. Kegiatan ini menjadi bagian dari upaya Departemen Manajemen Hutan IPB dalam memperluas pembelajaran internasional mengenai pengelolaan hutan berbasis masyarakat, tata kelola perhutanan sosial, penghidupan masyarakat, serta pengelolaan hutan berkelanjutan. Kegiatan ini diikuti oleh 30 peserta dari berbagai universitas...'
};

const listNews = [
  {
    id: 1,
    image: 'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=800&auto=format&fit=crop', // Ganti dengan path gambarmu
    date: '10 Juni 2029',
    title: 'Pelatihan Lifeskill Survive di Hutan',
    description: 'Fakultas Kehutanan dan Lingkungan (Fahutan) IPB University kembali menyelenggarakan Fahutan Training Series (FTS) yang merupakan program pelatihan softskill bagi para mahasiswa. Tahun ini bertajuk \'Pelatihan Life Skill untuk Survive di Hutan\'.'
  },
  {
    id: 2,
    image: 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=800&auto=format&fit=crop', // Ganti dengan path gambarmu
    date: '10 Juni 2029',
    title: 'Lorem Ipsum Dolor Sit Amet',
    description: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit laborum consectetur ad laboris adipiscing nostrud in veniam anim. Et enim nostrud nisi consectetur deserunt sunt eu sunt.'
  },
  {
    id: 3,
    image: 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?q=80&w=800&auto=format&fit=crop', // Ganti dengan path gambarmu
    date: '10 Juni 2029',
    title: 'Lorem Ipsum Dolor Sit Amet',
    description: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit laborum consectetur ad laboris adipiscing nostrud in veniam anim. Et enim nostrud nisi consectetur deserunt sunt eu sunt.'
  }
];
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

      <!-- Wrapper Grid (Kiri Berita Utama, Kanan List Berita) -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
        
        <!-- KOLOM KIRI: Berita Utama -->
        <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-shadow hover:shadow-md">
            <div class="relative w-full pb-[60%] sm:pb-[50%] lg:pb-[55%]">
                <div class="absolute inset-0 p-4">
                  <img 
                    :src="mainNews.image || 'https://via.placeholder.com/800x450'" 
                    :alt="mainNews.title" 
                    class="h-full w-full object-cover rounded-xl"
                  />
                </div>
          </div>
          <div class="flex flex-1 flex-col p-6">
            <span class="mb-2 text-xs font-semibold text-gray-500 sm:text-sm">
              {{ mainNews.date }}
            </span>
            <h3 class="mb-3 text-xl font-bold leading-snug text-[#152c5b] sm:text-2xl">
              {{ mainNews.title }}
            </h3>
            <p class="text-[13px] leading-relaxed text-gray-600 sm:text-sm">
              {{ mainNews.description }}
            </p>
          </div>
        </article>

        <!-- KOLOM KANAN: List Berita -->
        <div class="flex flex-col justify-between gap-6">
          <article 
            v-for="news in listNews" 
            :key="news.id"
            class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-shadow hover:shadow-md sm:flex-row sm:items-stretch lg:h-[30%]"
          >
            <!-- Gambar Thumbnail -->
            <div class="relative w-full pb-[50%] sm:w-[40%] sm:pb-0 lg:w-[35%]">
                <div class="absolute inset-0 p-4">
                    <img 
                         v-if="news.image"
                        :src="news.image" 
                        :alt="news.title" 
                        class="h-full w-full object-cover rounded-xl"
                    />
                </div>
            </div>
            
            <!-- Konten List -->
            <div class="flex w-full flex-col justify-center p-4 sm:w-[60%] sm:p-5 lg:w-[65%]">
              <span class="mb-1 text-[11px] font-semibold text-gray-500 sm:mb-2 sm:text-xs">
                {{ news.date }}
              </span>
              <h3 class="mb-2 text-sm font-bold leading-tight text-[#152c5b] line-clamp-2 sm:text-base">
                {{ news.title }}
              </h3>
              <p class="text-[11px] leading-relaxed text-gray-600 line-clamp-3 sm:text-xs">
                {{ news.description }}
              </p>
            </div>
          </article>
        </div>

      </div>

      <!-- Tombol Lebih Banyak -->
      <div class="mt-12 flex justify-center lg:mt-16">
        <Link 
          href="/berita" 
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
  </section>
</template>
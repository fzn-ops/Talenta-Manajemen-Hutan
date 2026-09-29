<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';

// Mock Data untuk halaman detail (Bisa diganti jadi props dari controller backend nanti)
const activity = ref({
  title: 'First Meet MNH 63',
  deadline: '21 Agustus 2029',
  participants: 67,
  description: [
    'Lorem ipsum dolor sit amet, cupidatat eiusmod duis ut. Magna dolore dolor ex elit sed non cillum do aliqua adipiscing ad. Ullamco fugiat occaecat proident dolore incididunt eu pariatur officia. Exercitation eiusmod sunt adipiscing pariatur est nulla tempor enim voluptate laborum pariatur. Dolore velit occaecat aliqua sint ullamco dolor exercitation.',
    'Commodo in non exercitation nulla enim qui aliquip nulla. Esse adipiscing ex anim fugiat labore mollit mollit. Id dolore ea minim enim ut laborum magna. Aliqua ut nulla consequat sunt enim laboris voluptate quis. Non dolore elit reprehenderit dolore laboris laboris mollit incididunt eu in.',
    'Velit quis aliqua sed dolore excepteur irure cillum labore duis. Sed fugiat officia ad reprehenderit excepteur laborum cillum laborum reprehenderit nostrud. Commodo laboris sed cupidatat cillum aute fugiat veniam ex in eiusmod. Adipiscing nisi elit exercitation ea id ullamco eu quis. Mollit reprehenderit duis ut ut deserunt magna esse excepteur pariatur nisi. Sed tempor anim dolore anim excepteur eu nisi labore.'
  ],
  // Gambar dummy dari Unsplash
  mainImage: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop',
  // Placeholder thumbnails
  thumbnails: [null, null, null],
  tags: [
    { name: 'Profesional', colorClass: 'text-[#4b857a]' },
    { name: 'Bisnis', colorClass: 'text-yellow-600' },
    { name: 'Birokrat', colorClass: 'text-red-600' },
    { name: 'Akademisi', colorClass: 'text-blue-600' }
  ]
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
          <span class="text-[#4b857a] underline decoration-2 underline-offset-4">
            {{ activity.title }}
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
                :src="activity.mainImage" 
                :alt="activity.title" 
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 hover:scale-105"
              />
              
              <!-- Badge Peserta -->
              <div class="absolute right-4 top-4 flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-gray-700 shadow-sm">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                {{ activity.participants }}
              </div>
            </div>

            <!-- Thumbnail (3 Kotak Sejajar) -->
            <div class="grid grid-cols-3 gap-4">
              <div 
                v-for="(thumb, index) in activity.thumbnails" 
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
              <h1 class="text-3xl font-extrabold tracking-tight text-[#152c5b] sm:text-4xl lg:text-[42px]">
                {{ activity.title }}
              </h1>
              
              <!-- Deadline Badge -->
              <div class="flex shrink-0 items-center gap-2 text-sm font-semibold text-[#152c5b]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Deadline : {{ activity.deadline }}
              </div>
            </div>

            <!-- Tags Kategori -->
            <div class="mb-8 flex flex-wrap items-center gap-2 text-sm font-bold sm:text-base">
              <template v-for="(tag, index) in activity.tags" :key="tag.name">
                <span :class="tag.colorClass">{{ tag.name }}</span>
                <!-- Garis Pemisah (Kecuali item terakhir) -->
                <span v-if="index < activity.tags.length - 1" class="text-gray-300">|</span>
              </template>
            </div>

            <!-- Deskripsi Paragraf -->
            <div class="mb-10 space-y-5 text-[15px] leading-relaxed text-[#5c6b89] sm:text-base">
              <p v-for="(paragraph, index) in activity.description" :key="index">
                {{ paragraph }}
              </p>
            </div>

            <!-- Tombol Aksi (Terdorong ke bawah/kanan) -->
            <div class="mt-auto flex flex-wrap items-center justify-start gap-3 sm:justify-end sm:gap-4">
              <Link 
                href="#" 
                class="rounded-full bg-[#152c5b] px-8 py-3 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md sm:px-10"
              >
                Link Daftar
              </Link>
              
              <button 
                type="button"
                class="rounded-full bg-[#152c5b] px-8 py-3 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-md sm:px-10"
              >
                Ikuti
              </button>
              
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
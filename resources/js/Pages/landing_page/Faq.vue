<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/landing_page/main.vue';

// ==========================================
// 1. STATE UNTUK ACCORDION
// ==========================================
const openItems = ref([]);

const toggleItem = (id) => {
  const index = openItems.value.indexOf(id);
  if (index > -1) {
    openItems.value.splice(index, 1);
  } else {
    openItems.value.push(id);
  }
};

// ==========================================
// 2. DATA MOCKUP FAQ
// ==========================================
const faqs = ref([
  {
    category: 'Lorem',
    items: [
      { 
        id: 1, 
        question: 'Lorem Ipsum?', 
        answer: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit laborum consectetur ad laboris adipiscing nostrud in veniam anim. Et enim nostrud nisi Fauzan Fuadiansyah deserunt sunt eu sunt. Ut in aliquip in tempor consectetur deserunt culpa voluptate.' 
      },
      { 
        id: 2, 
        question: 'Dolor sit Amet?', 
        answer: 'Jawaban detail mengenai dolor sit amet dapat Anda temukan di halaman panduan kami. Pastikan Anda telah membaca syarat dan ketentuan yang berlaku sebelum melanjutkan proses pendaftaran.' 
      },
      { 
        id: 3, 
        question: 'Voluptate ut nostrud?', 
        answer: 'Kami menyediakan layanan bantuan 24/7 untuk memastikan semua kendala voluptate ut nostrud dapat teratasi dengan cepat oleh tim teknis kami.' 
      },
    ]
  },
  {
    category: 'Ipsum',
    items: [
      { 
        id: 4, 
        question: 'Lorem Ipsum?', 
        answer: 'Proses lorem ipsum biasanya memakan waktu 1-3 hari kerja tergantung dari kelengkapan dokumen yang Anda unggah ke dalam sistem kami.' 
      },
      { 
        id: 5, 
        question: 'Dolor sit Amet?', 
        answer: 'Anda dapat menghubungi pusat bantuan melalui email atau nomor telepon yang tertera pada bagian paling bawah halaman website ini.' 
      },
      { 
        id: 6, 
        question: 'Voluptate ut nostrud?', 
        answer: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit laborum consectetur ad laboris adipiscing nostrud in veniam anim. Et enim nostrud nisi Fauzan Fuadiansyah deserunt sunt eu sunt. Ut in aliquip in tempor consectetur deserunt culpa voluptate.' 
      },
    ]
  }
]);
</script>

<template>
  <MainLayout>
    <div class="relative min-h-screen w-full overflow-hidden bg-[#fcfcfb] pt-28 pb-16 font-sans text-[#152c5b] lg:pt-36 lg:pb-24">
      
      <!-- ========================================== -->
      <!-- ORNAMEN TANDA TANYA (HIJAU AKSEN)          -->
      <!-- ========================================== -->
      
      <!-- Tanda Tanya: Kiri Atas -->
      <svg 
        class="pointer-events-none absolute -left-24 -top-10 z-0 h-64 w-64 -rotate-12 text-[#4b857a] opacity-10 sm:-left-24 sm:-top-16 sm:h-[400px] sm:w-[400px] lg:-left-48 lg:-top-24 lg:h-[600px] lg:w-[600px]" 
        viewBox="0 0 100 100" 
        xmlns="http://www.w3.org/2000/svg"
      >
        <!-- Menggunakan elemen text di dalam SVG agar tidak ada batasan border font -->
        <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-size="90" font-weight="900" font-style="italic" fill="currentColor">
          ?
        </text>
      </svg>
      
      <!-- Tanda Tanya: Kanan Bawah -->
      <svg 
        class="pointer-events-none absolute -bottom-10 -right-10 z-0 h-64 w-64 rotate-12 text-[#4b857a] opacity-10 sm:-bottom-16 sm:-right-16 sm:h-[400px] sm:w-[400px] lg:-bottom-24 lg:-right-24 lg:h-[600px] lg:w-[600px]" 
        viewBox="0 0 100 100" 
        xmlns="http://www.w3.org/2000/svg"
      >
        <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-size="90" font-weight="900" font-style="italic" fill="currentColor">
          ?
        </text>
      </svg>


      <!-- ========================================== -->
      <!-- KONTEN UTAMA FAQ                           -->
      <!-- ========================================== -->
      <!-- relative & z-10 agar selalu ada di atas ornamen tanda tanya -->
      <div class="relative z-10 mx-auto w-full max-w-4xl px-4 sm:px-6 md:px-10">
        
        <!-- Header -->
        <div class="mb-12 text-center sm:mb-16">
          <h1 class="mb-4 text-3xl font-extrabold tracking-tight sm:text-4xl md:text-[40px]">
            Frequently Asked Question (FAQ)
          </h1>
          <p class="mx-auto max-w-2xl text-[14.5px] font-medium leading-relaxed text-[#5c6b89] sm:text-base">
            Di halaman ini, kami telah merangkum berbagai pertanyaan yang paling sering ditanyakan beserta jawaban lengkapnya.
          </p>
        </div>

        <!-- List Accordion -->
        <div class="flex flex-col gap-10">
          
          <div v-for="section in faqs" :key="section.category">
            
            <h2 class="mb-5 text-xl font-extrabold text-[#152c5b] sm:text-[22px]">
              {{ section.category }}
            </h2>

            <!-- Wadah Accordion dengan efek semi transparan (backdrop-blur) -->
            <div class="flex flex-col divide-y divide-gray-200 overflow-hidden rounded-2xl border border-gray-200 bg-white/95 shadow-sm backdrop-blur-md transition-shadow hover:shadow-md">
              
              <div v-for="item in section.items" :key="item.id" class="flex flex-col">
                
                <!-- Tombol Pertanyaan -->
                <button 
                  @click="toggleItem(item.id)" 
                  class="flex w-full items-center justify-between px-5 py-4 text-left transition-colors hover:bg-gray-50/80 focus:outline-none sm:px-6 sm:py-5"
                >
                  <span class="pr-4 text-[14.5px] font-bold text-[#152c5b] sm:text-[15.5px]">
                    {{ item.question }}
                  </span>
                  
                  <svg 
                    :class="['h-5 w-5 shrink-0 text-[#152c5b] transition-transform duration-300', openItems.includes(item.id) ? 'rotate-180' : 'rotate-0']" 
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>

                <!-- Panel Jawaban (Animasi Slide Down) -->
                <div 
                  class="grid transition-all duration-300 ease-in-out"
                  :class="openItems.includes(item.id) ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
                >
                  <div class="overflow-hidden">
                    <div class="px-5 pb-5 text-[14px] leading-relaxed text-[#5c6b89] sm:px-6 sm:pb-6 sm:text-[14.5px]">
                      {{ item.answer }}
                    </div>
                  </div>
                </div>

              </div>
              
            </div> 
            
          </div>
          
        </div>

      </div>
    </div>
  </MainLayout>
</template>
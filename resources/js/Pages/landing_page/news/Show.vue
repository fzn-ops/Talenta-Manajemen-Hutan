<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';

// ==========================================
// PROPS DARI BACKEND LARAVEL
// ==========================================
const props = defineProps({
  // 1. Data Berita Utama yang sedang dibaca
  news: {
    type: Object,
    default: () => ({
      id: 1,
      title: 'Lorem Ipsum Dolor',
      date: '11 November 2029',
      image: 'https://images.unsplash.com/photo-1511497584788-876760111969?q=80&w=1000&auto=format&fit=crop', // Gambar dummy hutan/grup
      paragraphs: [
        'Lorem ipsum dolor sit amet, cupidatat eiusmod duis ut. Magna dolore dolor ex elit sed non cillum do aliqua adipiscing ad. Ullamco fugiat occaecat proident dolore incididunt eu pariatur officia. Exercitation eiusmod sunt adipiscing pariatur est nulla tempor enim voluptate laborum pariatur. Dolore velit occaecat aliqua sint ullamco dolor exercitation Fauzan Cikarang.',
        'Commodo in non exercitation nulla enim qui aliquip nulla. Esse adipiscing ex anim fugiat labore mollit mollit. Id dolore ea minim enim ut laborum magna. Aku Fauzan liqua ut nulla consequat sunt enim laboris voluptate quis. Non dolore elit reprehenderit dolore laboris laboris mollit incididunt eu in Fauzan Fuadiansyah. Velit quis aliqua sed dolore excepteur irure cillum labore duis. Sed fugiat officia ad reprehenderit excepteur laborum cillum laborum reprehenderit nostrud. Commodo laboris sed cupidatat cillum aute fugiat veniam ex in eiusmod. Adipiscing nisi elit exercitation ea id ullamco eu quis. Mollit reprehenderit duis ut ut deserunt magna esse excepteur pariatur nisi. Sed tempor anim dolore anim excepteur eu nisi labore.',
        'Lorem ipsum dolor sit amet, cupidatat eiusmod duis ut. Magna dolore dolor ex elit sed non cillum do aliqua adipiscing ad. Ullamco fugiat occaecat proident dolore incididunt eu pariatur officia. Exercitation eiusmod sunt adipiscing pariatur est nulla tempor enim voluptate laborum pariatur. Dolore velit occaecat aliqua sint ullamco dolor exercitation Fauzan Cikarang.'
      ]
    })
  },
  // 2. Data Berita Lainnya (Sidebar Kanan)
  relatedNews: {
    type: Array,
    default: () => [
      {
        id: 2,
        title: 'Pelatihan Lifeskill Survive di Hutan, Fahutan IPB',
        date: '10 Juni 2029',
        image: 'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=400&auto=format&fit=crop'
      },
      {
        id: 3,
        title: 'Pelatihan Lifeskill Survive di Hutan, Fahutan IPB',
        date: '10 Juni 2029',
        image: 'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=400&auto=format&fit=crop'
      },
      {
        id: 4,
        title: 'Pelatihan Lifeskill Survive di Hutan, Fahutan IPB',
        date: '10 Juni 2029',
        image: 'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=400&auto=format&fit=crop'
      }
    ]
  }
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] pt-12 pb-16 font-sans text-[#152c5b] lg:pt-24 lg:pb-24">
      
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10">
        
        <!-- ========================================== -->
        <!-- BREADCRUMB NAVIGATION                      -->
        <!-- ========================================== -->
        <nav class="mb-8 flex items-center text-sm font-bold sm:mb-10 sm:text-base">
          <Link href="/news" class="text-[#152c5b] transition-colors hover:text-gray-600">
            Berita
          </Link>
          <span class="mx-2 text-gray-400">/</span>
          <!-- Truncate agar kalau judul panjang tidak merusak layout -->
          <span class="text-[#4b857a] underline decoration-2 underline-offset-4 max-w-[200px] sm:max-w-xs truncate">
            {{ news.title }}...
          </span>
        </nav>

        <!-- ========================================== -->
        <!-- MAIN GRID LAYOUT                           -->
        <!-- ========================================== -->
        <!-- lg:grid-cols-12 membagi layar jadi 12 bagian di Desktop -->
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-10 xl:gap-16">
          
          <!-- ========================================== -->
          <!-- KIRI: KONTEN BERITA (Mengambil 8 kolom)    -->
          <!-- ========================================== -->
          <article class="flex flex-col lg:col-span-8">
            
            <!-- Judul & Tanggal -->
            <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-[#152c5b] sm:text-4xl md:text-[42px] leading-tight">
              {{ news.title }}
            </h1>
            <p class="mb-8 text-sm font-semibold text-gray-500 sm:text-[15px]">
              {{ news.date }}
            </p>

            <!-- Gambar Utama (Full width di kolomnya) -->
            <div class="mb-8 w-full overflow-hidden rounded-2xl bg-[#d9d9d9] aspect-[16/9] sm:aspect-[21/9] lg:aspect-[16/9]">
              <img 
                v-if="news.image" 
                :src="news.image" 
                :alt="news.title" 
                class="h-full w-full object-cover"
              />
            </div>

            <!-- Isi Paragraf Berita -->
            <div class="space-y-6 text-[15px] leading-relaxed text-[#5c6b89] sm:text-base text-justify">
              <p v-for="(paragraph, index) in news.paragraphs" :key="index">
                {{ paragraph }}
              </p>
            </div>
            
          </article>


          <!-- ========================================== -->
          <!-- KANAN: BERITA LAINNYA (Mengambil 4 kolom)  -->
          <!-- ========================================== -->
          <aside class="flex flex-col lg:col-span-4">
            
            <!-- Menggunakan sticky agar sidebar ikut turun saat di-scroll di desktop -->
            <div class="sticky top-32">
              <h2 class="mb-6 text-xl font-extrabold text-[#152c5b] sm:text-2xl">
                Berita Lainnya
              </h2>
              
              <div class="flex flex-col gap-4">
                
                <!-- Horizontal Cards (Gambar Kiri, Teks Kanan) -->
                <Link 
                  v-for="(item, index) in relatedNews" 
                  :key="index"
                  :href="`/news/${item.id}`" 
                  class="group flex overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                  <!-- Gambar Thumbnail (1/3 Lebar Kartu) -->
                  <div class="w-1/3 shrink-0 overflow-hidden bg-[#d9d9d9] aspect-[4/3]">
                    <img 
                      v-if="item.image" 
                      :src="item.image" 
                      :alt="item.title" 
                      class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                    />
                  </div>
                  
                  <!-- Teks Thumbnail (2/3 Lebar Kartu) -->
                  <div class="flex flex-col justify-center p-3 sm:p-4">
                    <span class="mb-1 text-[10px] font-extrabold tracking-wide text-gray-400 sm:text-[11px]">
                      {{ item.date }}
                    </span>
                    <h3 class="text-[13px] font-bold leading-snug text-[#152c5b] transition-colors group-hover:text-[#4b857a] sm:text-sm line-clamp-3">
                      {{ item.title }}
                    </h3>
                  </div>
                </Link>

              </div>
            </div>

          </aside>

        </div>

      </div>
    </div>
  </MainLayout>
</template>
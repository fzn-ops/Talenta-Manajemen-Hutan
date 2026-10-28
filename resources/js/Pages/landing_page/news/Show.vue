<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';

// ==========================================
// PROPS DARI BACKEND LARAVEL
// ==========================================
const props = defineProps({
  beritaDetail: {
    type: Object,
    required: true,
  },
  relatedNews: {
    type: Array,
    default: () => []
  }
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return path.startsWith('/') ? path : `/${path}`;
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] pt-12 pb-16 font-poppins text-[#152c5b] lg:pt-24 lg:pb-24">
      
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
          <span class="text-[#4b857a] underline decoration-2 underline-offset-4 max-w-[200px] sm:max-w-xs truncate" :title="beritaDetail?.judul">
            {{ beritaDetail?.judul }}
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
          <article class="flex flex-col lg:col-span-8 min-w-0">
            
            <!-- Judul & Tanggal -->
            <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-[#152c5b] sm:text-4xl md:text-[42px] leading-tight">
              {{ beritaDetail?.judul }}
            </h1>
            <p class="mb-8 text-sm font-semibold text-gray-500 sm:text-[15px]">
              {{ formatDate(beritaDetail?.created_at) }}
            </p>

            <!-- Gambar Utama (Full width di kolomnya) -->
            <div v-if="beritaDetail?.foto" class="mb-8 w-full overflow-hidden rounded-2xl bg-[#d9d9d9] aspect-[16/9] sm:aspect-[21/9] lg:aspect-[16/9] border border-gray-100 shadow-xs">
              <img 
                :src="getImageUrl(beritaDetail.foto)" 
                :alt="beritaDetail?.judul" 
                class="h-full w-full object-cover"
              />
            </div>

            <!-- Isi Paragraf Berita (Render Rich Text HTML persis seperti ActivityRegister) -->
            <div 
              class="w-full text-[15px] sm:text-base leading-relaxed text-[#475569] rich-text-content"
              v-html="beritaDetail?.deskripsi || '<p>Tidak ada isi berita.</p>'"
            ></div>
            
          </article>


          <!-- ========================================== -->
          <!-- KANAN: BERITA LAINNYA (Mengambil 4 kolom)  -->
          <!-- ========================================== -->
          <aside class="flex flex-col lg:col-span-4 min-w-0">
            
            <!-- Menggunakan sticky agar sidebar ikut turun saat di-scroll di desktop -->
            <div class="sticky top-32">
              <h2 class="mb-6 text-xl font-extrabold text-[#152c5b] sm:text-2xl">
                Berita Lainnya
              </h2>
              
              <div v-if="relatedNews && relatedNews.length > 0" class="flex flex-col gap-4">
                
                <!-- Horizontal Cards (Gambar Kiri, Teks Kanan) -->
                <Link 
                  v-for="(item, index) in relatedNews" 
                  :key="item.id || index"
                  :href="route('news.detail', item.id)" 
                  class="group flex overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                  <!-- Gambar Thumbnail (1/3 Lebar Kartu) -->
                  <div class="w-1/3 shrink-0 overflow-hidden bg-[#d9d9d9] aspect-[4/3]">
                    <img 
                      v-if="item.foto" 
                      :src="getImageUrl(item.foto)" 
                      :alt="item.judul" 
                      class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                    />
                  </div>
                  
                  <div class="flex flex-col justify-center p-3 sm:p-4 min-w-0">
                    <span class="mb-1 text-[10px] font-extrabold tracking-wide text-gray-400 sm:text-[11px]">
                      {{ formatDate(item.created_at) }}
                    </span>
                    <h3 class="text-[13px] font-bold leading-snug text-[#152c5b] transition-colors group-hover:text-[#4b857a] sm:text-sm line-clamp-3">
                      {{ item.judul }}
                    </h3>
                  </div>
                </Link>

              </div>

              <div v-else class="text-sm text-gray-400 italic">
                Belum ada berita lainnya.
              </div>
            </div>

          </aside>

        </div>

      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.rich-text-content {
  word-break: break-word;
}

.rich-text-content :deep(h1) {
  display: block !important;
  font-size: 1.65rem !important; /* ~26px */
  font-weight: 700 !important;
  color: #183669 !important;
  margin-top: 1.25rem !important;
  margin-bottom: 0.6rem !important;
  line-height: 1.3 !important;
}

.rich-text-content :deep(h2) {
  display: block !important;
  font-size: 1.35rem !important; /* ~22px */
  font-weight: 700 !important;
  color: #183669 !important;
  margin-top: 1.1rem !important;
  margin-bottom: 0.5rem !important;
  line-height: 1.35 !important;
}

.rich-text-content :deep(h3) {
  display: block !important;
  font-size: 1.15rem !important; /* ~18px */
  font-weight: 600 !important;
  color: #183669 !important;
  margin-top: 0.95rem !important;
  margin-bottom: 0.4rem !important;
  line-height: 1.4 !important;
}

.rich-text-content :deep(p),
.rich-text-content :deep(div) {
  margin: 0.5rem 0 !important;
  line-height: 1.75 !important;
}

.rich-text-content :deep(b),
.rich-text-content :deep(strong),
.rich-text-content :deep([style*="font-weight: bold"]),
.rich-text-content :deep([style*="font-weight: 700"]),
.rich-text-content :deep([style*="font-weight:bold"]) {
  font-weight: 700 !important;
  color: #0f2347 !important;
}

.rich-text-content :deep(i),
.rich-text-content :deep(em) {
  font-style: italic !important;
}

.rich-text-content :deep(u) {
  text-decoration: underline !important;
}

.rich-text-content :deep(s),
.rich-text-content :deep(strike) {
  text-decoration: line-through !important;
}

.rich-text-content :deep(ul) {
  display: block !important;
  list-style-type: disc !important;
  padding-left: 1.75rem !important;
  margin: 0.65rem 0 !important;
}

.rich-text-content :deep(ol) {
  display: block !important;
  list-style-type: decimal !important;
  padding-left: 1.75rem !important;
  margin: 0.65rem 0 !important;
}

.rich-text-content :deep(li) {
  display: list-item !important;
  margin: 0.25rem 0 !important;
}

.rich-text-content :deep(a) {
  color: #2563eb !important;
  text-decoration: underline !important;
  font-weight: 500 !important;
  word-break: break-all !important;
  transition: color 0.15s ease;
}

.rich-text-content :deep(a:hover) {
  color: #1d4ed8 !important;
}

.rich-text-content :deep(blockquote) {
  display: block !important;
  border-left: 3.5px solid #183669 !important;
  background: #f1f5f9 !important;
  padding: 0.65rem 1.15rem !important;
  margin: 0.85rem 0 !important;
  color: #334155 !important;
  font-style: italic !important;
  border-radius: 0 8px 8px 0 !important;
}

.rich-text-content :deep(code) {
  background: #e2e8f0 !important;
  color: #b91c1c !important;
  padding: 0.15rem 0.35rem !important;
  border-radius: 4px !important;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
  font-size: 0.875em !important;
}

.rich-text-content :deep(hr) {
  border: 0 !important;
  height: 1px !important;
  background: #cbd5e1 !important;
  margin: 1.25rem 0 !important;
}
</style>
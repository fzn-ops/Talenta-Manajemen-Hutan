<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import CardSkeleton from '@/Components/dashboard/CardSkeleton.vue';
import CardImage from '@/Components/dashboard/CardImage.vue';

const tabs = ['Semua', 'Umum', 'Lomba'];

const props = defineProps({
    data: {
        type: Object,
        required: true,
        default: () => ({ data: [] })
    },
    currentJenis: {
        type: String,
        default: 'Semua'
    }
});

const activeTab = ref(props.currentJenis);
const isLoading = ref(false);

const filterCategory = (tab) => {
    if (activeTab.value === tab || isLoading.value) return;
    activeTab.value = tab;
    isLoading.value = true;
    
    router.get('/', { 
        jenis: tab === 'Semua' ? undefined : tab
    }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const formatTanggal = (dateString) => {
  if (!dateString || dateString === '-') return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  try {
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('id-ID', options);
  } catch {
    return dateString;
  }
};

const formatCardDescription = (html) => {
  if (!html) return '';
  let text = String(html).replace(/<\/?(p|div|h[1-6]|ul|ol|li|blockquote|br)[^>]*>/gi, ' ');
  text = text.replace(/<a\b[^>]*>(.*?)<\/a>/gi, '$1');
  text = text.replace(/<(?!\/?(b|strong|i|em|u|s|span)\b)[^>]+>/gi, '');
  return text.replace(/\s+/g, ' ').trim();
};

const getCategoryChipClass = (cat) => {
  switch (cat) {
    case 'Profesional': return 'bg-indigo-50 text-indigo-700 border-indigo-200';
    case 'Bisnis': return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'Birokrat': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'Akademisi': return 'bg-purple-50 text-purple-700 border-purple-200';
    default: return 'bg-slate-100 text-slate-700 border-slate-200';
  }
};

const featured = computed(() => {
    return props.data.data && props.data.data.length > 0 ? props.data.data[0] : null;
});

const cards = computed(() => {
    return props.data.data && props.data.data.length > 1 ? props.data.data.slice(1) : [];
});

const getImageUrl = (path) => {
    if (!path) {
        return 'https://ui-avatars.com/api/?name=Activity&background=random'; 
    }
    
    if (typeof path === 'string' && (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:'))) {
        return path;
    }
    const clean = String(path).replace(/^\/?(public\/|storage\/)+/, '');
    return `/storage/${clean}`;
};
</script>


<template>
  <div class="min-h-screen bg-[#fcfcfb] py-16 px-4 relative overflow-hidden font-poppins">
    
    <!-- Ornamen Background (Opsional, untuk meniru coretan dan bintang) -->
    <div class="absolute top-10 left-10 text-slate-300 w-32 h-32 border-l-2 border-t-2 border-dashed rounded-tl-full opacity-50"></div>
    <div class="absolute bottom-10 right-10 text-slate-300 w-48 h-48 border-r-2 border-b-2 border-dashed rounded-br-full opacity-50"></div>
    
    <div class="max-w-7xl mx-auto relative z-10">
      
      <!-- Bagian Header & Tabs -->
      <div class="flex flex-col items-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#17334F] mb-6">Kegiatan Terkini</h1>
        
        <div class="flex gap-6 sm:gap-8 border-b-2 border-transparent">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            :disabled="isLoading"
            @click="filterCategory(tab)"
            class="pb-2 text-sm sm:text-base font-semibold transition-all select-none"
            :class="[
              activeTab === tab ? 'text-[#183669] border-b-2 border-[#183669]' : 'text-slate-500 hover:text-[#183669]',
              isLoading ? 'cursor-wait opacity-75' : 'cursor-pointer'
            ]"
          >
            {{ tab }}
          </button>
        </div>
      </div>

      <!-- Container Konten dengan Loading State Skeleton -->
      <div class="relative min-h-[500px]">
        <!-- SKELETON LOADING -->
        <CardSkeleton 
          v-if="isLoading" 
          type="activity-featured-layout" 
          :count="props.data.data?.length || 7" 
        />

        <!-- Empty State jika tidak ada data -->
        <div 
          v-else-if="!featured" 
          class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-2xl border border-dashed border-[#d6e0ee] bg-white shadow-2xs"
        >
          <img src="/assets/icons/notfound.svg" alt="Data Tidak Ditemukan" class="h-16 w-16 mb-4" />
          <h3 class="font-poppins text-lg font-bold text-[#17334F] mb-1">Belum Ada Aktivitas</h3>
          <p class="font-inter text-sm text-slate-500 max-w-md">
            Tidak ada aktivitas yang ditemukan untuk kategori ini saat ini.
          </p>
        </div>

        <!-- Grid Konten Utama -->
        <div v-else class="flex flex-col lg:flex-row gap-6">
          <!-- Featured Card Besar di Kiri (1/3) -->
          <div class="lg:w-1/3">
            <Link 
              :href="route('activity.detail', featured.id)" 
              class="group relative flex flex-col justify-end overflow-hidden rounded-[14px] sm:rounded-[16px] shadow-sm transition-[transform,box-shadow] duration-300 ease-out h-full min-h-[500px] w-full border border-[#d6e0ee] bg-[#1e293b] sm:hover:-translate-y-1.5 sm:hover:shadow-lg cursor-pointer select-none will-change-transform transform-gpu backface-hidden isolate"
            >
              <!-- Gambar Background -->
              <CardImage 
                :src="featured?.gambar_aktivitas?.find(g => g.utama == 1 || g.utama === true)?.gambar || featured.gambar_aktivitas?.[0]?.gambar" 
                :alt="featured?.judul" 
                class="absolute inset-0 h-full w-full"
                img-class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
                dark
              />

              <!-- Overlay Gradient Hijau Sesuai Activities.vue -->
              <div class="absolute inset-0 bg-gradient-to-t from-[#091e1b] via-[#133830]/80 to-transparent opacity-95 pointer-events-none"></div>

              <!-- Top Badges (Umum/Lomba & Partisipan) -->
              <div class="absolute inset-x-0 top-0 p-4 sm:p-5 flex items-center justify-between w-full pointer-events-none z-20">
                <!-- Badge Jenis -->
                <div 
                  :class="[
                    'inline-flex h-[26px] sm:h-[28px] items-center justify-center rounded-full px-3 text-[11px] sm:text-[11.5px] font-bold uppercase tracking-wider shadow-xs select-none antialiased leading-none transform-gpu backface-hidden',
                    String(featured?.jenis || '').toLowerCase() === 'lomba' 
                      ? 'bg-amber-500 text-white border border-amber-400/40'  
                      : 'bg-blue-600 text-white border border-blue-400/30'  
                  ]"
                >
                  <span>{{ String(featured?.jenis || '').toLowerCase() === 'lomba' ? 'Lomba' : 'Umum' }}</span>
                </div>

                <!-- Badge Partisipan -->
                <div class="inline-flex h-[26px] sm:h-[28px] items-center justify-center gap-1.5 rounded-full bg-white/95 px-3 text-[11px] sm:text-[11.5px] font-bold text-[#183669] border border-slate-200/90 shadow-xs backdrop-blur-sm select-none antialiased leading-none transform-gpu backface-hidden">
                  <svg class="h-3.5 w-3.5 text-[#183669] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                  </svg>
                  <span>{{ featured?.partisipan || 0 }}</span>
                </div>
              </div>

              <!-- Konten Bawah -->
              <div class="relative z-10 p-5 sm:p-6 flex flex-col justify-end">
                <div class="mb-1.5 flex items-center gap-1.5 text-[11px] sm:text-[12px] font-semibold text-white">
                  <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span>Deadline: {{ formatTanggal(featured?.deadline) }}</span>
                </div>

                <h3 class="mb-2 font-poppins text-xl sm:text-2xl font-bold leading-tight text-white line-clamp-2">
                  {{ featured?.judul }}
                </h3>

                <p 
                  class="mb-4 font-inter text-[12.5px] sm:text-[13px] leading-relaxed text-slate-200/90 line-clamp-3 sm:line-clamp-4"
                  v-html="formatCardDescription(featured?.deskripsi)"
                ></p>

                <!-- Tags / Kategori -->
                <div class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="tag in featured?.kategori" 
                    :key="tag" 
                    :class="[
                      'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[10.5px] font-semibold border shadow-xs',
                      getCategoryChipClass(tag)
                    ]"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </Link>
          </div>

          <!-- Kolom Kanan: Grid Kartu Kecil (2/3) -->
          <div class="lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link 
              v-for="card in cards"
              :key="card.id" 
              :href="route('activity.detail', card.id)"  
              class="group relative flex flex-col justify-end overflow-hidden rounded-[14px] shadow-sm transition-[transform,box-shadow] duration-300 ease-out h-[280px] w-full border border-[#d6e0ee] bg-[#1e293b] sm:hover:-translate-y-1.5 sm:hover:shadow-lg cursor-pointer select-none will-change-transform transform-gpu backface-hidden isolate"
            >
              <!-- Gambar Background -->
              <CardImage 
                :src="card?.gambar_aktivitas?.find(g => g.utama == 1 || g.utama === true)?.gambar || card.gambar_aktivitas?.[0]?.gambar" 
                :alt="card?.judul" 
                class="absolute inset-0 h-full w-full"
                img-class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
                dark
              />
              
              <!-- Overlay Gradient Hijau Sesuai Activities.vue -->
              <div class="absolute inset-0 bg-gradient-to-t from-[#091e1b] via-[#133830]/80 to-transparent opacity-95 pointer-events-none"></div>
              
              <!-- Top Badges -->
              <div class="absolute inset-x-0 top-0 p-3.5 flex items-center justify-between w-full pointer-events-none z-20">
                <div 
                  :class="[
                    'inline-flex h-[24px] items-center justify-center rounded-full px-2.5 text-[10.5px] font-bold uppercase tracking-wider shadow-xs select-none antialiased leading-none transform-gpu backface-hidden',
                    String(card?.jenis || '').toLowerCase() === 'lomba' 
                      ? 'bg-amber-500 text-white border border-amber-400/40'  
                      : 'bg-blue-600 text-white border border-blue-400/30'
                  ]"
                >
                  <span>{{ String(card?.jenis || '').toLowerCase() === 'lomba' ? 'Lomba' : 'Umum' }}</span>
                </div>

                <div class="inline-flex h-[24px] items-center justify-center gap-1.5 rounded-full bg-white/95 px-2.5 text-[10.5px] font-bold text-[#183669] border border-slate-200/90 shadow-xs backdrop-blur-sm select-none antialiased leading-none transform-gpu backface-hidden">
                  <svg class="h-3.5 w-3.5 text-[#183669] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                  </svg>
                  <span>{{ card?.partisipan || 0 }}</span>
                </div>
              </div>
                
              <!-- Konten Teks Bawah -->
              <div class="relative z-10 p-4 flex flex-col justify-end">
                <div class="mb-1 flex items-center gap-1.5 text-[10.5px] font-semibold text-white">
                  <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span>Deadline: {{ formatTanggal(card?.deadline) }}</span>
                </div>

                <h3 class="mb-1 font-poppins text-[15px] font-bold leading-snug text-white line-clamp-1">
                  {{ card?.judul }}
                </h3>

                <p 
                  class="mb-2.5 font-inter text-[11px] leading-relaxed line-clamp-2 text-slate-200/90"
                  v-html="formatCardDescription(card?.deskripsi)"
                ></p>
                
                <!-- Tags -->
                <div class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="tag in card?.kategori" 
                    :key="tag" 
                    :class="[
                      'inline-flex items-center justify-center rounded-full px-2 py-0.5 font-inter text-[9.5px] font-semibold border shadow-xs',
                      getCategoryChipClass(tag)
                    ]"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </div>

      <!-- Tombol Lebih Banyak -->
      <div class="flex justify-center mt-12">
        <Link 
          :href="route('activities')"
          class="bg-[#183669] text-white px-7 py-3 rounded-full flex items-center gap-2 text-sm font-semibold hover:bg-[#122b54] transition-colors shadow-md select-none"
        >
          Lebih Banyak
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </Link>
      </div>

    </div>
  </div>
</template>

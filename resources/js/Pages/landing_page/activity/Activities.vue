<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';

// ==========================================
// 1. STATE UNTUK BACKEND (FILTER, SEARCH, SORT)
// ==========================================
const props = defineProps({
  filters: Object, 
  // Nanti dari Laravel terima props pagination begini: 
  // activities: Object 
});

const activeCategory = ref(props.filters?.category || 'Semua');
const searchQuery = ref(props.filters?.search || '');
const activeSort = ref(props.filters?.sort || 'terbaru'); 

const categories = ['Semua', 'Kegiatan', 'Lomba'];
const isFilterOpen = ref(false); 

// ==========================================
// 2. LOGIC FILTERING KE BACKEND
// ==========================================
const applyFilters = () => {
  router.get('/activities', {
    search: searchQuery.value,
    category: activeCategory.value === 'Semua' ? null : activeCategory.value,
    sort: activeSort.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true 
  });
};

const setCategory = (cat) => {
  activeCategory.value = cat;
  applyFilters();
};

const setSort = (sortValue) => {
  activeSort.value = sortValue;
  isFilterOpen.value = false;
  applyFilters();
};

let searchTimeout;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
});

// ==========================================
// 3. DATA MOCKUP (MENSIMULASIKAN LARAVEL PAGINATION)
// ==========================================
const activities = ref({
  data: Array(8).fill({
    title: 'First Meet MNH 63',
    deadline: '21 Agustus 2029',
    description: 'Temukan berbagai pengalaman menarik dan tingkatkan kapasitas diri melalui kegiatan ini. Bergabunglah bersama kami untuk melatih talenta...',
    participants: 67,
    image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop', 
    tags: [
      { name: 'Profesional', colorClass: 'text-[#4b857a]' },
      { name: 'Bisnis', colorClass: 'text-yellow-600' },
      { name: 'Birokrat', colorClass: 'text-red-600' },
      { name: 'Akademisi', colorClass: 'text-blue-600' }
    ]
  }),
  links: [
    { url: null, label: '&laquo; Sebelumnya', active: false },
    { url: '/activities?page=1', label: '1', active: true },
    { url: '/activities?page=2', label: '2', active: false },
    { url: '/activities?page=3', label: '3', active: false },
    { url: '/activities?page=2', label: 'Selanjutnya &raquo;', active: false },
  ]
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] py-12 font-sans text-[#152c5b] lg:py-16 mt-10">
      
      <div class="mx-auto flex w-full max-w-7xl flex-col px-4 sm:px-6 md:px-10">
        
        <!-- HEADER -->
        <div class="mb-6 sm:mb-8">
          <h1 class="mb-2 text-2xl font-extrabold tracking-tight sm:text-4xl md:text-[40px]">
            Kumpulan Aktivitas
          </h1>
          <p class="text-sm font-medium text-gray-500 sm:text-base">
            Yuk temukan aktivitas yang cocok dengan talenta kamu disini!
          </p>
        </div>

        <!-- SEARCH & FILTER BAR (Diubah jadi flex-row biar sejajar di mobile) -->
        <div class="mb-6 flex w-full flex-row items-center gap-2 sm:gap-3">
          
          <!-- Search Input -->
          <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4">
              <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Cari Aktivitas disini" 
              class="w-full rounded-xl border border-gray-300 py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition-colors focus:border-[#152c5b] focus:ring-1 focus:ring-[#152c5b]"
            >
          </div>

          <!-- Filter Button -->
          <div class="relative shrink-0">
            <button 
              @click="isFilterOpen = !isFilterOpen"
              class="flex h-[42px] w-[42px] sm:h-[46px] sm:w-[46px] items-center justify-center rounded-xl bg-[#4b857a] text-white transition-colors hover:bg-[#3a685e] shadow-sm"
            >
              <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
              </svg>
            </button>

            <!-- Overlay & Dropdown Filter -->
            <div v-if="isFilterOpen" @click="isFilterOpen = false" class="fixed inset-0 z-20"></div>
            <div v-if="isFilterOpen" class="absolute right-0 top-[50px] sm:top-[54px] z-30 w-48 rounded-xl border border-gray-100 bg-white p-2 shadow-lg">
              <div class="px-3 py-2 text-xs font-bold text-gray-400">Urutkan Berdasarkan</div>
              <button @click="setSort('terbaru')" :class="['w-full rounded-lg px-3 py-2 text-left text-sm font-semibold transition-colors', activeSort === 'terbaru' ? 'bg-gray-100 text-[#152c5b]' : 'text-gray-600 hover:bg-gray-50']">Terbaru</button>
              <button @click="setSort('terpopuler')" :class="['w-full rounded-lg px-3 py-2 text-left text-sm font-semibold transition-colors', activeSort === 'terpopuler' ? 'bg-gray-100 text-[#152c5b]' : 'text-gray-600 hover:bg-gray-50']">Terpopuler (Peserta)</button>
              <button @click="setSort('segera_berakhir')" :class="['w-full rounded-lg px-3 py-2 text-left text-sm font-semibold transition-colors', activeSort === 'segera_berakhir' ? 'bg-gray-100 text-[#152c5b]' : 'text-gray-600 hover:bg-gray-50']">Segera Berakhir</button>
            </div>
          </div>

        </div>

        <!-- CATEGORY TABS -->
        <div class="mb-8 sm:mb-10 flex flex-wrap gap-2 sm:gap-3">
          <button 
            v-for="cat in categories" 
            :key="cat"
            @click="setCategory(cat)"
            :class="[
              'rounded-full px-5 sm:px-6 py-1.5 sm:py-2 text-xs sm:text-sm font-bold transition-all duration-300 border',
              activeCategory === cat 
                ? 'bg-white text-[#152c5b] border-[#152c5b] shadow-sm' 
                : 'bg-transparent text-gray-500 border-gray-300 hover:border-[#152c5b] hover:text-[#152c5b]'
            ]"
          >
            {{ cat }}
          </button>
        </div>

        <!-- GRID KARTU AKTIVITAS -->
        <!-- Perbaikan Card: Set fixed height di mobile (h-[340px]), balik normal di sm ke atas -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:gap-6">
          
          <Link 
            v-for="(item, index) in activities.data" 
            :key="index"
            :href="route('activity.detail', index)"
            class="group relative flex h-[340px] sm:h-auto sm:aspect-[3/4.2] w-full flex-col justify-end overflow-hidden rounded-2xl bg-gray-200 shadow-sm transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl"
          >
            <img 
              :src="item.image" 
              :alt="item.title" 
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#1b3631] via-[#244b43]/80 to-transparent opacity-90"></div>

            <div class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold text-gray-700 shadow-sm backdrop-blur-sm">
              <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
              </svg>
              {{ item.participants }}
            </div>

            <div class="relative z-10 p-4 sm:p-5 text-white">
              <div class="mb-1.5 flex items-center gap-1.5 text-[10px] font-medium text-gray-200">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Deadline : {{ item.deadline }}
              </div>
              <h3 class="mb-1.5 text-base sm:text-lg font-extrabold leading-snug">{{ item.title }}</h3>
              <p class="mb-3 sm:mb-4 text-[10px] sm:text-xs leading-relaxed text-gray-300 line-clamp-2 sm:line-clamp-3">{{ item.description }}</p>
              
              <div class="flex flex-wrap gap-1.5">
                <span 
                  v-for="tag in item.tags" 
                  :key="tag.name"
                  :class="[tag.colorClass, 'bg-white rounded-full px-2 sm:px-2.5 py-0.5 text-[9px] sm:text-[10px] font-extrabold tracking-wide shadow-sm']"
                >
                  {{ tag.name }}
                </span>
              </div>
            </div>
          </Link>

        </div>

        <!-- ========================================== -->
        <!-- PAGINATION -->
        <!-- ========================================== -->
        <div v-if="activities.links && activities.links.length > 3" class="mt-10 sm:mt-12 flex flex-wrap items-center justify-center sm:justify-end gap-1.5 sm:gap-2">
          <template v-for="(link, index) in activities.links" :key="index">
            <div 
              v-if="link.url === null" 
              class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs sm:text-sm text-gray-400 cursor-not-allowed"
              v-html="link.label"
            ></div>
            <Link 
              v-else 
              :href="link.url" 
              preserve-scroll
              :class="[
                'rounded-lg px-3 py-2 text-xs sm:text-sm font-semibold transition-colors border',
                link.active 
                  ? 'bg-[#152c5b] text-white border-[#152c5b] shadow-sm' 
                  : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100 hover:text-[#152c5b]'
              ]"
              v-html="link.label" 
            />
          </template>
        </div>
        
      </div>
    </div>
  </MainLayout>
</template>
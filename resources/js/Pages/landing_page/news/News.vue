<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';

// ==========================================
// 1. PROPS DARI BACKEND LARAVEL
// ==========================================
const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  },
  news: {
    type: Object,
    default: () => ({
      data: Array(6).fill({
        id: 1,
        date: '10 Juni 2029',
        title: 'Lorem Ipsum Dolor Sit Amet',
        description: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit laborum consectetur ad laboris adipiscing nostrud in veniam anim. Et enim nostrud nisi Fauzan Fuadiansyah deserunt sunt eu sunt. Ut in aliquip in tempor consectetur deserunt culpa voluptate. Ad velit elit sed.....',
        image: 'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=800&auto=format&fit=crop' 
      }),
      links: [
        { url: null, label: '&laquo; Sebelumnya', active: false },
        { url: '/news?page=1', label: '1', active: true },
        { url: '/news?page=2', label: '2', active: false },
        { url: '/news?page=3', label: '3', active: false },
        { url: '/news?page=2', label: 'Selanjutnya &raquo;', active: false },
      ]
    })
  }
});

// ==========================================
// 2. STATE FORM (Search & Date Filter)
// ==========================================
const searchQuery = ref(props.filters.search ?? '');
const dateFilter = ref(props.filters.date ?? ''); 
const isFilterOpen = ref(false);

// ==========================================
// 3. LOGIC INERTIA (Hook ke Backend)
// ==========================================
const applyFilters = () => {
  const params = {};
  if (searchQuery.value) params.search = searchQuery.value;
  if (dateFilter.value) params.date = dateFilter.value;

  router.get('/news', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
};

const setDateFilter = (value) => {
  dateFilter.value = value;
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
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] py-12 font-sans text-[#152c5b] lg:py-16 mt-10 relative">
      
      <!-- Overlay transparan untuk klik-di-luar-tutup-dropdown -->
      <div v-if="isFilterOpen" @click="isFilterOpen = false" class="fixed inset-0 z-20"></div>

      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10 relative z-30">
        
        <!-- HEADER -->
        <div class="mb-8">
          <h1 class="mb-2 text-3xl font-extrabold tracking-tight sm:text-4xl md:text-[40px]">
            Kumpulan Berita
          </h1>
          <p class="text-sm font-medium text-gray-500 sm:text-base">
            Yuk baca berita terbaru tentang manajemen hutan!
          </p>
        </div>

        <!-- SEARCH & FILTER TANGGAL (Dijamin sejajar pakai flex-row) -->
        <div class="mb-10 flex w-full flex-row items-center gap-3">
          
          <!-- Input Search (Mengisi sisa ruang kiri) -->
          <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4">
              <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input 
              v-model="searchQuery"
              @keyup.enter="applyFilters"
              type="text" 
              placeholder="Cari Berita disini" 
              class="w-full rounded-xl border border-gray-300 py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition-colors focus:border-[#152c5b] focus:ring-1 focus:ring-[#152c5b]"
            >
          </div>

          <!-- Tombol Filter Tanggal (shrink-0 agar tidak kepotong) -->
          <div class="relative shrink-0">
            <button 
              @click="isFilterOpen = !isFilterOpen"
              class="flex h-[42px] w-[42px] sm:h-[46px] sm:w-[46px] items-center justify-center rounded-xl bg-[#4b857a] text-white transition-colors hover:bg-[#3a685e] shadow-sm"
            >
              <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
              </svg>
            </button>

            <!-- Dropdown Menu Filter Tanggal -->
            <transition
              enter-active-class="transition duration-200 ease-out"
              enter-from-class="opacity-0 -translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition duration-150 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-2"
            >
              <div v-if="isFilterOpen" class="absolute right-0 top-[60px] z-40 w-48 rounded-xl border border-gray-100 bg-white p-2 shadow-xl">
                <div class="px-3 py-2 text-[11px] font-extrabold uppercase tracking-wider text-gray-400">
                  Urutkan Berita
                </div>
                <button 
                  @click="setDateFilter('')" 
                  :class="['w-full rounded-lg px-3 py-2.5 text-left text-sm transition-colors', dateFilter === '' ? 'bg-gray-50 font-extrabold text-[#152c5b]' : 'font-medium text-gray-600 hover:bg-gray-50 hover:text-[#152c5b]']"
                >
                  Berita Terbaru
                </button>
                <button 
                  @click="setDateFilter('terlama')" 
                  :class="['w-full rounded-lg px-3 py-2.5 text-left text-sm transition-colors', dateFilter === 'terlama' ? 'bg-gray-50 font-extrabold text-[#152c5b]' : 'font-medium text-gray-600 hover:bg-gray-50 hover:text-[#152c5b]']"
                >
                  Berita Terlama
                </button>
              </div>
            </transition>
          </div>

        </div>

        <!-- GRID KARTU BERITA -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          
          <Link 
            v-for="(item, index) in news.data" 
            :key="index"
            :href="route('news.detail', item.id)" 
            class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
          >
            <div class="aspect-[3/2] w-full overflow-hidden bg-[#d9d9d9]">
              <img 
                v-if="item.image" 
                :src="item.image" 
                :alt="item.title" 
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
            </div>
            <div class="flex flex-col p-5 sm:p-6">
              <span class="mb-2 text-[11.5px] font-extrabold tracking-wide text-gray-400">
                {{ item.date }}
              </span>
              <h3 class="mb-2.5 text-lg font-extrabold leading-snug text-[#152c5b] transition-colors group-hover:text-[#4b857a]">
                {{ item.title }}
              </h3>
              <p class="text-[13px] leading-relaxed text-[#5c6b89] line-clamp-4">
                {{ item.description }}
              </p>
            </div>
          </Link>

        </div>

        <!-- PAGINATION -->
        <div v-if="news.links && news.links.length > 3" class="mt-12 flex flex-wrap items-center justify-end gap-1.5 sm:gap-2">
          <template v-for="(link, index) in news.links" :key="index">
            <div 
              v-if="link.url === null" 
              class="cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-400 sm:text-sm"
              v-html="link.label"
            ></div>
            <Link 
              v-else 
              :href="link.url" 
              preserve-scroll
              :class="[
                'rounded-lg border px-3 py-2 text-xs font-semibold transition-colors sm:text-sm',
                link.active 
                  ? 'border-[#152c5b] bg-[#152c5b] text-white shadow-sm' 
                  : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-[#152c5b]'
              ]"
              v-html="link.label" 
            />
          </template>
        </div>
        
      </div>
    </div>
  </MainLayout>
</template>
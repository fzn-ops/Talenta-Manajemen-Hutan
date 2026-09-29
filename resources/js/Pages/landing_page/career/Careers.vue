<script setup>
import { ref } from 'vue';
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
  posisiOptions: {
    type: Array,
    default: () => ['Internship', 'Full Time', 'Part Time', 'Freelance']
  },
  industriOptions: {
    type: Array,
    default: () => ['Teknologi', 'Pertanian', 'Manajemen', 'Kesehatan']
  },
  careers: {
    type: Object,
    default: () => ({
      data: Array(6).fill({
        id: 1,
        title: 'Carpenter Internship',
        company: 'PT Merah Putih',
        major: 'S1 Manajemen Hutan',
        address: 'Gedung Building, Jalan Street, Kota City, Provinsi 42067',
        deadline: '21 Agustus 2029',
        logo: '' 
      }),
      links: [
        { url: null, label: '&laquo; Sebelumnya', active: false },
        { url: '/careers?page=1', label: '1', active: true },
        { url: '/careers?page=2', label: '2', active: false },
        { url: '/careers?page=2', label: 'Selanjutnya &raquo;', active: false },
      ]
    })
  }
});

// ==========================================
// 2. STATE FORM (Sinkron dengan inputan UI)
// ==========================================
const search = ref(props.filters.search ?? '');
const category_posisi = ref(props.filters.category_posisi ?? '');
const bidang_industri = ref(props.filters.bidang_industri ?? '');
const sort = ref(props.filters.sort ?? '');

// ==========================================
// 3. STATE CUSTOM DROPDOWN
// ==========================================
const openDropdown = ref(null); // Menyimpan nama dropdown yang sedang terbuka

const toggleDropdown = (name) => {
  openDropdown.value = openDropdown.value === name ? null : name;
};

const selectFilter = (type, value) => {
  if (type === 'posisi') category_posisi.value = value;
  if (type === 'industri') bidang_industri.value = value;
  if (type === 'sort') sort.value = value;
  
  openDropdown.value = null; // Tutup menu setelah memilih
};

// ==========================================
// 4. LOGIC INERTIA (Hook ke Backend)
// ==========================================
const applyFilters = () => {
  const params = {};
  if (search.value) params.search = search.value;
  if (category_posisi.value) params.category_posisi = category_posisi.value;
  if (bidang_industri.value) params.bidang_industri = bidang_industri.value;
  if (sort.value) params.sort = sort.value;

  router.get('/careers', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true 
  });
};

const resetFilters = () => {
  search.value = '';
  category_posisi.value = '';
  bidang_industri.value = '';
  sort.value = '';
  router.get('/careers');
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] py-12 font-sans text-[#152c5b] lg:py-16 mt-10 relative">
      
      <!-- Overlay transparan untuk menutup dropdown kalau diklik di luar kotak -->
      <div v-if="openDropdown" @click="openDropdown = null" class="fixed inset-0 z-30"></div>

      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10 relative z-40">
        
        <!-- HEADER -->
        <div class="mb-8">
          <h1 class="mb-2 text-3xl font-extrabold tracking-tight sm:text-4xl md:text-[40px]">
            Kumpulan Karir
          </h1>
          <p class="text-sm font-medium text-gray-500 sm:text-base">
            Yuk temukan karir yang cocok dengan talenta kamu disini!
          </p>
        </div>

        <!-- SEARCH & FILTER FORM -->
        <div class="mb-10 flex flex-col gap-4">
          
          <!-- Search Bar -->
          <div class="relative w-full">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
              <svg class="h-5 w-5 text-[#152c5b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input 
              v-model="search"
              @keyup.enter="applyFilters"
              type="text" 
              placeholder="Cari Karir disini" 
              class="w-full rounded-xl border border-gray-200 bg-white py-3.5 pl-11 pr-4 text-sm font-medium text-gray-700 shadow-sm outline-none transition-colors focus:border-[#152c5b] focus:ring-1 focus:ring-[#152c5b]"
            >
          </div>

          <!-- Custom Dropdown Filters -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            
            <!-- 1. Dropdown Kategori Posisi -->
            <div class="relative">
              <div 
                @click="toggleDropdown('posisi')"
                class="flex w-full cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3.5 text-sm shadow-sm transition-colors hover:border-[#152c5b]"
              >
                <span :class="category_posisi ? 'font-bold text-[#152c5b]' : 'font-medium text-gray-500'">
                  {{ category_posisi || 'Pilih Kategori Posisi' }}
                </span>
                <svg 
                  :class="['h-4 w-4 text-[#152c5b] transition-transform duration-300', openDropdown === 'posisi' ? 'rotate-180' : '']" 
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              
              <!-- Menu Options -->
              <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
              >
                <div v-if="openDropdown === 'posisi'" class="absolute left-0 top-[110%] z-50 w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl">
                  <ul class="max-h-60 overflow-auto py-1">
                    <li @click="selectFilter('posisi', '')" class="cursor-pointer px-4 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#152c5b]">
                      Semua Posisi
                    </li>
                    <li 
                      v-for="opsi in posisiOptions" :key="opsi" 
                      @click="selectFilter('posisi', opsi)" 
                      :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 hover:text-[#152c5b]', category_posisi === opsi ? 'bg-gray-50 font-extrabold text-[#152c5b]' : 'font-medium text-gray-700']"
                    >
                      {{ opsi }}
                    </li>
                  </ul>
                </div>
              </transition>
            </div>

            <!-- 2. Dropdown Bidang Industri -->
            <div class="relative">
              <div 
                @click="toggleDropdown('industri')"
                class="flex w-full cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3.5 text-sm shadow-sm transition-colors hover:border-[#152c5b]"
              >
                <span :class="bidang_industri ? 'font-bold text-[#152c5b]' : 'font-medium text-gray-500'">
                  {{ bidang_industri || 'Pilih Bidang Industri' }}
                </span>
                <svg 
                  :class="['h-4 w-4 text-[#152c5b] transition-transform duration-300', openDropdown === 'industri' ? 'rotate-180' : '']" 
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              
              <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
              >
                <div v-if="openDropdown === 'industri'" class="absolute left-0 top-[110%] z-50 w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl">
                  <ul class="max-h-60 overflow-auto py-1">
                    <li @click="selectFilter('industri', '')" class="cursor-pointer px-4 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#152c5b]">
                      Semua Industri
                    </li>
                    <li 
                      v-for="opsi in industriOptions" :key="opsi" 
                      @click="selectFilter('industri', opsi)" 
                      :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 hover:text-[#152c5b]', bidang_industri === opsi ? 'bg-gray-50 font-extrabold text-[#152c5b]' : 'font-medium text-gray-700']"
                    >
                      {{ opsi }}
                    </li>
                  </ul>
                </div>
              </transition>
            </div>

            <!-- 3. Dropdown Sortir -->
            <div class="relative">
              <div 
                @click="toggleDropdown('sort')"
                class="flex w-full cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3.5 text-sm shadow-sm transition-colors hover:border-[#152c5b]"
              >
                <span :class="sort ? 'font-bold text-[#152c5b]' : 'font-medium text-gray-500'">
                  {{ sort === 'segera_berakhir' ? 'Segera Berakhir' : 'Upload Terbaru' }}
                </span>
                <svg 
                  :class="['h-4 w-4 text-[#152c5b] transition-transform duration-300', openDropdown === 'sort' ? 'rotate-180' : '']" 
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              
              <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
              >
                <div v-if="openDropdown === 'sort'" class="absolute left-0 top-[110%] z-50 w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl">
                  <ul class="max-h-60 overflow-auto py-1">
                    <li @click="selectFilter('sort', '')" :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 hover:text-[#152c5b]', sort === '' ? 'bg-gray-50 font-extrabold text-[#152c5b]' : 'font-medium text-gray-700']">Upload Terbaru</li>
                    <li @click="selectFilter('sort', 'segera_berakhir')" :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 hover:text-[#152c5b]', sort === 'segera_berakhir' ? 'bg-gray-50 font-extrabold text-[#152c5b]' : 'font-medium text-gray-700']">Segera Berakhir</li>
                  </ul>
                </div>
              </transition>
            </div>

          </div>

          <!-- Action Buttons -->
          <div class="mt-2 flex w-full justify-end gap-3 sm:gap-4">
            <button @click="resetFilters" type="button" class="w-full rounded-lg border border-gray-200 bg-white px-8 py-2.5 text-sm font-bold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 sm:w-auto">
              Reset
            </button>
            <button @click="applyFilters" type="button" class="w-full rounded-lg bg-[#152c5b] px-10 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-[#0f1f43] sm:w-auto">
              Cari
            </button>
          </div>

        </div>

        <!-- GRID KARTU KARIR (Tidak berubah) -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="(item, index) in careers.data" :key="index" class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-md">
            
            <div class="mb-5 flex items-start gap-4">
              <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-gray-50">
                <img v-if="item.logo" :src="item.logo" alt="Logo" class="h-full w-full object-cover p-1" />
              </div>
              <div class="flex flex-col">
                <h3 class="text-base font-extrabold leading-tight text-[#152c5b]">{{ item.title }}</h3>
                <p class="mt-1 text-[13px] font-semibold text-gray-600">{{ item.company }}</p>
                <p class="mt-0.5 text-[12px] font-medium text-gray-500">{{ item.major }}</p>
              </div>
            </div>

            <div class="mb-4 flex items-start gap-2.5">
              <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#152c5b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"></path>
              </svg>
              <p class="text-[12px] leading-snug text-gray-500 line-clamp-2">{{ item.address }}</p>
            </div>

            <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
              <div class="flex items-center gap-2 text-gray-500">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                </svg>
                <span class="text-[11px] font-semibold">Deadline : {{ item.deadline }}</span>
              </div>
              
              <Link :href="route('career.detail', item.id)" class="rounded-full bg-[#4b857a] px-6 py-1.5 text-[12px] font-bold text-white transition-colors hover:bg-[#3a685e]">
                Detail
              </Link>
            </div>
          </div>
        </div>

        <!-- PAGINATION -->
        <div v-if="careers.links && careers.links.length > 3" class="mt-12 flex flex-wrap items-center justify-end gap-1.5 sm:gap-2">
          <template v-for="(link, index) in careers.links" :key="index">
            <div v-if="link.url === null" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs sm:text-sm text-gray-400 cursor-not-allowed" v-html="link.label"></div>
            <Link v-else :href="link.url" preserve-scroll :class="['rounded-lg px-3 py-2 text-xs sm:text-sm font-semibold transition-colors border', link.active ? 'bg-[#152c5b] text-white border-[#152c5b] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 hover:text-[#152c5b]']" v-html="link.label" />
          </template>
        </div>
        
      </div>
    </div>
  </MainLayout>
</template>
<script setup>
import { computed, ref, watch, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import CardSkeleton from '@/Components/dashboard/CardSkeleton.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';

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
      data: [],
      links: [],
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 6
    })
  }
});

// Helper format tanggal
const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Helper strip HTML untuk cuplikan deskripsi di kartu
const stripHtml = (html) => {
  if (!html) return '';
  const tmp = document.createElement('DIV');
  tmp.innerHTML = html;
  return tmp.textContent || tmp.innerText || '';
};

// Helper foto
const getImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=800&auto=format&fit=crop';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return path.startsWith('/') ? path : `/${path}`;
};

// ==========================================
// 2. STATE FORM (Search, Date Filter, & Pagination)
// ==========================================
const searchQuery = ref(props.filters.search ?? '');
const dateFilter = ref(props.filters.date ?? ''); 
const showFilter = ref(false);
const isLoading = ref(false);

const currentPage = ref(props.news?.current_page || 1);
const rowsPerPage = ref(Number(props.filters?.per_page) || props.news?.per_page || 6);

watch(() => props.news?.current_page, (newPage) => {
  if (newPage && newPage !== currentPage.value) {
    currentPage.value = newPage;
  }
});

const skeletonCount = computed(() => {
  const currentCount = props.news?.data?.length || 0;
  return currentCount > 0 ? currentCount : rowsPerPage.value;
});

const isFilterActive = computed(() => {
  return dateFilter.value !== '';
});

// ==========================================
// 3. LOGIC INERTIA (Hook ke Backend)
// ==========================================
const applyFilters = (targetPage = null) => {
  isLoading.value = true;
  const pageNum = targetPage !== null ? targetPage : currentPage.value;

  const params = {};
  if (searchQuery.value) params.search = searchQuery.value;
  if (dateFilter.value) params.date = dateFilter.value;
  if (pageNum > 1) params.page = pageNum;
  if (rowsPerPage.value) params.per_page = rowsPerPage.value;

  router.get('/news', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const handleFilterChange = () => {
  if (currentPage.value !== 1) {
    currentPage.value = 1;
  } else {
    applyFilters(1);
  }
};

const setDateFilter = (value) => {
  dateFilter.value = value;
  showFilter.value = false;
  handleFilterChange();
};

const resetFilters = () => {
  dateFilter.value = '';
  showFilter.value = false;
  handleFilterChange();
};

watch([currentPage, rowsPerPage], ([newPage, newRows], [oldPage, oldRows]) => {
  if (newRows !== oldRows) {
    if (currentPage.value !== 1) {
      currentPage.value = 1;
      return;
    }
  }
  applyFilters();
});

let searchTimeout;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleFilterChange();
  }, 400);
});

onUnmounted(() => {
  if (searchTimeout) clearTimeout(searchTimeout);
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] py-12 font-poppins text-[#152c5b] lg:py-16 mt-6 sm:mt-10 relative">
      
      <!-- Overlay transparan untuk klik-di-luar-tutup-dropdown -->
      <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-20"></div>

      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10 relative z-30">
        
        <!-- HEADER -->
        <div class="mb-6 sm:mb-8">
          <h1 class="mb-2 text-2xl font-extrabold tracking-tight sm:text-4xl md:text-[40px] text-[#17334F]">
            Kumpulan Berita
          </h1>
          <p class="text-sm font-medium text-gray-500 sm:text-base">
            Yuk baca berita terbaru dari Talenta Manajemen Hutan!
          </p>
        </div>

        <!-- SEARCH & FILTER BAR (PERSIS SEPERTI DASHBOARD) -->
        <div class="mb-6 sm:mb-8 flex flex-row items-center gap-2 sm:gap-3 w-full">
          <!-- Search Input Component -->
          <SearchBarTable
            class="w-full flex-1 min-w-0"
            v-model="searchQuery"
            placeholder="Cari Berita disini..."
          />

          <!-- Filter Dropdown Container -->
          <div class="relative shrink-0">
            <button
              type="button"
              @click="showFilter = !showFilter"
              class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
              :class="showFilter || isFilterActive
                ? 'border-[#183669]'
                : 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
              title="Filter Berita"
            >
              <img
                src="/assets/icons/filter.svg"
                alt="Filter Icon"
                class="h-5 w-5 shrink-0 object-contain pointer-events-none"
              />
              <!-- Red active indicator dot -->
              <span
                v-if="isFilterActive"
                class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"
              ></span>
            </button>

            <!-- Dropdown Menu Filter -->
            <Transition
              enter-active-class="transition duration-150 ease-out"
              enter-from-class="transform scale-95 opacity-0 translate-y-1"
              enter-to-class="transform scale-100 opacity-100 translate-y-0"
              leave-active-class="transition duration-100 ease-in"
              leave-from-class="transform scale-100 opacity-100 translate-y-0"
              leave-to-class="transform scale-95 opacity-0 translate-y-1"
            >
              <div
                v-if="showFilter"
                class="absolute right-0 top-14 w-72 sm:w-80 rounded-[14px] border border-[#d6e0ee] bg-white p-4 shadow-2xl ring-1 ring-black/10 z-40 font-inter"
              >
                <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3">
                  <p class="font-poppins text-xs font-bold text-[#183669]">
                    Filter Berita
                  </p>
                  <button
                    v-if="isFilterActive"
                    type="button"
                    @click="resetFilters"
                    class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer"
                  >
                    Reset Semua
                  </button>
                </div>

                <div class="mb-4">
                  <div class="flex flex-wrap gap-1.5">
                    <button
                      type="button"
                      @click="setDateFilter('')"
                      :class="dateFilter === '' ? 'bg-[#183669] text-white border-[#183669]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-[#183669] hover:text-[#183669]'"
                      class="rounded-full px-3 py-1.5 text-[11px] font-semibold border cursor-pointer transition"
                    >
                      Terbaru
                    </button>
                    <button
                      type="button"
                      @click="setDateFilter('terlama')"
                      :class="dateFilter === 'terlama' ? 'bg-[#183669] text-white border-[#183669]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-[#183669] hover:text-[#183669]'"
                      class="rounded-full px-3 py-1.5 text-[11px] font-semibold border cursor-pointer transition"
                    >
                      Terlama
                    </button>
                  </div>
                </div>

                <div class="flex items-center justify-end border-t border-[#f0f4f9] pt-3">
                  <button 
                    type="button"
                    @click="showFilter = false" 
                    class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-[#122b54] cursor-pointer"
                  >
                    Tutup
                  </button>
                </div>
              </div>
            </Transition>
          </div>

        </div>

        <!-- SKELETON LOADING KARTU BERITA -->
        <CardSkeleton v-if="isLoading" type="news" :count="skeletonCount" />

        <!-- JIKA KOSONG -->
        <div v-else-if="!news.data || news.data.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100 max-w-xl mx-auto my-10">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6"></path>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-[#152c5b] mb-1">Berita Tidak Ditemukan</h3>
          <p class="text-slate-500 text-sm">Tidak ada berita yang sesuai dengan kata kunci atau filter yang Anda cari.</p>
        </div>

        <!-- GRID KARTU BERITA -->
        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          
          <Link 
            v-for="(item, index) in news.data" 
            :key="item.id || index"
            :href="route('news.detail', item.id)" 
            class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
          >
            <div class="aspect-[3/2] w-full overflow-hidden bg-[#d9d9d9]">
              <img 
                :src="getImageUrl(item.foto || item.image)" 
                :alt="item.judul || item.title" 
                class="h-full w-full object-cover"
              />
            </div>
            <div class="flex flex-col p-5 sm:p-6">
              <span class="mb-2 text-[11.5px] font-extrabold tracking-wide text-gray-400">
                {{ formatDate(item.created_at || item.date) }}
              </span>
              <h3 class="mb-2.5 text-lg font-extrabold leading-snug text-[#152c5b] transition-colors group-hover:text-[#4b857a] line-clamp-2">
                {{ item.judul || item.title }}
              </h3>
              <p class="text-[13px] leading-relaxed text-[#5c6b89] line-clamp-4">
                {{ stripHtml(item.deskripsi || item.description) }}
              </p>
            </div>
          </Link>

        </div>

        <!-- PAGINATION -->
        <div v-if="news.total > 0" class="mt-10 sm:mt-12 border-t border-[#d6e0ee] pt-4 pb-4">
          <TablePagination
            v-model:currentPage="currentPage"
            v-model:rowsPerPage="rowsPerPage"
            :totalPages="news.last_page"
            :totalItems="news.total"
            :rows-options="[3, 6, 9, 12, 18]"
            :disabled="isLoading"
            item-label="Berita"
          />
        </div>
        
      </div>
    </div>
  </MainLayout>
</template>
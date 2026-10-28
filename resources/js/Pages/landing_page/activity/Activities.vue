<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';

// ==========================================
// 1. PROPS DARI BACKEND
// ==========================================
const props = defineProps({
  aktivitasData: {
    type: Object,
    default: () => ({ data: [], total: 0, current_page: 1, last_page: 1 })
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

// ==========================================
// 2. STATE FILTER & SEARCH (SAMA SEPERTI ACTIVITYLIST)
// ==========================================
const validDeadlines = ['terdekat', 'terjauh', 'terlewat'];
const validBidang = ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'];

const searchQuery = ref(props.filters?.search || '');
const activeCategory = ref(props.filters?.jenis || 'Semua');
const filterDeadline = ref(validDeadlines.includes(props.filters?.sort) ? props.filters.sort : '');
const filterBidang = ref(validBidang.includes(props.filters?.kategori) ? props.filters.kategori : '');

const categories = ['Semua', 'umum', 'lomba'];
const showFilter = ref(false);
const toggleFilter = () => { showFilter.value = !showFilter.value; };
const resetFilters = () => {
  filterDeadline.value = '';
  filterBidang.value = '';
};

const isFilterActive = computed(() => {
  const hasBidang = Boolean(filterBidang.value && validBidang.includes(filterBidang.value));
  const hasDeadline = Boolean(filterDeadline.value && validDeadlines.includes(filterDeadline.value));
  return hasBidang || hasDeadline;
});

// ==========================================
// 3. PAGINATION & LOADING STATE
// ==========================================
const currentPage = ref(props.aktivitasData?.current_page || 1);
const rowsPerPage = ref(Number(props.filters?.per_page) || 8);
const isLoading = ref(false);

const skeletonCount = computed(() => {
  const count = props.aktivitasData?.data?.length || 0;
  return count > 0 ? count : 4;
});

// ==========================================
// 4. REQUEST DATA KE BACKEND
// ==========================================
const fetchFilteredData = (targetPage = null) => {
  isLoading.value = true;
  const pageNum = targetPage !== null ? targetPage : currentPage.value;

  let params = {
    search: searchQuery.value || undefined,
    jenis: activeCategory.value !== 'Semua' ? activeCategory.value : undefined,
    kategori: filterBidang.value || undefined,
    page: pageNum > 1 ? pageNum : undefined,
    per_page: rowsPerPage.value || undefined
  };

  if (filterDeadline.value && validDeadlines.includes(filterDeadline.value)) {
    params.sort = filterDeadline.value;
  }

  router.get('/activities', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const handleFilterOrSearch = () => {
  if (currentPage.value !== 1) {
    currentPage.value = 1;
  } else {
    fetchFilteredData(1);
  }
};

watch([currentPage, rowsPerPage], () => {
  fetchFilteredData();
});

let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleFilterOrSearch();
  }, 300);
});

let filterTimeout = null;
watch([activeCategory, filterDeadline, filterBidang], () => {
  clearTimeout(filterTimeout);
  filterTimeout = setTimeout(() => {
    handleFilterOrSearch();
  }, 50);
});

watch(() => props.aktivitasData?.current_page, (newPage) => {
  if (newPage && newPage !== currentPage.value) {
    currentPage.value = newPage;
  }
});

onUnmounted(() => {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (filterTimeout) clearTimeout(filterTimeout);
});

// ==========================================
// 5. HELPER FORMATTING UI (SAMA PERSIS DENGAN ACTIVITYLIST)
// ==========================================
const formatTanggal = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
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

const formatCardDescription = (html) => {
  if (!html) return '';
  let text = String(html).replace(/<\/?(p|div|h[1-6]|ul|ol|li|blockquote|br)[^>]*>/gi, ' ');
  text = text.replace(/<a\b[^>]*>(.*?)<\/a>/gi, '$1');
  text = text.replace(/<(?!\/?(b|strong|i|em|u|s|span)\b)[^>]+>/gi, '');
  return text.replace(/\s+/g, ' ').trim();
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] py-12 font-poppins text-[#152c5b] lg:py-16 mt-6 sm:mt-10">
      
      <div class="mx-auto flex w-full max-w-7xl flex-col px-4 sm:px-6 md:px-10">
        
        <!-- HEADER -->
        <div class="mb-6 sm:mb-8">
          <h1 class="mb-2 text-2xl font-extrabold tracking-tight sm:text-4xl md:text-[40px] text-[#17334F]">
            Kumpulan Aktivitas
          </h1>
          <p class="text-sm font-medium text-gray-500 sm:text-base">
            Yuk temukan aktivitas yang cocok dengan talenta kamu disini!
          </p>
        </div>

        <!-- SEARCH & FILTER BAR (PERSIS SEPERTI MAHASISWA ACTIVITYLIST) -->
        <div class="mb-6 flex flex-row items-center gap-2 sm:gap-3 w-full">
          <SearchBarTable 
            v-model="searchQuery" 
            placeholder="Cari Aktivitas disini..." 
            class="flex-1 w-full min-w-0" 
          />
          
          <div class="relative shrink-0">
            <button 
              type="button"
              :disabled="isLoading"
              @click="toggleFilter" 
              class="relative z-40 flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none" 
              :class="[
                showFilter || isFilterActive ? 'border-[#183669]' : 'border-[#d6e0ee] hover:border-[#8ea9cb]',
                isLoading ? 'cursor-wait opacity-80' : 'cursor-pointer'
              ]" 
              title="Filter Aktivitas"
            >
              <img src="/assets/icons/filter.svg" class="h-5 w-5 shrink-0 object-contain pointer-events-none" onerror="this.style.display='none'" />
              <span v-if="isFilterActive" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
            </button>

            <!-- Popup Dropdown Filter -->
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
                class="absolute right-0 top-14 z-40 w-72 sm:w-80 max-w-[calc(100vw-32px)] max-h-[min(460px,calc(100vh-100px))] overflow-y-auto rounded-[14px] border border-[#d6e0ee] bg-white p-3.5 sm:p-4 shadow-2xl ring-1 ring-black/10 font-inter"
              >
                <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3 px-0.5 sticky top-0 bg-white z-10">
                  <p class="font-poppins text-xs font-bold text-[#183669]">
                    Filter Aktivitas
                  </p>
                  <button v-if="isFilterActive" @click="resetFilters" class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer">Reset Semua</button>
                </div>
                
                <!-- SECTION 1 (ATAS): FILTER BIDANG / KATEGORI TALENTA -->
                <div class="mb-3.5">
                  <p class="text-[10.5px] font-bold uppercase tracking-wider text-[#7188a3] mb-2 px-1">Kategori Bidang / Talenta</p>
                  <div class="flex flex-wrap gap-1.5">
                    <button
                      type="button"
                      @click="filterBidang = ''"
                      :class="!filterBidang ? 'bg-[#183669] text-white border-[#183669]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-[#183669] hover:text-[#183669]'"
                      class="rounded-full px-2.5 py-1 text-[11px] font-semibold border cursor-pointer transition"
                    >
                      Semua
                    </button>
                    <button
                      v-for="bidang in validBidang"
                      :key="bidang"
                      type="button"
                      @click="filterBidang = filterBidang === bidang ? '' : bidang"
                      :class="filterBidang === bidang ? 'bg-[#183669] text-white border-[#183669] shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-[#183669] hover:text-[#183669]'"
                      class="rounded-full px-2.5 py-1 text-[11px] font-semibold border cursor-pointer transition"
                    >
                      {{ bidang }}
                    </button>
                  </div>
                </div>

                <!-- SECTION 2 (BAWAH): FILTER DEADLINE -->
                <div class="border-t border-[#f0f4f9] pt-3 mb-3">
                  <p class="text-[10.5px] font-bold uppercase tracking-wider text-[#7188a3] mb-1.5 px-1">Urutan & Batas Deadline</p>
                  <div class="space-y-1">
                    <button
                      type="button"
                      @click="filterDeadline = ''"
                      :class="!filterDeadline ? 'bg-[#f0f4f9] font-semibold text-[#183669]' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                      class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                    >
                      <span>Semua Deadline</span>
                      <svg v-if="!filterDeadline" class="h-3.5 w-3.5 text-[#183669]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>

                    <button
                      type="button"
                      @click="filterDeadline = 'terdekat'"
                      :class="filterDeadline === 'terdekat' ? 'bg-[#f0f4f9] font-semibold text-[#183669]' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                      class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                    >
                      <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span>Deadline Terdekat</span>
                      </div>
                      <svg v-if="filterDeadline === 'terdekat'" class="h-3.5 w-3.5 text-[#183669]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>

                    <button
                      type="button"
                      @click="filterDeadline = 'terjauh'"
                      :class="filterDeadline === 'terjauh' ? 'bg-[#f0f4f9] font-semibold text-[#183669]' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                      class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                    >
                      <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span>Deadline Terjauh</span>
                      </div>
                      <svg v-if="filterDeadline === 'terjauh'" class="h-3.5 w-3.5 text-[#183669]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>

                    <button
                      type="button"
                      @click="filterDeadline = 'terlewat'"
                      :class="filterDeadline === 'terlewat' ? 'bg-red-50 font-semibold text-red-700' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                      class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                    >
                      <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        <span>Deadline Terlewat</span>
                      </div>
                      <svg v-if="filterDeadline === 'terlewat'" class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="flex justify-end border-t border-[#f0f4f9] pt-2.5 sticky bottom-0 bg-white">
                  <button type="button" @click="showFilter = false" class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#122b54] cursor-pointer transition">Tutup</button>
                </div>
              </div>
            </Transition>

            <!-- Backdrop -->
            <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>
          </div>
        </div>

        <!-- CATEGORY PILLS -->
        <div class="mb-6 sm:mb-8 flex flex-wrap gap-2 sm:gap-3">
          <button 
            v-for="cat in categories" 
            :key="cat" 
            type="button"
            :disabled="isLoading"
            @click="activeCategory = cat" 
            :class="[
              activeCategory === cat ? 'bg-[#183669] text-white border-[#183669] shadow-sm' : 'bg-transparent text-[#64748b] border-[#d6e0ee] hover:border-[#183669] hover:text-[#183669]',
              isLoading ? 'cursor-wait opacity-80' : 'cursor-pointer'
            ]" 
            class="rounded-full px-4 sm:px-5 py-1 sm:py-1.5 text-[11.5px] sm:text-[13px] font-bold transition-all duration-200 border select-none capitalize"
          >
            {{ cat }}
          </button>
        </div>

        <!-- ========================================================= -->
        <!-- GRID KARTU AKTIVITAS (SAMA SEPERTI ACTIVITYLIST MAHASISWA) -->
        <!-- ========================================================= -->
        <div class="relative min-h-[360px]">
          <!-- SKELETON LOADING -->
          <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 animate-pulse select-none">
            <div 
              v-for="n in skeletonCount" 
              :key="'skeleton-' + n"
              class="relative flex flex-col justify-end overflow-hidden rounded-[12px] sm:rounded-[14px] aspect-[3/4] w-full border border-[#d6e0ee] bg-slate-200"
            >
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-800/30 to-slate-200/40"></div>
              <div class="absolute left-3.5 top-3.5 z-20 flex gap-2">
                <div class="h-[24px] sm:h-[26px] w-14 rounded-full bg-slate-300/80"></div>
              </div>
              <div class="absolute right-3.5 top-3.5 z-20">
                <div class="h-[24px] sm:h-[26px] w-12 rounded-full bg-slate-300/80"></div>
              </div>
              <div class="relative z-10 p-4 sm:p-5 flex flex-col justify-end">
                <div class="mb-2 flex items-center gap-2">
                  <div class="h-3 w-3 rounded-sm bg-slate-300/70"></div>
                  <div class="h-3 w-28 rounded-md bg-slate-300/70"></div>
                </div>
                <div class="space-y-1.5 mb-2">
                  <div class="h-4 w-full rounded-md bg-slate-100/90"></div>
                  <div class="h-4 w-3/4 rounded-md bg-slate-100/90"></div>
                </div>
                <div class="space-y-1 mb-3">
                  <div class="h-2.5 w-full rounded bg-slate-300/60"></div>
                  <div class="h-2.5 w-4/5 rounded bg-slate-300/60"></div>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                  <div class="h-5 w-16 rounded-full bg-slate-300/70"></div>
                  <div class="h-5 w-20 rounded-full bg-slate-300/70"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cards List -->
          <div v-else>
            <div v-if="aktivitasData.data && aktivitasData.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
              <Link 
                v-for="item in aktivitasData.data" 
                :key="item.id"
                :href="route('activity.detail', item.id)"
                class="group relative flex flex-col justify-end overflow-hidden rounded-[12px] sm:rounded-[14px] shadow-sm transition-[transform,box-shadow] duration-300 ease-out aspect-[3/4] w-full border border-[#d6e0ee] bg-[#1e293b] sm:hover:-translate-y-1.5 sm:hover:shadow-lg cursor-pointer select-none will-change-transform transform-gpu backface-hidden isolate"
              >
                <!-- BACKGROUND GAMBAR & GRADIENT -->
                <img 
                  :src="getImageUrl(item?.gambar_aktivitas?.find(g => g.utama == 1 || g.utama === true)?.gambar || item?.gambar_aktivitas?.[0]?.gambar)" 
                  class="absolute inset-0 h-full w-full object-cover" 
                  :alt="item?.judul" 
                />
                
                <div class="absolute inset-0 bg-gradient-to-t from-[#091e1b] via-[#133830]/80 to-transparent opacity-95 pointer-events-none"></div>

                <!-- BADGE UMUM / LOMBA -->
                <div class="absolute left-3.5 top-3.5 z-20 pointer-events-none">
                  <div
                    :class="[
                      'inline-flex h-[24px] sm:h-[26px] items-center justify-center rounded-full px-2.5 sm:px-3 text-[10.5px] sm:text-[11px] font-bold uppercase tracking-wider shadow-xs select-none antialiased leading-none transform-gpu backface-hidden',
                      String(item.jenis || '').toLowerCase() === 'lomba'
                        ? 'bg-amber-500 text-white border border-amber-400/40'
                        : 'bg-blue-600 text-white border border-blue-400/30'
                    ]"
                  >
                    <span>{{ String(item.jenis || '').toLowerCase() === 'lomba' ? 'Lomba' : 'Umum' }}</span>
                  </div>
                </div>

                <!-- BADGE PESERTA -->
                <div class="absolute right-3.5 top-3.5 z-20 pointer-events-none">
                  <div class="inline-flex h-[24px] sm:h-[26px] items-center justify-center gap-1.5 rounded-full bg-white/95 px-2.5 sm:px-3 text-[10.5px] sm:text-[11px] font-bold text-[#183669] border border-slate-200/90 shadow-xs backdrop-blur-sm select-none antialiased leading-none transform-gpu backface-hidden">
                    <svg class="h-3.5 w-3.5 text-[#183669] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ item.partisipan || 0 }}</span>
                  </div>
                </div>

                <!-- KONTEN TEKS KARTU -->
                <div class="relative z-10 p-4 sm:p-5 flex flex-col justify-end">
                  <div class="mb-1 flex items-center gap-1.5 text-[10.5px] sm:text-[11px] font-semibold text-white">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Deadline: {{ formatTanggal(item.deadline) }}</span>
                  </div>
                  <h3 class="mb-1 font-poppins text-[15px] sm:text-[16px] font-bold leading-snug text-white line-clamp-2">
                    {{ item.judul }}
                  </h3>
                  <p 
                    class="mb-2.5 font-inter text-[11px] sm:text-[12px] leading-relaxed line-clamp-2 text-slate-200/90 card-description"
                    v-html="formatCardDescription(item.deskripsi)"
                  ></p>
                  <div class="flex flex-wrap gap-1.5">
                    <span 
                      v-for="tag in item.kategori" 
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

            <!-- Pesan Kosong -->
            <div v-else class="col-span-full py-16 text-center text-slate-400 font-inter font-medium">
              Data aktivitas tidak ditemukan.
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- PAGINATION (SAMA SEPERTI DASHBOARD)       -->
        <!-- ========================================== -->
        <div v-if="aktivitasData.total > 0" class="mt-10 sm:mt-12 border-t border-[#d6e0ee] pt-4 pb-4">
          <TablePagination 
            v-model:currentPage="currentPage" 
            v-model:rowsPerPage="rowsPerPage"
            :totalPages="aktivitasData.last_page" 
            :totalItems="aktivitasData.total"
            :rows-options="[4, 8, 12, 16, 24]"
            :disabled="isLoading"
          />
        </div>
        
      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.card-description :deep(b),
.card-description :deep(strong) {
  font-weight: 700;
  color: #ffffff;
}
.card-description :deep(i),
.card-description :deep(em) {
  font-style: italic;
  color: rgba(241, 245, 249, 0.95);
}
.card-description :deep(u) {
  text-decoration: underline;
}
.card-description :deep(s) {
  text-decoration: line-through;
}
</style>
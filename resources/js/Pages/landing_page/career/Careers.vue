<script setup>
import { ref, watch, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import CardSkeleton from '@/Components/dashboard/CardSkeleton.vue';

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
    default: () => ['Internship', 'Full time', 'Part time', 'Volunteering']
  },
  karirData: {
    type: Object,
    default: () => ({
      data: [],
      total: 0,
      current_page: 1,
      last_page: 1,
      per_page: 9
    })
  }
});

// ==========================================
// 2. STATE FORM & DROPDOWN
// ==========================================
const search = ref(props.filters.search ?? '');
const kategori = ref(props.filters.kategori ?? '');
const sort = ref(props.filters.sort ?? '');
const isLoading = ref(false);

const openDropdown = ref(null);

const toggleDropdown = (name) => {
  openDropdown.value = openDropdown.value === name ? null : name;
};

const closeDropdown = () => {
  openDropdown.value = null;
};

const selectFilter = (type, value) => {
  if (type === 'kategori') kategori.value = value;
  if (type === 'sort') sort.value = value;
  openDropdown.value = null;
};

// ==========================================
// 3. PAGINATION
// ==========================================
const currentPage = ref(props.karirData?.current_page || 1);
const rowsPerPage = ref(Number(props.karirData?.per_page) || 9);

// ==========================================
// 4. ACTION SUBMIT (AUTO FILTER DEBOUNCE & WATCH)
// ==========================================
const applyFilters = (targetPage = 1) => {
  isLoading.value = true;
  const params = {};
  if (search.value) params.search = search.value;
  if (kategori.value) params.kategori = kategori.value;
  if (sort.value) params.sort = sort.value;
  if (targetPage > 1) params.page = targetPage;
  if (rowsPerPage.value) params.per_page = rowsPerPage.value;

  router.get('/careers', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const resetFilters = () => {
  search.value = '';
  kategori.value = '';
  sort.value = '';
  applyFilters(1);
};

// Debounce otomatis saat user mengetik di search bar
let searchTimeout = null;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters(1);
  }, 350);
});

// Otomatis filter seketika opsi kategori atau sort dipilih
watch([kategori, sort], () => {
  applyFilters(1);
});

const handlePageChange = (page) => {
  currentPage.value = page;
  applyFilters(page);
};

onUnmounted(() => {
  if (searchTimeout) clearTimeout(searchTimeout);
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  try {
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('id-ID', options);
  } catch {
    return dateString;
  }
};

const getImageUrl = (path) => {
  if (!path) return '';
  if (typeof path === 'string' && (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:'))) {
    return path;
  }
  const clean = String(path).replace(/^\/?(public\/|storage\/)+/, '');
  return `/storage/${clean}`;
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen w-full bg-[#fcfcfb] py-12 font-poppins text-[#152c5b] lg:py-16 mt-6 sm:mt-10">
      
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10">
        
        <!-- HEADER -->
        <div class="mb-6 sm:mb-8">
          <h1 class="mb-2 text-2xl font-extrabold tracking-tight sm:text-4xl md:text-[40px] text-[#17334F]">
            Kumpulan Karir
          </h1>
          <p class="text-sm font-medium text-gray-500 sm:text-base">
            Yuk temukan karir yang cocok dengan talenta kamu disini!
          </p>
        </div>

        <!-- SEARCH & DROPDOWN FILTER FORM -->
        <div class="mb-10 flex flex-col gap-4">
          
          <!-- Search Bar (Harmonis menggunakan SearchBarTable) -->
          <div class="w-full">
            <SearchBarTable 
              v-model="search" 
              placeholder="Cari Karir disini..." 
            />
          </div>

          <!-- Custom Dropdown Filters -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            
            <!-- 1. Dropdown Kategori Posisi -->
            <div :class="['relative', openDropdown === 'posisi' ? 'z-40' : 'z-20']">
              <div 
                @click="toggleDropdown('posisi')"
                class="flex h-[46px] w-full cursor-pointer items-center justify-between rounded-[10px] border-2 border-[#d6e0ee] bg-white px-4 text-sm font-medium text-[#1e3456] shadow-xs transition-colors hover:border-[#8ea9cb]"
              >
                <span :class="kategori ? 'font-bold text-[#183669] capitalize' : 'text-slate-400'">
                  {{ kategori || 'Pilih Kategori Posisi' }}
                </span>
                <svg 
                  :class="['h-4 w-4 text-[#183669] transition-transform duration-300', openDropdown === 'posisi' ? 'rotate-180' : '']" 
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              
              <!-- Backdrop lokal untuk menutup dropdown jika klik di luar -->
              <div v-if="openDropdown === 'posisi'" @click="closeDropdown" class="fixed inset-0 z-40"></div>

              <!-- Menu Options -->
              <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-1 scale-95"
              >
                <div v-if="openDropdown === 'posisi'" class="absolute left-0 top-[110%] z-50 w-full overflow-hidden rounded-[12px] border border-[#d6e0ee] bg-white shadow-xl">
                  <ul class="max-h-60 overflow-auto py-1 font-inter">
                    <li 
                      @click="selectFilter('kategori', '')" 
                      :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-slate-50 hover:text-[#183669]', !kategori ? 'bg-slate-50 font-bold text-[#183669]' : 'text-slate-600']"
                    >
                      Semua Posisi
                    </li>
                    <li 
                      v-for="opsi in posisiOptions" 
                      :key="opsi" 
                      @click="selectFilter('kategori', opsi)" 
                      :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-slate-50 hover:text-[#183669] capitalize', kategori.toLowerCase() === opsi.toLowerCase() ? 'bg-slate-50 font-bold text-[#183669]' : 'text-slate-700']"
                    >
                      {{ opsi }}
                    </li>
                  </ul>
                </div>
              </Transition>
            </div>

            <!-- 2. Dropdown Sortir -->
            <div :class="['relative', openDropdown === 'sort' ? 'z-40' : 'z-10']">
              <div 
                @click="toggleDropdown('sort')"
                class="flex h-[46px] w-full cursor-pointer items-center justify-between rounded-[10px] border-2 border-[#d6e0ee] bg-white px-4 text-sm font-medium text-[#1e3456] shadow-xs transition-colors hover:border-[#8ea9cb]"
              >
                <span :class="sort ? 'font-bold text-[#183669]' : 'text-slate-400'">
                  {{ sort === 'segera_berakhir' ? 'Segera Berakhir' : 'Upload Terbaru' }}
                </span>
                <svg 
                  :class="['h-4 w-4 text-[#183669] transition-transform duration-300', openDropdown === 'sort' ? 'rotate-180' : '']" 
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              
              <!-- Backdrop lokal untuk menutup dropdown jika klik di luar -->
              <div v-if="openDropdown === 'sort'" @click="closeDropdown" class="fixed inset-0 z-40"></div>

              <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-1 scale-95"
              >
                <div v-if="openDropdown === 'sort'" class="absolute left-0 top-[110%] z-50 w-full overflow-hidden rounded-[12px] border border-[#d6e0ee] bg-white shadow-xl">
                  <ul class="max-h-60 overflow-auto py-1 font-inter">
                    <li 
                      @click="selectFilter('sort', '')" 
                      :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-slate-50 hover:text-[#183669]', sort === '' ? 'bg-slate-50 font-bold text-[#183669]' : 'text-slate-700']"
                    >
                      Upload Terbaru
                    </li>
                    <li 
                      @click="selectFilter('sort', 'segera_berakhir')" 
                      :class="['cursor-pointer px-4 py-2.5 text-sm transition-colors hover:bg-slate-50 hover:text-[#183669]', sort === 'segera_berakhir' ? 'bg-slate-50 font-bold text-[#183669]' : 'text-slate-700']"
                    >
                      Segera Berakhir
                    </li>
                  </ul>
                </div>
              </Transition>
            </div>

          </div>

          <!-- Tombol Reset Filter jika ada filter/search yang aktif -->
          <div v-if="search || kategori || sort" class="flex justify-end">
            <button 
              type="button"
              :disabled="isLoading"
              @click="resetFilters" 
              class="inline-flex items-center gap-1.5 font-inter text-xs font-semibold text-red-600 hover:text-red-700 hover:underline cursor-pointer select-none disabled:opacity-50"
            >
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Reset Semua Filter</span>
            </button>
          </div>

        </div>

        <!-- ========================================== -->
        <!-- GRID KARTU KARIR & SKELETON               -->
        <!-- ========================================== -->
        <div class="relative min-h-[360px]">
          <!-- SKELETON LOADING -->
          <CardSkeleton 
            v-if="isLoading" 
            type="career" 
            :count="karirData.data?.length || 6" 
          />

          <!-- KONTEN KARTU KARIR -->
          <div v-else>
            <div v-if="karirData.data && karirData.data.length > 0" class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
              <div 
                v-for="item in karirData.data" 
                :key="item.id" 
                class="group relative flex flex-col justify-between rounded-[14px] border border-[#d6e0ee] bg-white p-5 shadow-xs transition-[transform,box-shadow] duration-200 hover:-translate-y-1 hover:shadow-md"
              >
                <!-- Bagian Atas -->
                <div>
                  <div class="mb-4 flex items-start gap-3.5">
                    <!-- Logo Perusahaan / Instansi -->
                    <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#d6e0ee] bg-[#f8fafc]">
                      <img 
                        v-if="item.foto || item.logo" 
                        :src="getImageUrl(item.foto || item.logo)" 
                        :alt="item.nama_instansi || item.instansi" 
                        class="h-full w-full object-cover" 
                        @error="(e) => e.target.style.display = 'none'"
                      />
                      <svg v-else class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                      </svg>
                    </div>
                  
                    <div class="flex flex-col min-w-0 flex-1">
                      <h3 class="text-[15px] sm:text-[16px] font-bold leading-snug text-[#17334F] line-clamp-2" :title="item.posisi">
                        {{ item.posisi }}
                      </h3>
                      <p class="mt-0.5 text-[12.5px] font-semibold text-slate-600 truncate" :title="item.nama_instansi || item.instansi">
                        {{ item.nama_instansi || item.instansi }}
                      </p>
                      <div class="mt-1 flex items-center gap-1.5">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10.5px] font-semibold text-slate-700 capitalize border border-slate-200">
                          {{ item.kategori || 'Umum' }}
                        </span>
                      </div>
                    </div>
                  </div>
                
                  <!-- Bagian Alamat / Lokasi -->
                  <div class="mb-3.5 flex items-start gap-2 text-slate-500 font-inter">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"></path>
                    </svg>
                    <p class="text-[12px] leading-snug line-clamp-2">{{ item.lokasi || 'Lokasi tidak dicantumkan' }}</p>
                  </div>
                </div>
              
                <!-- Bagian Bawah (Deadline & Tombol Detail) -->
                <div class="mt-auto flex items-center justify-between border-t border-[#f0f4f9] pt-3.5 font-inter">
                  <div class="flex items-center gap-1.5 text-slate-500">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-[11px] font-semibold">Deadline: {{ formatDate(item.deadline) }}</span>
                  </div>

                  <Link 
                    :href="route('career.detail', item.id)" 
                    class="rounded-full bg-[#183669] px-5 py-1.5 text-[11.5px] font-bold text-white transition-colors hover:bg-[#122b54] shadow-xs select-none"
                  >
                    Detail
                  </Link>
                </div>
              </div>
            </div>

            <!-- KONDISI JIKA DATA KOSONG / TIDAK DITEMUKAN -->
            <div v-else class="py-16 text-center rounded-2xl border border-dashed border-[#d6e0ee] bg-white">
              <img src="/assets/icons/notfound.svg" alt="Tidak Ada Lowongan Karir" class="mx-auto mb-3.5 h-16 w-16" />
              <h3 class="text-base font-bold text-[#17334F]">Tidak Ada Lowongan Karir</h3>
              <p class="mt-1 text-sm text-slate-500 font-inter">Maaf, tidak ditemukan lowongan yang sesuai dengan filter atau pencarian Anda.</p>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- PAGINATION (HARMONIS SEPERTI ACTIVITIES)  -->
        <!-- ========================================== -->
        <div v-if="karirData.total > 0" class="mt-10 sm:mt-12 border-t border-[#d6e0ee] pt-4 pb-4">
          <TablePagination 
            v-model:currentPage="currentPage" 
            v-model:rowsPerPage="rowsPerPage"
            :totalPages="karirData.last_page" 
            :totalItems="karirData.total"
            :rows-options="[6, 9, 12, 18, 24]"
            :disabled="isLoading"
            @update:currentPage="handlePageChange"
          />
        </div>
        
      </div>
    </div>
  </MainLayout>
</template>

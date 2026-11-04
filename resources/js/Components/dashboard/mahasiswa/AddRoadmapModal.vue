<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  availableRoadmaps: {
    type: [Array, Object],
    default: () => ({ data: [] })
  },
  selectedData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'submit', 'update:show']);

// Data Roadmaps dari backend
const roadmaps = computed(() => {
  if (Array.isArray(props.availableRoadmaps)) {
    return props.availableRoadmaps;
  }
  return props.availableRoadmaps?.data || [];
});

// State checkbox yang dipilih
const selectedIds = ref([]);

// Search, Filter & Sorting State (Backend Driven)
const searchQuery = ref('');
const filterCategory = ref('');
const showFilter = ref(false);
const isLoading = ref(false);

const sortColumn = ref('id');
const sortDirection = ref('asc');

// Pagination State
const rowsPerPage = ref(props.availableRoadmaps?.per_page || 5);
const currentPage = computed(() => props.availableRoadmaps?.current_page || 1);
const totalPages = computed(() => props.availableRoadmaps?.last_page || 1);
const totalItems = computed(() => props.availableRoadmaps?.total || roadmaps.value.length);

const skeletonCount = computed(() => {
  const count = roadmaps.value.length;
  return count > 0 ? count : (props.availableRoadmaps?.per_page || rowsPerPage.value || 5);
});

// Fungsi request ke backend
const fetchRoadmaps = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;

  router.get('/mahasiswa/roadmap', {
    modal_search: searchQuery.value || undefined,
    modal_kategori: filterCategory.value || undefined,
    modal_sort: sortColumn.value !== 'id' ? sortColumn.value : undefined,
    modal_direction: sortDirection.value !== 'asc' ? sortDirection.value : undefined,
    modal_per_page: rowsPerPage.value !== 5 ? rowsPerPage.value : undefined,
    modal_page: targetPage > 1 ? targetPage : undefined,
  }, {
    only: ['availableRoadmaps'],
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Debounce Watcher untuk Search dan Filter Kategori
let debounceTimeout = null;
watch([searchQuery, filterCategory], () => {
  if (!props.show) return;
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchRoadmaps(1);
  }, 350);
});

// Sorting Handler
const handleSort = (column) => {
  if (isLoading.value) return;

  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = (column === 'totalTask' || column === 'partisipan') ? 'desc' : 'asc';
  }
  fetchRoadmaps(currentPage.value);
};

// Pagination Handlers
const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  fetchRoadmaps(newPage);
};

const handleRowsPerPageChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  fetchRoadmaps(1);
};

// Listener ESC key untuk menutup modal
const handleKeyDown = (e) => {
  if (e.key === 'Escape' || e.keyCode === 27) {
    if (props.show) {
      if (showFilter.value) {
        showFilter.value = false;
      } else {
        emit('close');
      }
    }
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  if (debounceTimeout) clearTimeout(debounceTimeout);
});

// Reset state ketika modal ditutup
watch(() => props.show, (newVal, oldVal) => {
  if (!newVal && oldVal) {
    const hadFilters = searchQuery.value || filterCategory.value || sortColumn.value !== 'id' || currentPage.value > 1;
    selectedIds.value = [];
    searchQuery.value = '';
    filterCategory.value = '';
    showFilter.value = false;
    sortColumn.value = 'id';
    sortDirection.value = 'asc';

    if (hadFilters) {
      router.get('/mahasiswa/roadmap', {}, {
        only: ['availableRoadmaps'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
      });
    }
  }
});

// Daftar ID roadmap yang belum di-enroll pada halaman saat ini
const selectableRoadmaps = computed(() => {
  return roadmaps.value.filter(item => !item.is_enrolled);
});

// Logic Select All checkbox
const selectAll = computed({
  get() {
    return selectableRoadmaps.value.length > 0 && 
      selectableRoadmaps.value.every(item => selectedIds.value.includes(item.id));
  },
  set(value) {
    if (value) {
      const currentIds = new Set(selectedIds.value);
      selectableRoadmaps.value.forEach(item => currentIds.add(item.id));
      selectedIds.value = Array.from(currentIds);
    } else {
      const pageIds = selectableRoadmaps.value.map(item => item.id);
      selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id));
    }
  }
});

const handleSubmit = () => {
  emit('submit', selectedIds.value);
};
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop Transition -->
    <Transition 
      enter-active-class="transition duration-300 ease-out" 
      enter-from-class="opacity-0" 
      enter-to-class="opacity-100" 
      leave-active-class="transition duration-200 ease-in" 
      leave-from-class="opacity-100" 
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        
        <!-- Background Overlay (Klik luar untuk tutup) -->
        <div class="absolute inset-0" @click="emit('close')"></div>
        
        <!-- Modal Content Transition -->
        <Transition 
          enter-active-class="transition duration-300 ease-out delay-75" 
          enter-from-class="opacity-0 scale-95 translate-y-4" 
          enter-to-class="opacity-100 scale-100 translate-y-0" 
          leave-active-class="transition duration-200 ease-in" 
          leave-from-class="opacity-100 scale-100 translate-y-0" 
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div v-if="show" class="relative w-full max-w-4xl lg:max-w-5xl max-h-[92vh] overflow-hidden rounded-2xl bg-white p-6 sm:p-8 shadow-2xl flex flex-col font-inter">
            
            <!-- HEADER MODAL -->
            <div class="mb-5 text-center shrink-0">
              <h2 class="text-2xl font-extrabold text-[#1a2b4c] font-poppins">Daftar Roadmap</h2>
              <p class="mt-1 text-[14px] sm:text-[15px] font-medium text-[#4d6786]">Yuk Pilih Roadmap yang Kamu Inginkan!</p>
            </div>

            <!-- SEARCH & FILTER TOOLBAR -->
            <div class="mb-4 flex flex-row items-center gap-2.5 sm:gap-3 w-full shrink-0">
              <SearchBarTable 
                class="w-full flex-1 min-w-0" 
                placeholder="Cari roadmap disini..." 
                v-model="searchQuery" 
                :disabled="isLoading"
              />

              <!-- Filter Dropdown Container -->
              <div class="relative shrink-0">
                <button
                  type="button"
                  :disabled="isLoading"
                  @click="showFilter = !showFilter"
                  class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors hover:border-[#8ea9cb] focus:outline-none select-none cursor-pointer disabled:opacity-75 disabled:cursor-wait"
                  :class="showFilter || filterCategory ? 'border-[#183669]' : 'border-[#d6e0ee]'"
                  title="Filter Kategori"
                >
                  <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                  <span v-if="filterCategory" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
                </button>

                <Transition
                  enter-active-class="transition duration-200 ease-out"
                  enter-from-class="opacity-0 translate-y-2"
                  enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition duration-150 ease-in"
                  leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 translate-y-2"
                >
                  <div v-if="showFilter" class="absolute right-0 top-14 z-50 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg font-inter">
                    <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Kategori</p>
                    <button
                      type="button"
                      @click="filterCategory = ''; showFilter = false"
                      class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium cursor-pointer"
                      :class="!filterCategory ? 'bg-gray-100 font-semibold text-[#183669]' : ''"
                    >
                      Semua Kategori
                    </button>
                    <button
                      v-for="cat in ['Profesional', 'Bisnis', 'Birokrasi', 'Akademisi']"
                      :key="cat"
                      type="button"
                      @click="filterCategory = cat; showFilter = false"
                      class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium cursor-pointer"
                      :class="filterCategory === cat ? 'bg-gray-100 font-semibold text-[#183669]' : ''"
                    >
                      {{ cat }}
                    </button>
                  </div>
                </Transition>

                <!-- Overlay penutup filter jika klik di luar -->
                <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-40"></div>
              </div>
            </div>

            <!-- TABLE CONTENT -->
            <div class="overflow-y-auto flex-1 custom-scrollbar pb-2">
              <div class="overflow-x-auto rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
                <table class="w-full min-w-[620px] md:min-w-full table-fixed md:table-auto border-separate border-spacing-0 text-sm">
                  <thead class="bg-[#416f65]">
                    <tr class="h-[48px]">
                      
                      <!-- Kolom Checkbox / Pilih -->
                      <th class="w-[60px] px-3 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tl-[12px] border-r border-white/15 lg:border-r-0">
                        <input 
                          type="checkbox" 
                          v-model="selectAll"
                          :disabled="isLoading || selectableRoadmaps.length === 0"
                          class="h-4 w-4 rounded border-white/50 bg-white/20 text-[#1a2b4c] focus:ring-0 focus:ring-offset-0 cursor-pointer disabled:cursor-not-allowed"
                        />
                      </th>

                      <!-- Kolom Judul (Centered Table Head & Sortable) -->
                      <th class="px-4 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                        <button 
                          type="button" 
                          :disabled="isLoading"
                          @click="handleSort('judul')" 
                          class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait" 
                          title="Urutkan Judul"
                        >
                          <span>Judul</span>
                          <span class="absolute left-full ml-1.5 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                            <svg v-if="sortColumn === 'judul'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </button>
                      </th>

                      <!-- Kolom Kategori (Tanpa Asc/Desc) -->
                      <th class="w-[140px] px-3 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                        Kategori
                      </th>

                      <!-- Kolom Total Task (Sortable) -->
                      <th class="w-[120px] px-3 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                        <button 
                          type="button" 
                          :disabled="isLoading"
                          @click="handleSort('totalTask')" 
                          class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait" 
                          title="Urutkan Total Task"
                        >
                          <span>Total Task</span>
                          <span class="absolute left-full ml-1.5 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                            <svg v-if="sortColumn === 'totalTask'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </button>
                      </th>

                      <!-- Kolom Partisipan (Sortable) -->
                      <th class="w-[120px] px-3 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">
                        <button 
                          type="button" 
                          :disabled="isLoading"
                          @click="handleSort('partisipan')" 
                          class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait" 
                          title="Urutkan Partisipan"
                        >
                          <span>Partisipan</span>
                          <span class="absolute left-full ml-1.5 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                            <svg v-if="sortColumn === 'partisipan'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                            </svg>
                          </span>
                        </button>
                      </th>
                    </tr>
                  </thead>
                  
                  <!-- SKELETON KHUSUS ADD ROADMAP MODAL -->
                  <tbody v-if="isLoading" class="animate-pulse select-none [&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee]">
                    <tr v-for="r in skeletonCount" :key="'modal-skel-' + r" class="h-[52px]">
                      <!-- Kolom Checkbox -->
                      <td class="px-3 py-2.5 text-center">
                        <div class="h-4 w-4 mx-auto rounded border border-slate-300/50 bg-slate-200"></div>
                      </td>

                      <!-- Kolom Judul -->
                      <td class="px-4 py-2.5 text-left">
                        <div 
                          class="h-4 rounded bg-slate-200" 
                          :style="{ width: ['75%', '60%', '85%', '70%', '80%'][(r - 1) % 5] }"
                        ></div>
                      </td>

                      <!-- Kolom Kategori (1 pill tunggal centered) -->
                      <td class="px-3 py-2.5 text-center">
                        <div class="h-5 w-20 mx-auto rounded-full bg-slate-200"></div>
                      </td>

                      <!-- Kolom Total Task (1 bar nilai centered) -->
                      <td class="px-3 py-2.5 text-center">
                        <div class="h-4 w-8 mx-auto rounded bg-slate-200"></div>
                      </td>

                      <!-- Kolom Partisipan (1 bar nilai centered) -->
                      <td class="px-3 py-2.5 text-center">
                        <div class="h-4 w-8 mx-auto rounded bg-slate-200"></div>
                      </td>
                    </tr>
                  </tbody>

                  <!-- TBODY DATA ASLI -->
                  <tbody v-else class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                    <tr 
                      v-for="item in roadmaps" 
                      :key="item.id" 
                      class="h-[52px] transition-colors hover:bg-[#f7f9fd]"
                    >
                      <td class="px-3 py-2.5 text-center">
                        <span v-if="item.is_enrolled" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                          Diikuti
                        </span>
                        <input 
                          v-else
                          type="checkbox" 
                          :value="item.id" 
                          v-model="selectedIds"
                          class="h-4 w-4 rounded border-gray-300 text-[#416f65] focus:ring-[#416f65] cursor-pointer"
                        />
                      </td>
                      <td class="px-4 py-2.5 text-left font-medium text-[#233547] leading-snug">
                        {{ item.judul }}
                      </td>
                      <td class="px-3 py-2.5 text-center">
                        <span :class="[
                          'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                          item.kategori === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                          item.kategori === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                          item.kategori === 'Birokrat' || item.kategori === 'Birokrasi' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                          item.kategori === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                          'bg-slate-100 text-slate-700 border-slate-200'
                        ]">
                          {{ item.kategori }}
                        </span>
                      </td>
                      <td class="px-3 py-2.5 text-center font-medium">
                        {{ item.totalTask }}
                      </td>
                      <td class="px-3 py-2.5 text-center font-medium">
                        {{ item.partisipan }}
                      </td>
                    </tr>
                    
                    <!-- NOT FOUND STATE -->
                    <tr v-if="roadmaps.length === 0">
                      <td colspan="5" class="px-4 py-10 text-center text-gray-500 font-medium">
                        <div class="flex flex-col items-center justify-center">
                          <img src="/assets/icons/notfound.svg" alt="Data Tidak Ditemukan" class="h-12 w-12 mb-2.5" />
                          <span>Data roadmap tidak ditemukan sesuai pencarian/filter Anda.</span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- PAGINATION DARI BACKEND -->
              <div v-if="totalItems > 0" class="mt-4">
                <TablePagination 
                  :current-page="currentPage" 
                  :total-pages="totalPages" 
                  :total-items="totalItems"
                  :rows-per-page="rowsPerPage"
                  :rows-options="[5, 10, 20]"
                  :disabled="isLoading"
                  @update:current-page="handlePageChange"
                  @update:rows-per-page="handleRowsPerPageChange"
                />
              </div>
            </div>

            <!-- FOOTER BUTTONS -->
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0 pt-4 border-t border-gray-100">
              <div class="text-xs sm:text-sm text-[#4d6786] font-medium">
                <span v-if="selectedIds.length > 0">
                  <strong class="text-[#1a2b4c]">{{ selectedIds.length }}</strong> roadmap dipilih
                </span>
                <span v-else>Pilih roadmap yang ingin kamu ikuti</span>
              </div>
              <div class="flex justify-end gap-3">
                <button 
                  type="button" 
                  @click="emit('close')" 
                  class="rounded-lg border border-gray-300 bg-white w-28 sm:w-32 py-2.5 text-sm font-bold text-[#1a2b4c] transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 cursor-pointer"
                >
                  Kembali
                </button>
                <button 
                  type="button" 
                  @click="handleSubmit"
                  :disabled="selectedIds.length === 0"
                  :class="[
                    'rounded-lg w-28 sm:w-32 py-2.5 text-sm font-bold text-white shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-[#1a2b4c] focus:ring-offset-2',
                    selectedIds.length === 0 ? 'bg-gray-400 cursor-not-allowed shadow-none' : 'bg-[#1a2b4c] hover:bg-[#122b54] active:scale-95 cursor-pointer'
                  ]"
                >
                  Tambahkan
                </button>
              </div>
            </div>
            
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>

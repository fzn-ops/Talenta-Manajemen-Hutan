<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';
import ApproveModal from '@/Components/dashboard/ApproveModal.vue';
import ModalFormHasilAktivitas from '@/Components/dashboard/admin/ModalFormHasilAktivitas.vue';
import ImagePreviewModal from '@/Components/dashboard/ImagePreviewModal.vue';

import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

const props = defineProps({
  hasilData: Object,
  filters: Object,
});

// ==============================
// 1. STATE FILTER & PENCARIAN
// ==============================
const searchQuery = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');
const showFilter = ref(false);

const sortColumn = ref(props.filters?.sort || 'updated_at');
const sortDirection = ref(props.filters?.direction || 'desc');

// Pagination
const currentPage = ref(props.hasilData?.current_page || 1);
const totalPages = computed(() => props.hasilData?.last_page || 1);
const rowsPerPage = ref(props.hasilData?.per_page || 10);

const getRowNumber = (index) => {
  if (sortColumn.value === 'id' && sortDirection.value === 'desc') {
    const total = props.hasilData?.total || 0;
    return total - ((currentPage.value - 1) * rowsPerPage.value + index);
  }
  return (props.hasilData?.from || 1) + index;
};

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

// Loading state untuk efek blur tabel saat filtering/sorting
const isLoading = ref(false);

// ==============================
// 2. FETCH KE BACKEND
// ==============================
const fetchHasil = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;

  router.get('/admin/aktivitas/hasil', {
    search: searchQuery.value || undefined,
    status: filterStatus.value || undefined,
    sort: sortColumn.value || undefined,
    direction: sortDirection.value || undefined,
    per_page: rowsPerPage.value || undefined,
    page: targetPage > 1 ? targetPage : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Debounce untuk Search dan Filter
let timeout = null;
watch([searchQuery, filterStatus], () => {
  currentPage.value = 1;
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    fetchHasil(1);
  }, 300);
});

watch(() => props.hasilData?.current_page, (newPage) => {
  if (newPage && newPage !== currentPage.value) {
    currentPage.value = newPage;
  }
});

// ==============================
// 3. FUNGSI SORTING & PAGINASI
// ==============================
// Table Columns definition
const columns = [
  { key: 'nama_mahasiswa', label: 'Nama Mahasiswa', sortable: true, width: 'w-[200px]' },
  { key: 'nim', label: 'NIM', sortable: true, width: 'w-[130px]' },
  { key: 'nama_kegiatan', label: 'Nama Kegiatan', sortable: true, width: 'w-[230px]' },
  { key: 'jenis_roadmap', label: 'Jenis Roadmap', sortable: false, width: 'w-[160px]' },
  { key: 'bukti', label: 'Bukti', sortable: false, width: 'w-[120px]' },
  { key: 'status', label: 'Status', sortable: false, width: 'w-[130px]' },
  { key: 'action', label: 'Aksi', sortable: false, width: 'w-[170px]' },
];

const handleSort = (column) => {
  if (isLoading.value) return;

  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  fetchHasil(currentPage.value);
};

const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  currentPage.value = newPage;
  fetchHasil(newPage);
};

const handleRowsPerPageChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  currentPage.value = 1;
  fetchHasil(1);
};

// ==============================
// 4. MODALS & TOAST STATE
// ==============================
const showDetailModal = ref(false);
const showRejectModal = ref(false);
const showConfirmApproveModal = ref(false);
const itemToApprove = ref(null);
const selectedItem = ref(null);

const showImageModal = ref(false);
const selectedImage = ref('');

const toast = ref({ show: false, message: '', type: 'success' });

const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
  }, 50);
};

// Pantau flash message dari Laravel redirect
const page = usePage();
watch(() => page.props.flash?.success, (msg) => {
  if (msg) showToast(msg, 'success');
}, { immediate: true });
watch(() => page.props.flash?.error, (msg) => {
  if (msg) showToast(msg, 'error');
}, { immediate: true });

// ==============================
// 5. HELPER FORMATTER & MEDIA
// ==============================
const getMediaName = (val) => {
  if (!val) return '-';
  if (typeof val === 'object' && val.name) return val.name;
  if (typeof val === 'string') {
    const clean = val.split('?')[0];
    return clean.split('/').pop() || 'media.jpg';
  }
  return 'media.jpg';
};

const getImageSrc = (val) => {
  if (!val) return '';
  if (typeof val === 'object' && val.url) return val.url;
  if (typeof val === 'string') {
    if (val.startsWith('http://') || val.startsWith('https://') || val.startsWith('blob:') || val.startsWith('data:')) return val;
    if (val.startsWith('/storage/')) return val;
    if (val.startsWith('storage/')) return '/' + val;
    if (val.startsWith('/')) return val;
    return '/storage/' + val;
  }
  return `https://picsum.photos/seed/hasil/800/600`;
};

const onImageError = (e) => {
  const seed = String(selectedImage.value || 'hasil').replace(/[^a-zA-Z0-9]/g, '');
  e.target.src = `https://picsum.photos/seed/${seed || 'hasil'}/800/600`;
};

const getBuktiList = (item) => {
  if (!item) return [];
  const val = item.bukti || item.gambar || item.media || item.file;
  if (!val) return [];
  if (Array.isArray(val)) return val.filter(Boolean);
  return [val];
};

const activeImageList = ref([]);
const activeImageIndex = ref(0);

const openImage = (target, index = 0) => {
  if (!target) return;
  const list = getBuktiList(target);
  if (list.length > 0) {
    activeImageList.value = list;
    activeImageIndex.value = index >= 0 && index < list.length ? index : 0;
    showImageModal.value = true;
    return;
  }
  activeImageList.value = [target];
  activeImageIndex.value = 0;
  showImageModal.value = true;
};

// ==============================
// 6. ACTION HANDLERS
// ==============================
const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const promptApprove = (item) => {
  const target = item || selectedItem.value;
  if (!target) return;

  if (String(target.status).toLowerCase() === 'disetujui') {
    showToast('Hasil aktivitas ini sudah disetujui sebelumnya.', 'error');
    return;
  }

  itemToApprove.value = target;
  showConfirmApproveModal.value = true;
};

const executeApprove = () => {
  if (!itemToApprove.value) return;
  const targetId = itemToApprove.value.id;

  isLoading.value = true;

  router.post(`/admin/aktivitas/hasil/${targetId}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showConfirmApproveModal.value = false;
      showDetailModal.value = false;
      itemToApprove.value = null;
    },
    onError: () => {
      showToast('Gagal menyetujui hasil aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const openReject = (item) => {
  if (item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const handleReject = (reason) => {
  if (!selectedItem.value) return;
  const targetId = selectedItem.value.id;

  isLoading.value = true;

  router.post(`/admin/aktivitas/hasil/${targetId}/reject`, {
    catatan: reason || ''
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false;
      showDetailModal.value = false;
    },
    onError: () => {
      showToast('Gagal menolak hasil aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const showDeleteModal = ref(false);
const itemToDelete = ref(null);

const confirmDelete = (item) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};

const executeDelete = () => {
  if (!itemToDelete.value) return;
  const targetId = itemToDelete.value.id;
  isLoading.value = true;

  router.delete(`/admin/aktivitas/hasil/${targetId}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      itemToDelete.value = null;
      showToast('Data hasil aktivitas berhasil dihapus!', 'success');
    },
    onError: () => {
      showToast('Gagal menghapus hasil aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};
</script>

<template>
  <Head title="Hasil Aktivitas Mahasiswa"/>

  <AdminLayout>
    <section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
      <div class="space-y-6">
        
        <!-- TOAST NOTIFICATION -->
        <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
          <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
            <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/>
          </div>
        </Transition>

        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Hasil Aktivitas Mahasiswa</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!</p>
        </div>

        <!-- TOP BAR -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Hasil Aktivitas disini..." v-model="searchQuery"/>

          <!-- Filter Dropdown Container -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <div class="relative">
              <button @click="showFilter = !showFilter" class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors hover:border-[#8ea9cb] focus:outline-none select-none cursor-pointer" :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee]'">
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                <div v-if="showFilter" class="absolute right-0 top-14 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg font-inter">
                  <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
                  <button @click="filterStatus = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium cursor-pointer" :class="!filterStatus ? 'bg-gray-100 font-semibold' : ''">Semua Data</button>
                  <button @click="filterStatus = 'menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium cursor-pointer" :class="filterStatus === 'menunggu' ? 'bg-gray-100 font-semibold' : ''">Menunggu</button>
                  <button @click="filterStatus = 'disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium cursor-pointer" :class="filterStatus === 'disetujui' ? 'bg-green-50 font-semibold' : ''">Disetujui</button>
                  <button @click="filterStatus = 'ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium cursor-pointer" :class="filterStatus === 'ditolak' ? 'bg-red-50 font-semibold' : ''">Ditolak</button>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- Overlay penutup filter jika klik di luar -->
        <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

        <!-- TABEL DATA DENGAN BLUR LOADING EFFECT -->
        <div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isLoading ? 'cursor-wait' : '']">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  
                  <!-- Kolom No -->
                  <th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button type="button" :disabled="isLoading" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan No">
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>

                  <th
                    v-for="col in columns"
                    :key="col.key"
                    :class="[
                      col.width,
                      'px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 last:border-r-0 lg:border-r-0 last:rounded-tr-[12px]'
                    ]"
                  >
                    <button
                      v-if="col.sortable"
                      type="button"
                      :disabled="isLoading"
                      @click="handleSort(col.key)"
                      class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
                      :title="`Urutkan ${col.label}`"
                    >
                      <span>{{ col.label }}</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg
                          v-if="sortColumn === col.key"
                          :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                        </svg>
                        <svg
                          v-else
                          class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                        </svg>
                      </span>
                    </button>
                    <span v-else class="whitespace-nowrap">{{ col.label }}</span>
                  </th>
                </tr>
              </thead>
              
              <tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isLoading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
                <tr v-for="(item, index) in hasilData?.data" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ getRowNumber(index) }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_mahasiswa">{{ item.nama_mahasiswa }}</td>
                  <td class="px-3 py-2.5 text-center font-medium">{{ item.nim }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.nama_kegiatan">{{ item.nama_kegiatan }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <span :class="[
                      'shrink-0 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                      item.jenis_roadmap === 'Professional' || item.jenis_roadmap === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                      item.jenis_roadmap === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                      item.jenis_roadmap === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                      item.jenis_roadmap === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                      'bg-slate-100 text-slate-700 border-slate-200'
                    ]">
                      {{ item.jenis_roadmap }}
                    </span>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <span v-if="getBuktiList(item).length === 0" class="font-inter text-[13px] text-gray-400 italic">-</span>
                    <button 
                      v-else
                      type="button"
                      @click="openImage(item)" 
                      class="group inline-flex items-center justify-center gap-1.5 rounded-full px-2.5 py-1 font-inter text-[11px] font-semibold border bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-900 transition-all duration-150 cursor-pointer select-none outline-none focus:outline-none" 
                      :title="getBuktiList(item).length > 1 ? `Lihat ${getBuktiList(item).length} Bukti` : 'Lihat Bukti'"
                    >
                      <svg v-if="getBuktiList(item).length > 1" class="h-3.5 w-3.5 shrink-0 text-slate-600 group-hover:text-slate-800 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="3" width="14" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6 21h12a2 2 0 0 0 2-2V7" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="6.5" cy="7.5" r="1" fill="currentColor" />
                        <path d="m2 14 3.5-3.5 3.5 3.5" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <svg v-else class="h-3.5 w-3.5 shrink-0 text-slate-500 group-hover:text-slate-700 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor" />
                        <path d="m21 15-5-5L5 21" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>

                      <span>Bukti</span>

                      <span 
                        v-if="getBuktiList(item).length > 1" 
                        class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold border border-slate-300/80 group-hover:bg-slate-300 transition-colors"
                      >
                        {{ getBuktiList(item).length }}
                      </span>
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto inline-flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
                         :class="{
                           'bg-gray-100 text-gray-600 border-gray-200': String(item.status).toLowerCase() === 'menunggu',
                           'bg-green-50 text-green-600 border-green-200': String(item.status).toLowerCase() === 'disetujui',
                           'bg-red-50 text-red-600 border-red-200': String(item.status).toLowerCase() === 'ditolak'
                         }">
                        <svg v-if="String(item.status).toLowerCase() === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="9" />
                          <polyline points="12 7 12 12 15 15" />
                        </svg>
                        <svg v-if="String(item.status).toLowerCase() === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-if="String(item.status).toLowerCase() === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="capitalize">{{ item.status }}</span>
                      </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                      <AcceptButton :disabled="String(item.status).toLowerCase() === 'disetujui'" @click="promptApprove(item)" />
                      <RejectButton :disabled="String(item.status).toLowerCase() === 'ditolak' || String(item.status).toLowerCase() === 'disetujui'" @click="openReject(item)" />
                      <PreviewButtonTable @click="openDetail(item)" />
                      <DeleteButtonTable
                        label="Hapus Hasil Aktivitas"
                        title="Hapus Hasil Aktivitas"
                        @click="confirmDelete(item)" 
                      />
                    </div>
                  </td>
                </tr>
                <tr v-if="!hasilData?.data || hasilData.data.length === 0">
                  <td colspan="8" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
          :current-page="currentPage" 
          :total-pages="totalPages" 
          :rows-per-page="rowsPerPage"
          @update:current-page="handlePageChange"
          @update:rows-per-page="handleRowsPerPageChange"
        />
      </div>
    </section>

    <!-- MODAL DETAIL / PREVIEW HASIL KEGIATAN MAHASISWA -->
    <ModalFormHasilAktivitas
      :show="showDetailModal"
      :data="selectedItem"
      @close="showDetailModal = false"
      @approve="promptApprove(selectedItem)"
      @reject="openReject(selectedItem)"
    />

    <!-- MODAL GAMBAR (LIGHTBOX) TERPUSAT -->
    <ImagePreviewModal
      :show="showImageModal"
      :images="activeImageList"
      :initial-index="activeImageIndex"
      label="Bukti"
      @close="showImageModal = false"
    />

    <!-- Modal Konfirmasi Setujui Hasil Aktivitas (Dengan penanganan tombol Escape) -->
    <ApproveModal
      :show="showConfirmApproveModal"
      title="Setujui Hasil Aktivitas?"
      info="Status hasil aktivitas mahasiswa ini akan diperbarui menjadi <strong>Disetujui</strong>."
      :is-loading="isLoading"
      @close="showConfirmApproveModal = false"
      @confirm="executeApprove"
    >
      <p class="text-xs sm:text-sm text-[#4d6786] font-inter mb-4 leading-relaxed">
        Apakah Anda yakin ingin menyetujui hasil kegiatan mahasiswa <span class="font-bold text-[#183669]">"{{ itemToApprove?.nama_mahasiswa }}"</span> untuk kegiatan <span class="font-bold text-[#183669]">"{{ itemToApprove?.nama_kegiatan }}"</span>?
      </p>
    </ApproveModal>

    <!-- Modal Penolakan -->
    <RejectModal 
      :show="showRejectModal" 
      :is-approved="String(selectedItem?.status).toLowerCase() === 'disetujui'"
      :is-loading="isLoading"
      @close="showRejectModal = false" 
      @submit="handleReject"
    />

    <!-- MODAL DELETE -->
    <DeleteModal
      :show="showDeleteModal"
      :is-loading="isLoading"
      @close="showDeleteModal = false"
      @confirm="executeDelete"
    >
      <template #title>
        Hapus Hasil Aktivitas?
      </template>
      <template #message>
        Apakah Anda yakin ingin menghapus data hasil aktivitas mahasiswa <span class="font-bold text-[#17334F]">"{{ itemToDelete?.nama_mahasiswa }}"</span>? Tindakan ini tidak dapat dibatalkan.
      </template>
      <template #confirm-text>
        Hapus Hasil
      </template>
    </DeleteModal>

  </AdminLayout>
</template>
<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';
import ApproveModal from '@/Components/dashboard/ApproveModal.vue';
import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

import ModalFormPendaftaranMahasiswa from '@/Components/dashboard/admin/ModalFormPendaftaranMahasiswa.vue';

const props = defineProps({
  pendaftaranData: Object,
  filters: Object
});

// 1. Inisialisasi Filter & Sort
const searchQuery = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');
const sortColumn = ref(props.filters?.sort || 'updated_at');
const sortDirection = ref(props.filters?.direction || 'desc');
const isLoading = ref(false);
const showFilter = ref(false);

const showDetailModal = ref(false);
const showRejectModal = ref(false);
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

// 2. Data Kegiatan Langsung Diambil dari Backend
const kegiatanList = computed(() => {
  return props.pendaftaranData?.data || [];
});

const currentPage = ref(props.pendaftaranData?.current_page || 1);
const rowsPerPage = ref(props.pendaftaranData?.per_page || 10);

const getRowNumber = (index) => {
  if (sortColumn.value === 'id' && sortDirection.value === 'desc') {
    const total = props.pendaftaranData?.total || 0;
    return total - ((currentPage.value - 1) * rowsPerPage.value + index);
  }
  return (props.pendaftaranData?.from || 1) + index;
};

// 3. Fetch ke Backend
const fetchPendaftaran = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;
  router.get(
    '/admin/aktivitas/bukti-pendaftaran',
    {
      search: searchQuery.value || undefined,
      status: filterStatus.value || undefined,
      sort: sortColumn.value || undefined,
      direction: sortDirection.value || undefined,
      per_page: rowsPerPage.value || undefined,
      page: targetPage > 1 ? targetPage : undefined,
    },
    { 
      preserveState: true, 
      replace: true, 
      preserveScroll: true,
      onFinish: () => {
        isLoading.value = false;
      }
    }
  );
};

// Table Columns definition
const columns = [
  { key: 'nama_tim', label: 'Nama Tim', sortable: true, width: 'w-[200px]' },
  { key: 'peserta', label: 'Nama Peserta', sortable: true, width: 'w-[280px]' },
  { key: 'bukti', label: 'Bukti Registrasi', sortable: false, width: 'w-[180px]' },
  { key: 'status', label: 'Status', sortable: false, width: 'w-[150px]' },
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
  fetchPendaftaran(currentPage.value);
};

const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  currentPage.value = newPage;
  fetchPendaftaran(newPage);
};

const handleRowsChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  currentPage.value = 1;
  fetchPendaftaran(1);
};

// 4. Sistem Filter Otomatis (Debounce)
let searchTimeout = null;
watch([searchQuery, filterStatus], () => {
  currentPage.value = 1;
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchPendaftaran(1);
  }, 300);
});

// ==========================================
// FUNGSI MODAL & PREVIEW DOKUMEN/GAMBAR
// ==========================================
const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const openReject = (item) => {
  if (item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const getImageSrc = (gambar) => {
  if (gambar && typeof gambar === 'object' && gambar.url) {
    return gambar.url;
  }
  if (gambar && typeof gambar === 'string') {
    if (gambar.startsWith('http://') || gambar.startsWith('https://') || gambar.startsWith('blob:') || gambar.startsWith('data:')) return gambar;
    if (gambar.startsWith('/storage/')) return gambar;
    if (gambar.startsWith('storage/')) return '/' + gambar;
    if (gambar.startsWith('/')) return gambar;
    return '/storage/' + gambar;
  }
  return `https://picsum.photos/seed/pendaftaran/800/600`;
};

const getBuktiList = (item) => {
  if (!item) return [];
  const val = item.bukti_pendaftaran || item.gambar || item.file;
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
    selectedImage.value = getImageSrc(list[activeImageIndex.value]);
    showImageModal.value = true;
    return;
  }
  selectedImage.value = getImageSrc(target);
  activeImageList.value = [target];
  activeImageIndex.value = 0;
  showImageModal.value = true;
};

const prevImage = () => {
  if (activeImageList.value.length <= 1) return;
  activeImageIndex.value = (activeImageIndex.value - 1 + activeImageList.value.length) % activeImageList.value.length;
  selectedImage.value = getImageSrc(activeImageList.value[activeImageIndex.value]);
};

const nextImage = () => {
  if (activeImageList.value.length <= 1) return;
  activeImageIndex.value = (activeImageIndex.value + 1) % activeImageList.value.length;
  selectedImage.value = getImageSrc(activeImageList.value[activeImageIndex.value]);
};

// Touch swipe support on mobile
const touchStartX = ref(0);
const touchEndX = ref(0);

const handleTouchStart = (e) => {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  touchStartX.value = e.changedTouches[0].clientX;
};

const handleTouchEnd = (e) => {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  touchEndX.value = e.changedTouches[0].clientX;
  const diff = touchEndX.value - touchStartX.value;
  if (Math.abs(diff) > 40 && activeImageList.value.length > 1) {
    if (diff > 0) {
      prevImage();
    } else {
      nextImage();
    }
  }
};

// Hover state for navigation arrows & indicator on desktop
const isHoveringNav = ref(false);

// Keyboard navigation (Esc to close, Arrow keys to slide)
const handleModalKeyDown = (e) => {
  if (!showImageModal.value) return;
  if (e.key === 'Escape') {
    showImageModal.value = false;
  } else if (e.key === 'ArrowLeft') {
    prevImage();
  } else if (e.key === 'ArrowRight') {
    nextImage();
  }
};

watch(showImageModal, (isOpen) => {
  if (isOpen) {
    window.addEventListener('keydown', handleModalKeyDown);
  } else {
    window.removeEventListener('keydown', handleModalKeyDown);
    isHoveringNav.value = false;
  }
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleModalKeyDown);
});

const onImageError = (e) => {
  const seed = String(selectedImage.value || 'talenta').replace(/[^a-zA-Z0-9]/g, '');
  e.target.src = `https://picsum.photos/seed/${seed || 'talenta'}/800/600`;
};

// ==========================================
// FUNGSI AKSI (APPROVE & REJECT) VIA INERTIA
// ==========================================
const showConfirmApproveModal = ref(false);
const itemToApprove = ref(null);

const promptApprove = (item) => {
  const target = item || selectedItem.value;
  if (!target) return;

  if (target.status === 'disetujui') {
    showToast('Pendaftaran ini sudah disetujui sebelumnya.', 'error');
    return;
  }

  itemToApprove.value = target;
  showConfirmApproveModal.value = true;
};

const executeApprove = () => {
  if (!itemToApprove.value) return;
  const targetId = itemToApprove.value.id;

  isLoading.value = true;

  router.patch(`/admin/aktivitas/bukti-pendaftaran/${targetId}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showConfirmApproveModal.value = false;
      showDetailModal.value = false;
      itemToApprove.value = null;
      showToast('Pendaftaran berhasil disetujui!', 'success');
    },
    onError: () => {
      showToast('Gagal menyetujui pendaftaran.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const handleReject = (reason) => {
  if (!selectedItem.value) return;
  const targetId = selectedItem.value.id;

  isLoading.value = true;

  router.patch(`/admin/aktivitas/bukti-pendaftaran/${targetId}/reject`, {
    catatan_penolakan: reason
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false;
      showDetailModal.value = false; 
      showToast('Pendaftaran berhasil ditolak.', 'success');
    },
    onError: (errors) => {
      showToast(errors?.catatan_penolakan || 'Gagal menolak pendaftaran.', 'error');
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

  router.delete(`/admin/aktivitas/bukti-pendaftaran/${targetId}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      itemToDelete.value = null;
      showToast('Data pendaftaran aktivitas berhasil dihapus!', 'success');
    },
    onError: () => {
      showToast('Gagal menghapus pendaftaran aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};
</script>

<template>
  <Head title="Pendaftaran Aktivitas Mahasiswa"/>

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
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Pendaftaran Aktivitas Mahasiswa</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Yuk lihat bukti registrasi dan tentukan apakah bukti tersebut valid atau tidak!</p>
        </div>

        <!-- TOP BAR -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Aktivitas disini" v-model="searchQuery"/>

          <!-- Filter Dropdown Container -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <div class="relative">
              <button @click="showFilter = !showFilter" class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors hover:border-[#8ea9cb] focus:outline-none select-none cursor-pointer" :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee]'">
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                <div v-if="showFilter" class="absolute right-0 top-14 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                  <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
                  <button @click="filterStatus = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium" :class="!filterStatus ? 'bg-gray-100' : ''">Semua Data</button>
                  <button @click="filterStatus = 'menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium" :class="filterStatus === 'menunggu' ? 'bg-gray-100' : ''">Menunggu</button>
                  <button @click="filterStatus = 'disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium" :class="filterStatus === 'disetujui' ? 'bg-green-50' : ''">Disetujui</button>
                  <button @click="filterStatus = 'ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium" :class="filterStatus === 'ditolak' ? 'bg-red-50' : ''">Ditolak</button>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- Overlay penutup filter jika klik di luar -->
        <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

        <!-- TABEL DATA -->
        <div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isLoading ? 'cursor-wait' : '']">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  <th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button type="button" :disabled="isLoading" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85">
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
                <tr v-for="(item, index) in kegiatanList" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ getRowNumber(index) }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_tim">{{ item.nama_tim }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.peserta.map(p => p.nama).join(', ')">
                    {{ item.peserta.map(p => p.nama).join(', ') }}
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <span v-if="getBuktiList(item).length === 0" class="text-xs text-gray-400 italic">-</span>
                    <button 
                      v-else
                      type="button"
                      @click="openImage(item)" 
                      class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer inline-flex items-center justify-center gap-1 align-middle whitespace-nowrap" 
                      :title="getBuktiList(item).length > 1 ? `Lihat ${getBuktiList(item).length} Bukti` : 'Lihat Bukti'"
                    >
                      <span>Bukti</span>
                      <span v-if="getBuktiList(item).length > 1" class="text-xs font-semibold text-[#3b82f6]">({{ getBuktiList(item).length }})</span>
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
                         :class="{
                           'bg-gray-100 text-gray-600 border-gray-200': item.status === 'menunggu',
                           'bg-green-50 text-green-600 border-green-200': item.status === 'disetujui',
                           'bg-red-50 text-red-600 border-red-200': item.status === 'ditolak'
                         }">
                      <svg v-if="item.status === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                      </svg>
                      <svg v-if="item.status === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-if="item.status === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ item.status }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                      <AcceptButton :disabled="item.status === 'disetujui'" @click="promptApprove(item)" />
                      <RejectButton :disabled="item.status === 'ditolak' || item.status === 'disetujui'" @click="openReject(item)" />
                      <PreviewButtonTable @click="openDetail(item)" />
                      <DeleteButtonTable
                        label="Hapus Pendaftaran"
                        title="Hapus Pendaftaran"
                        @click="confirmDelete(item)" 
                      />
                    </div>
                  </td>
                </tr>
                <tr v-if="kegiatanList.length === 0">
                  <td colspan="6" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
            :current-page="pendaftaranData?.current_page || 1" 
            :total-pages="pendaftaranData?.last_page || 1" 
            :rows-per-page="pendaftaranData?.per_page || 10"
            @update:current-page="handlePageChange"
            @update:rows-per-page="handleRowsChange"
        />
      </div>
    </section>

    <!-- MODAL GAMBAR (LIGHTBOX) -->
    <Teleport to="body">
      <Transition
        enter-active-class="ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showImageModal"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all"
          @click="showImageModal = false"
        >
          <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="showImageModal"
              class="relative flex flex-col items-center justify-center bg-transparent max-w-full max-h-full touch-pan-y select-none"
              @click.stop
              @touchstart="handleTouchStart"
              @touchend="handleTouchEnd"
              @mouseenter="isHoveringNav = true"
              @mouseleave="isHoveringNav = false"
            >
              <!-- Close button -->
              <button
                type="button"
                @click="showImageModal = false"
                class="absolute top-3.5 right-3.5 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                title="Tutup Preview"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Prev button (slide in from left on image hover) -->
              <button
                v-if="activeImageList.length > 1"
                type="button"
                @click="prevImage"
                :class="[
                  'absolute left-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
                  isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 -translate-x-6 pointer-events-none'
                ]"
                title="Bukti Sebelumnya"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <!-- Next button (slide in from right on image hover) -->
              <button
                v-if="activeImageList.length > 1"
                type="button"
                @click="nextImage"
                :class="[
                  'absolute right-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
                  isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 translate-x-6 pointer-events-none'
                ]"
                title="Bukti Berikutnya"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </button>

              <!-- Image -->
              <img
                :src="selectedImage"
                alt="Zoomed Preview"
                @error="onImageError"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />

              <!-- Bottom Indicator (slide in from bottom on image hover on PC; always visible on mobile) -->
              <div
                v-if="activeImageList.length > 1"
                :class="[
                  'absolute bottom-4 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 rounded-full bg-black/70 px-3.5 py-1.5 backdrop-blur-sm text-xs font-medium text-white shadow-lg transition-all duration-300 ease-out',
                  isHoveringNav ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-100 sm:opacity-0 sm:translate-y-6 sm:pointer-events-none'
                ]"
              >
                <span>Bukti {{ activeImageIndex + 1 }} dari {{ activeImageList.length }}</span>
                <div class="flex items-center gap-1.5 ml-1">
                  <button
                    v-for="(_, dotIdx) in activeImageList"
                    :key="dotIdx"
                    type="button"
                    @click="activeImageIndex = dotIdx; selectedImage = getImageSrc(activeImageList[dotIdx])"
                    class="h-2 rounded-full transition-all cursor-pointer"
                    :class="activeImageIndex === dotIdx ? 'w-4 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                    :title="'Buka Bukti ' + (dotIdx + 1)"
                  />
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL DETAIL (PERSETUJUAN REGISTRASI) -->
    <ModalFormPendaftaranMahasiswa
      :show="showDetailModal"
      :data="selectedItem"
      @close="showDetailModal = false"
      @approve="promptApprove"
      @reject="openReject"
    />

    <!-- MODAL KONFIRMASI SETUJUI -->
    <ApproveModal
      :show="showConfirmApproveModal"
      title="Setujui Pendaftaran Aktivitas?"
      info="Status pendaftaran tim ini akan diperbarui menjadi <strong>Disetujui</strong>."
      :is-loading="isLoading"
      @close="showConfirmApproveModal = false"
      @confirm="executeApprove"
    >
      <p class="text-xs sm:text-sm text-[#4d6786] font-inter mb-4 leading-relaxed">
        Apakah Anda yakin ingin menyetujui pendaftaran tim <span class="font-bold text-[#183669]">"{{ itemToApprove?.nama_tim }}"</span>?
      </p>
    </ApproveModal>

    <!-- MODAL REJECT -->
    <RejectModal :show="showRejectModal" :is-loading="isLoading" @close="showRejectModal = false" @submit="handleReject"/>

    <!-- MODAL DELETE -->
    <DeleteModal
      :show="showDeleteModal"
      :is-loading="isLoading"
      @close="showDeleteModal = false"
      @confirm="executeDelete"
    >
      <template #title>
        Hapus Pendaftaran?
      </template>
      <template #message>
        Apakah Anda yakin ingin menghapus data pendaftaran tim <span class="font-bold text-[#17334F]">"{{ itemToDelete?.nama_tim }}"</span>? Tindakan ini tidak dapat dibatalkan.
      </template>
      <template #confirm-text>
        Hapus Pendaftaran
      </template>
    </DeleteModal>

  </AdminLayout>
</template>
<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import ActivitySubmissionModal from '@/Components/dashboard/mahasiswa/ActivitySubmissionModal.vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';

// ==========================================
// 1. STATE & DATA DUMMY
// ==========================================
const activeTab = ref('dibuka'); 
const searchQuery = ref('');
const activeCategory = ref('Semua');
const categories = ['Semua', 'Umum', 'Lomba'];

// Data Dummy "Aktivitas yang Dibuka"
const aktivitasDibuka = ref([
  { id: 1, judul: 'Lomba Agustusan MNH', jenis: 'Lomba', deadline: '2026-08-21', deskripsi: 'Menyambut hari kemerdekaan dengan lomba menarik antar mahasiswa.', peserta: 67, gambar: '123.jpg', tags: ['Profesional', 'Bisnis'] },
  { id: 2, judul: 'Seminar Kehutanan', jenis: 'Umum', deadline: '2026-09-10', deskripsi: 'Pemaparan materi kehutanan masa depan dan konservasi alam berkelanjutan.', peserta: 87, gambar: '1234.jpg', tags: ['Akademisi'] },
  { id: 3, judul: 'Hackathon Lingkungan', jenis: 'Lomba', deadline: '2026-10-15', deskripsi: 'Kompetisi membuat aplikasi pelestarian alam dan pemantauan satwa.', peserta: 120, gambar: '1235.jpg', tags: ['Profesional', 'Akademisi'] },
  { id: 4, judul: 'Bakti Sosial Desa', jenis: 'Umum', deadline: '2026-11-01', deskripsi: 'Kegiatan pengabdian masyarakat di desa binaan lingkar kampus.', peserta: 45, gambar: '123.jpg', tags: ['Birokrat'] },
]);

// Data Dummy "Aktivitas yang Diikuti"
const aktivitasDiikuti = ref([
  { id: 101, judul: 'Lomba Bisnis Plan', jenis: 'Lomba', deadline: '2026-08-25', deskripsi: 'Merancang rencana bisnis yang inovatif berbasis kehutanan.', status: 'Menunggu', gambar: '123.jpg', tags: ['Bisnis'] },
  { id: 102, judul: 'Pelatihan Leadership', jenis: 'Umum', deadline: '2026-09-05', deskripsi: 'Membangun karakter pemimpin unggul dan tangguh.', status: 'Menunggu', gambar: '1234.jpg', tags: ['Profesional'] },
  { id: 103, judul: 'Lomba Esai Nasional', jenis: 'Lomba', deadline: '2026-10-20', deskripsi: 'Menulis esai dengan tema teknologi hijau dan restorasi gambut.', status: 'Diterima', gambar: '1235.jpg', tags: ['Akademisi'] },
  { id: 104, judul: 'Workshop Desain Grafis', jenis: 'Umum', deadline: '2026-07-15', deskripsi: 'Belajar desain dasar untuk mahasiswa dan publikasi konten.', status: 'Ditolak', gambar: '123.jpg', catatan_penolakan: 'Format berkas portofolio salah.', tags: ['Profesional'] },
]);

const formatTanggal = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const getCategoryChipClass = (cat) => {
  switch (cat) {
    case 'Profesional':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200';
    case 'Bisnis':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'Birokrat':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'Akademisi':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200';
  }
};

// ==========================================
// 2. STATE FILTER DEADLINE, STATUS & KATEGORI BIDANG
// ==========================================
const showFilter = ref(false);
const filterDeadline = ref(''); // '', 'terdekat', 'terjauh', 'terlewat'
const filterStatus = ref(''); // '', 'Menunggu', 'Diterima', 'Ditolak'
const filterBidang = ref(''); // '', 'Profesional', 'Bisnis', 'Birokrat', 'Akademisi'
const filterButtonRef = ref(null);
const filterPlacement = ref('bottom'); // 'bottom' | 'top'

const isFilterActive = computed(() => {
  if (activeTab.value === 'dibuka') {
    return !!(filterDeadline.value || filterBidang.value);
  } else {
    return !!(filterStatus.value || filterBidang.value);
  }
});

const calculateFilterPlacement = () => {
  if (!filterButtonRef.value) return;
  const rect = filterButtonRef.value.getBoundingClientRect();
  const estimatedHeight = 370;
  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;

  if (spaceBelow < estimatedHeight && spaceAbove > spaceBelow) {
    filterPlacement.value = 'top';
  } else {
    filterPlacement.value = 'bottom';
  }
};

const toggleFilter = () => {
  if (!showFilter.value) {
    calculateFilterPlacement();
    showFilter.value = true;
    nextTick(() => {
      calculateFilterPlacement();
    });
  } else {
    showFilter.value = false;
  }
};

const resetFilters = () => {
  if (activeTab.value === 'dibuka') {
    filterDeadline.value = '';
    filterBidang.value = '';
  } else {
    filterStatus.value = '';
    filterBidang.value = '';
  }
};

// Helper tanggal hari ini dalam format YYYY-MM-DD
const getTodayDateString = () => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// ==========================================
// 3. LOGIC FILTERING & PAGINATION
// ==========================================
const currentPage = ref(1);
const rowsPerPage = ref(8);

const currentList = computed(() => {
  let data = activeTab.value === 'dibuka' ? [...aktivitasDibuka.value] : [...aktivitasDiikuti.value];

  // Filter Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => item.judul.toLowerCase().includes(query) || item.deskripsi.toLowerCase().includes(query));
  }

  // Filter Kategori Tab Bawah (Semua, Umum, Lomba)
  if (activeCategory.value !== 'Semua') {
    data = data.filter(item => item.jenis === activeCategory.value);
  }

  // Filter Bidang / Tag Kategori (Profesional, Bisnis, Birokrat, Akademisi)
  if (filterBidang.value) {
    data = data.filter(item => {
      const itemTags = Array.isArray(item.tags)
        ? item.tags.map(t => typeof t === 'object' ? t.name : t)
        : (typeof item.tags === 'string' ? item.tags.split(',').map(s => s.trim()) : []);
      return itemTags.includes(filterBidang.value);
    });
  }

  // Filter Khusus Tab 1: Aktivitas Dibuka (Urutan & Batas Deadline)
  if (activeTab.value === 'dibuka') {
    const todayStr = getTodayDateString();
    if (filterDeadline.value === 'terdekat') {
      data = data
        .filter(item => item.deadline >= todayStr)
        .sort((a, b) => a.deadline.localeCompare(b.deadline));
    } else if (filterDeadline.value === 'terjauh') {
      data = data
        .filter(item => item.deadline >= todayStr)
        .sort((a, b) => b.deadline.localeCompare(a.deadline));
    } else if (filterDeadline.value === 'terlewat') {
      data = data
        .filter(item => item.deadline < todayStr)
        .sort((a, b) => b.deadline.localeCompare(a.deadline));
    }
  }

  // Filter Khusus Tab 2: Aktivitas Diikuti (Status: Menunggu, Diterima, Ditolak)
  if (activeTab.value === 'diikuti' && filterStatus.value) {
    data = data.filter(item => item.status === filterStatus.value);
  }

  return data;
});

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return currentList.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(currentList.value.length / rowsPerPage.value));
});

watch([activeTab, searchQuery, activeCategory, filterDeadline, filterBidang, filterStatus], () => {
  currentPage.value = 1;
});

// ==========================================
// 4. DYNAMIC TOP-RIGHT CORNER LOGIC (FOLDER TABS)
// ==========================================
const tabsContainerRef = ref(null);
const isTopRightRounded = ref(true);
let tabsResizeObserver = null;

const checkTopRightCorner = () => {
  if (!tabsContainerRef.value) return;
  const container = tabsContainerRef.value;
  const children = container.children;
  if (children.length === 0) return;

  const lastChild = children[children.length - 1];
  const totalWidth = lastChild.offsetLeft + lastChild.offsetWidth;

  isTopRightRounded.value = totalWidth < container.clientWidth - 5;
};

const handleWindowEvents = () => {
  if (showFilter.value) {
    calculateFilterPlacement();
  }
};

onMounted(() => {
  if (tabsContainerRef.value) {
    tabsResizeObserver = new ResizeObserver(() => checkTopRightCorner());
    tabsResizeObserver.observe(tabsContainerRef.value);
  }
  window.addEventListener('resize', handleWindowEvents);
  window.addEventListener('scroll', handleWindowEvents, true);
  setTimeout(() => checkTopRightCorner(), 100);
});

onUnmounted(() => {
  if (tabsResizeObserver && tabsContainerRef.value) {
    tabsResizeObserver.unobserve(tabsContainerRef.value);
  }
  window.removeEventListener('resize', handleWindowEvents);
  window.removeEventListener('scroll', handleWindowEvents, true);
});

// ==========================================
// 5. STATE TOAST, MODAL DELETE & FORM
// ==========================================
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => { toast.value = { show: true, message, type }; setTimeout(() => { toast.value.show = false; }, 3500); }, 50);
};

// Modal Delete
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const openDeleteModal = (item) => { itemToDelete.value = item; showDeleteModal.value = true; };
const executeDelete = () => {
  aktivitasDiikuti.value = aktivitasDiikuti.value.filter(k => k.id !== itemToDelete.value.id);
  showDeleteModal.value = false; itemToDelete.value = null; showToast('Aktivitas berhasil dihapus!', 'success');
};

// Modal Edit
const isModalOpen = ref(false);
const selectedData = ref(null);
const handleEdit = (item) => {
  const tagsList = Array.isArray(item.tags) ? item.tags.map(t => typeof t === 'object' ? t.name : t) : [];
  selectedData.value = { ...item, kategori: tagsList, gambar: [item.gambar] };
  isModalOpen.value = true;
};
const handleModalSubmit = (formData) => {
  const index = aktivitasDiikuti.value.findIndex(k => k.id === formData.id);
  if (index !== -1) {
    aktivitasDiikuti.value[index] = { ...aktivitasDiikuti.value[index], judul: formData.judul, deskripsi: formData.deskripsi, deadline: formData.deadline };
  }
  showToast('Aktivitas berhasil diperbarui!', 'success');
  isModalOpen.value = false;
};

// Helper Format Gambar
const getImageUrl = (img) => img.startsWith('http') ? img : `https://picsum.photos/seed/${img}/400/600`;

const goToDetail = (id) => {
  router.visit('/mahasiswa/aktivitas/pendaftaran');
};
</script>

<template>
  <Head title="List Aktivitas" />

  <MahasiswaLayout>
    <section class="mx-auto w-full max-w-[1520px] px-4 pt-6 pb-24 font-poppins sm:px-6 sm:py-8 lg:px-8">
      <div class="space-y-6">
        
        <!-- TOAST NOTIFICATION -->
        <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
          <div v-if="toast.show" class="fixed top-8 right-8 z-[100]"><ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/></div>
        </Transition>

        <!-- PAGE HEADER -->
        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">
            List Aktivitas
          </h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">
            Yuk temukan aktivitas yang cocok dengan talenta kamu disini!
          </p>
        </div>

        <!-- ========================================== -->
        <!-- TABS & CONTAINER UTAMA (FILE-LIKE DESIGN) -->
        <!-- ========================================== -->
        <div class="w-full max-w-full overflow-visible">
          
          <!-- Folder Header Tabs Row (Seamless with underline indicator) -->
          <div 
            ref="tabsContainerRef"
            class="flex items-end select-none relative z-10 -mb-[1px] overflow-x-auto scrollbar-hide w-full" 
            style="scrollbar-width: none; -ms-overflow-style: none;"
          >
          <!-- Tab 1: Aktivitas yang Dibuka -->
          <button
            type="button"
            @click="activeTab = 'dibuka'"
            :style="{ borderTopLeftRadius: '10px', borderTopRightRadius: '10px' }"
            :class="[
              'relative shrink-0 flex items-center justify-center gap-2.5 px-5 sm:px-6 h-[48px] rounded-t-[10px] font-poppins text-[13px] sm:text-[14px] font-bold transition-all cursor-pointer border-t border-r border-[#d6e0ee] border-l',
              activeTab === 'dibuka'
                ? 'bg-white text-[#183669] border-b border-b-transparent z-20'
                : 'bg-[#f1f5f9] text-[#64748b] hover:bg-[#e2e8f0] hover:text-[#183669] border-b border-b-[#d6e0ee] z-10'
            ]"
          >
            <span class="whitespace-nowrap">Aktivitas yang Dibuka</span>

            <!-- Active Blue Underline Indicator -->
            <span
              v-if="activeTab === 'dibuka'"
              class="absolute -bottom-[1px] -left-[1px] -right-[1px] h-[3px] bg-[#183669] pointer-events-none"
            ></span>
          </button>

          <!-- Tab 2: Aktivitas yang Diikuti -->
          <button
            type="button"
            @click="activeTab = 'diikuti'"
            :style="{ borderTopLeftRadius: '10px', borderTopRightRadius: '10px' }"
            :class="[
              'relative shrink-0 flex items-center justify-center gap-2.5 px-5 sm:px-6 h-[48px] rounded-t-[10px] font-poppins text-[13px] sm:text-[14px] font-bold transition-all cursor-pointer border-t border-r border-[#d6e0ee] -ml-[1px] border-l',
              activeTab === 'diikuti'
                ? 'bg-white text-[#183669] border-b border-b-transparent z-20'
                : 'bg-[#f1f5f9] text-[#64748b] hover:bg-[#e2e8f0] hover:text-[#183669] border-b border-b-[#d6e0ee] z-10'
            ]"
          >
            <span class="whitespace-nowrap">Aktivitas yang Diikuti</span>

            <!-- Active Blue Underline Indicator -->
            <span
              v-if="activeTab === 'diikuti'"
              class="absolute -bottom-[1px] -left-[1px] -right-[1px] h-[3px] bg-[#183669] pointer-events-none"
            ></span>
          </button>
        </div>

        <!-- Main White Card Body (Top right corner dynamic; bottom corners 10px rounded) -->
        <div
          :class="[
            'border border-[#d6e0ee] bg-white p-5 sm:p-7 lg:p-8 shadow-xs font-poppins relative z-0 min-h-[500px]',
            isTopRightRounded ? 'rounded-tr-[10px]' : 'rounded-tr-none',
            'rounded-b-[10px] rounded-tl-none'
          ]"
        >
          
          <!-- TOOLBAR (Search & Filter Tanggal - Selalu 1 Baris di Mobile & Desktop) -->
          <div class="mb-6 flex flex-row items-center gap-2 sm:gap-3 w-full">
            <SearchBarTable v-model="searchQuery" placeholder="Cari Aktivitas disini..." class="flex-1 w-full min-w-0" />
            
            <div class="relative shrink-0" ref="filterButtonRef">
              <button 
                type="button"
                @click="toggleFilter" 
                class="relative z-40 flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer" 
                :class="showFilter || isFilterActive ? 'border-[#183669]' : 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
                title="Filter Aktivitas"
              >
                <img src="/assets/icons/filter.svg" class="h-5 w-5 shrink-0 object-contain pointer-events-none" onerror="this.style.display='none'" />
                <span v-if="isFilterActive" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition 
                enter-active-class="transition duration-150 ease-out" 
                :enter-from-class="filterPlacement === 'top' ? 'transform scale-95 opacity-0 -translate-y-1' : 'transform scale-95 opacity-0 translate-y-1'" 
                enter-to-class="transform scale-100 opacity-100 translate-y-0" 
                leave-active-class="transition duration-100 ease-in" 
                leave-from-class="transform scale-100 opacity-100 translate-y-0" 
                :leave-to-class="filterPlacement === 'top' ? 'transform scale-95 opacity-0 -translate-y-1' : 'transform scale-95 opacity-0 translate-y-1'"
              >
                <div 
                  v-if="showFilter" 
                  :class="[
                    'absolute right-0 z-40 w-72 sm:w-80 max-w-[calc(100vw-32px)] max-h-[min(460px,calc(100vh-100px))] overflow-y-auto rounded-[14px] border border-[#d6e0ee] bg-white p-3.5 sm:p-4 shadow-2xl ring-1 ring-black/10 font-inter',
                    filterPlacement === 'top' ? 'bottom-full mb-2' : 'top-14'
                  ]"
                >
                  <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3 px-0.5 sticky top-0 bg-white z-10">
                    <p class="font-poppins text-xs font-bold text-[#183669]">
                      {{ activeTab === 'dibuka' ? 'Filter Aktivitas Dibuka' : 'Filter Aktivitas Diikuti' }}
                    </p>
                    <button v-if="isFilterActive" @click="resetFilters" class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer">Reset Semua</button>
                  </div>
                  
                  <!-- TAB 1 (AKTIVITAS DIBUKA): ATAS = KATEGORI BIDANG, BAWAH = DEADLINE -->
                  <template v-if="activeTab === 'dibuka'">
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
                          v-for="bidang in ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi']"
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
                  </template>

                  <!-- TAB 2 (AKTIVITAS DIIKUTI): ATAS = STATUS PENGAJUAN, BAWAH = KATEGORI BIDANG -->
                  <template v-else>
                    <!-- SECTION 1 (ATAS): FILTER STATUS -->
                    <div class="mb-3.5">
                      <p class="text-[10.5px] font-bold uppercase tracking-wider text-[#7188a3] mb-1.5 px-1">Status Pengajuan</p>
                      <div class="space-y-1">
                        <button
                          type="button"
                          @click="filterStatus = ''"
                          :class="!filterStatus ? 'bg-[#f0f4f9] font-semibold text-[#183669]' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                          class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                        >
                          <span>Semua Status</span>
                          <svg v-if="!filterStatus" class="h-3.5 w-3.5 text-[#183669]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>

                        <button
                          type="button"
                          @click="filterStatus = 'Menunggu'"
                          :class="filterStatus === 'Menunggu' ? 'bg-[#f0f4f9] font-semibold text-[#183669]' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                          class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                        >
                          <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            <span>Menunggu</span>
                          </div>
                          <svg v-if="filterStatus === 'Menunggu'" class="h-3.5 w-3.5 text-[#183669]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>

                        <button
                          type="button"
                          @click="filterStatus = 'Diterima'"
                          :class="filterStatus === 'Diterima' ? 'bg-green-50/80 font-semibold text-green-700' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                          class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                        >
                          <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>Diterima</span>
                          </div>
                          <svg v-if="filterStatus === 'Diterima'" class="h-3.5 w-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>

                        <button
                          type="button"
                          @click="filterStatus = 'Ditolak'"
                          :class="filterStatus === 'Ditolak' ? 'bg-red-50 font-semibold text-red-700' : 'text-[#475569] hover:bg-[#f8fafc] font-medium'"
                          class="w-full rounded-lg px-2.5 py-1.5 text-left text-[12px] flex items-center justify-between cursor-pointer transition"
                        >
                          <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            <span>Ditolak</span>
                          </div>
                          <svg v-if="filterStatus === 'Ditolak'" class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <!-- SECTION 2 (BAWAH): FILTER BIDANG / KATEGORI TALENTA -->
                    <div class="border-t border-[#f0f4f9] pt-3 mb-3">
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
                          v-for="bidang in ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi']"
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
                  </template>

                  <div class="flex justify-end border-t border-[#f0f4f9] pt-2.5 sticky bottom-0 bg-white">
                    <button type="button" @click="showFilter = false" class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#122b54] cursor-pointer transition">Tutup</button>
                  </div>
                </div>
              </Transition>

              <!-- Overlay backdrop klik di luar -->
              <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>
            </div>
          </div>

          <!-- CATEGORY PILLS -->
          <div class="mb-6 sm:mb-7 flex flex-wrap gap-2 sm:gap-3">
            <button 
              v-for="cat in categories" 
              :key="cat" 
              type="button"
              @click="activeCategory = cat" 
              :class="activeCategory === cat ? 'bg-[#183669] text-white border-[#183669] shadow-sm' : 'bg-transparent text-[#64748b] border-[#d6e0ee] hover:border-[#183669] hover:text-[#183669]'" 
              class="rounded-full px-4 sm:px-5 py-1 sm:py-1.5 text-[11.5px] sm:text-[13px] font-bold transition-all duration-200 border cursor-pointer select-none"
            >
              {{ cat }}
            </button>
          </div>

          <!-- GRID KARTU AKTIVITAS (Rasio 3:4) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            
            <div 
              v-for="item in paginatedList" 
              :key="item.id"
              @click="goToDetail(item.id)"
              class="group relative flex flex-col justify-end overflow-hidden rounded-[12px] sm:rounded-[14px] shadow-sm transition-all duration-300 aspect-[3/4] w-full border border-[#d6e0ee] bg-[#1e293b] sm:hover:-translate-y-1.5 sm:hover:shadow-lg cursor-pointer select-none"
            >
              <!-- BACKGROUND GAMBAR & GRADIENT -->
              <img 
                :src="getImageUrl(item.gambar)" 
                class="absolute inset-0 h-full w-full object-cover" 
                :alt="item.judul"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-[#091e1b] via-[#133830]/80 to-transparent opacity-95"></div>

              <!-- ========================================== -->
              <!-- BADGE UMUM / LOMBA (POJOK KIRI ATAS) -->
              <!-- ========================================== -->
              <div class="absolute left-3.5 top-3.5 z-20">
                <span :class="[
                  'rounded-full px-3 py-1 text-[10px] font-extrabold text-white shadow-sm uppercase tracking-wider',
                  item.jenis === 'Lomba' ? 'bg-[#f59e0b]' : 'bg-[#2563eb]'
                ]">
                  {{ item.jenis }}
                </span>
              </div>

              <!-- ========================================== -->
              <!-- BADGES / ACTIONS (POJOK KANAN ATAS) -->
              <!-- ========================================== -->
              <div class="absolute right-3.5 top-3.5 flex items-center gap-1.5 z-20" @click.stop>
                
                <!-- TAB 1: Aktivitas Dibuka (Badge Peserta) -->
                <div v-if="activeTab === 'dibuka'" class="flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-[11px] font-bold text-[#183669] shadow-sm backdrop-blur-sm">
                  <svg class="h-3.5 w-3.5 text-[#183669]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                  </svg>
                  <span>{{ item.peserta }}</span>
                </div>

                <!-- TAB 2: Aktivitas Diikuti (Status, Edit, Delete) -->
                <template v-if="activeTab === 'diikuti'">
                  <!-- 1. Badge Status (Kiri) - Exact match with ActivitySubmission.vue -->
                  <div
                    class="inline-flex w-fit items-center justify-center gap-1.5 rounded-full px-2.5 py-1 font-inter text-[11px] font-semibold border shadow-xs"
                    :class="{
                      'bg-gray-100 text-gray-600 border-gray-200': item.status === 'Menunggu',
                      'bg-green-50 text-green-600 border-green-200': item.status === 'Diterima' || item.status === 'Disetujui',
                      'bg-red-50 text-red-600 border-red-200': item.status === 'Ditolak'
                    }"
                  >
                    <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="9" />
                      <polyline points="12 7 12 12 15 15" />
                    </svg>
                    <svg v-if="item.status === 'Diterima' || item.status === 'Disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ item.status }}</span>
                  </div>

                  <!-- 2. Tombol Edit (Tengah) -->
                  <EditButtonTable 
                    :disabled="item.status === 'Diterima' || item.status === 'Disetujui'"
                    :label="item.status === 'Diterima' || item.status === 'Disetujui' ? 'Aktivitas yang telah disetujui tidak dapat diedit' : 'Edit Pengajuan'"
                    @click="handleEdit(item)"
                  />

                  <!-- 3. Tombol Delete (Kanan) -->
                  <DeleteButtonTable 
                    :disabled="item.status !== 'Menunggu'"
                    :label="item.status !== 'Menunggu' ? 'Hanya status menunggu yang dapat dihapus' : 'Hapus Pengajuan'"
                    @click="openDeleteModal(item)"
                  />
                </template>

              </div>

              <!-- ========================================== -->
              <!-- KONTEN TEKS KARTU -->
              <!-- ========================================== -->
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

                <p class="mb-2.5 font-inter text-[11px] sm:text-[12px] leading-relaxed line-clamp-2 text-slate-200/90">
                  {{ item.deskripsi }}
                </p>
                
                <div class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="tag in item.tags" 
                    :key="typeof tag === 'object' ? tag.name : tag" 
                    :class="[
                      'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[10.5px] font-semibold border shadow-xs',
                      getCategoryChipClass(typeof tag === 'object' ? tag.name : tag)
                    ]"
                  >
                    {{ typeof tag === 'object' ? tag.name : tag }}
                  </span>
                </div>
              </div>
              
            </div>

            <!-- Empty State -->
            <div v-if="currentList.length === 0" class="col-span-full py-16 text-center text-slate-400 font-inter font-medium">
              Data aktivitas tidak ditemukan.
            </div>

          </div>

          <!-- Pagination -->
          <div class="mt-8 border-t border-[#d6e0ee] pt-4 pb-6 sm:pb-2">
            <TablePagination 
              v-model:currentPage="currentPage" 
              v-model:rowsPerPage="rowsPerPage"
              :totalPages="totalPages" 
              :totalItems="currentList.length"
              :rows-options="[4, 8, 12, 16, 24]"
            />
          </div>

        </div>
      </div>
    </div>
  </section>

    <!-- Modals -->
    <DeleteModal 
      :show="showDeleteModal" 
      title="Hapus Aktivitas?" 
      :message="`Apakah Anda yakin ingin menghapus aktivitas '${itemToDelete?.judul}'? Data yang dihapus tidak dapat dikembalikan.`" 
      @close="showDeleteModal = false" 
      @confirm="executeDelete" 
    />

    <ActivitySubmissionModal 
      :show="isModalOpen" 
      :isEditMode="true" 
      :initialData="selectedData" 
      @close="isModalOpen = false" 
      @submit="handleModalSubmit" 
    />

  </MahasiswaLayout>
</template>
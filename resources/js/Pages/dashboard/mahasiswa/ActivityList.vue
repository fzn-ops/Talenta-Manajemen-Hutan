<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import ActivitySubmissionModal from '@/Components/dashboard/mahasiswa/ActivitySubmissionModal.vue';

// ==========================================
// 1. STATE & DATA DUMMY
// ==========================================
const activeTab = ref('dibuka'); 
const searchQuery = ref('');
const activeCategory = ref('Semua');
const categories = ['Semua', 'Kegiatan', 'Lomba'];

// Data Dummy "Aktivitas yang Dibuka"
const aktivitasDibuka = ref([
  { id: 1, judul: 'Lomba Agustusan MNH', jenis: 'Lomba', deadline: '2026-08-21', deskripsi: 'Menyambut hari kemerdekaan dengan lomba menarik.', peserta: 67, gambar: '123.jpg', tags: [{name: 'Profesional', color: 'text-[#4b857a]'}, {name: 'Bisnis', color: 'text-yellow-600'}] },
  { id: 2, judul: 'Seminar Kehutanan', jenis: 'Kegiatan', deadline: '2026-09-10', deskripsi: 'Pemaparan materi kehutanan masa depan.', peserta: 87, gambar: '1234.jpg', tags: [{name: 'Akademisi', color: 'text-blue-600'}] },
  { id: 3, judul: 'Hackathon Lingkungan', jenis: 'Lomba', deadline: '2026-10-15', deskripsi: 'Kompetisi membuat aplikasi pelestarian alam.', peserta: 120, gambar: '1235.jpg', tags: [{name: 'Profesional', color: 'text-[#4b857a]'}, {name: 'Akademisi', color: 'text-blue-600'}] },
  { id: 4, judul: 'Bakti Sosial Desa', jenis: 'Umum', deadline: '2026-11-01', deskripsi: 'Kegiatan pengabdian masyarakat di desa binaan.', peserta: 45, gambar: '123.jpg', tags: [{name: 'Birokrat', color: 'text-red-600'}] },
]);

// Data Dummy "Aktivitas yang Diikuti" (Dihapus logic kartu putihnya)
const aktivitasDiikuti = ref([
  { id: 101, judul: 'Lomba Bisnis Plan', jenis: 'Lomba', deadline: '2026-08-25', deskripsi: 'Merancang rencana bisnis yang inovatif.', status: 'Menunggu', gambar: '123.jpg', tags: [{name: 'Bisnis', color: 'text-yellow-600'}] },
  { id: 102, judul: 'Pelatihan Leadership', jenis: 'Kegiatan', deadline: '2026-09-05', deskripsi: 'Membangun karakter pemimpin unggul.', status: 'Menunggu', gambar: '1234.jpg', tags: [{name: 'Profesional', color: 'text-[#4b857a]'}] },
  { id: 103, judul: 'Lomba Esai Nasional', jenis: 'Lomba', deadline: '2026-10-20', deskripsi: 'Menulis esai dengan tema teknologi hijau.', status: 'Diterima', gambar: '1235.jpg', tags: [{name: 'Akademisi', color: 'text-blue-600'}] },
  { id: 104, judul: 'Workshop Desain Grafis', jenis: 'Umum', deadline: '2026-07-15', deskripsi: 'Belajar desain dasar untuk mahasiswa.', status: 'Ditolak', gambar: '123.jpg', catatan_penolakan: 'Format berkas portofolio salah.', tags: [{name: 'Profesional', color: 'text-[#4b857a]'}] },
]);

const formatTanggal = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

// ==========================================
// 2. STATE FILTER TANGGAL (PENGGANTI STATUS)
// ==========================================
const showFilter = ref(false);
const filterBulan = ref(''); // Format: YYYY-MM

const resetFilters = () => {
  filterBulan.value = '';
};

// ==========================================
// 3. LOGIC FILTERING
// ==========================================
const currentList = computed(() => {
  let data = activeTab.value === 'dibuka' ? [...aktivitasDibuka.value] : [...aktivitasDiikuti.value];

  // Filter Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => item.judul.toLowerCase().includes(query) || item.deskripsi.toLowerCase().includes(query));
  }

  // Filter Kategori Tab Bawah (Semua, Kegiatan, Lomba)
  if (activeCategory.value !== 'Semua') {
    const catQuery = activeCategory.value === 'Kegiatan' ? ['Kegiatan', 'Umum'] : ['Lomba'];
    data = data.filter(item => catQuery.includes(item.jenis));
  }

  // Filter Bulan (Dropdown filter)
  if (filterBulan.value) {
    // Karena format item.deadline 'YYYY-MM-DD' dan filterBulan 'YYYY-MM', kita bisa pakai startsWith
    data = data.filter(item => item.deadline.startsWith(filterBulan.value));
  }

  return data;
});

// ==========================================
// 4. STATE TOAST, MODAL DELETE & FORM
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
  selectedData.value = { ...item, kategori: item.tags.map(t => t.name), gambar: [item.gambar] };
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

// Helper Format Gambar (Kalau Dummy)
const getImageUrl = (img) => img.startsWith('http') ? img : `https://picsum.photos/seed/${img}/400/600`;

const goToDetail = (id) => {
  router.get('pendaftaran');
};
</script>

<template>
  <Head title="List Aktivitas"/>

  <MahasiswaLayout>
    <div class="p-4 sm:p-8 font-sans bg-[#fcfcfc] min-h-screen relative overflow-hidden">
      
      <!-- TOAST NOTIFICATION -->
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]"><ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/></div>
      </Transition>

      <div class="mb-6 space-y-1.5">
        <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">List Aktivitas</h1>
        <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Yuk temukan aktivitas yang cocok dengan talenta kamu disini!</p>
      </div>

      <!-- ========================================== -->
      <!-- TABS & CONTAINER UTAMA -->
      <!-- ========================================== -->
      <div class="w-full">
        <!-- Tabs Nav -->
        <div class="flex items-end px-2 sm:px-6">
          <button @click="activeTab = 'dibuka'" :class="activeTab === 'dibuka' ? 'bg-white border-t border-l border-r border-gray-200 font-bold text-[#183669] rounded-t-xl z-10 relative -mb-px' : 'bg-transparent text-gray-500 font-medium border-b border-gray-200 hover:text-gray-700'" class="px-6 py-3.5 text-sm sm:text-[15px] transition-colors">
            Aktivitas yang Dibuka
          </button>
          <button @click="activeTab = 'diikuti'" :class="activeTab === 'diikuti' ? 'bg-white border-t border-l border-r border-gray-200 font-bold text-[#183669] rounded-t-xl z-10 relative -mb-px' : 'bg-transparent text-gray-500 font-medium border-b border-gray-200 hover:text-gray-700'" class="px-6 py-3.5 text-sm sm:text-[15px] transition-colors">
            Aktivitas yang Diikuti
          </button>
          <div class="flex-1 border-b border-gray-200"></div>
        </div>

        <!-- White Box Container -->
        <div class="bg-white border border-gray-200 rounded-b-2xl rounded-tr-2xl p-5 sm:p-8 shadow-sm min-h-[500px]">
          
          <!-- TOOLBAR (Search & Filter Tanggal) -->
          <div class="mb-6 flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
            <SearchBarTable v-model="searchQuery" placeholder="Cari Aktivitas disini..." class="flex-1 w-full" />
            
            <div class="relative shrink-0">
              <button @click="showFilter = !showFilter" class="relative z-40 flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer" :class="showFilter || filterBulan ? 'border-[#183669]' : 'border-[#d6e0ee] hover:border-[#8ea9cb]'">
                <img src="/assets/icons/filter.svg" class="h-5 w-5 shrink-0 object-contain pointer-events-none" onerror="this.style.display='none'" />
                <span v-if="filterBulan" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                <div v-if="showFilter" class="absolute right-0 top-14 w-80 rounded-[14px] border border-[#d6e0ee] bg-white p-4 shadow-2xl ring-1 ring-black/10 z-40 font-inter">
                  <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3">
                    <p class="font-poppins text-xs font-bold text-[#183669]">Filter Deadline</p>
                    <button v-if="filterBulan" @click="resetFilters" class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer">Reset Semua</button>
                  </div>
                  
                  <!-- INPUT FILTER BULAN -->
                  <div class="mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#7188a3] mb-2">Pilih Bulan & Tahun</p>
                    <input type="month" v-model="filterBulan" class="w-full rounded-lg border border-[#cbd5e1] px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#183669]" />
                  </div>

                  <div class="flex justify-end border-t border-[#f0f4f9] pt-3">
                    <button @click="showFilter = false" class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#122b54]">Tutup</button>
                  </div>
                </div>
              </Transition>
            </div>
          </div>

          <!-- CATEGORY PILLS -->
          <div class="mb-8 flex flex-wrap gap-2.5 sm:gap-3">
            <button v-for="cat in categories" :key="cat" @click="activeCategory = cat" :class="activeCategory === cat ? 'bg-[#183669] text-white border-[#183669] shadow-sm' : 'bg-transparent text-gray-500 border-gray-300 hover:border-[#183669] hover:text-[#183669]'" class="rounded-full px-5 py-1.5 text-xs sm:text-[13px] font-bold transition-all duration-300 border">
              {{ cat }}
            </button>
          </div>

          <!-- GRID KARTU -->
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:gap-6">
            
            <!-- KARTU AKTIVITAS (Semua pakai background gambar, ga ada yang putih polos) -->
            <div 
              v-for="item in currentList" 
              :key="item.id"
              @click="goToDetail()"
              class="group relative flex flex-col justify-end overflow-hidden rounded-2xl shadow-sm transition-transform duration-300 h-[380px] w-full border border-gray-100 bg-gray-200 hover:-translate-y-2 hover:shadow-xl cursor-pointer"
            >
              
              <!-- BACKGROUND GAMBAR & GRADIENT (Kini tampil di semua kartu) -->
              <img :src="getImageUrl(item.gambar)" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
              <div class="absolute inset-0 bg-gradient-to-t from-[#1b3631] via-[#244b43]/80 to-transparent opacity-90"></div>

              <!-- ========================================== -->
              <!-- BADGE UMUM / LOMBA (POJOK KIRI ATAS) -->
              <!-- ========================================== -->
              <div class="absolute left-3 top-3 z-20">
                <span :class="[
                  'rounded-md px-2.5 py-1 text-[9px] font-extrabold text-white shadow-sm uppercase tracking-wider',
                  item.jenis === 'Lomba' ? 'bg-[#ef4444]' : 'bg-[#3b82f6]'
                ]">
                  {{ item.jenis }}
                </span>
              </div>

              <!-- ========================================== -->
              <!-- BADGES POJOK KANAN ATAS -->
              <!-- ========================================== -->
              <div class="absolute right-3 top-3 flex items-center gap-1.5 z-20">
                
                <!-- TAB 1: Aktivitas Dibuka (Badge Peserta) -->
                <div v-if="activeTab === 'dibuka'" class="flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold text-gray-700 shadow-sm backdrop-blur-sm">
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                  {{ item.peserta }}
                </div>

                <!-- TAB 2: Aktivitas Diikuti (Aksi & Status) -->
                <template v-if="activeTab === 'diikuti'">
                  <!-- Tombol Edit (Kuning) -->
                  <button 
                    @click.stop="item.status !== 'Diterima' ? goToDetail() : null" 
                    :disabled="item.status === 'Diterima'"
                    :class="['flex h-6 w-6 items-center justify-center rounded text-white shadow-sm transition', item.status === 'Diterima' ? 'bg-gray-400/80 cursor-not-allowed' : 'bg-[#fbbf24] hover:bg-[#f59e0b] hover:scale-105']"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                  </button>

                  <!-- Tombol Delete (Merah) -->
                  <button 
                    @click.stop="item.status === 'Menunggu' ? openDeleteModal(item) : null" 
                    :disabled="item.status !== 'Menunggu'"
                    :class="['flex h-6 w-6 items-center justify-center rounded text-white shadow-sm transition', item.status !== 'Menunggu' ? 'bg-gray-400/80 cursor-not-allowed' : 'bg-[#ef4444] hover:bg-[#dc2626] hover:scale-105']"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>

                  <!-- Badge Status -->
                  <div :class="['flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold shadow-sm', 
                    item.status === 'Menunggu' ? 'bg-white/90 text-gray-600 backdrop-blur-sm' : 
                    item.status === 'Diterima' ? 'bg-[#4ade80] text-white' : 'bg-[#f87171] text-white']">
                    <svg v-if="item.status === 'Menunggu'" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" /></svg>
                    <svg v-if="item.status === 'Diterima'" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <svg v-if="item.status === 'Ditolak'" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ item.status }}
                  </div>
                </template>

              </div>

              <!-- ========================================== -->
              <!-- KONTEN TEKS BAWAH (Kini konsisten cerah di semua kartu) -->
              <!-- ========================================== -->
              <div class="relative z-10 p-5 flex flex-col justify-end">
                <div class="mb-1.5 flex items-center gap-1.5 text-[10px] font-medium text-gray-200">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Deadline : {{ formatTanggal(item.deadline) }}
                </div>
                <h3 class="mb-1.5 text-[17px] font-extrabold leading-snug text-white">{{ item.judul }}</h3>
                <p class="mb-4 text-[11px] leading-relaxed line-clamp-3 text-gray-300">{{ item.deskripsi }}</p>
                
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="tag in item.tags" :key="tag.name" :class="[tag.color, 'rounded-full px-2.5 py-0.5 text-[9px] font-extrabold tracking-wide shadow-sm bg-white']">
                    {{ tag.name }}
                  </span>
                </div>
              </div>
              
            </div>

            <!-- Empty State -->
            <div v-if="currentList.length === 0" class="col-span-full py-12 text-center text-gray-500 font-medium">
              Data aktivitas tidak ditemukan.
            </div>

          </div>

          <TablePagination :links="[]" :currentPage="1" :rowsPerPage="10" class="mt-10"/>

        </div>
      </div>
    </div>

    <!-- Modals -->
    <DeleteModal :show="showDeleteModal" title="Hapus Aktivitas?" :message="`Apakah Anda yakin ingin menghapus aktivitas '${itemToDelete?.judul}'? Data yang dihapus tidak dapat dikembalikan.`" @close="showDeleteModal = false" @confirm="executeDelete" />

    <ActivitySubmissionModal 
      :show="isModalOpen" 
      :isEditMode="true" 
      :initialData="selectedData" 
      @close="isModalOpen = false" 
      @submit="handleModalSubmit" 
    />

  </MahasiswaLayout>
</template>
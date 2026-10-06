<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

// Layout diubah jadi MahasiswaLayout
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';

import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';

import ActivitySubmissionModal from '@/Components/dashboard/mahasiswa/ActivitySubmissionModal.vue';

const aktivitasList = ref([
  { id: 1, judul: 'Pelatihan Manajemen Risiko', kategori: 'Profesional, Bisnis', deadline: '2029-12-10', gambar: ['123.jpg', '1234.jpg', '1235.jpg'], status: 'Menunggu', deskripsi: 'Pelatihan untuk manager...', catatan_penolakan: '' },
  { id: 2, judul: 'Seminar Teknologi', kategori: 'Akademisi', deadline: '2029-12-11', gambar: ['123.jpg'], status: 'Ditolak', deskripsi: 'Seminar IT tingkat nasional...', catatan_penolakan: 'Gambar bukti kurang jelas, mohon dilampirkan ulang dengan resolusi tinggi.' },
  { id: 3, judul: 'Lomba Bisnis Plan', kategori: 'Bisnis', deadline: '2029-12-12', gambar: ['123.jpg', '1234.jpg'], status: 'Diterima', deskripsi: 'Lomba tingkat mahasiswa...', catatan_penolakan: '' },
]);

const formatTanggal = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

const searchQuery = ref('');
const showFilter = ref(false);

const filterStatus = ref('');
const isStatusDropdownOpen = ref(false);
const statusOptions = [
  { label: 'Semua Status', value: '' },
  { label: 'Menunggu', value: 'Menunggu' },
  { label: 'Diterima', value: 'Diterima' },
  { label: 'Ditolak', value: 'Ditolak' }
];

const sortColumn = ref('');
const sortDirection = ref('asc');

const handleSort = (column) => {
  if (sortColumn.value === column) sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  else { sortColumn.value = column; sortDirection.value = 'asc'; }
};

const resetFilters = () => {
  filterStatus.value = '';
  isStatusDropdownOpen.value = false;
};

const processedAktivitas = computed(() => {
  let data = [...aktivitasList.value];
  if (searchQuery.value) data = data.filter(i => i.judul.toLowerCase().includes(searchQuery.value.toLowerCase()));
  if (filterStatus.value) data = data.filter(i => i.status === filterStatus.value);
  if (sortColumn.value) {
    data.sort((a, b) => {
      let valA = a[sortColumn.value]; let valB = b[sortColumn.value];
      if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
      if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
      return 0;
    });
  }
  return data;
});

const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => { toast.value = { show: true, message, type }; setTimeout(() => { toast.value.show = false; }, 3500); }, 50);
};

const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const openDeleteModal = (item) => { itemToDelete.value = item; showDeleteModal.value = true; };
const executeDelete = () => {
  aktivitasList.value = aktivitasList.value.filter(k => k.id !== itemToDelete.value.id);
  showDeleteModal.value = false; itemToDelete.value = null; showToast('Pengajuan berhasil dihapus!', 'success');
};

const showImageModal = ref(false);
const selectedImage = ref('');
const openImage = (gambarArray) => {
  const gmb = Array.isArray(gambarArray) ? gambarArray[0] : gambarArray;
  selectedImage.value = (typeof gmb === 'object') ? URL.createObjectURL(gmb) : (gmb.startsWith('http') ? gmb : `https://picsum.photos/seed/${gmb}/800/600`);
  showImageModal.value = true;
};

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedData = ref(null);

const openTambah = () => {
  selectedData.value = null;
  isEditMode.value = false;
  isModalOpen.value = true;
};

const handleEdit = (item) => {
  selectedData.value = { ...item };
  isEditMode.value = true;
  isModalOpen.value = true;
};

const handleModalSubmit = (formData) => {
  if (isEditMode.value) {
    const index = aktivitasList.value.findIndex(k => k.id === formData.id);
    if (index !== -1) {
      const katString = Array.isArray(formData.kategori) ? formData.kategori.join(', ') : formData.kategori;
      aktivitasList.value[index] = { ...formData, kategori: katString };
    }
    showToast('Pengajuan berhasil diperbarui!', 'success');
  } else {
    const newId = aktivitasList.value.length ? Math.max(...aktivitasList.value.map(k => k.id)) + 1 : 1;
    const katString = Array.isArray(formData.kategori) ? formData.kategori.join(', ') : formData.kategori;
    aktivitasList.value.unshift({ ...formData, id: newId, kategori: katString, status: 'Menunggu' });
    showToast('Pengajuan berhasil ditambahkan!', 'success');
  }
  isModalOpen.value = false;
};
</script>

<template>
  <Head title="Pengajuan Aktivitas"/>

  <MahasiswaLayout>
    <div class="font-sans bg-[#fcfcfc] min-h-screen relative overflow-hidden px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
      
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]"><ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/></div>
      </Transition>

      <div class="mb-6 flex flex-col gap-5">
        
        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Pengajuan Aktivitas</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Kamu punya aktivitas lain? Yuk ajukan disini!</p>
        </div>

        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
          <SearchBarTable v-model="searchQuery" placeholder="Cari Aktivitas disini..." class="flex-1 w-full" />
          
          <div class="flex items-center justify-end gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
            <!-- Filter Box -->
            <div class="relative">
              <div v-if="showFilter" @click="showFilter = false; isStatusDropdownOpen = false" class="fixed inset-0 z-30"></div>

              <button @click="showFilter = !showFilter" class="relative z-40 flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer" :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee] hover:border-[#8ea9cb]'">
                <img src="/assets/icons/filter.svg" class="h-5 w-5 shrink-0 object-contain pointer-events-none" onerror="this.style.display='none'" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>
              
              <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                <div v-if="showFilter" class="absolute right-0 top-14 w-80 rounded-[14px] border border-[#d6e0ee] bg-white p-4 shadow-2xl ring-1 ring-black/10 z-40 font-inter">
                  <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3">
                    <p class="font-poppins text-xs font-bold text-[#183669]">Filter Status</p>
                    <button v-if="filterStatus" @click="resetFilters" class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer">Reset Semua</button>
                  </div>
                  
                  <div class="mb-4 relative">
                    <div v-if="isStatusDropdownOpen" @click="isStatusDropdownOpen = false" class="fixed inset-0 z-40"></div>
                    
                    <button type="button" @click="isStatusDropdownOpen = !isStatusDropdownOpen" class="relative z-50 flex w-full items-center justify-between rounded-lg border border-[#cbd5e1] bg-white px-3 py-2.5 text-xs text-gray-700 outline-none transition-colors hover:border-[#183669] focus:border-[#183669] cursor-pointer">
                      <span class="font-medium">{{ filterStatus === '' ? 'Semua Status' : filterStatus }}</span>
                      <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': isStatusDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                      <ul v-if="isStatusDropdownOpen" class="absolute left-0 right-0 z-50 mt-1 max-h-48 overflow-y-auto rounded-lg border border-[#cbd5e1] bg-white py-1.5 shadow-xl font-inter">
                        <li v-for="opt in statusOptions" :key="opt.value" @click="filterStatus = opt.value; isStatusDropdownOpen = false" class="cursor-pointer px-3 py-2 text-xs transition-colors hover:bg-[#f0f4f9] hover:text-[#183669]" :class="filterStatus === opt.value ? 'bg-[#f0f4f9] font-bold text-[#183669]' : 'text-gray-700 font-medium'">
                          {{ opt.label }}
                        </li>
                      </ul>
                    </Transition>
                  </div>

                  <div class="flex justify-end border-t border-[#f0f4f9] pt-3"><button @click="showFilter = false; isStatusDropdownOpen = false" class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#122b54]">Tutup</button></div>
                </div>
              </Transition>
            </div>

            <!-- TOMBOL TAMBAH DIPINDAHKAN KE SINI (SAMPING KANAN FILTER) -->
            <button type="button" @click="openTambah" class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer">
              <span class="hidden sm:inline">Tambah</span>
              <svg class="h-5 w-5 sm:hidden shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            </button>
          </div>

        </div>
      </div>

      <!-- TABEL DATA -->
      <div class="overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-sm mb-6">
        <div class="overflow-x-auto">
          <table class="w-full text-center text-sm whitespace-nowrap">
            <thead class="bg-[#48796f] text-white select-none">
              <tr>
                <th class="px-4 py-4 font-semibold w-16">No</th>
                <th class="px-4 py-4 font-semibold max-w-[200px]">Judul</th>
                <th class="px-4 py-4 font-semibold max-w-[200px]">Kategori</th>
                <th class="px-4 py-4 font-semibold w-40">
                  <button type="button" @click="handleSort('deadline')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                    <span>Deadline</span>
                    <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                      <svg v-if="sortColumn === 'deadline'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                    </span>
                  </button>
                </th>
                <th class="px-4 py-4 font-semibold max-w-[150px]">Gambar</th>
                <th class="px-4 py-4 font-semibold w-36">Status</th>
                <th class="px-4 py-4 font-semibold w-40">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-gray-600">
              <tr v-for="(item, index) in processedAktivitas" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50/70 transition-colors">
                <td class="px-4 py-4">{{ index + 1 }}</td>
                <td class="px-4 py-4 font-medium text-[#233547] max-w-[200px] truncate" :title="item.judul">{{ item.judul }}</td>
                <td class="px-4 py-4 max-w-[200px] truncate" :title="item.kategori">{{ item.kategori }}</td>
                <td class="px-4 py-4">{{ formatTanggal(item.deadline) }}</td>
                <td class="px-4 py-4 max-w-[150px] truncate">
                  <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium underline">
                    {{ Array.isArray(item.gambar) ? item.gambar.map(g => (typeof g === 'object' ? g.name : g)).join(', ') : item.gambar }}
                  </button>
                </td>
                <td class="px-4 py-4">
                  <div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold shadow-sm" :class="{ 'bg-[#f4f4f5] text-[#52525b] border border-[#e4e4e7]': item.status === 'Menunggu', 'bg-[#4ade80] text-white': item.status === 'Diterima', 'bg-[#f87171] text-white': item.status === 'Ditolak' }">
                    <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" /></svg>
                    <svg v-if="item.status === 'Diterima'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ item.status }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <button v-if="!(item.status === 'Menunggu' || item.status === 'Ditolak')" disabled class="flex h-7 w-7 items-center justify-center rounded-md bg-gray-300 text-white cursor-not-allowed shadow-sm"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                    <EditButtonTable v-else @click="handleEdit(item)" />
                    <button v-if="item.status !== 'Menunggu'" disabled class="flex h-7 w-7 items-center justify-center rounded-md bg-gray-300 text-white cursor-not-allowed shadow-sm"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    <DeleteButtonTable v-else @click="openDeleteModal(item)" />
                  </div>
                </td>
              </tr>
              <tr v-if="processedAktivitas.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 font-medium">Data pengajuan tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <TablePagination :links="[]" :currentPage="1" :rowsPerPage="10"/>
    </div>

    <DeleteModal :show="showDeleteModal" title="Hapus Pengajuan?" :message="`Apakah Anda yakin ingin menghapus pengajuan '${itemToDelete?.judul}'? Data yang dihapus tidak dapat dikembalikan.`" @close="showDeleteModal = false" @confirm="executeDelete" />

    <ActivitySubmissionModal 
      :show="isModalOpen" 
      :isEditMode="isEditMode" 
      :initialData="selectedData" 
      @close="isModalOpen = false" 
      @submit="handleModalSubmit" 
    />

  </MahasiswaLayout>

  <!-- MODAL GAMBAR (LIGHTBOX) -->
  <Teleport to="body">
    <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showImageModal" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all" @click="showImageModal = false">
        <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
          <div v-if="showImageModal" class="relative flex items-center justify-center bg-transparent" @click.stop>
            <button type="button" @click="showImageModal = false" class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
            <img :src="selectedImage" alt="Zoomed Preview" class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl" />
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>

</template>
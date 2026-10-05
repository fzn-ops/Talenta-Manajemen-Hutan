<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';
import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';

// 10 Data Dummy Sesuai Gambar
const kegiatan = ref([
  { id: 1, judul_aktivitas: 'Lorem ipsum dolor sit amet', nama_tim: 'Tim Biasa Aja', peserta: ['Fauzan Fuadiansyah - J0403231085', 'Farhan Hakim - J0403231075', 'Rintan Arufafa Aji - J0403231113'], kategori: ['Bisnis', 'Akademisi', 'Birokrat'], gambar: '123.jpeg', status: 'Menunggu', alasan_tolak: '' },
  { id: 2, judul_aktivitas: 'Lomba Cerdas Cermat IT', nama_tim: 'Tim Tech Gen', peserta: ['Budi Santoso - J0403231001', 'Andi Wijaya - J0403231002'], kategori: ['Bisnis'], gambar: 'bukti.jpeg', status: 'Menunggu', alasan_tolak: '' },
  { id: 3, judul_aktivitas: 'Hackathon Nasional 2026', nama_tim: 'White Hat ID', peserta: ['Siti Aminah - J0403231011', 'Dewi Lestari - J0403231012'], kategori: ['Profesional', 'Akademisi'], gambar: 'transfer.jpeg', status: 'Menunggu', alasan_tolak: '' },
  { id: 4, judul_aktivitas: 'Seminar Bisnis Digital', nama_tim: 'Creative Hub', peserta: ['Rizky Febrian - J0403231021'], kategori: ['Birokrat'], gambar: 'resi-456.jpeg', status: 'Menunggu', alasan_tolak: '' },
  { id: 5, judul_aktivitas: 'Kompetisi Startup Kampus', nama_tim: 'Robo Mania', peserta: ['Deni Setiawan - J0403231031', 'Eko Prasetyo - J0403231032'], kategori: ['Akademisi', 'Birokrat'], gambar: 'bukti-bayar.jpeg', status: 'Menunggu', alasan_tolak: '' },
  { id: 6, judul_aktivitas: 'Pelatihan Cyber Security', nama_tim: 'Pena Muda', peserta: ['Nurul Hidayah - J0403231041'], kategori: ['Bisnis'], gambar: 'esai.jpeg', status: 'Disetujui', alasan_tolak: '' },
  { id: 7, judul_aktivitas: 'Pekan Olahraga Mahasiswa', nama_tim: 'Jaringan Ngebut', peserta: ['Hendra Gunawan - J0403231051', 'Ahmad Fauzi - J0403231053'], kategori: ['Profesional'], gambar: 'mikrotik.jpeg', status: 'Ditolak', alasan_tolak: 'Kurang Mantap' },
  { id: 8, judul_aktivitas: 'Lomba Debat Bahasa Inggris', nama_tim: 'Code Builder', peserta: ['Taufik Hidayat - J0403231061', 'Sri Wahyuni - J0403231062'], kategori: ['Akademisi'], gambar: 'hackathon.jpeg', status: 'Menunggu', alasan_tolak: '' },
  { id: 9, judul_aktivitas: 'Olimpiade Matematika', nama_tim: 'Bakti Nusa', peserta: ['Joko Susilo - J0403231071', 'Wawan Setiawan - J0403231072'], kategori: ['Bisnis', 'Birokrat'], gambar: 'olimpiade.jpeg', status: 'Disetujui', alasan_tolak: '' },
  { id: 10, judul_aktivitas: 'Festival Seni Budaya', nama_tim: 'Cloud Native', peserta: ['Faisal Akbar - J0403231081'], kategori: ['Profesional', 'Birokrat'], gambar: 'budaya.jpeg', status: 'Menunggu', alasan_tolak: '' },
]);

const searchQuery = ref('');
const filterStatus = ref('');
const showFilter = ref(false);

const sortColumn = ref('id');
const sortDirection = ref('asc');

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

// Fungsi Sortir
const handleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const processedKegiatan = computed(() => {
  let data = [...kegiatan.value];

  if (filterStatus.value) {
    data = data.filter(item => item.status === filterStatus.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => 
      item.nama_tim.toLowerCase().includes(query) ||
      item.peserta.join(', ').toLowerCase().includes(query)
    );
  }

  if (sortColumn.value) {
    data.sort((a, b) => {
      let valA = a[sortColumn.value];
      let valB = b[sortColumn.value];

      if (sortColumn.value === 'peserta') {
        valA = a.peserta.join(', ');
        valB = b.peserta.join(', ');
      }

      if (typeof valA === 'string') valA = valA.toLowerCase();
      if (typeof valB === 'string') valB = valB.toLowerCase();

      if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
      if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return data;
});

const currentPage = ref(1);
const rowsPerPage = ref(10);

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

const paginatedKegiatan = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return processedKegiatan.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.ceil(processedKegiatan.value.length / rowsPerPage.value));

watch([searchQuery, filterStatus, rowsPerPage], () => {
  currentPage.value = 1;
});

const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const openReject = (item) => {
  if(item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const openImage = (gambar) => {
  selectedImage.value = gambar;
  showImageModal.value = true;
};

const handleApprove = () => {
  showDetailModal.value = false;
  showToast('Pendaftaran berhasil disetujui!', 'success');
};

const handleReject = (reason) => {
  showRejectModal.value = false;
  showDetailModal.value = false; 
  showToast(`Pendaftaran ditolak. Alasan: ${reason || 'Tidak ada'}`, 'error');
};
</script>

<template>
  <Head title="Daftar Pendaftaran Mahasiswa"/>

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
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Daftar Pendaftaran Mahasiswa</h1>
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
                  <button @click="filterStatus = 'Menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium" :class="filterStatus === 'Menunggu' ? 'bg-gray-100' : ''">Menunggu</button>
                  <button @click="filterStatus = 'Disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium" :class="filterStatus === 'Disetujui' ? 'bg-green-50' : ''">Disetujui</button>
                  <button @click="filterStatus = 'Ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium" :class="filterStatus === 'Ditolak' ? 'bg-red-50' : ''">Ditolak</button>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- Overlay penutup filter jika klik di luar -->
        <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

        <!-- TABEL DATA -->
        <div class="overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  <th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button type="button" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[200px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('nama_tim')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Nama Tim</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'nama_tim'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[280px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('peserta')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Nama Peserta</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'peserta'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[180px] px-2 px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Bukti Registrasi</th>
                  <th class="w-[150px] px-2 px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Status</th>
                  <th class="w-[140px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedKegiatan" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_tim">{{ item.nama_tim }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.peserta.join(', ')">{{ item.peserta.join(', ') }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium">
                      {{ item.gambar }}
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border"
                         :class="{
                           'bg-gray-100 text-gray-600 border-gray-200': item.status === 'Menunggu',
                           'bg-green-50 text-green-600 border-green-200': item.status === 'Disetujui',
                           'bg-red-50 text-red-600 border-red-200': item.status === 'Ditolak'
                         }">
                      <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                      </svg>
                      <svg v-if="item.status === 'Disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ item.status }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <AcceptButton @click="handleApprove(item)" />
                      <RejectButton @click="openReject(item)" />
                      <PreviewButtonTable @click="openDetail(item)" />
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedKegiatan.length === 0">
                  <td colspan="6" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
          :current-page="currentPage" 
          :total-pages="totalPages" 
          :rows-per-page="rowsPerPage"
          @update:current-page="currentPage = $event"
          @update:rows-per-page="rowsPerPage = $event; currentPage = 1"
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
              class="relative flex items-center justify-center bg-transparent"
              @click.stop
            >
              <button
                type="button"
                @click="showImageModal = false"
                class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                title="Tutup Preview"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <img
                :src="`https://picsum.photos/seed/${selectedImage}/800/600`"
                alt="Zoomed Preview"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL DETAIL (PERSETUJUAN REGISTRASI) -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm md:p-8">
        <div class="absolute inset-0" @click="showDetailModal = false"></div>
        <Transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-4 scale-95">
          <div v-if="showDetailModal" class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 shadow-2xl">
            
            <h2 class="mb-8 text-2xl font-bold text-[#1a2b4c]">Persetujuan Registrasi Aktivitas</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
              <div class="flex flex-col gap-5">
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Judul Aktivitas<span class="text-red-500">*</span></label>
                  <input type="text" readonly :value="selectedItem?.judul_aktivitas" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Nama Tim<span class="text-red-500">*</span></label>
                  <input type="text" readonly :value="selectedItem?.nama_tim" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Nama Peserta<span class="text-red-500">*</span></label>
                  <div class="flex flex-col gap-3">
                    <input v-for="(org, i) in selectedItem?.peserta" :key="i" type="text" readonly :value="org" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                  </div>
                </div>
                <div>
                  <label class="mb-3 block text-sm font-bold text-[#1a2b4c]">Kategori<span class="text-red-500">*</span></label>
                  <div class="flex flex-wrap gap-5 text-xs font-semibold text-gray-500">
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Profesional')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Professional</label>
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Bisnis')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Bisnis</label>
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Akademisi')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Akademisi</label>
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Birokrat')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Birokrat</label>
                  </div>
                </div>
              </div>

              <div class="flex flex-col gap-6">
                <div>
                  <label class="mb-2 block text-sm font-bold text-[#1a2b4c]">Gambar<span class="text-red-500">*</span></label>
                  <div @click="openImage(selectedItem?.gambar || 'dummy.jpeg')" class="flex h-[220px] w-full items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-5 cursor-pointer group transition hover:bg-gray-50">
                    <div class="flex h-36 w-36 flex-shrink-0 items-center justify-center rounded-lg bg-gray-300 group-hover:scale-105 transition-all shadow-sm">
                      <svg class="h-14 w-14 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Catatan Penolakan Sebelumnya</label>
                  <p class="mb-2 text-[10px] text-gray-400">Berikut adalah catatan penolakan sebelumnya (Catatan kosong apabila submisi belum pernah ditolak)</p>
                  <div class="w-full rounded-lg border border-red-300 bg-[#fecaca] p-4 text-sm font-medium text-red-600 min-h-[120px]">
                    {{ selectedItem?.status === 'Ditolak' ? (selectedItem?.alasan_tolak || 'Data tidak lengkap.') : '' }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-10 flex justify-end gap-4">
              <button @click="showDetailModal = false" class="rounded-lg border border-gray-300 bg-white px-8 py-2.5 text-sm font-bold text-gray-500 transition hover:bg-gray-50 hover:text-gray-700">Kembali</button>
              <button @click="handleApprove" class="rounded-lg bg-[#51d28c] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-[#3fb874] hover:shadow-md">Setuju</button>
              <button @click="openReject(null)" class="rounded-lg bg-[#ef4444] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-red-600 hover:shadow-md">Tolak</button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <RejectModal :show="showRejectModal" @close="showRejectModal = false" @submit="handleReject"/>

  </AdminLayout>
</template>
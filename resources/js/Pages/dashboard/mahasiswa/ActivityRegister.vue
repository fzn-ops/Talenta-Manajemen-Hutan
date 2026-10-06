<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import LoadingSuccessModal from '@/Components/dashboard/mahasiswa/LoadingSuccessModal.vue';

// ==========================================
// 1. DATA DUMMY & USER STATE
// ==========================================
const page = usePage();
const currentUser = computed(() => {
  const user = page.props.auth?.user;
  return { name: user?.name || 'Fauzan Fuadiansyah', nim: user?.nim || 'J0403231085' };
});

const activity = ref({
  id: 1,
  judul: 'Lomba Agustusan MNH',
  deadline: '16 Agustus 2026',
  kategori: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
  deskripsi: [
    'Lorem ipsum dolor sit amet, cupidatat eiusmod duis ut. Magna dolore dolor ex elit sed non cillum do aliqua adipiscing ad.',
    'Commodo in non exercitation nulla enim qui aliquip nulla. Esse adipiscing ex anim fugiat labore mollit mollit.',
    'Velit quis aliqua sed dolore excepteur irure cillum labore duis.'
  ],
  link_pendaftaran: 'https://ipb.link/daftar-lomba-agustusan-manhut-26',
  gambar_utama: 'poster.jpg',
  thumbnails: [null, null, null]
});

// ==========================================
// 2. STATE FORM REGISTRASI & AUTOCOMPLETE
// ==========================================
const form = ref({
  nama_tim: '',
  peserta: [`${currentUser.value.name} - ${currentUser.value.nim}`], 
  bukti: null
});

const errors = ref({ nama_tim: '', peserta: [''], bukti: '' });

const dummyStudents = [
  { name: 'Fauzan Fuadiansyah', nim: 'J0403231085' },
  { name: 'Rio Ferddinansya', nim: 'J0403231086' },
  { name: 'Budi Santoso', nim: 'J0403231087' },
  { name: 'Siti Aminah', nim: 'J0403231088' },
  { name: 'Ahmad Maulana', nim: 'J0403231089' },
  { name: 'Kevin Pratama', nim: 'J0403231090' },
];

const activeDropdown = ref(null);

const getFilteredStudents = (query) => {
  if (!query) return dummyStudents;
  const lowerQuery = query.toLowerCase();
  return dummyStudents.filter(s => s.name.toLowerCase().includes(lowerQuery) || s.nim.toLowerCase().includes(lowerQuery));
};

const selectStudent = (index, student) => {
  form.value.peserta[index] = `${student.name} - ${student.nim}`;
  activeDropdown.value = null;
  errors.value.peserta[index] = '';
};

const addPeserta = () => {
  form.value.peserta.push('');
  errors.value.peserta.push('');
};

const removePeserta = (index) => {
  if (form.value.peserta.length > 1) {
    form.value.peserta.splice(index, 1);
    errors.value.peserta.splice(index, 1);
  }
};

// ==========================================
// 3. LOGIC UPLOAD FILE (DRAG & DROP)
// ==========================================
const fileInput = ref(null);
const imagePreview = ref(null);
const isDragging = ref(false); // State buat nandain lagi narik gambar atau nggak

const triggerUpload = () => fileInput.value.click();

// Fungsi inti buat proses gambar (dipakai di Klik maupun Drop)
const processFile = (file) => {
  if (!file) return;

  // Validasi tipe gambar
  if (!file.type.startsWith('image/')) {
    showToast('File harus berupa gambar (JPG/PNG)!', 'error');
    return;
  }

  // Validasi ukuran (Max 10MB)
  if (file.size > 10 * 1024 * 1024) {
    showToast('Ukuran gambar maksimal 10MB!', 'error');
    return;
  }

  form.value.bukti = file;
  imagePreview.value = URL.createObjectURL(file);
  errors.value.bukti = ''; 
};

// Ketika gambar dipilih via Klik
const handleFileUpload = (event) => {
  processFile(event.target.files[0]);
};

// Ketika gambar dilepas (Drop)
const handleDrop = (event) => {
  isDragging.value = false;
  processFile(event.dataTransfer.files[0]);
};

// ==========================================
// 4. TOAST, VALIDASI & SUBMIT LOGIC
// ==========================================
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => { toast.value = { show: true, message, type }; setTimeout(() => { toast.value.show = false; }, 3500); }, 50);
};

const isFeedbackModalOpen = ref(false);
const feedbackStatus = ref('loading');

const validateForm = () => {
  let isValid = true;
  errors.value.nama_tim = '';
  errors.value.bukti = '';
  errors.value.peserta = form.value.peserta.map(() => ''); 

  if (!form.value.nama_tim.trim()) {
    errors.value.nama_tim = 'Kolom nama tim harus diisi!';
    isValid = false;
  }

  form.value.peserta.forEach((p, index) => {
    if (!p.trim()) {
      errors.value.peserta[index] = 'Kolom nama peserta harus diisi!';
      isValid = false;
    }
  });

  if (!form.value.bukti) {
    errors.value.bukti = 'Bukti pendaftaran wajib diupload!';
    isValid = false;
  }

  return isValid;
};

const submitRegistration = () => {
  if (!validateForm()) {
    showToast('Mohon lengkapi form yang bertanda merah!', 'error');
    return;
  }
  
  isFeedbackModalOpen.value = true;
  feedbackStatus.value = 'loading';

  setTimeout(() => {
    console.log('Data Register:', form.value);
    feedbackStatus.value = 'success';
  }, 1500);
};

const handleFeedbackClose = () => {
  isFeedbackModalOpen.value = false;
  router.get('/mahasiswa/aktivitas/list');
};
</script>

<template>
  <Head :title="activity.judul" />

  <MahasiswaLayout>
    <div class="min-h-screen bg-[#fafafb] p-4 sm:p-8 font-sans">
      
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]"><ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/></div>
      </Transition>

      <div class="mx-auto max-w-7xl">
        
        <!-- BREADCRUMB -->
        <div class="mb-6 flex items-center text-sm sm:text-[15px] text-gray-800">
          <Link href="/list-aktivitas" class="font-medium hover:underline hover:text-[#183669]">List Aktivitas</Link>
          <span class="mx-2">/</span>
          <span class="font-bold border-b border-black pb-0.5">{{ activity.judul }}</span>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[400px_1fr] xl:gap-12">
          
          <!-- KOLOM KIRI (GAMBAR & THUMBNAILS) -->
          <div class="flex flex-col gap-4">
            <div class="aspect-[3/4] w-full overflow-hidden rounded-xl bg-gray-200 shadow-sm border border-gray-100 relative">
              <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop" class="h-full w-full object-cover" alt="Poster Lomba" />
            </div>
            <div class="grid grid-cols-3 gap-3">
              <div v-for="(thumb, index) in activity.thumbnails" :key="index" class="aspect-square w-full rounded-lg bg-[#d9d9d9] shadow-sm">
                <img v-if="thumb" :src="thumb" class="h-full w-full object-cover rounded-lg" />
              </div>
            </div>
          </div>

          <!-- KOLOM KANAN (INFO & FORM) -->
          <div class="flex flex-col">
            
            <div class="mb-6 border-b border-gray-200 pb-6">
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-3">
                <h1 class="text-3xl sm:text-[38px] font-bold text-[#1a2b4c] leading-tight">{{ activity.judul }}</h1>
                <div class="flex items-center gap-2 text-sm font-semibold text-[#1a2b4c] shrink-0 sm:mt-2">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Deadline : {{ activity.deadline }}
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-1.5 text-sm font-bold text-[#1a2b4c]">
                <span>|</span>
                <template v-for="(kat, index) in activity.kategori" :key="index">
                  <span>{{ kat }}</span><span>|</span>
                </template>
              </div>
            </div>

            <div class="mb-8 space-y-4 text-[13px] sm:text-[14.5px] leading-relaxed text-[#3b4c6b]">
              <p v-for="(paragraf, index) in activity.deskripsi" :key="index">{{ paragraf }}</p>
            </div>

            <div class="mb-10">
              <p class="font-bold text-[#1a2b4c] mb-1">Link Pendaftaran:</p>
              <a :href="activity.link_pendaftaran" target="_blank" class="text-sm font-semibold text-[#3b82f6] underline hover:text-blue-800 transition-colors break-all">
                {{ activity.link_pendaftaran }}
              </a>
            </div>

            <!-- ========================================== -->
            <!-- FORM REGISTRASI -->
            <!-- ========================================== -->
            <div class="flex flex-col gap-6 w-full max-w-3xl">
              
              <!-- Nama Tim -->
              <div>
                <label class="block text-[14px] font-bold text-[#1a2b4c]">Nama Tim<span class="text-red-500">*</span></label>
                <p class="text-[11px] text-gray-400 mb-2">Masukan nama tim kamu saat registrasi</p>
                <input 
                  type="text" 
                  v-model="form.nama_tim" 
                  placeholder="Masukan Nama Tim" 
                  @input="errors.nama_tim = ''"
                  :class="[
                    'w-full rounded-lg border px-4 py-3 text-sm text-gray-700 outline-none transition-colors',
                    errors.nama_tim ? 'border-red-500 focus:border-red-600 bg-red-50/30' : 'border-gray-200 bg-[#fafafa] focus:bg-white focus:border-[#152c5b]'
                  ]" 
                />
                <p v-if="errors.nama_tim" class="mt-1.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  {{ errors.nama_tim }}
                </p>
              </div>

              <!-- Nama Peserta -->
              <div>
                <label class="block text-[14px] font-bold text-[#1a2b4c]">Nama Peserta<span class="text-red-500">*</span></label>
                <p class="text-[11px] text-gray-400 mb-2">Masukkan nama peserta, termasuk namamu. Jika hanya kamu, cukup masukkan namamu.</p>
                
                <div class="flex flex-col gap-3">
                  <div v-for="(peserta, index) in form.peserta" :key="index" class="flex flex-col gap-1">
                    <div class="flex items-start gap-3">
                      
                      <div class="relative flex-1">
                        <input 
                          type="text" 
                          v-model="form.peserta[index]" 
                          placeholder="Masukkan Nama - NIM" 
                          @input="activeDropdown = index; errors.peserta[index] = ''"
                          @focus="activeDropdown = index"
                          @blur="activeDropdown = null"
                          :class="[
                            'w-full rounded-lg border px-4 py-3 text-sm text-gray-700 outline-none transition-colors',
                            errors.peserta[index] ? 'border-red-500 focus:border-red-600 bg-red-50/30' : 'border-gray-200 bg-[#fafafa] focus:bg-white focus:border-[#152c5b]'
                          ]" 
                        />
                        
                        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                          <ul v-if="activeDropdown === index && getFilteredStudents(form.peserta[index]).length > 0" class="absolute left-0 right-0 top-full z-50 mt-1 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1.5 shadow-xl font-inter">
                            <li v-for="student in getFilteredStudents(form.peserta[index])" :key="student.nim" @mousedown.prevent="selectStudent(index, student)" class="cursor-pointer px-4 py-2 text-sm transition-colors hover:bg-[#f0f4f9] hover:text-[#183669] flex flex-col">
                              <span class="font-bold text-gray-800">{{ student.name }}</span>
                              <span class="text-xs text-gray-500 font-medium">{{ student.nim }}</span>
                            </li>
                          </ul>
                        </Transition>
                      </div>
                      
                      <button v-if="index === 0" type="button" @click="addPeserta" class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-lg border border-[#183669]/30 bg-white text-[#183669] transition hover:bg-[#f0f4f9] active:scale-95 focus:outline-none"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></button>
                      <button v-else type="button" @click="removePeserta(index)" class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-lg border border-red-200 bg-white text-red-500 transition hover:bg-red-50 active:scale-95 focus:outline-none"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg></button>
                    </div>
                    
                    <p v-if="errors.peserta[index]" class="mt-0.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      {{ errors.peserta[index] }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ========================================== -->
              <!-- Bukti Pendaftaran (UPLOAD BOX DRAG & DROP) -->
              <!-- ========================================== -->
              <div>
                <label class="block text-[14px] font-bold text-[#1a2b4c]">Bukti Pendaftaran<span class="text-red-500">*</span></label>
                <p class="text-[11px] text-gray-400 mb-2">Masukan bukti pendaftaran berupa jpg/png/jpeg (Max 10MB)</p>
                
                <input type="file" ref="fileInput" accept="image/png, image/jpeg, image/jpg" class="hidden" @change="handleFileUpload" />

                <!-- Event Drag & Drop Ditambahkan Disini -->
                <div 
                  @click="triggerUpload" 
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleDrop"
                  :class="[
                    'relative flex h-[220px] w-full flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed p-4 cursor-pointer group transition', 
                    isDragging ? 'border-[#183669] bg-[#f0f4f9]' : (errors.bukti ? 'border-red-500 bg-red-50/30' : 'border-[#1a2b4c]/30 bg-[#fafafa] hover:border-[#1a2b4c] hover:bg-gray-50')
                  ]"
                >
                  <template v-if="imagePreview">
                    <img :src="imagePreview" alt="Preview" class="h-full w-full object-contain rounded-lg" />
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                      <span class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-[#1a2b4c] shadow-sm">Ganti Gambar</span>
                    </div>
                  </template>
                  <template v-else>
                    <svg :class="['mb-3 h-12 w-12 transition-colors', isDragging ? 'text-[#183669] scale-110' : (errors.bukti ? 'text-red-400' : 'text-gray-400 group-hover:text-[#1a2b4c]')]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
                    <!-- Teks menyesuaikan jika gambar sedang di drag -->
                    <p :class="['mb-4 text-[10px] font-medium transition-colors', isDragging ? 'text-[#183669] font-bold' : (errors.bukti ? 'text-red-500' : 'text-gray-400')]">
                      {{ isDragging ? 'Lepaskan gambar di sini' : 'Upload gambar atau seret gambar ke form ini' }}
                    </p>
                    <button type="button" :class="['rounded-lg border bg-white px-8 py-1.5 text-xs font-bold shadow-sm transition', isDragging ? 'border-[#183669] text-[#183669]' : (errors.bukti ? 'border-red-400 text-red-500' : 'border-gray-300 text-gray-500 group-hover:border-[#1a2b4c] group-hover:text-[#1a2b4c]')]">Upload</button>
                  </template>
                </div>
                
                <p v-if="errors.bukti" class="mt-1.5 text-xs text-red-500 font-semibold flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  {{ errors.bukti }}
                </p>
              </div>

              <!-- SUBMIT BUTTON -->
              <div class="mt-4 flex justify-end">
                <button type="button" @click="submitRegistration" class="rounded-lg bg-[#1a2b4c] px-10 py-2.5 text-[15px] font-bold text-white shadow-md transition hover:bg-[#12203b] active:scale-95">
                  Kirim
                </button>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>
    
    <LoadingSuccessModal 
      :show="isFeedbackModalOpen" 
      :status="feedbackStatus" 
      title="Registrasi Berhasil!" 
      message="Kamu telah sukses terdaftar dalam aktivitas ini. Silakan pantau pengumuman lebih lanjut."
      @close="handleFeedbackClose"
    />

  </MahasiswaLayout>
</template>
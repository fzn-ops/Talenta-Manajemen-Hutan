<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, useForm, usePage, Link } from '@inertiajs/vue3';
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import LoadingSuccessModal from '@/Components/dashboard/mahasiswa/LoadingSuccessModal.vue';
import ImagePreviewModal from '@/Components/dashboard/ImagePreviewModal.vue';


// ==========================================
// 1. DATA DUMMY & USER STATE
// ==========================================
const props = defineProps({
    aktivitas: Object,
    pendaftaranDetail: Object,
    mahasiswaList: Array
});

// ==========================================
// 1. SETUP DATA PESERTA (DETEKSI CREATE vs EDIT)
// ==========================================
const existingPeserta = props.pendaftaranDetail?.peserta || [];
const page = usePage();
const currentUser = computed(() => {
    const user = page.props.auth?.user;
    return { 
        id: user?.id,
        name: user?.nama || user?.name || '', 
        nim: user?.nim || '' 
    };
});

const isAlreadyRegistered = computed(() => Boolean(props.pendaftaranDetail));

const isStatusApproved = computed(() => {
    if (!props.pendaftaranDetail) return false;
    const s = String(props.pendaftaranDetail.status || '').toLowerCase();
    return s === 'disetujui' || s === 'diterima';
});

const getQueryParam = (key) => {
    try {
        if (typeof window !== 'undefined' && window.location.search) {
            return new URLSearchParams(window.location.search).get(key);
        }
        if (page?.url) {
            return new URL(page.url, 'http://localhost').searchParams.get(key);
        }
    } catch {
        return null;
    }
    return null;
};

const shouldStartInEdit = computed(() => {
    const editParam = getQueryParam('edit');
    return (editParam === 'true' || editParam === '1') && !isStatusApproved.value;
});

// Mode edit untuk pendaftaran yang sudah ada
const isEditing = ref(shouldStartInEdit.value);

const isFormLocked = computed(() => {
    // Kalau pendaftaran baru: form selalu terbuka untuk diisi
    if (!isAlreadyRegistered.value) return false;
    // Kalau sudah disetujui: form terkunci permanen
    if (isStatusApproved.value) return true;
    // Kalau belum klik tombol edit: form terkunci
    return !isEditing.value;
});

let initialDisplayPeserta = [];
let initialFormPeserta = [];
let initialErrorsPeserta = [];

if (existingPeserta.length > 0) {
    // Cari apakah currentUser sudah ada di daftar peserta yang tersimpan
    const currentInExisting = existingPeserta.find(p => p.id === currentUser.value.id);
    const others = existingPeserta.filter(p => p.id !== currentUser.value.id);

    // Pastikan user sendiri selalu berada di index 0
    const sortedPeserta = currentInExisting 
        ? [currentInExisting, ...others]
        : [
            { 
              id: currentUser.value.id, 
              name: currentUser.value.name, 
              nama: currentUser.value.name, 
              nim: currentUser.value.nim 
            }, 
            ...existingPeserta
          ];

    sortedPeserta.forEach(p => {
        initialDisplayPeserta.push(`${p.name || p.nama} - ${p.nim}`);
        initialFormPeserta.push(p.id);
        initialErrorsPeserta.push('');
    });
} else {
    // Pendaftaran baru: user sendiri wajib di index 0
    const myName = currentUser.value.name && currentUser.value.nim 
        ? `${currentUser.value.name} - ${currentUser.value.nim}`
        : (currentUser.value.name || 'Nama Anda');
    initialDisplayPeserta.push(myName);
    initialFormPeserta.push(currentUser.value.id || '');
    initialErrorsPeserta.push('');
}

// Snapshot awal untuk deteksi perubahan & fungsi cancel
const initialNamaTim = props.pendaftaranDetail?.nama_tim || '';
const initialPesertaIds = [...initialFormPeserta];
const initialBuktiPath = props.pendaftaranDetail?.bukti_pendaftaran || null;

const hasChanges = computed(() => {
    if (!isAlreadyRegistered.value) return true;

    // 1. Cek perubahan nama tim
    if (String(form.nama_tim || '').trim() !== String(initialNamaTim || '').trim()) {
        return true;
    }

    // 2. Cek apakah ada file bukti baru yang dipilih
    if (form.bukti !== null) {
        return true;
    }

    // 3. Cek perubahan jumlah peserta
    if (form.peserta.length !== initialPesertaIds.length) {
        return true;
    }
    // Cek ID tiap peserta
    for (let i = 0; i < form.peserta.length; i++) {
        if (form.peserta[i] !== initialPesertaIds[i]) {
            return true;
        }
    }

    return false;
});

const isSubmitDisabled = computed(() => {
    if (isFormLocked.value) return true;
    if (isAlreadyRegistered.value && !hasChanges.value) return true;
    return false;
});

const startEditing = () => {
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
    form.nama_tim = initialNamaTim;
    form.peserta = [...initialPesertaIds];
    form.bukti = null;
    displayPeserta.value = [...initialDisplayPeserta];
    imagePreview.value = initialBuktiPath ? getImageUrl(initialBuktiPath) : null;
    errors.value = {
        nama_tim: '',
        peserta: initialPesertaIds.map(() => ''),
        bukti: ''
    };
};

// ==========================================
// 2. STATE FORM REGISTRASI
// ==========================================
const displayPeserta = ref(initialDisplayPeserta);

const form = useForm({
    id_aktivitas: props.aktivitas?.id,
    nama_tim: props.pendaftaranDetail?.nama_tim || '',
    peserta: initialFormPeserta, 
    bukti: null
});

const errors = ref({ 
    nama_tim: '', 
    peserta: initialErrorsPeserta, 
    bukti: '' 
});

// ==========================================
// 3. LOAD PREVIEW GAMBAR & POSTER
// ==========================================
const getImageUrl = (path) => {
    if (!path) return null; 
    if (typeof path !== 'string') return null;
    
    // Kalau path sudah berupa URL utuh (Google, Unsplash, blob preview, dll)
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:')) {
        return path;
    }

    // Bersihkan prefix leading slash, public/, dan storage/ agar tidak jadi /storage/storage/...
    const cleanPath = path.replace(/^\/?(public\/|storage\/)+/, '');
    return `/storage/${cleanPath}`;
};

const activePosterUrl = ref('');

const allActivityImages = computed(() => {
    return Array.isArray(props.aktivitas?.gambar_aktivitas) ? props.aktivitas.gambar_aktivitas : [];
});

const mainPosterUrl = computed(() => {
    if (activePosterUrl.value) return activePosterUrl.value;
    const list = allActivityImages.value;
    if (list.length === 0) return 'https://ui-avatars.com/api/?name=Activity&background=random';
    const utama = list.find(g => g.utama == 1 || g.utama === true) || list[0];
    return getImageUrl(utama?.gambar) || 'https://ui-avatars.com/api/?name=Activity&background=random';
});

const imagePreview = ref(
    props.pendaftaranDetail?.bukti_pendaftaran 
        ? getImageUrl(props.pendaftaranDetail.bukti_pendaftaran) 
        : null
);




const getCategoryChipClass = (cat) => {
    switch (cat) {
        case 'Profesional': return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        case 'Bisnis': return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'Birokrat': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'Akademisi': return 'bg-purple-50 text-purple-700 border-purple-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};


// ==========================================
// 3. LOGIC AUTOCOMPLETE (PAKAI DATA ASLI)
// ==========================================
const activeDropdown = ref(null);

const getFilteredStudents = (index) => {
    // Index 0 tidak mencari autocomplete karena selalu diri sendiri
    if (index === 0) return [];

    const rawQuery = displayPeserta.value[index] || '';
    const query = String(rawQuery).toLowerCase();

    if (!props.mahasiswaList) return []; 
    const selectedIds = form.peserta.filter((id, i) => i !== index && id);

    return props.mahasiswaList.filter(student => {
        // Jangan tampilkan jika sudah dipilih di input lain atau merupakan diri sendiri
        if (selectedIds.includes(student.id)) return false; 
        if (currentUser.value.id && student.id === currentUser.value.id) return false;

        // Buang admin
        const role = String(student.role || '').toLowerCase();
        if (role === 'admin') return false;

        const nama = String(student.name || student.nama || '').toLowerCase();
        const nim = String(student.nim || '').toLowerCase();

        // Kalau kotak input kosong, tampilkan sisa mahasiswa yang belum dipilih
        if (query === '') return true;

        // Cocokkan ketikan dengan nama atau NIM
        return nama.includes(query) || nim.includes(query);
    });
};

const selectStudent = (index, student) => {
    if (index === 0) return; // Proteksi index 0
    form.peserta[index] = student.id; 
    displayPeserta.value[index] = `${student.name || student.nama} - ${student.nim}`;
    activeDropdown.value = null;
    errors.value.peserta[index] = '';
};

const addPeserta = () => {
    form.peserta.push('');
    displayPeserta.value.push('');
    errors.value.peserta.push('');
};

const removePeserta = (index) => {
    // Index 0 (diri sendiri) TIDAK BOLEH dihapus!
    if (index === 0) return;
    if (form.peserta.length > 1) {
        form.peserta.splice(index, 1);
        displayPeserta.value.splice(index, 1);
        errors.value.peserta.splice(index, 1);
    }
};

// ==========================================
// 4. LOGIC UPLOAD FILE (TETEP SAMA)
// ==========================================
const fileInput = ref(null);
const isDragging = ref(false);
const previewingImage = ref(null);

const triggerUpload = () => fileInput.value?.click();

const processFile = (file) => {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        showToast('File harus berupa gambar (JPG, JPEG, PNG)!', 'error');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        showToast('Ukuran gambar maksimal adalah 10MB!', 'error');
        return;
    }
    form.bukti = file;
    imagePreview.value = URL.createObjectURL(file);
    errors.value.bukti = ''; 
};

const handleFileUpload = (event) => processFile(event.target.files?.[0]);
const handleDrop = (event) => {
    isDragging.value = false;
    processFile(event.dataTransfer?.files?.[0]);
};

const removeImage = () => {
    form.bukti = null;
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

onMounted(() => {
    if (shouldStartInEdit.value) {
        isEditing.value = true;
    }
});

// ==========================================
// 5. TOAST & SUBMIT LOGIC KE LARAVEL
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
    errors.value.peserta = form.peserta.map(() => ''); 

    // 1. Validasi Nama Tim
    if (!form.nama_tim.trim()) {
        errors.value.nama_tim = 'Kolom nama tim harus diisi!';
        isValid = false;
    }

    // 2. Validasi Peserta
    if (currentUser.value.id) {
        form.peserta[0] = currentUser.value.id;
    }
    form.peserta.forEach((p, index) => {
        if (!p || p === '') { 
            errors.value.peserta[index] = 'Kolom nama peserta harus diisi/dipilih!';
            isValid = false;
        }
    });

    if (!form.bukti && !props.pendaftaranDetail) {
        errors.value.bukti = 'Bukti pendaftaran wajib diupload!';
        isValid = false;
    }

    return isValid;
};

const submitRegistration = () => {
    if (isSubmitDisabled.value) return;

    if (!validateForm()) {
        showToast('Mohon lengkapi formulir yang bertanda merah!', 'error');
        return;
    }
    
    isFeedbackModalOpen.value = true;
    feedbackStatus.value = 'loading';
    
    if (props.pendaftaranDetail) {
        form.transform((data) => ({
            ...data,
            peserta_ids: data.peserta,
        })).post(`/mahasiswa/aktivitas/pendaftaran/${props.pendaftaranDetail.id}/update`, { 
            preserveScroll: true,
            onSuccess: () => {
                feedbackStatus.value = 'success';
                isEditing.value = false;
            },
            onError: (err) => {
                isFeedbackModalOpen.value = false;
                console.error("Error validasi Update Laravel:", err);
                showToast('Gagal memperbarui data, cek kembali form Anda!', 'error');
            }
        });
    } else {
        // ==========================================
        // MODE DAFTAR BARU -> Kirim ke fungsi Daftar
        // ==========================================
        form.transform((data) => ({
            ...data,
            peserta_ids: data.peserta
        })).post('/mahasiswa/aktivitas/pendaftaran/daftar', {
            preserveScroll: true,
            onSuccess: () => {
                feedbackStatus.value = 'success';
            },
            onError: (err) => {
                isFeedbackModalOpen.value = false;
                console.error("Error validasi Daftar Laravel:", err);
                showToast('Gagal mendaftar, cek kembali data Anda!', 'error');
            }
        });
    }
};

const handleFeedbackClose = () => {
    isFeedbackModalOpen.value = false;
    router.visit('/mahasiswa/aktivitas/list');
};

const closeDropdown = () => {
    setTimeout(() => {
        activeDropdown.value = null;
    }, 200);
};  

const showImageModal = ref(false);
const previewImages = ref([]);
const previewIndex = ref(0);
const previewLabel = ref('Gambar');

const openImagePreview = (target, label = 'Gambar') => {
    if (!target) return;
    previewLabel.value = label;
    if (label === 'Poster' || (!label && allActivityImages.value.length > 0)) {
        const list = allActivityImages.value.map(g => getImageUrl(g?.gambar)).filter(Boolean);
        if (list.length > 0) {
            previewImages.value = list;
            const targetUrl = typeof target === 'string' ? target : getImageUrl(target);
            const foundIdx = list.indexOf(targetUrl);
            previewIndex.value = foundIdx >= 0 ? foundIdx : 0;
            previewLabel.value = 'Poster';
            showImageModal.value = true;
            return;
        }
    }
    const src = typeof target === 'string' ? target : getImageUrl(target);
    previewImages.value = [src];
    previewIndex.value = 0;
    showImageModal.value = true;
};  

</script>

<template>
  <Head :title="aktivitas.judul" />

  <MahasiswaLayout>
    <section class="mx-auto w-full max-w-[1200px] px-4 pt-8 pb-10 font-poppins sm:px-8 sm:pt-10 sm:pb-12">
      
      <!-- TOAST NOTIFICATION -->
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]"><ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/></div>
      </Transition>

      <div class="space-y-6 sm:space-y-8 w-full">
        
        <!-- BREADCRUMB -->
        <div class="flex h-11 items-center text-[13px] sm:text-[14px] font-poppins text-[#475569] w-full min-w-0">
          <Link href="/mahasiswa/aktivitas/list" class="shrink-0 font-medium text-[#64748b] hover:text-[#183669] hover:underline transition-colors">
            List Aktivitas
          </Link>
          <span class="shrink-0 mx-2 text-[#94a3b8]">/</span>
          <span class="flex-1 min-w-0 font-semibold text-[#17334F] underline decoration-[#17334F]/50 underline-offset-4 truncate">
            {{ aktivitas?.judul || 'Memuat...' }}
          </span>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[360px_1fr] xl:grid-cols-[400px_1fr] xl:gap-12 items-start w-full">
          
          <!-- KOLOM KIRI (POSTER & THUMBNAILS) -->
          <div class="flex flex-col gap-3.5 sm:gap-4 w-full max-w-[380px] mx-auto lg:mx-0 lg:max-w-none">
            <!-- MAIN POSTER WITH ZOOM AND TYPE BADGE -->
            <div 
              @click="openImagePreview(mainPosterUrl)"
              class="group aspect-[3/4] w-full overflow-hidden rounded-[14px] bg-slate-100 shadow-sm border border-[#d6e0ee] relative cursor-pointer select-none"
            >
              <!-- Badge Lomba / Umum on Poster (Top Left) -->
              <div class="absolute left-3.5 top-3.5 z-10 pointer-events-none">
                <div
                  :class="[
                    'inline-flex h-[26px] items-center justify-center rounded-full px-2.5 sm:px-3 text-[10.5px] sm:text-[11px] font-bold uppercase tracking-wider shadow-xs select-none antialiased leading-none',
                    String(aktivitas.jenis || '').toLowerCase() === 'lomba'
                      ? 'bg-amber-500 text-white border border-amber-400/40'
                      : 'bg-blue-600 text-white border border-blue-400/30'
                  ]"
                >
                  <span>{{ String(aktivitas.jenis || '').toLowerCase() === 'lomba' ? 'Lomba' : 'Umum' }}</span>
                </div>
              </div>

              <!-- Badge Peserta / Partisipan on Poster (Top Right) -->
              <div class="absolute right-3.5 top-3.5 z-10 pointer-events-none">
                <div class="inline-flex h-[26px] items-center justify-center gap-1.5 rounded-full bg-white/95 px-2.5 sm:px-3 text-[11px] font-bold text-[#183669] border border-slate-200/90 shadow-xs backdrop-blur-sm select-none antialiased leading-none">
                  <svg class="h-3.5 w-3.5 text-[#183669] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                  <span>{{ aktivitas.partisipan || 0 }}</span>
                </div>
              </div>

              <!-- Main Poster Image (Static) -->
              <img 
                :src="mainPosterUrl" 
                class="h-full w-full object-cover" 
                alt="Poster Aktivitas" 
              />
            </div>

            <!-- THUMBNAILS (NO BLUE OUTLINE!) -->
            <div v-if="allActivityImages.length > 1" class="grid grid-cols-3 gap-2.5 sm:gap-3">
              <div 
                v-for="(imgObj, index) in allActivityImages" 
                :key="index" 
                @click="activePosterUrl = getImageUrl(imgObj?.gambar)"
                class="aspect-square w-full rounded-[10px] bg-[#e2e8f0] shadow-xs overflow-hidden cursor-pointer transition-all"
                :class="mainPosterUrl === getImageUrl(imgObj?.gambar) 
                  ? 'border-2 border-slate-700 opacity-100 ring-1 ring-slate-400/30' 
                  : 'border border-slate-200/90 opacity-60 hover:opacity-100'"
              >
                <img 
                  :src="getImageUrl(imgObj?.gambar)" 
                  class="h-full w-full object-cover" 
                  alt="Thumbnail Aktivitas" 
                />
              </div>
            </div>

            <!-- Petunjuk Ukuran Penuh -->
            <p class="text-center font-inter text-[11px] sm:text-[11.5px] text-slate-400 italic select-none">
              *Tekan gambar untuk melihat ukuran penuh
            </p>
          </div>

          <!-- KOLOM KANAN (INFO & FORM REGISTRASI) -->
          <div class="flex flex-col w-full min-w-0">
            <div 
                v-if="props.pendaftaranDetail && String(props.pendaftaranDetail.status).toLowerCase() === 'ditolak' && props.pendaftaranDetail.catatan_penolakan"
                class="mb-5 flex items-start gap-3 rounded-[12px] border border-red-200 bg-red-50 p-4 shadow-sm"
              >
                <!-- Ikon Warning -->
                <div class="mt-0.5 shrink-0 rounded-full bg-red-100 p-1">
                  <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                
                <!-- Isi Pesan -->
                <div>
                  <h3 class="font-poppins text-[13.5px] font-bold text-red-800">
                    Pendaftaran Ditolak
                  </h3>
                  <p class="mt-1 font-inter text-[12.5px] sm:text-[13px] text-red-700 leading-relaxed">
                    Alasan: <span class="font-semibold">{{ props.pendaftaranDetail.catatan_penolakan }}</span>
                    <br>
                    <span class="text-[12px] opacity-90 mt-1 inline-block">Silakan perbaiki data form di bawah dan kirim ulang pendaftaran Anda.</span>
                  </p>
                </div>
              </div>
            <!-- HEADER DETAIL AKTIVITAS -->
            <div class="mb-5 sm:mb-6 border-b border-[#e2e8f0] pb-5 sm:pb-6 w-full">
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                
                <!-- 1. Judul & Kategori di Kiri (Disatukan agar tidak ada celah/gap) -->
                <div class="flex-1 min-w-0">
                  <h1 class="font-poppins text-2xl sm:text-[30px] lg:text-[34px] font-bold text-[#17334F] leading-tight">
                    {{ aktivitas.judul }}
                  </h1>

                  <!-- Kategori Tracks (Langsung di bawah judul tanpa gap) -->
                  <div v-if="aktivitas.kategori && aktivitas.kategori.length > 0" class="mt-2.5 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span
                      v-for="cat in aktivitas.kategori"
                      :key="cat"
                      :class="[
                        'inline-flex h-[26px] items-center justify-center rounded-full px-2.5 font-poppins text-[11px] sm:text-[11.5px] font-semibold border shadow-xs leading-none',
                        getCategoryChipClass(cat)
                      ]"
                    >
                      {{ cat }}
                    </span>
                  </div>
                </div>
                
                <!-- 2. Wadah Deadline & Status di Kanan -->
                <div class="flex flex-row sm:flex-col flex-wrap gap-2 shrink-0 sm:mt-1 self-start sm:self-auto sm:items-end">
                  
                  <!-- Badge Deadline -->
                  <div class="inline-flex h-[32px] sm:h-[34px] items-center gap-2 text-[12px] sm:text-[13px] font-semibold text-[#183669] font-poppins bg-slate-50 border border-slate-200 px-3 rounded-full shadow-xs">
                    <svg class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Deadline: {{ aktivitas.deadline }}</span>
                  </div>

                  <!-- Badge Status (Pakai tag yang ada di banner card ActivityList) -->
                  <div 
                    v-if="props.pendaftaranDetail"
                    class="inline-flex h-[32px] sm:h-[34px] items-center justify-center gap-1.5 rounded-full px-3 font-inter text-[11.5px] sm:text-[12px] font-semibold border shadow-xs"
                    :class="{
                      'bg-gray-100 text-gray-700 border-gray-300': String(props.pendaftaranDetail.status || '').toLowerCase() === 'menunggu',
                      'bg-green-50 text-green-700 border-green-200': ['diterima', 'disetujui'].includes(String(props.pendaftaranDetail.status || '').toLowerCase()),
                      'bg-red-50 text-red-700 border-red-200': String(props.pendaftaranDetail.status || '').toLowerCase() === 'ditolak'
                    }"
                  >
                    <svg v-if="String(props.pendaftaranDetail.status || '').toLowerCase() === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
                    <svg v-if="['diterima', 'disetujui'].includes(String(props.pendaftaranDetail.status || '').toLowerCase())" class="h-3.5 w-3.5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="String(props.pendaftaranDetail.status || '').toLowerCase() === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="capitalize">{{ props.pendaftaranDetail.status }}</span>
                  </div>

                </div>
              </div>
            </div>

            <!-- DESKRIPSI -->
            <div 
              class="mb-6 font-inter text-[13.5px] sm:text-[14px] leading-relaxed text-[#475569] w-full rich-text-content"
              v-html="aktivitas?.deskripsi || 'Tidak ada deskripsi.'"
            ></div>

            <!-- LINK PENDAFTARAN -->
            <div class="mb-7 w-full">
              <p class="font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669] mb-1">Link Pendaftaran:</p>
              <a :href="aktivitas?.link_pendaftaran" target="_blank" class="font-inter text-[13.5px] sm:text-sm font-semibold text-[#2563eb] underline hover:text-[#1d4ed8] transition-colors break-all">
                {{ aktivitas?.link_pendaftaran }}
              </a>
            </div>

            <!-- ========================================== -->
            <!-- FORM REGISTRASI (MAX-WIDTH SERAGAM DENGAN DESKRIPSI) -->
            <!-- ========================================== -->
            <form @submit.prevent="submitRegistration" novalidate class="flex flex-col gap-4 sm:gap-5 w-full">
              
              <!-- Nama Tim -->
              <div class="w-full">
                <label class="block font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                  Nama Tim<span class="text-red-500">*</span>
                </label>
                <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukan nama tim kamu saat registrasi</p>
                <input 
                  type="text" 
                  v-model="form.nama_tim" 
                  placeholder="Masukan Nama Tim" 
                  @input="errors.nama_tim = ''"
                  :disabled="isFormLocked"
                  class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border px-3.5 font-inter text-[13px] sm:text-[14px] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0 shadow-xs"
                  :class="[
                    isFormLocked
                      ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed opacity-85'
                      : (errors.nama_tim 
                          ? 'border-red-400 focus:border-red-500 bg-red-50/20 text-[#1e3456]' 
                          : 'bg-white text-[#1e3456] border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white')
                  ]" 
                />
                <p v-if="errors.nama_tim" class="mt-1 font-inter text-[11px] font-medium text-red-500 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  {{ errors.nama_tim }}
                </p>
              </div>

              <!-- Nama Peserta -->
              <div class="w-full">
                <label class="block font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                  Nama Peserta<span class="text-red-500">*</span>
                </label>
                <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukkan nama peserta, termasuk namamu. Jika hanya kamu, cukup masukkan namamu.</p>
                
                <div class="flex flex-col gap-2.5 pt-1 w-full">
                  <div v-for="(peserta, index) in form.peserta" :key="index" class="flex flex-col gap-1 w-full">
                    <div class="flex items-start gap-2 sm:gap-2.5 w-full">
                      
                      <div class="relative flex-1 min-w-0">
                        <!-- Input untuk Peserta 1 (Diri Sendiri, Permanen & Tidak Bisa Dihapus/Diganti) -->
                        <div v-if="index === 0" class="relative">
                          <input 
                            type="text" 
                            :value="displayPeserta[0]" 
                            readonly
                            tabindex="-1"
                            class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border px-3.5 pr-20 font-inter text-[13px] sm:text-[14px] font-medium select-none focus:outline-none focus:ring-0 shadow-xs"
                            :class="[
                              isFormLocked
                                ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed opacity-85'
                                : 'bg-slate-50/90 text-[#1e3456] border-[#d6e0ee] cursor-default'
                            ]"
                          />
                          <span 
                            class="absolute right-3 top-1/2 -translate-y-1/2 mt-0.5 inline-flex items-center rounded-md px-2 py-0.5 text-[10.5px] font-semibold select-none pointer-events-none transition-colors"
                            :class="isFormLocked ? 'bg-gray-200/90 text-gray-500' : 'bg-[#183669]/10 text-[#183669]'"
                          >
                            Anda
                          </span>
                        </div>

                        <!-- Input untuk Anggota Tambahan (Bisa Dicari & Dihapus) -->
                        <div v-else class="relative">
                          <input 
                            type="text" 
                            v-model="displayPeserta[index]" 
                            placeholder="Ketik Nama atau NIM rekan tim..." 
                            @input="activeDropdown = index; errors.peserta[index] = ''"
                            @focus="activeDropdown = index"
                            @blur="closeDropdown"
                            :disabled="isFormLocked"
                            class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border px-3.5 font-inter text-[13px] sm:text-[14px] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0 shadow-xs"
                            :class="[
                              isFormLocked 
                                // 1. JIKA DIKUNCI: Abu-abu kaku sama persis dengan Nama Tim
                                ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed pointer-events-none opacity-85' 
                                
                                // 2. JIKA DIBUKA: Cek error
                                : (errors.peserta[index] 
                                    // 2a. Kalau ada error -> Merah
                                    ? 'border-red-400 focus:border-red-500 bg-red-50/20 text-[#1e3456]' 
                                    // 2b. Kalau aman -> Normal
                                    : 'bg-white text-[#1e3456] border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white')
                            ]"
                          />
                          
                          <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                            <ul v-if="activeDropdown === index && getFilteredStudents(index).length > 0" class="absolute left-0 right-0 top-full z-50 mt-1 max-h-48 overflow-y-auto rounded-[10px] border border-[#d6e0ee] bg-white py-1.5 shadow-xl font-inter">
                              <li v-for="student in getFilteredStudents(index)" :key="student.nim" @mousedown.prevent="selectStudent(index, student)" class="cursor-pointer px-4 py-2 text-sm transition-colors hover:bg-[#f0f4f9] hover:text-[#183669] flex flex-col">
                                <span class="font-bold text-gray-800">{{ student.nama }}</span>
                                <span class="text-xs text-gray-500 font-medium">{{ student.nim }}</span>
                              </li>
                            </ul>
                          </Transition>
                        </div>
                      </div>
                      
                      <!-- Tombol Tambah Rekan Tim (Hanya di baris pertama & kalau belum dikunci) -->
                      <button 
                        v-if="index === 0 && !isFormLocked" 
                        type="button" 
                        @click="addPeserta" 
                        class="mt-1 sm:mt-1.5 flex h-[42px] sm:h-[44px] w-[42px] sm:w-[44px] shrink-0 items-center justify-center rounded-[10px] border border-[#d6e0ee] bg-white text-[#183669] transition-colors hover:border-[#183669] hover:bg-[#fafcff] active:scale-95 focus:outline-none cursor-pointer shadow-xs"
                        title="Tambah Rekan Tim"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                      
                      <!-- Tombol Hapus Rekan Tim (Hanya di baris kedua dst & kalau belum dikunci) -->
                      <button 
                        v-else-if="index !== 0 && !isFormLocked" 
                        type="button" 
                        @click="removePeserta(index)" 
                        class="mt-1 sm:mt-1.5 flex h-[42px] sm:h-[44px] w-[42px] sm:w-[44px] shrink-0 items-center justify-center rounded-[10px] border border-red-200 bg-white text-red-500 transition-colors hover:border-red-400 hover:bg-red-50 active:scale-95 focus:outline-none cursor-pointer shadow-xs"
                        title="Hapus Rekan Tim"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg>
                      </button>
                    </div>
                    
                    <p v-if="errors.peserta[index]" class="mt-0.5 font-inter text-[11px] font-medium text-red-500 flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      {{ errors.peserta[index] }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ========================================== -->
              <!-- Bukti Pendaftaran (DROPZONE & PREVIEW SAMA DENGAN MODAL) -->
              <!-- ========================================== -->
              <div class="w-full">
                <label class="block font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                  Bukti Pendaftaran<span class="text-red-500">*</span>
                </label>
                <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukan bukti pendaftaran berupa jpg/png/jpeg (MAX 10MB)</p>
                
                <!-- 🔥 Ganti jadi isFormLocked -->
                <input 
                  type="file" 
                  ref="fileInput" 
                  accept="image/png, image/jpeg, image/jpg" 
                  class="hidden" 
                  :disabled="isFormLocked"
                  @change="handleFileUpload" 
                />

                <!-- DROP ZONE -->
                <div
                  @click="!isFormLocked && triggerUpload()" 
                  @dragover.prevent="!isFormLocked && (isDragging = true)"
                  @dragleave.prevent="!isFormLocked && (isDragging = false)"
                  @drop.prevent="!isFormLocked && handleDrop($event)"
                  :class="[
                    'mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors min-h-[145px] w-full',
                    isFormLocked 
                      ? 'bg-gray-50/50 border-gray-200 opacity-95'
                      : (errors.bukti
                        ? 'border-red-400 bg-red-50/20'
                        : (isDragging
                          ? 'border-[#183669] bg-[#183669]/5'
                          : 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60 cursor-pointer'))
                  ]"
                >
                  <!-- State 1: Belum Ada Gambar -->
                  <div
                    v-if="!imagePreview"
                    class="flex flex-col items-center justify-center py-3"
                  >
                    <svg
                      class="h-9 w-9 text-[#8c9eb5]"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.8"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"
                      />
                    </svg>

                    <p class="mt-1.5 font-inter text-[12px] text-[#7188a3]">
                      Upload gambar atau seret gambar ke form ini
                    </p>

                    <p class="font-inter text-[10px] text-[#94a3b8] mt-0.5">
                      Format .JPG, .PNG, .JPEG • Maksimal 10MB
                    </p>

                    <button
                      type="button"
                      @click.stop="triggerUpload"
                      :disabled="isFormLocked"
                      class="mt-2.5 rounded-[8px] border-none bg-[#183669]/10 px-5 py-1.5 font-inter text-[12px] font-semibold text-[#183669] transition hover:bg-[#183669]/20 cursor-pointer"
                      :class="isFormLocked ? 'cursor-not-allowed opacity-50' : ''"
                    >
                      Upload
                    </button>
                  </div>

                  <!-- State 2: Sudah Ada Gambar -->
                  <div
                    v-else
                    class="w-full flex flex-col items-center justify-center py-1"
                  >
                    <!-- KONDISI A: MODE PREVIEW / TERKUNCI (isFormLocked) -->
                    <template v-if="isFormLocked">
                      <div 
                        @click.stop="openImagePreview(imagePreview)"
                        class="relative aspect-[4/3] h-36 sm:h-40 rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs cursor-pointer select-none"
                        title="Klik untuk melihat ukuran penuh"
                      >
                        <img
                          :src="imagePreview"
                          alt="Preview Bukti Pendaftaran"
                          class="h-full w-full object-cover"
                        />
                      </div>

                      <!-- Petunjuk Ukuran Penuh (Senada dengan Poster di kolom kiri) -->
                      <p class="mt-2 text-center font-inter text-[11px] sm:text-[11.5px] text-slate-400 italic select-none">
                        *Tekan gambar untuk melihat ukuran penuh
                      </p>
                    </template>

                    <!-- KONDISI B: MODE EDIT (Bisa Ganti, Hapus, atau Pratinjau) -->
                    <template v-else>
                      <div class="relative aspect-[4/3] h-36 sm:h-40 rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs group">
                        <img
                          :src="imagePreview"
                          alt="Preview Bukti Pendaftaran"
                          class="h-full w-full object-cover"
                        />

                        <!-- ACTION OVERLAY (DELETE, PREVIEW, REPLACE) -->
                        <div class="absolute inset-0 bg-black/40 flex flex-col justify-between p-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                          
                          <!-- DELETE -->
                          <div class="flex justify-end w-full">
                            <button
                              type="button"
                              @click.stop.prevent="removeImage"
                              class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white shadow-sm hover:bg-red-700 hover:scale-110 active:scale-95 transition cursor-pointer"
                              title="Hapus Bukti"
                            >
                              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>

                          <!-- PREVIEW + REPLACE -->
                          <div class="flex items-center justify-center gap-2 w-full pb-0.5">
                            <!-- Tombol PREVIEW (Kaca Pembesar) -->
                            <button
                              type="button"
                              @click.stop.prevent="openImagePreview(imagePreview)"
                              class="flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white shadow-sm hover:bg-black/85 hover:scale-110 active:scale-95 transition cursor-pointer"
                              title="Lihat Gambar Penuh"
                            >
                              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                              </svg>
                            </button>

                            <!-- Tombol REPLACE (Ganti Gambar) -->
                            <button
                              type="button"
                              @click.stop.prevent="triggerUpload"
                              class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500/90 text-white shadow-sm hover:bg-amber-600 hover:scale-110 active:scale-95 transition cursor-pointer"
                              title="Ganti Gambar"
                            >
                              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                              </svg>
                            </button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
                  <p v-if="errors.bukti" class="mt-1 font-inter text-[11px] font-medium text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ errors.bukti }}
                </p>
              </div>

              <!-- ACTION BUTTONS (SUBMIT & EDIT/CANCEL) -->
              <div class="mt-2 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 w-full">
                
                <!-- Tombol Batal Edit / Tombol Edit (Khusus aktivitas yang sudah didaftarkan) -->
                <template v-if="isAlreadyRegistered && !isStatusApproved">
                  <!-- Mode Sedang Edit: Tombol Batal -->
                  <button
                    v-if="isEditing"
                    type="button"
                    @click="cancelEditing"
                    class="w-full sm:w-auto rounded-[10px] bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 px-6 py-2.5 font-poppins text-sm font-semibold transition active:scale-[0.98] focus:outline-none outline-none border-none cursor-pointer"
                  >
                    Batal
                  </button>

                  <!-- Mode Belum Edit: Tombol Edit -->
                  <button
                    v-else
                    type="button"
                    @click="startEditing"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-[10px] bg-[#ffe58f] hover:bg-[#ffd666] active:bg-[#ffd666] text-[#d46b08] px-6 py-2.5 font-poppins text-sm font-semibold transition active:scale-[0.98] focus:outline-none outline-none border-none cursor-pointer"
                  >
                    <svg
                      width="16"
                      height="16"
                      viewBox="0 0 19 19"
                      fill="none"
                      class="h-4 w-4 shrink-0"
                      aria-hidden="true"
                    >
                      <path
                        d="M4.92119 4.92074C4.92119 5.18177 4.81749 5.43211 4.63291 5.61669C4.44833 5.80126 4.19798 5.90496 3.93695 5.90496H2.95271C2.69168 5.90496 2.44133 6.00865 2.25675 6.19323C2.07217 6.3778 1.96847 6.62814 1.96847 6.88917V15.7471C1.96847 16.0082 2.07217 16.2585 2.25675 16.4431C2.44133 16.6277 2.69168 16.7313 2.95271 16.7313H11.8108C12.0719 16.7313 12.3222 16.6277 12.5068 16.4431C12.6914 16.2585 12.7951 16.0082 12.7951 15.7471V14.7629C12.7951 14.5019 12.8988 14.2515 13.0834 14.067C13.2679 13.8824 13.5183 13.7787 13.7793 13.7787C14.0404 13.7787 14.2907 13.8824 14.4753 14.067C14.6599 14.2515 14.7636 14.5019 14.7636 14.7629V15.7471C14.7636 16.5302 14.4525 17.2812 13.8987 17.835C13.345 18.3887 12.594 18.6998 11.8108 18.6998H2.95271C2.1696 18.6998 1.41857 18.3887 0.864829 17.835C0.311088 17.2812 0 16.5302 0 15.7471V6.88917C0 6.10608 0.311088 5.35506 0.864829 4.80134C1.41857 4.24761 2.1696 3.93652 2.95271 3.93652H3.93695C4.19798 3.93652 4.44833 4.04022 4.63291 4.22479C4.81749 4.40937 4.92119 4.65971 4.92119 4.92074Z"
                        fill="#d46b08"
                      />
                      <path
                        d="M11.413 2.96342L15.7358 7.2861L9.55481 13.4896C9.4634 13.5813 9.3548 13.6541 9.23522 13.7037C9.11564 13.7534 8.98744 13.779 8.85797 13.779H5.90526C5.64422 13.779 5.39388 13.6753 5.2093 13.4907C5.02472 13.3061 4.92102 13.0558 4.92102 12.7948V9.84211C4.92105 9.71264 4.94662 9.58444 4.99628 9.46487C5.04593 9.34529 5.11869 9.23669 5.21039 9.14528L11.413 2.96342ZM17.8067 0.893608C18.3495 1.43606 18.6678 2.16332 18.6979 2.93014C18.728 3.69697 18.4677 4.44693 17.9691 5.03027L17.8076 5.20743L17.1256 5.89048L12.8077 1.57272L13.4918 0.893608C14.064 0.32144 14.84 0 15.6492 0C16.4584 0 17.2345 0.32144 17.8067 0.893608Z"
                        fill="#d46b08"
                      />
                    </svg>
                    <span>Edit</span>
                  </button>
                </template>

                <!-- Tombol Submit (Simpan Perubahan / Daftar Aktivitas) -->
                <button 
                  v-if="!isStatusApproved"
                  type="submit" 
                  :disabled="isSubmitDisabled"
                  class="w-full sm:w-auto rounded-[10px] px-8 py-2.5 font-poppins text-sm font-semibold transition active:scale-[0.98] focus:outline-none outline-none border-none"
                  :class="[
                    isSubmitDisabled
                      ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'
                      : 'bg-[#183669] text-white hover:bg-[#132b53] cursor-pointer'
                  ]"
                >
                  {{ isAlreadyRegistered ? 'Simpan Perubahan' : 'Daftar Aktivitas' }}
                </button>
                
                <!-- Keterangan jika sudah disetujui -->
                <div v-else class="text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-[10px] px-4 py-2 flex items-center gap-2">
                  <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Pendaftaran telah disetujui dan tidak dapat diubah lagi.</span>
                </div>

              </div>

            </form>

          </div>
        </div>
      </div>
    </section>
    
    <!-- MODAL GAMBAR (LIGHTBOX) TERPUSAT -->
    <ImagePreviewModal
      :show="showImageModal"
      :images="previewImages"
      :initial-index="previewIndex"
      :label="previewLabel"
      @close="showImageModal = false"
    />

    <!-- SUCCESS / FEEDBACK MODAL -->
    <LoadingSuccessModal 
      :show="isFeedbackModalOpen" 
      :status="feedbackStatus" 
      title="Registrasi Berhasil!" 
      message="Kamu telah sukses terdaftar dalam aktivitas ini. Silakan pantau pengumuman lebih lanjut."
      @close="handleFeedbackClose"
    />

  </MahasiswaLayout>
</template>

<style scoped>
.rich-text-content :deep(p) {
  margin-bottom: 0.75rem;
  line-height: 1.65;
}
.rich-text-content :deep(p:last-child) {
  margin-bottom: 0;
}
.rich-text-content :deep(b),
.rich-text-content :deep(strong) {
  font-weight: 700;
  color: #0f2347;
}
.rich-text-content :deep(i),
.rich-text-content :deep(em) {
  font-style: italic;
}
.rich-text-content :deep(u) {
  text-decoration: underline;
}
.rich-text-content :deep(s),
.rich-text-content :deep(strike) {
  text-decoration: line-through;
}
.rich-text-content :deep(ul) {
  display: block;
  list-style-type: disc;
  padding-left: 1.5rem;
  margin: 0.5rem 0;
}
.rich-text-content :deep(ol) {
  display: block;
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin: 0.5rem 0;
}
.rich-text-content :deep(li) {
  display: list-item;
  margin: 0.2rem 0;
}
.rich-text-content :deep(a) {
  color: #2563eb;
  text-decoration: underline;
  font-weight: 500;
  word-break: break-all;
  transition: color 0.15s ease;
}
.rich-text-content :deep(a:hover) {
  color: #1d4ed8;
}
.rich-text-content :deep(blockquote) {
  display: block;
  border-left: 3.5px solid #183669;
  background: #f1f5f9;
  padding: 0.5rem 1rem;
  margin: 0.65rem 0;
  color: #334155;
  font-style: italic;
  border-radius: 0 6px 6px 0;
}
.rich-text-content :deep(code) {
  background: #e2e8f0;
  color: #b91c1c;
  padding: 0.15rem 0.35rem;
  border-radius: 4px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.875em;
}
.rich-text-content :deep(hr) {
  border: 0;
  height: 1px;
  background: #cbd5e1;
  margin: 1rem 0;
}
</style>
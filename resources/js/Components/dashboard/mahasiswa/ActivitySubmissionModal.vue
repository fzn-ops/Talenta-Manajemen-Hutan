<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DatePicker from '@/Components/dashboard/DatePicker.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';
import RichTextEditor from '@/Components/dashboard/RichTextEditor.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },

    isEditMode: {
        type: Boolean,
        default: false
    },

    initialData: {
        type: Object,
        default: () => ({
            id: null,
            judul: '',
            deskripsi: '',
            deadline: '',
            kategori: [],
            gambar: [],
            catatan_penolakan: ''
        })
    }
});

const emit = defineEmits(['close', 'submit']);

const kategoriOptions = [
    'Profesional',
    'Bisnis',
    'Akademisi',
    'Birokrat'
];

// =====================================================
// FORM STATE
// =====================================================

const form = ref({
    id: null,
    judul: '',
    deskripsi: '',
    deadline: '',
    kategori: [],
    gambar: [],
    catatan_penolakan: ''
});

const errors = ref({
    judul: '',
    deskripsi: '',
    deadline: '',
    kategori: '',
    gambar: ''
});

const formError = ref('');

// =====================================================
// IMAGE STATE
// =====================================================

const fileInput = ref(null);
const replaceFileInput = ref(null);

const isDragging = ref(false);

const imageIndexToDelete = ref(null);
const imageIndexToReplace = ref(null);

const showDeleteImageModal = ref(false);

const showImageModal = ref(false);
const selectedImage = ref('');

const isBackdropClick = ref(false);

// =====================================================
// SYNC FORM DATA
// =====================================================

const syncFormData = () => {
    formError.value = '';

    errors.value = {
        judul: '',
        deskripsi: '',
        deadline: '',
        kategori: '',
        gambar: ''
    };

    const data = props.initialData || {};

    let kategori = [];

    if (Array.isArray(data.kategori)) {
        kategori = [...data.kategori];
    } else if (typeof data.kategori === 'string') {
        kategori = data.kategori
            .split(',')
            .map(item => item.trim())
            .filter(Boolean);
    }

    let gambar = [];

    if (Array.isArray(data.gambar)) {
        gambar = [...data.gambar];
    } else if (data.gambar) {
        gambar = [data.gambar];
    }

    form.value = {
        id: data.id || null,
        judul: data.judul || '',
        deskripsi: data.deskripsi || '',
        deadline: data.deadline || '',
        kategori,
        gambar,
        catatan_penolakan: data.catatan_penolakan || ''
    };
};

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            syncFormData();
        }
    },
    { immediate: true }
);

// =====================================================
// CLOSE MODAL
// =====================================================

const handleClose = () => {
    formError.value = '';

    errors.value = {
        judul: '',
        deskripsi: '',
        deadline: '',
        kategori: '',
        gambar: ''
    };

    emit('close');
};

const handleBackdropMouseDown = (event) => {
    isBackdropClick.value = event.target === event.currentTarget;
};

const handleBackdropMouseUp = (event) => {
    if (
        isBackdropClick.value &&
        event.target === event.currentTarget
    ) {
        handleClose();
    }

    isBackdropClick.value = false;
};

// =====================================================
// KEYBOARD
// =====================================================

const handleKeyDown = (event) => {
    if (event.key !== 'Escape' || !props.show) {
        return;
    }

    if (showImageModal.value) {
        showImageModal.value = false;
    } else if (showDeleteImageModal.value) {
        showDeleteImageModal.value = false;
    } else {
        handleClose();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeyDown);

    // Bersihkan object URL yang masih aktif
    form.value.gambar.forEach((gambar) => {
        if (
            typeof gambar === 'object' &&
            gambar?.isNew &&
            gambar?.url
        ) {
            URL.revokeObjectURL(gambar.url);
        }
    });
});

// =====================================================
// IMAGE UPLOAD
// =====================================================

const triggerFileInput = () => {
    fileInput.value?.click();
};

const triggerReplaceImage = (index) => {
    imageIndexToReplace.value = index;
    replaceFileInput.value?.click();
};

const processFiles = (files) => {
    if (!files || !files.length) {
        return;
    }

    const remainingSlot = 3 - form.value.gambar.length;

    if (remainingSlot <= 0) {
        return;
    }

    const filesToAdd = files.slice(0, remainingSlot);

    filesToAdd.forEach((file) => {
        // Validasi tipe
        if (!file.type.startsWith('image/')) {
            return;
        }

        // Maksimal 10 MB
        if (file.size > 10 * 1024 * 1024) {
            return;
        }

        const previewUrl = URL.createObjectURL(file);

        form.value.gambar.push({
            file,
            url: previewUrl,
            isNew: true
        });
    });

    if (form.value.gambar.length > 0) {
        errors.value.gambar = '';
    }
};

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files || []);

    processFiles(files);

    event.target.value = '';
};

const handleReplaceImage = (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        event.target.value = '';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        event.target.value = '';
        return;
    }

    const index = imageIndexToReplace.value;

    if (index !== null && index >= 0) {
        const oldImage = form.value.gambar[index];

        // Hapus object URL lama
        if (
            oldImage &&
            typeof oldImage === 'object' &&
            oldImage.isNew &&
            oldImage.url
        ) {
            URL.revokeObjectURL(oldImage.url);
        }

        const previewUrl = URL.createObjectURL(file);

        form.value.gambar[index] = {
            file,
            url: previewUrl,
            isNew: true
        };

        errors.value.gambar = '';
    }

    event.target.value = '';
    imageIndexToReplace.value = null;
};

const handleImageDrop = (event) => {
    isDragging.value = false;

    const files = Array.from(
        event.dataTransfer?.files || []
    );

    processFiles(files);
};

// =====================================================
// DELETE IMAGE
// =====================================================

const openDeleteImageModal = (index) => {
    imageIndexToDelete.value = index;
    showDeleteImageModal.value = true;
};

const executeRemoveImage = () => {
    if (imageIndexToDelete.value !== null) {
        const removed = form.value.gambar.splice(
            imageIndexToDelete.value,
            1
        )[0];

        if (
            removed &&
            typeof removed === 'object' &&
            removed.isNew &&
            removed.url
        ) {
            URL.revokeObjectURL(removed.url);
        }
    }

    showDeleteImageModal.value = false;
    imageIndexToDelete.value = null;
};

// =====================================================
// IMAGE PREVIEW
// =====================================================

const getImageSrc = (gambar) => {
    if (
        typeof gambar === 'object' &&
        gambar?.url
    ) {
        return gambar.url;
    }

    if (
        typeof gambar === 'string' &&
        (
            gambar.startsWith('http') ||
            gambar.startsWith('/')
        )
    ) {
        return gambar;
    }

    return `https://picsum.photos/seed/${gambar}/300/400`;
};

const openImage = (gambar) => {
    selectedImage.value = getImageSrc(gambar);
    showImageModal.value = true;
};

// =====================================================
// VALIDATION
// =====================================================

const stripHtml = (html) => {
    if (!html) {
        return '';
    }

    const tmp =
        typeof document !== 'undefined'
            ? document.createElement('div')
            : null;

    if (!tmp) {
        return html.replace(/<[^>]*>?/gm, '');
    }

    tmp.innerHTML = html;

    return tmp.textContent || tmp.innerText || '';
};

const validateForm = () => {
    errors.value = {
        judul: '',
        deskripsi: '',
        deadline: '',
        kategori: '',
        gambar: ''
    };

    let isValid = true;

    const judul = (form.value.judul || '').trim();

    const deskripsi = (
        form.value.deskripsi || ''
    ).trim();

    const textDeskripsi = stripHtml(
        deskripsi
    ).trim();

    const deadline = form.value.deadline;

    // Judul
    if (!judul) {
        errors.value.judul =
            'Judul aktivitas wajib diisi.';

        isValid = false;
    }

    // Deskripsi
    if (
        !deskripsi ||
        (
            !textDeskripsi &&
            !deskripsi.includes('<img')
        )
    ) {
        errors.value.deskripsi =
            'Deskripsi aktivitas wajib diisi.';

        isValid = false;
    }

    // Deadline
    if (!deadline) {
        errors.value.deadline =
            'Deadline batas registrasi aktivitas wajib dipilih.';

        isValid = false;
    }

    // Kategori
    if (
        !form.value.kategori ||
        form.value.kategori.length === 0
    ) {
        errors.value.kategori =
            'Pilih minimal 1 kategori aktivitas.';

        isValid = false;
    }

    // Gambar
    if (
        !props.isEditMode &&
        (
            !form.value.gambar ||
            form.value.gambar.length === 0
        )
    ) {
        errors.value.gambar =
            'Minimal 1 gambar pendukung wajib diunggah.';

        isValid = false;
    }

    return isValid;
};

// =====================================================
// SUBMIT
// =====================================================

const submit = () => {
    formError.value = '';

    if (!validateForm()) {
        return;
    }

    emit('submit', {
        id: form.value.id,
        judul: form.value.judul.trim(),
        deskripsi: form.value.deskripsi,
        deadline: form.value.deadline,
        kategori: [...form.value.kategori],
        gambar: [...form.value.gambar],
        catatan_penolakan: form.value.catatan_penolakan
    });
};
</script>

<template>
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
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto overscroll-contain bg-slate-900/40 backdrop-blur-xs p-3 sm:p-6 flex justify-center items-start min-h-screen"
                @mousedown="handleBackdropMouseDown"
                @mouseup="handleBackdropMouseUp"
            >
                <Transition
                    enter-active-class="ease-out duration-200"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="ease-in duration-150"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="show"
                        class="relative w-full max-w-4xl my-auto transform rounded-[16px] sm:rounded-[20px] bg-white p-4 sm:p-7 shadow-2xl font-poppins border border-[#e2e8f0] overflow-visible"
                    >
                        <!-- HEADER -->
                        <div class="mb-4 sm:mb-6">
                            <h2 class="text-[18px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
                                {{
                                    isEditMode
                                        ? 'Edit Pengajuan Kegiatan'
                                        : 'Form Pengajuan Kegiatan'
                                }}
                            </h2>
                        </div>

                        <!-- ERROR -->
                        <div
                            v-if="formError"
                            class="mb-4 rounded-[10px] bg-red-50 p-2.5 sm:p-3 font-inter text-[12px] sm:text-[13px] text-red-600 border border-red-200"
                        >
                            {{ formError }}
                        </div>

                        <form
                            @submit.prevent="submit"
                            novalidate
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

                                <!-- ================================================= -->
                                <!-- KOLOM KIRI -->
                                <!-- ================================================= -->

                                <div class="flex flex-col gap-3.5 sm:gap-4">

                                    <!-- JUDUL -->
                                    <div>
                                        <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                                            Judul Aktivitas
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                                            Masukkan judul aktivitas yang kamu ajukan di sini yah!
                                        </p>

                                        <input
                                            v-model="form.judul"
                                            type="text"
                                            placeholder="Pelatihan manajer KDMP"
                                            @input="errors.judul = ''"
                                            class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
                                            :class="
                                                errors.judul
                                                    ? 'border-red-400 focus:border-red-500 bg-red-50/20'
                                                    : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'
                                            "
                                        />

                                        <p
                                            v-if="errors.judul"
                                            class="mt-1 font-inter text-[11px] font-medium text-red-500"
                                        >
                                            {{ errors.judul }}
                                        </p>
                                    </div>

                                    <!-- KATEGORI -->
                                    <div>
                                        <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                                            Kategori Talenta
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                                            Masukkan kemungkinan kategori dari aktivitas kamu yah!
                                        </p>

                                        <div class="grid grid-cols-2 gap-2 sm:gap-2.5 mt-2">
                                            <label
                                                v-for="kat in kategoriOptions"
                                                :key="kat"
                                                class="flex items-center gap-2 p-2 rounded-[8px] border transition cursor-pointer select-none"
                                                :class="
                                                    form.kategori.includes(kat)
                                                        ? 'border-[#183669] bg-[#183669]/5'
                                                        : errors.kategori
                                                            ? 'border-red-300 hover:border-red-400 bg-red-50/10'
                                                            : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff]'
                                                "
                                            >
                                                <input
                                                    v-model="form.kategori"
                                                    type="checkbox"
                                                    :value="kat"
                                                    @change="errors.kategori = ''"
                                                    class="h-4 w-4 rounded border-[#d6e0ee] text-[#183669] focus:ring-0 focus:outline-none cursor-pointer"
                                                />

                                                <span class="font-inter text-[12.5px] sm:text-[13px] font-medium text-[#1e3456]">
                                                    {{ kat }}
                                                </span>
                                            </label>
                                        </div>

                                        <p
                                            v-if="errors.kategori"
                                            class="mt-1 font-inter text-[11px] font-medium text-red-500"
                                        >
                                            {{ errors.kategori }}
                                        </p>
                                    </div>

                                    <!-- DESKRIPSI -->
                                    <div>
                                        <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                                            Deskripsi
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1 sm:mb-1.5">
                                            Jelaskan gambaran kegiatan ini yah!
                                        </p>

                                        <RichTextEditor
                                            v-model="form.deskripsi"
                                            placeholder="Pelatihan manajer KDMP"
                                            min-height="115px"
                                            :has-error="!!errors.deskripsi"
                                            @update:modelValue="errors.deskripsi = ''"
                                        />

                                        <p
                                            v-if="errors.deskripsi"
                                            class="mt-1 font-inter text-[11px] font-medium text-red-500"
                                        >
                                            {{ errors.deskripsi }}
                                        </p>
                                    </div>
                                </div>

                                <!-- ================================================= -->
                                <!-- KOLOM KANAN -->
                                <!-- ================================================= -->

                                <div class="flex flex-col gap-3.5 sm:gap-4">

                                    <!-- GAMBAR -->
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                                                Gambar
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <span class="font-inter text-[11px] sm:text-[12px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-[#183669] border border-slate-200">
                                                {{ form.gambar.length }}/3
                                            </span>
                                        </div>

                                        <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
                                            Masukkan gambar pendukung berupa jpg/png/jpeg! (MAX 10MB, 3 Gambar)
                                        </p>

                                        <!-- ADD IMAGE INPUT -->
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/png, image/jpeg, image/jpg"
                                            multiple
                                            class="hidden"
                                            @change="handleFileUpload"
                                        />

                                        <!-- REPLACE IMAGE INPUT -->
                                        <input
                                            ref="replaceFileInput"
                                            type="file"
                                            accept="image/png, image/jpeg, image/jpg"
                                            class="hidden"
                                            @change="handleReplaceImage"
                                        />

                                        <!-- DROP ZONE -->
                                        <div
                                            @dragover.prevent="isDragging = true"
                                            @dragleave.prevent="isDragging = false"
                                            @drop.prevent="handleImageDrop"
                                            :class="[
                                                'mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors min-h-[145px]',
                                                errors.gambar
                                                    ? 'border-red-400 bg-red-50/20'
                                                    : isDragging
                                                        ? 'border-[#183669] bg-[#183669]/5'
                                                        : 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60'
                                            ]"
                                        >

                                            <!-- BELUM ADA GAMBAR -->
                                            <div
                                                v-if="form.gambar.length === 0"
                                                class="flex flex-col items-center justify-center py-2.5"
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
                                                    (JPG/PNG, Rasio Disarankan 3:4)
                                                </p>

                                                <button
                                                    type="button"
                                                    @click="triggerFileInput"
                                                    class="mt-2.5 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer"
                                                >
                                                    Upload
                                                </button>
                                            </div>

                                            <!-- SUDAH ADA GAMBAR -->
                                            <div
                                                v-else
                                                class="w-full"
                                            >
                                                <div class="grid grid-cols-3 gap-2.5 sm:gap-3 w-full items-center justify-items-center">

                                                    <div
                                                        v-for="(gambar, index) in form.gambar"
                                                        :key="index"
                                                        class="relative aspect-[3/4] w-full max-w-[95px] sm:max-w-[110px] rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs group"
                                                    >
                                                        <img
                                                            :src="getImageSrc(gambar)"
                                                            class="h-full w-full object-cover"
                                                            alt="Preview gambar"
                                                        />

                                                        <!-- ACTION OVERLAY -->
                                                        <div class="absolute inset-0 bg-black/40 flex flex-col justify-between p-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100 transition-opacity">

                                                            <!-- DELETE -->
                                                            <div class="flex justify-end w-full">
                                                                <button
                                                                    type="button"
                                                                    @click.stop.prevent="openDeleteImageModal(index)"
                                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white shadow-sm hover:bg-red-700 hover:scale-110 active:scale-95 transition cursor-pointer"
                                                                    title="Hapus Gambar"
                                                                >
                                                                    <svg
                                                                        class="h-3.5 w-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="2.2"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                        />
                                                                    </svg>
                                                                </button>
                                                            </div>

                                                            <!-- PREVIEW + REPLACE -->
                                                            <div class="flex items-center justify-center gap-1.5 w-full pb-0.5">

                                                                <!-- PREVIEW -->
                                                                <button
                                                                    type="button"
                                                                    @click.stop.prevent="openImage(gambar)"
                                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white shadow-sm hover:bg-black/85 hover:scale-110 active:scale-95 transition cursor-pointer"
                                                                    title="Lihat Gambar"
                                                                >
                                                                    <svg
                                                                        class="h-3.5 w-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"
                                                                        />
                                                                    </svg>
                                                                </button>

                                                                <!-- REPLACE -->
                                                                <button
                                                                    type="button"
                                                                    @click.stop.prevent="triggerReplaceImage(index)"
                                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/90 text-white shadow-sm hover:bg-amber-600 hover:scale-110 active:scale-95 transition cursor-pointer"
                                                                    title="Ganti Gambar"
                                                                >
                                                                    <svg
                                                                        class="h-3.5 w-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="2"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
                                                                        />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- TAMBAH GAMBAR -->
                                                    <div
                                                        v-if="form.gambar.length < 3"
                                                        @click="triggerFileInput"
                                                        class="aspect-[3/4] w-full max-w-[95px] sm:max-w-[110px] rounded-[10px] border-2 border-dashed border-[#a6b7cb] hover:border-[#183669] hover:bg-slate-50/80 flex flex-col items-center justify-center cursor-pointer transition text-[#5a718d] group select-none"
                                                    >
                                                        <svg
                                                            class="h-6 w-6 text-[#8c9eb5] group-hover:text-[#183669] transition-colors"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M12 4.5v15m7.5-7.5h-15"
                                                            />
                                                        </svg>

                                                        <span class="mt-1 font-inter text-[10.5px] sm:text-[11px] font-medium text-[#7188a3] group-hover:text-[#183669]">
                                                            Tambah
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <p
                                            v-if="errors.gambar"
                                            class="mt-1 font-inter text-[11px] font-medium text-red-500"
                                        >
                                            {{ errors.gambar }}
                                        </p>
                                    </div>

                                    <!-- DEADLINE -->
                                    <div>
                                        <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                                            Deadline
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                                            Masukkan tanggal batas registrasi dari aktivitas kamu yah!
                                        </p>

                                        <div class="mt-1 sm:mt-1.5">
                                            <DatePicker
                                                v-model="form.deadline"
                                                :has-error="!!errors.deadline"
                                                placeholder="Pilih tanggal deadline"
                                                @update:modelValue="errors.deadline = ''"
                                            />
                                        </div>

                                        <p
                                            v-if="errors.deadline"
                                            class="mt-1 font-inter text-[11px] font-medium text-red-500"
                                        >
                                            {{ errors.deadline }}
                                        </p>
                                    </div>

                                    <!-- CATATAN PENOLAKAN -->
                                    <div v-if="isEditMode">
                                        <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                                            Catatan Penolakan Sebelumnya
                                        </label>

                                        <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
                                            Berikut adalah catatan penolakan sebelumnya.
                                            Catatan kosong apabila submisi belum pernah ditolak.
                                        </p>

                                        <textarea
                                            readonly
                                            :value="form.catatan_penolakan"
                                            rows="5"
                                            class="w-full rounded-[10px] border border-[#d6e0ee] bg-slate-50 px-3.5 py-3 font-inter text-[13px] text-[#7188a3] outline-none resize-none"
                                        ></textarea>
                                    </div>

                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="mt-6 sm:mt-8 flex flex-row items-center justify-center sm:justify-end gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-slate-100">

                                <button
                                    type="button"
                                    @click="handleClose"
                                    class="h-[42px] sm:h-[44px] min-w-[120px] sm:min-w-[140px] px-5 sm:px-6 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13px] sm:text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 active:scale-98 cursor-pointer"
                                >
                                    Kembali
                                </button>

                                <button
                                    type="submit"
                                    class="h-[42px] sm:h-[44px] min-w-[120px] sm:min-w-[140px] px-5 sm:px-6 rounded-[10px] bg-[#183669] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer"
                                >
                                    {{ isEditMode ? 'Simpan Perubahan' : 'Submit' }}
                                </button>

                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>

    <!-- DELETE IMAGE MODAL -->
    <DeleteModal
        :show="showDeleteImageModal"
        title="Hapus Gambar?"
        :message="`Apakah Anda yakin ingin menghapus foto lampiran ke-${imageIndexToDelete !== null ? imageIndexToDelete + 1 : ''} dari pengajuan ini?`"
        @close="showDeleteImageModal = false"
        @confirm="executeRemoveImage"
    >
        <template #confirm-text>
            Hapus Gambar
        </template>
    </DeleteModal>

    <!-- IMAGE LIGHTBOX -->
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
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4"
                @click="showImageModal = false"
            >
                <div
                    class="relative flex items-center justify-center"
                    @click.stop
                >
                    <button
                        type="button"
                        @click="showImageModal = false"
                        class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 shadow-md transition hover:scale-105 active:scale-95 cursor-pointer"
                        title="Tutup Preview"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <img
                        :src="selectedImage"
                        alt="Preview Gambar"
                        class="max-h-[85vh] max-w-[90vw] w-auto h-auto min-w-[280px] sm:min-w-[480px] rounded-xl object-contain shadow-2xl"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
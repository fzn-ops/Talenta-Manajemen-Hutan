<script setup>
import { ref } from 'vue';

defineProps({
  show: Boolean,
});

const emit = defineEmits(['close', 'submit']);
const reason = ref('');

// State untuk custom dropdown
const isOpen = ref(false);
const options = [
  'Kurang Mantap',
  'Data Tidak Lengkap',
  'Tidak Sesuai Ketentuan'
];

const selectReason = (opt) => {
  reason.value = opt;
  isOpen.value = false; // Tutup dropdown setelah milih
};

const handleSubmit = () => {
  if (!reason.value) return;
  emit('submit', reason.value);
  reason.value = ''; 
  isOpen.value = false;
};

const handleClose = () => {
  emit('close');
  reason.value = ''; 
  isOpen.value = false;
};
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm">
      
      <!-- Overlay utama untuk tutup modal -->
      <div class="absolute inset-0" @click="handleClose"></div>

      <Transition
        enter-active-class="transition duration-300 ease-out delay-75"
        enter-from-class="opacity-0 translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-95"
      >
        <div v-if="show" class="relative w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl text-center">
          
          <div class="mx-auto mb-5 flex justify-center">
            <svg class="h-[72px] w-[72px] text-[#ef4444]" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L1 21h22L12 2zm1 16h-2v-2h2v2zm0-4h-2V10h2v4z"/>
            </svg>
          </div>

          <h2 class="text-lg font-bold text-[#152c5b] mb-6 px-4">
            Apakah Kamu yakin Ingin Menolak Pengajuan Ini?
          </h2>

          <div class="text-left mb-8 relative">
            <label class="block text-xs text-red-500 mb-2 font-medium">*Masukan alasan dari penolakan</label>
            
            <!-- CUSTOM DROPDOWN -->
            <div class="relative">
              <!-- Tombol Dropdown -->
              <button 
                type="button"
                @click="isOpen = !isOpen"
                class="flex w-full items-center justify-between rounded-lg border-2 px-4 py-3 text-sm font-medium outline-none transition-colors"
                :class="isOpen ? 'border-[#48796f] text-gray-800' : 'border-gray-400 text-gray-700 hover:border-gray-500'"
              >
                <!-- Teks Terpilih atau Placeholder -->
                <span :class="!reason ? 'text-gray-500' : 'text-gray-800'">
                  {{ reason || 'Pilih alasan penolakan' }}
                </span>
                
                <!-- Ikon Panah dengan Animasi Muter (Rotate) -->
                <svg 
                  class="h-4 w-4 text-gray-500 transition-transform duration-300 ease-in-out"
                  :class="isOpen ? 'rotate-180' : 'rotate-0'"
                  fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Overlay transparan buat nutup dropdown kalau user klik di luar kotak opsi -->
              <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-10"></div>

              <!-- List Pilihan (Muncul Melayang) -->
              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
              >
                <div v-if="isOpen" class="absolute left-0 right-0 top-[110%] z-20 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl">
                  <ul class="flex flex-col py-1">
                    <li 
                      v-for="(opt, index) in options" 
                      :key="index"
                      @click="selectReason(opt)"
                      class="cursor-pointer px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-[#3b82f6]"
                    >
                      {{ opt }}
                    </li>
                  </ul>
                </div>
              </Transition>
            </div>
            
          </div>

          <div class="flex items-center justify-center gap-4">
            <button @click="handleClose" class="w-32 rounded-lg bg-[#152c5b] py-2.5 text-sm font-bold text-white transition hover:bg-[#0e1d3e] hover:scale-105">
              Tidak
            </button>
            <button 
              @click="handleSubmit" 
              :disabled="!reason"
              class="w-32 rounded-lg border-2 py-2.5 text-sm font-bold transition shadow-sm disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:border-gray-200"
              :class="reason ? 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:scale-105' : ''"
            >
              Yakin
            </button>
          </div>
        </div>
      </Transition>

    </div>
  </Transition>
</template>
<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
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
  if (document.activeElement instanceof HTMLElement) {
    document.activeElement.blur();
  }
  emit('close');
  reason.value = ''; 
  isOpen.value = false;
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.show) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
    if (isOpen.value) {
      isOpen.value = false;
    } else {
      handleClose();
    }
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown, true);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown, true);
});
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[120] flex items-center justify-center overflow-y-auto overscroll-contain bg-slate-900/60 p-4 backdrop-blur-xs transition-opacity duration-200" @click.self="handleClose">
        
        <Transition
          enter-active-class="transition duration-300 ease-out delay-75"
          enter-from-class="opacity-0 translate-y-4 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 scale-100"
          leave-to-class="opacity-0 translate-y-4 scale-95"
        >
          <div v-if="show" class="relative w-full max-w-md rounded-[18px] sm:rounded-[20px] bg-white p-6 sm:p-8 shadow-2xl text-center font-poppins border border-[#e2e8f0]">
            
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
              <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L1 21h22L12 2zm1 16h-2v-2h2v2zm0-4h-2V10h2v4z"/>
              </svg>
            </div>

            <h2 class="text-[17px] sm:text-[19px] font-bold text-[#183669] mb-4 sm:mb-5 px-2 leading-snug">
              Apakah Kamu yakin Ingin Menolak Pengajuan Ini?
            </h2>

            <div class="text-left mb-6 relative">
              <label class="block text-xs text-red-500 mb-1.5 font-medium">*Masukan alasan dari penolakan</label>
              
              <!-- CUSTOM DROPDOWN -->
              <div class="relative">
                <!-- Tombol Dropdown -->
                <button 
                  type="button"
                  @click="isOpen = !isOpen"
                  class="flex w-full items-center justify-between rounded-[10px] border px-3.5 py-2.5 text-sm font-medium outline-none transition-colors cursor-pointer bg-white"
                  :class="isOpen ? 'border-[#183669] text-gray-800' : 'border-[#d6e0ee] text-gray-700 hover:border-[#8ea9cb]'"
                >
                  <span :class="!reason ? 'text-gray-400 font-normal text-xs sm:text-sm' : 'text-gray-800 text-xs sm:text-sm'">
                    {{ reason || 'Pilih alasan penolakan' }}
                  </span>
                  
                  <svg 
                    class="h-4 w-4 text-gray-500 transition-transform duration-200 shrink-0"
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
                  enter-active-class="transition duration-150 ease-out"
                  enter-from-class="opacity-0 translate-y-1 scale-95"
                  enter-to-class="opacity-100 translate-y-0 scale-100"
                  leave-active-class="transition duration-100 ease-in"
                  leave-from-class="opacity-100 translate-y-0 scale-100"
                  leave-to-class="opacity-0 translate-y-1 scale-95"
                >
                  <div v-if="isOpen" class="absolute left-0 right-0 top-full mt-1.5 z-20 overflow-hidden rounded-[10px] border border-[#d6e0ee] bg-white shadow-xl ring-1 ring-black/5">
                    <ul class="flex flex-col py-1">
                      <li 
                        v-for="(opt, index) in options" 
                        :key="index"
                        @click="selectReason(opt)"
                        class="cursor-pointer px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-700 transition-colors hover:bg-slate-50 hover:text-[#183669]"
                      >
                        {{ opt }}
                      </li>
                    </ul>
                  </div>
                </Transition>
              </div>
              
            </div>

            <div class="flex items-center justify-center gap-3 sm:gap-4">
              <button 
                type="button"
                @click="handleClose" 
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] border border-[#d6e0ee] bg-white py-2.5 text-xs sm:text-sm font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 cursor-pointer select-none outline-none focus:outline-none focus:ring-0"
                style="outline: none !important; box-shadow: none !important;"
              >
                Tidak
              </button>
              <button 
                type="button"
                @click="handleSubmit" 
                :disabled="!reason"
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] bg-[#ef4444] py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-red-700 shadow-sm disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 cursor-pointer select-none outline-none focus:outline-none focus:ring-0"
                style="outline: none !important; box-shadow: none !important;"
              >
                Yakin
              </button>
            </div>
          </div>
        </Transition>

      </div>
    </Transition>
  </Teleport>
</template>
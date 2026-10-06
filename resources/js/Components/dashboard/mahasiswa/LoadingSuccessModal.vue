<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  status: { type: String, default: 'loading' }, // 'loading' | 'success'
  title: { type: String, default: 'Berhasil!' },
  message: { type: String, default: 'Data berhasil diproses.' }
});

const emit = defineEmits(['close']);
</script>

<template>
  <Teleport to="body">
    <!-- BACKDROP & MODAL WRAPPER -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm">
        
        <!-- MODAL BOX -->
        <Transition
          enter-active-class="transition duration-300 ease-out delay-75"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div v-if="show" class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-white p-8 text-center shadow-xl">
            
            <!-- TRANSISI ANTARA LOADING & SUCCESS -->
            <Transition
              mode="out-in"
              enter-active-class="transition duration-300 ease-out"
              enter-from-class="opacity-0 translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition duration-200 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-2"
            >
              
              <!-- ========================================== -->
              <!-- STATE 1: LOADING -->
              <!-- ========================================== -->
              <div v-if="status === 'loading'" class="flex flex-col items-center justify-center py-4">
                <svg class="mb-6 h-14 w-14 animate-spin text-[#183669]" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-xl font-bold text-[#1a2b4c]">Memproses Data...</h3>
                <p class="mt-2 text-sm text-gray-500">Mohon tunggu sebentar.</p>
              </div>

              <!-- ========================================== -->
              <!-- STATE 2: SUCCESS -->
              <!-- ========================================== -->
              <div v-else-if="status === 'success'" class="flex flex-col items-center justify-center py-2">
                
                <!-- Lingkaran Hijau & Animasi Centang -->
                <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                  <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <!-- Class .draw-check yang bikin animasi garisnya -->
                    <path class="draw-check" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                
                <h3 class="text-xl font-bold text-[#1a2b4c]">{{ title }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ message }}</p>
                
                <button @click="emit('close')" class="mt-8 w-full rounded-xl bg-[#183669] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#122b54] active:scale-95">
                  Selesai
                </button>
              </div>

            </Transition>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* Animasi rahasia biar centangnya kayak digambar pelan-pelan */
.draw-check {
  stroke-dasharray: 50;
  stroke-dashoffset: 50;
  animation: draw 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  animation-delay: 0.15s; /* Jeda dikit biar gak balapan sama pop-up bulatnya */
}

@keyframes draw {
  to {
    stroke-dashoffset: 0;
  }
}
</style>
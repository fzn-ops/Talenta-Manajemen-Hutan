<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'confirm']);
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
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
      <div class="absolute inset-0" @click="emit('close')"></div>
      
      <Transition 
        enter-active-class="transition duration-300 ease-out delay-75" 
        enter-from-class="opacity-0 translate-y-4 scale-95" 
        enter-to-class="opacity-100 translate-y-0 scale-100" 
        leave-active-class="transition duration-200 ease-in" 
        leave-from-class="opacity-100 translate-y-0 scale-100" 
        leave-to-class="opacity-0 translate-y-4 scale-95"
      >
        <div v-if="show" class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl text-center">
          <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="mb-2 text-xl font-bold text-[#1a2b4c]">Hapus Aktivitas?</h3>
          <p class="mb-6 text-sm text-gray-500">Apakah Anda yakin ingin menghapus aktivitas ini? Data yang dihapus tidak dapat dikembalikan.</p>
          
          <div class="flex justify-center gap-3">
            <button @click="emit('close')" class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50">Batal</button>
            <button @click="emit('confirm')" class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 shadow-md">Ya, Hapus</button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
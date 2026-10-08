<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'submit']);

// Dummy Data
const roadmaps = ref([
  { id: 1, judul: 'Lorem Ipsum Dolor sit amet...', kategori: 'Birokrat', totalTask: 45, partisipan: 100 },
  { id: 2, judul: 'Lorem Ipsum Dolor sit amet...', kategori: 'Birokrat', totalTask: 45, partisipan: 100 },
  { id: 3, judul: 'Lorem Ipsum Dolor sit amet...', kategori: 'Profesional', totalTask: 32, partisipan: 85 },
  { id: 4, judul: 'Lorem Ipsum Dolor sit amet...', kategori: 'Bisnis', totalTask: 28, partisipan: 150 },
  { id: 5, judul: 'Lorem Ipsum Dolor sit amet...', kategori: 'Akademisi', totalTask: 50, partisipan: 200 },
]);

// State untuk checkbox yang dipilih
const selectedIds = ref([]);

// Reset selection ketika modal ditutup/dibuka
watch(() => props.show, (newVal) => {
  if (!newVal) {
    selectedIds.value = [];
  }
});

// Logic untuk "Select All" checkbox
const selectAll = computed({
  get() {
    return roadmaps.value.length > 0 && selectedIds.value.length === roadmaps.value.length;
  },
  set(value) {
    if (value) {
      selectedIds.value = roadmaps.value.map(item => item.id);
    } else {
      selectedIds.value = [];
    }
  }
});

const handleSubmit = () => {
  // Mengirimkan array ID yang dipilih ke parent component
  emit('submit', selectedIds.value);
  
};
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop Transition -->
    <Transition 
      enter-active-class="transition duration-300 ease-out" 
      enter-from-class="opacity-0" 
      enter-to-class="opacity-100" 
      leave-active-class="transition duration-200 ease-in" 
      leave-from-class="opacity-100" 
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        
        <!-- Background Overlay (Klik luar untuk tutup) -->
        <div class="absolute inset-0" @click="emit('close')"></div>
        
        <!-- Modal Content Transition -->
        <Transition 
          enter-active-class="transition duration-300 ease-out delay-75" 
          enter-from-class="opacity-0 scale-95 translate-y-4" 
          enter-to-class="opacity-100 scale-100 translate-y-0" 
          leave-active-class="transition duration-200 ease-in" 
          leave-from-class="opacity-100 scale-100 translate-y-0" 
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div v-if="show" class="relative w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl bg-white p-8 shadow-2xl flex flex-col">
            
            <!-- HEADER MODAL -->
            <div class="mb-8 text-center shrink-0">
              <h2 class="text-2xl font-extrabold text-[#1a2b4c]">Daftar Roadmap</h2>
              <p class="mt-1.5 text-[15px] font-medium text-[#4d6786]">Yuk Pilih Roadmap yang Kamu Inginkan!</p>
            </div>

            <!-- TABLE CONTENT -->
            <div class="overflow-y-auto overflow-x-auto flex-1 custom-scrollbar pb-4">
              <table class="w-full text-left text-sm whitespace-nowrap border-separate border-spacing-y-3">
                
                <!-- THEAD dengan Background Hijau & Rounded Corners -->
                <thead>
                  <tr class="bg-[#48796f] text-white">
                    <th class="px-5 py-4 font-semibold rounded-l-lg w-16 text-center">
                      <input 
                        type="checkbox" 
                        v-model="selectAll"
                        class="h-4 w-4 rounded border-white/50 bg-white/20 text-[#1a2b4c] focus:ring-0 focus:ring-offset-0 cursor-pointer"
                      />
                    </th>
                    <th class="px-4 py-4 font-semibold">Judul</th>
                    <th class="px-4 py-4 font-semibold text-center w-40">Kategori</th>
                    <th class="px-4 py-4 font-semibold text-center w-32">Total Task</th>
                    <th class="px-5 py-4 font-semibold rounded-r-lg text-center w-32">Partisipan</th>
                  </tr>
                </thead>
                
                <!-- TBODY -->
                <tbody class="text-gray-600 font-medium">
                  <tr 
                    v-for="item in roadmaps" 
                    :key="item.id" 
                    class="transition-colors hover:bg-gray-50/80 group"
                  >
                    <td class="px-5 py-3 text-center border-b border-gray-100 group-last:border-none">
                      <input 
                        type="checkbox" 
                        :value="item.id" 
                        v-model="selectedIds"
                        class="h-4 w-4 rounded border-gray-300 text-[#48796f] focus:ring-[#48796f] cursor-pointer"
                      />
                    </td>
                    <td class="px-4 py-3 border-b border-gray-100 group-last:border-none text-[#1a2b4c]">
                      {{ item.judul }}
                    </td>
                    <td class="px-4 py-3 text-center border-b border-gray-100 group-last:border-none text-[#4d6786]">
                      {{ item.kategori }}
                    </td>
                    <td class="px-4 py-3 text-center border-b border-gray-100 group-last:border-none text-[#1a2b4c]">
                      {{ item.totalTask }}
                    </td>
                    <td class="px-5 py-3 text-center border-b border-gray-100 group-last:border-none text-[#1a2b4c]">
                      {{ item.partisipan }}
                    </td>
                  </tr>
                  
                  <tr v-if="roadmaps.length === 0">
                    <td colspan="5" class="py-10 text-center text-gray-500">
                      Tidak ada roadmap tersedia.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- FOOTER BUTTONS -->
            <div class="mt-8 flex justify-end gap-4 shrink-0 pt-4 border-t border-gray-100">
              <button 
                type="button" 
                @click="emit('close')" 
                class="rounded-lg border border-gray-300 bg-white w-32 py-2.5 text-sm font-bold text-[#1a2b4c] transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200"
              >
                Kembali
              </button>
              <button 
                type="button" 
                @click="handleSubmit"
                :disabled="selectedIds.length === 0"
                :class="[
                  'rounded-lg w-32 py-2.5 text-sm font-bold text-white shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-[#1a2b4c] focus:ring-offset-2',
                  selectedIds.length === 0 ? 'bg-gray-400 cursor-not-allowed shadow-none' : 'bg-[#1a2b4c] hover:bg-[#12203b] active:scale-95'
                ]"
              >
                Tambahkan
              </button>
            </div>
            
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* Optional: Custom scrollbar untuk tabel jika datanya banyak */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>
```eof
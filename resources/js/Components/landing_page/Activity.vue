<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const tabs = ['Semua', 'Umum', 'Lomba'];

const props = defineProps({
    data: {
        type: Object,
        required: true,
        default: () => ({ data: [] })
    },
    currentJenis: {
        type: String,
        default: 'Semua'
    }
});

const activeTab = ref(props.currentJenis);

const filterCategory = (tab) => {
    activeTab.value = tab;
    
    router.get('/', { 
        jenis: tab === 'Semua' ? undefined : tab
    }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
};

const getTagColor = (tag) => {
  const colors = {
    'Profesional': 'text-teal-700',
    'Bisnis': 'text-amber-500',
    'Birokrat': 'text-red-500',
    'Akademisi': 'text-blue-600'
  }
  return colors[tag] || 'text-slate-700'
}

const featured = computed(() => {
    return props.data.data && props.data.data.length > 0 ? props.data.data[0] : {
        partisipan: 0,
        deadline: '-',
        judul: 'Belum ada aktivitas',
        deskripsi: 'Data aktivitas belum tersedia saat ini untuk filter ini.',
        kategori: []
    }
})

const cards = computed(() => {
    return props.data.data && props.data.data.length > 1 ? props.data.data.slice(1) : []
})

const getImageUrl = (path) => {
    if (!path) {
        return 'https://ui-avatars.com/api/?name=Activity&background=random'; 
    }
    
    // 2. Kalau ada isinya, cek http atau bersihkan storage/
    if (typeof path === 'string' && (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:'))) {
        return path;
    }
    const clean = String(path).replace(/^\/?(public\/|storage\/)+/, '');
    return `/storage/${clean}`;
};
</script>


<template>
  <div class="min-h-screen bg-[#fcfcfb] py-16 px-4 relative overflow-hidden font-poppins">
    
    <!-- Ornamen Background (Opsional, untuk meniru coretan dan bintang) -->
    <div class="absolute top-10 left-10 text-slate-300 w-32 h-32 border-l-2 border-t-2 border-dashed rounded-tl-full opacity-50"></div>
    <div class="absolute bottom-10 right-10 text-slate-300 w-48 h-48 border-r-2 border-b-2 border-dashed rounded-br-full opacity-50"></div>
    
    <div class="max-w-7xl mx-auto relative z-10">
      
      <!-- Bagian Header & Tabs -->
      <div class="flex flex-col items-center mb-10">
        <h1 class="text-4xl font-extrabold text-[#1e3856] mb-6">Kegiatan Terkini</h1>
        
        <div class="flex gap-8 border-b-2 border-transparent">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            @click="filterCategory(tab)"
            class="pb-2 text-sm font-semibold transition-all"
            :class="activeTab === tab ? 'text-[#1e3856] border-b-2 border-[#1e3856]' : 'text-slate-500 hover:text-[#1e3856]'"
          >
            {{ tab }}
          </button>
        </div>
      </div>

      <!-- Grid Konten Utama -->
      <div class="flex flex-col lg:flex-row gap-6">
        <div class="lg:w-1/3">
          <!-- Bungkus dengan Link Inertia.js -->
          <Link 
            v-if="featured" 
            :href="`/activities/${featured.id}`" 
            class="relative block h-full min-h-[500px] rounded-2xl overflow-hidden group cursor-pointer shadow-sm hover:scale-[1.02] transition-all duration-300"
          >
            <!-- Ganti src dengan gambar aslinya -->
            <img 
                :src="getImageUrl(featured?.gambar_aktivitas?.find(g => g.utama == 1 || g.utama === true)?.gambar || featured.gambar_aktivitas?.[0]?.gambar)" 
                :alt="featured?.judul" 
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
            />

            <!-- Overlay Gradient Hijau -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#366c5a] via-[#366c5a]/50 to-transparent"></div>

            <div class="absolute inset-0 p-6 flex flex-col justify-between">
              <!-- Badge Peserta -->
              <div class="flex items-center justify-between w-full">
                  <!-- Badge Jenis di Kiri -->
                  <div 
                    :class="[
                      'px-3 py-1.5 rounded-full text-xs font-bold shadow-sm capitalize text-white',
                      featured?.jenis?.toLowerCase() === 'lomba' 
                        ? 'bg-amber-600'  
                        : 'bg-[#4b857a]'  
                    ]"
                  >
                    {{ featured?.jenis }}
                  </div>
                  <div class="bg-white px-3 py-1.5 rounded-full flex items-center gap-1.5 text-xs font-bold text-slate-700 shadow-sm">
                      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                      </svg>
                      {{ featured?.partisipan }}
                  </div>
              </div>

              <!-- Info Bawah -->
              <div class="text-white">
                <div class="flex items-center gap-2 text-xs opacity-90 mb-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round"        stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Deadline : {{ featured.deadline }}
                </div>
                <h3 class="text-3xl font-bold mb-3">{{ featured.judul }}</h3>
                <p class="text-sm opacity-90 mb-5 line-clamp-4 leading-relaxed">
                  {{ featured.deskripsi }}
                </p>

                <!-- Tags -->
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="tag in featured.kategori" 
                    :key="tag" 
                    class="bg-white px-2.5 py-1 rounded-full text-[10px] font-bold"
                    :class="getTagColor(tag)"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>
          </Link>
        </div>

        <!-- Kolom Kanan: Grid Kartu Kecil -->
        <div class="lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link 
            v-for="card in cards"
            :href="`/activities/${card.id}`"  
            :key="card.id" 
            class="relative h-[280px] rounded-2xl overflow-hidden group cursor-pointer shadow-sm hover:scale-105 transition-transform duration-300"
          >
            <!-- Ganti src dengan gambar aslinya -->
            <img 
                :src="getImageUrl(card?.gambar_aktivitas?.find(g => g.utama == 1 || g.utama === true)?.gambar || card.gambar_aktivitas?.[0]?.gambar)" 
                :alt="featured?.judul" 
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
            />
            
            <!-- Overlay Gradient Hijau -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#366c5a] via-[#366c5a]/50 to-transparent"></div>
            
            <div class="absolute inset-0 p-4 flex flex-col justify-between">
              <!-- Badge Peserta -->
                <div class="flex items-center justify-between w-full">
                    <div 
                      :class="[
                        'px-3 py-1.5 rounded-full text-xs font-bold shadow-sm capitalize text-white',
                        card?.jenis?.toLowerCase() === 'lomba' 
                          ? 'bg-amber-600'  
                          : 'bg-[#4b857a]'
                      ]"
                    >
                      {{ card?.jenis }}
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-full flex items-center gap-1.5 text-xs font-bold text-slate-700 shadow-sm">
                      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                      </svg>
                      {{ card?.partisipan }}
                    </div>
                  </div>
              
              <!-- Info Bawah -->
              <div class="text-white">
                <div class="flex items-center gap-1.5 text-[10px] opacity-90 mb-1.5">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Deadline : {{ card.deadline }}
                </div>
                <h3 class="text-base font-bold mb-1.5 line-clamp-1">{{ card.judul }}</h3>
                <p class="text-[10px] opacity-90 mb-3 line-clamp-2 leading-tight">
                  {{ card.deskripsi }}
                </p>
                
                <!-- Tags -->
                <div class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="tag in card.kategori" 
                    :key="tag" 
                    class="bg-white px-2 py-0.5 rounded-full text-[8px] font-bold"
                    :class="getTagColor(tag)"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>
          </Link>
        </div>

      </div>

      <!-- Tombol Lebih Banyak -->
      <div class="flex justify-center mt-12">
        <Link 
        :href="'/activities'"
        class="bg-[#1e3856] text-white px-6 py-2.5 rounded-full flex items-center gap-2 text-sm font-semibold hover:bg-slate-800 transition-colors shadow-md">
          Lebih Banyak
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </Link>
      </div>

    </div>
  </div>
</template>
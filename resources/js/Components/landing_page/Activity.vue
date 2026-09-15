<script setup>
import { ref } from 'vue'

const tabs = ['Semua', 'Aktivitas', 'Lomba']
const activeTab = ref('Semua')

// Helper function untuk warna tag sesuai gambar
const getTagColor = (tag) => {
  const colors = {
    'Profesional': 'text-teal-700',
    'Bisnis': 'text-amber-500',
    'Birokrat': 'text-red-500',
    'Akademisi': 'text-blue-600'
  }
  return colors[tag] || 'text-slate-700'
}

// Data Dummy sesuai gambar referensi
const featured = {
  participants: 67,
  deadline: '21 Agustus 2029',
  title: 'First Meet MNH 63',
  description: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit laborum consectetur ad laboris adipisicing nostrud in veniam anim. Et enim nostrud nisi consectetur deserunt sunt eu sunt. Ut in aliquip in tempor consectetur deserunt culpa voluptate.',
  tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi']
}

const cards = Array.from({ length: 6 }).map((_, index) => ({
  id: index + 1,
  participants: 67,
  deadline: '21 Agustus 2029',
  title: 'First Meet MNH...',
  description: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit...',
  tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi']
}))
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
            @click="activeTab = tab"
            class="pb-2 text-sm font-semibold transition-all"
            :class="activeTab === tab ? 'text-[#1e3856] border-b-2 border-[#1e3856]' : 'text-slate-500 hover:text-[#1e3856]'"
          >
            {{ tab }}
          </button>
        </div>
      </div>

      <!-- Grid Konten Utama -->
      <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Kolom Kiri: Kartu Highlight (Besar) -->
        <div class="lg:w-1/3">
          <div class="relative h-full min-h-[500px] rounded-2xl overflow-hidden group cursor-pointer shadow-sm hover:scale-105 transition-transform duration-300">
            <!-- Ganti src dengan gambar aslinya -->
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800" alt="Highlight" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
            
            <!-- Overlay Gradient Hijau -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#366c5a] via-[#366c5a]/50 to-transparent"></div>
            
            <div class="absolute inset-0 p-6 flex flex-col justify-between">
              <!-- Badge Peserta -->
              <div class="self-end bg-white px-3 py-1.5 rounded-full flex items-center gap-1.5 text-xs font-bold text-slate-700 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                {{ featured.participants }}
              </div>
              
              <!-- Info Bawah -->
              <div class="text-white">
                <div class="flex items-center gap-2 text-xs opacity-90 mb-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Deadline : {{ featured.deadline }}
                </div>
                <h3 class="text-3xl font-bold mb-3">{{ featured.title }}</h3>
                <p class="text-sm opacity-90 mb-5 line-clamp-4 leading-relaxed">
                  {{ featured.description }}
                </p>
                
                <!-- Tags -->
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="tag in featured.tags" 
                    :key="tag" 
                    class="bg-white px-2.5 py-1 rounded-full text-[10px] font-bold"
                    :class="getTagColor(tag)"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Grid Kartu Kecil -->
        <div class="lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="card in cards" 
            :key="card.id" 
            class="relative h-[280px] rounded-2xl overflow-hidden group cursor-pointer shadow-sm hover:scale-105 transition-transform duration-300"
          >
            <!-- Ganti src dengan gambar aslinya -->
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=400" alt="Card" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
            
            <!-- Overlay Gradient Hijau -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#366c5a] via-[#366c5a]/50 to-transparent"></div>
            
            <div class="absolute inset-0 p-4 flex flex-col justify-between">
              <!-- Badge Peserta -->
              <div class="self-end bg-white px-2 py-1 rounded-full flex items-center gap-1 text-[10px] font-bold text-slate-700 shadow-sm">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                {{ card.participants }}
              </div>
              
              <!-- Info Bawah -->
              <div class="text-white">
                <div class="flex items-center gap-1.5 text-[10px] opacity-90 mb-1.5">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  Deadline : {{ card.deadline }}
                </div>
                <h3 class="text-base font-bold mb-1.5 line-clamp-1">{{ card.title }}</h3>
                <p class="text-[10px] opacity-90 mb-3 line-clamp-2 leading-tight">
                  {{ card.description }}
                </p>
                
                <!-- Tags -->
                <div class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="tag in card.tags" 
                    :key="tag" 
                    class="bg-white px-2 py-0.5 rounded-full text-[8px] font-bold"
                    :class="getTagColor(tag)"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Tombol Lebih Banyak -->
      <div class="flex justify-center mt-12">
        <button class="bg-[#1e3856] text-white px-6 py-2.5 rounded-full flex items-center gap-2 text-sm font-semibold hover:bg-slate-800 transition-colors shadow-md">
          Lebih Banyak
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
      </div>

    </div>
  </div>
</template>
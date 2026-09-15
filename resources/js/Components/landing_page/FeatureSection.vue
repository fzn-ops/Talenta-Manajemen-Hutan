<script setup>
const features = [
  {
    title: 'E-Learning',
    description: 'Disini kamu bisa belajar banyak hal terkait dengan talenta yang telah kamu temukan pada diri kamu saat ini.',
    iconPlaceholder: '/assets/icons/icon_elearning.svg'
  },
  {
    title: 'Ragam Aktivitas',
    description: 'Temukan aktivitas yang sesuai untuk melatih talentamu.',
    iconPlaceholder: '/assets/icons/icon_ragam_aktivitas.svg'
  },
  {
    title: 'Sertifikat',
    description: 'Setelah kamu menyelesaikan semua roadmap, kamu akan mendapatkan sertifikat sebagai bukti bahwa kamu telah menyelesaikan rangkaian pelatihan',
    iconPlaceholder: '/assets/icons/icon_sertifikat.svg'
  },
  {
    title: 'Info Karir',
    description: 'Kamu bisa mencari informasi karir yang sedang tersedia sesuai dengan jurusanmu.',
    iconPlaceholder: '/assets/icons/icon_info_karir.svg'
  }
];
</script>

<template>
  <section class="w-full bg-[#4b857a] py-12 lg:py-24 font-poppins text-white">
    <div class="mx-auto w-full max-w-7xl px-4 md:px-10">
      <h2 class="mb-8 text-center text-2xl font-extrabold tracking-wide md:text-4xl lg:mb-16">
        Apa yang Talenta sediakan?
      </h2>

      <!-- Ubah grid-cols-1 menjadi grid-cols-2 untuk default (mobile) -->
      <!-- Gap dikecilkan di mobile (gap-4) agar muat, lalu dibesarkan di tablet/desktop (sm:gap-6) -->
      <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4 items-stretch">
        <div 
          v-for="feature in features" 
          :key="feature.title" 
          class="flex flex-col rounded-[1.25rem] bg-[#fcfcfb] p-4 sm:p-6 lg:p-8 shadow-xl transition-transform duration-300 hover:-translate-y-2"
        >
          <div class="mb-4 sm:mb-6 lg:mb-8 -ml-4 -mt-2 sm:-ml-6 sm:-mt-2 lg:-ml-8 lg:-mt-4">
            <div class="h-20 sm:h-28 lg:h-36 w-[120%] transition-transform duration-300 group-hover:scale-[1.03]">
              <img 
                :src="feature.iconPlaceholder" 
                :alt="feature.title" 
                class="h-full w-full max-w-none object-contain object-left" 
              />
            </div>
          </div>
          
          <!-- Ukuran font judul dikecilkan sedikit di mobile -->
          <h3 class="mb-2 sm:mb-3 text-base sm:text-xl font-extrabold text-[#152c5b] leading-tight">
            {{ feature.title }}
          </h3>
          
          <!-- Ukuran font deskripsi disesuaikan agar tidak terlalu penuh -->
          <p class="text-[11px] sm:text-[13px] font-medium leading-relaxed text-[#475569]">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
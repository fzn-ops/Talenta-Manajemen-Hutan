<script setup>
const features = [
  {
    title: 'E-Learning',
    description: 'Disini kamu bisa belajar banyak hal terkait dengan talenta yang telah kamu temukan pada diri kamu saat ini.',
    iconPlaceholder: 'Icon E-Learning'
  },
  {
    title: 'Ragam Aktivitas',
    description: 'Temukan aktivitas yang sesuai untuk melatih talentamu.',
    iconPlaceholder: 'Icon Aktivitas'
  },
  {
    title: 'Sertifikat',
    description: 'Setelah kamu menyelesaikan semua roadmap, kamu akan mendapatkan sertifikat sebagai bukti bahwa kamu telah menyelesaikan rangkaian pelatihan',
    iconPlaceholder: 'Icon Sertifikat'
  },
  {
    title: 'Info Karir',
    description: 'Kamu bisa mencari informasi karir yang sedang tersedia sesuai dengan jurusanmu.',
    iconPlaceholder: 'Icon Karir'
  }
];
</script>

<template>
  <section class="w-full bg-[#4b857a] py-16 lg:py-24 font-sans text-white">
    <div class="mx-auto w-full max-w-7xl px-6 md:px-10">
      <h2 class="mb-12 text-center text-3xl font-extrabold tracking-wide md:text-4xl lg:mb-16">
        Apa yang Talenta sediakan?
      </h2>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6 items-stretch">
        <div 
          v-for="feature in features" 
          :key="feature.title" 
          class="flex flex-col rounded-[1.25rem] bg-[#fcfcfb] p-6 shadow-xl transition-transform duration-300 hover:-translate-y-2 lg:p-8"
        >
          <div class="mb-6 flex h-24 items-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-[#152c5b] text-center text-[10px] font-bold leading-tight text-white shadow-sm">
              {{ feature.iconPlaceholder }}
            </div>
          </div>
          <h3 class="mb-3 text-xl font-extrabold text-[#152c5b]">
            {{ feature.title }}
          </h3>
          <p class="text-[13px] font-medium leading-relaxed text-[#475569] sm:text-sm">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
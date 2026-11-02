<script setup>
defineProps({
  type: {
    type: String,
    default: 'activity',
    validator: (val) => ['activity', 'activity-featured-layout', 'news', 'career', 'participant'].includes(val)
  },
  count: {
    type: Number,
    default: 4
  },
  activeTab: {
    type: String,
    default: 'mahasiswa'
  }
});
</script>

<template>
  <!-- SKELETON KARTU PARTISIPAN (MODAL ROADMAP) -->
  <div
    v-if="type === 'participant'"
    class="flex flex-col space-y-2 animate-pulse select-none"
  >
    <!-- 1. Skeleton untuk Tab Mahasiswa (dengan bar progres dan tombol rincian) -->
    <template v-if="activeTab !== 'materi'">
      <div
        v-for="n in count"
        :key="'skeleton-participant-' + n"
        class="rounded-xl border border-[#eef2f6] bg-white overflow-hidden shadow-2xs"
      >
        <div class="p-3 sm:p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4">
          <!-- Left: Avatar & Identity -->
          <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="h-8 w-8 sm:h-9 sm:w-9 shrink-0 rounded-full bg-slate-200"></div>
            <div class="min-w-0 flex-1 space-y-1.5">
              <div class="flex items-center gap-1.5">
                <div class="h-3.5 w-32 sm:w-40 rounded bg-slate-200"></div>
                <div class="h-3 w-10 rounded bg-slate-200/80"></div>
              </div>
              <div class="flex items-center gap-1.5">
                <div class="h-2.5 w-16 rounded bg-slate-200"></div>
                <span class="text-slate-300">•</span>
                <div class="h-2.5 w-14 rounded bg-slate-200"></div>
                <span class="text-slate-300">•</span>
                <div class="h-2.5 w-20 rounded bg-slate-200"></div>
              </div>
            </div>
          </div>

          <!-- Right: Progress bar & Expand button -->
          <div class="flex items-center justify-between sm:justify-end gap-2.5 w-full sm:w-auto pt-1 sm:pt-0 border-t sm:border-t-0 border-slate-100">
            <div class="flex-1 sm:w-36 shrink-0 space-y-1.5">
              <div class="flex items-center justify-between">
                <div class="h-2.5 w-14 rounded bg-slate-200"></div>
                <div class="h-2.5 w-8 rounded bg-slate-200"></div>
              </div>
              <div class="h-1.5 w-full rounded-full bg-slate-200"></div>
            </div>
            <div class="h-7 w-7 sm:h-8 sm:w-8 shrink-0 rounded-lg bg-slate-200"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- 2. Skeleton untuk Tab Materi (Ukuran tepat sama persis dengan kartu per materi) -->
    <template v-else>
      <div
        v-for="n in count"
        :key="'skeleton-materi-participant-' + n"
        class="p-2.5 sm:p-3 rounded-xl border border-[#eef2f6] bg-white flex items-center justify-between gap-2.5 shadow-2xs h-[52px] sm:h-[54px]"
      >
        <!-- Left: Avatar & Identity -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
          <div class="h-8 w-8 shrink-0 rounded-full bg-slate-200"></div>
          <div class="min-w-0 flex-1 space-y-1">
            <div class="h-3.5 w-32 sm:w-44 rounded bg-slate-200"></div>
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-16 rounded bg-slate-200"></div>
              <span class="text-slate-300">•</span>
              <div class="h-2.5 w-14 rounded bg-slate-200"></div>
              <span class="text-slate-300">•</span>
              <div class="h-2.5 w-20 rounded bg-slate-200"></div>
            </div>
          </div>
        </div>

        <!-- Right: Status Badge (Sudah / Belum) -->
        <div class="h-5 w-14 sm:w-16 rounded-md bg-slate-200 shrink-0"></div>
      </div>
    </template>
  </div>

  <!-- SKELETON KARTU AKTIVITAS (FEATURED 1 BESAR + GRID KECIL DI LANDING PAGE) -->
  <div 
    v-else-if="type === 'activity-featured-layout'"
    class="flex flex-col lg:flex-row gap-6 animate-pulse select-none w-full"
  >
    <!-- Featured Card Besar di Kiri (1/3) -->
    <div class="lg:w-1/3">
      <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl h-full min-h-[500px] w-full border border-[#d6e0ee] bg-slate-200">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-800/40 to-slate-200/30 pointer-events-none"></div>

        <!-- Top Badges -->
        <div class="relative z-10 p-5 flex items-center justify-between w-full">
          <div class="h-[28px] w-18 rounded-full bg-slate-300/80"></div>
          <div class="h-[28px] w-14 rounded-full bg-slate-300/80"></div>
        </div>

        <!-- Bottom Content -->
        <div class="relative z-10 p-6 flex flex-col justify-end">
          <div class="mb-2.5 flex items-center gap-2">
            <div class="h-4 w-4 rounded-sm bg-slate-300/70"></div>
            <div class="h-3.5 w-32 rounded-md bg-slate-300/70"></div>
          </div>
          <div class="space-y-2 mb-3">
            <div class="h-6 w-full rounded-md bg-slate-100/90"></div>
            <div class="h-6 w-3/4 rounded-md bg-slate-100/90"></div>
          </div>
          <div class="space-y-1.5 mb-5">
            <div class="h-3 w-full rounded bg-slate-300/60"></div>
            <div class="h-3 w-full rounded bg-slate-300/60"></div>
            <div class="h-3 w-2/3 rounded bg-slate-300/60"></div>
          </div>
          <div class="flex flex-wrap gap-2">
            <div class="h-6 w-20 rounded-full bg-slate-300/70"></div>
            <div class="h-6 w-24 rounded-full bg-slate-300/70"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Small Cards Grid di Kanan (2/3) -->
    <div class="lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="n in (count > 1 ? count - 1 : 6)" 
        :key="'skeleton-small-act-' + n"
        class="relative flex flex-col justify-between overflow-hidden rounded-2xl h-[280px] w-full border border-[#d6e0ee] bg-slate-200"
      >
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-800/40 to-slate-200/30 pointer-events-none"></div>

        <!-- Top Badges -->
        <div class="relative z-10 p-4 flex items-center justify-between w-full">
          <div class="h-[24px] w-14 rounded-full bg-slate-300/80"></div>
          <div class="h-[24px] w-12 rounded-full bg-slate-300/80"></div>
        </div>

        <!-- Bottom Content -->
        <div class="relative z-10 p-4 flex flex-col justify-end">
          <div class="mb-1.5 flex items-center gap-1.5">
            <div class="h-3 w-3 rounded-sm bg-slate-300/70"></div>
            <div class="h-3 w-24 rounded-md bg-slate-300/70"></div>
          </div>
          <div class="h-4 w-4/5 rounded-md bg-slate-100/90 mb-2"></div>
          <div class="space-y-1 mb-2.5">
            <div class="h-2.5 w-full rounded bg-slate-300/60"></div>
            <div class="h-2.5 w-3/4 rounded bg-slate-300/60"></div>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <div class="h-5 w-16 rounded-full bg-slate-300/70"></div>
            <div class="h-5 w-18 rounded-full bg-slate-300/70"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SKELETON KARTU AKTIVITAS -->
  <div 
    v-else-if="type === 'activity'"
    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 animate-pulse select-none"
  >
    <div 
      v-for="n in count" 
      :key="'skeleton-activity-' + n"
      class="relative flex flex-col justify-end overflow-hidden rounded-[12px] sm:rounded-[14px] aspect-[3/4] w-full border border-[#d6e0ee] bg-slate-200"
    >
      <!-- Shimmer Overlay Gradient -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-800/30 to-slate-200/40 pointer-events-none"></div>

      <!-- Skeleton Top Badges -->
      <div class="absolute left-3.5 top-3.5 z-20 flex gap-2">
        <div class="h-[24px] sm:h-[26px] w-14 rounded-full bg-slate-300/80"></div>
      </div>
      <div class="absolute right-3.5 top-3.5 z-20">
        <div class="h-[24px] sm:h-[26px] w-12 rounded-full bg-slate-300/80"></div>
      </div>

      <!-- Skeleton Bottom Content -->
      <div class="relative z-10 p-4 sm:p-5 flex flex-col justify-end">
        <!-- Deadline row -->
        <div class="mb-2 flex items-center gap-2">
          <div class="h-3 w-3 rounded-sm bg-slate-300/70"></div>
          <div class="h-3 w-28 rounded-md bg-slate-300/70"></div>
        </div>
        <!-- Title rows -->
        <div class="space-y-1.5 mb-2">
          <div class="h-4 w-full rounded-md bg-slate-100/90"></div>
          <div class="h-4 w-3/4 rounded-md bg-slate-100/90"></div>
        </div>
        <!-- Description row -->
        <div class="space-y-1 mb-3">
          <div class="h-2.5 w-full rounded bg-slate-300/60"></div>
          <div class="h-2.5 w-4/5 rounded bg-slate-300/60"></div>
        </div>
        <!-- Category tags -->
        <div class="flex flex-wrap gap-1.5 pt-1">
          <div class="h-5 w-16 rounded-full bg-slate-300/70"></div>
          <div class="h-5 w-20 rounded-full bg-slate-300/70"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- SKELETON KARTU KARIR -->
  <div 
    v-else-if="type === 'career'"
    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 animate-pulse select-none"
  >
    <div 
      v-for="n in count" 
      :key="'skeleton-career-' + n"
      class="flex flex-col justify-between rounded-xl border border-[#d6e0ee] bg-white p-5 shadow-xs"
    >
      <div>
        <div class="mb-5 flex items-start gap-4">
          <!-- Logo Skeleton -->
          <div class="h-14 w-14 shrink-0 rounded-full bg-slate-200"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-4/5 rounded bg-slate-200"></div>
            <div class="h-3 w-1/2 rounded bg-slate-200/80"></div>
            <div class="h-3 w-1/3 rounded bg-slate-200/60"></div>
          </div>
        </div>
        <!-- Lokasi Skeleton -->
        <div class="mb-4 flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-slate-200 shrink-0"></div>
          <div class="h-3 w-2/3 rounded bg-slate-200/80"></div>
        </div>
      </div>
      <!-- Bagian Bawah Skeleton -->
      <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
        <div class="h-3 w-32 rounded bg-slate-200/80"></div>
        <div class="h-7 w-20 rounded-full bg-slate-200"></div>
      </div>
    </div>
  </div>

  <!-- SKELETON KARTU BERITA -->
  <div 
    v-else-if="type === 'news'"
    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 animate-pulse select-none"
  >
    <div 
      v-for="n in count" 
      :key="'skeleton-news-' + n"
      class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
    >
      <!-- Gambar Berita Aspect [3/2] -->
      <div class="aspect-[3/2] w-full overflow-hidden bg-slate-200 relative">
        <div class="absolute inset-0 bg-gradient-to-tr from-slate-300/40 via-transparent to-slate-100/30"></div>
      </div>
      <!-- Konten Teks Berita -->
      <div class="flex flex-col p-5 sm:p-6">
        <!-- Tanggal -->
        <div class="mb-3 h-3 w-24 rounded bg-slate-200"></div>
        <!-- Judul Berita -->
        <div class="space-y-2 mb-3">
          <div class="h-5 w-full rounded bg-slate-200"></div>
          <div class="h-5 w-2/3 rounded bg-slate-200"></div>
        </div>
        <!-- Cuplikan Deskripsi -->
        <div class="space-y-1.5">
          <div class="h-3.5 w-full rounded bg-slate-200/80"></div>
          <div class="h-3.5 w-full rounded bg-slate-200/80"></div>
          <div class="h-3.5 w-4/5 rounded bg-slate-200/80"></div>
        </div>
      </div>
    </div>
  </div>
</template>

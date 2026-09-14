<template>
  <nav class="flex items-center justify-between gap-6 px-20 py-3.5 bg-[#4b857a] shadow-md">
    <div class="flex items-center gap-3 shrink-0">
      <div class="w-8 h-8 rounded-full bg-neutral-300" />
      <span class="text-white text-xl font-bold">Talenta</span>
    </div>

    <ul class="flex-1 flex items-center justify-center gap-8 list-none">
      <li v-for="item in menuItems" :key="item.label">
        <a
          :href="item.href"
          class="group relative inline-block text-white text-sm opacity-95 hover:opacity-100 font-bold"
        >
          {{ item.label }}
          <span
            class="absolute left-0 -bottom-1 h-[2px] w-0 bg-white transition-all duration-300 ease-out group-hover:w-full"
          />
        </a>
      </li>
    </ul>

    <button
      type="button"
      class="shrink-0 bg-white text-[#4f8073] rounded-full px-6 py-2.5 text-sm font-bold hover:scale-105 hover:shadow-md transition-all duration-200"
      @click="$emit('login-click')"
    >
      Login
    </button>
  </nav>
</template>

<script setup>
defineEmits(['login-click'])

const menuItems = [
  { label: 'Beranda', href: '#beranda' },
  { label: 'Aktivitas', href: '#aktivitas' },
  { label: 'Karir', href: '#karir' },
  { label: 'Berita', href: '#berita' },
  { label: 'FAQ', href: '#faq' },
]
</script>
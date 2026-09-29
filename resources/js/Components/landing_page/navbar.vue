<script setup>
import { ref, computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3' // Wajib import dari inertia

const emit = defineEmits(['login-click'])
const isMenuOpen = ref(false)

const menuItems = [
  // Ganti #beranda jadi '/' untuk standar routing
  { label: 'Beranda', href: '/' }, 
  { label: 'Aktivitas', href: '/activities' },
  { label: 'Karir', href: '/careers' },
  { label: 'Berita', href: '/news' },
  { label: 'FAQ', href: '/FAQ' },
]

// Ambil URL saat ini secara reaktif dari Inertia
const page = usePage()
const currentUrl = computed(() => page.url)

// Fungsi mendeteksi halaman aktif
const isActive = (href) => {
  // 1. Kasus khusus untuk Beranda biar nggak nyala terus
  if (href === '/' || href === '#beranda') {
    return currentUrl.value === '/' || currentUrl.value === ''
  }
  
  // 2. Bersihkan href dari garis miring atau hashtag
  const cleanTarget = href.replace('/', '').replace('#', '')
  if (!cleanTarget) return false

  // 3. Cek apakah URL yang lagi dibuka mengandung nama href tersebut
  return currentUrl.value.includes(cleanTarget)
}

const handleLogin = () => {
  isMenuOpen.value = false
  emit('login-click')
}
</script>

<style scoped>
/* Background Overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Menu turun dari atas */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>

<template>
  <nav class="fixed top-0 left-0 right-0 z-50 w-full bg-[#4b857a] shadow-md font-poppins">
    
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 md:px-10">

      <!-- Logo & Brand -->
      <div class="flex shrink-0 items-center gap-3">
        <div class="h-8 w-8 rounded-full bg-neutral-300"></div>
        <span class="text-lg font-bold text-white sm:text-xl">
          Talenta
        </span>
      </div>

<!-- Desktop Navigation -->
      <ul class="hidden flex-1 list-none items-center justify-center gap-5 md:flex lg:gap-8">
        <li v-for="item in menuItems" :key="item.label">
          <Link
            :href="item.href"
            :class="[
              'group relative inline-block whitespace-nowrap text-sm transition-opacity hover:opacity-100',
              isActive(item.href) ? 'font-bold text-white opacity-100' : 'font-semibold text-white opacity-90'
            ]"
          >
            {{ item.label }}
            <!-- Garis Bawah -->
            <span 
              :class="[
                'absolute -bottom-1 left-0 h-[2px] bg-white transition-all duration-300 ease-out',
                isActive(item.href) ? 'w-full' : 'w-0 group-hover:w-full'
              ]"
            ></span>
          </Link>
        </li>
      </ul>

      <!-- Desktop Login -->
      <button
        type="button"
        class="hidden shrink-0 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#4f8073] shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md md:block lg:px-6"
        @click="$emit('login-click')"
      >
        Login
      </button>

      <!-- Mobile Hamburger -->
      <button
        type="button"
        class="flex h-10 w-10 items-center justify-center rounded-lg text-white transition-colors hover:bg-white/10 md:hidden"
        @click="isMenuOpen = !isMenuOpen"
        aria-label="Toggle menu"
      >
        <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </nav>

  <!-- Background Overlay -->
  <Transition name="fade">
    <div
      v-if="isMenuOpen"
      class="fixed inset-0 z-40 bg-black/50 backdrop-blur-[2px] md:hidden"
      @click="isMenuOpen = false"
    ></div>
  </Transition>

  <!-- Mobile Menu -->
  <Transition name="slide-down">
    <div
      v-if="isMenuOpen"
      class="fixed left-0 top-[64px] z-50 w-full bg-[#4b857a] px-5 pb-6 pt-3 shadow-xl md:hidden"
    >
      <ul class="flex flex-col gap-1">
        <!-- Navigation -->
<!-- Navigation Mobile -->
        <li v-for="item in menuItems" :key="item.label">
          <Link
            :href="item.href"
            @click="isMenuOpen = false"
            :class="[
              'block rounded-xl px-4 py-3 text-sm font-bold transition-colors hover:bg-white/10',
              isActive(item.href) ? 'bg-white/20 text-white' : 'text-white'
            ]"
          >
            {{ item.label }}
          </Link>
        </li>

        <!-- Divider -->
        <div class="my-2 h-px bg-white/20"></div>
        
        <!-- Login -->
        <li>
          <button
            type="button"
            class="w-full rounded-full bg-white px-5 py-3 text-sm font-bold text-[#4f8073] shadow-sm transition-all duration-200 hover:scale-[1.02] hover:shadow-md"
            @click="handleLogin"
          >
            Login
          </button>
        </li>
      </ul>
    </div>
  </Transition>
</template>
<script setup>
import { ref } from 'vue'

const emit = defineEmits(['login-click'])

const isMenuOpen = ref(false)

const menuItems = [
  { label: 'Beranda', href: '#beranda' },
  { label: 'Aktivitas', href: '#aktivitas' },
  { label: 'Karir', href: '#karir' },
  { label: 'Berita', href: '#berita' },
  { label: 'FAQ', href: '#faq' },
]

const handleLogin = () => {
  isMenuOpen.value = false
  emit('login-click')
}
</script>

<style scoped>
/* Background */
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
  transition:
    transform 0.3s ease,
    opacity 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>
<template>
  <!-- Navbar (Outer: Full Width & Fixed) -->
  <nav class="fixed top-0 left-0 right-0 z-50 w-full bg-[#4b857a] shadow-md">
    
    <!-- Inner Container: Penyeimbang Margin (Sama dengan Layout dan Footer) -->
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
          <a
            :href="item.href"
            class="group relative inline-block whitespace-nowrap text-sm font-bold text-white opacity-95 transition-opacity hover:opacity-100"
          >
            {{ item.label }}
            <span class="absolute -bottom-1 left-0 h-[2px] w-0 bg-white transition-all duration-300 ease-out group-hover:w-full"></span>
          </a>
        </li>
      </ul>

      <!-- Desktop Login -->
      <button
        type="button"
        class="hidden shrink-0 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#4f8073] shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-md md:block lg:px-6"
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
        <!-- Hamburger -->
        <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>

        <!-- Close -->
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
        <li v-for="item in menuItems" :key="item.label">
          <a
            :href="item.href"
            class="block rounded-xl px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-white/10"
            @click="isMenuOpen = false"
          >
            {{ item.label }}
          </a>
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



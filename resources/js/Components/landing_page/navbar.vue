<script setup>
import { ref, computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

const emit = defineEmits(['login-click'])
const isMenuOpen = ref(false)

const menuItems = [
  { label: 'Beranda', href: '/' }, 
  { label: 'Aktivitas', href: '/activities' },
  { label: 'Karir', href: '/careers' },
  { label: 'Berita', href: '/news' },
  { label: 'FAQ', href: '/FAQ' },
]

const page = usePage()
const currentUrl = computed(() => page.url)

// DETEKSI LOGIN
const user = computed(() => page.props.auth?.user)

// DETEKSI NAMA (Biar muncul kayak "admin doksli")
const userName = computed(() => {
  if (!user.value) return ''
  return user.value.nama || user.value.nama_mahasiswa || user.value.username || 'User'
})

const isActive = (href) => {
  if (href === '/' || href === '#beranda') {
    return currentUrl.value === '/' || currentUrl.value === ''
  }
  
  const cleanTarget = href.replace('/', '').replace('#', '')
  if (!cleanTarget) return false

  return currentUrl.value.includes(cleanTarget)
}

const handleLogin = () => {
  isMenuOpen.value = false
  emit('login-click')
}
</script>

<style scoped>
/* Overlay Fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Menu Dropdown Smooth Animasi */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>

<template>
  <div>
    <!-- NAVBAR UTAMA -->
    <!-- z-[100] biar dia selalu paling depan dari apapun di website lu -->
    <nav 
      class="fixed top-0 left-0 right-0 z-[100] w-full bg-[#4b857a] font-poppins transition-shadow duration-300"
      :class="isMenuOpen ? '' : 'shadow-md'"
    >
      
      <!-- Container Top Bar -->
      <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 md:px-10 relative z-20 bg-[#4b857a]">
        
        <!-- Logo -->
        <div class="flex shrink-0 items-center gap-3">
          <div class="h-8 w-8 rounded-full bg-[#d4d4d4]"></div>
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
              <span 
                :class="[
                  'absolute -bottom-1 left-0 h-[2px] bg-white transition-all duration-300 ease-out',
                  isActive(item.href) ? 'w-full' : 'w-0 group-hover:w-full'
                ]"
              ></span>
            </Link>
          </li>
        </ul>

        <!-- Desktop Button -->
        <Link
          v-if="!user"
          type="button"
          class="hidden shrink-0 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#4b857a] shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md md:block lg:px-6"
          :href="'/login'"
          @click="$emit('login-click')"
        >
          Login
        </Link>
        <Link
          v-else
          href="/login"
          class="hidden shrink-0 items-center justify-center gap-2.5 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-[#4b857a] shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md md:flex"
        >
          <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="max-w-[150px] truncate">{{ userName }}</span>
        </Link>

        <!-- Mobile Hamburger -->
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-lg text-white transition-colors hover:bg-white/10 md:hidden"
          @click="isMenuOpen = !isMenuOpen"
        >
          <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- MOBILE DROPDOWN MENU -->
      <!-- Menggunakan absolute top-full biar nempel sempurna tanpa celah -->
      <Transition name="slide-down">
        <div 
          v-show="isMenuOpen" 
          class="absolute top-full left-0 w-full bg-[#4b857a] px-4 pb-6 pt-1 shadow-xl md:hidden z-10"
        >
          <ul class="flex flex-col gap-1.5">
            <li v-for="item in menuItems" :key="item.label">
              <Link
                :href="item.href"
                @click="isMenuOpen = false"
                :class="[
                  'block rounded-xl px-4 py-2.5 text-[15px] font-bold transition-colors',
                  isActive(item.href) ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10'
                ]"
              >
                {{ item.label }}
              </Link>
            </li>
            
            <!-- JIKA BELUM LOGIN -->
            <li class="mt-3" v-if="!user">
              <Link
                href="/login"
                @click="isMenuOpen = false"
                class="flex w-full items-center justify-center rounded-full bg-white px-5 py-3 text-[15px] font-bold text-[#4b857a] shadow-sm transition-all duration-200 hover:scale-[1.02]"
              >
                Login
              </Link>
            </li>
            <li class="mt-3" v-else>
              <Link
                href="/login"
                @click="isMenuOpen = false"
                class="flex w-full items-center justify-center gap-2.5 rounded-full bg-white px-5 py-3 text-[15px] font-bold text-[#4b857a] shadow-sm transition-all duration-200 hover:scale-[1.02]"
              >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="truncate">{{ userName }}</span>
              </Link>
            </li>
          </ul>
        </div>
      </Transition>
    </nav>

    <!-- BACKGROUND OVERLAY -->
    <!-- z-[90] memastikannya selalu di bawah navbar tapi nutupin konten page -->
    <Transition name="fade">
      <div
        v-if="isMenuOpen"
        class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-sm md:hidden"
        @click="isMenuOpen = false"
      ></div>
    </Transition>
  </div>
</template>
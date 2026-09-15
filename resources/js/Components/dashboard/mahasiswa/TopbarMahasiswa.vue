<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineEmits(['toggle', 'logout']);

const page = usePage();
const showingAccountMenu = ref(false);
const imageLoadError = ref(false);

const userName = computed(() => page.props.auth?.user?.name || 'Mahasiswa');
const userProfilePicture = computed(() => {
	if (imageLoadError.value) return null;
	return page.props.auth?.user?.profile_picture || null;
});

const onImageError = () => {
	imageLoadError.value = true;
};

const closeAccountMenu = () => {
	showingAccountMenu.value = false;
};

onMounted(() => document.addEventListener('click', closeAccountMenu));
onBeforeUnmount(() => document.removeEventListener('click', closeAccountMenu));
</script>

<template>
	<header class="shrink-0 z-20 flex h-[70px] items-center justify-between border-b border-[#e2e8f0] bg-white px-4 sm:px-8 font-poppins">
		<!-- Hamburger Toggle Button -->
		<button
			type="button"
			aria-label="Buka menu navigasi"
			class="flex h-9 w-9 items-center justify-center rounded-lg text-[#17334F] transition-colors hover:bg-[#f1f5f9] focus:outline-none cursor-pointer"
			@click="$emit('toggle')"
		>
			<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
				<path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
			</svg>
		</button>

		<!-- Mahasiswa Profile Button with Dropdown -->
		<div class="relative" @click.stop>
			<button
				type="button"
				class="flex items-center gap-2 sm:gap-2.5 rounded-full md:rounded-lg py-1 px-1.5 md:px-2.5 md:py-1.5 text-[#17334F] transition-colors hover:bg-[#f1f5f9] focus:outline-none cursor-pointer"
				aria-label="Buka menu akun"
				:aria-expanded="showingAccountMenu"
				@click="showingAccountMenu = !showingAccountMenu"
			>
				<!-- Profile Image -->
				<div class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[#cbd5e1] bg-[#f8fafc] shadow-2xs">
					<img
						v-if="userProfilePicture"
						:src="userProfilePicture"
						:alt="userName"
						class="h-full w-full object-cover object-top"
						@error="onImageError"
					/>
					<img
						v-else
						src="/assets/icons/default-profile.svg"
						alt="Profile icon"
						class="h-full w-full rounded-full object-contain p-0.5"
					/>
				</div>

				<!-- Name -->
				<span class="hidden md:inline-block font-poppins text-sm font-semibold text-[#17334F] max-w-[200px] lg:max-w-[320px] truncate" :title="userName">
					{{ userName }}
				</span>

				<!-- Dropdown Chevron Arrow -->
				<svg
					:class="['h-4 w-4 text-[#17334F] transition-transform duration-200 shrink-0 opacity-80', showingAccountMenu ? 'rotate-180 opacity-100' : '']"
					fill="none"
					stroke="currentColor"
					stroke-width="2.2"
					viewBox="0 0 24 24"
				>
					<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
				</svg>
			</button>

			<!-- Dropdown Menu -->
			<div
				v-if="showingAccountMenu"
				class="absolute right-0 top-[52px] z-40 min-w-[190px] max-w-[calc(100vw-2rem)] w-max rounded-xl border border-[#e2e8f0] bg-white p-1.5 shadow-xl font-poppins space-y-0.5"
			>
				<!-- User Info Header -->
				<div class="px-3 py-2 border-b border-[#f1f5f9]">
					<p class="font-poppins text-xs font-semibold text-[#17334F] whitespace-nowrap truncate" :title="userName">
						{{ userName }}
					</p>
					<p class="font-poppins text-[11px] text-[#64748b] mt-0.5">
						Mahasiswa
					</p>
				</div>

				<Link
					href="/mahasiswa/profile"
					class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-[#17334F] transition-colors hover:bg-[#f1f5f9] focus:outline-none"
					@click="showingAccountMenu = false"
				>
					<svg class="h-4 w-4 shrink-0 text-[#64748b]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
					<span>Profile</span>
				</Link>

				<button
					type="button"
					class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-[#c93b2b] transition-colors hover:bg-[#feeceb] focus:outline-none cursor-pointer"
					@click="showingAccountMenu = false; $emit('logout')"
				>
					<svg class="h-4 w-4 shrink-0 text-[#c93b2b]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
						<polyline points="16 17 21 12 16 7" />
						<line x1="21" y1="12" x2="9" y2="12" />
					</svg>
					<span>Logout</span>
				</button>
			</div>
		</div>
	</header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
	collapsed: {
		type: Boolean,
		default: false,
	},
	mobile: {
		type: Boolean,
		default: false,
	},
});

defineEmits(['navigate', 'logout']);

const menuItems = [
	{ label: 'Dashboard', href: '/mahasiswa/dashboard', pattern: 'mahasiswa.dashboard*', icon: 'dashboard' },
	{ label: 'Roadmap', href: '/mahasiswa/roadmap', pattern: 'mahasiswa.roadmap*', icon: 'roadmap' },
	{
		label: 'Aktivitas',
		href: '/mahasiswa/aktivitas',
		pattern: 'mahasiswa.aktivitas*',
		icon: 'aktivitas',
		hasChildren: true,
		children: [
			{ label: 'List Aktivitas', href: '/mahasiswa/aktivitas/list', pattern: 'mahasiswa.aktivitas.list*', icon: 'circle' },
			{ label: 'Pengajuan', href: '/mahasiswa/aktivitas/pengajuan', pattern: 'mahasiswa.aktivitas.pengajuan*', icon: 'circle' },
		],
	},
	{ label: 'Profile', href: '/mahasiswa/profile', pattern: 'mahasiswa.profile*', icon: 'profile' },
];

const isActive = (item) => {
	try {
		return route().current(item.pattern) || window.location.pathname.startsWith(item.href);
	} catch {
		return window.location.pathname.startsWith(item.href);
	}
};

const STORAGE_KEY = 'sidebar_mahasiswa_open_dropdowns';

const getInitialOpenDropdowns = () => {
	const activeParents = menuItems
		.filter((item) => item.hasChildren && item.children?.some((child) => isActive(child)))
		.map((item) => item.label);

	if (typeof window === 'undefined') return activeParents;

	try {
		const saved = localStorage.getItem(STORAGE_KEY);
		if (saved !== null) {
			const parsed = JSON.parse(saved);
			if (Array.isArray(parsed)) {
				return Array.from(new Set([...parsed, ...activeParents]));
			}
		}
	} catch {
		// Ignore parsing error
	}

	return activeParents;
};

// State: retains open dropdowns even when sidebar is collapsed or closed
const openDropdowns = ref(getInitialOpenDropdowns());

const toggleDropdown = (label) => {
	const index = openDropdowns.value.indexOf(label);
	if (index > -1) {
		openDropdowns.value.splice(index, 1);
	} else {
		openDropdowns.value.push(label);
	}
	try {
		localStorage.setItem(STORAGE_KEY, JSON.stringify(openDropdowns.value));
	} catch {
		// Ignore storage error
	}
};

const isParentActive = (item) => {
	if (!isActive(item)) return false;
	// If dropdown is open, child is highlighted instead of parent
	if (openDropdowns.value.includes(item.label)) {
		return false;
	}
	return true;
};
</script>

<template>
	<aside :class="[
		'shrink-0 flex-col bg-[#416f65] font-poppins text-white select-none overflow-hidden will-change-[width,transform]',
		mobile
			? [
				'fixed left-0 top-0 bottom-0 z-50 h-screen h-[100dvh] w-[275px] max-w-[82vw] overflow-y-auto overflow-x-hidden shadow-2xl transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] flex',
				collapsed ? '-translate-x-full pointer-events-none' : 'translate-x-0 pointer-events-auto'
			]
			: [
				'h-full self-stretch overflow-hidden transition-[width] duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] flex shrink-0',
				collapsed ? 'w-[80px]' : 'w-[275px]'
			]
	]">
		<!-- Logo Header Row -->
		<div
			class="flex h-[70px] shrink-0 items-center justify-center border-b border-white/10 transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
			:class="(!mobile && collapsed) ? 'px-0' : 'px-4'"
		>
			<Link
				href="/mahasiswa/dashboard"
				class="flex items-center justify-center focus:outline-none overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
				:class="(!mobile && collapsed) ? 'gap-0' : 'gap-3.5'"
			>
				<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-black text-base font-black text-white shadow-sm">
				</span>
				<span
					class="whitespace-nowrap text-[22px] sm:text-[24px] font-bold leading-none tracking-tight text-white transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
					:class="(!mobile && collapsed) ? 'max-w-0 opacity-0 -translate-x-3 pointer-events-none' : 'max-w-[160px] opacity-100 translate-x-0'"
				>
					Talenta
				</span>
			</Link>
		</div>

		<!-- Nav Menu Area -->
		<nav aria-label="Navigasi utama" :class="[
			'mt-8 sm:mt-10 flex-1 flex flex-col gap-3.5 overflow-y-auto overflow-x-hidden transition-[padding] duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]',
			(!mobile && collapsed) ? 'px-3' : 'px-0'
		]">
			<template v-for="item in menuItems" :key="item.label">
				<!-- Menu Non-Dropdown -->
				<Link
					v-if="!item.hasChildren"
					:href="item.href"
					:class="[
						'flex h-11 items-center text-[15px] font-semibold transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] outline-none focus:outline-none focus-visible:outline-none focus:ring-0 focus-visible:ring-0 active:outline-none active:ring-0 select-none whitespace-nowrap overflow-hidden',
						(!mobile && collapsed) ? 'w-full justify-center rounded-full px-0 gap-0' : 'ml-6 w-[calc(100%-24px)] gap-5 rounded-l-full pl-7 pr-4',
						isActive(item)
							? 'bg-[#345c53] text-white shadow-sm'
							: ['text-white/80', !mobile ? 'hover:bg-[#528277] hover:text-white' : ''],
					]"
					:title="(!mobile && collapsed) ? item.label : undefined"
					@click="$emit('navigate')"
				>
					<img :src="`/assets/icons/${item.icon}.svg`" :alt="`${item.label} icon`" class="h-5 w-5 shrink-0 object-contain" />
					<span
						class="whitespace-nowrap truncate transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
						:class="(!mobile && collapsed) ? 'max-w-0 opacity-0 -translate-x-2 pointer-events-none' : 'max-w-[180px] opacity-100 translate-x-0'"
					>
						{{ item.label }}
					</span>
				</Link>

				<!-- Menu Dropdown (Aktivitas) -->
				<div v-else class="flex flex-col">
					<button
						type="button"
						:class="[
							'flex h-11 items-center text-[15px] font-semibold transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] outline-none focus:outline-none focus-visible:outline-none focus:ring-0 focus-visible:ring-0 active:outline-none active:ring-0 select-none whitespace-nowrap cursor-pointer overflow-hidden',
							(!mobile && collapsed) ? 'w-full justify-center rounded-full px-0' : 'ml-6 w-[calc(100%-24px)] justify-between rounded-l-full pl-7 pr-5',
							isParentActive(item)
								? 'bg-[#345c53] text-white shadow-sm'
								: ['text-white/80', !mobile ? 'hover:bg-[#528277] hover:text-white' : ''],
						]"
						:title="(!mobile && collapsed) ? item.label : undefined"
						@click="toggleDropdown(item.label)"
					>
						<div
							class="flex items-center transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
							:class="(!mobile && collapsed) ? 'justify-center gap-0' : 'gap-5'"
						>
							<img :src="`/assets/icons/${item.icon}.svg`" :alt="`${item.label} icon`" class="h-5 w-5 shrink-0 object-contain" />
							<span
								class="whitespace-nowrap truncate transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
								:class="(!mobile && collapsed) ? 'max-w-0 opacity-0 -translate-x-2 pointer-events-none' : 'max-w-[160px] opacity-100 translate-x-0'"
							>
								{{ item.label }}
							</span>
						</div>
						<svg
							:class="[
								'h-4 shrink-0 transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] text-white/80',
								openDropdowns.includes(item.label) ? 'rotate-180' : '',
								(!mobile && collapsed) ? 'w-0 opacity-0 scale-0 pointer-events-none -translate-x-2' : 'w-4 opacity-100 scale-100 mr-1 translate-x-0'
							]"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							stroke-width="2.5"
						>
							<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
						</svg>
					</button>

					<!-- Submenu Items (Smooth Grid Accordion Animation) -->
					<div
						class="submenu-collapse"
						:class="{ 'is-open': openDropdowns.includes(item.label) }"
					>
						<div class="submenu-inner min-h-0 overflow-hidden">
							<div
								class="flex flex-col pt-3 transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
								:class="(!mobile && collapsed) ? 'gap-2.5' : 'gap-3.5'"
							>
								<Link
									v-for="sub in item.children"
									:key="sub.label"
									:href="sub.href"
									:class="[
										'flex h-11 items-center text-[15px] font-semibold transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] outline-none focus:outline-none focus-visible:outline-none focus:ring-0 focus-visible:ring-0 active:outline-none active:ring-0 select-none whitespace-nowrap overflow-hidden',
										(!mobile && collapsed)
											? 'w-full justify-center rounded-full px-0 gap-0'
											: 'ml-6 w-[calc(100%-24px)] gap-5 rounded-l-full pl-7 pr-4',
										isActive(sub)
											? 'bg-[#345c53] text-white shadow-sm'
											: ['text-white/80', !mobile ? 'hover:bg-[#528277] hover:text-white' : ''],
									]"
									:title="(!mobile && collapsed) ? sub.label : undefined"
									@click="$emit('navigate')"
								>
									<img
										:src="`/assets/icons/${sub.icon}.svg`"
										:alt="`${sub.label} icon`"
										:class="(!mobile && collapsed) ? 'h-4 w-4' : 'h-5 w-5'"
										class="shrink-0 object-contain transition-all duration-300"
									/>
									<span
										class="whitespace-nowrap truncate transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
										:class="(!mobile && collapsed) ? 'max-w-0 opacity-0 -translate-x-2 pointer-events-none' : 'max-w-[180px] opacity-100 translate-x-0'"
									>
										{{ sub.label }}
									</span>
								</Link>
							</div>
						</div>
					</div>
				</div>
			</template>
		</nav>

		<!-- Logout Button -->
		<div class="mt-auto pb-8 pt-4">
			<button
				type="button"
				:class="[
					'flex h-11 items-center text-[15px] font-semibold text-white/80 transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)] outline-none focus:outline-none focus-visible:outline-none focus:ring-0 focus-visible:ring-0 active:outline-none active:ring-0 select-none whitespace-nowrap cursor-pointer overflow-hidden',
					(!mobile && collapsed) ? 'w-full justify-center rounded-full px-0 gap-0' : 'ml-6 w-[calc(100%-24px)] gap-5 rounded-l-full pl-7 pr-4',
					!mobile ? 'hover:bg-[#528277] hover:text-white' : '',
				]"
				:title="(!mobile && collapsed) ? 'Logout' : undefined"
				@click="$emit('logout')"
			>
				<img src="/assets/icons/logout.svg" alt="Logout icon" class="h-5 w-5 shrink-0 object-contain" />
				<span
					class="whitespace-nowrap transition-all duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]"
					:class="(!mobile && collapsed) ? 'max-w-0 opacity-0 -translate-x-2 pointer-events-none' : 'max-w-[180px] opacity-100 translate-x-0'"
				>
					Logout
				</span>
			</button>
		</div>
	</aside>
</template>

<style scoped>
.submenu-collapse {
	display: grid;
	grid-template-rows: 0fr;
	opacity: 0;
	visibility: hidden;
	transition: grid-template-rows 250ms cubic-bezier(0.4, 0, 0.2, 1), opacity 200ms ease, visibility 250ms;
}

.submenu-collapse.is-open {
	grid-template-rows: 1fr;
	opacity: 1;
	visibility: visible;
}

.submenu-inner {
	min-height: 0;
	overflow: hidden;
}

/* Hilangkan outline putih / browser focus ring saat tombol diklik / aktif */
aside,
aside *,
nav a,
nav button {
	outline: none !important;
	-webkit-tap-highlight-color: transparent !important;
	-webkit-touch-callout: none !important;
}

nav a:focus,
nav a:focus-visible,
nav a:active,
nav button:focus,
nav button:focus-visible,
nav button:active,
button:focus,
button:focus-visible,
button:active {
	outline: none !important;
	border-color: transparent !important;
	-webkit-tap-highlight-color: transparent !important;
}
</style>

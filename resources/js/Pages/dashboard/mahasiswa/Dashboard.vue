<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';

const props = defineProps({
	stats: {
		type: Object,
		default: () => ({
			profesional: 0,
			bisnis: 0,
			birokrat: 0,
			akademisi: 0,
			totalKegiatan: 0,
		}),
	},
	roadmaps: {
		type: Array,
		default: () => [],
	},
	activities: {
		type: Array,
		default: () => [],
	},
});

const page = usePage();

const userName = computed(() => {
	const user = page.props.auth?.user;
	return user?.nama || user?.name || 'Mahasiswa';
});

// Stats Items Configuration
const statCards = computed(() => [
	{
		label: 'Profesional',
		value: props.stats?.profesional ?? 0,
		icon: '/assets/icons/icon_profesional.svg',
	},
	{
		label: 'Bisnis',
		value: props.stats?.bisnis ?? 0,
		icon: '/assets/icons/icon_bisnis.svg',
	},
	{
		label: 'Birokrat',
		value: props.stats?.birokrat ?? 0,
		icon: '/assets/icons/icon_birokrat.svg',
	},
	{
		label: 'Akademisi',
		value: props.stats?.akademisi ?? 0,
		icon: '/assets/icons/icon_akademisi.svg',
	},
	{
		label: 'Total Kegiatan',
		value: props.stats?.totalKegiatan ?? props.stats?.total_kegiatan ?? 0,
		icon: '/assets/icons/icon_total_kegiatan.svg',
	},
]);

// Helper Gradient Fallback for Roadmap
const getCardGradient = (category) => {
	switch ((category || '').toLowerCase()) {
		case 'profesional':
			return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
		case 'bisnis':
			return 'from-[#0f3d4c] via-[#16566c] to-[#1e788e]';
		case 'birokrasi':
		case 'birokrat':
			return 'from-[#22285e] via-[#313b82] to-[#4553a6]';
		case 'akademisi':
			return 'from-[#382a57] via-[#4d3a76] to-[#6a4f9d]';
		default:
			return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
	}
};

// Category Badge Helpers for Roadmap (Matches Roadmap.vue)
const getCategoryBadges = (item) => {
	if (!item) return [];
	if (Array.isArray(item.kategori)) return item.kategori;
	if (Array.isArray(item.categories)) return item.categories;
	if (Array.isArray(item.category)) return item.category;
	const val = item.category || item.kategori || '';
	if (typeof val === 'string' && val.trim()) {
		if (val.includes('|')) return val.split('|').map((s) => s.trim()).filter(Boolean);
		if (val.includes(',')) return val.split(',').map((s) => s.trim()).filter(Boolean);
		return [val.trim()];
	}
	return ['Profesional'];
};

const getCategoryBadgeClass = (category) => {
	const cat = (category || '').trim();
	if (cat === 'Profesional' || cat === 'Professional') {
		return 'bg-indigo-50 text-indigo-700 border-indigo-200';
	}
	if (cat === 'Bisnis') {
		return 'bg-amber-50 text-amber-700 border-amber-200';
	}
	if (cat === 'Birokrat' || cat === 'Birokrasi') {
		return 'bg-emerald-50 text-emerald-700 border-emerald-200';
	}
	if (cat === 'Akademisi') {
		return 'bg-purple-50 text-purple-700 border-purple-200';
	}
	return 'bg-slate-100 text-slate-700 border-slate-200';
};

// Activity Chip Colors
const getCategoryChipClass = (cat) => {
	switch (cat) {
		case 'Profesional':
			return 'bg-indigo-50 text-indigo-700 border-indigo-200';
		case 'Bisnis':
			return 'bg-amber-50 text-amber-700 border-amber-200';
		case 'Birokrat':
		case 'Birokrasi':
			return 'bg-emerald-50 text-emerald-700 border-emerald-200';
		case 'Akademisi':
			return 'bg-purple-50 text-purple-700 border-purple-200';
		default:
			return 'bg-slate-100 text-slate-700 border-slate-200';
	}
};

const getImageUrl = (img) => {
	if (!img) return 'https://picsum.photos/seed/activity/400/600';
	if (img.startsWith('http') || img.startsWith('/')) return img;
	return `https://picsum.photos/seed/${img}/400/600`;
};

const formatDeadline = (val) => {
	if (!val) return '-';
	if (typeof val === 'string' && val.includes(' ') && !val.includes('-')) return val;
	const options = { day: 'numeric', month: 'long', year: 'numeric' };
	const d = new Date(val);
	return isNaN(d.getTime()) ? val : d.toLocaleDateString('id-ID', options);
};

const getActivityTags = (item) => {
	if (!item) return [];
	if (Array.isArray(item.tags) && item.tags.length > 0) {
		return item.tags.map((t) => (typeof t === 'object' ? t.name : t));
	}
	if (Array.isArray(item.kategori) && item.kategori.length > 0) return item.kategori;
	if (typeof item.kategori === 'string' && item.kategori.trim()) {
		return item.kategori.split(',').map((s) => s.trim()).filter(Boolean);
	}
	return [];
};

const handleRoadmapClick = (roadmap) => {
	router.visit(`/mahasiswa/roadmap/${roadmap.id || 1}`);
};

const handleActivityClick = (activity) => {
	if (activity?.id) {
		router.visit(`/mahasiswa/aktivitas/pendaftaran/${activity.id}`);
	} else {
		router.visit('/mahasiswa/aktivitas/list');
	}
};
</script>

<template>
	<Head title="Dashboard - Mahasiswa" />

	<MahasiswaLayout>
		<section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
			<div class="space-y-6 sm:space-y-8">
				<!-- Header Section -->
				<div class="space-y-1.5">
					<h1 class="text-[34px] font-extrabold leading-[1.05] tracking-tight text-[#17334F] sm:text-[42px] lg:text-[46px]">
						Selamat Datang {{ userName }}!
					</h1>
					<p class="font-inter text-[14px] font-normal leading-normal text-[#4d6786] sm:text-[15px]">
						Yuk temukan aktivitas yang cocok dengan talenta kamu disini!
					</p>
				</div>

				<!-- Stats Row (5 Cards) - Mobile: Horizontal swipe carousel / Desktop: 5-column grid (Non-clickable, static display) -->
				<div class="flex sm:grid sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-5 overflow-x-auto py-1 pb-1.5 sm:py-0 sm:pb-0 scrollbar-none [scrollbar-width:none] [&::-webkit-scrollbar]:hidden snap-x snap-mandatory">
					<div
						v-for="stat in statCards"
						:key="stat.label"
						class="relative flex h-[82px] sm:h-[90px] w-[155px] sm:w-auto shrink-0 snap-start items-center justify-between overflow-hidden rounded-[14px] border border-[#d6e0ee] bg-white px-3 sm:px-4 shadow-xs select-none cursor-default"
					>
						<!-- Left Icon (Flushed / Glued directly to bottom border) -->
						<div class="absolute bottom-0 left-2 sm:left-2.5 flex items-end pointer-events-none select-none">
							<img
								:src="stat.icon"
								:alt="stat.label"
								class="h-[48px] sm:h-[54px] w-auto max-w-[52px] sm:max-w-[60px] object-bottom object-contain block -mb-[1px]"
							/>
						</div>

						<!-- Right Content (Stacked & Aligned) -->
						<div class="ml-auto flex flex-col items-end sm:items-center justify-center text-right sm:text-center pl-12 sm:pl-14">
							<span class="font-poppins text-[12.5px] sm:text-[14px] lg:text-[14.5px] font-bold text-[#17334F] leading-tight">
								{{ stat.label }}
							</span>
							<span class="mt-0.5 font-inter text-[13px] sm:text-[14px] font-semibold text-[#475569]">
								{{ stat.value }}
							</span>
						</div>
					</div>
				</div>

				<!-- Section: Roadmap yang Sedang Kamu Ikuti -->
				<div class="space-y-3.5 sm:space-y-4">
					<!-- Title & Link Header -->
					<div class="flex items-center justify-between gap-2">
						<h2 class="font-poppins text-[15px] sm:text-[16px] font-bold text-[#17334F] leading-tight">
							Lihat kembali Roadmap yang terakhir kamu kunjungi!
						</h2>
						<Link
							href="/mahasiswa/roadmap"
							class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#183669] hover:underline cursor-pointer shrink-0"
						>
							Lihat Semua
						</Link>
					</div>

					<!-- Roadmap Cards Grid (Max 3 Cards, matches Roadmap.vue) -->
					<div v-if="roadmaps && roadmaps.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
						<div
							v-for="item in roadmaps.slice(0, 3)"
							:key="`roadmap-${item.id}`"
							@click="handleRoadmapClick(item)"
							class="group flex flex-col justify-between rounded-[14px] border border-[#d6e0ee] bg-white p-4 sm:p-5 shadow-xs transition-colors duration-150 sm:hover:bg-[#fafcff] sm:hover:border-[#a6b7cb] cursor-pointer"
							:title="`Buka detail ${item.title}`"
						>
							<div>
								<!-- Cover Banner (Rounded [10px], matches Roadmap.vue) -->
								<div class="relative flex h-[175px] sm:h-[190px] w-full items-center justify-center overflow-hidden rounded-[10px] bg-slate-100 shadow-inner">
									<img
										v-if="item.thumbnail"
										:src="item.thumbnail"
										:alt="item.title"
										class="h-full w-full object-cover"
									/>
									<!-- Soft Geometric Mural Pattern Fallback -->
									<div
										v-else
										:class="['relative h-full w-full bg-gradient-to-br flex items-center justify-center overflow-hidden select-none', getCardGradient(item.category)]"
									>
										<svg
											class="absolute inset-0 h-full w-full object-cover pointer-events-none opacity-40 mix-blend-screen"
											viewBox="0 0 400 240"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<circle cx="360" cy="30" r="110" fill="white" fill-opacity="0.2" />
											<circle cx="40" cy="220" r="90" fill="white" fill-opacity="0.15" />
											<path d="M-20 60 L120 -10 L180 90 L60 140 Z" fill="white" fill-opacity="0.08" />
											<path d="M120 -10 L280 20 L240 130 L180 90 Z" fill="white" fill-opacity="0.12" />
											<path d="M280 20 L420 -30 L380 90 L240 130 Z" fill="white" fill-opacity="0.06" />
											<path d="M180 90 L240 130 L320 220 L160 200 Z" fill="white" fill-opacity="0.09" />
											<path d="M60 140 L180 90 L160 200 L40 230 Z" fill="white" fill-opacity="0.05" />
											<path d="M240 130 L380 90 L430 200 L320 220 Z" fill="white" fill-opacity="0.11" />
											<path d="M-10 180 C80 140, 160 220, 260 170 C330 130, 380 180, 420 160" stroke="white" stroke-opacity="0.22" stroke-width="1.5" stroke-dasharray="4 4" fill="none" />
											<path d="M-10 200 C90 160, 180 240, 280 190 C350 150, 390 200, 430 180" stroke="white" stroke-opacity="0.15" stroke-width="1.5" fill="none" />
										</svg>

										<div class="relative z-10 flex flex-col items-center justify-center text-center px-4">
											<span class="font-poppins text-[15px] sm:text-[16px] font-bold tracking-wide text-white drop-shadow-xs">
												{{ item.category || 'Roadmap' }}
											</span>
											<p class="mt-0.5 font-inter text-[11px] sm:text-[12px] font-medium text-white/80">
												Talenta Manajemen Hutan
											</p>
										</div>
									</div>
								</div>

								<!-- Progress Bar Row (Matches Roadmap.vue) -->
								<div class="mt-4 flex items-center justify-between gap-3">
									<span class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#475569] shrink-0">
										{{ item.completed_tasks ?? 0 }}/{{ item.task_count || 1 }} Task
									</span>
									<div class="h-2 flex-1 rounded-full bg-[#e2e8f0] overflow-hidden">
										<div
											class="h-full rounded-full bg-[#416f65] transition-all duration-300"
											:style="{
												width: `${Math.min(100, Math.round(((item.completed_tasks ?? 0) / (item.task_count || 1)) * 100))}%`
											}"
										></div>
									</div>
									<span class="font-inter text-[12px] sm:text-[13px] font-bold text-[#183669] shrink-0">
										{{ Math.min(100, Math.round(((item.completed_tasks ?? 0) / (item.task_count || 1)) * 100)) }}%
									</span>
								</div>

								<!-- Title -->
								<h3
									class="mt-2.5 line-clamp-2 text-[16px] sm:text-[17px] font-bold leading-snug text-[#17334F] transition sm:group-hover:text-[#183669]"
									:title="item.title"
								>
									{{ item.title }}
								</h3>

								<!-- Category Badges (Matches Roadmap.vue) -->
								<div class="mt-2.5 flex flex-wrap items-center gap-1.5">
									<span
										v-for="(cat, catIdx) in getCategoryBadges(item)"
										:key="catIdx"
										:class="[
											'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
											getCategoryBadgeClass(cat)
										]"
									>
										{{ cat }}
									</span>
								</div>
							</div>
						</div>
					</div>

					<!-- Empty State Roadmap -->
					<div
						v-else
						class="flex flex-col items-center justify-center rounded-[14px] border border-dashed border-[#d6e0ee] bg-white px-6 py-12 text-center"
					>
						<div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f0f4f9] text-[#183669] mb-3">
							<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
							</svg>
						</div>
						<h3 class="font-poppins text-[15px] font-bold text-[#17334F]">
							Belum Ada Roadmap yang Diikuti
						</h3>
						<p class="mt-1 font-inter text-[13px] text-[#64748b] max-w-sm">
							Kamu belum mengikuti roadmap apapun. Yuk temukan dan ikuti roadmap yang cocok dengan talenta kamu!
						</p>
						<Link
							href="/mahasiswa/roadmap"
							class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-[#183669] px-4 py-2 font-poppins text-xs font-semibold text-white shadow-xs transition hover:bg-[#122b54] active:scale-95"
						>
							Jelajahi Roadmap
						</Link>
					</div>
				</div>

				<!-- Section: Aktivitas Terbaru Yang Bisa Kamu Ikuti Nih! -->
				<div class="space-y-3.5 sm:space-y-4">
					<!-- Title & Link Header -->
					<div class="flex items-center justify-between gap-2">
						<h2 class="font-poppins text-[15px] sm:text-[16px] font-bold text-[#17334F] leading-tight">
							Aktivitas terbaru yang bisa kamu ikuti!
						</h2>
						<Link
							href="/mahasiswa/aktivitas/list"
							class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#183669] hover:underline cursor-pointer shrink-0"
						>
							Lihat Semua
						</Link>
					</div>

					<!-- Activity Cards Grid (4 Columns on Desktop, 2 Columns on Tablet, 1 Column on Mobile) -->
					<div v-if="activities && activities.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
						<div
							v-for="item in activities.slice(0, 4)"
							:key="`activity-${item.id}`"
							@click="handleActivityClick(item)"
							class="group relative flex flex-col justify-end overflow-hidden rounded-[12px] sm:rounded-[14px] shadow-sm transition-all duration-300 aspect-[3/4] w-full border border-[#d6e0ee] bg-[#1e293b] sm:hover:-translate-y-1.5 sm:hover:shadow-lg cursor-pointer select-none"
							:title="item.judul"
						>
							<!-- Background Image & Gradient -->
							<img
								:src="getImageUrl(item.gambar)"
								class="absolute inset-0 h-full w-full object-cover"
								:alt="item.judul"
							/>
							<div class="absolute inset-0 bg-gradient-to-t from-[#091e1b] via-[#133830]/80 to-transparent opacity-95"></div>

							<!-- Badge Umum / Lomba (Top Left) -->
							<div class="absolute left-3.5 top-3.5 z-20">
								<span :class="[
									'rounded-full px-3 py-1 text-[10px] font-extrabold text-white shadow-sm uppercase tracking-wider',
									(item.jenis || 'Lomba') === 'Lomba' ? 'bg-[#f59e0b]' : 'bg-[#2563eb]'
								]">
									{{ item.jenis || 'Lomba' }}
								</span>
							</div>

							<!-- Participant Badge (Top Right) -->
							<div class="absolute right-3.5 top-3.5 flex items-center gap-1.5 z-20">
								<div class="flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-[11px] font-bold text-[#183669] shadow-sm backdrop-blur-sm">
									<svg class="h-3.5 w-3.5 text-[#183669]" fill="currentColor" viewBox="0 0 20 20">
										<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
									</svg>
									<span>{{ item.peserta ?? 0 }}</span>
								</div>
							</div>

							<!-- Content Container -->
							<div class="relative z-10 p-4 sm:p-5 flex flex-col justify-end">
								<!-- Deadline Row (White Text) -->
								<div class="mb-1 flex items-center gap-1.5 text-[10.5px] sm:text-[11px] font-semibold text-white">
									<svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
									</svg>
									<span>Deadline: {{ formatDeadline(item.deadline) }}</span>
								</div>

								<!-- Title -->
								<h3 class="mb-1 font-poppins text-[15px] sm:text-[16px] font-bold leading-snug text-white line-clamp-2">
									{{ item.judul }}
								</h3>

								<!-- Description -->
								<p class="mb-2.5 font-inter text-[11px] sm:text-[12px] leading-relaxed line-clamp-2 text-slate-200/90">
									{{ item.deskripsi }}
								</p>

								<!-- Category Tags (Pill Badges) -->
								<div class="flex flex-wrap gap-1.5">
									<span
										v-for="tag in getActivityTags(item)"
										:key="tag"
										:class="[
											'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[10.5px] font-semibold border shadow-xs',
											getCategoryChipClass(tag)
										]"
									>
										{{ tag }}
									</span>
								</div>
							</div>
						</div>
					</div>

					<!-- Empty State Aktivitas -->
					<div
						v-else
						class="flex flex-col items-center justify-center rounded-[14px] border border-dashed border-[#d6e0ee] bg-white px-6 py-12 text-center"
					>
						<div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f0f4f9] text-[#183669] mb-3">
							<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
							</svg>
						</div>
						<h3 class="font-poppins text-[15px] font-bold text-[#17334F]">
							Belum Ada Aktivitas Terbaru
						</h3>
						<p class="mt-1 font-inter text-[13px] text-[#64748b] max-w-sm">
							Saat ini belum ada aktivitas yang tersedia. Cek kembali nanti ya!
						</p>
					</div>
				</div>
			</div>
		</section>
	</MahasiswaLayout>
</template>

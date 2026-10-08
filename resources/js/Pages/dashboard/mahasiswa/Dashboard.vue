<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';

const props = defineProps({
	stats: {
		type: Object,
		default: () => ({
			profesional: 10,
			bisnis: 100,
			birokrat: 0,
			akademisi: 10,
			totalKegiatan: 10,
		}),
	},
	roadmaps: {
		type: Array,
		default: () => [
			{
				id: 1,
				title: 'Lorem Ipsum Dolor Lorem Ipsum DolorLorem Ipsum DolorLorem....',
				category: 'Profesional',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
			},
			{
				id: 2,
				title: 'Lorem Ipsum Dolor Lorem Ipsum DolorLorem Ipsum DolorLorem....',
				category: 'Bisnis',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
			},
			{
				id: 3,
				title: 'Lorem Ipsum Dolor Lorem Ipsum DolorLorem Ipsum DolorLorem....',
				category: 'Birokrasi',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
			},
			{
				id: 4,
				title: 'Lorem Ipsum Dolor Lorem Ipsum DolorLorem Ipsum DolorLorem....',
				category: 'Akademisi',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
			},
		],
	},
	activities: {
		type: Array,
		default: () => [
			{
				id: 1,
				judul: 'Lomba Agustus...',
				deskripsi: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit....',
				deadline: '21 Agustus 2026',
				peserta: 67,
				gambar: '123.jpg',
				tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
			},
			{
				id: 2,
				judul: 'First Meet MNH...',
				deskripsi: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit....',
				deadline: '21 Agustus 2026',
				peserta: 67,
				gambar: '1234.jpg',
				tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
			},
			{
				id: 3,
				judul: 'First Meet MNH...',
				deskripsi: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit....',
				deadline: '21 Agustus 2026',
				peserta: 67,
				gambar: '1235.jpg',
				tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
			},
			{
				id: 4,
				judul: 'First Meet MNH...',
				deskripsi: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit....',
				deadline: '21 Agustus 2026',
				peserta: 67,
				gambar: '123.jpg',
				tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
			},
			{
				id: 5,
				judul: 'First Meet MNH...',
				deskripsi: 'Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit....',
				deadline: '21 Agustus 2026',
				peserta: 67,
				gambar: '1234.jpg',
				tags: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
			},
		],
	},
});

const page = usePage();

const userName = computed(() => {
	const user = page.props.auth?.user;
	return user?.nama || user?.name || 'Fauzan';
});

// Stats Items Configuration
const statCards = computed(() => [
	{
		label: 'Profesional',
		value: props.stats?.profesional ?? 10,
		icon: '/assets/icons/icon_profesional.svg',
	},
	{
		label: 'Bisnis',
		value: props.stats?.bisnis ?? 100,
		icon: '/assets/icons/icon_bisnis.svg',
	},
	{
		label: 'Birokrat',
		value: props.stats?.birokrat ?? 0,
		icon: '/assets/icons/icon_birokrat.svg',
	},
	{
		label: 'Akademisi',
		value: props.stats?.akademisi ?? 10,
		icon: '/assets/icons/icon_akademisi.svg',
	},
	{
		label: 'Total Kegiatan',
		value: props.stats?.totalKegiatan ?? props.stats?.total_kegiatan ?? 10,
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
	if (!val) return '21 Agustus 2026';
	if (typeof val === 'string' && val.includes(' ')) return val;
	const options = { day: 'numeric', month: 'long', year: 'numeric' };
	const d = new Date(val);
	return isNaN(d.getTime()) ? val : d.toLocaleDateString('id-ID', options);
};

const getActivityTags = (item) => {
	if (!item) return [];
	if (Array.isArray(item.tags)) {
		return item.tags.map((t) => (typeof t === 'object' ? t.name : t));
	}
	if (Array.isArray(item.kategori)) return item.kategori;
	if (typeof item.kategori === 'string') {
		return item.kategori.split(',').map((s) => s.trim()).filter(Boolean);
	}
	return ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'];
};

const handleRoadmapClick = (roadmap) => {
	router.visit(`/mahasiswa/roadmap/${roadmap.id || 1}`);
};

const handleActivityClick = () => {
	router.visit('/mahasiswa/aktivitas/pendaftaran');
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

				<!-- Stats Row (5 Cards) -->
				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4 lg:gap-5">
					<div
						v-for="stat in statCards"
						:key="stat.label"
						class="flex items-center justify-between gap-3 rounded-[14px] border border-[#d6e0ee] bg-white px-4 py-4 sm:px-5 sm:py-5 shadow-xs transition-colors duration-150 hover:border-[#8ea9cb]"
					>
						<!-- Left Icon -->
						<div class="flex h-11 w-11 sm:h-12 sm:w-12 shrink-0 items-center justify-center">
							<img
								:src="stat.icon"
								:alt="stat.label"
								class="max-h-full max-w-full object-contain pointer-events-none"
							/>
						</div>

						<!-- Right Content (Stacked & Centered) -->
						<div class="flex flex-col items-center justify-center text-center">
							<span class="font-poppins text-[13.5px] sm:text-[14.5px] lg:text-[15px] font-bold text-[#17334F]">
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
					<div class="flex items-center justify-between">
						<h2 class="font-poppins text-[15px] sm:text-[16px] font-bold text-[#17334F]">
							Yuk Lihat Roadmap yang Sedang Kamu Ikuti!
						</h2>
						<Link
							href="/mahasiswa/roadmap"
							class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#183669] hover:underline cursor-pointer"
						>
							Lihat Semua
						</Link>
					</div>

					<!-- Roadmap Cards Grid (4 Columns) -->
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
						<div
							v-for="item in roadmaps.slice(0, 4)"
							:key="`roadmap-${item.id}`"
							@click="handleRoadmapClick(item)"
							class="group flex flex-col justify-between rounded-[14px] border border-[#d6e0ee] bg-white p-4 sm:p-5 shadow-xs transition-colors duration-150 hover:bg-[#fafcff] hover:border-[#a6b7cb] cursor-pointer"
							:title="`Buka detail ${item.title}`"
						>
							<div>
								<!-- Cover Banner -->
								<div class="relative flex h-[165px] sm:h-[175px] w-full items-center justify-center overflow-hidden rounded-[10px] bg-slate-100 shadow-inner">
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

								<!-- Progress Bar Row -->
								<div class="mt-4 flex items-center justify-between gap-3">
									<span class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#475569] shrink-0">
										{{ item.completed_tasks ?? 0 }}/{{ item.task_count || 40 }} Task
									</span>
									<div class="h-2 flex-1 rounded-full bg-[#e2e8f0] overflow-hidden">
										<div
											class="h-full rounded-full bg-[#183669] transition-all duration-300"
											:style="{
												width: `${Math.min(100, Math.round(((item.completed_tasks ?? 0) / (item.task_count || 40)) * 100))}%`
											}"
										></div>
									</div>
								</div>

								<!-- Title -->
								<h3
									class="mt-2.5 line-clamp-2 text-[15px] sm:text-[16px] font-bold leading-snug text-[#17334F] transition group-hover:text-[#183669]"
									:title="item.title"
								>
									{{ item.title }}
								</h3>

								<!-- Categories Label -->
								<p class="mt-2.5 font-inter text-[11.5px] sm:text-[12px] font-medium text-[#64748b]">
									{{ item.categories_label || 'Profesional | Bisnis | Birokrat | Akademisi' }}
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Section: Aktivitas Terbaru Yang Bisa Kamu Ikuti Nih! -->
				<div class="space-y-3.5 sm:space-y-4">
					<!-- Title & Link Header -->
					<div class="flex items-center justify-between">
						<h2 class="font-poppins text-[15px] sm:text-[16px] font-bold text-[#17334F]">
							Aktivitas Terbaru Yang Bisa Kamu Ikuti Nih!
						</h2>
						<Link
							href="/mahasiswa/aktivitas/list"
							class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#183669] hover:underline cursor-pointer"
						>
							Lihat Semua
						</Link>
					</div>

					<!-- Activity Cards Grid (5 Columns on Desktop) -->
					<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
						<div
							v-for="item in activities.slice(0, 5)"
							:key="`activity-${item.id}`"
							@click="handleActivityClick"
							class="group relative flex flex-col justify-end overflow-hidden rounded-[12px] sm:rounded-[14px] shadow-sm transition-all duration-300 aspect-[3/4] w-full border border-[#d6e0ee] bg-[#1e293b] hover:-translate-y-1.5 hover:shadow-lg cursor-pointer select-none"
							:title="item.judul"
						>
							<!-- Background Image & Gradient -->
							<img
								:src="getImageUrl(item.gambar)"
								class="absolute inset-0 h-full w-full object-cover"
								:alt="item.judul"
							/>
							<div class="absolute inset-0 bg-gradient-to-t from-[#091e1b] via-[#133830]/80 to-transparent opacity-95"></div>

							<!-- Participant Badge (Top Right) -->
							<div class="absolute right-3 top-3 z-20 flex items-center gap-1.5 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-bold text-[#183669] shadow-sm backdrop-blur-sm">
								<svg class="h-3.5 w-3.5 text-[#183669]" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
								</svg>
								<span>{{ item.peserta || 67 }}</span>
							</div>

							<!-- Content Container -->
							<div class="relative z-10 p-3.5 sm:p-4 flex flex-col justify-end">
								<!-- Deadline Row (White Text) -->
								<div class="mb-1 flex items-center gap-1.5 text-[10.5px] sm:text-[11px] font-semibold text-white">
									<svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
									</svg>
									<span>Deadline : {{ formatDeadline(item.deadline) }}</span>
								</div>

								<!-- Title -->
								<h3 class="mb-1 font-poppins text-[14px] sm:text-[15px] font-bold leading-snug text-white line-clamp-1">
									{{ item.judul }}
								</h3>

								<!-- Description -->
								<p class="mb-2.5 font-inter text-[10.5px] sm:text-[11px] leading-relaxed line-clamp-2 text-slate-200/90">
									{{ item.deskripsi }}
								</p>

								<!-- Category Tags (Pill Badges) -->
								<div class="flex flex-wrap gap-1 sm:gap-1.5">
									<span
										v-for="tag in getActivityTags(item)"
										:key="tag"
										:class="[
											'inline-flex items-center justify-center rounded-full px-2 py-0.5 font-inter text-[10px] font-semibold border shadow-xs',
											getCategoryChipClass(tag)
										]"
									>
										{{ tag }}
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</MahasiswaLayout>
</template>

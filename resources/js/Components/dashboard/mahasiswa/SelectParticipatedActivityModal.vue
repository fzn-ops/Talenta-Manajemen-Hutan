<script setup>
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	eligibleActivities: {
		type: Array,
		default: () => [],
	},
	roadmapCategory: {
		type: String,
		default: 'Roadmap',
	},
});

const emit = defineEmits(['close', 'select']);

const selectedId = ref(null);
const search = ref('');

watch(
	() => props.show,
	(val) => {
		if (val) {
			selectedId.value = null;
			search.value = '';
		}
	}
);

// Hanya ambil aktivitas yang status pendaftarannya telah disetujui (accepted)
const acceptedActivities = computed(() => {
	return (props.eligibleActivities || []).filter((item) => {
		const st = (item.status_pendaftaran || '').toLowerCase();
		return st === 'disetujui' || st === 'accepted';
	});
});

const filteredActivities = computed(() => {
	const list = acceptedActivities.value;
	if (!search.value.trim()) return list;

	const q = search.value.toLowerCase().trim();
	return list.filter((item) => {
		const title = (item.judul || '').toLowerCase();
		const tim = (item.nama_tim || '').toLowerCase();
		return title.includes(q) || tim.includes(q);
	});
});

const selectedItem = computed(() => {
	return acceptedActivities.value.find((item) => item.id === selectedId.value);
});

const handleConfirm = () => {
	if (!selectedItem.value) return;
	emit('select', selectedItem.value);
	emit('close');
};

const normalizeCategory = (cat) => {
	const c = (cat || '').toLowerCase().trim();
	if (c === 'birokrat') return 'birokrasi';
	if (c === 'professional') return 'profesional';
	return c;
};

const isMatchingTag = (tag) => {
	return normalizeCategory(tag) === normalizeCategory(props.roadmapCategory);
};

const activityListUrlWithFilter = computed(() => {
	const cat = props.roadmapCategory || '';
	return `/mahasiswa/aktivitas/list?kategori=${encodeURIComponent(cat)}`;
});
</script>

<template>
	<Teleport to="body">
		<!-- Backdrop -->
		<Transition
			enter-active-class="transition duration-200 ease-out"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="transition duration-150 ease-in"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs font-poppins"
			>
				<div class="absolute inset-0" @click="emit('close')"></div>

				<!-- Modal Box -->
				<Transition
					enter-active-class="transition duration-200 ease-out"
					enter-from-class="opacity-0 scale-95 translate-y-3"
					enter-to-class="opacity-100 scale-100 translate-y-0"
					leave-active-class="transition duration-150 ease-in"
					leave-from-class="opacity-100 scale-100 translate-y-0"
					leave-to-class="opacity-0 scale-95 translate-y-3"
				>
					<div
						v-if="show"
						class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-2xl bg-white shadow-2xl overflow-hidden"
					>
						<!-- Modal Header (Tanpa tag badge pill di atas agar clean dan tidak messy) -->
						<div class="flex items-start justify-between border-b border-[#e2e8f0] px-6 py-5 bg-[#fafcff]">
							<div>
								<h3 class="text-[19px] sm:text-[21px] font-extrabold text-[#17334F]">
									Pilih Kegiatan yang Telah Diikuti
								</h3>
								<p class="mt-1 text-[13px] font-medium text-[#64748b]">
									Pilih kegiatan relevan yang telah disetujui untuk memenuhi tugas ini.
								</p>
							</div>
							<button
								type="button"
								@click="emit('close')"
								class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
								title="Tutup"
							>
								<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
								</svg>
							</button>
						</div>

						<!-- Search Filter jika kegiatan > 2 -->
						<div v-if="acceptedActivities.length > 2" class="px-6 pt-4 pb-2">
							<div class="relative">
								<svg
									class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
									fill="none"
									stroke="currentColor"
									viewBox="0 0 24 24"
								>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
								</svg>
								<input
									type="text"
									v-model="search"
									placeholder="Cari kegiatan yang telah diikuti..."
									class="w-full rounded-xl border border-[#cbd5e1] bg-white py-2 pl-10 pr-4 text-xs sm:text-[13px] text-slate-700 placeholder-slate-400 focus:border-[#183669] focus:outline-none focus:ring-1 focus:ring-[#183669]"
								/>
							</div>
						</div>

						<!-- Modal Body -->
						<div class="flex-1 overflow-y-auto px-6 py-5 space-y-3 custom-scrollbar min-h-[240px]">
							<!-- 1. EMPTY STATE: Belum ada kegiatan yang disetujui (Intuitif & UI Friendly) -->
							<div
								v-if="acceptedActivities.length === 0"
								class="flex flex-col items-center justify-center py-10 px-6 text-center rounded-2xl border border-dashed border-[#cbd5e1] bg-[#f8fafc]"
							>
								<div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#183669]/10 text-[#183669] mb-4">
									<svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
									</svg>
								</div>
								<h4 class="text-[16px] sm:text-[17px] font-extrabold text-[#17334F]">
									Belum Ada Kegiatan yang Disetujui
								</h4>
								<p class="mt-1.5 text-xs sm:text-[13px] text-[#64748b] max-w-md leading-relaxed font-inter">
									Anda belum memiliki riwayat kegiatan dengan status <strong class="text-[#183669]">Disetujui</strong> yang memiliki tag kategori <span class="font-bold text-[#183669]">{{ roadmapCategory }}</span>.
								</p>
								<Link
									:href="activityListUrlWithFilter"
									class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#183669] px-6 py-2.5 text-xs sm:text-[13px] font-bold text-white hover:bg-[#122b54] active:scale-95 transition shadow-sm cursor-pointer"
								>
									<span>Ikuti Aktivitas {{ roadmapCategory }}</span>
									<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
									</svg>
								</Link>
							</div>

							<!-- 2. HASIL PENCARIAN KOSONG -->
							<div
								v-else-if="filteredActivities.length === 0"
								class="flex flex-col items-center justify-center py-10 px-4 text-center"
							>
								<p class="text-sm font-medium text-slate-500">
									Tidak ada kegiatan yang cocok dengan kata kunci <span class="font-bold text-[#17334F]">"{{ search }}"</span>.
								</p>
								<button
									type="button"
									@click="search = ''"
									class="mt-3 text-xs font-bold text-[#183669] hover:underline cursor-pointer"
								>
									Reset Pencarian
								</button>
							</div>

							<!-- 3. LIST KEGIATAN YANG ELIGIBLE -->
							<div
								v-else
								v-for="act in filteredActivities"
								:key="act.id"
								@click="selectedId = act.id"
								:class="[
									'flex items-start gap-4 p-4 rounded-xl border transition-all cursor-pointer select-none',
									selectedId === act.id
										? 'border-[#183669] bg-[#f0f5fc] ring-2 ring-[#183669]/20 shadow-xs'
										: 'border-[#e2e8f0] bg-white hover:border-[#94a3b8] hover:bg-slate-50/70'
								]"
							>
								<!-- Radio Indicator -->
								<div class="pt-0.5 shrink-0">
									<div
										:class="[
											'h-5 w-5 rounded-full border flex items-center justify-center transition-colors',
											selectedId === act.id ? 'border-[#183669] bg-[#183669]' : 'border-slate-300 bg-white'
										]"
									>
										<div v-if="selectedId === act.id" class="h-2 w-2 rounded-full bg-white"></div>
									</div>
								</div>

								<!-- Content Info -->
								<div class="flex-1 min-w-0 space-y-1.5">
									<div class="flex items-start justify-between gap-3">
										<h4 class="font-poppins text-[14px] sm:text-[15px] font-bold text-[#17334F] leading-snug">
											{{ act.judul }}
										</h4>
										<span
											class="shrink-0 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1"
										>
											<svg class="h-3 w-3 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
											</svg>
											Disetujui
										</span>
									</div>

									<!-- Tags Row (Hanya warna hijau dan ceklis, tanpa teks kata Cocok) -->
									<div class="flex flex-wrap items-center gap-1.5 pt-0.5">
										<span
											v-for="(tag, tIdx) in (act.kategori || [])"
											:key="tIdx"
											:class="[
												'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border',
												isMatchingTag(tag)
													? 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold ring-1 ring-emerald-300/40'
													: 'bg-slate-100 text-slate-600 border-slate-200'
											]"
										>
											<svg v-if="isMatchingTag(tag)" class="h-3 w-3 text-emerald-700 shrink-0 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
											</svg>
											{{ tag }}
										</span>
									</div>

									<div class="flex items-center gap-4 text-xs text-slate-500 pt-1 font-inter">
										<span v-if="act.nama_tim" class="truncate font-medium text-slate-700">
											Tim: {{ act.nama_tim }}
										</span>
										<span v-if="act.deadline" class="shrink-0">
											Deadline: {{ act.deadline }}
										</span>
									</div>
								</div>
							</div>
						</div>

						<!-- Modal Footer -->
						<div class="flex items-center justify-end gap-3 border-t border-[#e2e8f0] px-6 py-4 bg-white">
							<button
								type="button"
								@click="emit('close')"
								class="rounded-xl border border-[#cbd5e1] bg-white px-5 py-2.5 text-xs sm:text-[13px] font-bold text-[#17334F] hover:bg-slate-50 transition cursor-pointer"
							>
								Batal
							</button>
							<button
								type="button"
								@click="handleConfirm"
								:disabled="!selectedId"
								:class="[
									'rounded-xl px-6 py-2.5 text-xs sm:text-[13px] font-bold text-white transition shadow-sm cursor-pointer',
									selectedId
										? 'bg-[#183669] hover:bg-[#122b54] active:scale-95'
										: 'bg-slate-300 text-slate-500 cursor-not-allowed shadow-none'
								]"
							>
								Gunakan Kegiatan Ini
							</button>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
	width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
	background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
	background: #cbd5e1;
	border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
	background: #94a3b8;
}
</style>

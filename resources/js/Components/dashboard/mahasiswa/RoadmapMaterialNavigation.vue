<script setup>
const props = defineProps({
	items: {
		type: Array,
		required: true,
	},
	selectedItemId: {
		type: [Number, String],
		default: null,
	},
});

const emit = defineEmits(['update:selectedItemId']);

// Helper to determine status for Tugas
const getTaskStatus = (item) => {
	if (item.status === 'diterima' || item.status === 'approved' || (item.isCompleted && item.status !== 'ditolak' && item.status !== 'menunggu')) {
		return 'diterima';
	}
	if (item.status === 'menunggu' || item.status === 'pending' || (item.activities && item.activities.length > 0 && item.activities.some((a) => a.notes || a.fileName))) {
		return 'menunggu';
	}
	if (item.status === 'ditolak' || item.status === 'rejected') {
		return 'ditolak';
	}
	return 'belum';
};
</script>

<template>
	<div class="w-full font-poppins">
		<!-- Items list with responsive scrollbar on desktop and natural height in drawers -->
		<div class="space-y-2 sm:space-y-2.5 md:max-h-[calc(100vh-220px)] md:overflow-y-auto md:pr-1.5 custom-scrollbar">
			<div
				v-for="item in items"
				:key="item.id"
				@click="emit('update:selectedItemId', item.id)"
				:style="{ borderRadius: '10px' }"
				:class="[
					'group flex min-h-[44px] sm:min-h-[48px] items-center justify-between rounded-[10px] px-3.5 py-2.5 sm:px-4 sm:py-3 transition-colors duration-150 cursor-pointer select-none border',
					selectedItemId === item.id
						? 'bg-[#183669] text-white border-[#183669] shadow-sm'
						: 'bg-white text-[#183669] border-[#d6e0ee] hover:border-[#183669] hover:bg-[#fafcff]'
				]"
			>
				<!-- Left: Item Title -->
				<div class="flex items-center min-w-0 py-0.5 pr-2">
					<span
						:class="[
							'font-poppins text-[13.5px] sm:text-[14.5px] font-bold truncate block leading-snug',
							selectedItemId === item.id ? 'text-white' : 'text-[#183669]'
						]"
						:title="item.title"
					>
						{{ item.title }}
					</span>
				</div>

				<!-- Right: Status Indicator -->
				<div class="shrink-0 flex items-center ml-2">
					<!-- ================= BEHAVIOR UNTUK MATERI ================= -->
					<template v-if="item.type === 'Materi'">
						<!-- Selesai: SVG Disetujui / Selesai -->
						<svg
							v-if="item.isCompleted"
							:class="[
								'h-4 w-4 sm:h-[18px] sm:w-[18px] shrink-0 transition-transform',
								selectedItemId === item.id ? 'text-emerald-400' : 'text-green-500'
							]"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							stroke-width="2"
							title="Materi Selesai Dibaca"
						>
							<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>

						<!-- Belum Selesai (Pending): Subtle Circle Outline -->
						<div
							v-else
							:class="[
								'flex h-4 w-4 sm:h-[18px] sm:w-[18px] items-center justify-center rounded-full border-2 transition-colors',
								selectedItemId === item.id
									? 'border-white/40 bg-white/10'
									: 'border-[#cbd5e1] group-hover:border-[#183669]/50'
							]"
							title="Belum Selesai"
						></div>
					</template>

					<!-- ================= BEHAVIOR UNTUK TUGAS ================= -->
					<template v-else-if="item.type === 'Tugas'">
						<!-- Status: Disetujui / Diterima (SVG dari ActivityApproval.vue) -->
						<svg
							v-if="getTaskStatus(item) === 'diterima'"
							:class="[
								'h-4 w-4 sm:h-[18px] sm:w-[18px] shrink-0 transition-transform',
								selectedItemId === item.id ? 'text-emerald-400' : 'text-green-500'
							]"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							stroke-width="2"
							title="Tugas Disetujui / Diterima"
						>
							<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>

						<!-- Status: Menunggu (SVG dari ActivityApproval.vue) -->
						<svg
							v-else-if="getTaskStatus(item) === 'menunggu'"
							:class="[
								'h-4 w-4 sm:h-[18px] sm:w-[18px] shrink-0 transition-transform',
								selectedItemId === item.id ? 'text-amber-300' : 'text-gray-500'
							]"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							stroke-width="2"
							title="Menunggu Persetujuan Admin"
						>
							<circle cx="12" cy="12" r="9" />
							<polyline points="12 7 12 12 15 15" />
						</svg>

						<!-- Status: Ditolak (SVG dari ActivityApproval.vue) -->
						<svg
							v-else-if="getTaskStatus(item) === 'ditolak'"
							:class="[
								'h-4 w-4 sm:h-[18px] sm:w-[18px] shrink-0 transition-transform',
								selectedItemId === item.id ? 'text-red-400' : 'text-red-500'
							]"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							stroke-width="2"
							title="Tugas Ditolak"
						>
							<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>

						<!-- Status: Belum Dikerjakan -->
						<div
							v-else
							:class="[
								'flex h-4 w-4 sm:h-[18px] sm:w-[18px] items-center justify-center rounded-full border-2 transition-colors',
								selectedItemId === item.id
									? 'border-white/40 bg-white/10'
									: 'border-[#cbd5e1] group-hover:border-[#183669]/50'
							]"
							title="Tugas Belum Dikerjakan"
						></div>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
	width: 5px;
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



<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	students: {
		type: Array,
		default: () => [],
	},
});

const emit = defineEmits(['close', 'confirm']);

const selectedAngkatans = ref([]);
const isDeleting = ref(false);

// Extract available angkatans from students
const availableAngkatans = computed(() => {
	const angkatans = new Set();
	props.students.forEach((s) => {
		if (s.angkatan) {
			angkatans.add(s.angkatan.toString());
		}
	});
	return Array.from(angkatans).sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
});

// Count students per angkatan
const getStudentCountByAngkatan = (angkatan) => {
	return props.students.filter((s) => s.angkatan?.toString() === angkatan.toString()).length;
};

// Total students that will be deleted based on selection
const totalSelectedStudentsCount = computed(() => {
	return props.students.filter((s) => selectedAngkatans.value.includes(s.angkatan?.toString())).length;
});

// Select All toggle
const isAllSelected = computed(() => {
	return (
		availableAngkatans.value.length > 0 &&
		selectedAngkatans.value.length === availableAngkatans.value.length
	);
});

const toggleSelectAll = () => {
	if (isAllSelected.value) {
		selectedAngkatans.value = [];
	} else {
		selectedAngkatans.value = [...availableAngkatans.value];
	}
};

// Reset state on open/close
watch(
	() => props.show,
	(isOpen) => {
		if (isOpen) {
			selectedAngkatans.value = [];
			isDeleting.value = false;
		}
	}
);

const handleClose = () => {
	if (!isDeleting.value) {
		emit('close');
	}
};

const handleConfirm = () => {
	if (selectedAngkatans.value.length === 0 || isDeleting.value) return;

	isDeleting.value = true;
	emit('confirm', {
		angkatans: [...selectedAngkatans.value],
		count: totalSelectedStudentsCount.value,
	});
};

const handleKeyDown = (e) => {
	if (e.key === 'Escape' && props.show) {
		handleClose();
	}
};

onMounted(() => {
	document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
	document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
	<Teleport to="body">
		<Transition
			enter-active-class="ease-out duration-200"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="ease-in duration-150"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/40 backdrop-blur-xs p-3.5 sm:p-4 font-poppins"
				@click.self="handleClose"
			>
				<Transition
					enter-active-class="ease-out duration-200"
					enter-from-class="opacity-0 scale-95 translate-y-2"
					enter-to-class="opacity-100 scale-100 translate-y-0"
					leave-active-class="ease-in duration-150"
					leave-from-class="opacity-100 scale-100 translate-y-0"
					leave-to-class="opacity-0 scale-95 translate-y-2"
				>
					<div
						v-if="show"
						class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-100"
					>
						<!-- Modal Header -->
						<div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
							<div class="flex items-center gap-3">
								<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
									<svg class="h-5 w-auto" viewBox="0 0 14 18" fill="currentColor">
										<path d="M0.942857 15.6444C0.942857 16.72 1.79143 17.6 2.82857 17.6H10.3714C11.4086 17.6 12.2571 16.72 12.2571 15.6444V5.86667C12.2571 4.79111 11.4086 3.91111 10.3714 3.91111H2.82857C1.79143 3.91111 0.942857 4.79111 0.942857 5.86667V15.6444ZM12.2571 0.977778H9.9L9.23057 0.283556C9.06086 0.107556 8.81571 0 8.57057 0H4.62943C4.38429 0 4.13914 0.107556 3.96943 0.283556L3.3 0.977778H0.942857C0.424286 0.977778 0 1.41778 0 1.95556C0 2.49333 0.424286 2.93333 0.942857 2.93333H12.2571C12.7757 2.93333 13.2 2.49333 13.2 1.95556C13.2 1.41778 12.7757 0.977778 12.2571 0.977778Z" />
									</svg>
								</div>
								<div>
									<h2 class="text-base font-bold text-[#17334F]">
										Hapus Banyak Mahasiswa
									</h2>
									<p class="text-xs text-slate-500">
										Hapus data mahasiswa secara massal berdasarkan angkatan
									</p>
								</div>
							</div>

							<!-- Close (X) Button -->
							<button
								type="button"
								@click="handleClose"
								class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none transition cursor-pointer"
							>
								<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
								</svg>
							</button>
						</div>

						<!-- Modal Body -->
						<div class="p-6 space-y-5">
							<!-- Angkatan Selection Card -->
							<div class="space-y-2.5">
								<div class="flex items-center justify-between">
									<label class="text-xs font-semibold text-[#17334F]">
										Pilih Angkatan yang Ingin Dihapus
									</label>
									<button
										v-if="availableAngkatans.length > 0"
										type="button"
										@click="toggleSelectAll"
										class="text-xs font-semibold text-[#183669] hover:underline cursor-pointer"
									>
										{{ isAllSelected ? 'Batalkan Semua' : 'Pilih Semua' }}
									</button>
								</div>

								<!-- List of Angkatans with Checkboxes -->
								<div class="max-h-52 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/50 p-2 space-y-1 divide-y divide-slate-100">
									<label
										v-for="ang in availableAngkatans"
										:key="ang"
										class="flex items-center justify-between rounded-lg px-3 py-2.5 hover:bg-white hover:shadow-xs transition cursor-pointer select-none"
									>
										<div class="flex items-center gap-3">
											<input
												type="checkbox"
												:value="ang"
												v-model="selectedAngkatans"
												class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 cursor-pointer"
											/>
											<span class="text-sm font-medium text-slate-700">
												Angkatan {{ ang }}
											</span>
										</div>
										<span class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded-md">
											{{ getStudentCountByAngkatan(ang) }} mahasiswa
										</span>
									</label>

									<div v-if="availableAngkatans.length === 0" class="py-4 text-center text-xs text-slate-400">
										Tidak ada data angkatan tersedia
									</div>
								</div>
							</div>

							<!-- Summary Box -->
							<div
								v-if="selectedAngkatans.length > 0"
								class="flex items-center justify-between rounded-xl bg-red-50/80 border border-red-100 px-4 py-3 text-xs"
							>
								<span class="font-medium text-red-800">
									Total Mahasiswa yang akan dihapus:
								</span>
								<span class="font-bold text-red-700 text-sm">
									{{ totalSelectedStudentsCount }} Data
								</span>
							</div>

							<!-- Danger Warning Banner -->
							<div class="flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200/70 p-3.5 text-xs text-amber-800">
								<svg class="h-5 w-5 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
								</svg>
								<div class="leading-relaxed">
									<span class="font-bold">Perhatian:</span> Seluruh data mahasiswa dan akun pada angkatan yang dipilih akan dihapus secara permanen dari sistem. Tindakan ini tidak dapat dibatalkan.
								</div>
							</div>
						</div>

						<!-- Modal Footer -->
						<div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
							<button
								type="button"
								:disabled="isDeleting"
								@click="handleClose"
								class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none transition cursor-pointer disabled:opacity-50"
							>
								Batal
							</button>

							<button
								type="button"
								:disabled="selectedAngkatans.length === 0 || isDeleting"
								@click="handleConfirm"
								class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 active:scale-95 focus:outline-none transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
							>
								<svg
									v-if="isDeleting"
									class="h-4 w-4 animate-spin text-white"
									xmlns="http://www.w3.org/2000/svg"
									fill="none"
									viewBox="0 0 24 24"
								>
									<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
									<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
								</svg>
								<span>
									{{
										isDeleting
											? 'Menghapus...'
											: selectedAngkatans.length > 0
											? `Hapus (${totalSelectedStudentsCount} Data)`
											: 'Pilih Angkatan'
									}}
								</span>
							</button>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>

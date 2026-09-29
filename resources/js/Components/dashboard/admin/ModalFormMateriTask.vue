<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	monthName: {
		type: String,
		default: '',
	},
});

const emit = defineEmits(['close', 'submit']);

const categoryOptions = ['Materi', 'Tugas'];

const form = ref({
	category: 'Materi',
	title: '',
});

const errors = ref({});
const isCategoryDropdownOpen = ref(false);
const categoryDropdownRef = ref(null);

watch(
	() => props.show,
	(val) => {
		if (val) {
			form.value = {
				category: 'Materi',
				title: '',
			};
			errors.value = {};
			isCategoryDropdownOpen.value = false;
		}
	}
);

const selectCategory = (cat) => {
	form.value.category = cat;
	errors.value.category = '';
	isCategoryDropdownOpen.value = false;
};

const handleDocumentClick = (e) => {
	if (categoryDropdownRef.value && !categoryDropdownRef.value.contains(e.target)) {
		isCategoryDropdownOpen.value = false;
	}
};

onMounted(() => {
	document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
	document.removeEventListener('click', handleDocumentClick);
});

const handleClose = () => {
	emit('close');
};

const handleSubmit = () => {
	errors.value = {};
	if (!form.value.category) {
		errors.value.category = 'Kategori wajib dipilih.';
	}
	if (!form.value.title.trim()) {
		errors.value.title = 'Nama Materi/Task wajib diisi.';
	}

	if (Object.keys(errors.value).length > 0) return;

	emit('submit', {
		type: form.value.category,
		title: form.value.title.trim(),
	});
};
</script>

<template>
	<Teleport to="body">
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
				class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain bg-black/40 p-4 backdrop-blur-xs font-poppins"
				@click.self="handleClose"
			>
				<!-- Modal Container -->
				<div class="w-full max-w-[440px] rounded-[20px] bg-white p-7 sm:p-8 shadow-2xl border border-[#e2e8f0] relative">
					<!-- Title -->
					<h3 class="text-center text-[20px] sm:text-[22px] font-extrabold text-[#17334F] tracking-tight">
						Form Tambah Materi/Task
					</h3>

					<form @submit.prevent="handleSubmit" class="mt-6 space-y-5">
						<!-- 1. Kategori Field -->
						<div ref="categoryDropdownRef" class="relative">
							<label class="block text-[13px] font-bold text-[#17334F]">
								Kategori<span class="text-red-500">*</span>
							</label>
							<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">
								Pilih Kategori Materi atau Tugas
							</p>

							<!-- Custom Dropdown Button -->
							<div class="relative mt-1.5">
								<button
									type="button"
									@click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
									class="h-[46px] w-full rounded-[10px] border bg-white px-4 font-inter text-[13.5px] transition-colors duration-150 flex items-center justify-between cursor-pointer focus:outline-none focus:ring-0 select-none text-left"
									:class="[
										errors.category
											? 'border-red-400 bg-red-50/20'
											: (isCategoryDropdownOpen
												? 'border-[#183669] bg-white'
												: 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff]')
									]"
								>
									<span :class="form.category ? 'text-[#1e3456] font-medium' : 'text-[#94a3b8]'">
										{{ form.category || 'Materi/Task' }}
									</span>
									<svg
										:class="['h-4 w-4 text-[#183669] transition-transform duration-150 shrink-0', isCategoryDropdownOpen ? 'rotate-180' : '']"
										fill="none"
										stroke="currentColor"
										stroke-width="2.5"
										viewBox="0 0 24 24"
									>
										<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
									</svg>
								</button>

								<!-- Dropdown Popover List -->
								<Transition
									enter-active-class="transition duration-150 ease-out"
									enter-from-class="transform scale-95 opacity-0 -translate-y-1"
									enter-to-class="transform scale-100 opacity-100 translate-y-0"
									leave-active-class="transition duration-100 ease-in"
									leave-from-class="transform scale-100 opacity-100 translate-y-0"
									leave-to-class="transform scale-95 opacity-0 -translate-y-1"
								>
									<div
										v-if="isCategoryDropdownOpen"
										class="absolute left-0 top-full mt-1.5 z-50 w-full rounded-[12px] border border-[#d6e0ee] bg-white p-1.5 shadow-xl ring-1 ring-black/5"
									>
										<button
											v-for="cat in categoryOptions"
											:key="cat"
											type="button"
											@click="selectCategory(cat)"
											:class="[
												'flex w-full items-center justify-between px-3 py-2.5 rounded-[8px] text-left font-inter text-[13.5px] transition-colors cursor-pointer',
												form.category === cat
													? 'bg-[#183669]/10 font-bold text-[#183669]'
													: 'text-[#2c4363] hover:bg-[#f1f5f9]'
											]"
										>
											<span>{{ cat }}</span>
											<svg
												v-if="form.category === cat"
												class="h-4 w-4 text-[#183669] shrink-0"
												fill="none"
												stroke="currentColor"
												stroke-width="2.5"
												viewBox="0 0 24 24"
											>
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
											</svg>
										</button>
									</div>
								</Transition>
							</div>
							<p v-if="errors.category" class="mt-1 font-inter text-[11px] font-medium text-red-500">
								{{ errors.category }}
							</p>
						</div>

						<!-- 2. Nama Materi/Task Field -->
						<div>
							<label class="block text-[13px] font-bold text-[#17334F]">
								Nama Materi/Task<span class="text-red-500">*</span>
							</label>
							<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">
								Masukkan Nama Materi atau Tugas
							</p>
							<input
								v-model="form.title"
								type="text"
								placeholder="Cara mendapatkan uang saku"
								@input="errors.title = ''"
								class="mt-1.5 h-[46px] w-full rounded-[10px] border bg-white px-4 font-inter text-[13.5px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
								:class="errors.title ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
							/>
							<p v-if="errors.title" class="mt-1 font-inter text-[11px] font-medium text-red-500">
								{{ errors.title }}
							</p>
						</div>

						<!-- Action Buttons (Kembali & Simpan) -->
						<div class="pt-4 grid grid-cols-2 gap-3.5">
							<button
								type="button"
								@click="handleClose"
								class="flex h-[44px] items-center justify-center rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13.5px] sm:text-[14px] font-bold text-[#183669] transition hover:bg-slate-50 active:scale-98 focus:outline-none cursor-pointer"
							>
								Kembali
							</button>
							<button
								type="submit"
								class="flex h-[44px] items-center justify-center rounded-[10px] bg-[#183669] font-poppins text-[13.5px] sm:text-[14px] font-bold text-white transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer shadow-xs"
							>
								Simpan
							</button>
						</div>
					</form>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>

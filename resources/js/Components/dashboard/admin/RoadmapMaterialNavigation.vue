<script setup>
import { ref, nextTick } from 'vue';

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

const emit = defineEmits([
	'update:selectedItemId',
	'update-item-title',
	'delete-item',
	'add-item',
]);

// Inline Edit State
const editingItemId = ref(null);
const editingItemTitle = ref('');

const adjustTextareaHeight = (el) => {
	if (!el) return;
	el.style.height = 'auto';
	el.style.height = `${Math.max(el.scrollHeight, 26)}px`;
};

const startInlineEdit = (item, e) => {
	e?.stopPropagation();
	emit('update:selectedItemId', item.id);
	editingItemId.value = item.id;
	editingItemTitle.value = item.title;
	nextTick(() => {
		const el = document.getElementById(`inline-edit-item-${item.id}`);
		if (el) {
			adjustTextareaHeight(el);
			el.focus();
			const len = el.value.length;
			el.setSelectionRange(len, len);
		}
	});
};

const saveInlineItemTitle = (item, e) => {
	e?.stopPropagation();
	const newTitle = editingItemTitle.value.trim();
	if (newTitle && newTitle !== item.title) {
		emit('update-item-title', item, newTitle);
	}
	editingItemId.value = null;
};

const cancelInlineEdit = () => {
	editingItemId.value = null;
};
</script>

<template>
	<div class="space-y-3">
		<!-- List of Items -->
		<div class="space-y-2.5">
			<div
				v-for="item in items"
				:key="item.id"
				@click="emit('update:selectedItemId', item.id)"
				:style="{ borderRadius: '10px' }"
				:class="[
					'group flex items-center justify-between rounded-[10px] p-2.5 px-3 transition-colors cursor-pointer select-none',
					selectedItemId === item.id
						? 'bg-[#183669] text-white shadow-xs'
						: 'bg-white text-[#183669] border border-[#d6e0ee] hover:border-[#183669] shadow-xs'
				]"
			>
				<!-- Left Title Block -->
				<div class="flex-1 min-w-0 py-0.5 px-1 mr-1">
					<!-- Inline Edit Mode -->
					<template v-if="editingItemId === item.id">
						<div class="relative w-full">
							<textarea
								:id="`inline-edit-item-${item.id}`"
								v-model="editingItemTitle"
								rows="1"
								spellcheck="false"
								autocomplete="off"
								maxlength="65"
								@input="adjustTextareaHeight($event.target)"
								@click.stop
								@keydown.enter.exact.prevent="saveInlineItemTitle(item, $event)"
								@keydown.esc="cancelInlineEdit"
								:style="{
									wordBreak: 'break-word',
									overflowWrap: 'anywhere'
								}"
								:class="[
									'w-full resize-none rounded-[6px] border border-dashed bg-transparent px-2 py-0 font-poppins text-[13.5px] sm:text-[14px] font-bold leading-[24px] focus:outline-none focus:ring-0 transition-none block overflow-hidden',
									selectedItemId === item.id
										? 'border-white text-white placeholder-white/60 focus:border-white'
										: 'border-[#183669] text-[#183669] placeholder-[#183669]/60 focus:border-[#183669]'
								]"
								placeholder="Nama materi/tugas..."
							></textarea>
							<div 
								:class="[
									'text-right text-[10px] font-medium mt-1 pr-1',
									selectedItemId === item.id ? 'text-white/80' : 'text-[#8ca1b9]'
								]"
							>
								{{ editingItemTitle.length }}/65
							</div>
						</div>
					</template>

					<!-- Normal Display Mode -->
					<template v-else>
						<span
							:class="[
								'font-poppins text-[13.5px] sm:text-[14px] font-bold leading-[26px] line-clamp-2 block px-2',
								selectedItemId === item.id ? 'text-white' : 'text-[#183669]'
							]"
							:style="{
								display: '-webkit-box',
								WebkitLineClamp: 2,
								WebkitBoxOrient: 'vertical',
								overflow: 'hidden',
								textOverflow: 'ellipsis',
								wordBreak: 'break-word',
								overflowWrap: 'anywhere'
							}"
							:title="item.title"
						>
							{{ item.title }}
						</span>
					</template>
				</div>

				<!-- Right Action Buttons -->
				<div class="flex items-center gap-1.5 shrink-0" @click.stop>
					<!-- Submit Button when in Edit Mode -->
					<button
						v-if="editingItemId === item.id"
						type="button"
						@click="saveInlineItemTitle(item, $event)"
						:class="[
							'flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:opacity-90 active:scale-95 focus:outline-none cursor-pointer shadow-xs',
							selectedItemId === item.id
								? 'bg-emerald-500 text-white hover:bg-emerald-600'
								: 'bg-emerald-600 text-white hover:bg-emerald-700'
						]"
						:title="`Simpan perubahan ${item.title}`"
					>
						<svg class="h-4 w-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
						</svg>
					</button>

					<!-- Edit Button when Normal -->
					<button
						v-else
						type="button"
						@click="startInlineEdit(item, $event)"
						class="flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:opacity-85 active:scale-95 focus:outline-none cursor-pointer bg-[#ffd56a] text-[#f4a300]"
						:title="`Edit ${item.title}`"
					>
						<img src="/assets/icons/edit.svg" alt="Edit" class="h-4 w-4 object-contain" />
					</button>

					<!-- Delete Item Button -->
					<button
						type="button"
						@click="emit('delete-item', item, $event)"
						class="flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:opacity-85 active:scale-95 focus:outline-none cursor-pointer bg-[#ff9ca1] text-[#ff2f35]"
						:title="`Hapus ${item.title}`"
					>
						<img src="/assets/icons/delete.svg" alt="Delete" class="h-4 w-4 object-contain" />
					</button>
				</div>
			</div>
		</div>

		<!-- Add New Item Button -->
		<button
			type="button"
			@click="emit('add-item')"
			:style="{ borderRadius: '10px' }"
			class="flex w-full items-center justify-center rounded-[10px] border border-[#d6e0ee] bg-white py-3.5 text-[#183669] shadow-xs transition hover:border-[#183669] hover:bg-slate-50 active:scale-98 focus:outline-none cursor-pointer"
			title="Tambah Materi / Tugas"
		>
			<svg class="h-6 w-6 text-[#183669] stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
			</svg>
		</button>
	</div>
</template>

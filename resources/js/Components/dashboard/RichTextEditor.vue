<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
	modelValue: {
		type: String,
		default: '',
	},
	placeholder: {
		type: String,
		default: 'Tuliskan deskripsi di sini...',
	},
	minHeight: {
		type: String,
		default: '130px',
	},
	hasError: {
		type: Boolean,
		default: false,
	},
	error: {
		type: [Boolean, String],
		default: false,
	},
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const containerRef = ref(null);

// Resizing height state
const editorHeight = ref(props.minHeight || '130px');
const isResizing = ref(false);

const startResize = (e) => {
	if (e.type === 'mousedown') {
		e.preventDefault();
	}
	e.stopPropagation();
	isResizing.value = true;
	const startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
	const startHeight = editorRef.value ? editorRef.value.offsetHeight : 130;

	const onMouseMove = (moveEvent) => {
		if (!isResizing.value) return;
		const clientY = moveEvent.type.includes('touch') ? moveEvent.touches[0].clientY : moveEvent.clientY;
		const newHeight = Math.max(90, Math.min(500, startHeight + (clientY - startY)));
		editorHeight.value = `${newHeight}px`;
	};

	const onMouseUp = (upEvent) => {
		upEvent.stopPropagation();
		isResizing.value = false;
		window.removeEventListener('mousemove', onMouseMove);
		window.removeEventListener('mouseup', onMouseUp);
		window.removeEventListener('touchmove', onMouseMove);
		window.removeEventListener('touchend', onMouseUp);
	};

	window.addEventListener('mousemove', onMouseMove);
	window.addEventListener('mouseup', onMouseUp);
	window.addEventListener('touchmove', onMouseMove, { passive: false });
	window.addEventListener('touchend', onMouseUp);
};

// Formats State
const activeFormats = ref({
	bold: false,
	italic: false,
	underline: false,
	strikeThrough: false,
	h1: false,
	h2: false,
	h3: false,
	blockquote: false,
	insertUnorderedList: false,
	insertOrderedList: false,
	justifyLeft: false,
	justifyCenter: false,
	justifyRight: false,
	justifyFull: false,
	isLink: false,
});

// Dropdown states
const showHeadingMenu = ref(false);
const showMoreMenu = ref(false);

// Active block label
const currentHeadingLabel = computed(() => {
	if (activeFormats.value.h1) return 'Heading 1';
	if (activeFormats.value.h2) return 'Heading 2';
	if (activeFormats.value.h3) return 'Heading 3';
	if (activeFormats.value.blockquote) return 'Kutipan';
	return 'Paragraf';
});

const isEmpty = ref(true);

// Link Modal State
const showLinkModal = ref(false);
const linkUrl = ref('');
const linkText = ref('');
const savedSelection = ref(null);

const saveSelection = () => {
	if (window.getSelection) {
		const sel = window.getSelection();
		if (sel.getRangeAt && sel.rangeCount) {
			savedSelection.value = sel.getRangeAt(0);
		}
	}
};

const restoreSelection = () => {
	if (savedSelection.value && window.getSelection) {
		const sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(savedSelection.value);
	}
};

const updateEmptyState = () => {
	if (!editorRef.value) {
		isEmpty.value = true;
		return;
	}
	const text = editorRef.value.innerText.trim();
	const hasMedia = !!editorRef.value.querySelector('img') || !!editorRef.value.querySelector('hr');
	const hasBlock = !!editorRef.value.querySelector('blockquote') || !!editorRef.value.querySelector('ul') || !!editorRef.value.querySelector('ol') || !!editorRef.value.querySelector('h1') || !!editorRef.value.querySelector('h2') || !!editorRef.value.querySelector('h3');

	isEmpty.value = text === '' && !hasMedia && !hasBlock;
};

const showPlaceholder = computed(() => {
	if (!isEmpty.value) return false;
	if (
		activeFormats.value.blockquote ||
		activeFormats.value.h1 ||
		activeFormats.value.h2 ||
		activeFormats.value.h3 ||
		activeFormats.value.insertUnorderedList ||
		activeFormats.value.insertOrderedList
	) {
		return false;
	}
	return true;
});

const checkLinkState = () => {
	if (typeof window === 'undefined') return false;
	const sel = window.getSelection();
	if (!sel.rangeCount) return false;
	let node = sel.anchorNode;
	while (node && node !== editorRef.value) {
		if (node.nodeName === 'A') return true;
		node = node.parentNode;
	}
	return false;
};

const isSelectionInsideEditor = () => {
	if (typeof window === 'undefined' || !editorRef.value) return false;
	const sel = window.getSelection();
	if (!sel || !sel.rangeCount || sel.rangeCount === 0) return false;
	const node = sel.anchorNode;
	return node && (node === editorRef.value || editorRef.value.contains(node));
};

const updateActiveFormats = () => {
	if (typeof document === 'undefined' || !editorRef.value) return;

	// Only query active formats if user is actively focused/selecting inside the editor
	if (isSelectionInsideEditor()) {
		try {
			activeFormats.value.bold = document.queryCommandState('bold');
			activeFormats.value.italic = document.queryCommandState('italic');
			activeFormats.value.underline = document.queryCommandState('underline');
			activeFormats.value.strikeThrough = document.queryCommandState('strikeThrough');
			activeFormats.value.insertUnorderedList = document.queryCommandState('insertUnorderedList');
			activeFormats.value.insertOrderedList = document.queryCommandState('insertOrderedList');
			activeFormats.value.justifyLeft = document.queryCommandState('justifyLeft');
			activeFormats.value.justifyCenter = document.queryCommandState('justifyCenter');
			activeFormats.value.justifyRight = document.queryCommandState('justifyRight');
			activeFormats.value.justifyFull = document.queryCommandState('justifyFull');

			const block = (document.queryCommandValue('formatBlock') || '').toLowerCase();
			activeFormats.value.h1 = block === 'h1';
			activeFormats.value.h2 = block === 'h2';
			activeFormats.value.h3 = block === 'h3';
			activeFormats.value.blockquote = block === 'blockquote';
			activeFormats.value.isLink = checkLinkState();
		} catch (e) {
			// Ignore command state query errors
		}
	}

	updateEmptyState();
};

const exec = (command, value = null) => {
	if (!editorRef.value) return;
	editorRef.value.focus();
	document.execCommand(command, false, value);
	handleInput();
	updateActiveFormats();
};

const setBlockFormat = (tag) => {
	showHeadingMenu.value = false;
	if (!editorRef.value) return;
	editorRef.value.focus();
	if (tag === 'p') {
		document.execCommand('formatBlock', false, '<p>');
	} else if (tag === 'blockquote') {
		document.execCommand('formatBlock', false, '<blockquote>');
	} else {
		try {
			document.execCommand('formatBlock', false, `<${tag}>`);
		} catch (e) {
			document.execCommand('formatBlock', false, tag);
		}
	}
	handleInput();
	updateActiveFormats();
};

const insertHorizontalRule = () => {
	showMoreMenu.value = false;
	exec('insertHorizontalRule');
};

const insertCodeBlock = () => {
	showMoreMenu.value = false;
	if (!editorRef.value) return;
	editorRef.value.focus();
	const sel = window.getSelection();
	if (sel && sel.rangeCount > 0) {
		const selectedText = sel.toString();
		if (selectedText) {
			document.execCommand('insertHTML', false, `<code>${selectedText}</code>`);
		} else {
			document.execCommand('insertHTML', false, `<code>code</code>`);
		}
	}
	handleInput();
	updateActiveFormats();
};

// Link Handling
const openLinkModal = () => {
	saveSelection();
	const sel = window.getSelection();
	linkText.value = sel ? sel.toString() : '';
	linkUrl.value = '';

	if (editorRef.value) {
		let node = sel ? sel.anchorNode : null;
		while (node && node !== editorRef.value) {
			if (node.nodeName === 'A') {
				linkUrl.value = node.getAttribute('href') || '';
				if (!linkText.value) linkText.value = node.innerText || '';
				break;
			}
			node = node?.parentNode;
		}
	}

	showLinkModal.value = true;
};

const applyLink = () => {
	showLinkModal.value = false;
	if (!editorRef.value) return;
	editorRef.value.focus();
	restoreSelection();

	let url = linkUrl.value.trim();
	if (!url) return;
	if (!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url) && !url.startsWith('/')) {
		url = 'https://' + url;
	}

	if (linkText.value.trim() && (!savedSelection.value || savedSelection.value.toString() !== linkText.value.trim())) {
		const html = `<a href="${url}" target="_blank" rel="noopener noreferrer">${linkText.value.trim()}</a>`;
		document.execCommand('insertHTML', false, html);
	} else {
		document.execCommand('createLink', false, url);
		const links = editorRef.value.querySelectorAll('a[href="' + url + '"]');
		links.forEach((a) => {
			a.setAttribute('target', '_blank');
			a.setAttribute('rel', 'noopener noreferrer');
		});
	}

	handleInput();
	updateActiveFormats();
};

const removeLink = () => {
	exec('unlink');
};

const handleInput = () => {
	if (!editorRef.value) return;
	let html = editorRef.value.innerHTML;
	if (html === '<p><br></p>' || html === '<br>' || html === '<p></p>') {
		html = '';
	}
	updateEmptyState();
	emit('update:modelValue', html);
};

// Click outside handler for dropdowns
const handleDocumentClick = (e) => {
	if (containerRef.value && !containerRef.value.contains(e.target)) {
		showHeadingMenu.value = false;
		showMoreMenu.value = false;
	}
};

watch(
	() => props.modelValue,
	(newVal) => {
		if (editorRef.value && newVal !== editorRef.value.innerHTML) {
			editorRef.value.innerHTML = newVal || '';
			updateEmptyState();
		}
	},
);

onMounted(() => {
	if (editorRef.value) {
		editorRef.value.innerHTML = props.modelValue || '';
		updateEmptyState();
	}
	document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
	document.removeEventListener('click', handleDocumentClick);
});
</script>

<template>
	<div
		ref="containerRef"
		:class="[
			'relative flex flex-col rounded-[10px] border bg-white transition-colors duration-150',
			(hasError || error)
				? 'border-red-400 focus-within:border-red-500 bg-red-50/10'
				: 'border-[#d6e0ee] hover:border-[#a6b7cb] focus-within:border-[#183669]'
		]"
	>
		<!-- Compact Single-Row Toolbar -->
		<div class="border-b border-[#e6edf6] bg-[#f8fafc] px-2 py-1.5 rounded-t-[9px] select-none text-[#435b76]">
			<!-- Toolbar Items -->
			<div class="flex flex-wrap w-full items-center gap-1.5">
				<!-- Heading Dropdown -->
				<div class="relative">
					<button
						type="button"
						@mousedown.prevent
						@click.stop="showHeadingMenu = !showHeadingMenu; showMoreMenu = false"
						class="flex h-7 items-center gap-1.5 rounded-[6px] px-2 text-[12px] font-medium text-[#2f4b6e] transition hover:bg-[#e8eef8]"
						title="Format Teks"
					>
						<span>{{ currentHeadingLabel }}</span>
						<svg class="h-3 w-3 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
						</svg>
					</button>

					<!-- Heading Dropdown Menu -->
					<div
						v-if="showHeadingMenu"
						class="absolute left-0 top-full z-40 mt-1 w-36 rounded-[8px] border border-[#d6e0ee] bg-white py-1 shadow-lg ring-1 ring-black/5"
					>
						<button
							type="button"
							@mousedown.prevent
							@click="setBlockFormat('p')"
							:class="['flex w-full items-center px-3 py-1.5 text-left text-xs transition hover:bg-[#f0f4f9]', !activeFormats.h1 && !activeFormats.h2 && !activeFormats.h3 && !activeFormats.blockquote ? 'font-bold text-[#183669] bg-[#f0f4f9]' : 'text-[#435b76]']"
						>
							Paragraf Biasa
						</button>
						<button
							type="button"
							@mousedown.prevent
							@click="setBlockFormat('h1')"
							:class="['flex w-full items-center px-3 py-1.5 text-left text-xs transition hover:bg-[#f0f4f9]', activeFormats.h1 ? 'font-bold text-[#183669] bg-[#f0f4f9]' : 'text-[#435b76]']"
						>
							Heading 1
						</button>
						<button
							type="button"
							@mousedown.prevent
							@click="setBlockFormat('h2')"
							:class="['flex w-full items-center px-3 py-1.5 text-left text-xs transition hover:bg-[#f0f4f9]', activeFormats.h2 ? 'font-bold text-[#183669] bg-[#f0f4f9]' : 'text-[#435b76]']"
						>
							Heading 2
						</button>
						<button
							type="button"
							@mousedown.prevent
							@click="setBlockFormat('h3')"
							:class="['flex w-full items-center px-3 py-1.5 text-left text-xs transition hover:bg-[#f0f4f9]', activeFormats.h3 ? 'font-bold text-[#183669] bg-[#f0f4f9]' : 'text-[#435b76]']"
						>
							Heading 3
						</button>
						<button
							type="button"
							@mousedown.prevent
							@click="setBlockFormat('blockquote')"
							:class="['flex w-full items-center px-3 py-1.5 text-left text-xs transition hover:bg-[#f0f4f9]', activeFormats.blockquote ? 'font-bold text-[#183669] bg-[#f0f4f9]' : 'text-[#435b76]']"
						>
							Kutipan (Quote)
						</button>
					</div>
				</div>

				<div class="h-4 w-px bg-[#d6e0ee]"></div>

				<!-- Basic Formats (B, I, U) -->
				<div class="flex items-center gap-0.5">
					<button
						type="button"
						@mousedown.prevent
						@click="exec('bold')"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] text-xs font-bold transition', activeFormats.bold ? 'bg-[#183669] text-white' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Tebal (Ctrl+B)"
					>
						B
					</button>
					<button
						type="button"
						@mousedown.prevent
						@click="exec('italic')"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] text-xs italic font-serif transition', activeFormats.italic ? 'bg-[#183669] text-white' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Miring (Ctrl+I)"
					>
						I
					</button>
					<button
						type="button"
						@mousedown.prevent
						@click="exec('underline')"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] text-xs underline font-semibold transition', activeFormats.underline ? 'bg-[#183669] text-white' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Garis Bawah (Ctrl+U)"
					>
						U
					</button>
				</div>

				<div class="h-4 w-px bg-[#d6e0ee]"></div>

				<!-- Lists (Bullet & Numbered) -->
				<div class="flex items-center gap-0.5">
					<button
						type="button"
						@mousedown.prevent
						@click="exec('insertUnorderedList')"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] transition', activeFormats.insertUnorderedList ? 'bg-[#183669] text-white' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Bullet List"
					>
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
						</svg>
					</button>
					<button
						type="button"
						@mousedown.prevent
						@click="exec('insertOrderedList')"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] transition', activeFormats.insertOrderedList ? 'bg-[#183669] text-white' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Numbered List"
					>
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M4.5 4.5v4.5m0 0H3m1.5 0h1.5M3 13.5h3v2.25H3m0 0h3m-3 0v2.25H6" />
						</svg>
					</button>
				</div>

				<div class="h-4 w-px bg-[#d6e0ee]"></div>

				<!-- Link & Unlink -->
				<div class="flex items-center gap-0.5">
					<button
						type="button"
						@click="openLinkModal"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] transition', activeFormats.isLink ? 'bg-[#183669] text-white' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Sisipkan Link"
					>
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
						</svg>
					</button>
					<button
						v-if="activeFormats.isLink"
						type="button"
						@mousedown.prevent
						@click="removeLink"
						class="flex h-7 w-7 items-center justify-center rounded-[6px] text-red-500 hover:bg-red-50 transition"
						title="Hapus Link"
					>
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<div class="h-4 w-px bg-[#d6e0ee]"></div>

				<!-- More Options Dropdown (...) -->
				<div class="relative">
					<button
						type="button"
						@click.stop="showMoreMenu = !showMoreMenu; showHeadingMenu = false"
						:class="['flex h-7 w-7 items-center justify-center rounded-[6px] transition', showMoreMenu ? 'bg-[#e8eef8] text-[#183669]' : 'hover:bg-[#e8eef8] hover:text-[#183669]']"
						title="Format & Opsi Lainnya"
					>
						<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
						</svg>
					</button>

					<!-- More Options Menu -->
					<div
						v-if="showMoreMenu"
						class="absolute left-0 top-full z-40 mt-1 w-48 rounded-[8px] border border-[#d6e0ee] bg-white p-1.5 shadow-lg ring-1 ring-black/5"
					>
						<!-- Alignment Options in Grid -->
						<div class="mb-1.5 px-2 pt-1">
							<span class="text-[10px] font-semibold text-[#8ca1b9] uppercase tracking-wider">Perataan Teks</span>
							<div class="mt-1 flex items-center justify-between rounded-[6px] bg-[#f8fafc] p-0.5 border border-[#e6edf6]">
								<button
									type="button"
									@mousedown.prevent
									@click="exec('justifyLeft')"
									:class="['flex h-6 flex-1 items-center justify-center rounded-[4px] transition', activeFormats.justifyLeft ? 'bg-[#183669] text-white' : 'text-[#435b76] hover:bg-[#e8eef8]']"
									title="Rata Kiri"
								>
									<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5m-10.5 5.25h16.5" />
									</svg>
								</button>
								<button
									type="button"
									@mousedown.prevent
									@click="exec('justifyCenter')"
									:class="['flex h-6 flex-1 items-center justify-center rounded-[4px] transition', activeFormats.justifyCenter ? 'bg-[#183669] text-white' : 'text-[#435b76] hover:bg-[#e8eef8]']"
									title="Rata Tengah"
								>
									<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M6.75 12h10.5M3.75 17.25h16.5" />
									</svg>
								</button>
								<button
									type="button"
									@mousedown.prevent
									@click="exec('justifyRight')"
									:class="['flex h-6 flex-1 items-center justify-center rounded-[4px] transition', activeFormats.justifyRight ? 'bg-[#183669] text-white' : 'text-[#435b76] hover:bg-[#e8eef8]']"
									title="Rata Kanan"
								>
									<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M9.75 12h10.5m-16.5 5.25h16.5" />
									</svg>
								</button>
								<button
									type="button"
									@mousedown.prevent
									@click="exec('justifyFull')"
									:class="['flex h-6 flex-1 items-center justify-center rounded-[4px] transition', activeFormats.justifyFull ? 'bg-[#183669] text-white' : 'text-[#435b76] hover:bg-[#e8eef8]']"
									title="Rata Kanan Kiri"
								>
									<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
									</svg>
								</button>
							</div>
						</div>

						<div class="h-px bg-[#edf2f7] my-1"></div>

						<!-- Strikethrough -->
						<button
							type="button"
							@mousedown.prevent
							@click="exec('strikeThrough'); showMoreMenu = false"
							class="flex w-full items-center gap-2.5 rounded-[5px] px-2.5 py-1.5 text-xs text-[#435b76] transition hover:bg-[#f0f4f9] hover:text-[#183669]"
						>
							<span class="font-semibold line-through">S</span>
							<span>Coretan (Strikethrough)</span>
						</button>

						<!-- Code Block -->
						<button
							type="button"
							@mousedown.prevent
							@click="insertCodeBlock"
							class="flex w-full items-center gap-2.5 rounded-[5px] px-2.5 py-1.5 text-xs text-[#435b76] transition hover:bg-[#f0f4f9] hover:text-[#183669]"
						>
							<span class="font-mono text-[11px] font-bold">&lt;/&gt;</span>
							<span>Kode (Code Snippet)</span>
						</button>

						<!-- Divider / Garis -->
						<button
							type="button"
							@mousedown.prevent
							@click="insertHorizontalRule"
							class="flex w-full items-center gap-2.5 rounded-[5px] px-2.5 py-1.5 text-xs text-[#435b76] transition hover:bg-[#f0f4f9] hover:text-[#183669]"
						>
							<span class="font-bold">—</span>
							<span>Garis Pemisah (Divider)</span>
						</button>

						<div class="h-px bg-[#edf2f7] my-1"></div>

						<!-- Clear Formatting -->
						<button
							type="button"
							@mousedown.prevent
							@click="exec('removeFormat'); showMoreMenu = false"
							class="flex w-full items-center gap-2.5 rounded-[5px] px-2.5 py-1.5 text-xs text-red-600 transition hover:bg-red-50"
						>
							<span class="font-semibold text-[11px]">Tx</span>
							<span>Hapus Format Teks</span>
						</button>
					</div>
				</div>

				<!-- Right Group: Undo & Redo -->
				<div class="flex items-center gap-0.5 ml-auto">
					<button
						type="button"
						@mousedown.prevent
						@click="exec('undo')"
						class="flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:bg-[#e8eef8] hover:text-[#183669]"
						title="Undo (Ctrl+Z)"
					>
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
						</svg>
					</button>
					<button
						type="button"
						@mousedown.prevent
						@click="exec('redo')"
						class="flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:bg-[#e8eef8] hover:text-[#183669]"
						title="Redo (Ctrl+Y)"
					>
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3" />
						</svg>
					</button>
				</div>
			</div>
		</div>

		<!-- Contenteditable Text Area with Controlled Height -->
		<div class="relative w-full">
			<div
				ref="editorRef"
				contenteditable="true"
				@input="handleInput"
				@keyup="updateActiveFormats"
				@mouseup="updateActiveFormats"
				@focus="updateActiveFormats"
				:style="{ height: editorHeight, minHeight }"
				class="editor-content w-full overflow-y-auto p-3.5 pb-7 font-inter text-[14px] leading-relaxed text-[#1e3456] focus:outline-none max-h-[500px] rounded-b-[9px]"
			></div>

			<!-- Floating Placeholder -->
			<div
				v-if="showPlaceholder"
				class="pointer-events-none absolute left-3.5 top-3.5 font-inter text-[14px] text-[#a6b7cb] select-none"
			>
				{{ placeholder }}
			</div>

			<!-- Bottom Full-Width Resize Handle -->
			<div
				@mousedown="startResize"
				@touchstart.passive="startResize"
				class="absolute bottom-0 left-0 flex h-3.5 w-full cursor-ns-resize items-center justify-center bg-[#fafcff] hover:bg-[#e8eef8] border-t border-[#d6e0ee] transition select-none rounded-b-[8px]"
				style="touch-action: none;"
				title="Tarik ke bawah untuk memanjangkan kotak deskripsi"
			>
				<div class="h-1 w-12 rounded-full bg-[#cbd5e1]"></div>
			</div>
		</div>

		<!-- Link Modal / Popover -->
		<div
			v-if="showLinkModal"
			class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/25 backdrop-blur-[1px] p-3 rounded-[10px]"
		>
			<div class="w-full max-w-sm rounded-[10px] border border-[#d6e0ee] bg-white p-4 shadow-xl">
				<h4 class="font-poppins text-[13px] font-bold text-[#183669] mb-2.5">Sisipkan Link</h4>
				<div class="space-y-2.5">
					<div>
						<label class="block text-[11px] font-medium text-[#435b76]">Teks (Opsional)</label>
						<input
							v-model="linkText"
							type="text"
							placeholder="Teks yang ditampilkan..."
							class="mt-1 h-8 w-full rounded-[6px] border border-[#d6e0ee] px-2.5 text-xs text-[#1e3456] focus:border-[#183669] focus:outline-none"
						/>
					</div>
					<div>
						<label class="block text-[11px] font-medium text-[#435b76]">URL Tautan<span class="text-red-500">*</span></label>
						<input
							v-model="linkUrl"
							type="text"
							placeholder="https://contoh.com atau file..."
							@keyup.enter="applyLink"
							class="mt-1 h-8 w-full rounded-[6px] border border-[#d6e0ee] px-2.5 text-xs text-[#1e3456] focus:border-[#183669] focus:outline-none"
							autofocus
						/>
					</div>
				</div>
				<div class="mt-3.5 flex items-center justify-end gap-2">
					<button
						type="button"
						@click="showLinkModal = false"
						class="rounded-[6px] border border-[#d6e0ee] px-3 py-1 text-xs font-semibold text-[#5a718d] hover:bg-slate-50 transition"
					>
						Batal
					</button>
					<button
						type="button"
						@click="applyLink"
						class="rounded-[6px] bg-[#183669] px-3.5 py-1 text-xs font-semibold text-white hover:bg-[#122b54] transition"
					>
						Terapkan
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<style>
/* Rich Text Editor Content Styling (Overrides Tailwind CSS Reset) */
.editor-content {
	word-break: break-word;
	white-space: pre-wrap;
}

.editor-content h1 {
	display: block !important;
	font-size: 1.55rem !important; /* ~24-25px */
	font-weight: 700 !important;
	color: #183669 !important;
	margin-top: 0.5rem !important;
	margin-bottom: 0.35rem !important;
	line-height: 1.25 !important;
}

.editor-content h2 {
	display: block !important;
	font-size: 1.3rem !important; /* ~20-21px */
	font-weight: 700 !important;
	color: #183669 !important;
	margin-top: 0.4rem !important;
	margin-bottom: 0.25rem !important;
	line-height: 1.3 !important;
}

.editor-content h3 {
	display: block !important;
	font-size: 1.125rem !important; /* ~18px */
	font-weight: 600 !important;
	color: #183669 !important;
	margin-top: 0.35rem !important;
	margin-bottom: 0.2rem !important;
	line-height: 1.35 !important;
}

.editor-content p,
.editor-content div {
	margin: 0.2rem 0 !important;
	line-height: 1.6 !important;
}

.editor-content b,
.editor-content strong {
	font-weight: 700 !important;
	color: #0f2347 !important;
}

.editor-content i,
.editor-content em {
	font-style: italic !important;
}

.editor-content u {
	text-decoration: underline !important;
}

.editor-content s,
.editor-content strike {
	text-decoration: line-through !important;
}

.editor-content ul {
	display: block !important;
	list-style-type: disc !important;
	padding-left: 1.5rem !important;
	margin: 0.35rem 0 !important;
}

.editor-content ol {
	display: block !important;
	list-style-type: decimal !important;
	padding-left: 1.5rem !important;
	margin: 0.35rem 0 !important;
}

.editor-content li {
	display: list-item !important;
	margin: 0.15rem 0 !important;
}

.editor-content a {
	color: #2563eb !important;
	text-decoration: underline !important;
	font-weight: 500 !important;
	cursor: pointer !important;
}

.editor-content a:hover {
	color: #1d4ed8 !important;
}

.editor-content blockquote {
	display: block !important;
	border-left: 3.5px solid #183669 !important;
	background: #f1f5f9 !important;
	padding: 0.4rem 0.85rem !important;
	margin: 0.45rem 0 !important;
	color: #334155 !important;
	font-style: italic !important;
	border-radius: 0 6px 6px 0 !important;
}

.editor-content code {
	background: #e2e8f0 !important;
	color: #b91c1c !important;
	padding: 0.15rem 0.35rem !important;
	border-radius: 4px !important;
	font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
	font-size: 0.875em !important;
}

.editor-content hr {
	border: 0 !important;
	height: 1px !important;
	background: #cbd5e1 !important;
	margin: 0.75rem 0 !important;
}
</style>


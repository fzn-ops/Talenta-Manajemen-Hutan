<script setup>
const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Setujui',
  },
});

const emit = defineEmits(['click']);

const handleClick = (e) => {
	if (props.disabled) return;
	if (e && e.currentTarget && typeof e.currentTarget.blur === 'function') {
		e.currentTarget.blur();
	}
	emit('click');
};
</script>

<template>
  <button 
    type="button"
    :disabled="disabled"
    :title="title" 
    class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 accept-table-btn"
    :class="disabled ? 'opacity-35 cursor-not-allowed filter grayscale-[40%]' : 'cursor-pointer active:brightness-60'"
    style="outline: none !important; box-shadow: none !important;"
    @click="handleClick($event)"
  >
    <img src="/assets/icons/button-setuju.svg" alt="Setuju" class="h-7 w-7 rounded-[5px]" />
  </button>
</template>

<style scoped>
@media (hover: hover) and (pointer: fine) {
  .accept-table-btn:not(:disabled):hover {
    filter: brightness(0.75);
  }
}
</style>
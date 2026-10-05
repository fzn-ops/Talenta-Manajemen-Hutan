<script setup>
const emit = defineEmits(['click']);

const handleClick = (e) => {
	if (e && e.currentTarget && typeof e.currentTarget.blur === 'function') {
		e.currentTarget.blur();
	}
	emit('click');
};
</script>

<template>
  <button 
    type="button"
    title="Tolak" 
    class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 active:brightness-60 outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 cursor-pointer reject-table-btn"
    style="outline: none !important; box-shadow: none !important;"
    @click="handleClick($event)"
  >
    <img src="/assets/icons/button-tolak.svg" alt="Tolak" class="h-7 w-7 rounded-[5px]" />
  </button>
</template>

<style scoped>
@media (hover: hover) and (pointer: fine) {
  .reject-table-btn:hover {
    filter: brightness(0.75);
  }
}
</style>
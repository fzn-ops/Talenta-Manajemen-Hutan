<script setup>
defineProps({
  rows: {
    type: Number,
    default: 5
  },
  columns: {
    type: Number,
    default: 6
  }
});
</script>

<template>
  <tbody class="animate-pulse select-none [&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee]">
    <tr v-for="r in rows" :key="'table-skel-' + r" class="h-[52px]">
      <!-- Kolom No (selalu kolom pertama) -->
      <td class="px-3 py-2.5 text-center">
        <div class="h-4 w-6 mx-auto rounded bg-slate-200"></div>
      </td>

      <!-- Kolom Tengah (adaptif menyesuaikan total columns) -->
      <td v-for="c in (columns - 2)" :key="'skel-cell-' + c" class="px-3 py-2.5">
        <div v-if="c === 1" class="h-4 w-3/4 rounded bg-slate-200"></div>
        <div v-else-if="c === 2" class="flex items-center gap-1.5">
          <div class="h-5 w-16 rounded-full bg-slate-200"></div>
          <div class="h-5 w-14 rounded-full bg-slate-200"></div>
        </div>
        <div v-else class="h-4 w-20 mx-auto rounded-md bg-slate-200"></div>
      </td>

      <!-- Kolom Aksi (selalu kolom terakhir) -->
      <td class="px-3 py-2.5 text-center">
        <div class="flex items-center justify-center gap-2">
          <div class="h-7 w-7 rounded-[7px] bg-slate-200"></div>
          <div class="h-7 w-7 rounded-[7px] bg-slate-200"></div>
        </div>
      </td>
    </tr>
  </tbody>
</template>

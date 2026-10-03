<template>
  <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
    <li v-for="theme in themes" :key="theme.id">
      <ThemeCard :key="theme.id" :theme="theme" />
    </li>
  </ul>
</template>

<script setup lang="ts">
import ThemeCard from '@/components/profile-preferences/theme/ThemeCard.vue'

defineProps<{ themes: Theme[] }>()
</script>

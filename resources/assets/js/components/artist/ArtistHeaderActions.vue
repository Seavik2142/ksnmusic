<template>
  <div class="flex items-center gap-1.5" data-testid="artist-header-actions">
    <FavoriteButton
      :favorite="artist.favorite"
      size="md"
      class="action-btn"
      :class="{ 'text-k-love': artist.favorite }"
      @toggle="emit('toggle-favorite')"
    />

    <StarRating :rateable="artist" class="px-2" />

    <button
      v-if="allowEdit"
      v-koel-tooltip
      type="button"
      class="action-btn"
      title="Edit artist photo & info"
      @click="emit('edit')"
    >
      <Icon :icon="faPen" />
      <span class="sr-only">Edit artist</span>
    </button>

    <button v-koel-tooltip type="button" class="action-btn" title="More actions" @click="emit('more', $event)">
      <Icon :icon="faEllipsis" fixed-width />
      <span class="sr-only">More Actions</span>
    </button>
  </div>
</template>

<script lang="ts" setup>
import { faEllipsis, faPen } from '@fortawesome/free-solid-svg-icons'

import FavoriteButton from '@/components/ui/FavoriteButton.vue'
import StarRating from '@/components/ui/StarRating.vue'

defineProps<{ artist: Artist; allowEdit: boolean }>()

const emit = defineEmits<{
  (e: 'toggle-favorite' | 'edit'): void
  (e: 'more', event: MouseEvent): void
}>()
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.action-btn {
  @apply size-[34px] flex items-center justify-center rounded-full text-k-fg-70 hover:text-k-fg hover:bg-k-fg-10
    active:scale-95 transition-[color,background-color,transform] duration-150;
}
</style>

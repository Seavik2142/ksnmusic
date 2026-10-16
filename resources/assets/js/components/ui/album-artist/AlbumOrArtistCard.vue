<template>
  <div
    class="card-wrapper animate-rise rounded-lg max-w-full md:max-w-[256px]"
    :class="{ compact: layout === 'compact' }"
  >
    <article
      :class="layout"
      class="relative group flex h-full p-3 rounded-[inherit] flex-col gap-3"
      data-testid="artist-album-card"
      :draggable="!isMobile.any"
      tabindex="0"
      @dblclick="onDblClick"
      @dragstart="onDragStart"
      @contextmenu.prevent="onContextMenu"
    >
      <slot name="thumbnail">
        <Thumbnail v-if="hasThumbnail(entity)" :entity />
      </slot>

      <footer class="flex flex-1 flex-col gap-1 overflow-hidden px-1">
        <div class="name flex flex-col gap-1 whitespace-nowrap">
          <slot name="name" />
        </div>
        <p class="meta text-[0.9rem] flex gap-1.5 text-k-fg-60">
          <slot name="meta" />
        </p>
      </footer>

      <slot />
    </article>
  </div>
</template>

<script lang="ts" setup>
import isMobile from 'ismobilejs'
import { toRefs } from 'vue'

import Thumbnail from '@/components/ui/album-artist/AlbumOrArtistThumbnail.vue'

const props = withDefaults(defineProps<{ layout?: CardLayout; entity: Artist | Album | Podcast | RadioStation }>(), {
  layout: 'full',
})

const emit = defineEmits<{
  (e: 'dblclick'): void
  (e: 'dragstart', event: DragEvent): void
  (e: 'contextmenu', event: MouseEvent): void
}>()

const hasThumbnail = (entity: Artist | Album | Podcast | RadioStation): entity is Artist | Album =>
  entity.type !== 'radio-stations' && entity.type !== 'podcasts'

const { layout } = toRefs(props)

const onDblClick = () => emit('dblclick')
const onDragStart = (e: DragEvent) => emit('dragstart', e)
const onContextMenu = (e: MouseEvent) => emit('contextmenu', e)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
.card-wrapper {
  &:nth-child(2) {
    --stagger: 1;
  }
  &:nth-child(3) {
    --stagger: 2;
  }
  &:nth-child(4) {
    --stagger: 3;
  }
  &:nth-child(5) {
    --stagger: 4;
  }
  &:nth-child(6) {
    --stagger: 5;
  }
  &:nth-child(7) {
    --stagger: 6;
  }
  &:nth-child(n + 8) {
    --stagger: 7;
  }
}

article {
  :deep(.card-thumbnail) {
    transition: transform 0.35s var(--ease-out);
  }

  :deep(.card-thumbnail .art) {
    transition: box-shadow 0.35s var(--ease-out);
  }

  &:is(:hover, :focus-within) :deep(.card-thumbnail) {
    transform: translateY(-4px);
  }

  &:is(:hover, :focus-within) :deep(.card-thumbnail .art) {
    box-shadow: 0 18px 36px rgb(0 0 0 / 0.5);
  }

  :deep(.card-thumbnail img) {
    transition: transform 0.7s var(--ease-out);
  }

  &:hover :deep(.card-thumbnail img) {
    transform: scale(1.06);
  }

  @apply bg-transparent hover:bg-k-fg-5 focus-within:bg-k-fg-5 transition-colors duration-200;

  .meta :deep(a) {
    @apply text-k-fg-60 hover:text-k-fg hover:underline underline-offset-2;
  }

  .name {
    @apply text-k-fg-60;

    &:deep(a) {
      @apply overflow-hidden text-ellipsis text-k-fg-60;

      &:is(:hover, :focus-visible) {
        @apply text-k-fg underline underline-offset-2;
      }
    }

    > :first-child :deep(a),
    > :deep(a:first-child) {
      @apply text-k-fg font-semibold;
    }
  }

  &:focus-visible {
    @apply outline-none ring-1 ring-k-fg-30;
  }

  &.compact {
    @apply flex-row gap-4 p-2 rounded-md items-center bg-k-fg-5 hover:bg-k-fg-10;

    :deep(.thumbnail) {
      @apply w-[80px] rounded-md;
    }
  }
}

.compact {
  @apply max-w-full rounded-md;
}
</style>

<template>
  <div
    :class="{ playing: playable?.playback_state === 'Playing' }"
    :draggable="draggable"
    class="song-info px-6 py-0 flex items-center content-start w-[84px] md:w-[420px] gap-5"
    @dragstart="onDragStart"
  >
    <span
      v-koel-tooltip
      :class="playable && 'cursor-pointer'"
      :title="playable ? 'Scroll to currently playing' : undefined"
      class="album-thumb block h-[55%] md:h-3/4 aspect-square rounded-full bg-cover"
      @click="scrollToCurrentInQueue"
    />
    <div v-if="playable" class="meta overflow-hidden hidden md:block">
      <h3 class="title overflow-hidden whitespace-nowrap">
        <MarqueeText :text="playable.title" />
      </h3>
      <a :href="artistOrPodcastUri" class="artist overflow-hidden whitespace-nowrap block text-[0.9rem]">
        <MarqueeText :text="artistOrPodcastName" />
      </a>
    </div>
  </div>
</template>

<script lang="ts" setup>
import isMobile from 'ismobilejs'
import type { Ref } from 'vue'
import { computed, ref } from 'vue'
import { getPlayableProp, requireInjection, use } from '@/utils/helpers'
import { isSong } from '@/utils/typeGuards'
import { CurrentStreamableKey } from '@/config/symbols'
import { useDraggable } from '@/composables/useDragAndDrop'
import { useRouter } from '@/composables/useRouter'
import { useBranding } from '@/composables/useBranding'
import { cache } from '@/services/cache'

import MarqueeText from '@/components/ui/MarqueeText.vue'

const { startDragging } = useDraggable('playables')
const { go, url } = useRouter()
const { cover: defaultCover } = useBranding()

const playable = requireInjection<Ref<Playable | undefined>>(CurrentStreamableKey, ref())

const cover = computed(() =>
  playable.value ? getPlayableProp(playable.value, 'album_cover', 'episode_image') : defaultCover,
)

const artistOrPodcastUri = computed(() => {
  if (!playable.value) {
    return ''
  }

  return isSong(playable.value)
    ? url('artists.show', { id: playable.value?.artist_id })
    : url('podcasts.show', { id: playable.value?.podcast_id })
})

const artistOrPodcastName = computed(() =>
  playable.value ? getPlayableProp(playable.value, 'artist_name', 'podcast_title') : '',
)

const coverBackgroundImage = computed(() => `url(${cover.value ?? defaultCover})`)
const draggable = computed(() => Boolean(playable.value) && !isMobile.any)

const onDragStart = (event: DragEvent) => use(playable.value, p => startDragging(event, [p]))

const scrollToCurrentInQueue = () => {
  if (!playable.value) {
    return
  }

  cache.set('scroll-to-current-in-queue', true)
  go(url('queue'))
}
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
.song-info {
  :fullscreen & {
    @apply pl-0;
  }

  .album-thumb {
    position: relative;
    background-image: v-bind(coverBackgroundImage);
    transition:
      transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
      box-shadow 0.3s ease;

    &:hover {
      transform: scale(1.08);
    }

    /* Vinyl Grooves */
    &::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 9999px;
      background: repeating-radial-gradient(
        circle at center,
        transparent,
        transparent 5px,
        rgba(255, 255, 255, 0.05) 6px,
        transparent 7px
      );
      pointer-events: none;
      box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.7);
    }

    /* Vinyl Center Spindle Hole */
    &::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 14%;
      height: 14%;
      border-radius: 9999px;
      background-color: #111111;
      border: 2px solid rgba(255, 255, 255, 0.35);
      box-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
      z-index: 2;
      pointer-events: none;
    }

    :fullscreen & {
      @apply h-20;
    }
  }

  .meta {
    .title {
      transition: color 0.2s ease;
    }

    .artist {
      transition: color 0.2s ease;
      &:hover {
        @apply text-k-highlight;
      }
    }

    :fullscreen & {
      @apply -mt-72 origin-bottom-left absolute overflow-hidden;

      .title {
        @apply text-5xl mb-[0.4rem] font-bold;
      }

      .artist {
        @apply text-3xl w-fit;
      }
    }
  }

  &.playing .album-thumb {
    @apply motion-reduce:animate-none animate-vinyl-spin;
    box-shadow:
      0 0 0 2px color-mix(in srgb, var(--color-highlight), transparent 60%),
      0 0 20px -2px color-mix(in srgb, var(--color-highlight), transparent 30%),
      0 4px 12px rgba(0, 0, 0, 0.5);
  }
}
</style>

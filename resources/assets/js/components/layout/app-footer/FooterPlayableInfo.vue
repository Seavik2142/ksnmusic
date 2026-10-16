<template>
  <div
    :class="{ playing: isPlaying }"
    :draggable="draggable"
    class="song-info px-4 py-0 flex items-center content-start w-[84px] md:w-[420px] gap-3.5"
    @dragstart="onDragStart"
  >
    <span
      :key="playable?.id"
      v-koel-tooltip
      :class="playable && 'cursor-pointer'"
      :title="playable ? 'Scroll to currently playing' : undefined"
      class="album-thumb animate-pop-in relative block h-[55%] md:h-14 aspect-square rounded-md bg-cover bg-center shrink-0"
      @click="scrollToCurrentInQueue"
    />
    <div v-if="playable" :key="playable.id" class="meta animate-rise overflow-hidden hidden md:flex flex-col gap-0.5">
      <h3 class="title flex items-center gap-2 overflow-hidden whitespace-nowrap font-medium text-k-fg">
        <MarqueeText :text="playable.title" />
        <AudioEqualizer v-if="isPlaying" class="shrink-0" size="sm" :bar-count="3" />
      </h3>
      <a :href="artistOrPodcastUri" class="artist overflow-hidden whitespace-nowrap block text-[0.9rem] text-k-fg-60">
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
import AudioEqualizer from '@/components/ui/AudioEqualizer.vue'

const { startDragging } = useDraggable('playables')
const { go, url } = useRouter()
const { cover: defaultCover } = useBranding()

const playable = requireInjection<Ref<Playable | undefined>>(CurrentStreamableKey, ref())
const isPlaying = computed(() => playable.value?.playback_state === 'Playing')

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
    background-image: v-bind(coverBackgroundImage);
    box-shadow: var(--shadow-floating);

    :fullscreen & {
      @apply h-20;
    }

    &::after {
      @apply content-[''] absolute inset-0 rounded-[inherit] opacity-0 pointer-events-none;
      box-shadow: 0 0 22px 2px color-mix(in srgb, var(--color-highlight), transparent 45%);
      transition: opacity 0.4s var(--ease-standard);
    }
  }

  &.playing .album-thumb::after {
    animation: now-playing-breathe 3.2s ease-in-out infinite;
  }

  .meta {
    .artist {
      @apply transition-colors duration-150 hover:text-k-fg hover:underline underline-offset-2;
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
}

@keyframes now-playing-breathe {
  0%,
  100% {
    opacity: 0.25;
  }

  50% {
    opacity: 0.85;
  }
}
</style>

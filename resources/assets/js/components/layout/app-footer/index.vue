<template>
  <footer
    ref="root"
    :class="{ 'is-playing': isPlaying }"
    class="flex flex-col relative z-20 backdrop-blur-2xl bg-k-fg-3/85 border border-k-fg-5 m-4 rounded-xl overflow-hidden h-k-footer-height pt-(--progress-bar-height) transition-all duration-300"
    @mousemove="showControls"
    @contextmenu.prevent="requestContextMenu"
  >
    <div v-if="isPlaying" class="ambient-aura pointer-events-none absolute inset-x-0 top-0 h-[2px] z-30" />

    <AudioPlayer v-show="currentStreamable" :class="isRadio && 'pointer-events-none'" />

    <div class="fullscreen-backdrop hidden" />

    <div class="wrapper relative flex flex-1">
      <RadioStationInfo v-if="isRadio" />
      <SongInfo v-else />
      <PlaybackControls />
      <ExtraControls />
    </div>

    <Transition>
      <UpNext v-show="showingUpNext" :playable="nextPlayable" class="up-next" />
    </Transition>
  </footer>
</template>

<script lang="ts" setup>
import { useThrottleFn } from '@vueuse/core'
import { computed, nextTick, ref, watch } from 'vue'
import { useFullscreen } from '@vueuse/core'
import { eventBus } from '@/utils/eventBus'
import { isEpisode, isRadioStation, isSong } from '@/utils/typeGuards'
import { isAudioContextSupported } from '@/utils/supports'
import { defineAsyncComponent, requireInjection } from '@/utils/helpers'
import { CurrentStreamableKey } from '@/config/symbols'
import { artistStore } from '@/stores/artistStore'
import { preferenceStore } from '@/stores/preferenceStore'
import { audioService } from '@/services/audioService'
import { playback } from '@/services/playbackManager'
import { useContextMenu } from '@/composables/useContextMenu'

import AudioPlayer from '@/components/layout/app-footer/AudioPlayer.vue'
import ExtraControls from '@/components/layout/app-footer/FooterExtraControls.vue'
import PlaybackControls from '@/components/layout/app-footer/FooterPlaybackControls.vue'

const SongInfo = defineAsyncComponent(() => import('@/components/layout/app-footer/FooterPlayableInfo.vue'))
const RadioStationInfo = defineAsyncComponent(() => import('@/components/layout/app-footer/FooterRadioStationInfo.vue'))
const UpNext = defineAsyncComponent(() => import('@/components/layout/app-footer/UpNext.vue'))
const PlayableContextMenu = defineAsyncComponent(() => import('@/components/playable/PlayableContextMenu.vue'))
const RadioStationContextMenu = defineAsyncComponent(() => import('@/components/radio/RadioStationContextMenu.vue'))

const currentStreamable = requireInjection(CurrentStreamableKey, ref())
let hideControlsTimeout: number

const root = ref<HTMLElement>()
const artist = ref<Artist>()
const nextPlayable = ref<Playable | null>(null)

const { isFullscreen, toggle: toggleFullscreen } = useFullscreen(root)
const { openContextMenu } = useContextMenu()

const showingUpNext = computed(() => nextPlayable.value && isFullscreen.value)
const isRadio = computed(() => currentStreamable.value && isRadioStation(currentStreamable.value))
const isPlaying = computed(() => currentStreamable.value?.playback_state === 'Playing')

const requestContextMenu = (event: MouseEvent) => {
  if (document.fullscreenElement || !currentStreamable.value) {
    return
  }

  if (isRadio.value) {
    openContextMenu<'RADIO_STATION'>(RadioStationContextMenu, event, {
      station: currentStreamable.value as RadioStation,
    })
  } else {
    openContextMenu<'PLAYABLES'>(PlayableContextMenu, event, {
      playables: [currentStreamable.value as Playable],
    })
  }
}

watch(currentStreamable, async streamable => {
  if (!streamable) {
    return
  }

  if (isSong(streamable)) {
    artist.value = await artistStore.resolve(streamable.artist_id)
  }
})

const appBackgroundImage = computed(() => {
  if (!currentStreamable.value) {
    return 'none'
  }

  let src: string | null = null

  if (isSong(currentStreamable.value)) {
    src = artist.value?.image ?? currentStreamable.value.album_cover
  } else if (isEpisode(currentStreamable.value)) {
    src = currentStreamable.value.episode_image
  } else if (isRadio.value) {
    src = (currentStreamable.value as RadioStation).logo
  }

  return src ? `url(${src})` : 'none'
})

const initPlaybackRelatedServices = async () => {
  const audioElement = document.querySelector<HTMLMediaElement>('#audio-player')

  if (!audioElement) {
    await nextTick()
    await initPlaybackRelatedServices()
    return
  }

  // Defaults to the queue playback over radio playback.
  const playbackService = playback()

  // If audio context is supported, initialize the audio service which handles audio processing (equalizer, etc.)
  if (isAudioContextSupported) {
    audioService.init(playbackService.media)
  }
}

watch(
  preferenceStore.initialized,
  async initialized => {
    if (!initialized) {
      return
    }

    await initPlaybackRelatedServices()
  },
  { immediate: true },
)

const setupControlHidingTimer = () => {
  hideControlsTimeout = window.setTimeout(() => root.value?.classList.add('hide-controls'), 5000)
}

const showControls = useThrottleFn(() => {
  if (!document.fullscreenElement) {
    return
  }

  root.value?.classList.remove('hide-controls')
  window.clearTimeout(hideControlsTimeout)
  setupControlHidingTimer()
}, 100)

watch(isFullscreen, fullscreen => {
  if (fullscreen) {
    setupControlHidingTimer()
    root.value?.classList.remove('hide-controls')
  } else {
    window.clearTimeout(hideControlsTimeout)
  }
})

eventBus.on('FULLSCREEN_TOGGLE', () => toggleFullscreen()).on('UP_NEXT', next => (nextPlayable.value = next))
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
.v-enter-active,
.v-leave-active {
  transition: opacity 2s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

footer {
  box-shadow:
    0 4px 30px rgba(0, 0, 0, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);

  &.is-playing {
    border-color: color-mix(in srgb, var(--color-highlight), transparent 75%);
    box-shadow:
      0 0 35px -5px color-mix(in srgb, var(--color-highlight), transparent 75%),
      0 8px 32px 0 rgba(0, 0, 0, 0.4),
      inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }

  .ambient-aura {
    background: linear-gradient(
      90deg,
      transparent 0%,
      var(--color-highlight) 25%,
      #ff758c 50%,
      var(--color-highlight) 75%,
      transparent 100%
    );
    background-size: 200% 100%;
    animation: ambient-flow 4s ease-in-out infinite;
    box-shadow: 0 0 14px 2px var(--color-highlight);
  }

  .fullscreen-backdrop {
    background-color: #1d1d1d;
    background-image: v-bind(appBackgroundImage);
  }

  &:fullscreen {
    padding: calc(100vh - 9rem) 5vw 0;
    @apply bg-none;

    &.hide-controls :not(.fullscreen-backdrop, .up-next, .up-next *) {
      transition: opacity 2s ease-in-out !important; /* overriding all children's custom transition, if any */
      @apply opacity-0;
    }

    &.hide-controls::after {
      transition: opacity 2s ease-in-out !important;
      @apply opacity-0;
    }

    .wrapper {
      @apply z-[3];
    }

    &::before {
      @apply bg-black bg-repeat absolute top-0 left-0 opacity-50 z-1 pointer-events-none -m-[20rem];
      content: '';
      background-image:
        linear-gradient(135deg, #111 25%, transparent 25%), linear-gradient(225deg, #111 25%, transparent 25%),
        linear-gradient(45deg, #111 25%, transparent 25%), linear-gradient(315deg, #111 25%, rgba(255, 255, 255, 0) 25%);
      background-position:
        6px 0,
        6px 0,
        0 0,
        0 0;
      background-size: 6px 6px;
      width: calc(100% + 40rem);
      height: calc(100% + 40rem);
      transform: rotate(10deg);
    }

    &::after {
      background-image: linear-gradient(0deg, var(--color-bg) 0%, rgba(255, 255, 255, 0) 30vh);
      content: '';
      @apply absolute w-full h-full top-0 left-0 z-1 pointer-events-none;
    }

    .fullscreen-backdrop {
      @apply saturate-[0.2] block absolute top-0 left-0 w-full h-full z-0 bg-cover bg-no-repeat bg-top;
    }
  }
}

@keyframes ambient-flow {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>

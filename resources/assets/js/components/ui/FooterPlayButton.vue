<template>
  <FooterButton
    :title
    :class="[
      'w-12! rounded-full aspect-square transition-all duration-300 text-2xl! has-[.icon-play]:indent-[0.23rem]',
      playing
        ? 'playing text-white bg-k-highlight border-2 border-k-highlight shadow-[0_0_20px_color-mix(in_srgb,var(--color-highlight),transparent_25%)] hover:scale-115 active:scale-95'
        : 'border-2 border-solid border-k-fg-70 hover:border-k-highlight hover:text-k-highlight hover:scale-115 hover:shadow-[0_0_15px_color-mix(in_srgb,var(--color-highlight),transparent_50%)] active:scale-95',
    ]"
    @click.prevent="toggle"
  >
    <Icon v-if="playing" :icon="faPause" />
    <Icon v-else :icon="faPlay" class="icon-play" />
  </FooterButton>
</template>

<script lang="ts" setup>
import { faPause, faPlay } from '@fortawesome/free-solid-svg-icons'
import { computed, ref } from 'vue'
import { commonStore } from '@/stores/commonStore'
import { queueStore } from '@/stores/queueStore'
import { recentlyPlayedStore } from '@/stores/recentlyPlayedStore'
import { playableStore } from '@/stores/playableStore'
import { useRouter } from '@/composables/useRouter'
import { requireInjection } from '@/utils/helpers'
import { CurrentStreamableKey } from '@/config/symbols'
import { playback } from '@/services/playbackManager'

import FooterButton from '@/components/layout/app-footer/FooterButton.vue'

const { getCurrentScreen, getRouteParam, go, url } = useRouter()
const streamable = requireInjection(CurrentStreamableKey, ref())

const libraryEmpty = computed(() => commonStore.state.song_count === 0)
const playing = computed(() => streamable.value?.playback_state === 'Playing')
const isRadio = computed(() => streamable.value?.type === 'radio-stations')

const title = computed(() => {
  if (isRadio.value) {
    return streamable.value?.playback_state === 'Playing' ? 'Stop streaming' : 'Start streaming'
  }

  return playing.value ? 'Pause' : 'Play or resume'
})

const initiatePlayback = async () => {
  if (libraryEmpty.value) {
    return
  }

  let playables: Playable[]

  switch (getCurrentScreen()) {
    case 'Album':
      playables = await playableStore.fetchSongsForAlbum(getRouteParam('id')!)
      break
    case 'Artist':
      playables = await playableStore.fetchSongsForArtist(getRouteParam('id')!)
      break
    case 'Playlist':
      playables = await playableStore.fetchForPlaylist(getRouteParam('id')!)
      break
    case 'Favorites':
      playables = await playableStore.fetchFavorites()
      break
    case 'RecentlyPlayed':
      playables = await recentlyPlayedStore.fetch()
      break
    case 'Genre':
      playables = await playableStore.fetchSongsByGenre(getRouteParam('id')!)
      break
    default:
      playables = await queueStore.fetchRandom()
      break
  }

  await playback().queueAndPlay(playables)
  go(url('queue'))
}

const toggle = async () => {
  if (!streamable.value) {
    await initiatePlayback()
    return
  }

  if (isRadio.value) {
    await playback('radio').toggle()
    return
  }

  await playback('queue').toggle()
}
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

@keyframes play-glow-pulse {
  0%,
  100% {
    box-shadow:
      0 0 16px 2px color-mix(in srgb, var(--color-highlight), transparent 30%),
      inset 0 0 10px rgba(255, 255, 255, 0.2);
  }
  50% {
    box-shadow:
      0 0 28px 6px color-mix(in srgb, var(--color-highlight), transparent 10%),
      inset 0 0 14px rgba(255, 255, 255, 0.4);
  }
}

.playing {
  animation: play-glow-pulse 2.4s ease-in-out infinite;
}
</style>

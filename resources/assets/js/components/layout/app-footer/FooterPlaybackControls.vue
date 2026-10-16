<template>
  <div class="playback-controls flex flex-1 flex-col justify-center">
    <div class="flex items-center justify-between md:justify-center gap-5 md:gap-8 px-4 md:px-0">
      <span v-if="showTime" class="time hidden md:block text-right" data-testid="elapsed-time">{{ elapsed }}</span>

      <FavoriteButton
        v-if="streamable"
        :favorite="streamable.favorite"
        class="text-base text-k-fg-60 hover:text-k-fg transition-colors"
        @toggle="toggleFavorite"
      />

      <button v-else type="button" />
      <!-- a placeholder to maintain the asymmetric layout -->

      <FooterBtn
        :class="isRadio && 'pointer-events-none opacity-30 cursor-not-allowed'"
        class="skip-btn text-lg"
        title="Play previous in queue"
        @click.prevent="playPrev"
      >
        <Icon :icon="faStepBackward" />
      </FooterBtn>

      <PlayButton />

      <FooterBtn
        :class="isRadio && 'pointer-events-none opacity-30 cursor-not-allowed'"
        class="skip-btn text-lg"
        title="Play next in queue"
        @click.prevent="playNext"
      >
        <Icon :icon="faStepForward" />
      </FooterBtn>

      <RepeatModeSwitch
        :class="isRadio && 'pointer-events-none opacity-30 cursor-not-allowed'"
        class="text-base text-k-fg-60 hover:text-k-fg transition-colors"
      />

      <span v-if="showTime" class="time hidden md:block" data-testid="duration">{{ duration }}</span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { faStepBackward, faStepForward } from '@fortawesome/free-solid-svg-icons'
import { computed, ref } from 'vue'
import { requireInjection } from '@/utils/helpers'
import { CurrentStreamableKey } from '@/config/symbols'
import { playableStore } from '@/stores/playableStore'
import { playback } from '@/services/playbackManager'
import { radioStationStore } from '@/stores/radioStationStore'
import { usePlaybackTime } from '@/composables/usePlaybackTime'

import RepeatModeSwitch from '@/components/ui/RepeatModeSwitch.vue'
import PlayButton from '@/components/ui/FooterPlayButton.vue'
import FooterBtn from '@/components/layout/app-footer/FooterButton.vue'
import FavoriteButton from '@/components/ui/FavoriteButton.vue'

const streamable = requireInjection(CurrentStreamableKey, ref())

const isRadio = computed(() => streamable.value?.type === 'radio-stations')

const { hasDuration, elapsed, duration } = usePlaybackTime()
const showTime = computed(() => !!streamable.value && !isRadio.value && hasDuration.value)

const playPrev = async () => isRadio.value || (await playback().playPrev())
const playNext = async () => isRadio.value || (await playback().playNext())

const toggleFavorite = () => {
  if (isRadio.value) {
    radioStationStore.toggleFavorite(streamable.value as RadioStation)
  } else {
    playableStore.toggleFavorite(streamable.value as Playable)
  }
}
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
.time {
  @apply w-12 text-xs tabular-nums text-k-fg-50 select-none;
}

.skip-btn {
  @apply text-k-fg-70 hover:text-k-fg active:scale-95 transition-[color,transform] duration-150;
}

:fullscreen .playback-controls {
  @apply scale-125;
}
</style>

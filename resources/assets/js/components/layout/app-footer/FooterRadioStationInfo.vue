<template>
  <div
    :class="{ playing: station?.playback_state === 'Playing' }"
    class="station-info px-6 py-0 flex items-center content-start w-[84px] md:w-[420px] gap-5"
  >
    <span class="logo block h-[55%] md:h-3/4 aspect-square rounded-full bg-cover" />
    <div v-if="station" class="meta overflow-hidden hidden md:block">
      <h3 class="title truncate">{{ station.name }}</h3>
      <p v-if="nowPlaying" class="truncate text-k-text-secondary">
        {{ nowPlaying }}
      </p>
      <p v-else class="truncate">{{ station.description }}</p>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { Ref } from 'vue'
import { computed, ref } from 'vue'
import { requireInjection } from '@/utils/helpers'
import { CurrentStreamableKey } from '@/config/symbols'
import { useBranding } from '@/composables/useBranding'
import { radioStationStore } from '@/stores/radioStationStore'

const station = requireInjection<Ref<RadioStation | undefined>>(CurrentStreamableKey, ref())
const nowPlaying = radioStationStore.nowPlaying

const { cover: defaultCover } = useBranding()

const cover = computed(() => (station.value ? station.value.logo : defaultCover))
const coverBackgroundImage = computed(() => `url(${cover.value ?? defaultCover})`)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
.station-info {
  :fullscreen & {
    @apply pl-0;
  }

  .logo {
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

    :fullscreen & {
      @apply -mt-72 origin-bottom-left absolute overflow-hidden;

      .title {
        @apply text-5xl mb-[0.4rem] font-bold;
      }
    }
  }

  &.playing .logo {
    @apply motion-reduce:animate-none animate-vinyl-spin;
    box-shadow:
      0 0 0 2px color-mix(in srgb, var(--color-highlight), transparent 60%),
      0 0 20px -2px color-mix(in srgb, var(--color-highlight), transparent 30%),
      0 4px 12px rgba(0, 0, 0, 0.5);
  }
}
</style>

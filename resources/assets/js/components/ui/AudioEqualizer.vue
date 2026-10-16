<template>
  <div
    :class="[
      'audio-equalizer flex items-end justify-center gap-[2.5px] select-none pointer-events-none',
      `size-${size}`,
      { animated: playing },
    ]"
    role="presentation"
    aria-hidden="true"
  >
    <span v-for="i in barCount" :key="i" class="eq-bar" />
  </div>
</template>

<script lang="ts" setup>
withDefaults(
  defineProps<{
    playing?: boolean
    barCount?: number
    size?: 'sm' | 'md' | 'lg'
  }>(),
  {
    playing: true,
    barCount: 4,
    size: 'md',
  },
)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.audio-equalizer {
  &.size-sm {
    height: 12px;
    .eq-bar {
      width: 2px;
      border-radius: 1px;
    }
  }

  &.size-md {
    height: 18px;
    .eq-bar {
      width: 3px;
      border-radius: 1.5px;
    }
  }

  &.size-lg {
    height: 24px;
    .eq-bar {
      width: 4px;
      border-radius: 2px;
    }
  }
}

.eq-bar {
  height: 100%;
  background-color: var(--color-highlight);
  transform-origin: bottom;
  transform: scaleY(0.2);
  transition: transform 0.25s ease-out;
}

.audio-equalizer.animated {
  .eq-bar:nth-child(1) {
    animation: eq-bounce-1 1.1s ease-in-out infinite alternate;
  }
  .eq-bar:nth-child(2) {
    animation: eq-bounce-2 0.9s ease-in-out infinite alternate 0.15s;
  }
  .eq-bar:nth-child(3) {
    animation: eq-bounce-3 1.3s ease-in-out infinite alternate 0.05s;
  }
  .eq-bar:nth-child(4) {
    animation: eq-bounce-4 0.85s ease-in-out infinite alternate 0.25s;
  }
  .eq-bar:nth-child(5) {
    animation: eq-bounce-2 1.05s ease-in-out infinite alternate 0.35s;
  }
}

@keyframes eq-bounce-1 {
  0% {
    transform: scaleY(0.25);
  }
  50% {
    transform: scaleY(0.85);
  }
  100% {
    transform: scaleY(0.4);
  }
}

@keyframes eq-bounce-2 {
  0% {
    transform: scaleY(0.6);
  }
  40% {
    transform: scaleY(0.2);
  }
  80% {
    transform: scaleY(1);
  }
  100% {
    transform: scaleY(0.45);
  }
}

@keyframes eq-bounce-3 {
  0% {
    transform: scaleY(0.3);
  }
  35% {
    transform: scaleY(0.95);
  }
  70% {
    transform: scaleY(0.5);
  }
  100% {
    transform: scaleY(0.8);
  }
}

@keyframes eq-bounce-4 {
  0% {
    transform: scaleY(0.7);
  }
  45% {
    transform: scaleY(0.3);
  }
  90% {
    transform: scaleY(0.9);
  }
  100% {
    transform: scaleY(0.25);
  }
}
</style>

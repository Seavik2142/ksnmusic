<template>
  <section class="max-h-full min-h-full w-full flex flex-col transform-gpu overflow-hidden">
    <div
      v-if="backgroundImage"
      class="cover-bg"
      data-testid="cover-bg"
      :style="{ backgroundImage: `url(${backgroundImage})` }"
    />
    <div v-else class="screen-glow" aria-hidden="true" />
    <slot name="header" />

    <main class="relative scroll-mask-y overflow-scroll flex flex-col b-16 md:b-6 p-6 flex-1 place-content-start">
      <HookSlot :context="{ screen: getCurrentScreen() }" name="screen.header" />
      <slot />
    </main>
  </section>
</template>

<script lang="ts" setup>
import { useRouter } from '@/composables/useRouter'

import HookSlot from '@/components/utils/HookSlot.vue'

withDefaults(
  defineProps<{
    backgroundImage?: string
  }>(),
  {
    backgroundImage: undefined,
  },
)

const { getCurrentScreen } = useRouter()
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

section {
  animation: screen-slide-fade 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes screen-slide-fade {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

main {
  -ms-overflow-style: -ms-autohiding-scrollbar;
}

.screen-glow {
  @apply absolute inset-x-0 top-0 h-80 pointer-events-none rounded-t-[inherit];
  background: linear-gradient(
    to bottom,
    color-mix(in srgb, var(--color-highlight), transparent 82%) 0%,
    color-mix(in srgb, var(--color-highlight), transparent 94%) 45%,
    transparent 100%
  );
  animation:
    screen-glow-in 0.9s var(--ease-out) backwards,
    screen-glow-breathe 9s ease-in-out 0.9s infinite alternate;
}

@keyframes screen-glow-in {
  from {
    opacity: 0;
  }
}

@keyframes screen-glow-breathe {
  from {
    opacity: 1;
  }

  to {
    opacity: 0.6;
  }
}

.cover-bg {
  @apply absolute bg-cover bg-center pointer-events-none;
  inset: -32px;
  filter: blur(24px);
  -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, transparent 50%);
  mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, transparent 50%);
}
</style>

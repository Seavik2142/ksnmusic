<template>
  <p
    :class="{ active: isActive }"
    class="relative leading-relaxed origin-left transition-all duration-300 cursor-pointer text-k-fg px-3 py-1.5 rounded-lg select-none"
  >
    <span class="lyric-text block transition-transform duration-300">{{ line.text }}</span>
  </p>
</template>

<script lang="ts" setup>
defineProps<{
  line: LrcLine
  isActive: boolean
}>()
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

p {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  line-height: var(--line-height-lyrics, 1.85);

  &:hover:not(.active) {
    transform: translateX(4px);
    background-color: rgba(255, 255, 255, 0.04);
  }

  &.active {
    @apply scale-[1.04] font-bold text-k-highlight;
    background: linear-gradient(90deg, color-mix(in srgb, var(--color-highlight), transparent 88%), transparent);
    border-left: 3px solid var(--color-highlight);
    text-shadow: 0 0 24px color-mix(in srgb, var(--color-highlight), transparent 45%);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  }
}
</style>

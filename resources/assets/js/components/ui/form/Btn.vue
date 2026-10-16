<template>
  <button
    v-if="tag === 'button'"
    ref="button"
    :data-variant="variant"
    :data-size="size"
    class="text-base font-medium text-white border border-transparent bg-k-primary px-4 py-2 rounded-full cursor-pointer transition-[box-shadow,transform] duration-150 active:scale-[0.98] disabled:active:scale-100"
    type="button"
  >
    <slot>Click me</slot>
  </button>
  <a
    v-else
    ref="button"
    :data-variant="variant"
    :data-size="size"
    class="text-base font-medium text-white border border-transparent bg-k-primary px-4 py-2 rounded-full cursor-pointer transition-[box-shadow,transform] duration-150 active:scale-[0.98] disabled:active:scale-100"
  >
    <slot>Click me</slot>
  </a>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

type Variant = 'success' | 'destructive' | 'highlight' | 'ghost'
type Size = 'small' | 'large'

withDefaults(defineProps<{ tag?: 'button' | 'a'; variant?: Variant; size?: Size }>(), {
  tag: 'button',
})

const button = ref<HTMLButtonElement | HTMLAnchorElement>()

defineExpose({
  button,
})
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

/**
 * Except for the `highlight` variant, button text colors are independent from the theme.
 */
button,
a {
  &:not([disabled]):hover {
    box-shadow: inset 0 0 0 10rem rgba(0, 0, 0, 0.1);
  }

  &:not([disabled]):active {
    box-shadow: inset 0 10px 10px -10px rgba(0, 0, 0, 0.6);
  }

  &[data-size='large'] {
    @apply px-6 py-3;
  }

  &[data-size='small'] {
    @apply text-[0.9rem] px-3 py-1;
  }

  &[data-variant='success'] {
    @apply bg-k-success text-white;
  }

  &[data-variant='ghost'] {
    @apply bg-transparent text-k-fg hover:text-k-fg-80 active:text-k-fg-70;
  }

  &[data-variant='destructive'] {
    @apply bg-k-danger text-white;
  }

  &[data-variant='highlight'] {
    @apply bg-k-highlight text-k-highlight-fg;
  }

  &[rounded] {
    @apply rounded-full;
  }

  &[unrounded] {
    @apply rounded-none;
  }

  &[uppercase] {
    @apply uppercase;
  }

  &[bordered] {
    @apply border-k-fg-20 bg-transparent;

    &[data-variant='success'] {
      @apply border-k-success;
    }

    &[data-variant='destructive'] {
      @apply border-k-danger;
    }

    &[data-variant='highlight'] {
      @apply border-k-highlight text-k-highlight;
    }
  }
}
</style>

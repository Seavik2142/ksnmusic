<template>
  <header
    :class="[layout, disabled ? 'disabled' : '', { 'circular-thumbnail': circularThumbnail }]"
    class="screen-header gap-6 min-h-0 md:min-h-full flex items-end shrink-0 relative content-stretch leading-normal p-6"
  >
    <aside
      v-if="$slots.thumbnail"
      class="thumbnail-wrapper animate-pop-in hidden md:flex items-end overflow-hidden rounded-lg shadow-[0_16px_40px_rgb(0_0_0/0.5)]"
    >
      <slot name="thumbnail" />
    </aside>

    <main class="flex flex-1 gap-5 items-center overflow-hidden">
      <div class="w-full flex-1 overflow-hidden">
        <span
          v-if="$slots.eyebrow"
          class="eyebrow animate-rise hidden md:flex items-center gap-1.5 mb-1 text-[0.9rem] font-semibold tracking-wide text-k-fg-80"
        >
          <slot name="eyebrow" />
        </span>
        <h1 class="name animate-rise text-k-fg mr-4 font-bold tracking-tight my-0 leading-tight overflow-hidden">
          <MarqueeText hover-only :speed="70">
            <slot />
          </MarqueeText>
        </h1>
        <span v-if="$slots.meta" class="meta animate-rise hidden text-[0.9rem] leading-loose text-k-fg-70">
          <slot name="meta" />
        </span>
      </div>

      <slot name="controls" />
    </main>
  </header>
</template>

<script lang="ts" setup>
import MarqueeText from '@/components/ui/MarqueeText.vue'

withDefaults(
  defineProps<{
    layout?: ScreenHeaderLayout
    disabled?: boolean
    circularThumbnail?: boolean
  }>(),
  {
    layout: 'expanded',
    disabled: false,
    circularThumbnail: false,
  },
)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
header.screen-header {
  --transition-duration: 300ms;

  .thumbnail-wrapper {
    @apply origin-top duration-(--transition-duration) translate-y-0 opacity-100 w-[192px];
    will-change: transform, opacity;
    transition-property: transform, opacity;
  }

  &.collapsed {
    @apply gap-0;

    .thumbnail-wrapper {
      @apply -translate-y-full opacity-0 w-0;
    }

    .eyebrow {
      @apply hidden;
    }
  }

  &.circular-thumbnail .thumbnail-wrapper {
    @apply rounded-full ring-1 ring-k-fg-10;
  }

  .eyebrow {
    --stagger: 0;
  }

  &.disabled {
    @apply opacity-50 cursor-not-allowed;

    *,
    *::before,
    *::after {
      @apply pointer-events-none;
    }
  }

  &.expanded {
    .meta {
      @apply block;
    }

    main {
      @apply flex-col items-start;
    }
  }

  h1.name {
    --stagger: 1;
    font-size: clamp(1.8rem, 3.2vw, 4.5rem);
  }

  .meta {
    --stagger: 2;
  }

  &.collapsed h1.name {
    font-size: clamp(1.6rem, 2.2vw, 2.25rem);
  }

  .meta {
    a {
      @apply text-k-fg font-medium hover:text-k-fg hover:underline underline-offset-2;
    }

    :deep(> * + *) {
      @apply ml-1 inline-block before:content-['•'] before:mr-1 before:text-k-fg-50;
    }
  }
}
</style>

<template>
  <span class="btn-group inline-flex relative flex-nowrap">
    <slot />
  </span>
</template>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
.btn-group {
  :deep(button) {
    &:not(:first-child) {
      @apply rounded-none;
    }

    &:first-of-type {
      @apply rounded-l-full rounded-r-none;
    }

    &:last-of-type {
      @apply rounded-r-full rounded-l-none;
    }

    &:only-of-type {
      @apply rounded-full;
    }
  }

  &[uppercase] :deep(button) {
    @apply uppercase tracking-wider text-[0.8rem] font-semibold;
  }
}
</style>

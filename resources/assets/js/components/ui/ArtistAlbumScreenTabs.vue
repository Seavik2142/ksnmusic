<template>
  <div class="tabs">
    <header>
      <slot name="header" />
    </header>
    <main>
      <slot />
    </main>
  </div>
</template>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
:deep(.tabs) {
  @apply flex flex-col overflow-hidden flex-1;
}

:deep(header ul) {
  @apply flex flex-nowrap gap-2 px-6 py-3 overflow-x-auto overflow-y-hidden shrink-0 border-b border-k-fg-5;

  li {
    @apply relative shrink-0;

    a {
      @apply inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-k-fg-5 text-k-fg-80
        hover:bg-k-fg-10 hover:text-k-fg transition-colors duration-150;
    }

    &.active a {
      @apply bg-k-fg text-k-bg hover:bg-k-fg hover:text-k-bg;
    }
  }
}

:deep(:is(main, .songs-pane, .albums-pane)) {
  @apply flex flex-col flex-1 overflow-auto;
}

:deep(.albums-pane) {
  > ul {
    @apply p-7 overflow-auto;
  }

  .none {
    @apply px-7 py-4;
  }
}

:deep(.info-pane) {
  @apply p-7;
}
</style>

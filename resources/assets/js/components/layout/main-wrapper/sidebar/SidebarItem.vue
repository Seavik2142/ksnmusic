<template>
  <li :class="{ active }" class="relative px-3" data-testid="sidebar-item">
    <a
      :href="props.href"
      class="flex items-center overflow-x-hidden gap-3 h-10 px-3 relative rounded-lg text-k-fg-70 hover:text-k-fg hover:bg-k-fg-5 active:bg-k-fg-10 transition-colors duration-150"
      @click.prevent="onClick"
      @dblclick.prevent="onDblClick"
    >
      <span class="icon flex w-5 justify-center shrink-0">
        <slot name="icon" />
      </span>

      <span class="flex-1 overflow-hidden">
        <MarqueeText hover-only>
          <slot />
        </MarqueeText>
      </span>

      <span
        v-if="$slots.badge"
        class="shrink-0 rounded-full px-2 py-0.5 text-[0.6875rem] leading-none bg-k-fg-10 text-k-fg-70"
        data-testid="sidebar-item-badge"
      >
        <slot name="badge" />
      </span>
    </a>
  </li>
</template>

<script lang="ts" setup>
import { useRouter } from '@/composables/useRouter'
import { eventBus } from '@/utils/eventBus'
import MarqueeText from '@/components/ui/MarqueeText.vue'

const props = withDefaults(
  defineProps<{
    href?: string | undefined
    active?: boolean
  }>(),
  {
    active: false,
  },
)

const emit = defineEmits<{ dblclick: [] }>()

const { go } = useRouter()

let clickTimer = 0

const navigate = () => {
  if (props.href) {
    go(props.href)
    eventBus.emit('TOGGLE_SIDEBAR')
  }
}

const onClick = () => {
  clearTimeout(clickTimer)
  clickTimer = window.setTimeout(navigate, 150)
}

const onDblClick = () => {
  clearTimeout(clickTimer)
  emit('dblclick')
}
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.icon {
  transition:
    transform 0.25s var(--ease-out),
    color 0.15s var(--ease-standard);
}

a:hover .icon {
  transform: scale(1.15);
}

li.active a {
  @apply text-k-fg font-medium bg-k-fg-10;

  &::before {
    @apply content-[''] absolute left-0 top-1/2 h-5 w-[3px] -mt-2.5 rounded-full bg-k-highlight;
    animation: active-indicator-in 0.35s var(--ease-out);
  }

  .icon {
    @apply text-k-highlight;
  }
}

@keyframes active-indicator-in {
  from {
    transform: scaleY(0);
    opacity: 0;
  }

  to {
    transform: scaleY(1);
    opacity: 1;
  }
}
</style>

<template>
  <button v-koel-tooltip :title :class="{ popping }" class="relative" @click.prevent.stop="emit('toggle')">
    <span v-if="popping" class="burst" aria-hidden="true" data-testid="favorite-burst" />
    <Icon :icon="favorite ? faHeart : faEmptyHeart" :size="iconSize" class="heart" />
    <span class="sr-only">{{ title }}</span>
  </button>
</template>

<script setup lang="ts">
import { faHeart } from '@fortawesome/free-solid-svg-icons'
import { faHeart as faEmptyHeart } from '@fortawesome/free-regular-svg-icons'
import { computed, onBeforeUnmount, ref, watch } from 'vue'

const props = withDefaults(defineProps<{ favorite: boolean; size?: 'sm' | 'md' }>(), { size: 'sm' })
const emit = defineEmits<{ (e: 'toggle'): void }>()

const POP_DURATION_MS = 600

const title = computed(() => (props.favorite ? 'Undo Favorite' : 'Favorite'))
const iconSize = computed(() => (props.size === 'md' ? undefined : props.size))

const popping = ref(false)
let popTimeout: ReturnType<typeof setTimeout> | undefined

const stopPopping = () => {
  clearTimeout(popTimeout)
  popTimeout = undefined
  popping.value = false
}

watch(
  () => props.favorite,
  (isFavorite, wasFavorite) => {
    stopPopping()

    if (isFavorite && !wasFavorite) {
      popping.value = true
      popTimeout = setTimeout(stopPopping, POP_DURATION_MS)
    }
  },
)

onBeforeUnmount(stopPopping)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
button::after {
  content: '';
  @apply absolute -inset-3;
}

button.popping .heart {
  @apply text-k-love;
  animation: heart-pop 0.45s var(--ease-out);
}

.burst {
  @apply absolute left-1/2 top-1/2 size-[1.5em] rounded-full pointer-events-none border-2 border-k-love;
  animation: heart-burst 0.6s var(--ease-out) forwards;
}

@keyframes heart-pop {
  0% {
    transform: scale(1);
  }
  35% {
    transform: scale(1.4);
  }
  65% {
    transform: scale(0.9);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes heart-burst {
  from {
    opacity: 0.9;
    transform: translate(-50%, -50%) scale(0.4);
  }
  to {
    opacity: 0;
    transform: translate(-50%, -50%) scale(2.4);
  }
}
</style>

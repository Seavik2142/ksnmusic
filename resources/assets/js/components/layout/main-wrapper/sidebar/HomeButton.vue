<template>
  <a
    class="bg-k-fg-5 hover:bg-k-fg-10 active:scale-95 flex items-center justify-center w-10 shrink-0 rounded-full text-k-fg-70 hover:text-k-fg transition-[background-color,color,transform] duration-150"
    :href="url('home')"
    title="Home"
    @click="onClick"
  >
    <Icon :icon="faHome" fixed-width />
  </a>
</template>

<script setup lang="ts">
import { faHome } from '@fortawesome/free-solid-svg-icons'
import { eventBus } from '@/utils/eventBus'
import { useRouter } from '@/composables/useRouter'

const { url } = useRouter()
const onClick = () => eventBus.emit('TOGGLE_SIDEBAR')
</script>

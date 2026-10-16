<template>
  <a
    :href="url('recently-played')"
    class="text-sm font-semibold text-k-fg-60 hover:text-k-fg hover:underline underline-offset-2 transition-colors"
    @click.prevent="goToRecentlyPlayedScreen"
  >
    Show all
  </a>
</template>

<script lang="ts" setup>
import { useRouter } from '@/composables/useRouter'

const { go, url } = useRouter()
const goToRecentlyPlayedScreen = () => go(url('recently-played'))
</script>

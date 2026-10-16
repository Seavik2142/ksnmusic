<template>
  <span
    v-if="usesBundledLogo"
    :style="{ aspectRatio: bundledLogoAspectRatio }"
    class="brand-logo inline-block"
    data-testid="brand-logo"
    v-html="bundledLogoMarkup"
  />
  <img v-else :src="logo" :alt="`${name} logo`" class="object-contain" data-testid="brand-logo" />
</template>

<script lang="ts" setup>
import bundledLogoMarkup from '@/../img/ksnlogo.svg?raw'
import { useBranding } from '@/composables/useBranding'

const { logo, name, isKoelBirdLogo } = useBranding()

const usesBundledLogo = isKoelBirdLogo(logo)

const [, , viewBoxWidth, viewBoxHeight] = /viewBox="([^"]+)"/.exec(bundledLogoMarkup)![1].split(/\s+/)
const bundledLogoAspectRatio = `${viewBoxWidth} / ${viewBoxHeight}`
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.brand-logo :deep(svg) {
  @apply block size-full;
}

.brand-logo :deep(.ksn-logo) {
  --ksn-logo-mark: var(--color-fg);
}
</style>

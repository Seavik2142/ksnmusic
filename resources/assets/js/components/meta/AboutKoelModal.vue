<template>
  <div
    v-koel-focus
    class="about text-center max-w-[480px] overflow-hidden relative"
    data-testid="about-koel"
    tabindex="0"
    @keydown.esc="close"
  >
    <main class="p-6">
      <div class="mb-4 flex justify-center">
        <img alt="Logo" class="inline-block object-contain" :src="logo" width="180" />
      </div>

      <div class="current-version">
        {{ currentVersion }}
        <span v-if="isPlus" class="badge">Plus</span>
        <span v-else>Community</span>
        Edition
        <p v-if="isPlus" class="plus-badge">
          Licensed to {{ license.customerName }} &lt;{{ license.customerEmail }}&gt;
          <br />
          License key: <span class="key font-mono">{{ license.shortKey }}</span>
        </p>
      </div>

      <p v-if="shouldNotifyNewVersion" data-testid="new-version-about">
        <a :href="latestVersionReleaseUrl" target="_blank">
          A new version of {{ appName }} is available ({{ latestVersion }})!
        </a>
      </p>

      <p class="author my-4 text-k-fg-70 text-sm leading-relaxed">
        This is created my Team start Up builder from Norton University Manager Mao Seavik - Kea somneang. Contact me if
        you want about full concept.
      </p>

      <CreditsBlock v-if="isDemo" />
    </main>

    <footer>
      <Btn variant="destructive" data-testid="close-modal-btn" rounded @click.prevent="close">Close</Btn>
    </footer>
  </div>
</template>

<script lang="ts" setup>
import { useKoelPlus } from '@/composables/useKoelPlus'
import { useNewVersionNotification } from '@/composables/useNewVersionNotification'
import { useBranding } from '@/composables/useBranding'

import Btn from '@/components/ui/form/Btn.vue'
import CreditsBlock from '@/components/meta/CreditsBlock.vue'

const emit = defineEmits<{ (e: 'close'): void }>()
const { name: appName, logo } = useBranding()
const { shouldNotifyNewVersion, currentVersion, latestVersion, latestVersionReleaseUrl } = useNewVersionNotification()

const { isPlus, license } = useKoelPlus()

const close = () => emit('close')

const isDemo = window.KOEL.is_demo
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
p {
  @apply mx-0 my-3;
}

a {
  @apply text-k-fg hover:text-k-highlight;
}

.plus-badge {
  .key {
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-image: linear-gradient(97.78deg, #c62be8 17.5%, #671ce4 113.39%);
  }
}
</style>

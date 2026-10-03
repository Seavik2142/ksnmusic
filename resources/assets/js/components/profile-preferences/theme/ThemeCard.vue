<template>
  <article
    :title="isCurrentTheme ? `${theme.name} (current theme)` : `Set current theme to ${theme.name}`"
    class="theme group relative flex flex-col rounded-2xl overflow-hidden cursor-pointer transition-all duration-300 border shadow-md"
    :class="[
      isCurrentTheme
        ? 'ring-2 ring-k-highlight shadow-xl shadow-k-highlight/20 border-k-highlight bg-k-bg-secondary/60'
        : 'border-white/10 hover:border-white/30 hover:shadow-xl hover:-translate-y-1 bg-k-bg-secondary/40',
    ]"
    data-testid="theme-card"
    @contextmenu.prevent="onContextMenu"
  >
    <WithGradientBorder
      :color="highlightColor"
      border-color="transparent"
      border-width="1px"
      class="h-full rounded-[inherit] flex flex-col"
    >
      <button
        class="w-full text-left flex flex-col focus:outline-none focus-visible:ring-2 focus-visible:ring-k-highlight rounded-[inherit]"
        type="button"
        :aria-label="theme.name"
        @click="onClick"
      >
        <!-- Mock UI Theme Preview Canvas -->
        <div
          class="thumbnail relative h-[106px] w-full bg-center bg-cover overflow-hidden flex flex-col justify-between p-3 select-none"
        >
          <!-- Glass sheen and ambient shadow overlay -->
          <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/10 to-black/60 pointer-events-none" />

          <!-- Top row in preview: mini window controls & active badge / preview hint -->
          <div class="relative z-10 flex items-center justify-between w-full">
            <!-- Mini window indicator dots -->
            <div class="flex items-center gap-1.5 opacity-80">
              <span class="w-2 h-2 rounded-full bg-white/20" />
              <span class="w-2 h-2 rounded-full bg-white/20" />
              <span class="w-2 h-2 rounded-full bg-white/20" />
              <span
                v-if="isAnimated"
                class="ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-medium bg-amber-500/30 text-amber-300 border border-amber-400/40 backdrop-blur-xs flex items-center gap-1 shadow-xs"
              >
                ✨ Live
              </span>
            </div>

            <!-- Active Badge or Hover Action -->
            <div>
              <span
                v-if="isCurrentTheme"
                class="theme-highlight-bg inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-white shadow-md backdrop-blur-md border border-white/20"
              >
                <Icon :icon="faCheck" class="text-[9px]" />
                <span>Active</span>
              </span>
              <span
                v-else
                class="opacity-0 group-hover:opacity-100 transition-all duration-200 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium text-white/95 bg-black/60 backdrop-blur-md border border-white/10 shadow-sm"
              >
                Apply
              </span>
            </div>
          </div>

          <!-- Bottom row in preview: Mini KSN Music player layout representation -->
          <div class="relative z-10 flex items-end justify-between w-full mt-auto">
            <!-- Mini track snippet -->
            <div
              class="flex items-center gap-2 bg-black/45 backdrop-blur-xs px-2.5 py-1.5 rounded-lg border border-white/10 max-w-[70%]"
            >
              <div
                class="theme-highlight-bg w-5 h-5 rounded shrink-0 flex items-center justify-center text-[10px] text-white font-black"
              >
                K
              </div>
              <div class="flex flex-col gap-1 min-w-0">
                <span class="w-14 h-1.5 rounded-full bg-white/80 block" />
                <span class="w-9 h-1 rounded-full bg-white/40 block" />
              </div>
            </div>

            <!-- Mini dynamic sound equalizer bars -->
            <div
              class="flex items-end gap-0.5 h-4.5 px-1.5 py-1 bg-black/45 backdrop-blur-xs rounded-md border border-white/10"
            >
              <span
                class="theme-highlight-bg w-1 rounded-full transition-all duration-300"
                :class="isCurrentTheme ? 'animate-eq-1' : 'group-hover:animate-eq-1 h-2'"
              />
              <span
                class="theme-highlight-bg w-1 rounded-full transition-all duration-300"
                :class="isCurrentTheme ? 'animate-eq-2' : 'group-hover:animate-eq-2 h-3.5'"
              />
              <span
                class="theme-highlight-bg w-1 rounded-full transition-all duration-300"
                :class="isCurrentTheme ? 'animate-eq-3' : 'group-hover:animate-eq-3 h-1.5'"
              />
              <span
                class="theme-highlight-bg w-1 rounded-full transition-all duration-300"
                :class="isCurrentTheme ? 'animate-eq-4' : 'group-hover:animate-eq-4 h-3'"
              />
            </div>
          </div>
        </div>

        <!-- Meta Footer: Theme Name & Color Swatches -->
        <div
          class="p-3.5 bg-black/35 backdrop-blur-md border-t border-white/5 flex items-center justify-between gap-3 w-full"
        >
          <div class="flex flex-col min-w-0">
            <span
              class="theme-name font-semibold text-sm text-k-fg group-hover:text-k-highlight transition-colors truncate tracking-wide"
            >
              {{ theme.name }}
            </span>
            <span class="text-[11px] text-k-text-secondary">
              {{ categoryLabel }}
            </span>
          </div>

          <!-- Color Swatch Dots -->
          <div class="flex items-center gap-1.5 shrink-0">
            <span class="theme-base-bg w-3.5 h-3.5 rounded-full border border-white/20 shadow-xs" />
            <span
              class="theme-highlight-bg w-3.5 h-3.5 rounded-full border border-white/20 shadow-xs ring-1 ring-white/10"
            />
          </div>
        </div>
      </button>
    </WithGradientBorder>
  </article>
</template>

<script lang="ts" setup>
import { computed, toRefs } from 'vue'
import { faCheck } from '@fortawesome/free-solid-svg-icons'
import { themeStore } from '@/stores/themeStore'
import { defineAsyncComponent } from '@/utils/helpers'
import { useContextMenu } from '@/composables/useContextMenu'

import WithGradientBorder from '@/components/ui/WithGradientBorder.vue'

const props = defineProps<{ theme: Theme }>()

const ContextMenu = defineAsyncComponent(() => import('@/components/profile-preferences/theme/ThemeContextMenu.vue'))

const { theme } = toRefs(props)

const { openContextMenu } = useContextMenu()

const animatedThemeIds = ['khmer-new-year', 'pchum-ben', 'water-festival', 'angkor-sunrise']
const isAnimated = computed(() => animatedThemeIds.includes(theme.value.id))

const categoryLabel = computed(() => {
  if (theme.value.id === 'khmer-new-year') {
    return 'Khmer Traditional'
  }
  if (theme.value.id === 'pchum-ben') {
    return 'Khmer Traditional'
  }
  if (theme.value.id === 'water-festival') {
    return 'Khmer Traditional'
  }
  if (theme.value.id === 'angkor-sunrise') {
    return 'Khmer Heritage'
  }
  if (theme.value.is_custom) {
    return 'Custom Theme'
  }
  return 'KSN Preset'
})

const isCurrentTheme = computed(() => themeStore.isCurrentTheme(theme.value))
const thumbnailColor = computed(
  () => theme.value.thumbnail_color || theme.value.properties?.['--color-bg'] || '#181818',
)
const thumbnailImage = computed(() => (theme.value.thumbnail_image ? `url(${theme.value.thumbnail_image})` : 'none'))

const themeFontFamily = computed(() => theme.value.properties?.['--font-family'] || 'inherit')

const highlightColor = computed(
  () =>
    theme.value.properties?.['--color-highlight'] ||
    getComputedStyle(document.documentElement).getPropertyValue('--color-highlight').trim() ||
    '#ff5252',
)

const onClick = () => themeStore.setTheme(theme.value)

const onContextMenu = (event: MouseEvent) =>
  openContextMenu<'THEME'>(ContextMenu, event, {
    theme: theme.value,
  })
</script>

<style lang="postcss" scoped>
.thumbnail {
  background-color: v-bind(thumbnailColor);
  background-image: v-bind(thumbnailImage);
}

.theme-highlight-bg {
  background-color: v-bind(highlightColor);
}

.theme-base-bg {
  background-color: v-bind(thumbnailColor);
}

.theme-name {
  font-family: v-bind(themeFontFamily);
}

@keyframes eq-bounce-1 {
  0%,
  100% {
    height: 4px;
  }
  50% {
    height: 14px;
  }
}
@keyframes eq-bounce-2 {
  0%,
  100% {
    height: 13px;
  }
  50% {
    height: 5px;
  }
}
@keyframes eq-bounce-3 {
  0%,
  100% {
    height: 6px;
  }
  50% {
    height: 15px;
  }
}
@keyframes eq-bounce-4 {
  0%,
  100% {
    height: 14px;
  }
  50% {
    height: 4px;
  }
}

.animate-eq-1 {
  animation: eq-bounce-1 1.2s ease-in-out infinite;
}
.animate-eq-2 {
  animation: eq-bounce-2 0.9s ease-in-out infinite;
}
.animate-eq-3 {
  animation: eq-bounce-3 1.1s ease-in-out infinite;
}
.animate-eq-4 {
  animation: eq-bounce-4 0.8s ease-in-out infinite;
}
</style>

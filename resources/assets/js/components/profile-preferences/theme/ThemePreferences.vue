<template>
  <section class="space-y-6">
    <!-- Header banner with active theme info -->
    <div
      class="p-6 rounded-2xl bg-gradient-to-r from-k-bg-secondary/90 via-k-bg-secondary/60 to-k-bg-secondary/30 border border-white/10 backdrop-blur-md flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-lg"
    >
      <div class="space-y-1">
        <div class="flex items-center gap-2.5">
          <PaletteIcon class="w-5 h-5 text-k-highlight" />
          <h3 class="text-xl font-bold text-k-fg tracking-tight">Themes &amp; Appearance</h3>
        </div>
        <p class="text-sm text-k-text-secondary max-w-xl">
          Personalize the look and feel of KSN Music. Choose from our curated official themes or customize your visual
          experience.
        </p>
      </div>

      <div
        v-if="currentTheme"
        class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/10 shrink-0 shadow-sm"
      >
        <div class="flex flex-col text-right">
          <span class="text-[10px] uppercase font-semibold tracking-wider text-k-text-secondary">Current Theme</span>
          <span class="text-sm font-bold text-k-fg">{{ currentTheme.name }}</span>
        </div>
        <div
          class="w-9 h-9 rounded-lg border border-white/20 shadow-md flex items-center justify-center font-bold text-xs"
          :style="{
            backgroundColor: currentTheme.thumbnail_color || currentTheme.properties?.['--color-bg'] || '#181818',
            color: currentTheme.properties?.['--color-highlight'] || 'var(--color-highlight)',
          }"
        >
          <SparklesIcon class="w-4 h-4" />
        </div>
      </div>
    </div>

    <!-- Built-in Themes section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h4 class="text-base font-semibold text-k-fg flex items-center gap-2">
          <span>Official Themes</span>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/10 text-k-text-secondary font-normal">
            {{ builtInThemes.length }}
          </span>
        </h4>
      </div>
      <ThemeList :themes="builtInThemes" data-testid="built-in-themes" />
    </div>

    <!-- Custom Themes section -->
    <template v-if="isPlus">
      <div class="space-y-4 pt-6 border-t border-white/10">
        <div class="flex items-center justify-between">
          <h4 class="text-base font-semibold text-k-fg flex items-center gap-2">
            <span>Custom Themes</span>
            <span
              v-if="customThemes.length"
              class="text-xs px-2.5 py-0.5 rounded-full bg-white/10 text-k-text-secondary font-normal"
            >
              {{ customThemes.length }}
            </span>
          </h4>
          <Btn variant="ghost" bordered @click="requestCreateThemeForm">New Theme</Btn>
        </div>
        <ThemeList v-if="customThemes.length" :themes="customThemes" data-testid="custom-themes" />
      </div>
    </template>
  </section>
</template>

<script lang="ts" setup>
import { computed, onMounted, toRef } from 'vue'
import { PaletteIcon, SparklesIcon } from 'lucide-vue-next'
import { themeStore } from '@/stores/themeStore'
import { defineAsyncComponent } from '@/utils/helpers'
import { useKoelPlus } from '@/composables/useKoelPlus'
import { useModal } from '@/composables/useModal'

import Btn from '@/components/ui/form/Btn.vue'
import ThemeList from '@/components/profile-preferences/theme/ThemeList.vue'

const CreateThemeForm = defineAsyncComponent(() => import('@/components/profile-preferences/theme/CreateThemeForm.vue'))
const { openModal } = useModal()

const themes = toRef(themeStore.state, 'themes')

const builtInThemes = computed(() => themes.value.filter(theme => !theme.is_custom))
const customThemes = computed(() => themes.value.filter(theme => theme.is_custom))
const currentTheme = computed(() => themeStore.getCurrentTheme())

const { isPlus } = useKoelPlus()

const requestCreateThemeForm = () => openModal<'CREATE_THEME_FORM'>(CreateThemeForm)

onMounted(async () => {
  if (isPlus.value) {
    await themeStore.fetchCustomThemes()
  }
})
</script>

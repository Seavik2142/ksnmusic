<template>
  <Transition name="sidebar-backdrop">
    <div
      v-if="mobileShowing"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[998] md:hidden cursor-pointer"
      @click="collapseSidebar"
    />
  </Transition>

  <nav
    :class="{ collapsed: !expanded, 'tmp-showing': tmpShowing, showing: mobileShowing }"
    class="group left-0 top-0 flex flex-col fixed h-full w-full md:relative md:w-k-sidebar-width md:h-auto md:m-2 md:mb-0 md:rounded-xl md:bg-k-surface md:backdrop-blur-2xl md:border md:border-k-fg-5 z-[999] md:z-10"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    <section class="btn-collapse-block flex md:hidden items-center border-b border-b-k-fg-5 h-k-header-height px-6">
      <div class="bg-k-fg-5 rounded-full">
        <SideSheetButton @click.prevent="collapseSidebar">
          <Icon :icon="faTimes" fixed-width />
        </SideSheetButton>
      </div>
    </section>

    <section class="brand-block px-6 pt-6 pb-2 flex items-center">
      <a :href="url('home')" class="inline-block" title="KSN Music">
        <BrandLogo class="h-8 max-w-[180px]" />
      </a>
    </section>

    <section class="home-search-block px-4 pb-4 pt-3 flex gap-2">
      <HomeButton v-show="!searchFocused" />
      <SearchForm class="flex-1" @focus-change="onSearchFocusChange" />
    </section>

    <section class="scroll-mask-y pt-2 pb-10 overflow-y-auto space-y-6">
      <SidebarYourMusicSection />
      <SidebarPlaylistsSection />
      <SidebarManageSection v-if="showManageOptions" />
    </section>

    <section v-if="footerItems.length" class="p-6 flex-1 flex flex-col justify-end gap-3">
      <component :is="item" v-for="(item, index) in footerItems" :key="index" />
    </section>

    <SidebarToggleButton
      v-model="expanded"
      class="opacity-0 no-hover:hidden group-hover:opacity-100 transition"
      :class="expanded || 'opacity-100'"
    />
  </nav>
</template>

<script lang="ts" setup>
import { faTimes } from '@fortawesome/free-solid-svg-icons'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { eventBus } from '@/utils/eventBus'
import { useLocalStorage } from '@/composables/useLocalStorage'
import { useRouter } from '@/composables/useRouter'
import { usePolicies } from '@/composables/usePolicies'
import { useHookSlot } from '@/composables/useHookSlot'

import HomeButton from '@/components/layout/main-wrapper/sidebar/HomeButton.vue'
import SearchForm from '@/components/ui/SearchForm.vue'
import BrandLogo from '@/components/ui/BrandLogo.vue'
import SideSheetButton from '@/components/layout/main-wrapper/side-sheet/SideSheetButton.vue'
import SidebarManageSection from './SidebarManageSection.vue'
import SidebarPlaylistsSection from './SidebarPlaylistsSection.vue'
import SidebarToggleButton from '@/components/layout/main-wrapper/sidebar/SidebarToggleButton.vue'
import SidebarYourMusicSection from './SidebarYourLibrarySection.vue'

const { url, onRouteChanged } = useRouter()
const { currentUserCan } = usePolicies()
const { get: lsGet, set: lsSet } = useLocalStorage()

const mobileShowing = ref(false)
const searchFocused = ref(false)

const onSearchFocusChange = (focused: boolean) => (searchFocused.value = focused)
const EXPAND_DELAY = 500
const COLLAPSE_DELAY = 500

const expanded = ref(!lsGet('sidebar-collapsed', false))
const tmpShowing = ref(false)

let expandTimer: number | undefined
let collapseTimer: number | undefined

const clearExpandTimer = () => {
  if (expandTimer) {
    clearTimeout(expandTimer)
    expandTimer = undefined
  }
}

const clearCollapseTimer = () => {
  if (collapseTimer) {
    clearTimeout(collapseTimer)
    collapseTimer = undefined
  }
}

watch(expanded, value => {
  lsSet('sidebar-collapsed', !value)
  clearExpandTimer()
  clearCollapseTimer()
  tmpShowing.value = false
})

const onMouseEnter = () => {
  if (expanded.value) {
    return
  }

  clearCollapseTimer()

  if (tmpShowing.value) {
    return
  }

  expandTimer = window.setTimeout(() => {
    expandTimer = undefined

    if (!expanded.value) {
      tmpShowing.value = true
    }
  }, EXPAND_DELAY)
}

const onMouseLeave = (event: MouseEvent) => {
  if (!event.relatedTarget) {
    return
  }

  clearExpandTimer()

  if (!tmpShowing.value) {
    return
  }

  collapseTimer = window.setTimeout(() => {
    collapseTimer = undefined
    tmpShowing.value = false
  }, COLLAPSE_DELAY)
}

onBeforeUnmount(() => {
  clearExpandTimer()
  clearCollapseTimer()
})

const showManageOptions = computed(
  () => currentUserCan.manageSettings() || currentUserCan.manageUsers() || currentUserCan.uploadSongs(),
)

const footerItems = useHookSlot('sidebar.footer')

onRouteChanged(_ => (mobileShowing.value = false))

const collapseSidebar = () => (mobileShowing.value = false)

/**
 * Listen to toggle sidebar event to show or hide the sidebar.
 * This should only be triggered on a mobile device.
 */
eventBus.on('TOGGLE_SIDEBAR', () => (mobileShowing.value = !mobileShowing.value))
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.sidebar-backdrop-enter-active,
.sidebar-backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.sidebar-backdrop-enter-from,
.sidebar-backdrop-leave-to {
  opacity: 0;
}

nav {
  -ms-overflow-style: -ms-autohiding-scrollbar;
  transition: width 0.3s var(--ease-standard);

  &.collapsed {
    @apply w-4 md:bg-transparent md:border-transparent md:backdrop-blur-none;

    > *:not(.btn-toggle) {
      @apply hidden;
    }

    &.tmp-showing {
      @apply fixed h-screen m-0 rounded-none bg-k-bg w-k-sidebar-width shadow-2xl z-[999];

      > *:not(.btn-toggle, .btn-collapse-block) {
        @apply block;
      }

      > .home-search-block {
        @apply flex;
      }
    }
  }

  @media screen and (max-width: 768px) {
    background-color: var(--color-bg);
    background-image: var(--bg-image);
    background-attachment: var(--bg-attachment);
    background-size: var(--bg-size);
    background-position: var(--bg-position);
    box-shadow: none;

    transform: translateX(-100%);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);

    &.showing {
      transform: translateX(0);
      box-shadow: 16px 0 48px rgba(0, 0, 0, 0.65);
    }
  }
}
</style>

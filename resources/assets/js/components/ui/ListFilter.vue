<template>
  <OnClickOutside @trigger="maybeHideInput">
    <form
      class="flex items-center h-[34px] border rounded-full border-k-fg-10 hover:bg-k-fg-5 focus-within:border-k-highlight transition-colors"
      @submit.prevent
    >
      <button
        v-koel-tooltip
        class="size-[32px] flex items-center justify-center text-k-fg-70 hover:text-k-fg"
        title="Filter"
        type="button"
        @click.prevent="showInput"
      >
        <Icon :icon="faFilter" fixed-width />
      </button>
      <TextInput
        v-if="showingInput"
        ref="input"
        v-model="keywords"
        class="text-k-fg bg-transparent border-0 rounded-none pl-0! h-[unset]! placeholder:text-k-fg-50 outline-hidden"
        placeholder="Keywords"
        type="search"
        @blur="inputting = false"
        @focus="inputting = true"
      />
    </form>
  </OnClickOutside>
</template>

<script lang="ts" setup>
import { OnClickOutside } from '@vueuse/components'
import { faFilter } from '@fortawesome/free-solid-svg-icons'
import { computed, nextTick, ref } from 'vue'
import { requireInjection } from '@/utils/helpers'
import { FilterKeywordsKey } from '@/config/symbols'

import TextInput from '@/components/ui/form/TextInput.vue'

const input = ref<InstanceType<typeof TextInput>>()
const inputting = ref(false)

const keywords = requireInjection(FilterKeywordsKey, ref(''))

// We show the input if the user is currently typing in it, or if there are any keywords entered
const showingInput = computed(() => inputting.value || keywords.value.trim())

const maybeHideInput = () => {
  inputting.value = false
}

const showInput = () => {
  inputting.value = true

  nextTick(() => {
    input.value?.el?.focus()
    input.value?.el?.select()
  })
}
</script>

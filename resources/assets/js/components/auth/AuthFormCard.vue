<template>
  <form
    :class="{ error: failed }"
    class="w-[288px] sm:border duration-500 p-7 rounded-xl border border-k-fg-3 bg-linear-to-b from-k-fg-3 to-k-fg-5 space-y-3"
    @submit.prevent="$emit('submit')"
  >
    <div class="text-center mb-10 -mt-[89px] flex justify-center">
      <BrandLogo class="w-[150px] drop-shadow-md" />
    </div>

    <slot />
  </form>
</template>

<script lang="ts" setup>
import BrandLogo from '@/components/ui/BrandLogo.vue'

withDefaults(defineProps<{ failed?: boolean }>(), { failed: false })
defineEmits<{ (e: 'submit'): void }>()
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

@keyframes shake {
  8%,
  41% {
    transform: translateX(-10px);
  }
  25%,
  58% {
    transform: translateX(10px);
  }
  75% {
    transform: translateX(-5px);
  }
  92% {
    transform: translateX(5px);
  }
  0%,
  100% {
    transform: translateX(0);
  }
}

form.error {
  @apply border-red-500;
  animation: shake 0.5s;
}
</style>

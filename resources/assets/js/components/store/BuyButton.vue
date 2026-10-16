<template>
  <button
    v-koel-tooltip
    :class="size"
    :title="`Buy for ${price}`"
    class="buy-btn"
    data-testid="buy-button"
    type="button"
    @click.stop.prevent="openCheckout"
  >
    <Icon :icon="faLock" class="lock" />
    <span v-if="size === 'large'">Buy</span>
    <span class="tabular-nums">{{ price }}</span>
  </button>
</template>

<script lang="ts" setup>
import { faLock } from '@fortawesome/free-solid-svg-icons'
import { computed } from 'vue'
import { formatPrice } from '@/utils/formatters'
import { commonStore } from '@/stores/commonStore'
import { storeService } from '@/services/storeService'
import type { Purchasable } from '@/services/storeService'
import { useModal } from '@/composables/useModal'
import { defineAsyncComponent } from '@/utils/helpers'

const props = withDefaults(defineProps<{ item: Purchasable; size?: 'small' | 'large' }>(), { size: 'small' })

const CheckoutModal = defineAsyncComponent(() => import('@/components/store/CheckoutModal.vue'))

const { openModal } = useModal()

const price = computed(() => formatPrice(storeService.priceOf(props.item), commonStore.state.store.currency))

const openCheckout = () => openModal<'CHECKOUT'>(CheckoutModal, { items: [props.item] })
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.buy-btn {
  @apply inline-flex items-center gap-1.5 rounded-full font-semibold transition-[background-color,transform] duration-150
    active:scale-95;

  &.small {
    @apply px-2.5 py-0.5 text-[0.75rem] bg-k-highlight/15 text-k-highlight hover:bg-k-highlight/25;

    .lock {
      @apply text-[0.65rem];
    }
  }

  &.large {
    @apply h-[34px] px-4 text-sm bg-k-highlight text-k-highlight-fg hover:brightness-110;
  }
}
</style>

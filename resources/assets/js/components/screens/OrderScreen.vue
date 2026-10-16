<template>
  <ScreenBase>
    <template #header>
      <ScreenHeader layout="collapsed">Your Order</ScreenHeader>
    </template>

    <div class="flex flex-1 items-center justify-center py-10">
      <section class="order-card animate-pop-in" :class="state" data-testid="order-status">
        <span class="status-icon" aria-hidden="true">
          <Icon v-if="state === 'paid'" :icon="faCheck" />
          <Icon v-else-if="state === 'failed'" :icon="faXmark" />
          <Icon v-else-if="state === 'not-found'" :icon="faQuestion" />
          <Icon v-else :icon="faSpinner" spin />
        </span>

        <h2 class="text-2xl font-bold text-k-fg">{{ heading }}</h2>
        <p class="text-k-fg-70 max-w-sm">{{ explanation }}</p>

        <ul v-if="order?.items.length" class="w-full space-y-1.5 text-left" data-testid="order-items">
          <li v-for="item in order.items" :key="item.id" class="flex justify-between gap-4 text-sm">
            <span class="truncate text-k-fg">
              {{ item.name ?? 'Removed item' }}
              <span class="text-k-fg-50">· {{ item.type === 'albums' ? 'Album' : 'Song' }}</span>
            </span>
            <span class="tabular-nums text-k-fg-70">{{ formatPrice(item.amount_cents, order.currency) }}</span>
          </li>
          <li class="flex justify-between gap-4 border-t border-k-fg-10 pt-2 font-semibold text-k-fg">
            <span>Total</span>
            <span class="tabular-nums">{{ formatPrice(order.amount_cents, order.currency) }}</span>
          </li>
        </ul>

        <div class="flex flex-wrap justify-center gap-2">
          <Btn v-if="state === 'paid'" :href="url('purchases')" tag="a" variant="highlight">Go to Purchases</Btn>
          <Btn :href="url('home')" tag="a" variant="ghost">Back to Home</Btn>
        </div>
      </section>
    </div>
  </ScreenBase>
</template>

<script lang="ts" setup>
import { faCheck, faQuestion, faSpinner, faXmark } from '@fortawesome/free-solid-svg-icons'
import { computed, onBeforeUnmount, ref } from 'vue'
import { formatPrice } from '@/utils/formatters'
import { storeService } from '@/services/storeService'
import { useRouter } from '@/composables/useRouter'

import Btn from '@/components/ui/form/Btn.vue'
import ScreenBase from '@/components/screens/ScreenBase.vue'
import ScreenHeader from '@/components/ui/ScreenHeader.vue'

const POLL_INTERVAL_MS = 2500
const MAX_POLLS = 48

type OrderScreenState = 'confirming' | 'paid' | 'failed' | 'still-pending' | 'not-found'

const { getRouteParam, onScreenActivated, url } = useRouter()

const order = ref<StoreOrder | null>(null)
const state = ref<OrderScreenState>('confirming')

let pollTimer: ReturnType<typeof setTimeout> | undefined
let polls = 0

const stopPolling = () => {
  clearTimeout(pollTimer)
  pollTimer = undefined
}

const headings: Record<OrderScreenState, string> = {
  'confirming': 'Confirming your payment…',
  'paid': 'It’s yours',
  'failed': 'Payment didn’t go through',
  'still-pending': 'Still waiting for confirmation',
  'not-found': 'Order not found',
}

const explanations: Record<OrderScreenState, string> = {
  'confirming': 'This usually takes a few seconds. Keep this page open.',
  'paid': 'Your purchase is unlocked. Enjoy the full songs, anytime.',
  'failed': 'The payment wasn’t completed, so nothing was charged for this order. You can try again.',
  'still-pending': 'The payment provider hasn’t confirmed yet. Your purchase unlocks automatically once it does.',
  'not-found': 'This order doesn’t exist or belongs to another account.',
}

const heading = computed(() => headings[state.value])
const explanation = computed(() => explanations[state.value])

const settle = (settledOrder: StoreOrder) => {
  order.value = settledOrder

  if (settledOrder.status === 'paid') {
    state.value = 'paid'
    storeService.applyPaidOrder(settledOrder)
    return true
  }

  if (settledOrder.status === 'failed' || settledOrder.status === 'cancelled') {
    state.value = 'failed'
    return true
  }

  return false
}

const poll = async (orderId: StoreOrder['id']) => {
  try {
    if (settle(await storeService.getOrder(orderId))) {
      return
    }
  } catch {
    if (!order.value) {
      state.value = 'not-found'
      return
    }
  }

  if (++polls >= MAX_POLLS) {
    state.value = 'still-pending'
    return
  }

  pollTimer = setTimeout(() => poll(orderId), POLL_INTERVAL_MS)
}

onScreenActivated('Order', () => {
  stopPolling()
  polls = 0
  order.value = null
  state.value = 'confirming'
  poll(getRouteParam('id')!)
})

onBeforeUnmount(stopPolling)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.order-card {
  @apply w-full max-w-md flex flex-col items-center gap-4 rounded-2xl border border-k-fg-10 bg-k-fg-5 p-8 text-center;
}

.status-icon {
  @apply size-16 flex items-center justify-center rounded-full bg-k-fg-10 text-2xl text-k-fg-70;

  .paid & {
    @apply bg-k-success/20 text-k-success;
  }

  .failed & {
    @apply bg-k-danger/20 text-k-danger;
  }
}
</style>

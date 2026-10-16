<template>
  <form class="md:w-[440px] min-w-full" data-testid="checkout-modal" @submit.prevent="handleSubmit" @keydown.esc="close">
    <header>
      <h1>{{ headline ?? 'Checkout' }}</h1>
    </header>

    <main class="space-y-5">
      <ul class="space-y-2" data-testid="checkout-items">
        <li v-for="item in items" :key="item.id" class="flex items-center gap-3">
          <span class="size-11 shrink-0 rounded-md bg-cover bg-center bg-k-fg-10" :style="{ backgroundImage: coverOf(item) }" />
          <span class="flex-1 min-w-0">
            <span class="block truncate text-k-fg font-medium">{{ nameOf(item) }}</span>
            <span class="block truncate text-sm text-k-fg-60">{{ item.type === 'albums' ? 'Album' : 'Song' }} · {{ item.artist_name }}</span>
          </span>
          <span class="tabular-nums text-k-fg">{{ formatPrice(storeService.priceOf(item), currency) }}</span>
        </li>
      </ul>

      <div class="flex items-center justify-between border-t border-k-fg-10 pt-3 text-k-fg">
        <span class="font-semibold">Total</span>
        <span class="text-lg font-bold tabular-nums" data-testid="checkout-total">{{ formatPrice(total, currency) }}</span>
      </div>

      <fieldset v-if="availableMethods.length" class="space-y-2">
        <legend class="mb-2 text-sm font-semibold text-k-fg-70">Pay with</legend>
        <label
          v-for="method in availableMethods"
          :key="method.id"
          :class="{ selected: data.method === method.id }"
          class="payment-method"
        >
          <input v-model="data.method" :value="method.id" class="sr-only" name="method" type="radio" required />
          <span class="flex-1">
            <span class="block font-semibold text-k-fg">{{ method.label }}</span>
            <span class="block text-sm text-k-fg-60">{{ method.hint }}</span>
          </span>
          <span class="radio-dot" aria-hidden="true" />
        </label>
      </fieldset>

      <p v-else class="rounded-md bg-k-fg-5 p-3 text-sm text-k-fg-70" data-testid="checkout-unavailable">
        Payments aren't set up yet. Ask an admin to connect PayPal or ABA PayWay.
      </p>
    </main>

    <footer>
      <Btn :disabled="!availableMethods.length || loading" type="submit" variant="highlight">
        {{ loading ? 'Taking you to payment…' : `Pay ${formatPrice(total, currency)}` }}
      </Btn>
      <Btn type="button" variant="ghost" @click.prevent="close">Cancel</Btn>
    </footer>
  </form>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { formatPrice } from '@/utils/formatters'
import { commonStore } from '@/stores/commonStore'
import { storeService } from '@/services/storeService'
import type { Purchasable } from '@/services/storeService'
import { useForm } from '@/composables/useForm'

import Btn from '@/components/ui/form/Btn.vue'

const props = defineProps<{ items: Purchasable[]; headline?: string }>()
const emit = defineEmits<{ (e: 'close'): void }>()

interface MethodOption {
  id: PaymentMethod
  provider: PaymentProvider
  label: string
  hint: string
}

const METHODS: MethodOption[] = [
  { id: 'khqr', provider: 'payway', label: 'KHQR', hint: 'Scan with ABA Mobile or any Bakong bank app' },
  { id: 'card', provider: 'payway', label: 'Card', hint: 'Visa, Mastercard, UnionPay, or JCB via ABA PayWay' },
  { id: 'paypal', provider: 'paypal', label: 'PayPal', hint: 'Your PayPal balance or an international card' },
]

const currency = computed(() => commonStore.state.store.currency)
const total = computed(() => storeService.totalOf(props.items))

const availableMethods = computed(() =>
  METHODS.filter(method => commonStore.state.store.payment_providers.includes(method.provider)),
)

const nameOf = (item: Purchasable) => (item.type === 'songs' ? item.title : item.name)
const coverOf = (item: Purchasable) => `url(${item.type === 'songs' ? item.album_cover : item.cover})`

const close = () => emit('close')

const { data, loading, handleSubmit } = useForm<{ method: PaymentMethod | null }>({
  initialValues: { method: availableMethods.value[0]?.id ?? null },
  useOverlay: false,
  onSubmit: async ({ method }) => await storeService.checkout(props.items, method!),
  onSuccess: ({ payment }) => storeService.continueToPayment(payment),
})
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.payment-method {
  @apply flex items-center gap-3 rounded-lg border border-k-fg-10 px-4 py-3 cursor-pointer
    transition-colors duration-150 hover:bg-k-fg-5;

  &.selected {
    @apply border-k-highlight bg-k-highlight/10;
  }

  &:has(input:focus-visible) {
    @apply ring-2 ring-k-highlight;
  }
}

.radio-dot {
  @apply size-4 shrink-0 rounded-full border-2 border-k-fg-30;

  .selected & {
    @apply border-k-highlight bg-k-highlight;
    box-shadow: inset 0 0 0 3px var(--color-bg);
  }
}
</style>

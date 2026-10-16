import { http } from '@/services/http'
import { eventBus } from '@/utils/eventBus'
import { isSong } from '@/utils/typeGuards'
import { albumStore } from '@/stores/albumStore'
import { playableStore } from '@/stores/playableStore'

export type Purchasable = Song | Album

interface CheckoutResponse {
  order: StoreOrder
  payment: PaymentInitiation
}

const PROVIDER_BY_METHOD: Record<PaymentMethod, PaymentProvider> = {
  paypal: 'paypal',
  khqr: 'payway',
  card: 'payway',
}

const toItemReference = (item: Purchasable) => ({ type: item.type === 'songs' ? 'song' : 'album', id: item.id })

const markSongAsOwned = (song: Song) => {
  song.is_purchased = true
  song.preview_only = false
}

export const storeService = {
  priceOf: (item: Purchasable) => item.price_cents ?? 0,

  totalOf (items: Purchasable[]) {
    return items.reduce((total, item) => total + this.priceOf(item), 0)
  },

  checkout: (items: Purchasable[], method: PaymentMethod) =>
    http.post<CheckoutResponse>('store/orders', {
      items: items.map(toItemReference),
      provider: PROVIDER_BY_METHOD[method],
      payment_method: method === 'paypal' ? null : method,
    }),

  getOrder: (id: StoreOrder['id']) => http.get<StoreOrder>(`store/orders/${id}`),

  fetchPurchases: () => http.get<{ songs: Song[]; albums: Album[] }>('store/purchases'),

  /**
   * Send the browser on to the payment provider: a plain redirect, or (for PayWay) an auto-submitted
   * form carrying the signed fields, which is how PayWay's hosted checkout expects to be opened.
   */
  continueToPayment (payment: PaymentInitiation) {
    if (payment.method === 'GET') {
      window.location.assign(payment.url)
      return
    }

    const form = document.createElement('form')
    form.method = 'POST'
    form.action = payment.url
    form.hidden = true

    Object.entries(payment.fields).forEach(([name, value]) => {
      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = name
      input.value = value
      form.appendChild(input)
    })

    document.body.appendChild(form)
    form.submit()
  },

  /**
   * Reflect a paid order in what's already loaded, so the bought songs play in full right away.
   */
  applyPaidOrder (order: StoreOrder) {
    order.items.forEach(item => {
      if (item.type === 'songs') {
        const song = playableStore.byId(item.id)
        song && isSong(song) && markSongAsOwned(song)
        return
      }

      const album = albumStore.byId(item.id)

      if (album) {
        album.is_purchased = true
      }

      Array.from(playableStore.vault.values())
        .filter((playable): playable is Song => isSong(playable) && playable.album_id === item.id)
        .forEach(markSongAsOwned)
    })

    eventBus.emit('ORDER_PAID', order)
  },
}

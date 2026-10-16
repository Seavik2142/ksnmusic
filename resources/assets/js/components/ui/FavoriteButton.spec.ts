import { describe, expect, it, vi } from 'vite-plus/test'
import { screen } from '@testing-library/vue'
import { createHarness } from '@/__tests__/TestHarness'
import Component from './FavoriteButton.vue'

describe('favoriteButton.vue', () => {
  const h = createHarness()

  it.each([
    [true, 'Undo Favorite'],
    [false, 'Favorite'],
  ])('renders and emits the toggle event when clicked', async (favorite, label) => {
    const { emitted } = h.render(Component, {
      props: {
        favorite,
      },
    })

    await h.user.click(screen.getByRole('button', { name: label }))
    expect(emitted()).toHaveProperty('toggle')
  })

  it('does not celebrate an item that is already a favorite on render', () => {
    h.render(Component, { props: { favorite: true } })

    expect(screen.queryByTestId('favorite-burst')).toBeNull()
  })

  it('celebrates becoming a favorite, then settles', async () => {
    vi.useFakeTimers()

    const { rerender } = h.render(Component, { props: { favorite: false } })
    await rerender({ favorite: true })

    screen.getByTestId('favorite-burst')

    await vi.advanceTimersByTimeAsync(600)

    expect(screen.queryByTestId('favorite-burst')).toBeNull()

    vi.useRealTimers()
  })

  it('does not celebrate being removed from favorites', async () => {
    const { rerender } = h.render(Component, { props: { favorite: true } })
    await rerender({ favorite: false })

    expect(screen.queryByTestId('favorite-burst')).toBeNull()
  })
})

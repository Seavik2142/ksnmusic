import { screen } from '@testing-library/vue'
import { describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import Component from './ArtistHeaderActions.vue'

describe('artistHeaderActions.vue', () => {
  const h = createHarness()

  const renderComponent = (allowEdit: boolean, favorite = false) =>
    h.render(Component, {
      props: {
        artist: h.factory('artist').make({ favorite }),
        allowEdit,
      },
    })

  it('emits toggle-favorite from the heart', async () => {
    const { emitted } = renderComponent(false)

    await h.user.click(screen.getByRole('button', { name: 'Favorite' }))

    expect(emitted()).toHaveProperty('toggle-favorite')
  })

  it('shows the edit button and emits edit when editing is allowed', async () => {
    const { emitted } = renderComponent(true)

    await h.user.click(screen.getByRole('button', { name: 'Edit artist' }))

    expect(emitted()).toHaveProperty('edit')
  })

  it('hides the edit button when editing is not allowed', () => {
    renderComponent(false)

    expect(screen.queryByRole('button', { name: 'Edit artist' })).toBeNull()
  })

  it('emits more with the click event', async () => {
    const { emitted } = renderComponent(false)

    await h.user.click(screen.getByRole('button', { name: 'More Actions' }))

    expect(emitted<[MouseEvent]>().more[0][0]).toBeInstanceOf(MouseEvent)
  })
})

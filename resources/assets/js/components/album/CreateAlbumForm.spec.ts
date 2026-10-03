import { describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import { screen, waitFor } from '@testing-library/vue'
import { albumStore } from '@/stores/albumStore'
import Component from './CreateAlbumForm.vue'

describe('createAlbumForm.vue', () => {
  const h = createHarness()

  const renderComponent = () => {
    return h.render(Component)
  }

  it('submits form with valid details', async () => {
    h.actingAsArtist()
    const storeMock = h.mock(albumStore, 'store').mockResolvedValue(h.factory('album').make())
    renderComponent()

    await h.type(screen.getByTitle('Album name'), 'My Debut Album')
    await h.type(screen.getByTitle('Artist name'), 'Artist One')
    await h.type(screen.getByTitle('Release year'), '2026')

    await h.user.click(screen.getByRole('button', { name: 'Create' }))

    await waitFor(() => {
      expect(storeMock).toHaveBeenCalledWith({
        name: 'My Debut Album',
        artist_name: 'Artist One',
        year: 2026,
        cover: null,
      })
    })
  })

  it('submits with an uploaded cover', async () => {
    h.actingAsArtist()
    const storeMock = h.mock(albumStore, 'store').mockResolvedValue(h.factory('album').make())
    renderComponent()

    await h.type(screen.getByTitle('Album name'), 'My Visual Album')
    await h.user.upload(
      screen.getByLabelText('Pick or paste a cover (optional)'),
      new File(['bytes'], 'cover.png', { type: 'image/png' }),
    )

    await waitFor(() => expect(screen.getByRole('img').getAttribute('src')).toBe('data:image/png;base64,Ynl0ZXM='))

    await h.user.click(screen.getByRole('button', { name: 'Create' }))

    await waitFor(() => {
      expect(storeMock).toHaveBeenCalledWith({
        name: 'My Visual Album',
        artist_name: expect.any(String),
        year: expect.any(Number),
        cover: 'data:image/png;base64,Ynl0ZXM=',
      })
    })
  })
})

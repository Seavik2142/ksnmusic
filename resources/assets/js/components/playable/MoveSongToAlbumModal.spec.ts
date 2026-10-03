import { describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import { screen, waitFor } from '@testing-library/vue'
import { http } from '@/services/http'
import { albumStore } from '@/stores/albumStore'
import { playableStore as songStore } from '@/stores/playableStore'
import Component from './MoveSongToAlbumModal.vue'

describe('moveSongToAlbumModal.vue', () => {
  const h = createHarness()

  const renderComponent = (songs?: Song[]) => {
    const targetSongs = songs || [h.factory('song').make({ title: 'Song in Limbo' })]
    return h.render(Component, {
      props: {
        songs: targetSongs,
      },
    })
  }

  it('renders modal and lists existing albums', async () => {
    const albumOne = h.factory('album').make({ name: 'Summer Vibes', artist_name: 'Artist A' })
    const albumTwo = h.factory('album').make({ name: 'Winter Chill', artist_name: 'Artist B' })

    h.mock(http, 'get').mockResolvedValue({
      data: [albumOne, albumTwo],
      meta: { next_cursor: null },
    })

    renderComponent()

    await waitFor(() => {
      expect(screen.getByText('Summer Vibes')).toBeTruthy()
      expect(screen.getByText('Winter Chill')).toBeTruthy()
    })
  })

  it('moves song to selected existing album', async () => {
    const targetAlbum = h.factory('album').make({ name: 'Midnight Sun', artist_name: 'Luna' })
    const song = h.factory('song').make({ title: 'Eclipse' })

    h.mock(http, 'get').mockResolvedValue({
      data: [targetAlbum],
      meta: { next_cursor: null },
    })

    const updateMock = h.mock(songStore, 'updateSongs').mockResolvedValue({
      songs: [song],
      albums: [targetAlbum],
      artists: [],
      removed: { album_ids: [], artist_ids: [] },
    })

    const { emitted } = renderComponent([song])

    await waitFor(() => expect(screen.getByText('Midnight Sun')).toBeTruthy())

    await h.user.click(screen.getByText('Midnight Sun'))
    await h.user.click(screen.getByRole('button', { name: 'Move' }))

    await waitFor(() => {
      expect(updateMock).toHaveBeenCalledWith([song], {
        album_name: 'Midnight Sun',
        album_artist_name: 'Luna',
        artist_name: 'Luna',
      })
      expect(emitted().close).toBeTruthy()
    })
  })

  it('creates a new album and moves song into it', async () => {
    const song = h.factory('song').make({ title: 'Fresh Track' })
    const createdAlbum = h.factory('album').make({ name: 'Brand New Album', artist_name: 'Fresh Artist' })

    h.mock(http, 'get').mockResolvedValue({
      data: [],
      meta: { next_cursor: null },
    })

    const storeAlbumMock = h.mock(albumStore, 'store').mockResolvedValue(createdAlbum)
    const updateMock = h.mock(songStore, 'updateSongs').mockResolvedValue({
      songs: [song],
      albums: [createdAlbum],
      artists: [],
      removed: { album_ids: [], artist_ids: [] },
    })

    const { emitted } = renderComponent([song])

    await h.user.click(screen.getByRole('button', { name: 'Create New Album' }))

    await h.type(screen.getByPlaceholderText('e.g. Greatest Hits'), 'Brand New Album')
    await h.type(screen.getByPlaceholderText('Artist name'), 'Fresh Artist')

    await h.user.click(screen.getByRole('button', { name: 'Move' }))

    await waitFor(() => {
      expect(storeAlbumMock).toHaveBeenCalledWith({
        name: 'Brand New Album',
        artist_name: 'Fresh Artist',
        year: expect.any(Number),
      })
      expect(updateMock).toHaveBeenCalledWith([song], {
        album_name: 'Brand New Album',
        album_artist_name: 'Fresh Artist',
        artist_name: 'Fresh Artist',
      })
      expect(emitted().close).toBeTruthy()
    })
  })
})

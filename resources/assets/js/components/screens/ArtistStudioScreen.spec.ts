import { screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import { artistStudioService } from '@/services/artistStudioService'
import type { ArtistStudioOverview } from '@/services/artistStudioService'
import Component from './ArtistStudioScreen.vue'

describe('artistStudioScreen.vue', () => {
  const h = createHarness()

  const mockOverview: ArtistStudioOverview = {
    artist: {
      id: 'artist-123',
      name: 'VannDa',
      image: 'https://example.com/vannda.jpg',
    },
    stats: {
      total_streams: 125000,
      monthly_listeners: 45000,
      followers: 12000,
      total_tracks: 14,
      total_albums: 2,
      total_playlists: 8,
    },
    top_songs: [
      {
        id: 'song-1',
        title: 'Time to Rise',
        album_id: 'album-1',
        album_name: 'Skull 2',
        album_cover: 'https://example.com/skull.jpg',
        length: 245,
        play_count: 85000,
        favorites_count: 3200,
        created_at: '2026-01-01T00:00:00Z',
      },
    ],
    releases: [
      {
        id: 'album-1',
        name: 'Skull 2',
        cover: 'https://example.com/skull.jpg',
        year: 2022,
        track_count: 10,
        total_plays: 95000,
        created_at: '2026-01-01T00:00:00Z',
      },
    ],
    playlist_appearances: [
      {
        id: 'playlist-1',
        name: 'Khmer Hip Hop Hits',
        creator_name: 'Admin',
        cover: null,
        songs_count: 25,
        artist_tracks_count: 4,
      },
    ],
    top_listeners: [
      {
        id: 1,
        name: 'Sokha',
        avatar: '',
        play_count: 340,
        last_played_at: '2026-10-02T12:00:00Z',
      },
    ],
  }

  const renderComponent = () => {
    h.mock(artistStudioService, 'fetchOverview').mockResolvedValue(mockOverview)
    h.mock(artistStudioService, 'fetchAccessibleArtists').mockResolvedValue([
      { id: 'artist-123', name: 'VannDa', image: null },
    ])

    return h.render(Component)
  }

  it('renders artist profile and overview stats', async () => {
    renderComponent()

    await waitFor(() => {
      expect(screen.getByText('VannDa')).toBeTruthy()
      expect(screen.getByText('45,000')).toBeTruthy()
      expect(screen.getByText('125,000')).toBeTruthy()
      expect(screen.getByText('12,000')).toBeTruthy()
    })
  })

  it('navigates to releases tab and shows album releases', async () => {
    renderComponent()

    await waitFor(() => {
      expect(screen.getByText('VannDa')).toBeTruthy()
    })

    const releasesTab = screen.getByRole('button', { name: /releases/i })
    await h.user.click(releasesTab)

    await waitFor(() => {
      expect(screen.getByText('Skull 2')).toBeTruthy()
      expect(screen.getByText('Music Releases (Albums & Singles)')).toBeTruthy()
    })
  })

  it('navigates to playlist tracker tab and displays playlist placements', async () => {
    renderComponent()

    await waitFor(() => {
      expect(screen.getByText('VannDa')).toBeTruthy()
    })

    const playlistsTab = screen.getByRole('button', { name: /playlist tracker/i })
    await h.user.click(playlistsTab)

    await waitFor(() => {
      expect(screen.getByText('Khmer Hip Hop Hits')).toBeTruthy()
      expect(screen.getByText('4 of your tracks')).toBeTruthy()
    })
  })

  it('navigates to audience tab and displays top fans', async () => {
    renderComponent()

    await waitFor(() => {
      expect(screen.getByText('VannDa')).toBeTruthy()
    })

    const audienceTab = screen.getByRole('button', { name: /audience & fans/i })
    await h.user.click(audienceTab)

    await waitFor(() => {
      expect(screen.getByText('Sokha')).toBeTruthy()
      expect(screen.getByText('340 plays')).toBeTruthy()
    })
  })
})

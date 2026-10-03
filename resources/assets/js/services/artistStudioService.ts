import { http } from '@/services/http'

export interface ArtistStudioStats {
  total_streams: number
  monthly_listeners: number
  followers: number
  total_tracks: number
  total_albums: number
  total_playlists: number
}

export interface ArtistStudioTopSong {
  id: string
  title: string
  album_id: string | null
  album_name: string
  album_cover: string | null
  length: number
  play_count: number
  favorites_count: number
  created_at: string | null
}

export interface ArtistStudioRelease {
  id: string
  name: string
  cover: string | null
  year: number | null
  track_count: number
  total_plays: number
  created_at: string | null
}

export interface ArtistStudioPlaylistAppearance {
  id: string
  name: string
  creator_name: string
  cover: string | null
  songs_count: number
  artist_tracks_count: number
}

export interface ArtistStudioTopListener {
  id: number
  name: string
  avatar: string
  play_count: number
  last_played_at: string | null
}

export interface ArtistStudioArtistInfo {
  id: string
  name: string
  image: string | null
}

export interface ArtistStudioOverview {
  artist: ArtistStudioArtistInfo
  stats: ArtistStudioStats
  top_songs: ArtistStudioTopSong[]
  releases: ArtistStudioRelease[]
  playlist_appearances: ArtistStudioPlaylistAppearance[]
  top_listeners: ArtistStudioTopListener[]
}

export const artistStudioService = {
  fetchOverview: async (artistId?: string) => {
    const url = artistId ? `artist-studio/overview?artist_id=${artistId}` : 'artist-studio/overview'
    return await http.get<ArtistStudioOverview>(url)
  },

  fetchAccessibleArtists: async () => {
    return await http.get<ArtistStudioArtistInfo[]>('artist-studio/artists')
  },
}

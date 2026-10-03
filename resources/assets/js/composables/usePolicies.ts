import { arrayify } from '@/utils/helpers'
import { useAuthorization } from '@/composables/useAuthorization'
import { useKoelPlus } from '@/composables/useKoelPlus'
import { Filter } from '@/config/hooks'
import { applyFilters } from '@/hooks'

export const usePolicies = () => {
  const { currentUser } = useAuthorization()
  const { isPlus } = useKoelPlus()

  const currentUserCan = {
    editSong: (songs: MaybeArray<Song>) => {
      if (currentUser.value.abilities.includes('manage songs')) {
        return true
      }

      if (currentUser.value.role === 'admin' || currentUser.value.role === 'manager') {
        return true
      }

      return arrayify(songs).every(
        song =>
          song.owner_id === currentUser.value.id ||
          (currentUser.value.role === 'artist' && song.artist_name === currentUser.value.name),
      )
    },

    editPlaylist: (playlist: Playlist) => playlist.permissions.edit,
    deletePlaylist: (playlist: Playlist) => playlist.permissions.delete,
    editAlbum: (album: Album) => {
      if (album.permissions?.edit) {
        return true
      }

      if (currentUser.value.role === 'admin' || currentUser.value.role === 'manager') {
        return true
      }

      if (currentUser.value.abilities.includes('manage songs')) {
        return true
      }

      if (currentUser.value.role === 'artist' && album.artist_name === currentUser.value.name) {
        return true
      }

      return false
    },
    editArtist: (artist: Artist) => artist.permissions.edit,
    editUser: (user: User) => user.permissions.edit,
    deleteUser: (user: User) => user.permissions.delete,

    editRadioStation: (station: RadioStation) => station.permissions.edit,
    deleteRadioStation: (station: RadioStation) => station.permissions.delete,

    // If the user has the permission, they can always add a radio station, even in demo mode.
    addRadioStation: () => !window.KOEL.is_demo || currentUser.value.abilities.includes('manage radio stations'),

    manageSettings: () => currentUser.value.abilities.includes('manage settings'),
    manageUsers: () => currentUser.value.abilities.includes('manage users'),
    accessArtistStudio: () =>
      currentUser.value.role === 'artist' ||
      currentUser.value.role === 'admin' ||
      currentUser.value.role === 'manager' ||
      currentUser.value.abilities.includes('manage songs'),
    createAlbum: () => {
      if (currentUser.value.abilities.includes('manage songs')) {
        return true
      }

      return isPlus.value && currentUser.value.role !== 'guest'
    },
    uploadSongs: () => {
      if (currentUser.value.abilities.includes('manage songs')) {
        return true
      }

      // On Plus, every user has their own library to upload to — except Guests, who don't.
      return isPlus.value && currentUser.value.role !== 'guest'
    },
  }

  return {
    currentUserCan: applyFilters(Filter.POLICIES, currentUserCan),
  }
}

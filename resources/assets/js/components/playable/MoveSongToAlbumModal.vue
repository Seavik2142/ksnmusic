<template>
  <form class="move-song-to-album-modal max-w-[540px] w-full" @submit.prevent="handleSubmit" @keydown.esc="close">
    <header>
      <h1>Move to Album</h1>
      <p class="text-sm text-k-fg-60 mt-1">
        Move <span class="text-k-fg font-medium">{{ songSummary }}</span> into an album.
      </p>
    </header>

    <main class="space-y-4 my-4">
      <div class="flex items-center justify-between pb-2 border-b border-k-fg-10">
        <span class="text-xs uppercase tracking-wider font-bold text-k-fg-50">
          {{ isCreatingNew ? 'Create & Move to New Album' : 'Select Destination Album' }}
        </span>
        <button
          type="button"
          class="text-xs text-k-highlight hover:underline font-medium cursor-pointer flex items-center gap-1"
          @click="toggleCreateNew"
        >
          <Icon :icon="isCreatingNew ? faCompactDisc : faPlus" class="text-[11px]" />
          <span>{{ isCreatingNew ? 'Pick Existing Album' : 'Create New Album' }}</span>
        </button>
      </div>

      <div v-if="isCreatingNew" class="space-y-3 p-4 rounded-xl bg-k-fg-5 border border-k-fg-10">
        <FormRow>
          <template #label>Album Name</template>
          <TextInput
            v-model="newAlbumData.name"
            v-koel-focus
            name="new_album_name"
            placeholder="e.g. Greatest Hits"
            required
          />
        </FormRow>
        <div class="grid grid-cols-2 gap-3">
          <FormRow>
            <template #label>Artist Name</template>
            <TextInput
              v-model="newAlbumData.artist_name"
              name="new_album_artist"
              placeholder="Artist name"
            />
          </FormRow>
          <FormRow>
            <template #label>Release Year</template>
            <TextInput
              v-model="newAlbumData.year"
              type="number"
              name="new_album_year"
              placeholder="e.g. 2026"
            />
          </FormRow>
        </div>
      </div>

      <div v-else class="space-y-3">
        <div class="relative">
          <TextInput
            v-model="searchQuery"
            v-koel-focus
            name="album_search"
            placeholder="Search albums by name or artist..."
            class="pl-9! w-full"
          />
          <Icon :icon="faSearch" class="absolute left-3 top-1/2 -translate-y-1/2 text-k-fg-40 pointer-events-none text-xs" />
        </div>

        <div v-if="loading" class="py-8 text-center text-k-fg-50 text-sm">
          Loading albums…
        </div>

        <div
          v-else-if="filteredAlbums.length"
          class="max-h-[260px] overflow-y-auto space-y-1 pr-1 scroll-mask-y rounded-xl border border-k-fg-10 p-1 bg-k-fg-5"
        >
          <div
            v-for="album in filteredAlbums"
            :key="album.id"
            class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-colors border"
            :class="selectedAlbumId === album.id ? 'bg-k-highlight/15 border-k-highlight text-k-fg' : 'border-transparent hover:bg-k-fg-10 text-k-fg-80'"
            @click="selectedAlbumId = album.id"
          >
            <div class="w-10 h-10 rounded-md overflow-hidden shrink-0 bg-k-fg-10 relative">
              <img
                :src="album.cover || defaultCover"
                :alt="album.name"
                class="w-full h-full object-cover"
                @error="($event.target as HTMLElement).style.display = 'none'"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm text-k-fg truncate">{{ album.name }}</div>
              <div class="text-xs text-k-fg-50 truncate flex items-center gap-2">
                <span>{{ album.artist_name }}</span>
                <span v-if="album.year" class="opacity-60">• {{ album.year }}</span>
              </div>
            </div>
            <div
              v-if="selectedAlbumId === album.id"
              class="w-6 h-6 rounded-full bg-k-highlight text-k-highlight-fg flex items-center justify-center shrink-0 text-xs"
            >
              <Icon :icon="faCheck" />
            </div>
          </div>
        </div>

        <div v-else class="py-8 text-center text-k-fg-50 text-sm space-y-2">
          <div>No albums found{{ searchQuery ? ` matching "${searchQuery}"` : '' }}.</div>
          <button
            type="button"
            class="text-xs text-k-highlight hover:underline font-medium"
            @click="switchToCreateWithQuery"
          >
            Create new album "{{ searchQuery || 'My Album' }}"
          </button>
        </div>
      </div>

      <label class="flex items-center gap-2 text-xs text-k-fg-70 cursor-pointer pt-1 select-none">
        <CheckBox v-model="updateSongArtist" />
        <span>Update song artist to match album artist</span>
      </label>
    </main>

    <footer class="flex justify-end gap-2 pt-3 border-t border-k-fg-10">
      <Btn variant="ghost" type="button" @click="close">Cancel</Btn>
      <Btn
        type="submit"
        :disabled="submitting || (!isCreatingNew && !selectedAlbumId) || (isCreatingNew && !newAlbumData.name.trim())"
      >
        {{ submitting ? 'Moving…' : 'Move' }}
      </Btn>
    </footer>
  </form>
</template>

<script lang="ts" setup>
import { faCheck, faCompactDisc, faPlus, faSearch } from '@fortawesome/free-solid-svg-icons'
import { computed, onMounted, reactive, ref } from 'vue'
import { http } from '@/services/http'
import { albumStore } from '@/stores/albumStore'
import { playableStore as songStore } from '@/stores/playableStore'
import { useBranding } from '@/composables/useBranding'
import { useMessageToaster } from '@/composables/useMessageToaster'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { useAuthorization } from '@/composables/useAuthorization'
import { pluralize } from '@/utils/formatters'
import { eventBus } from '@/utils/eventBus'
import type { SongUpdateData } from '@/stores/playableStore'

import Btn from '@/components/ui/form/Btn.vue'
import CheckBox from '@/components/ui/form/CheckBox.vue'
import TextInput from '@/components/ui/form/TextInput.vue'
import FormRow from '@/components/ui/form/FormRow.vue'

const props = defineProps<{ songs: Song[] }>()
const emit = defineEmits<{ (e: 'close'): void }>()

const { cover: defaultCover } = useBranding()
const { toastSuccess } = useMessageToaster()
const { handleHttpError } = useErrorHandler('dialog')
const { currentUser } = useAuthorization()

const searchQuery = ref('')
const selectedAlbumId = ref<string | null>(null)
const updateSongArtist = ref(true)
const isCreatingNew = ref(false)
const loading = ref(false)
const submitting = ref(false)
const albums = ref<Album[]>([])

const newAlbumData = reactive({
  name: '',
  artist_name: props.songs[0]?.artist_name || currentUser.value?.name || '',
  year: props.songs[0]?.year || new Date().getFullYear(),
})

const songSummary = computed(() => {
  if (props.songs.length === 1) {
    return `"${props.songs[0].title}"`
  }
  return `${props.songs.length} songs`
})

const filteredAlbums = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) {
    return albums.value
  }
  return albums.value.filter(
    album => album.name.toLowerCase().includes(query) || album.artist_name.toLowerCase().includes(query),
  )
})

const toggleCreateNew = () => {
  isCreatingNew.value = !isCreatingNew.value
}

const switchToCreateWithQuery = () => {
  newAlbumData.name = searchQuery.value.trim() || 'My Album'
  isCreatingNew.value = true
}

const close = () => emit('close')

const loadAlbums = async () => {
  loading.value = true
  try {
    const resource = await http.get<CursorPaginatorResource<Album>>('albums?sort=created_at&order=desc')
    albums.value = albumStore.syncWithVault(resource.data)
  } catch (error) {
    albums.value = albumStore.state.albums
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  submitting.value = true
  try {
    let targetAlbum: Album | undefined

    if (isCreatingNew.value) {
      targetAlbum = await albumStore.store({
        name: newAlbumData.name.trim(),
        artist_name: newAlbumData.artist_name.trim() || undefined,
        year: newAlbumData.year ? Number(newAlbumData.year) : undefined,
      })
    } else {
      targetAlbum = albums.value.find(album => album.id === selectedAlbumId.value)
    }

    if (!targetAlbum) {
      return
    }

    const payload: SongUpdateData = {
      album_name: targetAlbum.name,
      album_artist_name: targetAlbum.artist_name,
    }

    if (updateSongArtist.value) {
      payload.artist_name = targetAlbum.artist_name
    }

    const result = await songStore.updateSongs(props.songs, payload)
    toastSuccess(`Moved ${pluralize(props.songs, 'song')} to "${targetAlbum.name}".`)
    eventBus.emit('SONGS_UPDATED', result)
    close()
  } catch (error) {
    handleHttpError(error)
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await loadAlbums()
})
</script>

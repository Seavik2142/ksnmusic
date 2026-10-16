<template>
  <ScreenBase>
    <template #header>
      <ScreenHeader :layout="songs.length ? headerLayout : 'collapsed'">
        Purchases

        <template v-if="songs.length || albums.length" #meta>
          <span v-if="songs.length">{{ pluralize(songs, 'song') }}</span>
          <span v-if="albums.length">{{ pluralize(albums, 'album') }}</span>
        </template>

        <template #controls>
          <SongListControls
            v-if="songs.length && activeTab === 'songs'"
            :config
            @filter="applyFilter"
            @play-all="playAll"
            @play-selected="playSelected"
          />
        </template>
      </ScreenHeader>
    </template>

    <PlayableListSkeleton v-if="loading" class="-m-6" role="status" aria-busy="true" aria-label="Loading" />

    <ScreenEmptyState v-else-if="!songs.length && !albums.length">
      <template #icon>
        <Icon :icon="faBagShopping" />
      </template>
      Nothing bought yet.
      <span class="secondary block">Songs and albums you buy show up here, ready to play in full.</span>
    </ScreenEmptyState>

    <ScreenTabs v-else class="-m-6">
      <template #header>
        <nav>
          <ul>
            <li :class="activeTab === 'songs' && 'active'">
              <a href="#" @click.prevent="activeTab = 'songs'">Songs</a>
            </li>
            <li :class="activeTab === 'albums' && 'active'">
              <a href="#" @click.prevent="activeTab = 'albums'">Albums</a>
            </li>
          </ul>
        </nav>
      </template>

      <div v-show="activeTab === 'songs'" class="songs-pane">
        <SongList v-if="songs.length" ref="songList" @press:enter="onPressEnter" @swipe="onSwipe" />
        <p v-else class="p-6 text-k-fg-60">You haven't bought individual songs.</p>
      </div>

      <div v-show="activeTab === 'albums'" class="albums-pane">
        <GridListView v-if="albums.length" class="scroll-mask-y">
          <AlbumCard v-for="album in albums" :key="album.id" :album />
        </GridListView>
        <p v-else class="p-6 text-k-fg-60">You haven't bought any albums.</p>
      </div>
    </ScreenTabs>
  </ScreenBase>
</template>

<script lang="ts" setup>
import { faBagShopping } from '@fortawesome/free-solid-svg-icons'
import { ref } from 'vue'
import { pluralize } from '@/utils/formatters'
import { eventBus } from '@/utils/eventBus'
import { playableStore } from '@/stores/playableStore'
import { albumStore } from '@/stores/albumStore'
import { storeService } from '@/services/storeService'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { usePlayableList } from '@/composables/usePlayableList'
import { usePlayableListControls } from '@/composables/usePlayableListControls'
import { useRouter } from '@/composables/useRouter'
import { defineAsyncComponent } from '@/utils/helpers'

import ScreenBase from '@/components/screens/ScreenBase.vue'
import ScreenHeader from '@/components/ui/ScreenHeader.vue'
import ScreenEmptyState from '@/components/ui/ScreenEmptyState.vue'
import ScreenTabs from '@/components/ui/ArtistAlbumScreenTabs.vue'
import GridListView from '@/components/ui/GridListView.vue'
import PlayableListSkeleton from '@/components/playable/playable-list/PlayableListSkeleton.vue'

const AlbumCard = defineAsyncComponent(() => import('@/components/album/AlbumCard.vue'))

const songs = ref<Song[]>([])
const albums = ref<Album[]>([])
const loading = ref(false)
const activeTab = ref<'songs' | 'albums'>('songs')

const {
  PlayableList: SongList,
  headerLayout,
  playableList: songList,
  onPressEnter,
  playAll,
  playSelected,
  applyFilter,
  onSwipe,
} = usePlayableList(songs, { type: 'Purchases' })

const { PlayableListControls: SongListControls, config } = usePlayableListControls('Purchases')

const fetchPurchases = async () => {
  loading.value = true

  try {
    const purchases = await storeService.fetchPurchases()
    songs.value = playableStore.syncWithVault(purchases.songs) as Song[]
    albums.value = albumStore.syncWithVault(purchases.albums)
    activeTab.value = songs.value.length || !albums.value.length ? 'songs' : 'albums'
  } catch (error: unknown) {
    useErrorHandler('dialog').handleHttpError(error)
  } finally {
    loading.value = false
  }
}

useRouter().onScreenActivated('Purchases', fetchPurchases)
eventBus.on('ORDER_PAID', fetchPurchases)
</script>

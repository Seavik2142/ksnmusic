<template>
  <div
    :class="{ artist: !forAlbum }"
    class="card-thumbnail group/thumb relative w-full aspect-square text-white"
    data-testid="album-artist-card-thumbnail"
  >
    <div
      class="art absolute inset-0 rounded-md overflow-hidden bg-cover bg-center bg-no-repeat shadow-[var(--shadow-card)]"
      :style="{ backgroundImage: `url(${defaultCover})` }"
    >
      <img
        v-if="image"
        :src="image"
        :alt="entity.name"
        class="absolute inset-0 w-full h-full object-cover"
        loading="lazy"
      />

      <div
        class="overlay absolute inset-0 z-10 bg-linear-to-b from-black/50 via-transparent to-black/40 opacity-0 group-hover/thumb:opacity-100 no-hover:opacity-100 transition-opacity duration-200"
      />
    </div>

    <div class="badges absolute z-20 inset-x-0 top-3 px-4 flex items-center justify-between pointer-events-none">
      <FavoriteButton
        :favorite="entity.favorite"
        size="md"
        class="pointer-events-auto transition-opacity"
        :class="
          entity.favorite
            ? 'is-set opacity-100 drop-shadow-[0_1px_4px_var(--k-bg-50)]'
            : 'opacity-0 group-hover/thumb:opacity-100 no-hover:opacity-100'
        "
        @toggle="emit('toggle-favorite')"
      />

      <StarRating
        :rateable="entity"
        size="sm"
        class="pointer-events-auto transition-opacity"
        :class="
          entity.rating > 0
            ? 'is-set opacity-100 drop-shadow-[0_1px_4px_var(--k-bg-50)]'
            : 'opacity-0 group-hover/thumb:opacity-100 no-hover:opacity-100'
        "
      />
    </div>

    <button
      type="button"
      class="play-btn absolute bottom-2 right-2 z-20 opacity-0 translate-y-2 group-hover/thumb:opacity-100 group-hover/thumb:translate-y-0 focus-visible:opacity-100 focus-visible:translate-y-0 no-hover:opacity-100 no-hover:translate-y-0 transition-[opacity,transform] duration-200"
      :title="playLabel"
      @click.stop="playOrQueue"
    >
      <span
        class="play-icon flex items-center justify-center w-11 aspect-square rounded-full bg-k-highlight text-k-highlight-fg shadow-[var(--shadow-floating)] hover:scale-105 transition-transform duration-150"
        aria-hidden="true"
      >
        <Icon :icon="faPlay" size="lg" class="ml-0.5" />
      </span>
      <span class="sr-only">{{ playLabel }}</span>
    </button>

    <button
      type="button"
      class="more-btn absolute bottom-3 left-3 z-20 p-1 opacity-0 group-hover/thumb:opacity-100 no-hover:opacity-100 transition-opacity"
      title="More actions"
      @click.stop="emit('context-menu', $event)"
    >
      <Icon :icon="faEllipsis" />
      <span class="sr-only">More actions</span>
    </button>
  </div>
</template>

<script lang="ts" setup>
import { orderBy } from 'lodash-es'
import { faEllipsis, faPlay } from '@fortawesome/free-solid-svg-icons'
import { computed, toRefs } from 'vue'
import { queueStore } from '@/stores/queueStore'
import { playableStore } from '@/stores/playableStore'
import { useRouter } from '@/composables/useRouter'
import { useMessageToaster } from '@/composables/useMessageToaster'
import { playback } from '@/services/playbackManager'
import { useBranding } from '@/composables/useBranding'

import FavoriteButton from '@/components/ui/FavoriteButton.vue'
import StarRating from '@/components/ui/StarRating.vue'

const props = defineProps<{ entity: Album | Artist }>()
const { entity } = toRefs(props)

const emit = defineEmits<{
  (e: 'toggle-favorite'): void
  (e: 'context-menu', event: MouseEvent): void
}>()

const { toastSuccess } = useMessageToaster()
const { go, url } = useRouter()
const { cover: defaultCover } = useBranding()

const forAlbum = computed(() => entity.value.type === 'albums')

const image = computed(() =>
  forAlbum.value ? (entity.value as Album).cover || defaultCover : (entity.value as Artist).image || defaultCover,
)

const playLabel = computed(() =>
  forAlbum.value ? `Play all songs in the album ${entity.value.name}` : `Play all songs by ${entity.value.name}`,
)

const sortFields = computed(() => (forAlbum.value ? ['disc', 'track'] : ['album_id', 'disc', 'track']))

const playOrQueue = async (event: MouseEvent) => {
  const songs = forAlbum.value
    ? await playableStore.fetchSongsForAlbum(entity.value as Album)
    : await playableStore.fetchSongsForArtist(entity.value as Artist)

  if (event.altKey) {
    queueStore.queue(orderBy(songs, sortFields.value))
    toastSuccess('Songs added to queue.')
    return
  }

  playback().queueAndPlay(songs)
  go(url('queue'))
}
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';

.card-thumbnail.artist {
  .art {
    @apply rounded-full ring-1 ring-k-fg-10;
  }

  .badges {
    @apply inset-x-auto top-auto bottom-[9%] left-1/2 -translate-x-1/2 px-3 py-1.5 gap-2 rounded-full bg-black/55
      backdrop-blur-sm opacity-0 transition-opacity duration-200 group-hover/thumb:opacity-100 no-hover:opacity-100;

    > * {
      @apply opacity-100;
    }
  }

  &:has(.badges .is-set) .badges {
    @apply opacity-100;
  }

  .play-btn {
    @apply bottom-[2%] right-[2%];
  }

  .more-btn {
    @apply top-[2%] right-[2%] bottom-auto left-auto size-8 p-0 flex items-center justify-center rounded-full bg-black/55
      backdrop-blur-sm;
  }
}
</style>

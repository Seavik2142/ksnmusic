<template>
  <AlbumArtistInfo :mode="mode" data-testid="artist-info">
    <template #header>{{ artist.name }}</template>

    <template #art>
      <ArtistThumbnail :entity="artist" class="group" />
    </template>

    <ParagraphSkeleton v-if="loading" role="status" aria-busy="true" aria-label="Loading" />

    <div v-else-if="hasBio" class="bio" v-html="info!.bio!.full" />

    <div
      v-else
      class="empty flex flex-col items-center text-center gap-2 py-12 text-k-fg-60"
      data-testid="artist-info-empty"
    >
      <span class="size-14 mb-2 flex items-center justify-center rounded-full bg-k-fg-5 text-xl text-k-fg-50">
        <Icon :icon="faMicrophone" />
      </span>
      <p class="text-k-fg font-semibold">No biography yet</p>
      <p class="text-sm max-w-sm">There's no information about {{ artist.name }} available right now.</p>
    </div>

    <template v-if="hasBio && info?.url" #footer>
      <a :href="info.url" rel="noopener noreferrer" target="_blank" class="source-link">
        Source
        <Icon :icon="faArrowUpRightFromSquare" class="text-[0.7em]" />
      </a>
    </template>
  </AlbumArtistInfo>
</template>

<script lang="ts" setup>
import { faArrowUpRightFromSquare, faMicrophone } from '@fortawesome/free-solid-svg-icons'
import { computed, ref, toRefs, watch } from 'vue'
import { encyclopediaService } from '@/services/encyclopediaService'
import { useThirdPartyServices } from '@/composables/useThirdPartyServices'

import ArtistThumbnail from '@/components/ui/album-artist/AlbumOrArtistThumbnail.vue'
import AlbumArtistInfo from '@/components/ui/album-artist/AlbumOrArtistInfo.vue'
import ParagraphSkeleton from '@/components/ui/ParagraphSkeleton.vue'

const props = withDefaults(defineProps<{ artist: Artist; mode?: EncyclopediaDisplayMode }>(), { mode: 'aside' })
const { artist, mode } = toRefs(props)

const { useMusicBrainz, useLastfm, useSpotify } = useThirdPartyServices()

const loading = ref(false)
const info = ref<ArtistInfo | null>(null)
const hasBio = computed(() => Boolean(info.value?.bio?.full?.trim()))

watch(
  artist,
  async () => {
    info.value = null

    if (useMusicBrainz.value || useLastfm.value || useSpotify.value) {
      loading.value = true
      info.value = await encyclopediaService.fetchForArtist(artist.value)
      loading.value = false
    }
  },
  { immediate: true },
)
</script>

<style lang="postcss" scoped>
@reference '@css/app.pcss';
:deep(.play-icon) {
  @apply scale-[3];
}

.full .bio {
  @apply max-w-3xl text-[0.95rem] leading-relaxed text-k-fg-80;
}

.full .bio :deep(p + p) {
  @apply mt-4;
}

.source-link {
  @apply inline-flex items-center gap-1.5 text-sm text-k-fg-60 hover:text-k-fg hover:underline underline-offset-2;
}
</style>

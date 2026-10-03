<template>
  <form @submit.prevent="handleSubmit" @keydown.esc="maybeClose">
    <header>
      <h1>Create Album</h1>
    </header>

    <main class="space-y-5">
      <FormRow>
        <template #label>Name</template>
        <TextInput v-model="data.name" v-koel-focus name="name" placeholder="Album name" required title="Album name" />
      </FormRow>
      <div class="grid grid-cols-2 gap-2">
        <FormRow>
          <template #label>Artist</template>
          <TextInput v-model="data.artist_name" name="artist" placeholder="Artist name" title="Artist name" />
        </FormRow>
        <FormRow>
          <template #label>Release year</template>
          <TextInput v-model="data.year" type="number" name="year" title="Release year" min="1000" />
        </FormRow>
      </div>
      <ArtworkField v-model="data.cover">Pick or paste a cover (optional)</ArtworkField>
    </main>

    <footer>
      <Btn type="submit">Create</Btn>
      <Btn variant="ghost" @click.prevent="maybeClose">Cancel</Btn>
    </footer>
  </form>
</template>

<script setup lang="ts">
import { useAuthorization } from '@/composables/useAuthorization'
import { useMessageToaster } from '@/composables/useMessageToaster'
import { useDialogBox } from '@/composables/useDialogBox'
import type { AlbumCreateData } from '@/stores/albumStore'
import { albumStore } from '@/stores/albumStore'
import { useForm } from '@/composables/useForm'

import FormRow from '@/components/ui/form/FormRow.vue'
import Btn from '@/components/ui/form/Btn.vue'
import TextInput from '@/components/ui/form/TextInput.vue'
import ArtworkField from '@/components/ui/form/ArtworkField.vue'

const emit = defineEmits<{ (e: 'close'): void }>()

const { currentUser } = useAuthorization()
const { toastSuccess } = useMessageToaster()
const { showConfirmDialog } = useDialogBox()

const close = () => emit('close')

const { data, isPristine, handleSubmit } = useForm<AlbumCreateData>({
  initialValues: {
    name: '',
    artist_name: currentUser.value?.name || '',
    year: new Date().getFullYear(),
    cover: null,
  },
  onSubmit: async data => {
    await albumStore.store({
      name: data.name,
      artist_name: data.artist_name || undefined,
      year: data.year ? Number(data.year) : undefined,
      cover: data.cover || null,
    })
  },
  onSuccess: () => {
    toastSuccess('Album created.')
    close()
  },
})

const maybeClose = async () => {
  if (isPristine() || (await showConfirmDialog('Discard all changes?'))) {
    close()
  }
}
</script>

import { useIntervalFn } from '@vueuse/core'
import { computed, ref } from 'vue'
import { crossfadeService } from '@/services/crossfadeService'
import { playback } from '@/services/playbackManager'
import { secondsToHis } from '@/utils/formatters'

export const getActivePlaybackMedia = (): HTMLMediaElement | null => {
  if (crossfadeService.active && crossfadeService.state) {
    return crossfadeService.state.incomingAudio
  }

  return playback('current')?.media ?? null
}

export const usePlaybackTime = (refreshIntervalMs = 250) => {
  const elapsedSeconds = ref(0)
  const durationSeconds = ref(0)

  const refresh = () => {
    const media = getActivePlaybackMedia()
    const hasFiniteDuration = !!media && Number.isFinite(media.duration) && media.duration > 0

    elapsedSeconds.value = hasFiniteDuration ? media.currentTime : 0
    durationSeconds.value = hasFiniteDuration ? media.duration : 0
  }

  refresh()
  useIntervalFn(refresh, refreshIntervalMs)

  const hasDuration = computed(() => durationSeconds.value > 0)
  const elapsed = computed(() => secondsToHis(elapsedSeconds.value))
  const duration = computed(() => secondsToHis(durationSeconds.value))

  const formatAtRatio = (ratio: number) => secondsToHis(Math.max(0, Math.min(1, ratio)) * durationSeconds.value)

  return {
    hasDuration,
    elapsed,
    duration,
    formatAtRatio,
    refresh,
  }
}

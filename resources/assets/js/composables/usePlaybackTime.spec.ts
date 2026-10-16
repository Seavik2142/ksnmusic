import { describe, expect, it, vi } from 'vite-plus/test'
import { effectScope } from 'vue'
import { usePlaybackTime } from './usePlaybackTime'

const { currentMedia } = vi.hoisted(() => ({
  currentMedia: { value: null as Partial<HTMLMediaElement> | null },
}))

vi.mock('@/services/playbackManager', () => ({
  playback: () => (currentMedia.value ? { media: currentMedia.value } : null),
}))

const runInScope = () => {
  const scope = effectScope()
  const result = scope.run(() => usePlaybackTime())!

  return { ...result, stop: () => scope.stop() }
}

describe('usePlaybackTime', () => {
  it('formats the elapsed time and duration of the active media', () => {
    currentMedia.value = { currentTime: 42, duration: 169 }

    const { hasDuration, elapsed, duration, stop } = runInScope()

    expect(hasDuration.value).toBe(true)
    expect(elapsed.value).toBe('00:42')
    expect(duration.value).toBe('02:49')

    stop()
  })

  it('reports no duration when nothing is playing', () => {
    currentMedia.value = null

    const { hasDuration, elapsed, stop } = runInScope()

    expect(hasDuration.value).toBe(false)
    expect(elapsed.value).toBe('00:00')

    stop()
  })

  it('reports no duration for an endless stream', () => {
    currentMedia.value = { currentTime: 12, duration: Infinity }

    const { hasDuration, stop } = runInScope()

    expect(hasDuration.value).toBe(false)

    stop()
  })

  it('formats the time at a given ratio of the duration, clamped to the track', () => {
    currentMedia.value = { currentTime: 0, duration: 200 }

    const { formatAtRatio, stop } = runInScope()

    expect(formatAtRatio(0.5)).toBe('01:40')
    expect(formatAtRatio(1.5)).toBe('03:20')
    expect(formatAtRatio(-1)).toBe('00:00')

    stop()
  })

  it('picks up time changes on refresh', () => {
    const media = { currentTime: 10, duration: 100 }
    currentMedia.value = media

    const { elapsed, refresh, stop } = runInScope()
    expect(elapsed.value).toBe('00:10')

    media.currentTime = 75
    refresh()

    expect(elapsed.value).toBe('01:15')

    stop()
  })
})

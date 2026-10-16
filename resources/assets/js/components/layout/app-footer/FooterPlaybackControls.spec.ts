import { ref } from 'vue'
import { describe, expect, it, vi } from 'vite-plus/test'
import { screen } from '@testing-library/vue'
import { createHarness } from '@/__tests__/TestHarness'
import { CurrentStreamableKey } from '@/config/symbols'
import { playbackService } from '@/services/QueuePlaybackService'
import Component from './FooterPlaybackControls.vue'

const playbackTime = vi.hoisted(() => ({ hasDuration: false }))

vi.mock('@/composables/usePlaybackTime', async () => {
  const { computed } = await import('vue')

  return {
    usePlaybackTime: () => ({
      hasDuration: computed(() => playbackTime.hasDuration),
      elapsed: computed(() => '00:42'),
      duration: computed(() => '02:49'),
    }),
  }
})

describe('footerPlaybackControls.vue', () => {
  const h = createHarness()

  const renderComponent = (playable?: Playable | null) => {
    if (playable === undefined) {
      playable = h.factory('song').make({
        id: '00000000-0000-0000-0000-000000000000',
        title: 'Fahrstuhl to Heaven',
        artist_name: 'Led Zeppelin',
        artist_id: 'led-zeppelin',
        album_name: 'Led Zeppelin IV',
        album_id: 'iv',
        favorite: true,
      })
    }

    return h.render(Component, {
      global: {
        stubs: {
          PlayButton: h.stub('PlayButton'),
        },
        provide: {
          [<symbol>CurrentStreamableKey]: ref(playable),
        },
      },
    })
  }

  it('plays the previous playable', async () => {
    h.createAudioPlayer()

    const playMock = h.mock(playbackService, 'playPrev')
    renderComponent()

    await h.user.click(screen.getByRole('button', { name: 'Play previous in queue' }))

    expect(playMock).toHaveBeenCalled()
  })

  it('plays the next playable', async () => {
    h.createAudioPlayer()

    const playMock = h.mock(playbackService, 'playNext')
    renderComponent()

    await h.user.click(screen.getByRole('button', { name: 'Play next in queue' }))

    expect(playMock).toHaveBeenCalled()
  })

  it('shows the elapsed time and duration of the current playable', () => {
    playbackTime.hasDuration = true
    renderComponent()

    expect(screen.getByTestId('elapsed-time').textContent).toBe('00:42')
    expect(screen.getByTestId('duration').textContent).toBe('02:49')
  })

  it('hides the time when the media has no known duration', () => {
    playbackTime.hasDuration = false
    renderComponent()

    expect(screen.queryByTestId('elapsed-time')).toBeNull()
    expect(screen.queryByTestId('duration')).toBeNull()
  })

  it('hides the time when nothing is playing', () => {
    playbackTime.hasDuration = true
    renderComponent(null)

    expect(screen.queryByTestId('elapsed-time')).toBeNull()
    expect(screen.queryByTestId('duration')).toBeNull()
  })
})

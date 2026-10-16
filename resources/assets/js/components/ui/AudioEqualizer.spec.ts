import { describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import Component from './AudioEqualizer.vue'

describe('audioEqualizer', () => {
  const h = createHarness()

  it('renders correctly with default props', () => {
    const { container } = h.render(Component)
    expect(container.querySelectorAll('.eq-bar').length).toBe(4)
    expect(container.querySelector('.audio-equalizer')?.classList.contains('animated')).toBe(true)
  })

  it('renders custom bar count and paused state', () => {
    const { container } = h.render(Component, {
      props: {
        barCount: 5,
        playing: false,
        size: 'sm',
      },
    })
    expect(container.querySelectorAll('.eq-bar').length).toBe(5)
    expect(container.querySelector('.audio-equalizer')?.classList.contains('animated')).toBe(false)
    expect(container.querySelector('.audio-equalizer')?.classList.contains('size-sm')).toBe(true)
  })
})

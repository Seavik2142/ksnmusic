import { screen } from '@testing-library/vue'
import { afterEach, describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import Component from './BrandLogo.vue'

describe('brandLogo.vue', () => {
  const h = createHarness()
  const originalLogo = window.KOEL.branding.logo

  afterEach(() => {
    window.KOEL.branding.logo = originalLogo
  })

  it('inlines the bundled logo so its letters follow the theme color', () => {
    window.KOEL.branding.logo = ''
    h.render(Component)

    const logo = screen.getByTestId('brand-logo')

    expect(logo.tagName).toBe('SPAN')
    expect(logo.querySelector('svg.ksn-logo')).not.toBeNull()
    expect(logo.querySelector('.ksn-logo-mark')).not.toBeNull()
    expect(logo.querySelector('.ksn-logo-accent')).not.toBeNull()
    expect(logo.style.aspectRatio).toMatch(/^\d+(\.\d+)? \/ \d+(\.\d+)?$/)
  })

  it('keeps the bundled logo accessible', () => {
    window.KOEL.branding.logo = ''
    h.render(Component)

    screen.getByRole('img', { name: 'KSN Music' })
  })

  it('shows a custom uploaded logo as-is', () => {
    window.KOEL.branding.logo = 'https://example.com/custom-logo.png'
    h.render(Component)

    const logo = screen.getByTestId('brand-logo')

    expect(logo.tagName).toBe('IMG')
    expect(logo.getAttribute('src')).toBe('https://example.com/custom-logo.png')
  })
})

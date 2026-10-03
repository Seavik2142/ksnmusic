import { screen } from '@testing-library/vue'
import { describe, expect, it } from 'vite-plus/test'
import { createHarness } from '@/__tests__/TestHarness'
import { commonStore } from '@/stores/commonStore'
import Component from './SettingsScreen.vue'

describe('settingsScreen.vue', () => {
  const h = createHarness({
    beforeEach: () => {
      commonStore.state.storage_driver = 'local'
    },
  })

  const renderComponent = () =>
    h.render(Component, {
      global: {
        stubs: {
          MediaPathSettingGroup: h.stub('media-path-setting-group'),
          BrandingSettingGroup: h.stub('branding-setting-group'),
          AiSettingGroup: h.stub('ai-setting-group'),
        },
      },
    })

  const tabIds = () => screen.getAllByTestId(/^settings-tab-/).map(tab => tab.dataset.testid)

  it('offers the media path tab in the Community edition', () => {
    renderComponent()

    expect(tabIds()).toEqual(['settings-tab-media-path'])
    screen.getByTestId('media-path-setting-group')
  })

  it('adds the branding and AI tabs in the Plus edition', async () => {
    await h.withPlusEdition(() => {
      renderComponent()

      expect(tabIds()).toEqual(['settings-tab-media-path', 'settings-tab-branding', 'settings-tab-ai'])
    })
  })

  it('shows the picked tab and keeps the others mounted', async () => {
    await h.withPlusEdition(async () => {
      renderComponent()

      await h.user.click(screen.getByTestId('settings-tab-ai'))

      const panelOf = (testId: string) => screen.getByTestId(testId).closest<HTMLElement>('[role=tabpanel]')

      expect(panelOf('ai-setting-group')?.style.display).toBe('')
      expect(panelOf('media-path-setting-group')?.style.display).toBe('none')
    })
  })

  it('labels each panel with its tab', () => {
    renderComponent()

    expect(
      screen.getByTestId('media-path-setting-group').closest('[role=tabpanel]')?.getAttribute('aria-labelledby'),
    ).toBe(screen.getByTestId('settings-tab-media-path').id)
  })
})

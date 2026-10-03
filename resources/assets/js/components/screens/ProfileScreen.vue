<template>
  <ScreenBase>
    <template #header>
      <ScreenHeader>Profile &amp; Preferences</ScreenHeader>
    </template>

    <Tabs class="-mx-6">
      <TabList>
        <TabButton
          :selected="currentTab === 'profile'"
          aria-controls="profilePaneProfile"
          @click="currentTab = 'profile'"
        >
          Profile
        </TabButton>
        <TabButton
          :selected="currentTab === 'preferences'"
          aria-controls="profilePanePreferences"
          @click="currentTab = 'preferences'"
        >
          Preferences
        </TabButton>
        <TabButton :selected="currentTab === 'themes'" aria-controls="profilePaneThemes" @click="currentTab = 'themes'">
          Themes
        </TabButton>
        <TabButton
          :selected="currentTab === 'offline'"
          aria-controls="profilePaneOffline"
          @click="currentTab = 'offline'"
        >
          Offline
        </TabButton>
        <TabButton
          :selected="currentTab === 'security'"
          aria-controls="profilePaneSecurity"
          @click="currentTab = 'security'"
        >
          Security
        </TabButton>
        <TabButton :selected="currentTab === 'qr'" aria-controls="profilePaneQr" @click="currentTab = 'qr'">
          <QrCodeIcon :size="16" />
        </TabButton>
      </TabList>

      <TabPanelContainer class="scroll-mask-y">
        <TabPanel v-show="currentTab === 'profile'" id="profilePaneProfile" aria-labelledby="profilePaneProfile">
          <ProfileForm />
        </TabPanel>

        <TabPanel
          v-if="currentTab === 'preferences'"
          id="profilePanePreferences"
          aria-labelledby="profilePanePreferences"
        >
          <PreferencesForm />
        </TabPanel>

        <TabPanel v-if="currentTab === 'themes'" id="profilePaneThemes" aria-labelledby="profilePaneThemes">
          <ThemeList />
        </TabPanel>

        <TabPanel v-if="currentTab === 'offline'" id="profilePaneOffline" aria-labelledby="profilePaneOffline">
          <OfflineStorage />
        </TabPanel>

        <TabPanel v-if="currentTab === 'security'" id="profilePaneSecurity" aria-labelledby="profilePaneSecurity">
          <main class="space-y-6">
            <ChangePasswordForm />
            <TwoFactorAuthSettings />
          </main>
        </TabPanel>

        <TabPanel v-if="currentTab === 'qr'" id="profilePaneQr" aria-labelledby="profilePaneQr">
          <QRLogin />
        </TabPanel>
      </TabPanelContainer>
    </Tabs>
  </ScreenBase>
</template>

<script lang="ts" setup>
import { QrCodeIcon } from 'lucide-vue-next'
import { ref, watch } from 'vue'
import { useLocalStorage } from '@/composables/useLocalStorage'
import { defineAsyncComponent } from '@/utils/helpers'

import ScreenHeader from '@/components/ui/ScreenHeader.vue'
import ScreenBase from '@/components/screens/ScreenBase.vue'
import TabButton from '@/components/ui/tabs/TabButton.vue'
import TabList from '@/components/ui/tabs/TabList.vue'
import TabPanelContainer from '@/components/ui/tabs/TabPanelContainer.vue'
import TabPanel from '@/components/ui/tabs/TabPanel.vue'
import Tabs from '@/components/ui/tabs/Tabs.vue'

const ProfileForm = defineAsyncComponent(() => import('@/components/profile-preferences/ProfileForm.vue'))
const PreferencesForm = defineAsyncComponent(() => import('@/components/profile-preferences/PreferencesForm.vue'))
const ThemeList = defineAsyncComponent(() => import('@/components/profile-preferences/theme/ThemePreferences.vue'))
const OfflineStorage = defineAsyncComponent(() => import('@/components/profile-preferences/OfflineStorage.vue'))
const ChangePasswordForm = defineAsyncComponent(() => import('@/components/profile-preferences/ChangePasswordForm.vue'))
const TwoFactorAuthSettings = defineAsyncComponent(
  () => import('@/components/auth/two-factor/TwoFactorAuthSettings.vue'),
)
const QRLogin = defineAsyncComponent(() => import('@/components/profile-preferences/QRLogin.vue'))

const { get, set } = useLocalStorage()

const validTabs = ['profile', 'preferences', 'themes', 'offline', 'security', 'qr'] as const
type Tab = (typeof validTabs)[number]

const isValidTab = (tab: unknown): tab is Tab => (validTabs as readonly unknown[]).includes(tab)

const savedTab = get<Tab>('profileScreenTab', 'profile')
const currentTab = ref<Tab>(isValidTab(savedTab) ? savedTab : 'profile')

watch(currentTab, tab => set('profileScreenTab', tab))
</script>

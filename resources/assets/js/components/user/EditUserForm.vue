<template>
  <form
    class="w-full md:min-w-[500px] md:max-w-[540px] text-k-fg"
    data-testid="edit-user-form"
    novalidate
    @submit.prevent="handleSubmit"
    @keydown.esc="maybeClose"
  >
    <header class="flex items-center justify-between border-b border-k-fg-10 pb-4">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-400 border border-amber-500/20 flex items-center justify-center text-lg shadow-sm"
        >
          <Icon :icon="faUserPen" />
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-k-fg leading-snug">Edit User</h1>
          <p class="text-xs text-k-fg-60">Update profile details, credentials, and access role</p>
        </div>
      </div>
      <button
        type="button"
        class="w-8 h-8 rounded-lg flex items-center justify-center text-k-fg-50 hover:text-k-fg hover:bg-k-fg-10 transition-colors cursor-pointer"
        title="Close"
        @click.prevent="maybeClose"
      >
        <Icon :icon="faTimes" />
      </button>
    </header>

    <main class="space-y-4 pt-4">
      <!-- Live User Identity Card -->
      <div
        class="flex items-center gap-3.5 p-3.5 rounded-xl bg-k-fg-5 border border-k-fg-10 backdrop-blur-md transition-all duration-200"
      >
        <div
          class="w-11 h-11 rounded-full bg-gradient-to-tr from-amber-500 to-rose-500 text-white font-bold text-base flex items-center justify-center shadow-md uppercase select-none ring-2 ring-k-fg-10"
        >
          {{ avatarInitial }}
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <span class="font-semibold text-sm text-k-fg truncate">
              {{ data.name.trim() || user.name }}
            </span>
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wider uppercase border"
              :class="
                data.role === 'admin'
                  ? 'bg-amber-500/20 text-amber-300 border-amber-500/30'
                  : 'bg-k-fg-10 text-k-fg-70 border-k-fg-10'
              "
            >
              <Icon :icon="data.role === 'admin' ? faShield : faUser" class="mr-1 text-[9px]" />
              {{ data.role }}
            </span>
            <span
              v-if="user.sso_provider"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30"
            >
              SSO: {{ user.sso_provider }}
            </span>
          </div>
          <p class="text-xs text-k-fg-50 truncate mt-0.5 font-mono">
            {{ data.email.trim() || user.email }}
          </p>
        </div>
      </div>

      <AlertBox v-if="user.sso_provider" type="info">
        This user authenticates externally via <strong>{{ user.sso_provider }}</strong> SSO. Their email is managed by
        the identity provider.
      </AlertBox>

      <!-- Full Name Field -->
      <FormRow>
        <template #label>
          <span class="font-medium text-sm flex items-center gap-1.5">
            <Icon :icon="faUser" class="text-xs text-k-fg-40" />
            Full Name
            <span class="text-red-400 font-bold">*</span>
          </span>
        </template>
        <TextInput
          v-model="data.name"
          v-koel-focus
          name="name"
          placeholder="e.g. Jane Doe"
          required
          :class="{ 'border-red-500! ring-1! ring-red-500!': errors.name }"
          @input="errors.name = ''"
        />
        <template #help>
          <span v-if="errors.name" class="text-xs text-red-400 flex items-center gap-1 mt-0.5 font-medium">
            <Icon :icon="faCircleExclamation" class="text-[10px]" />
            {{ errors.name }}
          </span>
        </template>
      </FormRow>

      <!-- Email Field -->
      <FormRow>
        <template #label>
          <span class="font-medium text-sm flex items-center gap-1.5">
            <Icon :icon="faEnvelope" class="text-xs text-k-fg-40" />
            Email Address
            <span class="text-red-400 font-bold">*</span>
          </span>
        </template>
        <TextInput
          v-model="data.email"
          :readonly="Boolean(user.sso_provider)"
          name="email"
          type="email"
          placeholder="e.g. jane@example.com"
          required
          title="Email"
          :class="{ 'border-red-500! ring-1! ring-red-500!': errors.email }"
          @input="errors.email = ''"
        />
        <template #help>
          <span v-if="errors.email" class="text-xs text-red-400 flex items-center gap-1 mt-0.5 font-medium">
            <Icon :icon="faCircleExclamation" class="text-[10px]" />
            {{ errors.email }}
          </span>
        </template>
      </FormRow>

      <!-- Password Field (Optional on edit) -->
      <FormRow v-if="!user.sso_provider">
        <template #label>
          <span class="font-medium text-sm flex items-center gap-1.5">
            <Icon :icon="faLock" class="text-xs text-k-fg-40" />
            Change Password
            <span class="text-xs font-normal text-k-fg-40">(optional)</span>
          </span>
        </template>
        <PasswordField
          v-model="data.password"
          autocomplete="new-password"
          name="password"
          placeholder="Leave blank for no changes"
          :class="{ 'border-red-500! ring-1! ring-red-500!': errors.password }"
          @input="errors.password = ''"
        />

        <template #help>
          <div class="space-y-1.5 mt-1">
            <div class="flex items-center justify-between">
              <div v-if="errors.password" class="text-xs text-red-400 flex items-center gap-1 font-medium">
                <Icon :icon="faCircleExclamation" class="text-[10px]" />
                {{ errors.password }}
              </div>
              <span v-else class="text-xs text-k-fg-50">
                Leave blank to keep existing password. If changing, min. 10 characters.
              </span>

              <button
                type="button"
                class="text-xs text-k-primary hover:underline flex items-center gap-1 font-medium transition-colors cursor-pointer"
                title="Generate a secure random password"
                @click.prevent="generatePassword"
              >
                <Icon :icon="faKey" class="text-[10px]" />
                Generate Password
              </button>
            </div>

            <!-- Guidelines active only when user types a password -->
            <div v-if="data.password" class="flex flex-wrap gap-2 text-[11px] text-k-fg-60">
              <span
                class="flex items-center gap-1 px-2 py-0.5 rounded-md border transition-colors"
                :class="
                  passwordLengthMet
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                    : 'bg-k-fg-5 text-k-fg-50 border-k-fg-10'
                "
              >
                <Icon :icon="passwordLengthMet ? faCheck : faCircleInfo" class="text-[9px]" />
                At least 10 characters
              </span>
              <span
                class="flex items-center gap-1 px-2 py-0.5 rounded-md border transition-colors"
                :class="
                  passwordComplexityMet
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                    : 'bg-k-fg-5 text-k-fg-50 border-k-fg-10'
                "
              >
                <Icon :icon="passwordComplexityMet ? faCheck : faCircleInfo" class="text-[9px]" />
                Mix of letters, numbers &amp; symbols
              </span>
            </div>
          </div>
        </template>
      </FormRow>

      <!-- Role Picker -->
      <RolePicker v-model="data.role" />
    </main>

    <footer class="flex items-center justify-between pt-4 border-t border-k-fg-10 mt-2">
      <span class="text-xs text-k-fg-50 flex items-center gap-1">
        <span class="text-red-400 font-bold">*</span>
        Required fields
      </span>
      <div class="flex items-center gap-2">
        <Btn variant="ghost" :disabled="loading" class="btn-cancel" @click.prevent="maybeClose">Cancel</Btn>
        <Btn :disabled="loading" class="btn-update" type="submit">
          <Icon v-if="loading" :icon="faSpinner" class="animate-spin mr-1.5" />
          <Icon v-else :icon="faCheck" class="mr-1.5" />
          Update
        </Btn>
      </div>
    </footer>
  </form>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { pick } from 'lodash-es'
import {
  faCheck,
  faCircleExclamation,
  faCircleInfo,
  faEnvelope,
  faKey,
  faLock,
  faShield,
  faSpinner,
  faTimes,
  faUser,
  faUserPen,
} from '@fortawesome/free-solid-svg-icons'
import type { UpdateUserData } from '@/stores/userStore'
import { userStore } from '@/stores/userStore'
import { useDialogBox } from '@/composables/useDialogBox'
import { useMessageToaster } from '@/composables/useMessageToaster'
import { useForm } from '@/composables/useForm'

import Btn from '@/components/ui/form/Btn.vue'
import AlertBox from '@/components/ui/AlertBox.vue'
import TextInput from '@/components/ui/form/TextInput.vue'
import FormRow from '@/components/ui/form/FormRow.vue'
import RolePicker from '@/components/user/RolePicker.vue'
import PasswordField from '@/components/ui/form/PasswordField.vue'

const props = defineProps<{ user: User }>()
const emit = defineEmits<{ (e: 'close'): void }>()

const { user } = props

const { toastSuccess } = useMessageToaster()
const { showConfirmDialog } = useDialogBox()

const close = () => emit('close')

const errors = ref<{
  name?: string
  email?: string
  password?: string
  role?: string
}>({})

const { data, isPristine, loading, handleSubmit } = useForm<UpdateUserData>({
  initialValues: {
    ...pick(user, 'name', 'email', 'role'),
    password: '',
  },
  validator: data => {
    errors.value = {}
    let valid = true

    if (!data.name || !data.name.trim()) {
      errors.value.name = 'Full name is required.'
      valid = false
    }

    if (!data.email || !data.email.trim()) {
      errors.value.email = 'Email address is required.'
      valid = false
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email.trim())) {
      errors.value.email = 'Please enter a valid email address.'
      valid = false
    }

    if (data.password && data.password.length < 10) {
      errors.value.password = 'Password must be at least 10 characters if provided.'
      valid = false
    }

    if (!data.role) {
      errors.value.role = 'Role selection is required.'
      valid = false
    }

    return valid
  },
  onSubmit: async data => {
    const formattedData = { ...data }

    if (!formattedData.password) {
      delete formattedData.password
    }

    await userStore.update(user, formattedData)
  },
  onSuccess: () => {
    toastSuccess('User profile updated.')
    close()
  },
})

const avatarInitial = computed(() => {
  const name = (data.name || user.name).trim()
  if (name) {
    return name.charAt(0).toUpperCase()
  }
  const email = (data.email || user.email).trim()
  if (email) {
    return email.charAt(0).toUpperCase()
  }
  return '?'
})

const passwordLengthMet = computed(() => (data.password?.length || 0) >= 10)
const passwordComplexityMet = computed(() => {
  const p = data.password || ''
  return /[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)
})

const generatePassword = () => {
  const uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ'
  const lowers = 'abcdefghijkmnopqrstuvwxyz'
  const digits = '23456789'
  const specials = '!@#$%^&*'
  const all = uppers + lowers + digits + specials

  let pass = ''
  pass += uppers[Math.floor(Math.random() * uppers.length)]
  pass += lowers[Math.floor(Math.random() * lowers.length)]
  pass += digits[Math.floor(Math.random() * digits.length)]
  pass += specials[Math.floor(Math.random() * specials.length)]

  for (let i = 0; i < 8; i++) {
    pass += all[Math.floor(Math.random() * all.length)]
  }

  data.password = pass
    .split('')
    .sort(() => Math.random() - 0.5)
    .join('')
  errors.value.password = ''
}

const maybeClose = async () => {
  if (isPristine() || (await showConfirmDialog('Discard all changes?'))) {
    close()
  }
}
</script>

<template>
  <form
    class="w-full md:min-w-[500px] md:max-w-[540px] text-k-fg"
    novalidate
    @submit.prevent="handleSubmit"
    @keydown.esc="maybeClose"
  >
    <header class="flex items-center justify-between border-b border-k-fg-10 pb-4">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-xl bg-k-primary/15 text-k-primary border border-k-primary/20 flex items-center justify-center text-lg shadow-sm"
        >
          <Icon :icon="faUserPlus" />
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-k-fg leading-snug">Add New User</h1>
          <p class="text-xs text-k-fg-60">Create a new account with custom permissions and access</p>
        </div>
      </div>
      <button
        type="button"
        class="w-8 h-8 rounded-lg flex items-center justify-center text-k-fg-50 hover:text-k-fg hover:bg-k-fg-10 transition-colors"
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
          class="w-11 h-11 rounded-full bg-gradient-to-tr from-sky-600 to-indigo-600 text-white font-bold text-base flex items-center justify-center shadow-md uppercase select-none ring-2 ring-k-fg-10"
        >
          {{ avatarInitial }}
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <span class="font-semibold text-sm text-k-fg truncate">
              {{ data.name.trim() || 'New User Account' }}
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
          </div>
          <p class="text-xs text-k-fg-50 truncate mt-0.5 font-mono">
            {{ data.email.trim() || 'email@example.com' }}
          </p>
        </div>
      </div>

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
          placeholder="e.g. Sokha Chan"
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
          name="email"
          type="email"
          placeholder="e.g. sokha@example.com"
          required
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

      <!-- Password Field with Quick Generator -->
      <FormRow>
        <template #label>
          <span class="font-medium text-sm flex items-center gap-1.5">
            <Icon :icon="faLock" class="text-xs text-k-fg-40" />
            Password
            <span class="text-red-400 font-bold">*</span>
          </span>
        </template>
        <PasswordField
          v-model="data.password"
          autocomplete="new-password"
          name="password"
          placeholder="Min. 10 characters"
          required
          title="Password"
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
              <span v-else class="text-xs text-k-fg-50">Create a secure login password</span>

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

            <!-- Password Guidelines / Indicators -->
            <div class="flex flex-wrap gap-2 text-[11px] text-k-fg-60">
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
        All fields are required
      </span>
      <div class="flex items-center gap-2">
        <Btn variant="ghost" :disabled="loading" class="btn-cancel" @click.prevent="maybeClose">Cancel</Btn>
        <Btn :disabled="loading" class="btn-add" type="submit">
          <Icon v-if="loading" :icon="faSpinner" class="animate-spin mr-1.5" />
          <Icon v-else :icon="faCheck" class="mr-1.5" />
          Save
        </Btn>
      </div>
    </footer>
  </form>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
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
  faUserPlus,
} from '@fortawesome/free-solid-svg-icons'
import type { CreateUserData } from '@/stores/userStore'
import { userStore } from '@/stores/userStore'
import { useDialogBox } from '@/composables/useDialogBox'
import { useMessageToaster } from '@/composables/useMessageToaster'
import { useForm } from '@/composables/useForm'

import Btn from '@/components/ui/form/Btn.vue'
import TextInput from '@/components/ui/form/TextInput.vue'
import FormRow from '@/components/ui/form/FormRow.vue'
import RolePicker from '@/components/user/RolePicker.vue'
import PasswordField from '@/components/ui/form/PasswordField.vue'

const emit = defineEmits<{ (e: 'close'): void }>()

const { toastSuccess } = useMessageToaster()
const { showConfirmDialog } = useDialogBox()

const close = () => emit('close')

const errors = ref<{
  name?: string
  email?: string
  password?: string
  role?: string
}>({})

const { data, isPristine, loading, handleSubmit } = useForm<CreateUserData>({
  initialValues: {
    name: '',
    email: '',
    password: '',
    role: 'user',
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

    if (!data.password) {
      errors.value.password = 'Password is required.'
      valid = false
    } else if (data.password.length < 10) {
      errors.value.password = 'Password must be at least 10 characters.'
      valid = false
    }

    if (!data.role) {
      errors.value.role = 'Role selection is required.'
      valid = false
    }

    return valid
  },
  onSubmit: async data => await userStore.store(data),
  onSuccess: (user: User) => {
    toastSuccess(`New user "${user.name}" created.`)
    close()
  },
})

const avatarInitial = computed(() => {
  const name = data.name.trim()
  if (name) {
    return name.charAt(0).toUpperCase()
  }
  const email = data.email.trim()
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

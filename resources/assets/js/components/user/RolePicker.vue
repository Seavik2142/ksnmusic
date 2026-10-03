<template>
  <FormRow>
    <template #label>
      <span class="font-medium text-sm flex items-center gap-1.5">
        <Icon :icon="faShield" class="text-xs text-k-fg-40" />
        Account Role
        <span class="text-red-400 font-bold">*</span>
      </span>
    </template>
    <SelectBox v-model="value" name="role" required>
      <option v-for="{ id, label } in assignableRoles" :key="id" :value="id">{{ label }}</option>
    </SelectBox>
    <template #help>
      <span class="text-xs text-k-fg-60 flex items-center gap-1 mt-0.5">
        <Icon :icon="faCircleInfo" class="text-[10px]" />
        {{ selectedRoleDescription }}
      </span>
    </template>
  </FormRow>
</template>

<script setup lang="ts">
import { computed, toRef } from 'vue'
import { faCircleInfo, faShield } from '@fortawesome/free-solid-svg-icons'
import { commonStore } from '@/stores/commonStore'

import FormRow from '@/components/ui/form/FormRow.vue'
import SelectBox from '@/components/ui/form/SelectBox.vue'

const props = withDefaults(defineProps<{ modelValue?: Role }>(), { modelValue: 'user' })
const emit = defineEmits<{ (e: 'update:modelValue', value: Role): void }>()

const assignableRoles = toRef(commonStore.state, 'assignable_roles')

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const selectedRoleDescription = computed(() => {
  const selectedRole = assignableRoles.value.find(({ id }) => id === value.value)
  return selectedRole?.description || ''
})
</script>

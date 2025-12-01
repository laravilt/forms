<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  placeholder?: string
  disabled?: boolean
  readonly?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  placeholder: 'Phone number',
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  'update:value': [value: string | null]
}>()

// Popular country codes with flags
const countryCodes = [
  { code: '+1', country: 'US', flag: '🇺🇸', name: 'United States' },
  { code: '+1', country: 'CA', flag: '🇨🇦', name: 'Canada' },
  { code: '+44', country: 'GB', flag: '🇬🇧', name: 'United Kingdom' },
  { code: '+61', country: 'AU', flag: '🇦🇺', name: 'Australia' },
  { code: '+86', country: 'CN', flag: '🇨🇳', name: 'China' },
  { code: '+33', country: 'FR', flag: '🇫🇷', name: 'France' },
  { code: '+49', country: 'DE', flag: '🇩🇪', name: 'Germany' },
  { code: '+91', country: 'IN', flag: '🇮🇳', name: 'India' },
  { code: '+81', country: 'JP', flag: '🇯🇵', name: 'Japan' },
  { code: '+82', country: 'KR', flag: '🇰🇷', name: 'South Korea' },
  { code: '+52', country: 'MX', flag: '🇲🇽', name: 'Mexico' },
  { code: '+7', country: 'RU', flag: '🇷🇺', name: 'Russia' },
  { code: '+966', country: 'SA', flag: '🇸🇦', name: 'Saudi Arabia' },
  { code: '+27', country: 'ZA', flag: '🇿🇦', name: 'South Africa' },
  { code: '+34', country: 'ES', flag: '🇪🇸', name: 'Spain' },
  { code: '+971', country: 'AE', flag: '🇦🇪', name: 'UAE' },
  { code: '+20', country: 'EG', flag: '🇪🇬', name: 'Egypt' },
]

const selectedCountryCode = ref('+1')
const phoneNumber = ref('')
const isCountrySelectorOpen = ref(false)
const searchQuery = ref('')
let isUserTyping = false

// Filtered countries based on search
const filteredCountries = computed(() => {
  if (!searchQuery.value) return countryCodes
  const query = searchQuery.value.toLowerCase()
  return countryCodes.filter(country =>
    country.name.toLowerCase().includes(query) ||
    country.code.includes(query) ||
    country.country.toLowerCase().includes(query)
  )
})

// Parse initial value if provided
const initialValue = props.modelValue || props.value || ''
if (initialValue) {
  const match = initialValue.match(/^(\+\d+)\s*(.*)$/)
  if (match) {
    selectedCountryCode.value = match[1]
    phoneNumber.value = match[2]
  } else {
    phoneNumber.value = initialValue
  }
}

// Watch for prop changes from parent (but not during user typing)
watch(() => props.modelValue || props.value, (newValue) => {
  if (isUserTyping) return

  if (!newValue) {
    phoneNumber.value = ''
    return
  }
  const match = newValue.match(/^(\+\d+)\s*(.*)$/)
  if (match) {
    selectedCountryCode.value = match[1]
    phoneNumber.value = match[2]
  } else {
    phoneNumber.value = newValue
  }
})

const fullPhoneNumber = computed(() => {
  if (!phoneNumber.value) return null
  return `${selectedCountryCode.value} ${phoneNumber.value}`
})

const updateCountryCode = (code: string) => {
  selectedCountryCode.value = code
  isCountrySelectorOpen.value = false
  searchQuery.value = ''
  // Emit the updated value
  emit('update:modelValue', fullPhoneNumber.value)
  emit('update:value', fullPhoneNumber.value)
}

// Watch for phoneNumber changes and emit
watch(phoneNumber, () => {
  isUserTyping = true
  // Emit the updated value
  emit('update:modelValue', fullPhoneNumber.value)
  emit('update:value', fullPhoneNumber.value)
  // Clear flag after a short delay (after the debounce completes in parent)
  // The parent (TextInput) has 300ms debounce for live fields
  setTimeout(() => {
    isUserTyping = false
  }, 400)
})
</script>

<template>
  <div class="relative">
    <!-- Country Code Selector (inside input as prefix) -->
    <div class="absolute inset-y-0 left-0 flex items-center z-10">
      <Popover v-model:open="isCountrySelectorOpen">
        <PopoverTrigger as-child>
          <Button
            variant="ghost"
            role="combobox"
            :aria-expanded="isCountrySelectorOpen"
            class="h-full border-0 bg-transparent hover:bg-accent focus:ring-0 focus-visible:ring-0 rounded-r-none px-2 gap-1"
          >
            <span class="text-base">{{ countryCodes.find(c => c.code === selectedCountryCode)?.flag }}</span>
            <span class="text-sm font-medium">{{ selectedCountryCode }}</span>
            <ChevronsUpDown class="h-3 w-3 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[280px] p-0" align="start">
          <div class="flex flex-col">
            <!-- Search input -->
            <div class="p-3 border-b border-border">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search country..."
                  class="pl-9 h-9"
                />
              </div>
            </div>

            <!-- Countries list -->
            <div class="max-h-[300px] overflow-y-auto">
              <div v-if="filteredCountries.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                No country found.
              </div>
              <button
                v-else
                v-for="country in filteredCountries"
                :key="country.country"
                type="button"
                class="w-full flex items-center gap-2 px-3 py-2 hover:bg-accent transition-colors text-left"
                :class="{ 'bg-accent': selectedCountryCode === country.code }"
                @click="updateCountryCode(country.code)"
              >
                <Check
                  class="h-4 w-4"
                  :class="{ 'opacity-100': selectedCountryCode === country.code, 'opacity-0': selectedCountryCode !== country.code }"
                />
                <span class="text-lg">{{ country.flag }}</span>
                <span class="flex-1">{{ country.name }}</span>
                <span class="text-sm text-muted-foreground">{{ country.code }}</span>
              </button>
            </div>
          </div>
        </PopoverContent>
      </Popover>
    </div>

    <!-- Phone Number Input -->
    <Input
      type="tel"
      v-model="phoneNumber"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      class="pl-[110px]"
    />

    <!-- Hidden input for full phone number -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="fullPhoneNumber || ''"
    />
  </div>
</template>

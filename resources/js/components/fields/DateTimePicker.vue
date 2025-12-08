<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  DatePickerRoot,
  DatePickerField,
  DatePickerInput,
  DatePickerTrigger,
  DatePickerContent,
  DatePickerCalendar,
  DatePickerHeader,
  DatePickerHeading,
  DatePickerGrid,
  DatePickerCell,
  DatePickerHeadCell,
  DatePickerGridHead,
  DatePickerGridBody,
  DatePickerGridRow,
  DatePickerCellTrigger,
  DatePickerNext,
  DatePickerPrev,
} from 'reka-ui'
import { CalendarIcon, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { parseDateTime, now, getLocalTimeZone, type CalendarDateTime, CalendarDate } from '@internationalized/date'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  label?: string
  helperText?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  minDate?: string | null
  maxDate?: string | null
  hourCycle?: 12 | 24
  locale?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  placeholder: 'Select date and time',
  hourCycle: 24,
  locale: 'en',
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  'update:value': [value: string | null]
}>()

// Parse string datetime to CalendarDateTime
const parseDateTimeString = (dateTimeStr: string | null | undefined): CalendarDateTime | null => {
  if (!dateTimeStr) return null
  try {
    // Expected format: YYYY-MM-DDTHH:mm or YYYY-MM-DD HH:mm
    const normalized = dateTimeStr.replace(' ', 'T')
    return parseDateTime(normalized)
  } catch (e) {
    return null
  }
}

// Convert CalendarDateTime to string
const formatDateTimeValue = (dateTime: CalendarDateTime | null | undefined): string | null => {
  if (!dateTime) return null

  const year = dateTime.year
  const month = String(dateTime.month).padStart(2, '0')
  const day = String(dateTime.day).padStart(2, '0')
  const hour = String(dateTime.hour).padStart(2, '0')
  const minute = String(dateTime.minute).padStart(2, '0')

  return `${year}-${month}-${day} ${hour}:${minute}`
}

const selectedDateTime = ref<CalendarDateTime | null>(
  parseDateTimeString(props.modelValue || props.value)
)

// Watch for prop changes
watch(
  () => props.modelValue || props.value,
  (newValue) => {
    selectedDateTime.value = parseDateTimeString(newValue)
  }
)

const minDateValue = computed(() => parseDateTimeString(props.minDate))
const maxDateValue = computed(() => parseDateTimeString(props.maxDate))

// Month/Year navigation
const placeholder = ref(selectedDateTime.value || now(getLocalTimeZone()))

// Generate years range (50 years back, 10 years forward)
const years = computed(() => {
  const currentYear = now(getLocalTimeZone()).year
  const startYear = currentYear - 50
  const endYear = currentYear + 10
  const yearsList = []
  for (let year = startYear; year <= endYear; year++) {
    yearsList.push(year)
  }
  return yearsList
})

// Generate months
const months = [
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' },
]

const selectedMonth = computed({
  get: () => placeholder.value.month,
  set: (month: number) => {
    placeholder.value = placeholder.value.set({ month })
  }
})

const selectedYear = computed({
  get: () => placeholder.value.year,
  set: (year: number) => {
    placeholder.value = placeholder.value.set({ year })
  }
})

const updateDateTime = (dateTime: CalendarDateTime | null | undefined) => {
  selectedDateTime.value = dateTime ?? null
  const dateTimeStr = formatDateTimeValue(dateTime)
  emit('update:modelValue', dateTimeStr)
  emit('update:value', dateTimeStr)
}
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Label -->
    <label
      v-if="label"
      :for="name"
      class="text-sm font-medium block text-foreground"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ms-0.5">*</span>
    </label>

    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="formatDateTimeValue(selectedDateTime) || ''"
    />

    <!-- DateTime Picker -->
    <DatePickerRoot
      v-bind="{
        ...(selectedDateTime ? { modelValue: selectedDateTime } : {}),
        ...(minDateValue ? { minValue: minDateValue } : {}),
        ...(maxDateValue ? { maxValue: maxDateValue } : {}),
      }"
      v-model:placeholder="placeholder"
      @update:model-value="updateDateTime"
      :disabled="disabled"
      :readonly="readonly"
      :locale="locale"
      :hour-cycle="hourCycle"
      granularity="minute"
    >
      <DatePickerField
        v-slot="{ segments }"
        class="flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
        :class="{ 'opacity-50 cursor-not-allowed': disabled }"
      >
        <div class="flex-1 flex items-center gap-1">
          <template v-for="item in segments" :key="item.part">
            <DatePickerInput
              v-if="item.part === 'literal'"
              :part="item.part"
            >
              {{ item.value }}
            </DatePickerInput>
            <DatePickerInput
              v-else
              :part="item.part"
              class="px-1 tabular-nums outline-none focus:bg-accent rounded"
              :class="{ 'min-w-[2ch]': ['hour', 'minute'].includes(item.part), 'uppercase': item.part === 'dayPeriod' }"
            >
              {{ item.value }}
            </DatePickerInput>
          </template>
        </div>

        <DatePickerTrigger
          class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
        >
          <CalendarIcon class="h-4 w-4 text-muted-foreground" />
        </DatePickerTrigger>
      </DatePickerField>

      <DatePickerContent
        class="z-50 mt-2 w-auto rounded-md border bg-popover p-3 text-popover-foreground shadow-md outline-none"
      >
        <DatePickerCalendar v-slot="{ weekDays, grid }" class="space-y-4">
          <DatePickerHeader class="flex items-center justify-between mb-2">
            <DatePickerPrev
              class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 w-7"
            >
              <ChevronLeft class="h-4 w-4" />
            </DatePickerPrev>

            <div class="flex items-center gap-2">
              <!-- Month Selector -->
              <select
                v-model="selectedMonth"
                class="text-sm font-medium border border-input bg-background text-foreground rounded-md px-2 py-1 cursor-pointer transition-colors hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-0"
              >
                <option v-for="month in months" :key="month.value" :value="month.value">
                  {{ month.label }}
                </option>
              </select>

              <!-- Year Selector -->
              <select
                v-model="selectedYear"
                class="text-sm font-medium border border-input bg-background text-foreground rounded-md px-2 py-1 cursor-pointer transition-colors hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-0"
              >
                <option v-for="year in years" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>

            <DatePickerNext
              class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 w-7"
            >
              <ChevronRight class="h-4 w-4" />
            </DatePickerNext>
          </DatePickerHeader>

          <DatePickerGrid
            v-for="month in grid"
            :key="month.value.toString()"
            class="w-full border-collapse space-y-1"
          >
            <DatePickerGridHead>
              <DatePickerGridRow class="flex">
                <DatePickerHeadCell
                  v-for="day in weekDays"
                  :key="day"
                  class="text-muted-foreground rounded-md w-9 font-normal text-[0.8rem]"
                >
                  {{ day }}
                </DatePickerHeadCell>
              </DatePickerGridRow>
            </DatePickerGridHead>

            <DatePickerGridBody>
              <DatePickerGridRow
                v-for="(weekDates, index) in month.rows"
                :key="`weekDate-${index}`"
                class="flex w-full mt-2"
              >
                <DatePickerCell
                  v-for="weekDate in weekDates"
                  :key="weekDate.toString()"
                  :date="weekDate"
                >
                  <DatePickerCellTrigger
                    :day="weekDate"
                    :month="month.value"
                    class="relative inline-flex items-center justify-center p-0 text-center text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hover:bg-accent hover:text-accent-foreground h-9 w-9 font-normal aria-selected:opacity-100 rounded-md data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:hover:bg-primary data-[selected]:hover:text-primary-foreground data-[disabled]:text-muted-foreground data-[disabled]:opacity-50 data-[unavailable]:text-muted-foreground data-[unavailable]:line-through"
                  />
                </DatePickerCell>
              </DatePickerGridRow>
            </DatePickerGridBody>
          </DatePickerGrid>
        </DatePickerCalendar>
      </DatePickerContent>
    </DatePickerRoot>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>

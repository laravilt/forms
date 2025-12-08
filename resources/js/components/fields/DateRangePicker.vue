<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  DateRangePickerRoot,
  DateRangePickerField,
  DateRangePickerInput,
  DateRangePickerTrigger,
  DateRangePickerContent,
  DateRangePickerCalendar,
  DateRangePickerHeader,
  DateRangePickerHeading,
  DateRangePickerGrid,
  DateRangePickerCell,
  DateRangePickerHeadCell,
  DateRangePickerGridHead,
  DateRangePickerGridBody,
  DateRangePickerGridRow,
  DateRangePickerCellTrigger,
  DateRangePickerNext,
  DateRangePickerPrev,
} from 'reka-ui'
import { CalendarIcon, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { parseDate, today, getLocalTimeZone, type DateValue, type DateRange } from '@internationalized/date'

interface Props {
  name?: string
  value?: { start: string | null; end: string | null } | null
  modelValue?: { start: string | null; end: string | null } | null
  label?: string
  helperText?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  minDate?: string | null
  maxDate?: string | null
  locale?: string
  numberOfMonths?: number
  closeOnSelect?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  placeholder: 'Select date range',
  locale: 'en',
  numberOfMonths: 2,
  closeOnSelect: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: { start: string | null; end: string | null } | null]
  'update:value': [value: { start: string | null; end: string | null } | null]
}>()

// Parse string date to DateValue
const parseDateString = (dateStr: string | null | undefined): DateValue | null => {
  if (!dateStr) return null
  try {
    return parseDate(dateStr)
  } catch (e) {
    return null
  }
}

// Convert DateValue to string
const formatDateValue = (date: DateValue | null | undefined): string | null => {
  if (!date) return null
  return `${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}`
}

// Parse range object
const parseRangeValue = (range: { start: string | null; end: string | null } | null | undefined): DateRange | null => {
  if (!range) {
    return null
  }

  const start = parseDateString(range.start)
  const end = parseDateString(range.end)

  if (!start || !end) {
    return null
  }

  return { start, end }
}

// Format range object
const formatRangeValue = (range: DateRange | null | undefined): { start: string | null; end: string | null } | null => {
  if (!range) return null

  return {
    start: formatDateValue(range.start),
    end: formatDateValue(range.end),
  }
}

const selectedRange = ref<DateRange | null>(
  parseRangeValue(props.modelValue || props.value)
)

// Watch for prop changes
watch(
  () => props.modelValue || props.value,
  (newValue) => {
    selectedRange.value = parseRangeValue(newValue)
  }
)

const minDateValue = computed(() => parseDateString(props.minDate))
const maxDateValue = computed(() => parseDateString(props.maxDate))

// Month/Year navigation
const placeholder = ref(selectedRange.value?.start || today(getLocalTimeZone()))

// Generate years range (50 years back, 10 years forward)
const years = computed(() => {
  const currentYear = today(getLocalTimeZone()).year
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
    const newDate = placeholder.value.set({ month })
    placeholder.value = newDate
  }
})

const selectedYear = computed({
  get: () => placeholder.value.year,
  set: (year: number) => {
    const newDate = placeholder.value.set({ year })
    placeholder.value = newDate
  }
})

const updateRange = (range: DateRange | null | undefined) => {
  selectedRange.value = range ?? null
  const rangeObj = formatRangeValue(range)
  emit('update:modelValue', rangeObj)
  emit('update:value', rangeObj)
}

// Formatted display value
const displayValue = computed(() => {
  if (!selectedRange.value || !selectedRange.value.start || !selectedRange.value.end) {
    return props.placeholder
  }

  const startDate = new Date(
    selectedRange.value.start.year,
    selectedRange.value.start.month - 1,
    selectedRange.value.start.day
  )
  const endDate = new Date(
    selectedRange.value.end.year,
    selectedRange.value.end.month - 1,
    selectedRange.value.end.day
  )

  const formatter = new Intl.DateTimeFormat(props.locale, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })

  return `${formatter.format(startDate)} - ${formatter.format(endDate)}`
})
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

    <!-- Hidden inputs for form submission -->
    <template v-if="name">
      <input
        type="hidden"
        :name="`${name}[start]`"
        :value="selectedRange?.start ? formatDateValue(selectedRange.start) : ''"
      />
      <input
        type="hidden"
        :name="`${name}[end]`"
        :value="selectedRange?.end ? formatDateValue(selectedRange.end) : ''"
      />
    </template>

    <!-- Date Range Picker -->
    <DateRangePickerRoot
      v-bind="{
        ...(selectedRange ? { modelValue: selectedRange } : {}),
        ...(minDateValue ? { minValue: minDateValue } : {}),
        ...(maxDateValue ? { maxValue: maxDateValue } : {}),
      }"
      v-model:placeholder="placeholder"
      @update:model-value="updateRange"
      :disabled="disabled"
      :readonly="readonly"
      :locale="locale"
      :number-of-months="numberOfMonths"
      :close-on-select="closeOnSelect"
    >
      <DateRangePickerField
        v-slot="{ segments }"
        class="flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
        :class="{ 'opacity-50 cursor-not-allowed': disabled }"
      >
        <div class="flex-1 flex items-center gap-1">
          <template v-for="item in segments.start" :key="`start-${item.part}`">
            <DateRangePickerInput
              v-if="item.part === 'literal'"
              type="start"
              :part="item.part"
            >
              {{ item.value }}
            </DateRangePickerInput>
            <DateRangePickerInput
              v-else
              type="start"
              :part="item.part"
              class="px-1 tabular-nums outline-none focus:bg-accent rounded"
            >
              {{ item.value }}
            </DateRangePickerInput>
          </template>

          <span class="text-muted-foreground px-1">-</span>

          <template v-for="item in segments.end" :key="`end-${item.part}`">
            <DateRangePickerInput
              v-if="item.part === 'literal'"
              type="end"
              :part="item.part"
            >
              {{ item.value }}
            </DateRangePickerInput>
            <DateRangePickerInput
              v-else
              type="end"
              :part="item.part"
              class="px-1 tabular-nums outline-none focus:bg-accent rounded"
            >
              {{ item.value }}
            </DateRangePickerInput>
          </template>
        </div>

        <DateRangePickerTrigger
          class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
        >
          <CalendarIcon class="h-4 w-4 text-muted-foreground" />
        </DateRangePickerTrigger>
      </DateRangePickerField>

      <DateRangePickerContent
        class="z-50 mt-2 w-auto rounded-md border bg-popover p-3 text-popover-foreground shadow-md outline-none"
      >
        <DateRangePickerCalendar v-slot="{ weekDays, grid }" class="flex gap-3">
          <div v-for="(month, index) in grid" :key="month.value.toString()">
            <DateRangePickerHeader class="flex items-center justify-between mb-2">
              <DateRangePickerPrev
                v-if="index === 0"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 w-7"
              >
                <ChevronLeft class="h-4 w-4" />
              </DateRangePickerPrev>
              <div v-else class="w-7" />

              <div v-if="index === 0" class="flex items-center gap-2">
                <!-- Month Selector (only for first month) -->
                <select
                  v-model="selectedMonth"
                  class="text-sm font-medium border border-input bg-background text-foreground rounded-md px-2 py-1 cursor-pointer transition-colors hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-0"
                >
                  <option v-for="m in months" :key="m.value" :value="m.value">
                    {{ m.label }}
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
              <DateRangePickerHeading v-else class="text-sm font-medium" />

              <DateRangePickerNext
                v-if="index === grid.length - 1"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 w-7"
              >
                <ChevronRight class="h-4 w-4" />
              </DateRangePickerNext>
              <div v-else class="w-7" />
            </DateRangePickerHeader>

            <DateRangePickerGrid class="w-full border-collapse space-y-1">
              <DateRangePickerGridHead>
                <DateRangePickerGridRow class="flex">
                  <DateRangePickerHeadCell
                    v-for="day in weekDays"
                    :key="day"
                    class="text-muted-foreground rounded-md w-9 font-normal text-[0.8rem]"
                  >
                    {{ day }}
                  </DateRangePickerHeadCell>
                </DateRangePickerGridRow>
              </DateRangePickerGridHead>

              <DateRangePickerGridBody>
                <DateRangePickerGridRow
                  v-for="(weekDates, weekIndex) in month.rows"
                  :key="`weekDate-${weekIndex}`"
                  class="flex w-full mt-2"
                >
                  <DateRangePickerCell
                    v-for="weekDate in weekDates"
                    :key="weekDate.toString()"
                    :date="weekDate"
                  >
                    <DateRangePickerCellTrigger
                      :day="weekDate"
                      :month="month.value"
                      class="relative inline-flex items-center justify-center p-0 text-center text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hover:bg-accent hover:text-accent-foreground h-9 w-9 font-normal aria-selected:opacity-100 rounded-md data-[selection-start]:bg-primary data-[selection-start]:text-primary-foreground data-[selection-start]:hover:bg-primary data-[selection-start]:hover:text-primary-foreground data-[selection-end]:bg-primary data-[selection-end]:text-primary-foreground data-[selection-end]:hover:bg-primary data-[selection-end]:hover:text-primary-foreground data-[selected]:bg-accent data-[selected]:text-accent-foreground data-[selected]:rounded-none data-[selection-start]:rounded-r-none data-[selection-end]:rounded-l-none data-[disabled]:text-muted-foreground data-[disabled]:opacity-50 data-[unavailable]:text-muted-foreground data-[unavailable]:line-through"
                    />
                  </DateRangePickerCell>
                </DateRangePickerGridRow>
              </DateRangePickerGridBody>
            </DateRangePickerGrid>
          </div>
        </DateRangePickerCalendar>
      </DateRangePickerContent>
    </DateRangePickerRoot>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>

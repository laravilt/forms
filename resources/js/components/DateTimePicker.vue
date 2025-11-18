<template>
  <div class="laravilt-datetime-picker">
    <div class="relative">
      <!-- DateTime Input -->
      <input
        type="text"
        :value="displayValue"
        @click="showPicker = !showPicker"
        :placeholder="placeholder || 'Select date and time'"
        :required="required"
        :disabled="disabled"
        readonly
        class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 pr-10"
      />

      <!-- Calendar-Clock Icon -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>

    <!-- DateTime Picker Dropdown -->
    <div
      v-show="showPicker"
      @click.away="showPicker = false"
      class="absolute z-50 mt-2 w-80 origin-top-left rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800"
    >
      <!-- Tab Navigation -->
      <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button
          type="button"
          @click="activeTab = 'date'"
          :class="{
            'border-b-2 border-primary-600 text-primary-600': activeTab === 'date',
            'text-gray-500': activeTab !== 'date'
          }"
          class="flex-1 px-4 py-3 text-sm font-medium"
        >
          Date
        </button>
        <button
          type="button"
          @click="activeTab = 'time'"
          :class="{
            'border-b-2 border-primary-600 text-primary-600': activeTab === 'time',
            'text-gray-500': activeTab !== 'time'
          }"
          class="flex-1 px-4 py-3 text-sm font-medium"
        >
          Time
        </button>
      </div>

      <div class="p-4">
        <!-- Date Tab -->
        <div v-show="activeTab === 'date'">
          <!-- Month/Year Header -->
          <div class="flex items-center justify-between mb-4">
            <button
              type="button"
              @click="previousMonth"
              class="p-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <span class="text-sm font-semibold text-gray-900 dark:text-white">
              {{ currentMonthYear }}
            </span>

            <button
              type="button"
              @click="nextMonth"
              class="p-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <!-- Day Names -->
          <div class="grid grid-cols-7 gap-1 mb-2">
            <div
              v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']"
              :key="day"
              class="text-center text-xs font-semibold text-gray-600 dark:text-gray-400"
            >
              {{ day }}
            </div>
          </div>

          <!-- Calendar Days -->
          <div class="grid grid-cols-7 gap-1">
            <button
              v-for="day in calendarDays"
              :key="day.date"
              type="button"
              @click="selectDate(day.date)"
              :disabled="!day.isCurrentMonth"
              :class="{
                'bg-primary-600 text-white': isSelectedDate(day.date),
                'hover:bg-primary-50 dark:hover:bg-primary-900': !isSelectedDate(day.date) && day.isCurrentMonth,
                'text-gray-400': !day.isCurrentMonth,
                'font-semibold': isToday(day.date)
              }"
              class="h-8 rounded text-sm text-gray-900 dark:text-white disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ day.day }}
            </button>
          </div>
        </div>

        <!-- Time Tab -->
        <div v-show="activeTab === 'time'">
          <div class="grid grid-cols-2 gap-4">
            <!-- Hours -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Hour
              </label>
              <div class="max-h-48 overflow-y-auto border border-gray-300 rounded dark:border-gray-600">
                <button
                  v-for="hour in availableHours"
                  :key="hour"
                  type="button"
                  @click="selectHour(hour)"
                  :class="{
                    'bg-primary-50 text-primary-600 dark:bg-primary-900 dark:text-primary-400': hour === selectedHour
                  }"
                  class="w-full px-3 py-2 text-sm text-left hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
                >
                  {{ padZero(hour) }}
                </button>
              </div>
            </div>

            <!-- Minutes -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Minute
              </label>
              <div class="max-h-48 overflow-y-auto border border-gray-300 rounded dark:border-gray-600">
                <button
                  v-for="minute in availableMinutes"
                  :key="minute"
                  type="button"
                  @click="selectMinute(minute)"
                  :class="{
                    'bg-primary-50 text-primary-600 dark:bg-primary-900 dark:text-primary-400': minute === selectedMinute
                  }"
                  class="w-full px-3 py-2 text-sm text-left hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
                >
                  {{ padZero(minute) }}
                </button>
              </div>
            </div>
          </div>

          <!-- Timezone (if enabled) -->
          <div v-if="withTimezone" class="mt-4">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Timezone
            </label>
            <select
              v-model="selectedTimezone"
              @change="updateValue"
              class="block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option v-for="tz in timezones" :key="tz" :value="tz">
                {{ tz }}
              </option>
            </select>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            @click="setNow"
            class="flex-1 px-3 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded dark:text-primary-400 dark:hover:bg-primary-900"
          >
            Now
          </button>
          <button
            type="button"
            @click="clearDateTime"
            class="flex-1 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Clear
          </button>
          <button
            type="button"
            @click="confirmSelection"
            class="flex-1 px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded"
          >
            OK
          </button>
        </div>
      </div>
    </div>

    <!-- Hidden Input -->
    <input type="hidden" :name="name" :value="modelValue" />
  </div>
</template>

<script>
export default {
  name: 'LaraviltDateTimePicker',

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    name: {
      type: String,
      required: true
    },
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    format: {
      type: String,
      default: 'Y-m-d H:i:s'
    },
    minDate: String,
    maxDate: String,
    withTimezone: {
      type: Boolean,
      default: false
    },
    timezone: {
      type: String,
      default: 'UTC'
    },
    minuteStep: {
      type: Number,
      default: 1
    }
  },

  emits: ['update:modelValue'],

  data() {
    return {
      showPicker: false,
      activeTab: 'date',
      currentDate: new Date(),
      selectedDate: null,
      selectedHour: 0,
      selectedMinute: 0,
      selectedTimezone: this.timezone,
      timezones: [
        'UTC',
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'Europe/London',
        'Europe/Paris',
        'Asia/Tokyo',
        'Asia/Shanghai',
        'Australia/Sydney'
      ]
    };
  },

  computed: {
    currentMonthYear() {
      return this.currentDate.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
      });
    },

    calendarDays() {
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startingDayOfWeek = firstDay.getDay();

      const days = [];

      // Previous month days
      const prevMonthLastDay = new Date(year, month, 0).getDate();
      for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        days.push({
          day: prevMonthLastDay - i,
          date: new Date(year, month - 1, prevMonthLastDay - i),
          isCurrentMonth: false
        });
      }

      // Current month days
      for (let i = 1; i <= daysInMonth; i++) {
        days.push({
          day: i,
          date: new Date(year, month, i),
          isCurrentMonth: true
        });
      }

      // Next month days
      const remainingDays = 42 - days.length;
      for (let i = 1; i <= remainingDays; i++) {
        days.push({
          day: i,
          date: new Date(year, month + 1, i),
          isCurrentMonth: false
        });
      }

      return days;
    },

    availableHours() {
      const hours = [];
      for (let i = 0; i < 24; i++) {
        hours.push(i);
      }
      return hours;
    },

    availableMinutes() {
      const minutes = [];
      for (let i = 0; i < 60; i += this.minuteStep) {
        minutes.push(i);
      }
      return minutes;
    },

    displayValue() {
      if (!this.modelValue) return '';

      const date = new Date(this.modelValue);
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: this.selectedTimezone
      });
    }
  },

  watch: {
    modelValue: {
      immediate: true,
      handler(value) {
        if (value) {
          this.parseDateTime(value);
        }
      }
    }
  },

  methods: {
    parseDateTime(datetime) {
      const date = new Date(datetime);
      this.selectedDate = date;
      this.selectedHour = date.getHours();
      this.selectedMinute = date.getMinutes();
      this.currentDate = new Date(date.getFullYear(), date.getMonth(), 1);
    },

    previousMonth() {
      this.currentDate = new Date(
        this.currentDate.getFullYear(),
        this.currentDate.getMonth() - 1,
        1
      );
    },

    nextMonth() {
      this.currentDate = new Date(
        this.currentDate.getFullYear(),
        this.currentDate.getMonth() + 1,
        1
      );
    },

    selectDate(date) {
      this.selectedDate = date;
      this.activeTab = 'time';
      this.updateValue();
    },

    selectHour(hour) {
      this.selectedHour = hour;
      this.updateValue();
    },

    selectMinute(minute) {
      this.selectedMinute = minute;
      this.updateValue();
    },

    updateValue() {
      if (!this.selectedDate) return;

      const datetime = new Date(this.selectedDate);
      datetime.setHours(this.selectedHour);
      datetime.setMinutes(this.selectedMinute);
      datetime.setSeconds(0);

      // Format as ISO 8601
      const value = datetime.toISOString().slice(0, 19).replace('T', ' ');

      this.$emit('update:modelValue', value);
    },

    setNow() {
      const now = new Date();
      this.selectedDate = now;
      this.selectedHour = now.getHours();
      this.selectedMinute = now.getMinutes();
      this.currentDate = new Date(now.getFullYear(), now.getMonth(), 1);
      this.updateValue();
    },

    clearDateTime() {
      this.selectedDate = null;
      this.selectedHour = 0;
      this.selectedMinute = 0;
      this.$emit('update:modelValue', '');
      this.showPicker = false;
    },

    confirmSelection() {
      this.showPicker = false;
    },

    isSelectedDate(date) {
      if (!this.selectedDate) return false;
      return (
        date.getDate() === this.selectedDate.getDate() &&
        date.getMonth() === this.selectedDate.getMonth() &&
        date.getFullYear() === this.selectedDate.getFullYear()
      );
    },

    isToday(date) {
      const today = new Date();
      return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
      );
    },

    padZero(num) {
      return String(num).padStart(2, '0');
    }
  }
};
</script>

<style scoped>
.laravilt-datetime-picker {
  position: relative;
}
</style>

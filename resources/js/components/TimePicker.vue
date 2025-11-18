<template>
  <div class="laravilt-time-picker">
    <div class="relative">
      <!-- Time Input -->
      <input
        type="text"
        :value="displayValue"
        @click="showPicker = !showPicker"
        :placeholder="placeholder || (format24Hour ? 'HH:MM' : 'HH:MM AM/PM')"
        :required="required"
        :disabled="disabled"
        readonly
        class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 pr-10"
      />

      <!-- Clock Icon -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>

    <!-- Time Picker Dropdown -->
    <div
      v-show="showPicker"
      @click.away="showPicker = false"
      class="absolute z-50 mt-2 w-72 origin-top-left rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800"
    >
      <div class="p-4">
        <!-- Hour and Minute Selectors -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <!-- Hours -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
              {{ format24Hour ? 'Hour' : 'Hour' }}
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
                {{ formatHour(hour) }}
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

        <!-- Seconds (if enabled) -->
        <div v-if="withSeconds" class="mb-4">
          <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
            Second
          </label>
          <div class="max-h-32 overflow-y-auto border border-gray-300 rounded dark:border-gray-600">
            <button
              v-for="second in availableSeconds"
              :key="second"
              type="button"
              @click="selectSecond(second)"
              :class="{
                'bg-primary-50 text-primary-600 dark:bg-primary-900 dark:text-primary-400': second === selectedSecond
              }"
              class="w-full px-3 py-2 text-sm text-left hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
            >
              {{ padZero(second) }}
            </button>
          </div>
        </div>

        <!-- AM/PM Selector (12-hour format) -->
        <div v-if="!format24Hour" class="flex gap-2 mb-4">
          <button
            type="button"
            @click="selectPeriod('AM')"
            :class="{
              'bg-primary-600 text-white': period === 'AM',
              'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': period !== 'AM'
            }"
            class="flex-1 px-4 py-2 text-sm font-medium rounded-lg"
          >
            AM
          </button>
          <button
            type="button"
            @click="selectPeriod('PM')"
            :class="{
              'bg-primary-600 text-white': period === 'PM',
              'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': period !== 'PM'
            }"
            class="flex-1 px-4 py-2 text-sm font-medium rounded-lg"
          >
            PM
          </button>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            @click="setCurrentTime"
            class="flex-1 px-3 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded dark:text-primary-400 dark:hover:bg-primary-900"
          >
            Now
          </button>
          <button
            type="button"
            @click="clearTime"
            class="flex-1 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Clear
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
  name: 'LaraviltTimePicker',

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
    format24Hour: {
      type: Boolean,
      default: false
    },
    step: {
      type: Number,
      default: 1
    },
    minTime: String,
    maxTime: String,
    withSeconds: {
      type: Boolean,
      default: false
    }
  },

  emits: ['update:modelValue'],

  data() {
    return {
      showPicker: false,
      selectedHour: null,
      selectedMinute: 0,
      selectedSecond: 0,
      period: 'AM'
    };
  },

  computed: {
    availableHours() {
      const max = this.format24Hour ? 23 : 12;
      const min = this.format24Hour ? 0 : 1;
      const hours = [];
      for (let i = min; i <= max; i++) {
        hours.push(i);
      }
      return hours;
    },

    availableMinutes() {
      const minutes = [];
      for (let i = 0; i < 60; i += this.step) {
        minutes.push(i);
      }
      return minutes;
    },

    availableSeconds() {
      const seconds = [];
      for (let i = 0; i < 60; i++) {
        seconds.push(i);
      }
      return seconds;
    },

    displayValue() {
      if (!this.modelValue) return '';

      const parts = this.modelValue.split(':');
      let hour = parseInt(parts[0]);
      const minute = parts[1];
      const second = parts[2];

      if (!this.format24Hour) {
        const period = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        const timeStr = `${this.padZero(hour)}:${minute}`;
        return this.withSeconds ? `${timeStr}:${second} ${period}` : `${timeStr} ${period}`;
      }

      const timeStr = `${this.padZero(hour)}:${minute}`;
      return this.withSeconds ? `${timeStr}:${second}` : timeStr;
    }
  },

  watch: {
    modelValue: {
      immediate: true,
      handler(value) {
        if (value) {
          this.parseTime(value);
        }
      }
    }
  },

  methods: {
    parseTime(time) {
      const parts = time.split(':');
      let hour = parseInt(parts[0]);
      this.selectedMinute = parseInt(parts[1]);
      this.selectedSecond = parseInt(parts[2] || 0);

      if (!this.format24Hour) {
        this.period = hour >= 12 ? 'PM' : 'AM';
        this.selectedHour = hour % 12 || 12;
      } else {
        this.selectedHour = hour;
      }
    },

    selectHour(hour) {
      this.selectedHour = hour;
      this.updateValue();
    },

    selectMinute(minute) {
      this.selectedMinute = minute;
      this.updateValue();
    },

    selectSecond(second) {
      this.selectedSecond = second;
      this.updateValue();
    },

    selectPeriod(period) {
      this.period = period;
      this.updateValue();
    },

    updateValue() {
      if (this.selectedHour === null) return;

      let hour = this.selectedHour;
      if (!this.format24Hour) {
        if (this.period === 'PM' && hour !== 12) {
          hour += 12;
        } else if (this.period === 'AM' && hour === 12) {
          hour = 0;
        }
      }

      const timeStr = `${this.padZero(hour)}:${this.padZero(this.selectedMinute)}`;
      const value = this.withSeconds ? `${timeStr}:${this.padZero(this.selectedSecond)}` : timeStr;

      this.$emit('update:modelValue', value);
    },

    setCurrentTime() {
      const now = new Date();
      this.selectedHour = this.format24Hour ? now.getHours() : (now.getHours() % 12 || 12);
      this.selectedMinute = now.getMinutes();
      this.selectedSecond = now.getSeconds();
      this.period = now.getHours() >= 12 ? 'PM' : 'AM';
      this.updateValue();
    },

    clearTime() {
      this.selectedHour = null;
      this.selectedMinute = 0;
      this.selectedSecond = 0;
      this.period = 'AM';
      this.$emit('update:modelValue', '');
      this.showPicker = false;
    },

    formatHour(hour) {
      return this.padZero(hour);
    },

    padZero(num) {
      return String(num).padStart(2, '0');
    }
  }
};
</script>

<style scoped>
.laravilt-time-picker {
  position: relative;
}
</style>

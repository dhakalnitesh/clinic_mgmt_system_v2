<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  provinces: { type: Array, default: () => [] },
  districts: { type: Array, default: () => [] },
  municipals: { type: Array, default: () => [] },
  form: { type: Object, required: true }
})

const filteredDistricts = computed(() =>
  props.districts.filter(d => d.province_id == props.form.province_id)
)

const filteredMunicipals = computed(() =>
  props.municipals.filter(m => m.district_id == props.form.district_id)
)

watch(() => props.form.province_id, () => {
  props.form.district_id = ''
  props.form.municipal_id = ''
})

watch(() => props.form.district_id, () => {
  props.form.municipal_id = ''
})
</script>

<template>
  <div class="space-y-4">
    <!-- Citizenship Type -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Citizenship Type
      </label>
      <select v-model="form.citizenship_type"
        class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500">
        <option value="">Select Citizenship</option>
        <option value="nepali">Nepali Citizen</option>
        <option value="foreign">Foreign Citizen</option>
      </select>
    </div>

    <!-- Province / District / Municipality Grid (hidden for foreign) -->
    <div v-if="form.citizenship_type !== 'foreign'" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Province</label>
        <select v-model="form.province_id"
          class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500">
          <option value="">Select Province</option>
          <option v-for="p in provinces" :key="p.id" :value="p.id">
            {{ p.province_name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">District</label>
        <select v-model="form.district_id"
          class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500">
          <option value="">Select District</option>
          <option v-for="d in filteredDistricts" :key="d.id" :value="d.id">
            {{ d.district_name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Municipality</label>
        <select v-model="form.municipal_id"
          class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500">
          <option value="">Select Municipality</option>
          <option v-for="m in filteredMunicipals" :key="m.id" :value="m.id">
            {{ m.municipal_name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Simple address input for foreign citizens -->
    <div v-if="form.citizenship_type === 'foreign'" class="mt-3">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
      <input v-model="form.foreign_address" type="text" placeholder="Full address (country, city, etc.)"
        class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
    </div>

    <!-- Certificate fields (nullable) -->
    <div v-if="form.citizenship_type">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        {{ form.citizenship_type === 'nepali' ? 'Citizenship Number (Optional)' : 'Passport / ID Number (Optional)' }}
      </label>
      <input v-model="form.certificate_number" type="text"
        class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
        placeholder="Optional document number" />
    </div>
  </div>
</template>

<template>
  <!-- Edit passes the existing supplier to the shared form component -->
  <SupplierForm :supplier="supplier" />
</template>

<script setup>
import SupplierForm from '@/Pages/Pharmacy/Suppliers/Create.vue'
defineProps({ supplier: Object })
</script>
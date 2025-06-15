<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  userName: String,
  balance: Number,
})

const currentTime = ref('')

onMounted(() => {
  const now = new Date()

  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const dayName = days[now.getDay()]

  const tanggal = now.getDate()
  const bulan = now.getMonth() + 1
  const tahun = now.getFullYear()

  const jam = now.getHours().toString().padStart(2, '0')
  const menit = now.getMinutes().toString().padStart(2, '0')
  const detik = now.getSeconds().toString().padStart(2, '0')

  currentTime.value = `${dayName}, ${tanggal}-${bulan}-${tahun} ${jam}:${menit}:${detik}`
})
</script>

<template>
  <Head title="Balance" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Cek Saldo
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 space-y-2">
            <p><strong>Time:</strong> {{ currentTime }}</p>
            <p><strong>User ID:</strong> {{ props.userName }}</p>
            <p>Rp {{ props.balance.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const kursUSD = ref(null)
const kursJual = ref(null)
const kursBeli = ref(null)
const showKurs = ref(false)

const props = defineProps({
  userName: String,
  balance: Number,
})

function toggleKurs() {
  showKurs.value = !showKurs.value
}

const currentTime = ref('')
const showBalance = ref(false)

onMounted(async () => {
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

  try {
    const res = await fetch(`https://api.frankfurter.app/latest?from=USD&to=IDR`)
    const data = await res.json()
    kursUSD.value = data.rates.IDR
    kursJual.value = kursUSD.value + 50
    kursBeli.value = kursUSD.value - 100
  } catch (e) {
    kursUSD.value = 'Error. Try Again Later'
  }
})

function toggleBalance() {
  showBalance.value = !showBalance.value
}

const hiddenBalance = computed(() => {
  const rawNumber = props.balance.toFixed(2).replace(/\D/g, '') 
  return 'Rp ' + '●'.repeat(rawNumber.length)
})
</script>

<template>
  <Head title="Balance" />
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 space-y-4">
            <p><strong>Time:</strong> {{ currentTime }}</p>
            <p><strong>User ID:</strong> {{ props.userName }}</p>

            <div>
              <p class="text-3xl font-bold text-green-600">
                <template v-if="showBalance">
                  Rp {{ props.balance.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
                </template>
                <template v-else>
                  {{ hiddenBalance }}
                </template>
              </p>

              <button
                @click="toggleBalance"
                class="mt-6 rounded bg-green-600 px-3 py-1 text-sm text-white hover:bg-green-700"
              >
                {{ showBalance ? 'Hide' : 'Show' }}
              </button>
            </div>

            <Link
              href="/my-balance/mutation"
              class="w-full border border-gray-300 rounded px-4 py-3 text-left text-black bg-transparent hover:bg-gray-50 font-semibold block"
            >
              Check Mutation
            </Link>


            <button
              @click="toggleKurs"
              class="w-full flex items-center justify-between border border-gray-300 rounded px-4 py-3 text-left text-black bg-transparent hover:bg-gray-50"
              >
              <span class="font-semibold">Exchange Rate</span>
              <span class="text-xl font-bold">V</span>
            </button>
            <transition name="slide-fade">
              <div v-if="showKurs" class="mt-4 border-t pt-4 text-black text-sm space-y-1">
                <p>-IDR to USD-</p>
                <p>
                  Kurs Jual :
                  <strong class="text-black">Rp {{ kursJual?.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}</strong>
                </p>
                <p>
                  Kurs Beli :
                  <strong class="text-black">Rp {{ kursBeli?.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}</strong>
                </p>
              </div>
            </transition>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

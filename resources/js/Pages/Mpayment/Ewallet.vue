<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import html2canvas from 'html2canvas';

const form = ref({
  wallet_type: 'DANA',
  wallet_number: '',
  amount: ''
});

const props = defineProps({
  user: Object,
  recentTransactions: Array,
});

const isLoading = ref(false);
const errorMsg = ref('');
const showResult = ref(false);
const transactionResult = ref(null);

const submit = async () => {
  errorMsg.value = '';
  isLoading.value = true;
  try {
    await axios.get('/sanctum/csrf-cookie');
    const res = await axios.post('/e-wallet/transfer', form.value, {
      withCredentials: true,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });
    transactionResult.value = res.data.data;
    showResult.value = true;
    form.value.wallet_number = '';
    form.value.amount = '';
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Terjadi kesalahan.';
  } finally {
    isLoading.value = false;
  }
};

const formatCurrency = (num) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);

const downloadStrukAsImage = async () => {
  const el = document.getElementById('print-area');
  if (!el) return;
  const canvas = await html2canvas(el);
  const link = document.createElement('a');
  link.href = canvas.toDataURL('image/png');
  link.download = `struk-ewallet-${Date.now()}.png`;
  link.click();
};

const closeResult = () => {
  showResult.value = false;
  transactionResult.value = null;
};
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Transfer E-Wallet - M-Payment" />
    <div class="min-h-screen bg-gray-50 p-6">
      <div class="mx-auto max-w-md">
        <div class="mb-6 text-center">
          <h1 class="mb-2 text-2xl font-bold text-gray-900">Transfer E-Wallet</h1>
          <p class="text-gray-600">Halo {{ props.user?.name }}, silahkan pilih jenis transaksi</p>
        </div>

        <div class="rounded-lg bg-white p-6 shadow-sm">
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Jenis E-Wallet</label>
              <select v-model="form.wallet_type" class="w-full rounded border p-2">
                <option>DANA</option>
                <option>OVO</option>
                <option>GoPay</option>
                <option>ShopeePay</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Nomor Tujuan</label>
              <input type="text" v-model="form.wallet_number" class="w-full border rounded px-3 py-2" required />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Nominal</label>
              <input type="number" v-model="form.amount" class="w-full border rounded px-3 py-2" required min="1000" />
            </div>

            <div v-if="errorMsg" class="text-red-600 text-sm">{{ errorMsg }}</div>

            <button
              type="submit"
              class="w-full rounded bg-green-600 py-2 text-white font-medium hover:bg-green-700 transition"
              :disabled="isLoading"
            >
              <span v-if="isLoading">Memproses...</span>
              <span v-else>Bayar</span>
            </button>
          </form>
        </div>

        <!-- Riwayat Transaksi -->
        <div
          v-if="props.recentTransactions?.length > 0"
          class="mt-6 rounded-lg bg-white p-4 shadow-sm"
        >
          <h4 class="mb-3 font-medium text-gray-900">Transaksi Terakhir</h4>
          <div class="space-y-2">
            <div
              v-for="transaction in props.recentTransactions"
              :key="transaction.id"
              class="flex items-center justify-between border-b border-gray-100 py-2 last:border-b-0"
            >
              <div>
                <p class="text-sm font-medium">
                  EWT-{{ transaction.id.toString().padStart(6, '0') }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ new Date(transaction.created_at).toLocaleDateString('id-ID') }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium">
                  {{ formatCurrency(transaction.amount) }}
                </p>
                <span
                  class="rounded-full px-2 py-1 text-xs"
                  :class="{
                    'bg-green-100 text-green-800': transaction.status === 'success',
                    'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                    'bg-red-100 text-red-800': transaction.status === 'failed',
                  }"
                >
                  {{ transaction.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <!-- END Riwayat -->
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showResult"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    >
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <div class="mb-4 text-center">
          <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900">Pembayaran Berhasil!</h3>
          <p class="mt-1 text-sm text-gray-600">Transfer e-wallet berhasil diproses</p>
        </div>

        <div id="print-area" class="mb-4 rounded-lg bg-gray-50 p-4">
          <div class="space-y-2 text-sm">
            <div>
              <span class="font-medium">Jenis E-Wallet:</span> {{ transactionResult.wallet_type }}
            </div>
            <div>
              <span class="font-medium">No Tujuan:</span> {{ transactionResult.wallet_number }}
            </div>
            <div>
              <span class="font-medium">Nominal:</span> {{ formatCurrency(transactionResult.amount) }}
            </div>
            <div>
              <span class="font-medium">Tanggal:</span>
              {{ new Date(transactionResult.created_at).toLocaleString('id-ID') }}
            </div>
            <div>
              <span class="font-medium">Status:</span> {{ transactionResult.status }}
            </div>
          </div>
        </div>

        <div class="flex space-x-3">
          <button @click="closeResult" class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700">
            Selesai
          </button>
          <button
            @click="downloadStrukAsImage"
            class="flex-1 rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200"
          >
            Download Struk
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

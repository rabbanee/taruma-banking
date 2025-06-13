<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { debounce } from 'lodash';
import html2canvas from 'html2canvas';

//---------- Download/ss struk ----------
const downloadStrukAsImage = async () => {
    const element = document.getElementById('print-area');
    if (!element) return;

    // Ambil tampilan elemen sebagai gambar
    const canvas = await html2canvas(element);
    const imageData = canvas.toDataURL('image/png');

    // link download
    const link = document.createElement('a');
    link.href = imageData;
    link.download = `struk-pln-${Date.now()}.png`;
    link.click();
};

//-------------------------------------------------------------------
const props = defineProps({
    user: Object,
    recentTransactions: Array,
});

// --- STATE REAKTIF ---
const token = ref('');
const selectedAmount = ref('');
const customAmount = ref('');
const isLoading = ref(false);
const showResult = ref(false);
const transactionResult = ref(null);
const tokenInput = ref(null);

// Daftar nominal Harga Pln
const amounts = [
    { value: '20000', label: 'Rp 20.000' },
    { value: '50000', label: 'Rp 50.000' },
    { value: '100000', label: 'Rp 100.000' },
    { value: 'custom', label: 'Nominal Lain' },
];

// --- ERROR HANDLING ---

const currentError = ref('');

// --- COMPUTED PROPERTIES ---
const validateToken = (token) => {
    if (!token) return { valid: false, message: 'Masukkan nomor token' };
    if (token.length !== 12)
        return { valid: false, message: 'Token harus 12 digit' };
    if (!/^\d+$/.test(token))
        return { valid: false, message: 'Token harus angka saja' };
    return { valid: true, message: 'Token valid' };
};

const validateAmount = (amount) => {
    const numAmount = parseInt(amount);
    if (isNaN(numAmount))
        return { valid: false, message: 'Masukkan nominal yang valid' };
    if (numAmount < 20000)
        return { valid: false, message: 'Minimum Rp 20.000' };
    return { valid: true, message: 'Nominal valid' };
};

const tokenValidation = computed(() => validateToken(token.value));
const amountValidation = computed(() => {
    return selectedAmount.value === 'custom'
        ? validateAmount(customAmount.value)
        : validateAmount(selectedAmount.value);
});

const finalAmount = computed(() => {
    return selectedAmount.value === 'custom'
        ? customAmount.value
        : selectedAmount.value;
});

const canProceed = computed(() => {
    return tokenValidation.value.valid && amountValidation.value.valid;
});

// --- METHODS ---
const formatTokenInput = debounce(() => {
    token.value = token.value.replace(/\D/g, '').slice(0, 12);
}, 300);

const formatCustomAmount = () => {
    customAmount.value = customAmount.value.replace(/\D/g, '');
};

const formatCurrency = (value) => {
    if (!value) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const resetForm = () => {
    token.value = '';
    selectedAmount.value = '';
    customAmount.value = '';
    currentError.value = '';
};

const closeResult = () => {
    showResult.value = false;
    transactionResult.value = null;
};

const handleSubmit = async () => {
    if (!canProceed.value) return;

    isLoading.value = true;
    currentError.value = '';

    // Pastikan user sudah login
    if (!props.user) {
        currentError.value = 'Silakan login terlebih dahulu';
        isLoading.value = false;
        return router.visit('/login');
    }

    try {
        const response = await axios.post(
            './pln/purchase',
            {
                token_number: token.value,
                amount: parseInt(finalAmount.value),
            },
            {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                withCredentials: true,
            },
        );

        if (response.data.success) {
            transactionResult.value = response.data.data;
            showResult.value = true;
            resetForm();
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        if (error.response?.status === 401) {
            currentError.value = 'Session habis, silakan login kembali';
            router.visit('/login');
        } else {
            currentError.value =
                error.response?.data?.message ||
                error.message ||
                'Terjadi kesalahan';
        }
        console.error('Error:', error);
    } finally {
        isLoading.value = false;
    }
};

// Auto-focus input token saat komponen dimuat
onMounted(() => {
    if (tokenInput.value) {
        tokenInput.value.focus();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="PLN Pascabayar - M-Payment" />

        <div class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-md">
                <div class="mb-6 text-center">
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">
                        PLN Pascabayar
                    </h1>
                    <p class="text-gray-600">
                        Halo {{ props.user?.name }}, isi token listrik dengan
                        mudah di Taruna-Banking
                    </p>
                </div>

                <!-- Form Pembelian -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <!-- Input Token -->
                        <div>
                            <label
                                for="token"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Nomor Token Listrik
                            </label>
                            <input
                                id="token"
                                ref="tokenInput"
                                v-model="token"
                                @input="formatTokenInput"
                                type="text"
                                placeholder="Masukkan 12 digit nomor token"
                                maxlength="12"
                                class="w-full rounded-lg border px-4 py-3 text-center font-mono text-lg tracking-wider focus:outline-none focus:ring-2"
                                :class="{
                                    'border-green-500 bg-green-50 focus:ring-green-200':
                                        tokenValidation.valid &&
                                        token.length > 0,
                                    'border-red-500 bg-red-50 focus:ring-red-200':
                                        !tokenValidation.valid &&
                                        token.length > 0,
                                    'border-gray-300 focus:border-green-500 focus:ring-green-200':
                                        token.length === 0,
                                }"
                            />
                            <div
                                class="mt-1 text-sm"
                                :class="{
                                    'text-green-600':
                                        tokenValidation.valid &&
                                        token.length > 0,
                                    'text-red-600':
                                        !tokenValidation.valid &&
                                        token.length > 0,
                                    'text-gray-500': token.length === 0,
                                }"
                            >
                                {{
                                    token.length > 0
                                        ? tokenValidation.message
                                        : `${token.length}/12 digit`
                                }}
                            </div>
                        </div>

                        <!-- Pilihan Nominal -->
                        <div>
                            <label
                                class="mb-3 block text-sm font-medium text-gray-700"
                            >
                                Pilih Nominal Pembelian
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="amount in amounts"
                                    :key="amount.value"
                                    type="button"
                                    @click="selectedAmount = amount.value"
                                    class="rounded-lg border-2 p-3 text-center font-medium transition-all duration-200"
                                    :class="{
                                        'border-green-500 bg-blue-50 text-green-700':
                                            selectedAmount === amount.value,
                                        'border-gray-200 bg-white text-gray-700 hover:border-gray-300':
                                            selectedAmount !== amount.value,
                                    }"
                                >
                                    {{ amount.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Input Nominal Kustom -->
                        <div
                            v-if="selectedAmount === 'custom'"
                            class="animate-slide-down"
                        >
                            <label
                                for="customAmount"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Masukkan Nominal (Min. Rp 20.000)
                            </label>
                            <input
                                id="customAmount"
                                v-model="customAmount"
                                @input="formatCustomAmount"
                                type="text"
                                placeholder="20000"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-300"
                            />
                            <div
                                class="mt-1 text-sm"
                                :class="{
                                    'text-green-600':
                                        amountValidation.valid && customAmount,
                                    'text-red-600':
                                        !amountValidation.valid && customAmount,
                                    'text-gray-500': !customAmount,
                                }"
                            >
                                {{
                                    customAmount
                                        ? amountValidation.valid
                                            ? formatCurrency(customAmount)
                                            : amountValidation.message
                                        : 'Masukkan nominal'
                                }}
                            </div>
                        </div>

                        <!-- Ringkasan Pembelian -->
                        <div
                            v-if="tokenValidation.valid && selectedAmount"
                            class="rounded-lg bg-gray-50 p-4"
                        >
                            <h3 class="mb-2 font-medium text-gray-900">
                                Ringkasan Pembelian
                            </h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Token:</span>
                                    <span class="font-mono">{{ token }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Nominal:</span>
                                    <span class="font-medium">{{
                                        formatCurrency(finalAmount)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div
                            v-if="currentError"
                            class="rounded-lg bg-red-50 p-3 text-red-600"
                        >
                            {{ currentError }}
                        </div>

                        <!-- Tombol Submit -->
                        <button
                            type="submit"
                            :disabled="!canProceed || isLoading"
                            class="w-full rounded-lg py-3 font-medium text-white transition-all duration-200"
                            :class="{
                                'bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200':
                                    canProceed && !isLoading,
                                'cursor-not-allowed bg-gray-400':
                                    !canProceed || isLoading,
                            }"
                        >
                            <span
                                v-if="isLoading"
                                class="flex items-center justify-center"
                            >
                                <svg
                                    class="mr-2 h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                        fill="none"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                                Memproses...
                            </span>
                            <span v-else> Beli Token PLN </span>
                        </button>
                    </form>
                </div>

                <!-- Informasi Penting -->
                <div class="mt-6 rounded-lg bg-blue-50 p-4">
                    <h4 class="mb-2 font-medium text-green-900">
                        Informasi Penting:
                    </h4>
                    <ul class="space-y-1 text-sm text-green-800">
                        <li>• Token listrik akan dikirim via SMS</li>
                        <li>• Pembelian minimum Rp 20.000</li>
                        <li>• Proses pembelian maksimal 2 menit</li>
                        <li>• Download struk sebagai bukti pembelian</li>
                    </ul>
                </div>

                <!-- Riwayat Transaksi -->
                <div
                    v-if="props.recentTransactions?.length > 0"
                    class="mt-6 rounded-lg bg-white p-4 shadow-sm"
                >
                    <h4 class="mb-3 font-medium text-gray-900">
                        Transaksi Terakhir
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="transaction in props.recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between border-b border-gray-100 py-2 last:border-b-0"
                        >
                            <div>
                                <p class="text-sm font-medium">
                                    {{ transaction.transaction_id }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{
                                        new Date(
                                            transaction.created_at,
                                        ).toLocaleDateString('id-ID')
                                    }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">
                                    {{ formatCurrency(transaction.amount) }}
                                </p>
                                <span
                                    class="rounded-full px-2 py-1 text-xs"
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            transaction.status === 'success',
                                        'bg-yellow-100 text-yellow-800':
                                            transaction.status === 'pending',
                                        'bg-red-100 text-red-800':
                                            transaction.status === 'failed',
                                    }"
                                >
                                    {{ transaction.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Hasil Transaksi -->
        <div
            id="print-area"
            v-if="showResult"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <div class="mb-4 text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100"
                    >
                        <svg
                            class="h-6 w-6 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            ></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Pembelian Berhasil!
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Token PLN Anda sudah siap digunakan
                    </p>
                </div>

                <div class="mb-4 rounded-lg bg-gray-50 p-4">
                    <div class="space-y-3">
                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >ID Transaksi</label
                            >
                            <p class="font-mono text-sm text-gray-900">
                                {{ transactionResult?.transaction_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                            >
                                No Token :</label
                            >
                            <p
                                class="rounded border bg-white px-3 py-2 font-mono text-lg text-gray-900"
                            >
                                {{ formatToken(transactionResult?.token1) }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Energi Listrik</label
                            >
                            <p class="text-sm text-gray-900">
                                {{ transactionResult?.kwh }} kWh
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button
                        @click="closeResult"
                        class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700"
                    >
                        Selesai
                    </button>

                    <button
                        @click="downloadStrukAsImage"
                        class="flex-1 rounded-lg bg-gray-100 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-200"
                    >
                        Download Struk
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

// --- Pengubah angka token (-) ------
<script>
function formatToken(token) {
    return token?.replace(/(.{4})/g, '$1-').replace(/-$/, '') || '';
}
</script>

<style scoped>
.animate-slide-down {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

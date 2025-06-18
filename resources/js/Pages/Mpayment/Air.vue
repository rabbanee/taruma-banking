<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import html2canvas from 'html2canvas';

const downloadStrukAsImage = async () => {
    const element = document.getElementById('print-area');
    if (!element) return;
    const canvas = await html2canvas(element);
    const imageData = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.href = imageData;
    link.download = `struk-pdam-${Date.now()}.png`;
    link.click();
};

const props = defineProps({
    user: Object,
    recentTransactions: Array,
});

const customerNumber = ref('');
const isLoading = ref(false);
const showResult = ref(false);
const transactionResult = ref(null);
const customerInput = ref(null);
const currentError = ref('');

const validateCustomerNumber = (number) => {
    if (!number) return { valid: false, message: 'Masukkan nomor pelanggan' };
    if (!/^\d{6,12}$/.test(number))
        return { valid: false, message: 'Nomor tidak valid (6-12 digit)' };
    return { valid: true, message: 'Nomor pelanggan valid' };
};

const customerValidation = computed(() =>
    validateCustomerNumber(customerNumber.value),
);
const canProceed = computed(() => customerValidation.value.valid);

const formatCustomerInput = () => {
    customerNumber.value = customerNumber.value.replace(/\D/g, '').slice(0, 12);
};

const resetForm = () => {
    customerNumber.value = '';
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

    if (!props.user) {
        currentError.value = 'Silakan login terlebih dahulu';
        isLoading.value = false;
        return router.visit('/login');
    }

    try {
        const response = await axios.post(
            './pdam/purchase',
            {
                customer_number: customerNumber.value,
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
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pembayaran Air - M-Payment" />
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-md">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Pembayaran Air (PDAM)
                    </h1>
                    <p class="text-gray-600">
                        Bayar tagihan air dengan mudah bersama Taruna-Banking
                    </p>
                </div>

                <!-- Form -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700"
                                >Nomor Pelanggan</label
                            >
                            <input
                                v-model="customerNumber"
                                @input="formatCustomerInput"
                                type="text"
                                placeholder="Masukkan nomor pelanggan PDAM"
                                class="w-full rounded-lg border px-4 py-3 text-center font-mono text-lg tracking-wider focus:outline-none focus:ring-2"
                                :class="{
                                    'border-green-500 bg-green-50 focus:ring-green-200':
                                        customerValidation.valid &&
                                        customerNumber,
                                    'border-red-500 bg-red-50 focus:ring-red-200':
                                        !customerValidation.valid &&
                                        customerNumber,
                                    'border-gray-300 focus:border-green-500 focus:ring-green-200':
                                        !customerNumber,
                                }"
                            />
                            <div
                                class="mt-1 text-sm"
                                :class="{
                                    'text-green-600':
                                        customerValidation.valid &&
                                        customerNumber,
                                    'text-red-600':
                                        !customerValidation.valid &&
                                        customerNumber,
                                    'text-gray-500': !customerNumber,
                                }"
                            >
                                {{
                                    customerNumber
                                        ? customerValidation.message
                                        : '6-12 digit angka'
                                }}
                            </div>
                        </div>

                        <div
                            v-if="currentError"
                            class="rounded-lg bg-red-50 p-3 text-red-600"
                        >
                            {{ currentError }}
                        </div>

                        <button
                            type="submit"
                            :disabled="!canProceed || isLoading"
                            class="w-full rounded-lg py-3 font-medium text-white transition-all duration-200"
                            :class="{
                                'bg-green-600 hover:bg-green-700':
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
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                    />
                                </svg>
                                Memproses...
                            </span>
                            <span v-else>Bayar Tagihan</span>
                        </button>
                    </form>
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
                                    {{
                                        new Intl.NumberFormat('id-ID', {
                                            style: 'currency',
                                            currency: 'IDR',
                                        }).format(transaction.amount)
                                    }}
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
                            />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Pembayaran Berhasil!
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Tagihan air telah dibayarkan
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
                                >Nomor Pelanggan</label
                            >
                            <p class="font-mono text-sm text-gray-900">
                                {{ transactionResult?.customer_number }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Jumlah Tagihan</label
                            >
                            <p class="text-sm text-gray-900">
                                {{
                                    new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                    }).format(transactionResult?.amount)
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Periode</label
                            >
                            <p class="text-sm text-gray-900">
                                {{ transactionResult?.period || '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button
                        @click="closeResult"
                        class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                    >
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

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { debounce } from 'lodash';
import html2canvas from 'html2canvas';

//---------- Download/ss struk ----------
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

//-------------------------------------------------------------------

const { props } = usePage();

// --- STATE REAKTIF ---
const customerNumber = ref('');
const selectedAmount = ref('');
const customAmount = ref('');
const selectedRegion = ref('');
const selectedDistrict = ref('');
const isLoading = ref(false);
const showResult = ref(false);
const transactionResult = ref(null);
const customerNumberInput = ref(null);

// Daftar nominal Pembayaran
const amounts = [
    { value: '50000', label: 'Rp 50.000' },
    { value: '100000', label: 'Rp 100.000' },
    { value: '200000', label: 'Rp 200.000' },
    { value: 'custom', label: 'Nominal Lain' },
];

// Daftar wilayah PDAM
const pdamRegions = [
    {
        id: 'jabodetabek',
        name: 'Jabodetabek',
        districts: [
            { code: 'JKT01', name: 'PDAM Jaya - Jakarta Pusat' },
            { code: 'JKT02', name: 'PDAM Jaya - Jakarta Barat' },
            { code: 'JKT03', name: 'PDAM Jaya - Jakarta Selatan' },
            { code: 'JKT04', name: 'PDAM Jaya - Jakarta Timur' },
            { code: 'JKT05', name: 'PDAM Jaya - Jakarta Utara' },
            { code: 'DPK01', name: 'PDAM Kota Depok' },
            { code: 'BGR01', name: 'PDAM Kota Bogor' },
            { code: 'TGR01', name: 'PDAM Kab. Tangerang' },
        ],
    },
    {
        id: 'jabar',
        name: 'Jawa Barat',
        districts: [
            { code: 'BDG01', name: 'PDAM Kota Bandung' },
            { code: 'CRB01', name: 'PDAM Kota Cirebon' },
            { code: 'TSK01', name: 'PDAM Kota Tasikmalaya' },
            { code: 'BJR01', name: 'PDAM Kota Banjar' },
        ],
    },
    {
        id: 'jateng',
        name: 'Jawa Tengah',
        districts: [
            { code: 'SMG01', name: 'PDAM Kota Semarang' },
            { code: 'SRG01', name: 'PDAM Kota Surakarta' },
            { code: 'PWT01', name: 'PDAM Kota Purwokerto' },
            { code: 'TGL01', name: 'PDAM Kota Tegal' },
        ],
    },
    {
        id: 'jatim',
        name: 'Jawa Timur',
        districts: [
            { code: 'SBY01', name: 'PDAM Surya Sembada - Surabaya' },
            { code: 'MLG01', name: 'PDAM Kota Malang' },
            { code: 'JBR01', name: 'PDAM Kota Jember' },
            { code: 'MJK01', name: 'PDAM Kota Mojokerto' },
        ],
    },
    {
        id: 'bali',
        name: 'Bali',
        districts: [
            { code: 'DPS01', name: 'PDAM Denpasar' },
            { code: 'BAD01', name: 'PDAM Kab. Badung' },
            { code: 'GIY01', name: 'PDAM Kab. Gianyar' },
        ],
    },

    {
        id: 'NTB',
        name: 'Nusa Tenggara Barat',
        districts: [
            { code: 'NTB01', name: 'PDAM Kota Mataram' },
            { code: 'NTB02', name: 'PDAM Kota Sumbawa' },
            { code: 'NTB03', name: 'PDAM Kota Lombok' },
            { code: 'NTB04', name: 'PDAM Kota Bima' },
        ],
    },

    {
        id: 'DIY',
        name: 'Daerah Istimewa Yogyakarta',
        districts: [
            { code: 'YOG01', name: 'PDAM Kota Yogyakarta' },
            { code: 'YOG02', name: 'PDAM Kab. Sleman' },
            { code: 'YOG03', name: 'PDAM Kab. Bantul' },
            { code: 'YOG04', name: 'PDAM Kab. Gunungkidul' },
        ],
    },
];

// --- ERROR HANDLING ---
const currentError = ref('');

// --- COMPUTED PROPERTIES ---
const validateCustomerNumber = (number) => {
    if (!number) return { valid: false, message: 'Masukkan nomor pelanggan' };
    if (number.length < 6 || number.length > 10)
        return { valid: false, message: 'Nomor pelanggan 6-10 digit' };
    if (!/^\d+$/.test(number))
        return { valid: false, message: 'Hanya boleh angka' };
    return { valid: true, message: 'Nomor valid' };
};

const validateAmount = (amount) => {
    const numAmount = parseInt(amount);
    if (isNaN(numAmount))
        return { valid: false, message: 'Masukkan nominal yang valid' };
    if (numAmount < 20000)
        return { valid: false, message: 'Minimum Rp 20.000' };
    return { valid: true, message: 'Nominal valid' };
};

const customerNumberValidation = computed(() =>
    validateCustomerNumber(customerNumber.value),
);
const amountValidation = computed(() => {
    return selectedAmount.value === 'custom'
        ? validateAmount(customAmount.value)
        : validateAmount(selectedAmount.value);
});

const regionValidation = computed(() => {
    return selectedRegion.value
        ? { valid: true, message: 'Wilayah valid' }
        : { valid: false, message: 'Pilih wilayah PDAM' };
});

const districtValidation = computed(() => {
    return selectedDistrict.value
        ? { valid: true, message: 'Cabang valid' }
        : { valid: false, message: 'Pilih cabang PDAM' };
});

const finalAmount = computed(() => {
    return selectedAmount.value === 'custom'
        ? customAmount.value
        : selectedAmount.value;
});

const canProceed = computed(() => {
    return (
        customerNumberValidation.value.valid &&
        amountValidation.value.valid &&
        regionValidation.value.valid &&
        districtValidation.value.valid
    );
});

const filteredDistricts = computed(() => {
    if (!selectedRegion.value) return [];
    const region = pdamRegions.find((r) => r.id === selectedRegion.value);
    return region ? region.districts : [];
});

// --- METHODS ---
const formatCustomerNumberInput = debounce(() => {
    customerNumber.value = customerNumber.value.replace(/\D/g, '').slice(0, 12);
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
    customerNumber.value = '';
    selectedAmount.value = '';
    customAmount.value = '';
    selectedRegion.value = '';
    selectedDistrict.value = '';
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

    console.log('Sending payload:', {
        customer_number: customerNumber.value,
        amount: parseInt(finalAmount.value),
        region: selectedRegion.value,
        district_code: selectedDistrict.value,
    });

    try {
        const response = await axios.post(route('m-payment.air.purchase'), {
            customer_number: customerNumber.value,
            amount: parseInt(finalAmount.value),
            region: selectedRegion.value,
            district_code: selectedDistrict.value,
            paid_at: new Date().toISOString(),
        });

        if (response.data.success) {
            transactionResult.value = response.data.data;
            showResult.value = true;
            resetForm();
        } else {
            currentError.value =
                response.data.message || 'Pembayaran gagal diproses';
        }
    } catch (error) {
        console.error('Payment error:', error);
        if (error.response) {
            // Handle error validasi
            if (error.response.status === 422) {
                const errors = error.response.data.errors;
                currentError.value = Object.values(errors)[0][0];
            } else {
                currentError.value = 'Koneksi jaringan bermasalah';
                console.error(
                    'Payment error:',
                    error.response?.data || error.message,
                );
            }
        }
    } finally {
        isLoading.value = false;
    }
};
// Auto-focus input saat komponen dimuat
onMounted(() => {
    if (customerNumberInput.value) {
        customerNumberInput.value.focus();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Pembayaran PDAM - M-Payment" />

        <div class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-md">
                <div class="mb-6 text-center">
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">
                        Pembayaran PDAM
                    </h1>
                    <p class="text-gray-600">
                        Halo, {{ props.user?.name }} Bayar tagihan air dengan
                        mudah di Taruma-Banking
                    </p>
                </div>

                <!-- Form Pembayaran -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <!-- Input Nomor Pelanggan -->
                        <div>
                            <label
                                for="customerNumber"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Nomor Pelanggan PDAM
                            </label>
                            <input
                                id="customerNumber"
                                ref="customerNumberInput"
                                v-model="customerNumber"
                                @input="formatCustomerNumberInput"
                                type="text"
                                placeholder="Masukkan nomor pelanggan"
                                maxlength="10"
                                class="w-full rounded-lg border px-4 py-3 text-center font-mono text-lg tracking-wider focus:outline-none focus:ring-2"
                                :class="{
                                    'border-green-500 bg-green-50 focus:ring-green-200':
                                        customerNumberValidation.valid &&
                                        customerNumber.length > 0,
                                    'border-red-500 bg-red-50 focus:ring-red-200':
                                        !customerNumberValidation.valid &&
                                        customerNumber.length > 0,
                                    'border-gray-300 focus:border-green-500 focus:ring-green-200':
                                        customerNumber.length === 0,
                                }"
                            />
                            <div
                                class="mt-1 text-sm"
                                :class="{
                                    'text-green-600':
                                        customerNumberValidation.valid &&
                                        customerNumber.length > 0,
                                    'text-red-600':
                                        !customerNumberValidation.valid &&
                                        customerNumber.length > 0,
                                    'text-gray-500':
                                        customerNumber.length === 0,
                                }"
                            >
                                {{
                                    customerNumber.length > 0
                                        ? customerNumberValidation.message
                                        : 'Masukkan 6-10 digit'
                                }}
                            </div>
                        </div>

                        <!-- Pilihan Wilayah -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Pilih Wilayah PDAM
                            </label>
                            <select
                                v-model="selectedRegion"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200"
                                :class="{
                                    'border-green-500 bg-green-50':
                                        regionValidation.valid &&
                                        selectedRegion,
                                    'border-red-500 bg-red-50':
                                        !regionValidation.valid &&
                                        selectedRegion,
                                }"
                            >
                                <option value="" disabled selected>
                                    -- Pilih Wilayah --
                                </option>
                                <option
                                    v-for="region in pdamRegions"
                                    :key="region.id"
                                    :value="region.id"
                                >
                                    {{ region.name }}
                                </option>
                            </select>
                            <div
                                class="mt-1 text-sm"
                                :class="{
                                    'text-green-600':
                                        regionValidation.valid &&
                                        selectedRegion,
                                    'text-red-600':
                                        !regionValidation.valid &&
                                        selectedRegion,
                                    'text-gray-500': !selectedRegion,
                                }"
                            >
                                {{ regionValidation.message }}
                            </div>
                        </div>

                        <!-- Pilihan Cabang PDAM -->
                        <div v-if="selectedRegion">
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Pilih Cabang PDAM
                            </label>
                            <select
                                v-model="selectedDistrict"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-200"
                                :class="{
                                    'border-green-500 bg-green-50':
                                        districtValidation.valid &&
                                        selectedDistrict,
                                    'border-red-500 bg-red-50':
                                        !districtValidation.valid &&
                                        selectedDistrict,
                                }"
                            >
                                <option value="" disabled selected>
                                    -- Pilih Cabang --
                                </option>
                                <option
                                    v-for="district in filteredDistricts"
                                    :key="district.code"
                                    :value="district.code"
                                >
                                    {{ district.name }}
                                </option>
                            </select>
                            <div
                                class="mt-1 text-sm"
                                :class="{
                                    'text-green-600':
                                        districtValidation.valid &&
                                        selectedDistrict,
                                    'text-red-600':
                                        !districtValidation.valid &&
                                        selectedDistrict,
                                    'text-gray-500': !selectedDistrict,
                                }"
                            >
                                {{ districtValidation.message }}
                            </div>
                        </div>

                        <!-- Pilihan Nominal -->
                        <div>
                            <label
                                class="mb-3 block text-sm font-medium text-gray-700"
                            >
                                Pilih Nominal Pembayaran
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
                                Masukkan Nominal (Min. Rp 20.000 )
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

                        <!-- Ringkasan Pembayaran -->
                        <div
                            v-if="
                                customerNumberValidation.valid &&
                                selectedAmount &&
                                selectedDistrict
                            "
                            class="rounded-lg bg-gray-50 p-4"
                        >
                            <h3 class="mb-2 font-medium text-gray-900">
                                Ringkasan Pembayaran
                            </h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Nomor Pelanggan:</span>
                                    <span class="font-mono">{{
                                        customerNumber
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Cabang PDAM:</span>
                                    <span class="font-medium">
                                        {{
                                            pdamRegions.find(
                                                (r) => r.id === selectedRegion,
                                            )?.name
                                        }}
                                        -
                                        {{
                                            filteredDistricts.find(
                                                (d) =>
                                                    d.code === selectedDistrict,
                                            )?.name
                                        }}
                                    </span>
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
                            <span v-else> Bayar Tagihan PDAM </span>
                        </button>
                    </form>
                </div>

                <!-- Informasi Penting -->
                <div class="mt-6 rounded-lg bg-blue-50 p-4">
                    <h4 class="mb-2 font-medium text-green-900">
                        Informasi Penting:
                    </h4>
                    <ul class="space-y-1 text-sm text-green-800">
                        <li>
                            • Pastikan nomor pelanggan dan wilayah PDAM benar
                        </li>
                        <li>• Pembayaran minimum Rp 20.000</li>
                        <li>• Proses pembayaran maksimal 2 menit</li>
                        <li>• Download struk sebagai bukti pembayaran</li>
                        <li>
                            • Tagihan akan otomatis terupdate maksimal 1x24 jam
                        </li>
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
                        Pembayaran Berhasil!
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Tagihan PDAM Anda sudah terbayar
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
                                {{ transactionResult.district_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                            >
                                Nomor Pelanggan:</label
                            >
                            <p class="text-sm font-medium text-gray-900">
                                {{ transactionResult?.customer_number }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Cabang PDAM</label
                            >
                            <p class="text-sm text-gray-900">
                                {{ transactionResult?.district_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Tanggal Pembayaran</label
                            >
                            <p class="text-sm text-gray-900">
                                {{ transactionResult?.date }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Nominal</label
                            >
                            <p class="text-sm font-medium text-green-600">
                                {{ formatCurrency(transactionResult?.amount) }}
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

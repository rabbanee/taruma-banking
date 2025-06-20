<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import html2canvas from 'html2canvas';

const recipients = ref([]);
const selectedRecipient = ref(null);
const amount = ref('');
const success = ref('');
const error = ref('');
const history = ref([]);
const showModal = ref(false);
const transactionData = ref(null);

onMounted(() => {
    fetchRecipients();
    fetchHistory();
});

const fetchRecipients = async () => {
    try {
        const res = await axios.get('/mtransfer/bank-recipients');
        recipients.value = res.data;
    } catch {
        error.value = 'Gagal memuat daftar rekening bank.';
    }
};

const fetchHistory = async () => {
    try {
        const res = await axios.get('/mtransfer/interbank-history');
        history.value = res.data.slice(0, 5);
    } catch (err) {
        console.error('Gagal mengambil riwayat', err);
    }
};

const adminFee = computed(() => {
    if (!selectedRecipient.value || !amount.value) return 0;
    return selectedRecipient.value.bank_name.trim().toLowerCase() !==
        'taruma bank'
        ? Math.floor(amount.value * 0.03)
        : 0;
});

const total = computed(() => {
    return Number(amount.value) + adminFee.value;
});

const toRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val || 0);
};

const sendTransfer = async () => {
    error.value = '';
    success.value = '';

    if (
        !selectedRecipient.value ||
        !amount.value ||
        Number(amount.value) < 20000
    ) {
        error.value = 'Pilih rekening & jumlah minimal 20.000';
        return;
    }

    try {
        await axios.post('/mtransfer/interbank-transfer', {
            bank_recipient_id: selectedRecipient.value.id,
            amount: Number(amount.value),
        });

        transactionData.value = {
            bank_name: selectedRecipient.value.bank_name,
            account_name: selectedRecipient.value.account_name,
            account_number: selectedRecipient.value.account_number,
            amount: Number(amount.value),
            fee: adminFee.value,
            total: total.value,
            created_at: new Date().toLocaleString('id-ID'),
        };

        showModal.value = true;
        amount.value = '';
        selectedRecipient.value = null;
        await fetchHistory();
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal transfer';
    }
};

const downloadStruk = async () => {
    const element = document.getElementById('struk-area');
    if (!element) return;

    const canvas = await html2canvas(element);
    const link = document.createElement('a');
    link.download = 'struk-transfer.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
};

const handleAmountInput = () => {
    amount.value = amount.value.replace(/\D/g, '');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transfer Antar Bank" />

        <div
            class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-6"
        >
            <div
                class="w-full max-w-md space-y-5 rounded-xl bg-white p-6 shadow-lg"
            >
                <h1 class="text-center text-2xl font-bold text-gray-800">
                    Transfer Antar Bank
                </h1>

                <!-- Pilih rekening -->
                <div>
                    <label class="text-sm font-medium text-gray-700"
                        >Pilih Tujuan Bank</label
                    >
                    <div
                        class="max-h-48 overflow-y-auto rounded border bg-gray-50 p-2 text-sm"
                    >
                        <div
                            v-for="r in recipients"
                            :key="r.id"
                            @click="selectedRecipient = r"
                            :class="[
                                'cursor-pointer rounded px-3 py-2',
                                selectedRecipient?.id === r.id
                                    ? 'bg-green-200 font-semibold'
                                    : 'hover:bg-gray-100',
                            ]"
                        >
                            {{ r.bank_name }} - {{ r.account_name }} ({{
                                r.account_number
                            }})
                        </div>
                    </div>
                </div>

                <!-- Nominal -->
                <div>
                    <label class="text-sm font-medium text-gray-700"
                        >Jumlah Uang</label
                    >
                    <input
                        v-model="amount"
                        @input="handleAmountInput"
                        type="text"
                        placeholder="Min. 20000"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring"
                    />
                </div>

                <!-- Rekening Kita -->
                <div class="mb-4 rounded border bg-gray-100 p-3 text-sm">
                    <p class="font-medium">Rekening Anda</p>
                    <p>{{ myName }} - {{ myNumber }}</p>
                </div>

                <!-- Ringkasan -->
                <div class="space-y-1 text-sm text-gray-700">
                    <p>
                        Biaya Admin: <strong>{{ toRupiah(adminFee) }}</strong>
                    </p>
                    <p>
                        Total: <strong>{{ toRupiah(total) }}</strong>
                    </p>
                </div>

                <button
                    @click="sendTransfer"
                    class="w-full rounded bg-green-600 py-2 text-white transition hover:bg-green-700"
                >
                    Kirim Transfer
                </button>

                <!-- Feedback -->
                <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
                <p v-if="success" class="text-sm text-green-600">
                    {{ success }}
                </p>

                <!-- Riwayat -->
                <div>
                    <h2 class="mb-2 mt-4 text-sm font-semibold text-gray-700">
                        Riwayat Transfer Terakhir
                    </h2>
                    <div
                        class="max-h-48 overflow-y-auto rounded border bg-gray-50 p-2 text-sm"
                    >
                        <ul v-if="history.length">
                            <li
                                v-for="(h, i) in history"
                                :key="i"
                                class="mb-2 rounded border bg-white p-2"
                            >
                                {{ h.bank_recipient.bank_name }} -
                                {{ h.bank_recipient.account_name }}<br />
                                <span class="text-xs text-gray-500">
                                    Rp {{ h.amount.toLocaleString('id-ID') }} |
                                    {{
                                        new Date(h.created_at).toLocaleString(
                                            'id-ID',
                                        )
                                    }}
                                </span>
                            </li>
                        </ul>
                        <p v-else class="text-gray-500">
                            Belum ada transfer antar bank.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Struk -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-lg bg-white p-6 shadow-lg"
            >
                <div id="struk-area">
                    <h2
                        class="mb-4 text-center text-xl font-bold text-green-700"
                    >
                        ✅ Transfer Berhasil
                    </h2>

                    <div class="space-y-2 text-sm text-gray-800">
                        <p>
                            <strong>Bank:</strong>
                            {{ transactionData.bank_name }}
                        </p>
                        <p>
                            <strong>Nama:</strong>
                            {{ transactionData.account_name }}
                        </p>
                        <p>
                            <strong>Rekening:</strong>
                            {{ transactionData.account_number }}
                        </p>
                        <p>
                            <strong>Jumlah:</strong>
                            {{ toRupiah(transactionData.amount) }}
                        </p>
                        <p>
                            <strong>Biaya Admin:</strong>
                            {{ toRupiah(transactionData.fee) }}
                        </p>
                        <p>
                            <strong>Total:</strong>
                            {{ toRupiah(transactionData.total) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Waktu: {{ transactionData.created_at }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-4">
                    <button
                        @click="showModal = false"
                        class="rounded bg-gray-200 px-4 py-2 text-sm hover:bg-gray-300"
                    >
                        Tutup
                    </button>
                    <button
                        @click="downloadStruk"
                        class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700"
                    >
                        Download Struk
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

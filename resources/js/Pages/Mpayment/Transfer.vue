<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import html2canvas from 'html2canvas';

// State
const recipients = ref([]);
const selectedRecipient = ref(null);
const amount = ref('');
const error = ref('');
const success = ref('');
const history = ref([]);
const showResult = ref(false);
const strukData = ref(null);

// Format angka ke Rp
const formatToRupiah = (value) => {
    if (!value) return '';
    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// Fetch data
const fetchRecipients = async () => {
    const res = await axios.get('/mtransfer/recipients');
    recipients.value = res.data;
};

const fetchHistory = async () => {
    const res = await axios.get('/mtransfer/history');
    history.value = res.data;
};

// Transfer
const sendTransfer = async () => {
    error.value = '';
    success.value = '';

    const cleanAmount = parseInt(amount.value.replace(/\D/g, ''));

    if (!selectedRecipient.value || !amount.value || cleanAmount < 20000) {
        error.value = 'Minimal transfer Rp 20.000';
        return;
    }

    try {
        await axios.post('/mtransfer/send', {
            recipient_id: selectedRecipient.value.id,
            amount: cleanAmount,
        });

        success.value = 'Transfer berhasil!';
        strukData.value = {
            name: selectedRecipient.value.name,
            account_number: selectedRecipient.value.account_number,
            amount: cleanAmount,
            timestamp: new Date(),
        };
        showResult.value = true;
        selectedRecipient.value = null;
        amount.value = '';
        fetchHistory();
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal transfer';
    }
};

// Hapus rekening
const deleteRecipient = async (id) => {
    try {
        await axios.delete(`/mtransfer/recipients/${id}`);
        recipients.value = recipients.value.filter((r) => r.id !== id);
    } catch (err) {
        alert('Gagal menghapus rekening');
    }
};

// Download struk
const downloadStrukAsImage = async () => {
    const el = document.getElementById('print-area-content');
    if (!el) return;
    const canvas = await html2canvas(el);
    const dataUrl = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = `struk-transfer-${Date.now()}.png`;
    link.click();
};

onMounted(() => {
    fetchRecipients();
    fetchHistory();
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transfer" />
        <div
            class="flex min-h-screen items-start justify-center bg-gray-100 px-4 py-10"
        >
            <div
                class="w-full max-w-md space-y-6 rounded-xl bg-white p-6 shadow"
            >
                <h1 class="text-center text-xl font-bold text-gray-800">
                    Transfer
                </h1>

                <!-- PILIH REKENING -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Pilih Rekening Tujuan</label
                    >
                    <div
                        class="max-h-48 space-y-1 overflow-y-auto rounded border bg-gray-50 p-2"
                    >
                        <div
                            v-for="(item, index) in recipients"
                            :key="index"
                            class="flex items-center justify-between rounded p-2 hover:bg-gray-100"
                            :class="
                                selectedRecipient?.id === item.id
                                    ? 'bg-green-200 font-semibold'
                                    : ''
                            "
                        >
                            <div
                                class="flex-1 cursor-pointer"
                                @click="selectedRecipient = item"
                            >
                                {{ item.name }} - {{ item.account_number }}
                            </div>
                            <div class="flex items-center">
                                <button
                                    @click="deleteRecipient(item.id)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INPUT NOMINAL -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Jumlah Uang</label
                    >
                    <input
                        v-model="amount"
                        type="text"
                        class="w-full rounded border px-3 py-2 focus:outline-none focus:ring"
                        placeholder="Contoh: 20000"
                        @input="amount = amount.replace(/\D/g, '')"
                    />
                    <p v-if="amount" class="mt-1 text-xs text-gray-500">
                        Rp {{ formatToRupiah(amount) }}
                    </p>
                </div>

                <!-- Rekening Kita -->
                <div class="mb-4 rounded border bg-gray-100 p-3 text-sm">
                    <p class="font-medium">Rekening Anda</p>
                    <p>{{ myName }} - {{ myNumber }}</p>
                </div>

                <!-- TOMBOL KIRIM -->
                <div>
                    <button
                        @click="sendTransfer"
                        class="w-full rounded bg-green-600 px-4 py-2 text-white transition hover:bg-green-700"
                    >
                        Kirim Transfer
                    </button>
                </div>

                <!-- NOTIFIKASI -->
                <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
                <p v-if="success" class="text-sm text-green-600">
                    {{ success }}
                </p>

                <!-- RIWAYAT -->
                <div class="border-t pt-4">
                    <h2 class="mb-2 text-lg font-bold">Riwayat Transfer</h2>
                    <div
                        v-if="history.length === 0"
                        class="text-sm italic text-gray-500"
                    >
                        Belum ada transaksi.
                    </div>
                    <div
                        v-for="item in history"
                        :key="item.id"
                        class="mb-3 rounded border bg-white p-3 text-sm shadow-sm"
                    >
                        <p><strong>Nama:</strong> {{ item.recipient.name }}</p>
                        <p>
                            <strong>Rekening:</strong>
                            {{ item.recipient.account_number }}
                        </p>
                        <p>
                            <strong>Jumlah:</strong> Rp
                            {{ formatToRupiah(item.amount) }}
                        </p>
                        <p>
                            <strong>Tanggal:</strong>
                            {{
                                new Date(item.created_at).toLocaleString(
                                    'id-ID',
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL STRUK -->
        <div
            v-if="showResult"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
        >
            <div
                id="print-area-content"
                class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl"
            >
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
                        Transfer Berhasil!
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Struk transfer siap disimpan.
                    </p>
                </div>

                <div class="mb-4 rounded-lg bg-gray-50 p-4">
                    <div class="space-y-3">
                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Nama Tujuan</label
                            >
                            <p class="text-sm text-gray-900">
                                {{ strukData?.name }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >No Rekening</label
                            >
                            <p class="text-sm text-gray-900">
                                {{ strukData?.account_number }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Jumlah</label
                            >
                            <p class="text-sm text-gray-900">
                                Rp {{ formatToRupiah(strukData?.amount) }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-xs font-medium uppercase tracking-wide text-gray-500"
                                >Waktu</label
                            >
                            <p class="text-sm text-gray-900">
                                {{
                                    new Date(
                                        strukData?.timestamp,
                                    ).toLocaleString('id-ID')
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button
                        @click="showResult = false"
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

<style scoped>
.input {
    display: block;
    width: 100%;
    border: 1px solid #ccc;
    padding: 0.5rem;
    border-radius: 6px;
}
</style>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const user = usePage().props.auth.user;

const bankList = [
    'BCA',
    'BNI',
    'BRI',
    'Mandiri',
    'CIMB Niaga',
    'UOB',
    'Danamon',
    'Permata Bank',
    'BTN',
    'Taruma Bank',
];

const selectedBank = ref('');
const accountName = ref('');
const accountNumber = ref('');
const success = ref('');
const error = ref('');
const recipients = ref([]);
const isLoading = ref(false);

const fetchBankRecipients = async () => {
    try {
        const res = await axios.get('/mtransfer/bank-recipients');
        recipients.value = res.data;
    } catch {
        error.value = 'Gagal memuat daftar rekening bank';
    }
};

const saveBankRecipient = async () => {
    error.value = '';
    success.value = '';
    isLoading.value = true;

    if (!selectedBank.value || !accountName.value || !accountNumber.value) {
        error.value = 'Semua field wajib diisi';
        isLoading.value = false;
        return;
    }

    try {
        await axios.post('/mtransfer/bank-recipients', {
            bank_name: selectedBank.value.trim(),
            account_name: accountName.value.trim(),
            account_number: accountNumber.value.trim(),
        });

        success.value = 'Rekening bank berhasil disimpan!';
        selectedBank.value = '';
        accountName.value = '';
        accountNumber.value = '';
        await fetchBankRecipients();
        isLoading.value = false;
    } catch (err) {
        error.value = err.response?.data?.message || 'Gagal menyimpan';
        isLoading.value = false;
    }
};

const handleNumberInput = () => {
    accountNumber.value = accountNumber.value.replace(/\D/g, '').slice(0, 20);
};

onMounted(() => {
    fetchBankRecipients();
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Daftar Bank" />
        <div
            class="flex min-h-screen items-center justify-center bg-gray-100 px-4"
        >
            <div
                class="w-full max-w-md space-y-5 rounded-xl bg-white p-6 shadow-lg"
            >
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Daftar Bank
                    </h1>
                    <p class="text-sm text-gray-600">
                        Halo, {{ user.name }} 👋
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        Simpan rekening tujuan untuk transfer antar bank
                    </p>
                </div>

                <!-- Pilih Bank -->
                <div>
                    <label class="text-sm font-medium text-gray-700"
                        >Pilih Bank</label
                    >
                    <div
                        class="max-h-48 overflow-y-auto rounded border bg-gray-50 p-2"
                    >
                        <div
                            v-for="bank in bankList"
                            :key="bank"
                            @click="selectedBank = bank"
                            :class="[
                                'cursor-pointer rounded px-3 py-2 text-sm',
                                selectedBank === bank
                                    ? 'bg-green-200 font-semibold'
                                    : 'hover:bg-gray-100',
                            ]"
                        >
                            {{ bank }}
                        </div>
                    </div>
                </div>

                <!-- Nama Pemilik -->
                <div>
                    <label class="text-sm font-medium text-gray-700"
                        >Nama Pemilik</label
                    >
                    <input
                        v-model="accountName"
                        type="text"
                        placeholder="Contoh: Siti Aisyah"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring"
                    />
                </div>

                <!-- Nomor Rekening -->
                <div>
                    <label class="text-sm font-medium text-gray-700"
                        >Nomor Rekening</label
                    >
                    <input
                        v-model="accountNumber"
                        @input="handleNumberInput"
                        type="text"
                        maxlength="20"
                        placeholder="Maksimal 20 digit"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring"
                    />
                </div>

                <!-- Tombol Simpan -->
                <button
                    @click="saveBankRecipient"
                    :disabled="isLoading"
                    class="w-full rounded bg-green-600 py-2 text-white transition hover:bg-green-700"
                >
                    Simpan Rekening Bank
                </button>

                <!-- Alert -->
                <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
                <p v-if="success" class="text-sm text-green-600">
                    {{ success }}
                </p>

                <!-- Scroll daftar tersimpan -->
                <div>
                    <h2 class="mb-2 mt-4 text-sm font-semibold text-gray-700">
                        Rekening Bank Tersimpan
                    </h2>
                    <div
                        class="max-h-48 overflow-y-auto rounded border bg-gray-50 p-2 text-sm"
                    >
                        <ul v-if="recipients.length">
                            <li
                                v-for="(item, i) in recipients"
                                :key="i"
                                class="mb-1 rounded border bg-white px-3 py-2"
                            >
                                {{ item.bank_name }} -
                                {{ item.account_name }} ({{
                                    item.account_number
                                }})
                            </li>
                        </ul>
                        <p v-else class="text-gray-500">
                            Belum ada rekening disimpan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

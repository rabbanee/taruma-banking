<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Ambil data user
const page = usePage();
const user = page.props.auth.user;

const myData = ref('Selamat Datang Di Menu Daftar Rekening');

// Form data
const name = ref('');
const accountNumber = ref('');
const error = ref('');
const success = ref('');

// Data rekening yang sudah disimpan
const recipients = ref([]);

// Ambil daftar rekening dari DB
const fetchRecipients = async () => {
    try {
        const res = await axios.get('/mtransfer/recipients');
        recipients.value = res.data;
    } catch (err) {
        error.value = 'Gagal memuat data rekening';
    }
};

// Simpan rekening baru
const saveRecipient = async () => {
    error.value = '';
    success.value = '';

    // Validasi frontend
    if (!name.value || !accountNumber.value) {
        error.value = 'Semua field wajib diisi';
        return;
    }

    try {
        console.log('Account number:', accountNumber.value);
        await axios.post('/mtransfer/recipients', {
            name: name.value,
            account_number: accountNumber.value,
        });

        success.value = 'Rekening berhasil disimpan!';
        name.value = '';
        accountNumber.value = '';
        fetchRecipients();
    } catch (err) {
        error.value =
            err.response?.data?.message || 'Terjadi kesalahan saat menyimpan';
    }
};

// Batasi input angka max 10 digit
const handleAccountInput = () => {
    accountNumber.value = accountNumber.value.replace(/\D/g, '').slice(0, 10);
};

onMounted(() => {
    fetchRecipients();
});
</script>
<template>
    <AuthenticatedLayout>
        <Head title="Daftar Rekening" />

        <div
            class="flex min-h-screen items-center justify-center bg-gray-100 px-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-xl bg-white p-6 shadow"
            >
                <h1 class="text-center text-xl font-bold text-gray-800">
                    Daftar Rekening
                </h1>
                <p class="text-center text-gray-600">
                    Halo, {{ user.name }} 👋
                </p>
                <p class="text-center text-gray-600">{{ myData }}</p>

                <!-- Form Nama -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Nama Pemilik</label
                    >
                    <input
                        v-model="name"
                        type="text"
                        class="w-full rounded border px-3 py-2 focus:outline-none focus:ring"
                        placeholder="Contoh: Budi Santoso"
                    />
                </div>

                <!-- Form Nomor Rekening -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Nomor Rekening
                    </label>
                    <input
                        v-model="accountNumber"
                        type="text"
                        maxlength="10"
                        @input="handleAccountInput"
                        class="w-full rounded border px-3 py-2 focus:outline-none focus:ring"
                        placeholder=" Masukkan No Rekening"
                    />
                </div>

                <!-- Tombol Simpan -->
                <div>
                    <button
                        @click="saveRecipient"
                        class="w-full rounded bg-green-600 px-4 py-2 text-white transition hover:bg-green-700"
                    >
                        Simpan Rekening
                    </button>
                </div>

                <!-- Alert -->
                <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
                <p v-if="success" class="text-sm text-green-600">
                    {{ success }}
                </p>

                <!-- Scroll Box -->
                <div
                    class="mt-4 max-h-48 overflow-y-auto rounded border bg-gray-50 p-3"
                >
                    <h2 class="mb-2 font-semibold text-gray-700">
                        Rekening Tersimpan
                    </h2>
                    <ul
                        v-if="recipients.length"
                        class="space-y-1 text-sm text-gray-800"
                    >
                        <li
                            v-for="(item, index) in recipients"
                            :key="index"
                            class="rounded border bg-white p-2"
                        >
                            {{ item.name }} - {{ item.account_number }}
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">
                        Belum ada rekening.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

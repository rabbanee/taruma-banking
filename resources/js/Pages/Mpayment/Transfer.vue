<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const { props } = usePage();

const beneficiaries = ref([]);
const formAdd = ref({
    account_name: '',
    bank_name: '',
    account_number: '',
});
const formTransfer = ref({
    beneficiary_id: '',
    amount: '',
});

const message = ref('');
const tab = ref('transfer');

const getBeneficiaries = async () => {
    try {
        const res = await axios.get('/m-payment/transfer/beneficiaries');
        beneficiaries.value = res.data;
    } catch (err) {
        console.error(err);
    }
};

const saveBeneficiary = async () => {
    try {
        const res = await axios.post(
            '/m-payment/transfer/beneficiary',
            formAdd.value,
        );
        message.value = res.data.message;
        formAdd.value = { account_name: '', bank_name: '', account_number: '' };
        getBeneficiaries();
        tab.value = 'transfer';
    } catch (err) {
        message.value = err.response?.data?.message || 'Gagal simpan rekening';
    }
};

const submitTransfer = async () => {
    try {
        const res = await axios.post(
            '/m-payment/transfer/store',
            formTransfer.value,
        );
        message.value = res.data.message;
        formTransfer.value = { beneficiary_id: '', amount: '' };
    } catch (err) {
        message.value = err.response?.data?.message || 'Gagal transfer';
    }
};

getBeneficiaries();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transfer" />
        <div class="p-6">
            <h1 class="mb-4 text-2xl font-bold">Transfer Antar Rekening</h1>

            <div class="mb-4">
                <button
                    @click="tab = 'transfer'"
                    :class="tab === 'transfer' ? 'font-bold' : ''"
                    class="mr-2"
                >
                    Transfer
                </button>
                <button
                    @click="tab = 'add'"
                    :class="tab === 'add' ? 'font-bold' : ''"
                >
                    Daftar Rekening
                </button>
            </div>

            <div class="mb-4 text-green-600" v-if="message">{{ message }}</div>

            <div v-if="tab === 'transfer'">
                <label class="mb-1 block">Pilih Rekening Tujuan:</label>
                <select
                    v-model="formTransfer.beneficiary_id"
                    class="input mb-2"
                >
                    <option disabled value="">-- Pilih Rekening --</option>
                    <option
                        v-for="b in beneficiaries"
                        :key="b.id"
                        :value="b.id"
                    >
                        {{ b.bank_name }} - {{ b.account_number }} ({{
                            b.account_name
                        }})
                    </option>
                </select>
                <input
                    v-model="formTransfer.amount"
                    type="number"
                    class="input mb-2"
                    placeholder="Jumlah Transfer"
                />
                <button
                    @click="submitTransfer"
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                >
                    Transfer
                </button>
            </div>

            <div v-else>
                <input
                    v-model="formAdd.account_name"
                    class="input mb-2"
                    placeholder="Nama Pemilik Rekening"
                />
                <select v-model="formAdd.bank_name" class="input mb-2">
                    <option disabled value="">-- Pilih Bank --</option>
                    <option>BCA</option>
                    <option>BRI</option>
                    <option>BNI</option>
                    <option>Mandiri</option>
                    <option>BTN</option>
                    <option>Danamon</option>
                    <option>Permata</option>
                    <option>CIMB Niaga</option>
                </select>
                <input
                    v-model="formAdd.account_number"
                    class="input mb-2"
                    placeholder="Nomor Rekening"
                />
                <button
                    @click="saveBeneficiary"
                    class="rounded bg-green-600 px-4 py-2 text-white"
                >
                    Simpan Rekening
                </button>
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

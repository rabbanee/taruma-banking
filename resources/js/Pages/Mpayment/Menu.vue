<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Ambil data user
const page = usePage();
const user = page.props.auth.user;

const myData = ref('Selamat Datang Di Menu Transfer');

// Arahkan ke halaman tertentu
function goTo(page) {
    router.get(`/mtransfer/${page}`);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Menu Transfer" />

        <div class="mx-auto max-w-3xl px-4 py-6">
            <!-- Judul & Sapaan -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">Menu Transfer</h1>
                <p class="text-gray-600">Halo, {{ user.name }} 👋</p>
                <p>{{ myData }}</p>
            </div>

            <!-- Card Menu -->
            <div class="space-y-4 rounded-xl bg-white p-6 shadow">
                <!-- Daftar Rekening -->
                <button
                    @click="goTo('daftar-rekening')"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-green-50 px-4 py-3 text-left text-green-800 transition duration-150 hover:bg-green-100"
                >
                    <span class="font-medium"> Daftar Rekening</span>
                    <span class="text-lg font-bold text-green-500">›</span>
                </button>

                <!-- Daftar Bank -->
                <button
                    @click="goTo('daftar-bank')"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-green-50 px-4 py-3 text-left text-green-800 transition duration-150 hover:bg-green-100"
                >
                    <span class="font-medium"> Daftar Bank</span>
                    <span class="text-lg font-bold text-green-500">›</span>
                </button>

                <!-- Transfer -->
                <button
                    @click="goTo('transfer')"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-green-50 px-4 py-3 text-left text-green-800 transition duration-150 hover:bg-green-100"
                >
                    <span class="font-medium"> Transfer</span>
                    <span class="text-lg font-bold text-green-500">›</span>
                </button>

                <!-- Transfer Antar Bank -->
                <button
                    @click="goTo('transfer-antar-bank')"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-green-50 px-4 py-3 text-left text-green-800 transition duration-150 hover:bg-green-100"
                >
                    <span class="font-medium"> Transfer Antar Bank</span>
                    <span class="text-lg font-bold text-green-500">›</span>
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const items = [
    { label: 'PLN Pascabayar', route: '/m-payment/pln' },
    { label: 'Air (PDAM)', route: '/m-payment/air' },
    { label: 'T-Transfer', route: '/m-payment/transfer' },
];

function handleItemClick(item) {
    console.log('Item diklik:', item.label);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="M-Payment" />
        <div class="p-6">
            <h1 class="mb-4 text-2xl font-bold">M-Payment</h1>
            <p class="mb-6">Selamat datang di fitur Mobile Payment 🎉</p>

            <div class="divide-y rounded border bg-white shadow-sm">
                <Link
                    v-for="(item, index) in items"
                    :key="index"
                    :href="item.route"
                    :target="item.openInNewTab"
                    class="flex items-center justify-between px-4 py-4 transition-colors duration-200 hover:bg-gray-50"
                    @click="handleItemClick(item, $event)"
                >
                    <span class="font-medium">{{ item.label }}</span>
                    <span class="text-blue-500">
                        {{ item.openInNewTab ? '➡️' : '➡️' }}
                    </span>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

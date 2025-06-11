<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="M-Payment" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">M-Payment</h1>
            <p>Selamat datang di fitur Mobile Payment 🎉</p>
        </div>
    </AuthenticatedLayout>
</template>

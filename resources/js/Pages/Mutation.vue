<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    mutation: Array,
});

const startDate = ref('');
const endDate = ref('');

const filteredMutation = computed(() => {
    if (!startDate.value && !endDate.value) return props.mutation;

    return props.mutation.filter((item) => {
        const date = new Date(item.created_at);
        const start = startDate.value ? new Date(startDate.value) : null;
        const end = endDate.value ? new Date(endDate.value) : null;

        return (!start || date >= start) && (!end || date <= end);
    });
});
</script>

<template>
    <Head title="Mutasi" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                <div>
                    <Link
                        href="/my-balance"
                        class="inline-block rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                    >
                        &lt; Back
                    </Link>
                </div>

                <div class="space-x-4 rounded bg-white p-4 shadow">
                    <label>
                        From:
                        <input
                            type="date"
                            v-model="startDate"
                            class="rounded border px-2 py-1"
                        />
                    </label>
                    <label>
                        To:
                        <input
                            type="date"
                            v-model="endDate"
                            class="rounded border px-2 py-1"
                        />
                    </label>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="space-y-4 p-6 text-gray-900">
                        <h2 class="text-xl font-bold">Mutation History</h2>

                        <div v-if="filteredMutation.length">
                            <div
                                v-for="item in filteredMutation"
                                :key="item.id"
                                class="rounded border bg-white p-4 shadow"
                            >
                                <p>
                                    <strong>>>> </strong>
                                    {{ item.payment_type }}
                                </p>
                                <p>
                                    <strong>Jumlah:</strong> Rp
                                    {{
                                        item.amount.toLocaleString('id-ID', {
                                            minimumFractionDigits: 2,
                                        })
                                    }}
                                </p>
                                <p>
                                    <strong>Status:</strong> {{ item.status }}
                                </p>
                                <p>
                                    <strong>Tanggal:</strong>
                                    {{
                                        new Date(
                                            item.created_at,
                                        ).toLocaleString('id-ID')
                                    }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-gray-500">
                            Semua riwayat transaksimu pasti tersimpan disini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

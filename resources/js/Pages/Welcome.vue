<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});

const user = usePage().props.auth.user;

console.log(user);
</script>

<template>
    <Head title="Welcome to Taruma Banking" />
    <div
        class="flex min-h-screen flex-col bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200"
    >
        <!-- Navigation -->
        <nav
            class="flex justify-between space-x-4 border-b border-gray-300 p-4 dark:border-gray-700"
        >
            <div>
                <Link
                    v-if="user"
                    :href="route('dashboard')"
                    class="self-start rounded-md border border-gray-600 px-4 py-2 transition hover:bg-gray-200 dark:hover:bg-gray-700"
                >
                    Dashboard
                </Link>
            </div>
            <div>
                <Link
                    v-if="!user && canLogin"
                    :href="route('login')"
                    class="mr-2 rounded-md border border-gray-600 px-4 py-2 transition hover:bg-gray-200 dark:hover:bg-gray-700"
                >
                    Login {{ user }}
                </Link>
                <Link
                    v-if="!user && canRegister"
                    :href="route('register')"
                    class="rounded-md border border-gray-600 px-4 py-2 transition hover:bg-gray-200 dark:hover:bg-gray-700"
                >
                    Register
                </Link>
            </div>
        </nav>

        <!-- Main Content -->
        <main
            class="flex flex-grow flex-col items-center justify-center px-6 text-center"
        >
            <h1 class="mb-2 text-4xl font-bold">
                Selamat Datang di Taruma Banking
            </h1>
            <p class="mb-8 max-w-xl text-lg">
                Kelola transaksi perbankanmu dengan mudah, aman, dan cepat
                melalui aplikasi kami.
            </p>

            <!-- Logo Taruma Banking -->
            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 10px;
                "
            >
                <img
                    src="../../../public/assets/img/Taruma_banking_logo.png"
                    alt="Taruma Banking Logo"
                    style="height: 160px; width: auto; object-fit: contain"
                />
            </div>
        </main>

        <footer
            class="border-t border-gray-300 p-4 text-center text-sm text-gray-400 dark:border-gray-700"
        >
            &copy; 2025 Taruma Banking. All rights reserved.
        </footer>
    </div>
</template>

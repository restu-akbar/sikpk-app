<script setup lang="ts">
const loginWithGoogle = () => {
    window.location.href = '/auth/google';
};
</script>

<template>
    <div
        class="fixed inset-0 flex items-center justify-center bg-background px-4"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-lg"
        >
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-foreground">
                    Sistem Pelaporan dan Penanganan Kekerasan Polban
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Login menggunakan akun Google Polban
                </p>
            </div>

            <button
                @click="loginWithGoogle"
                class="flex w-full items-center justify-center gap-3 rounded-xl border border-border bg-background px-4 py-3 text-sm font-medium text-foreground transition hover:bg-accent hover:text-accent-foreground"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 48 48"
                    class="h-5 w-5"
                >
                    <path
                        fill="#FFC107"
                        d="M43.611 20.083H42V20H24v8h11.303C33.659 32.657 29.244 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"
                    />
                    <path
                        fill="#FF3D00"
                        d="M6.306 14.691l6.571 4.819C14.655 16.108 18.961 13 24 13c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4c-7.682 0-14.347 4.337-17.694 10.691z"
                    />
                    <path
                        fill="#4CAF50"
                        d="M24 44c5.166 0 9.86-1.977 13.409-5.193l-6.19-5.238C29.176 35.091 26.715 36 24 36c-5.223 0-9.626-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"
                    />
                    <path
                        fill="#1976D2"
                        d="M43.611 20.083H42V20H24v8h11.303c-1.058 3.011-3.223 5.421-6.084 6.569l.003-.002 6.19 5.238C35.006 39.999 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"
                    />
                </svg>
                Login dengan Google
            </button>

            <div
                class="mt-6 rounded-lg bg-muted p-4 text-sm text-muted-foreground"
            >
                Hanya akun Google dengan domain Polban yang dapat mengakses
                sistem aplikasi.
            </div>
        </div>
    </div>
</template>

<style>
html,
body,
#__nuxt {
    height: 100%;
    margin: 0;
    overflow: hidden;
}
</style>

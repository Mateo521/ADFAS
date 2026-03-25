<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-xl sm:rounded-xl border-t-4 border-blue-700">
            
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">Ingresar a ADFAS</h2>

            <div v-if="status" class="mb-4 font-medium text-lg text-green-600 bg-green-100 p-4 rounded">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-xl font-medium text-gray-700">Correo Electrónico</label>
                    <input id="email" type="email" v-model="form.email" required autofocus autocomplete="username"
                           class="mt-2 block w-full text-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-4">
                    <div v-if="form.errors.email" class="text-red-600 mt-2 font-semibold">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="block text-xl font-medium text-gray-700">Contraseña</label>
                    <input id="password" type="password" v-model="form.password" required autocomplete="current-password"
                           class="mt-2 block w-full text-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-4">
                    <div v-if="form.errors.password" class="text-red-600 mt-2 font-semibold">{{ form.errors.password }}</div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" v-model="form.remember" class="w-6 h-6 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ms-3 text-lg text-gray-700">Recordar mi sesión</span>
                    </label>
                </div>

                <div class="flex flex-col items-center justify-end mt-8 space-y-4">
                    <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                            class="w-full text-center py-4 bg-blue-700 text-white text-xl font-bold rounded-lg shadow-md hover:bg-blue-800 transition">
                        Ingresar
                    </button>
                    
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-lg text-blue-600 hover:text-blue-900 underline">
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
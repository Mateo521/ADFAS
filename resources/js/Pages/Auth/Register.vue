<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    apellido: '',
    telefono: '',
    email: '',
    password: '',
    password_confirmation: '',
    foto_perfil: null,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registro de Árbitros" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-xl mt-6 px-8 py-8 bg-white shadow-lg overflow-hidden sm:rounded-xl">
            
            <h2 class="text-3xl font-bold text-center text-blue-800 mb-8">Crear Perfil ADFAS</h2>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-lg font-medium text-gray-700">Nombre</label>
                        <input id="name" type="text" v-model="form.name" required autofocus 
                               class="mt-2 block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-3">
                        <div v-if="form.errors.name" class="text-red-600 mt-2">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label for="apellido" class="block text-lg font-medium text-gray-700">Apellido</label>
                        <input id="apellido" type="text" v-model="form.apellido" required 
                               class="mt-2 block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-3">
                        <div v-if="form.errors.apellido" class="text-red-600 mt-2">{{ form.errors.apellido }}</div>
                    </div>
                </div>

                <div>
                    <label for="telefono" class="block text-lg font-medium text-gray-700">Teléfono / WhatsApp</label>
                    <input id="telefono" type="tel" v-model="form.telefono" required 
                           class="mt-2 block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-3">
                    <div v-if="form.errors.telefono" class="text-red-600 mt-2">{{ form.errors.telefono }}</div>
                </div>

                <div>
                    <label for="email" class="block text-lg font-medium text-gray-700">Correo Electrónico</label>
                    <input id="email" type="email" v-model="form.email" required 
                           class="mt-2 block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-3">
                    <div v-if="form.errors.email" class="text-red-600 mt-2">{{ form.errors.email }}</div>
                </div>

                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <label for="foto_perfil" class="block text-lg font-medium text-gray-700 mb-2">Foto de Perfil (Opcional)</label>
                    <input id="foto_perfil" type="file" @input="form.foto_perfil = $event.target.files[0]" accept="image/*"
                           class="block w-full text-md text-gray-600 file:mr-4 file:py-3 file:px-4 file:rounded-md file:border-0 file:text-md file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                    <div v-if="form.errors.foto_perfil" class="text-red-600 mt-2">{{ form.errors.foto_perfil }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-lg font-medium text-gray-700">Contraseña</label>
                        <input id="password" type="password" v-model="form.password" required autocomplete="new-password"
                               class="mt-2 block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-3">
                        <div v-if="form.errors.password" class="text-red-600 mt-2">{{ form.errors.password }}</div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-lg font-medium text-gray-700">Confirmar Contraseña</label>
                        <input id="password_confirmation" type="password" v-model="form.password_confirmation" required 
                               class="mt-2 block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-3">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <Link :href="route('login')" class="text-lg text-blue-600 hover:text-blue-900 underline">
                        ¿Ya tienes cuenta?
                    </Link>

                    <button type="submit" :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                            class="px-8 py-4 bg-blue-700 text-white text-lg font-bold rounded-lg shadow hover:bg-blue-800 transition ease-in-out duration-150">
                        Registrarme
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
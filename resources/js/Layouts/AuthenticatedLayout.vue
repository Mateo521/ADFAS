<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

 
const user = usePage().props.auth.user;
const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans">
        
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center shadow">
                        <span class="text-white text-2xl font-bold leading-none">A</span>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-800 tracking-wider leading-none">ADFAS</p>
                        <p class="text-sm text-gray-500 font-medium">Asociación Arbitral</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-2">
                    <Link :href="route('dashboard')" 
                          class="px-5 py-3 rounded-lg font-bold text-lg transition-colors"
                          :class="route().current('dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        Inicio
                    </Link>
                    <button class="px-5 py-3 rounded-lg font-bold text-lg text-gray-600 hover:bg-gray-100 transition-colors">Designaciones</button>
                    <button class="px-5 py-3 rounded-lg font-bold text-lg text-gray-600 hover:bg-gray-100 transition-colors">Árbitros</button>
                    
                    <Link v-if="user.rol === 'admin'" :href="route('admin.importar.index')" 
                          class="px-5 py-3 rounded-lg font-bold text-lg transition-colors"
                          :class="route().current('admin.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        Admin
                    </Link>

                    <Link :href="route('noticias.index')" 
                          class="px-5 py-3 rounded-lg font-bold text-lg transition-colors"
                          :class="route().current('noticias.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        Noticias
                    </Link>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 border-l pl-4 border-gray-200">
                        <div class="hidden sm:block text-right">
                            <p class="text-base font-bold text-gray-800 leading-none">{{ user.name }} {{ user.apellido }}</p>
                            <p class="text-sm text-blue-600 font-semibold uppercase mt-1">{{ user.rol }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-50 border-2 border-blue-200 flex items-center justify-center text-blue-700 font-bold text-lg overflow-hidden">
                            <img v-if="user.foto_perfil" :src="`/storage/${user.foto_perfil}`" class="w-full h-full object-cover">
                            <span v-else>{{ user.name.charAt(0) }}{{ user.apellido.charAt(0) }}</span>
                        </div>
                    </div>

                    <Link :href="route('logout')" method="post" as="button"
                          class="ml-2 px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-red-100 hover:text-red-700 transition-colors shadow-sm cursor-pointer">
                        Salir
                    </Link>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

    </div>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    noticias: Array
});


const colorTipo = (tipo) => {
    if (tipo === 'Urgente') return 'bg-red-100 text-red-800 border-red-300';
    if (tipo === 'Citación') return 'bg-yellow-100 text-yellow-800 border-yellow-300';
    return 'bg-blue-100 text-blue-800 border-blue-300';
};
</script>

<template>
    <Head title="Tablón de Noticias - ADFAS" />

    <AuthenticatedLayout>
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="text-gray-500 text-lg uppercase tracking-widest font-semibold mb-1">Comunicados Oficiales</p>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-wider uppercase">Tablón de Noticias</h1>
            </div>
            
            <Link v-if="$page.props.auth.user.rol === 'admin'" :href="route('noticias.create')" 
                  class="px-6 py-3 bg-blue-700 text-white text-lg font-bold rounded-xl shadow-md hover:bg-blue-800 transition">
                + Redactar Noticia
            </Link>
        </div>

        <div class="space-y-6 max-w-5xl">
            <div v-if="noticias.length === 0" class="bg-white border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center">
                <p class="text-xl text-gray-500 font-bold">No hay comunicados publicados aún.</p>
            </div>

            <Link v-for="noticia in noticias" :key="noticia.id" :href="route('noticias.show', noticia.id)" 
                  class="block bg-white border-2 border-gray-200 rounded-2xl p-6 md:p-8 hover:border-blue-400 hover:shadow-md transition-all group">
                
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    
                    <div v-if="noticia.imagen_ruta" class="w-full md:w-48 h-32 rounded-xl overflow-hidden shrink-0 border border-gray-200">
                        <img :src="`/storage/${noticia.imagen_ruta}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div v-else class="w-full md:w-48 h-32 rounded-xl bg-gray-50 border-2 border-gray-100 flex items-center justify-center shrink-0">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-3">
                            <span :class="colorTipo(noticia.tipo)" class="px-3 py-1 border text-sm font-black rounded-md uppercase tracking-wider">
                                {{ noticia.tipo }}
                            </span>
                            <span class="text-gray-500 font-bold text-sm">
                                {{ new Date(noticia.created_at).toLocaleDateString('es-ES') }}
                            </span>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-2 group-hover:text-blue-700 transition-colors">
                            {{ noticia.titulo }}
                        </h2>
                        <p class="text-lg text-gray-600 font-medium line-clamp-2">
                            {{ noticia.contenido }}
                        </p>
                        
                        <div v-if="noticia.archivo_ruta" class="mt-4 inline-flex items-center gap-2 text-purple-700 bg-purple-50 px-3 py-1.5 rounded-lg border border-purple-200 text-sm font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Contiene archivo adjunto
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
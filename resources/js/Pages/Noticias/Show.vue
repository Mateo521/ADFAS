<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    noticia: Object
});
</script>

<template>
    <Head :title="noticia.titulo + ' - ADFAS'" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            
            <Link :href="route('noticias.index')" class="inline-flex items-center gap-2 text-blue-600 font-bold text-lg hover:underline mb-6">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                Volver al Tablón
            </Link>

            <div class="bg-white border-2 border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                
                <div v-if="noticia.imagen_ruta" class="w-full h-64 md:h-96 bg-gray-100 border-b-2 border-gray-200">
                    <img :src="`/storage/${noticia.imagen_ruta}`" class="w-full h-full object-cover">
                </div>

                <div class="p-6 md:p-10">
                    <div class="flex items-center justify-between border-b-2 border-gray-100 pb-6 mb-6">
                        <div>
                            <span class="px-4 py-1.5 bg-gray-100 text-gray-700 text-sm font-black rounded-md uppercase tracking-wider border border-gray-300">
                                {{ noticia.tipo }}
                            </span>
                            <p class="text-gray-500 font-bold mt-3">
                                Publicado por: <span class="text-gray-800">{{ noticia.autor.name }} {{ noticia.autor.apellido }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-black text-blue-600">{{ new Date(noticia.created_at).toLocaleDateString('es-ES') }}</p>
                        </div>
                    </div>

                    <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-8 leading-tight">
                        {{ noticia.titulo }}
                    </h1>

                    <div class="text-xl text-gray-700 font-medium leading-relaxed whitespace-pre-wrap mb-10">
                        {{ noticia.contenido }}
                    </div>

                    <div v-if="noticia.archivo_ruta" class="bg-purple-50 border-2 border-purple-200 rounded-xl p-8 text-center">
                        <h3 class="text-2xl font-black text-purple-900 mb-2">Material Adjunto</h3>
                        <p class="text-lg text-purple-700 font-medium mb-6">Descarga el documento relacionado a este comunicado.</p>
                        
                        <a :href="`/storage/${noticia.archivo_ruta}`" target="_blank" download
                           class="inline-flex items-center justify-center gap-3 w-full md:w-auto px-10 py-5 bg-purple-600 text-white text-xl font-black uppercase tracking-wider rounded-xl hover:bg-purple-700 transition-colors shadow-md">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Descargar Archivo
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';  

const form = useForm({
    tipo: 'Información',
    titulo: '',
    contenido: '',
    imagen: null,
    archivo: null,
});

 
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

const submit = () => {
    form.post(route('noticias.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            document.getElementById('imagen').value = '';
            document.getElementById('archivo').value = '';
            
            
            Toast.fire({
                icon: 'success',
                title: '¡Noticia publicada con éxito!'
            });
        },
    });
};
</script>

<template>
    <Head title="Publicar Noticia - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="mb-8">
            <Link :href="route('dashboard')" class="text-blue-600 font-bold text-lg hover:underline flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                Volver al Panel
            </Link>
            <p class="text-gray-500 text-lg uppercase tracking-widest font-semibold mb-1">Comunicación</p>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-wider uppercase">Publicar Noticia</h1>
        </div>

        <div class="bg-white border-2 border-gray-200 rounded-2xl p-6 md:p-10 shadow-sm max-w-4xl">
            <form @submit.prevent="submit" class="space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="tipo" class="block text-xl font-bold text-gray-700 mb-2">Tipo de Aviso</label>
                        <select id="tipo" v-model="form.tipo" class="block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl p-4 bg-gray-50">
                            <option>Información</option>
                            <option>Citación</option>
                            <option>Urgente</option>
                            <option>Actualización de Reglas</option>
                        </select>
                        <div v-if="form.errors.tipo" class="text-red-600 mt-2 font-bold">{{ form.errors.tipo }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="titulo" class="block text-xl font-bold text-gray-700 mb-2">Título de la Noticia</label>
                        <input id="titulo" type="text" v-model="form.titulo" required placeholder="Ej: Citación a pruebas físicas..."
                               class="block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl p-4 bg-gray-50">
                        <div v-if="form.errors.titulo" class="text-red-600 mt-2 font-bold">{{ form.errors.titulo }}</div>
                    </div>
                </div>

                <div>
                    <label for="contenido" class="block text-xl font-bold text-gray-700 mb-2">Contenido / Mensaje</label>
                    <textarea id="contenido" v-model="form.contenido" rows="6" required placeholder="Escribe todos los detalles aquí..."
                              class="block w-full text-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl p-4 bg-gray-50 resize-none"></textarea>
                    <div v-if="form.errors.contenido" class="text-red-600 mt-2 font-bold">{{ form.errors.contenido }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t-2 border-dashed border-gray-200 pt-8">
                    
                    <div class="bg-blue-50 border-2 border-blue-100 rounded-xl p-6">
                        <label for="imagen" class="flex items-center gap-2 text-xl font-black text-blue-900 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Destacada (Opcional)
                        </label>
                        <p class="text-blue-700 text-sm font-medium mb-4">Formatos: JPG, PNG. Máx: 2MB.</p>
                        <input id="imagen" type="file" @input="form.imagen = $event.target.files[0]" accept="image/*"
                               class="block w-full text-md text-gray-700 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-md file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        <div v-if="form.errors.imagen" class="text-red-600 mt-2 font-bold">{{ form.errors.imagen }}</div>
                    </div>

                    <div class="bg-purple-50 border-2 border-purple-100 rounded-xl p-6">
                        <label for="archivo" class="flex items-center gap-2 text-xl font-black text-purple-900 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Archivo Adjunto (Opcional)
                        </label>
                        <p class="text-purple-700 text-sm font-medium mb-4">PDF, Word o Excel. Máx: 5MB.</p>
                        <input id="archivo" type="file" @input="form.archivo = $event.target.files[0]" accept=".pdf,.doc,.docx,.xls,.xlsx"
                               class="block w-full text-md text-gray-700 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-md file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
                        <div v-if="form.errors.archivo" class="text-red-600 mt-2 font-bold">{{ form.errors.archivo }}</div>
                    </div>

                </div>

                <div class="pt-6">
                    <button type="submit" :disabled="form.processing"
                            class="w-full md:w-auto px-10 py-5 bg-green-600 text-white text-xl font-black uppercase tracking-wider rounded-xl hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed shadow-md">
                        <span v-if="form.processing">Subiendo archivos y publicando...</span>
                        <span v-else>Publicar Noticia</span>
                    </button>
                </div>

            </form>
        </div>

    </AuthenticatedLayout>
</template>
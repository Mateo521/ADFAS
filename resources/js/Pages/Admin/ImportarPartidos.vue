<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    documento: null,
});

const nombreArchivo = ref('');
const flash = usePage().props.flash;  

const procesarArchivo = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.documento = file;
        nombreArchivo.value = file.name;
    }
};

const submit = () => {
    form.post(route('admin.importar.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('documento');
            nombreArchivo.value = '';
        },
    });
};
</script>

<template>
    <Head title="Panel Admin - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="mb-10">
            <p class="text-gray-500 text-lg uppercase tracking-widest font-semibold mb-1">Panel de control</p>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-wider uppercase">Administración</h1>
        </div>

        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-8 bg-green-100 border-l-8 border-green-600 p-6 rounded-r-xl shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                <p class="text-2xl font-bold text-green-800">{{ $page.props.flash.success }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 mb-10">
            <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 shadow-sm lg:col-span-2">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-8 bg-blue-600 rounded-full"></div>
                    <h2 class="text-2xl font-bold tracking-widest text-gray-800 uppercase">Importar Planilla Estandarizada</h2>
                </div>
                
                <form @submit.prevent="submit">
                    <div class="border-4 border-dashed border-blue-200 rounded-2xl p-10 text-center hover:border-blue-400 hover:bg-blue-50 transition-colors relative cursor-pointer group mb-6">
                        <input 
                            type="file" 
                            id="documento" 
                            @change="procesarArchivo"
                            accept=".xlsx, .csv"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            required
                        >
                        <svg class="w-16 h-16 text-blue-300 group-hover:text-blue-500 mx-auto mb-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        
                        <p class="text-xl text-gray-600 font-bold mb-2">Toca aquí para seleccionar tu Excel</p>
                        <p class="text-md text-gray-500 font-medium">Formato requerido: .xlsx</p>

                        <div v-if="nombreArchivo" class="mt-6 inline-flex items-center gap-2 bg-blue-100 px-6 py-3 rounded-xl border border-blue-300">
                            <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-blue-800 font-bold text-lg">{{ nombreArchivo }}</span>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing || !nombreArchivo"
                        class="w-full py-5 bg-blue-700 text-white text-xl font-black uppercase tracking-wider rounded-xl hover:bg-blue-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed shadow-md"
                    >
                        <span v-if="form.processing">Procesando planilla...</span>
                        <span v-else>Confirmar y Publicar Designaciones</span>
                    </button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 shadow-sm">
                    <h3 class="text-xl font-black text-gray-800 mb-4 uppercase">Instrucciones</h3>
                    <ul class="text-lg text-gray-600 font-medium space-y-3 list-disc pl-5">
                        <li>Usa la <strong>plantilla oficial</strong> con los encabezados correctos.</li>
                        <li>El apellido del árbitro debe coincidir con el registrado en el sistema.</li>
                        <li>Al publicar, las designaciones llegarán instantáneamente a cada árbitro.</li>
                    </ul>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
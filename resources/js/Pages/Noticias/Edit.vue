<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';  

const props = defineProps({
    noticia: Object
});

const form = useForm({
    _method: 'put',  
    tipo: props.noticia.tipo,
    titulo: props.noticia.titulo,
    contenido: props.noticia.contenido,
    imagen: null,
    archivo: null,
});

const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true
});

const submit = () => {
 
    form.post(route('noticias.update', props.noticia.id), {
        preserveScroll: true,
        onSuccess: () => {
            Toast.fire({ icon: 'success', title: '¡Noticia actualizada con éxito!' });
        },
    });
};
</script>

<template>
    <Head title="Editar Noticia - ADFAS" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto font-['Lato',sans-serif] pb-12">
            
            <div class="mb-8">
                <Link :href="route('noticias.show', noticia.id)" class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6B7280] hover:text-[#D4A843] uppercase tracking-widest transition-colors group mb-6">
                    <div class="p-1.5 rounded-full bg-white border border-[#E5E7EB] group-hover:border-[#D4A843] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </div>
                    Cancelar Edición
                </Link>
                
                <p class="text-[11px] font-black tracking-[3px] uppercase text-[#A87C20] mb-2">Módulo de Comunicación</p>
                <h1 class="- text-[32px] md:text-[40px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-3">
                    Editar Comunicado
                </h1>
                <div class="flex items-center gap-2.5">
                    <span class="w-16 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                    <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded p-8 md:p-12 shadow-[0_8px_30px_rgba(13,27,62,0.04)] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#D4A843]/5 rounded-full blur-3xl pointer-events-none"></div>

                <form @submit.prevent="submit" class="space-y-8 relative z-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1 flex flex-col gap-2">
                            <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Tipo de Aviso</label>
                            <select v-model="form.tipo" class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                <option value="Información">Información General</option>
                                <option value="Citación">Citación Oficial</option>
                                <option value="Urgente">Aviso Urgente</option>
                                <option value="Actualización de Reglas">Actualización de Reglas</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex flex-col gap-2">
                            <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Titular de la Noticia</label>
                            <input type="text" v-model="form.titulo" required class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-bold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Cuerpo del Comunicado</label>
                        <textarea v-model="form.contenido" rows="8" required class="w-full px-5 py-4 bg-[#F9FAFB] border border-[#D1D5DB] rounded-xl text-[15px] font-medium text-[#374151] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all resize-y"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-[#E5E7EB]">
                        <div class="group relative border-2 border-dashed border-[#D1D5DB] hover:border-[#D4A843]/60 bg-[#F9FAFB] hover:bg-white rounded-xl p-6 transition-all duration-300">
                            <p class="text-[13px] font-black text-[#0D1B3E] uppercase tracking-wide mb-1">Cambiar Portada</p>
                            <p class="text-[11px] font-bold text-[#9CA3AF] mb-3">Deja en blanco para conservar la actual.</p>
                            <input type="file" @input="form.imagen = $event.target.files[0]" accept="image/*" class="block w-full text-[12px] text-[#6B7280] font-medium file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[11px] file:font-black file:uppercase file:bg-[#0D1B3E] file:text-white cursor-pointer focus:outline-none">
                        </div>

                        <div class="group relative border-2 border-dashed border-[#D1D5DB] hover:border-[#D4A843]/60 bg-[#F9FAFB] hover:bg-white rounded-xl p-6 transition-all duration-300">
                            <p class="text-[13px] font-black text-[#0D1B3E] uppercase tracking-wide mb-1">Cambiar Documento</p>
                            <p class="text-[11px] font-bold text-[#9CA3AF] mb-3">Deja en blanco para conservar el actual.</p>
                            <input type="file" @input="form.archivo = $event.target.files[0]" accept=".pdf,.doc,.docx,.xls,.xlsx" class="block w-full text-[12px] text-[#6B7280] font-medium file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[11px] file:font-black file:uppercase file:bg-[#0D1B3E] file:text-white cursor-pointer focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-[#E5E7EB] flex items-center justify-end">
                        <button type="submit" :disabled="form.processing" class="group relative overflow-hidden w-full md:w-auto px-10 py-4 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[13px] font-black uppercase tracking-[2px] rounded-lg transition-all duration-300 shadow-[0_2px_12px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] disabled:opacity-65">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <span v-if="form.processing" class="relative z-10 flex items-center gap-2.5">Guardando...</span>
                            <span v-else class="relative z-10 flex items-center gap-2">Actualizar Comunicado</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
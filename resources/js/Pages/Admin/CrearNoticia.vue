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
                title: 'Noticia publicada con éxito'
            });
        },
    });
};
</script>

<template>
    <Head title="Publicar Noticia - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="max-w-7xl mx-auto font-['Lato',sans-serif] pb-12">
            
            <div class="mb-8">
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6B7280] hover:text-[#D4A843] uppercase tracking-widest transition-colors group mb-6">
                    <div class="p-1.5 rounded-full bg-white border border-[#E5E7EB] group-hover:border-[#D4A843] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </div>
                    Volver al Panel
                </Link>
                
                <p class="text-base font-black tracking-[3px] uppercase text-[#A87C20] mb-2">Módulo de Comunicación</p>
                <h1 class="- text-[32px] md:text-[40px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-3">
                    Redactar Comunicado
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
                            <label for="tipo" class="text-base font-black tracking-[1px] uppercase text-[#0D1B3E]">Tipo de Aviso</label>
                            <select id="tipo" v-model="form.tipo" class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                <option value="Información">Información General</option>
                                <option value="Citación">Citación Oficial</option>
                                <option value="Urgente">Aviso Urgente</option>
                                <option value="Actualización de Reglas">Actualización de Reglas</option>
                            </select>
                            <div v-if="form.errors.tipo" class="text-base font-bold text-red-500 mt-1">{{ form.errors.tipo }}</div>
                        </div>

                        <div class="md:col-span-2 flex flex-col gap-2">
                            <label for="titulo" class="text-base font-black tracking-[1px] uppercase text-[#0D1B3E]">Titular de la Noticia</label>
                            <input id="titulo" type="text" v-model="form.titulo" required placeholder="Ej: Citación obligatoria a pruebas físicas anuales"
                                   class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-bold text-[#111827] placeholder:text-[#9CA3AF] placeholder:font-medium focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                            <div v-if="form.errors.titulo" class="text-base font-bold text-red-500 mt-1">{{ form.errors.titulo }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <label for="contenido" class="text-base font-black tracking-[1px] uppercase text-[#0D1B3E]">Cuerpo del Comunicado</label>
                            <span class="text-[10px] font-bold text-[#9CA3AF] uppercase tracking-wider">Podés usar múltiples párrafos</span>
                        </div>
                        <textarea id="contenido" v-model="form.contenido" rows="8" required placeholder="Escribí acá los detalles del comunicado de forma clara y directa..."
                                  class="w-full px-5 py-4 bg-[#F9FAFB] border border-[#D1D5DB] rounded text-[15px] font-medium text-[#374151] leading-relaxed placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all resize-y min-h-[150px]"></textarea>
                        <div v-if="form.errors.contenido" class="text-base font-bold text-red-500 mt-1">{{ form.errors.contenido }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-[#E5E7EB]">
                        
                        <div class="group relative border-2 border-dashed border-[#D1D5DB] hover:border-[#D4A843]/60 bg-[#F9FAFB] hover:bg-white rounded p-6 transition-all duration-300">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-[#0D1B3E]/5 text-[#0D1B3E] flex items-center justify-center group-hover:bg-[#0D1B3E] group-hover:text-[#D4A843] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[13px] font-black text-[#0D1B3E] uppercase tracking-wide">Foto de Portada</p>
                                    <p class="text-base font-bold text-[#9CA3AF]">JPG, PNG (Opcional)</p>
                                </div>
                            </div>
                            <input id="imagen" type="file" @input="form.imagen = $event.target.files[0]" accept="image/*"
                                   class="block w-full text-[12px] text-[#6B7280] font-medium file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-base file:font-black file:uppercase file:tracking-wider file:bg-[#0D1B3E] file:text-white hover:file:bg-[#162554] hover:file:text-[#D4A843] file:transition-colors cursor-pointer focus:outline-none">
                            <div v-if="form.errors.imagen" class="text-base font-bold text-red-500 mt-2">{{ form.errors.imagen }}</div>
                        </div>

                        <div class="group relative border-2 border-dashed border-[#D1D5DB] hover:border-[#D4A843]/60 bg-[#F9FAFB] hover:bg-white rounded p-6 transition-all duration-300">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-[#0D1B3E]/5 text-[#0D1B3E] flex items-center justify-center group-hover:bg-[#0D1B3E] group-hover:text-[#D4A843] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[13px] font-black text-[#0D1B3E] uppercase tracking-wide">Documento Adjunto</p>
                                    <p class="text-base font-bold text-[#9CA3AF]">PDF, Word, Excel (Opcional)</p>
                                </div>
                            </div>
                            <input id="archivo" type="file" @input="form.archivo = $event.target.files[0]" accept=".pdf,.doc,.docx,.xls,.xlsx"
                                   class="block w-full text-[12px] text-[#6B7280] font-medium file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-base file:font-black file:uppercase file:tracking-wider file:bg-[#0D1B3E] file:text-white hover:file:bg-[#162554] hover:file:text-[#D4A843] file:transition-colors cursor-pointer focus:outline-none">
                            <div v-if="form.errors.archivo" class="text-base font-bold text-red-500 mt-2">{{ form.errors.archivo }}</div>
                        </div>

                    </div>

                    <div class="pt-6 border-t border-[#E5E7EB] flex items-center justify-between flex-wrap-reverse gap-4">
                        <p class="text-base font-bold text-[#9CA3AF] uppercase tracking-wider">
                            La noticia será visible inmediatamente para todos los árbitros.
                        </p>
                        <button type="submit" :disabled="form.processing"
                                class="group relative overflow-hidden w-full md:w-auto px-10 py-4 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[13px] font-black uppercase tracking-[2px] rounded-lg transition-all duration-300 shadow-[0_2px_12px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] hover:shadow-[0_8px_20px_rgba(168,124,32,0.35)] disabled:opacity-65 disabled:cursor-not-allowed disabled:transform-none">
                            
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <span v-if="form.processing" class="relative z-10 flex items-center justify-center gap-2.5">
                                <svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" stroke-dasharray="20" stroke-dashoffset="5"/>
                                </svg>
                                Publicando...
                            </span>
                            <span v-else class="relative z-10 flex items-center justify-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                Publicar Comunicado
                            </span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    documento: null,
    fecha_base: '' // <-- VOLVEMOS A PEDIR LA FECHA
});

const nombreArchivo = ref('');

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
            form.reset('documento', 'fecha_base');
            nombreArchivo.value = '';
        },
    });
};
</script>

<template>
    <Head title="Importar Planilla - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="mb-8">
            <h1 class="font-['Playfair_Display',serif]- text-[28px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-2 uppercase tracking-wide">
                Importar Planilla
            </h1>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                <span class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span>
            </div>
            <p class="text-base text-[#6B7280] font-black uppercase ">Módulo de Administración</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 items-start font-['Lato',sans-serif]">
            
            <div class="lg:col-span-2 flex flex-col gap-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex gap-3 shadow-sm items-start">
                    <div class="mt-0.5 bg-green-500 text-white rounded-full p-1 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-green-800 font-black text-[12px] uppercase tracking-widest mb-0.5">Protección Antiduplicados Activada</h4>
                        <p class="text-green-700 text-[13px] font-medium leading-snug">
                            Si subís un Excel que ya habías cargado antes, el sistema <b>NO duplicará</b> los partidos. Solamente actualizará la información.
                        </p>
                    </div>
                </div>

                <div class="bg-white border border-[#E5E7EB] rounded p-7 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        
                        <div class="flex flex-col gap-2">
                            <label for="fecha_base" class="flex items-center gap-2 text-base font-black tracking-[1px] uppercase text-[#0D1B3E]">
                                <span class="text-[#D4A843]">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="2" y="3" width="10" height="9" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M4 1.5v2m6-2v2M2 6h10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                                </span>
                                Seleccione el Lunes de esta semana
                            </label>
                            <div class="relative rounded-lg border-[1.5px] border-[#D1D5DB] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15">
                                <input type="date" id="fecha_base" v-model="form.fecha_base" required
                                       class="w-full px-4 py-3 border-none bg-transparent text-[13px] text-[#111827] font-semibold rounded-lg focus:ring-0 uppercase tracking-wide">
                            </div>
                            <p class="text-xs text-[#6B7280] font-medium mt-0.5">El sistema tomará el MES y AÑO de esta fecha, y le sumará el número de día que esté escrito en el Excel (Ej: "SABADO 21").</p>
                        </div>

                        <div class="relative group mt-2">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#D4A843]/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded"></div>
                            <div class="relative border-2 border-dashed border-[#D1D5DB] group-hover:border-[#D4A843]/70 rounded p-12 flex flex-col items-center justify-center transition-all bg-[#F9FAFB] group-hover:bg-white overflow-hidden min-h-[250px]">
                                
                                <input type="file" id="documento" @change="procesarArchivo" accept=".xlsx, .csv" required
                                       class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer">
                                
                                <div v-if="!nombreArchivo" class="flex flex-col items-center text-center">
                                    <div class="w-14 h-14 mb-4 rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center shadow-sm text-[#9CA3AF] group-hover:text-[#D4A843] group-hover:border-[#D4A843]/30 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </div>
                                    <p class="text-[15px] font-bold text-[#374151]">Hacé clic o arrastra tu archivo Excel acá</p>
                                    <p class="text-xs font-medium text-[#9CA3AF] mt-1.5 uppercase tracking-wider">Formatos soportados: .XLSX, .CSV</p>
                                </div>

                                <div v-else class="flex flex-col items-center text-center z-20">
                                    <div class="flex items-center gap-3 bg-[#0D1B3E] px-6 py-3 rounded-lg shadow-md border border-[#D4A843]/40">
                                        <svg class="w-5 h-5 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-white font-bold text-[14px] truncate max-w-[250px]">{{ nombreArchivo }}</span>
                                    </div>
                                    <p class="text-base font-black text-[#D4A843] mt-4 uppercase tracking-widest">Archivo Listo para Procesar</p>
                                    <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-wider">Hacé clic nuevamente si querés cambiarlo</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E5E7EB] mt-2">
                            <button type="submit" :disabled="form.processing || !nombreArchivo || !form.fecha_base"
                                    class="group relative overflow-hidden w-full py-[14px] px-6 bg-gradient-to-br from-[#0D1B3E] to-[#1E3370] text-white border border-[#D4A843]/40 rounded-lg text-[13px] font-black tracking-[2px] uppercase transition-all duration-300 hover:-translate-y-[1px] hover:shadow-[0_8px_20px_rgba(13,27,62,0.2),0_0_0_1px_rgba(212,168,67,0.4)] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                
                                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A843]/15 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <span v-if="form.processing" class="relative z-10 flex items-center justify-center gap-2.5">
                                    <svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" stroke-dasharray="20" stroke-dashoffset="5"/></svg>
                                    Extrayendo Datos Inteligentes...
                                </span>
                                <span v-else class="relative z-10 flex items-center justify-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none"><path d="M7 1v12M1.5 6.5h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                    Extraer Fechas y Cargar Pizarra
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-[#0D1B3E] rounded shadow-lg border border-[#D4A843]/20 relative overflow-hidden h-full">
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#D4A843]/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 p-7">
                    <div class="flex items-center gap-2.5 mb-5">
                        <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                        <h3 class="text-[#D4A843] font-black text-base uppercase">Instrucciones del Sistema</h3>
                    </div>
                    <ul class="text-white/80 font-medium space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="text-[#D4A843] font-black mt-0.5 opacity-70">01.</span>
                            <p class="leading-relaxed">Asegúrese de que el Excel contenga una columna llamada <strong class="text-white">DIA</strong> y que especifique el número (Ej: "SABADO 21")</p>
                        </li>
                        </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
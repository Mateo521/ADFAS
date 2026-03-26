<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    partidos: Array,
    arbitros: Array
});

const form = useForm({
    asignaciones: props.partidos.map(partido => ({
        partido_id: partido.id,
        principal_id: '',
        asistente_id: ''
    }))
});

const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true
});

// ¡LA MAGIA ANTI-TOPES DE HORARIO!
const arbitrosDisponibles = (indexPartidoActual) => {
    const partidoActual = props.partidos[indexPartidoActual];
    
    const arbitrosOcupados = form.asignaciones.map((asignacion, i) => {
        const otroPartido = props.partidos[i];
        
        if (i !== indexPartidoActual && 
            otroPartido.fecha === partidoActual.fecha && 
            otroPartido.hora_inicio === partidoActual.hora_inicio) {
            
            return [asignacion.principal_id, asignacion.asistente_id];
        }
        return [];
    }).flat().filter(id => id !== ''); 

    return props.arbitros.filter(arbitro => !arbitrosOcupados.includes(arbitro.id));
};

const submit = () => {
    form.post(route('admin.asignar.store'), {
        preserveScroll: true,
        onSuccess: () => {
            Toast.fire({ icon: 'success', title: '¡Designaciones publicadas y enviadas a los árbitros!' });
        },
    });
};
</script>

<template>
    <Head title="Pizarra de Asignaciones - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-['Playfair_Display',serif] text-[28px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-2 uppercase tracking-wide">
                    Pizarra de Asignaciones
                </h1>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                    <span class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span>
                </div>
                <p class="text-[11px] text-[#6B7280] font-black uppercase tracking-[0.2em]">Módulo de Administración</p>
            </div>

            <div v-if="partidos.length > 0" class="flex items-center gap-3 bg-white border border-[#D4A843]/30 px-5 py-2.5 rounded-lg shadow-sm">
                <div class="w-2 h-2 rounded-full bg-[#D4A843] animate-pulse"></div>
                <span class="text-[#0D1B3E] font-black text-[12px] uppercase tracking-widest">{{ partidos.length }} Partidos pendientes</span>
            </div>
        </div>

        <div v-if="partidos.length === 0" class="bg-white border-2 border-dashed border-[#E5E7EB] rounded p-12 text-center shadow-sm max-w-3xl mx-auto mt-10">
            <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-green-100">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="font-['Playfair_Display',serif] text-[24px] font-extrabold text-[#0D1B3E] mb-2 uppercase tracking-wide">¡Todo al día!</h2>
            <p class="text-[14px] text-[#6B7280] font-medium">No hay partidos pendientes de asignación en este momento. Sube una nueva planilla Excel para comenzar.</p>
            <Link :href="route('admin.importar.index')" class="inline-block mt-6 px-6 py-2.5 bg-gradient-to-r from-[#0D1B3E] to-[#162554] text-white text-[11px] font-black uppercase tracking-widest rounded-lg shadow-md hover:shadow-lg transition-all">
                Ir a Importar Excel
            </Link>
        </div>

        <form v-else @submit.prevent="submit" class="flex flex-col h-[calc(100vh-220px)]">
            
            <div class="bg-white border border-[#E5E7EB] rounded shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-auto max-h-[calc(100vh-320px)]">
                <table class="w-full text-left whitespace-nowrap relative">
                    
                    <thead class="sticky top-0 z-20 shadow-sm">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">Fecha / Hora</th>
                            <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB]">Cat.</th>
                            <th scope="col" class="px-5 py-3.5 border-b border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-right bg-[#F9FAFB]">Local</th>
                            <th scope="col" class="px-2 py-3.5 border-b border-[#E5E7EB] bg-[#F9FAFB]"></th>
                            <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">Visitante</th>
                            <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">Cancha</th>
                            <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-blue-50/50 w-[280px]">Árbitro Principal</th>
                            <th scope="col" class="px-5 py-3.5 border-b border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-blue-50/50 w-[280px]">Asistente / 2do</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(asignacion, index) in form.asignaciones" :key="asignacion.partido_id" 
                            class="border-b border-[#E5E7EB] hover:bg-gray-50 transition-colors group">
                            
                            <td class="px-5 py-3 border-r border-[#E5E7EB]">
                                <span class="font-bold text-[13px] text-[#374151]">{{ partidos[index].fecha }}</span> 
                                <span class="text-[#D4A843] font-black text-[13px] ml-1.5">{{ partidos[index].hora_inicio.substring(0,5) }}</span>
                            </td>
                            
                            <td class="px-5 py-3 border-r border-[#E5E7EB] text-center">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-black uppercase tracking-wider border border-gray-200">
                                    {{ partidos[index].categoria }}
                                </span>
                            </td>
                            
                            <td class="px-5 py-3 text-right font-black text-[13px] text-[#0D1B3E] uppercase">{{ partidos[index].equipo_local }}</td>
                            <td class="px-2 py-3 text-center text-[10px] font-bold text-gray-400">VS</td>
                            <td class="px-5 py-3 font-black text-[13px] text-[#0D1B3E] uppercase border-r border-[#E5E7EB]">{{ partidos[index].equipo_visitante }}</td>
                            
                            <td class="px-5 py-3 border-r border-[#E5E7EB] text-[12px] font-semibold text-[#6B7280] truncate max-w-[150px]" :title="partidos[index].cancha">
                                {{ partidos[index].cancha }}
                            </td>

                            <td class="px-3 py-2 border-r border-[#E5E7EB] bg-blue-50/10 group-hover:bg-blue-50/30 transition-colors">
                                <select v-model="asignacion.principal_id" class="block w-full text-[12px] font-semibold border-[#D1D5DB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded-md py-1.5 px-3 bg-white shadow-sm cursor-pointer text-[#111827]">
                                    <option value="" disabled class="text-gray-400">-- Seleccionar --</option>
                                    <option v-for="arbitro in arbitrosDisponibles(index)" :key="arbitro.id" :value="arbitro.id" class="text-gray-900 font-medium">
                                        {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                    </option>
                                </select>
                            </td>

                            <td class="px-3 py-2 bg-blue-50/10 group-hover:bg-blue-50/30 transition-colors">
                                <select v-model="asignacion.asistente_id" class="block w-full text-[12px] font-semibold border-[#D1D5DB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded-md py-1.5 px-3 bg-white shadow-sm cursor-pointer text-[#111827]">
                                    <option value="" class="text-gray-400">-- Sin asistente --</option>
                                    <option v-for="arbitro in arbitrosDisponibles(index)" :key="arbitro.id" :value="arbitro.id" class="text-gray-900 font-medium">
                                        {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                    </option>
                                </select>
                            </td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-5 border border-t-0 border-[#E5E7EB] rounded-b-xl shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] flex items-center justify-between z-30 shrink-0">
                <p class="text-[12px] text-[#6B7280] font-medium hidden md:block">
                    Al confirmar, se enviarán notificaciones inmediatas a los árbitros seleccionados.
                </p>
                <button type="submit" :disabled="form.processing"
                        class="group relative overflow-hidden px-8 py-3.5 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[12px] font-black uppercase tracking-[2px] rounded-lg transition-all duration-300 shadow-[0_2px_8px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] hover:shadow-[0_8px_20px_rgba(168,124,32,0.35)] disabled:opacity-65 disabled:cursor-not-allowed disabled:transform-none w-full md:w-auto">
                    
                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <span v-if="form.processing" class="relative z-10 flex items-center justify-center gap-2">
                        <svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" stroke-dasharray="20" stroke-dashoffset="5"/>
                        </svg>
                        Procesando envíos...
                    </span>
                    <span v-else class="relative z-10 flex items-center justify-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Confirmar Designaciones
                    </span>
                </button>
            </div>

        </form>

    </AuthenticatedLayout>
</template>
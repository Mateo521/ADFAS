<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link , router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    arbitro: Object,
    stats: Object,
    historial: Array,
    licencias: Array
});

const getStatusClasses = (estado) => {
    if (estado === 'confirmado') return 'bg-green-50 text-green-700 border-green-200';
    if (estado === 'rechazado') return 'bg-red-50 text-red-700 border-red-200';
    return 'bg-[#D4A843]/10 text-[#A87C20] border-[#D4A843]/30'; 
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const [year, month, day] = fecha.split('-');
    return `${day}/${month}/${year}`;
};

const cambiarEstadoLicencia = (id, nuevoEstado) => {
    Swal.fire({
        title: `¿${nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1)} certificado?`,
        icon: nuevoEstado === 'aprobado' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado === 'aprobado' ? '#10B981' : '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: `Sí, ${nuevoEstado}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('admin.licencias.estado', id), { estado: nuevoEstado }, {
                preserveScroll: true,
                onSuccess: () => Swal.fire('¡Listo!', `Certificado ${nuevoEstado}.`, 'success')
            });
        }
    });
};
</script>

<template>
    <Head :title="`Ficha: ${arbitro.apellido} - ADFAS`" />

    <AuthenticatedLayout>
        <div class="font-['Lato',sans-serif] max-w-6xl mx-auto pb-12">
            
            <div class="mb-6">
                <Link :href="route('admin.arbitros.index')" class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6B7280] hover:text-[#D4A843] uppercase tracking-widest transition-colors group">
                    <div class="p-1.5 rounded-full bg-white border border-[#E5E7EB] group-hover:border-[#D4A843] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </div>
                    Volver al Directorio
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-white border border-[#E5E7EB]  p-8 text-center shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 inset-x-0 h-32 bg-[#0D1B3E] border-b-4 border-[#D4A843]"></div>
                        
                        <div class="relative w-32 h-32 mx-auto rounded-full bg-[#F9FAFB] border-4 border-white shadow-lg overflow-hidden flex items-center justify-center mt-8 mb-4">
                            <img v-if="arbitro.foto_perfil" :src="`/storage/${arbitro.foto_perfil}`" class="w-full h-full object-cover">
                            <span v-else class="text-[#0D1B3E] font-black text-4xl">{{ arbitro.name.charAt(0) }}{{ arbitro.apellido.charAt(0) }}</span>
                        </div>
                        
                        <h2 class="text-[22px] font-black text-[#0D1B3E] uppercase tracking-wide leading-tight mb-1">
                            {{ arbitro.name }} {{ arbitro.apellido }}
                        </h2>
                        <p class="text-[12px] font-bold text-[#6B7280] uppercase tracking-widest mb-6">Colegiado ADFAS</p>
                        
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-center gap-2 text-[13px] font-bold text-[#374151]">
                                <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ arbitro.email }}
                            </div>
                            
                            <a v-if="arbitro.telefono" :href="`https://wa.me/${arbitro.telefono}`" target="_blank"
                               class="mt-4 flex items-center justify-center gap-2 w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-lg transition-colors shadow-sm cursor-pointer">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.383 0 12.03c0 2.124.553 4.195 1.605 6.01L.031 24l6.143-1.579a12.015 12.015 0 005.857 1.522c6.645 0 12.03-5.385 12.03-12.03C24 5.383 18.675 0 12.031 0zm0 21.944c-1.802 0-3.565-.483-5.111-1.398l-.367-.217-3.801.977 1.015-3.69-.239-.38a9.988 9.988 0 01-1.525-5.305c0-5.508 4.484-9.992 9.992-9.992s9.992 4.484 9.992 9.992-4.484 9.992-9.992 9.992zm5.48-7.48c-.301-.15-1.782-.88-2.057-.98-.276-.1-.478-.15-.678.15-.2.302-.779.98-.954 1.18-.175.201-.351.226-.652.076a8.212 8.212 0 01-2.42-1.493 8.973 8.973 0 01-1.678-2.083c-.176-.301-.019-.465.132-.615.136-.135.301-.351.452-.526.15-.176.2-.302.3-.502.101-.202.051-.377-.025-.527-.075-.15-.678-1.63-.928-2.232-.243-.586-.489-.507-.678-.516-.175-.008-.376-.008-.577-.008a1.11 1.11 0 00-.803.376 3.36 3.36 0 00-1.054 2.502c0 1.956 1.405 3.847 1.605 4.122.2.276 2.81 4.288 6.804 6.015 2.593 1.123 3.633 1.206 4.887 1.018.99-.148 3.123-1.275 3.563-2.507.44-1.232.44-2.285.308-2.507-.132-.222-.478-.352-.779-.502z"/></svg>
                                <span class="text-[12px] font-black uppercase tracking-widest">Enviar WhatsApp</span>
                            </a>
                            <div v-else class="mt-4 w-full py-3.5 bg-gray-100 text-gray-400 border border-dashed border-gray-300 rounded-lg text-[11px] font-black uppercase tracking-widest">
                                Sin teléfono registrado
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white border border-[#E5E7EB]  p-5 border-l-4 border-l-[#0D1B3E] shadow-sm">
                            <p class="text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] mb-1">Total Asignado</p>
                            <p class="text-3xl font-black text-[#0D1B3E]">{{ stats.total }}</p>
                        </div>
                        <div class="bg-white border border-green-100  p-5 border-l-4 border-l-green-500 shadow-sm">
                            <p class="text-[10px] font-black text-green-600/70 uppercase tracking-[0.15em] mb-1">Confirmadas</p>
                            <p class="text-3xl font-black text-green-600">{{ stats.confirmadas }}</p>
                        </div>
                        <div class="bg-white border border-amber-100  p-5 border-l-4 border-l-amber-400 shadow-sm">
                            <p class="text-[10px] font-black text-amber-700/70 uppercase tracking-[0.15em] mb-1">Pendientes</p>
                            <p class="text-3xl font-black text-amber-600">{{ stats.pendientes }}</p>
                        </div>
                        <div class="bg-white border border-red-100  p-5 border-l-4 border-l-red-500 shadow-sm">
                            <p class="text-[10px] font-black text-red-600/70 uppercase tracking-[0.15em] mb-1">Ausencias</p>
                            <p class="text-3xl font-black text-red-600">{{ stats.rechazadas }}</p>
                        </div>
                    </div>

                    <div>
                        <div v-if="licencias && licencias.length > 0" class="mb-10">
                        <h3 class=" text-[20px] font-extrabold text-[#0D1B3E] mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Certificados y Licencias
                        </h3>
                        
                        <div class="grid gap-4">
                            <div v-for="licencia in licencias" :key="licencia.id" class="bg-white border border-[#E5E7EB]  p-5 shadow-sm flex flex-col md:flex-row gap-4 items-start md:items-center justify-between" :class="{'border-l-4 border-l-amber-400': licencia.estado === 'pendiente', 'border-l-4 border-l-green-500': licencia.estado === 'aprobado', 'border-l-4 border-l-red-500': licencia.estado === 'rechazado'}">
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded" :class="{'bg-amber-100 text-amber-700': licencia.estado === 'pendiente', 'bg-green-100 text-green-700': licencia.estado === 'aprobado', 'bg-red-100 text-red-700': licencia.estado === 'rechazado'}">
                                            {{ licencia.estado }}
                                        </span>
                                        <span class="text-[12px] font-bold text-gray-500">
                                            {{ formatearFecha(licencia.fecha_desde) }} al {{ formatearFecha(licencia.fecha_hasta) }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-[#0D1B3E]">{{ licencia.motivo }}</p>
                                </div>

                                <div class="flex items-center gap-2 w-full md:w-auto">
                                    
                                    <a v-if="licencia.certificado || licencia.certificado_path || licencia.archivo" 
                                       :href="`/storage/${licencia.certificado || licencia.certificado_path || licencia.archivo}`" 
                                       target="_blank" 
                                       class="flex-1 md:flex-none text-center px-4 py-2 bg-[#0D1B3E]/5 text-[#0D1B3E] hover:bg-[#D4A843]/10 hover:text-[#A87C20] hover:border-[#D4A843]/30 border border-[#0D1B3E]/20 text-xs font-black uppercase rounded transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Ver Certificado
                                    </a>
                                    
                                    <template v-if="licencia.estado === 'pendiente'">
                                        <button @click="cambiarEstadoLicencia(licencia.id, 'aprobado')" class="flex-1 md:flex-none px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-black uppercase rounded transition-colors shadow-sm">
                                            Aprobar
                                        </button>
                                        <button @click="cambiarEstadoLicencia(licencia.id, 'rechazado')" class="flex-1 md:flex-none px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-black uppercase rounded transition-colors shadow-sm">
                                            Rechazar
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                        <h3 class=" text-[20px] font-extrabold text-[#0D1B3E] mb-4">
                            Últimos 10 partidos
                        </h3>
                        
                        <div class="bg-white border border-[#E5E7EB]  shadow-sm overflow-hidden">
                            <table class="w-full text-left whitespace-nowrap">
                                <thead class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                                    <tr>
                                        <th class="px-5 py-3 text-[10px] font-black text-[#6B7280] uppercase tracking-widest">Fecha</th>
                                        <th class="px-5 py-3 text-[10px] font-black text-[#6B7280] uppercase tracking-widest">Encuentro</th>
                                        <th class="px-5 py-3 text-[10px] font-black text-[#6B7280] uppercase tracking-widest">Función</th>
                                        <th class="px-5 py-3 text-[10px] font-black text-[#6B7280] uppercase tracking-widest text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="historial.length === 0">
                                        <td colspan="4" class="px-5 py-8 text-center text-[13px] text-gray-500 font-bold">No tiene historial de partidos.</td>
                                    </tr>
                                    <tr v-for="desig in historial" :key="desig.id" class="border-b border-[#E5E7EB] last:border-0 hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            <span class="font-bold text-[12px] text-[#374151] block">{{ desig.partido.fecha }}</span>
                                            <span class="text-[#D4A843] font-black text-[11px]">{{ desig.partido.hora_inicio.substring(0,5) }} hs</span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="text-[12px] font-black text-[#0D1B3E] uppercase">{{ desig.partido.equipo_local }}</div>
                                            <div class="text-[12px] font-black text-[#0D1B3E] uppercase mt-0.5">{{ desig.partido.equipo_visitante }}</div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="text-[10px] font-black text-[#0D1B3E] uppercase tracking-wider bg-blue-50 px-2 py-1 rounded border border-blue-100">{{ desig.funcion }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-[9px] px-2 py-0.5 rounded border uppercase font-black tracking-widest" :class="getStatusClasses(desig.estado_confirmacion)">
                                                {{ desig.estado_confirmacion }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
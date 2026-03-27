<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    partidos: Object,
    arbitros: Array,  
    filtros: Object
});

const filtroFecha = ref(props.filtros.fecha || '');
const filtroCategoria = ref(props.filtros.categoria || '');
const filtroEquipo = ref(props.filtros.equipo || '');

const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

 
const modalAbierto = ref(false);
const partidoSeleccionado = ref(null);

const formReasignar = useForm({
    principal_id: '',
    asistente_id: ''
});

const abrirModalReasignar = (partido) => {
    partidoSeleccionado.value = partido;
    const principal = getPrincipal(partido);
    const asistente = getAsistente(partido);
 
    formReasignar.principal_id = principal ? principal.user_id : '';
    formReasignar.asistente_id = asistente ? asistente.user_id : '';
    
    modalAbierto.value = true;
};

const cerrarModal = () => {
    modalAbierto.value = false;
    partidoSeleccionado.value = null;
    formReasignar.reset();
};
 
const arbitrosDisponibles = computed(() => {
    if (!partidoSeleccionado.value) return props.arbitros;
    
    const pActual = partidoSeleccionado.value;
     
    const ocupados = props.partidos.data.map(p => {
        if (p.id !== pActual.id && p.fecha === pActual.fecha && p.hora_inicio === pActual.hora_inicio) {
            return p.designaciones.map(d => d.user_id);
        }
        return [];
    }).flat();

    return props.arbitros.filter(a => !ocupados.includes(a.id));
});

const confirmarReasignacion = () => {
    formReasignar.post(route('admin.historial.reasignar', partidoSeleccionado.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal();
            Toast.fire({ icon: 'success', title: 'Árbitros reasignados con éxito.' });
        }
    });
};
 
let intervalId = null;

const obtenerEstadosEnPantalla = () => {
    let estados = {};
    props.partidos.data.forEach(partido => {
        partido.designaciones.forEach(desig => {
            estados[desig.id] = desig.estado_confirmacion;
        });
    });
    return estados;
};

let estadosAnteriores = obtenerEstadosEnPantalla();

onMounted(() => {
    intervalId = setInterval(() => {
        if (!modalAbierto.value) {
            router.reload({ 
                only: ['partidos'], 
                preserveScroll: true, 
                preserveState: true,
                onSuccess: (page) => {
                    const nuevosPartidos = page.props.partidos.data;
                    
                    nuevosPartidos.forEach(partido => {
                        partido.designaciones.forEach(desig => {
                            const estadoViejo = estadosAnteriores[desig.id];
                            const estadoNuevo = desig.estado_confirmacion;

                            if (estadoViejo && estadoViejo !== estadoNuevo) {
                                const nombreArbitro = `${desig.user.name} ${desig.user.apellido}`.toUpperCase();
                                const nombrePartido = `${partido.equipo_local} VS ${partido.equipo_visitante}`;
                                const horaPartido = partido.hora_inicio.substring(0,5);

                               
                                if (estadoNuevo === 'confirmado' || estadoNuevo === 'rechazado') {
                                    
                                    const esConfirmado = estadoNuevo === 'confirmado';
                                    
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: esConfirmado ? 'success' : 'error',
                                        title: esConfirmado ? '¡Asistencia Confirmada!' : ' Asistencia Rechazada',
                                        html: `
                                            <div style="text-align: left; font-size: 13px; margin-top: 5px; font-family: 'Lato', sans-serif;">
                                                <b>Árbitro:</b> ${nombreArbitro}<br>
                                                <b>Partido:</b> <span style="color: #D4A843; font-weight: bold;">${nombrePartido}</span><br>
                                                <b>Hora:</b> ${horaPartido} hs
                                            </div>
                                        `,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Entendido',
                                        confirmButtonColor: '#0D1B3E',
                                        timer: undefined, 
                                        padding: '1em',
                                        customClass: {
                                            popup: 'border border-gray-200 shadow-xl'
                                        }
                                    });
                                }
                            }
                            
                            estadosAnteriores[desig.id] = estadoNuevo;
                        });
                    });
                }
            });
        }
    }, 5000); 
});

onUnmounted(() => { if (intervalId) clearInterval(intervalId); });

 
const aplicarFiltros = () => {
    router.get(route('admin.historial.index'), { fecha: filtroFecha.value, categoria: filtroCategoria.value, equipo: filtroEquipo.value }, { preserveState: true, replace: true });
};
const limpiarFiltros = () => {
    filtroFecha.value = ''; filtroCategoria.value = ''; filtroEquipo.value = ''; aplicarFiltros();
};
const getPrincipal = (partido) => partido.designaciones.find(d => d.funcion === 'ARBITRO PRINCIPAL');
const getAsistente = (partido) => partido.designaciones.find(d => d.funcion === 'ASISTENTE 1');

const getStatusClasses = (estado) => {
    if (estado === 'confirmado') return 'bg-green-50 text-green-700 border-green-200';
    if (estado === 'rechazado') return 'bg-red-50 text-red-700 border-red-200';
    return 'bg-[#D4A843]/10 text-[#A87C20] border-[#D4A843]/30'; 
};
</script>

<template>
    <Head title="Gestión y Monitor - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="font-['Lato',sans-serif]">
            
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="font-['Playfair_Display',serif]- text-[28px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-2 uppercase tracking-wide">
                        Gestión y Monitoreo
                    </h1>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                        <span class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span>
                    </div>
                    <p class="text-[12px] text-[#6B7280] font-medium tracking-wide">Supervisa el estado de confirmación de los árbitros en tiempo real.</p>
                </div>
                <div class="hidden md:flex items-center gap-2 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full shadow-sm">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-[10px] font-black text-green-700 uppercase tracking-widest">En Vivo</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded border border-[#E5E7EB] shadow-[0_2px_10px_rgba(0,0,0,0.02)] mb-6 flex flex-wrap gap-4 items-end relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-[#D4A843]"></div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Buscar por Fecha</label>
                    <input type="date" v-model="filtroFecha" @change="aplicarFiltros" class="w-[160px] px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase tracking-wide">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Categoría</label>
                    <input type="text" v-model="filtroCategoria" @keyup.enter="aplicarFiltros" placeholder="Ej: PRIMERA A" class="w-[160px] px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase">
                </div>
                <div class="flex flex-col gap-1.5 flex-1 min-w-[200px]">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Buscar Equipo</label>
                    <input type="text" v-model="filtroEquipo" @keyup.enter="aplicarFiltros" placeholder="Buscar por club..." class="w-full px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase">
                </div>
                <div class="flex gap-2">
                    <button @click="limpiarFiltros" class="px-5 py-2 rounded-lg text-[12px] font-black uppercase tracking-widest text-[#6B7280] bg-white border border-[#D1D5DB] hover:bg-gray-50 transition-colors">Limpiar</button>
                    <button @click="aplicarFiltros" class="px-6 py-2 rounded-lg text-[12px] font-black uppercase tracking-widest text-white bg-gradient-to-br from-[#0D1B3E] to-[#1E3370] border border-[#D4A843]/30 hover:shadow-md transition-all hover:-translate-y-[1px]">Buscar</button>
                </div>
            </div>

             <div class="bg-white border border-[#E5E7EB] rounded shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-auto max-h-[calc(100vh-320px)]">
                <table class="w-full text-left whitespace-nowrap relative">
                    
                    <thead class="sticky top-0 z-20 shadow-sm">
                        <tr>
                            <th class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">Fecha / Hora</th>
                            <th class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB]">Cat.</th>
                            <th class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB]">Partido</th>
                            
                            <th class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-[#EFF6FF]">Árbitro Principal</th>
                            <th class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-[#EFF6FF]">Asistente</th>
                            
                            <th class="px-5 py-3.5 border-b border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="partidos.data.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center"><span class="text-[14px] text-gray-500 font-bold">No se encontraron partidos.</span></td>
                        </tr>
                        <tr v-for="partido in partidos.data" :key="partido.id" class="border-b border-[#E5E7EB] hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 border-r border-[#E5E7EB]">
                                <span class="font-bold text-[13px] text-[#374151] block">{{ partido.fecha }}</span> 
                                <span class="text-[#D4A843] font-black text-[13px]">{{ partido.hora_inicio.substring(0,5) }} hs</span>
                            </td>
                            <td class="px-5 py-3 border-r border-[#E5E7EB] text-center">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-black uppercase tracking-wider border border-gray-200">{{ partido.categoria }}</span>
                            </td>
                            <td class="px-5 py-3 border-r border-[#E5E7EB] text-center min-w-[200px]">
                                <div class="font-black text-[13px] text-[#0D1B3E] uppercase">{{ partido.equipo_local }}</div>
                                <div class="text-[10px] text-gray-400 font-bold my-0.5">VS</div>
                                <div class="font-black text-[13px] text-[#0D1B3E] uppercase">{{ partido.equipo_visitante }}</div>
                                <div class="text-base text-[#6B7280] mt-1.5 font-medium flex items-center justify-center gap-1">{{ partido.cancha }}</div>
                            </td>
                            <td class="px-5 py-3 border-r border-[#E5E7EB] bg-blue-50/10">
                                <div v-if="getPrincipal(partido)" class="flex flex-col items-start gap-1.5">
                                    <p class="font-bold text-[13px] text-[#111827] uppercase">{{ getPrincipal(partido).user.apellido }}, {{ getPrincipal(partido).user.name.charAt(0) }}.</p>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full border uppercase font-black tracking-widest transition-colors duration-500" :class="getStatusClasses(getPrincipal(partido).estado_confirmacion)">{{ getPrincipal(partido).estado_confirmacion }}</span>
                                </div>
                                <span v-else class="text-[12px] text-gray-400 font-medium italic">Sin designar</span>
                            </td>
                            <td class="px-5 py-3 border-r border-[#E5E7EB] bg-blue-50/10">
                                <div v-if="getAsistente(partido)" class="flex flex-col items-start gap-1.5">
                                    <p class="font-bold text-[13px] text-[#111827] uppercase">{{ getAsistente(partido).user.apellido }}, {{ getAsistente(partido).user.name.charAt(0) }}.</p>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full border uppercase font-black tracking-widest transition-colors duration-500" :class="getStatusClasses(getAsistente(partido).estado_confirmacion)">{{ getAsistente(partido).estado_confirmacion }}</span>
                                </div>
                                <span v-else class="text-[12px] text-gray-400 font-medium italic">Sin designar</span>
                            </td>
                            
                            <td class="px-5 py-3 text-center">
                                <button @click="abrirModalReasignar(partido)" class="text-[#0D1B3E] font-bold text-base uppercase tracking-wider bg-[#0D1B3E]/5 px-3 py-1.5 rounded border border-[#0D1B3E]/20 hover:bg-[#D4A843]/10 hover:text-[#A87C20] hover:border-[#D4A843]/30 transition-colors">
                                    Reasignar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center text-[12px] text-[#6B7280] font-medium gap-4" v-if="partidos.links && partidos.links.length > 3">
                <div>Mostrando <span class="font-bold text-[#0D1B3E]">{{ partidos.from || 0 }}</span> a <span class="font-bold text-[#0D1B3E]">{{ partidos.to || 0 }}</span> de <span class="font-bold text-[#0D1B3E]">{{ partidos.total }}</span> registros</div>
                <div class="flex gap-1 shadow-sm rounded-lg overflow-hidden border border-[#E5E7EB]">
                    <Link v-for="(link, key) in partidos.links" :key="key" :href="link.url || '#'" class="px-3.5 py-2 border-r border-[#E5E7EB] last:border-0 font-bold transition-colors" :class="{'bg-[#0D1B3E] text-white': link.active, 'bg-white text-[#374151] hover:bg-gray-50 hover:text-[#D4A843]': !link.active && link.url, 'bg-gray-50 text-gray-400 cursor-not-allowed': !link.url}" v-html="link.label"></Link>
                </div>
            </div>

        </div>

        <div v-if="modalAbierto" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 font-['Lato',sans-serif]">
            <div class="bg-white rounded shadow-2xl w-full max-w-lg overflow-hidden animate-formAppear border border-[#D4A843]/30">
                
                <div class="bg-[#0D1B3E] p-6 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#D4A843]/20 rounded-full blur-2xl"></div>
                    <h2 class="relative z-10 font-['Playfair_Display',serif]- text-[22px] font-extrabold text-white uppercase tracking-wide">
                        Modificar Designación
                    </h2>
                    <p class="relative z-10 text-[12px] font-medium text-[#D4A843] mt-1 uppercase tracking-widest">
                        {{ partidoSeleccionado.equipo_local }} VS {{ partidoSeleccionado.equipo_visitante }}
                    </p>
                </div>

                <div class="p-8">
                    <form @submit.prevent="confirmarReasignacion" class="space-y-6">
                        
                        <div class="flex flex-col gap-2">
                            <label class="text-base font-black tracking-[1px] uppercase text-[#0D1B3E]">Nuevo Árbitro Principal</label>
                            <select v-model="formReasignar.principal_id" required class="w-full px-4 py-3 bg-gray-50 border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                <option value="" disabled class="text-gray-400">-- Seleccione un Árbitro --</option>
                                <option v-for="arbitro in arbitrosDisponibles" :key="arbitro.id" :value="arbitro.id">
                                    {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-base font-black tracking-[1px] uppercase text-[#0D1B3E]">Nuevo Asistente (Opcional)</label>
                            <select v-model="formReasignar.asistente_id" class="w-full px-4 py-3 bg-gray-50 border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                <option value="">-- Dejar sin Asistente --</option>
                                <option v-for="arbitro in arbitrosDisponibles" :key="arbitro.id" :value="arbitro.id">
                                    {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                </option>
                            </select>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex gap-3 mt-2">
                            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-base font-medium text-blue-800 leading-tight">Al guardar, los árbitros anteriores van a ser desvinculados y los nuevos van a recibir una notificación. Sus estados van a volver a PENDIENTE.</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
                            <button type="button" @click="cerrarModal" class="px-6 py-3 rounded-lg text-[12px] font-black uppercase tracking-widest text-[#6B7280] bg-white border border-[#D1D5DB] hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="formReasignar.processing" class="px-6 py-3 rounded-lg text-[12px] font-black uppercase tracking-widest text-[#0D1B3E] bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] hover:shadow-md transition-all disabled:opacity-50">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

     

    </AuthenticatedLayout>
    
</template>
   <style scoped>
        .animate-formAppear { animation: formAppear 0.3s ease-out forwards; }
        @keyframes formAppear {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        </style>
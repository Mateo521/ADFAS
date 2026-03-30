<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router, Link , useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, onMounted, onUnmounted } from 'vue';  

const user = usePage().props.auth.user;

const props = defineProps({
    esAdmin: Boolean,
    designaciones: Array,
    telefonoAdmin: String,
    stats: Object,
    statsAdmin: Object,
    noticias: Array,
    listaPendientes: Array, 
    listaRechazadas: Array,
    licenciasPendientes: Array
});

const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true
});


const mostrarModalLicencia = ref(false);

const formLicencia = useForm({
    fecha_desde: '',
    fecha_hasta: '',
    motivo: '',
    certificado: null
});
 
const enviarLicencia = () => {
    formLicencia.post(route('licencias.store'), {
        preserveScroll: true,
        onSuccess: () => {
            mostrarModalLicencia.value = false;
            formLicencia.reset();
            Toast.fire({ icon: 'success', title: 'Certificado enviado a revisión' });
        }
    });
};

const mostrarModalDetalles = ref(false);
const tituloModal = ref('');
const datosModal = ref([]);

const abrirModalDetalles = (tipo) => {
    if (tipo === 'pendientes') {
        tituloModal.value = 'Árbitros Pendientes';
        datosModal.value = props.listaPendientes || [];
    } else if (tipo === 'rechazadas') {
        tituloModal.value = 'Ausencias y Rechazos';
        datosModal.value = props.listaRechazadas || [];
    }
    mostrarModalDetalles.value = true;
};


let intervalId = null;
let confirmadasAnteriores = props.statsAdmin ? props.statsAdmin.confirmadas : 0;
let rechazadasAnteriores = props.statsAdmin ? props.statsAdmin.rechazadas : 0;

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const partes = fecha.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

let idsAnteriores = props.designaciones ? props.designaciones.map(d => d.id).join(',') : '';
let licenciasAnteriores = props.licenciasPendientes ? props.licenciasPendientes.length : 0;
onMounted(() => {
    intervalId = setInterval(() => {

        if (props.esAdmin) {
            router.reload({
                
                only: ['statsAdmin', 'listaPendientes', 'listaRechazadas', 'licenciasPendientes'], 
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    const nuevasConfirmadas = page.props.statsAdmin.confirmadas;
                    const nuevasRechazadas = page.props.statsAdmin.rechazadas;
                    const nuevasLicencias = page.props.licenciasPendientes ? page.props.licenciasPendientes.length : 0;

                    if (nuevasConfirmadas > confirmadasAnteriores) {
                        Toast.fire({ icon: 'success', title: 'Un árbitro acaba de CONFIRMAR su asistencia' });
                        confirmadasAnteriores = nuevasConfirmadas;
                    }
                    if (nuevasRechazadas > rechazadasAnteriores) {
                        Toast.fire({ icon: 'warning', title: 'ALERTA: Un árbitro ha RECHAZADO un partido.' });
                        rechazadasAnteriores = nuevasRechazadas;
                    }
                    
                 
                    if (nuevasLicencias > licenciasAnteriores) {
                        Toast.fire({ icon: 'info', title: 'Un árbitro envió un CERTIFICADO MÉDICO' });
                    }
                    licenciasAnteriores = nuevasLicencias;  

                    if (mostrarModalDetalles.value) {
                        if (tituloModal.value.includes('Pendientes')) {
                            datosModal.value = page.props.listaPendientes || [];
                        } else {
                            datosModal.value = page.props.listaRechazadas || [];
                        }
                    }
                }
            });
        }else {
            router.reload({
                only: ['designaciones', 'stats', 'noticias'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    const nuevasDesig = page.props.designaciones || [];
                    const nuevosIds = nuevasDesig.map(d => d.id).join(',');

                    if (nuevosIds !== idsAnteriores) {
                        Toast.fire({ icon: 'info', title: ' Tu lista de designaciones fue actualizada.' });
                        idsAnteriores = nuevosIds;
                    }
                }
            });
        }

    }, 5000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

const responder = (idDesignacion, estadoRespuesta) => {
    const desig = props.designaciones.find(d => d.id === idDesignacion);

    if (estadoRespuesta === 'rechazado' && desig && desig.estado_confirmacion === 'confirmado') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Cancelar una asistencia confirmada a último momento puede afectar el torneo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar asistencia',
            cancelButtonText: 'Volver',
            customClass: { popup: 'font-["Lato",sans-serif] rounded-xl' }
        }).then((result) => {
            if (result.isConfirmed) enviarRespuesta(idDesignacion, estadoRespuesta);
        });
        return;
    }
    enviarRespuesta(idDesignacion, estadoRespuesta);
};

const enviarRespuesta = (idDesignacion, estadoRespuesta) => {
    router.patch(route('designaciones.responder', idDesignacion), { estado: estadoRespuesta }, {
        preserveScroll: true,
        onSuccess: () => {
            let mensaje = estadoRespuesta === 'confirmado' ? 'Asistencia confirmada' : 'Asistencia rechazada';
            let icono = estadoRespuesta === 'confirmado' ? 'success' : 'warning';
            Toast.fire({ icon: icono, title: mensaje });
        }
    });
};

const colorNoticia = (tipo) => {
    if (tipo === 'Urgente') return { border: 'border-red-200', iconBg: 'bg-red-50', iconBorder: 'border-red-200', iconText: 'text-red-500', tag: 'bg-red-100 text-red-800 border-red-200' };
    if (tipo === 'Citación') return { border: 'border-amber-200', iconBg: 'bg-amber-50', iconBorder: 'border-amber-200', iconText: 'text-amber-600', tag: 'bg-amber-100 text-amber-800 border-amber-200' };
    return { border: 'border-[#0D1B3E]/12', iconBg: 'bg-blue-50', iconBorder: 'border-blue-100', iconText: 'text-[#1E3370]', tag: 'bg-blue-50 text-[#1E3370] border-blue-200' };
};
</script>

<template>

    <Head title="Inicio — ADFAS" />

    <AuthenticatedLayout>

        <div class="mb-10 pb-8 border-b border-[#0D1B3E]/10">
            <p class="text-[#A87C20] text-base font-black uppercase tracking-[0.25em] mb-2">
                Bienvenido de vuelta
            </p>
            <h1 class="text-4xl md:text-5xl font-black text-[#0D1B3E] tracking-tight leading-none mb-4">
                {{ user.name }} {{ user.apellido }}
            </h1>
            <div class="flex flex-wrap items-center gap-3 mt-4">
                <span
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0D1B3E] text-[#D4A843] text-[10px] font-black uppercase tracking-[0.15em] rounded-md border border-[#D4A843]/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#D4A843]"></span>
                    {{ user.rol === 'admin' ? 'Administrador' : 'Árbitro' }}
                </span>
            </div>
        </div>

        <div v-if="props.esAdmin">

            <div class="flex items-center gap-3 mb-6">
                <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                    <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
                </div>
                <h2 class="text-sm font-black text-[#0D1B3E] uppercase ">
                    Estado General del Torneo
                </h2>
            </div>

            <div v-if="props.licenciasPendientes && props.licenciasPendientes.length > 0" class="mb-8 p-5 bg-red-50 border border-red-200 rounded-xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-3 mb-3">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    <h3 class="text-red-800 font-black uppercase tracking-widest text-sm">
                        Atención: Certificados sin leer ({{ props.licenciasPendientes.length }})
                    </h3>
                </div>
                <p class="text-red-600 text-xs font-bold mb-4">Tenés certificados médicos o licencias esperando tu aprobación. Hacé clic en el árbitro para revisarlo.</p>
                
                <div class="flex flex-wrap gap-2">
                    <Link v-for="licencia in props.licenciasPendientes" :key="licencia.id" :href="route('admin.arbitros.show', licencia.user.id)" 
                           class="px-4 py-2 bg-white border border-red-200 text-red-700 text-xs font-black uppercase tracking-widest rounded-lg hover:bg-red-100 transition-colors flex items-center gap-2 shadow-sm">
                         {{ licencia.user.apellido }}, {{ licencia.user.name }}
                         <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                     </Link>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">

                <div class="bg-white rounded border border-[#0D1B3E]/10 p-5 shadow-sm border-l-4 border-l-[#0D1B3E]">
                    <p class="text-[10px] font-black text-[#0D1B3E]/50 uppercase tracking-[0.15em] mb-3">
                        Partidos en Sistema
                    </p>
                    <p class="text-4xl font-black text-[#0D1B3E]">{{ statsAdmin.totalPartidos }}</p>
                    <div class="w-8 h-0.5 bg-[#D4A843] mt-3 rounded-full"></div>
                </div>

                <div class="bg-white rounded border border-green-100 p-5 shadow-sm border-l-4 border-l-green-500">
                    <p class="text-[10px] font-black text-green-600/70 uppercase tracking-[0.15em] mb-3">
                        Confirmados
                    </p>
                    <p class="text-4xl font-black text-green-600">{{ statsAdmin.confirmadas }}</p>
                    <div class="w-8 h-0.5 bg-green-400 mt-3 rounded-full"></div>
                </div>

                <div @click="abrirModalDetalles('pendientes')" class="bg-white rounded border border-amber-100 p-5 shadow-sm border-l-4 border-l-amber-400 cursor-pointer hover:bg-amber-50 transition-colors">
                    <p class="text-[10px] font-black text-amber-700/70 uppercase tracking-[0.15em] mb-3 flex items-center justify-between">
                        Faltan Confirmar
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </p>
                    <p class="text-4xl font-black text-amber-600">{{ statsAdmin.pendientes }}</p>
                    <div class="w-8 h-0.5 bg-amber-400 mt-3 rounded-full"></div>
                </div>

                <div @click="abrirModalDetalles('rechazadas')" class="bg-white rounded border border-red-100 p-5 shadow-sm border-l-4 border-l-red-500 cursor-pointer hover:bg-red-50 transition-colors">
                    <p class="text-[10px] font-black text-red-600/70 uppercase tracking-[0.15em] mb-3 flex items-center justify-between">
                        Ausencias
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </p>
                    <p class="text-4xl font-black text-red-600">{{ statsAdmin.rechazadas }}</p>
                    <div class="w-8 h-0.5 bg-red-400 mt-3 rounded-full"></div>
                </div>
            </div>

            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                    <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
                </div>
                <h2 class="text-sm font-black text-[#0D1B3E] uppercase ">Acciones</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-4 mb-10">
                <Link :href="route('admin.importar.index')" class="group bg-white border border-[#0D1B3E]/10 rounded p-6 hover:border-[#D4A843]/60 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-[#0D1B3E] flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
                        </div>
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-1">Subir Designaciones</h3>
                        <p class="text-base text-[#0D1B3E]/50 font-semibold leading-relaxed">Cargá partidos nuevos desde Excel</p>
                    </div>
                    <svg class="w-5 h-5 text-[#D4A843]/40 group-hover:text-[#D4A843] group-hover:translate-x-1 transition-all shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </Link>

                <Link :href="route('noticias.create')" class="group bg-white border border-[#0D1B3E]/10 rounded p-6 hover:border-[#D4A843]/60 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-[#0D1B3E] flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-1">Publicar Noticia</h3>
                        <p class="text-base text-[#0D1B3E]/50 font-semibold leading-relaxed">Avisá reuniones o adjunta archivos</p>
                    </div>
                    <svg class="w-5 h-5 text-[#D4A843]/40 group-hover:text-[#D4A843] group-hover:translate-x-1 transition-all shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </Link>

                <Link :href="route('admin.asignar.index')" class="group bg-white border border-[#0D1B3E]/10 rounded p-6 hover:border-[#D4A843]/60 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-[#0D1B3E] flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-1">Asignar Árbitros</h3>
                        <p class="text-base text-[#0D1B3E]/50 font-semibold leading-relaxed">Pizarra y cruce de partidos</p>
                    </div>
                    <svg class="w-5 h-5 text-[#D4A843]/40 group-hover:text-[#D4A843] group-hover:translate-x-1 transition-all shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </Link>
            </div>
        </div>

        <div v-else>
            <div v-if="designaciones && designaciones.length > 0" class="mb-10 space-y-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                            <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
                        </div>
                        <h2 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest">
                            Tus Próximos Partidos ({{ designaciones.length }})
                        </h2>
                    </div>
                    
                    <button @click="mostrarModalLicencia = true" class="flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Avisar Ausencia
                    </button>
                </div>

                <div v-for="desig in designaciones" :key="desig.id" class="bg-white rounded border border-[#0D1B3E]/10 overflow-hidden shadow-sm">
                    <div class="bg-[#0D1B3E] px-6 py-3 flex items-center justify-between">
                        <span class="text-[#D4A843] text-[10px] font-black uppercase tracking-[0.2em]">Partido Designado</span>
                        <span class="px-2.5 py-1 bg-[#D4A843]/15 border border-[#D4A843]/30 text-[#D4A843] text-[10px] font-black uppercase tracking-wider rounded-md">
                            {{ desig.partido.categoria }}
                        </span>
                    </div>

                    <div class="p-6 md:p-8">
                        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="flex-1 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase tracking-[0.15em] mb-1">Local</p>
                                        <p class="text-xl md:text-2xl font-black text-[#0D1B3E] leading-tight">{{ desig.partido.equipo_local }}</p>
                                    </div>
                                    <div class="shrink-0 flex flex-col items-center gap-1">
                                        <span class="text-base font-black text-[#D4A843] bg-[#0D1B3E] px-3 py-1.5 rounded-md">VS</span>
                                    </div>
                                    <div class="flex-1 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase tracking-[0.15em] mb-1">Visitante</p>
                                        <p class="text-xl md:text-2xl font-black text-[#0D1B3E] leading-tight">{{ desig.partido.equipo_visitante }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-[#F7F5F0] rounded-lg px-4 py-3 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase mb-0.5">Hora</p>
                                        <p class="text-sm font-black text-[#0D1B3E]">{{ desig.partido.hora_inicio.substring(0,5) }}</p>
                                    </div>
                                    <div class="bg-[#F7F5F0] rounded-lg px-4 py-3 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase mb-0.5">Fecha</p>
                                        <p class="text-sm font-black text-[#0D1B3E]">{{ formatearFecha(desig.partido.fecha) }}</p>
                                    </div>
                                    <div class="bg-[#F7F5F0] rounded-lg px-4 py-3 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase mb-0.5">Cancha</p>
                                        <p class="text-sm font-black text-[#0D1B3E] truncate">{{ desig.partido.cancha }}</p>
                                    </div>
                                </div>

                                <div v-if="desig.partido.designaciones" class="mt-6 pt-5 border-t border-[#0D1B3E]/10">
                                    <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase tracking-[0.15em] mb-3">Equipo Arbitral</p>
                                    <div class="flex flex-col gap-2">
                                        <div v-for="colega in desig.partido.designaciones" :key="colega.id"
                                             class="flex items-center justify-between border rounded-md px-3 py-2 transition-colors"
                                             :class="colega.user_id === user.id ? 'bg-[#D4A843]/10 border-[#D4A843]/30' : 'bg-white border-[#E5E7EB]'">
                                            <div class="flex items-center gap-3">
                                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black shrink-0"
                                                     :class="colega.user_id === user.id ? 'bg-[#D4A843] text-[#0D1B3E]' : 'bg-[#0D1B3E] text-[#D4A843]'">
                                                    {{ colega.user.name.charAt(0) }}{{ colega.user.apellido.charAt(0) }}
                                                </div>
                                                <span class="text-[13px] font-bold text-[#0D1B3E] uppercase">
                                                    {{ colega.user.apellido }}, {{ colega.user.name }}
                                                    <span v-if="colega.user_id === user.id" class="text-[#D4A843] ml-1 tracking-widest text-[10px]">(Vos)</span>
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded"
                                                  :class="colega.user_id === user.id ? 'bg-white text-[#0D1B3E]' : 'bg-gray-100 text-gray-500'">
                                                {{ colega.funcion }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-center justify-center gap-4 lg:border-l lg:border-[#0D1B3E]/8 lg:pl-10 min-w-[200px]">
                                <div v-if="desig.estado_confirmacion === 'pendiente'" class="flex gap-2 w-full">
                                    <button @click="responder(desig.id, 'confirmado')" class="flex-1 py-3 bg-green-50 text-green-800 border border-green-400 font-black text-xs uppercase rounded">Asisto</button>
                                    <button @click="responder(desig.id, 'rechazado')" class="flex-1 py-3 bg-red-50 text-red-800 border border-red-400 font-black text-xs uppercase rounded">No Asisto</button>
                                </div>
                                <div v-else-if="desig.estado_confirmacion === 'confirmado'" class="w-full text-center">
                                    <div class="bg-green-50 text-green-800 border border-green-400 py-3 font-black text-xs uppercase rounded w-full mb-2">¡Confirmado!</div>
                                    <button @click="responder(desig.id, 'rechazado')" class="text-[10px] text-red-500 font-bold hover:underline">Cancelar asistencia</button>
                                </div>
                                <div v-else class="bg-red-50 text-red-800 border border-red-400 py-3 font-black text-xs uppercase rounded w-full text-center">
                                    Rechazado
                                </div>

                                <a :href="`https://wa.me/${telefonoAdmin}?text=Hola soy el árbitro ${user.apellido}, te escribo por el partido ${desig.partido.equipo_local} vs ${desig.partido.equipo_visitante} del día ${desig.partido.fecha}.`"
                                   target="_blank" class="w-full flex items-center justify-center gap-2 mt-2 px-4 py-2.5 bg-[#25D366]/10 text-[#075E54] border border-[#25D366]/30 rounded hover:bg-[#25D366]/20 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.383 0 12.03c0 2.124.553 4.195 1.605 6.01L.031 24l6.143-1.579a12.015 12.015 0 005.857 1.522c6.645 0 12.03-5.385 12.03-12.03C24 5.383 18.675 0 12.031 0zm0 21.944c-1.802 0-3.565-.483-5.111-1.398l-.367-.217-3.801.977 1.015-3.69-.239-.38a9.988 9.988 0 01-1.525-5.305c0-5.508 4.484-9.992 9.992-9.992s9.992 4.484 9.992 9.992-4.484 9.992-9.992 9.992zm5.48-7.48c-.301-.15-1.782-.88-2.057-.98-.276-.1-.478-.15-.678.15-.2.302-.779.98-.954 1.18-.175.201-.351.226-.652.076a8.212 8.212 0 01-2.42-1.493 8.973 8.973 0 01-1.678-2.083c-.176-.301-.019-.465.132-.615.136-.135.301-.351.452-.526.15-.176.2-.302.3-.502.101-.202.051-.377-.025-.527-.075-.15-.678-1.63-.928-2.232-.243-.586-.489-.507-.678-.516-.175-.008-.376-.008-.577-.008a1.11 1.11 0 00-.803.376 3.36 3.36 0 00-1.054 2.502c0 1.956 1.405 3.847 1.605 4.122.2.276 2.81 4.288 6.804 6.015 2.593 1.123 3.633 1.206 4.887 1.018.99-.148 3.123-1.275 3.563-2.507.44-1.232.44-2.285.308-2.507-.132-.222-.478-.352-.779-.502z" /></svg>
                                    <span class="text-[11px] font-black uppercase tracking-widest">Contactar a la Liga</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="mb-10 bg-white border border-dashed border-[#0D1B3E]/15 rounded p-10 text-center shadow-sm">
                <div class="w-14 h-14 rounded-full bg-[#F7F5F0] border border-[#0D1B3E]/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-[#0D1B3E]/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <h2 class="text-lg font-black text-[#0D1B3E] mb-2 uppercase tracking-widest">Sin partidos asignados</h2>
                <p class="text-sm text-[#0D1B3E]/50 font-medium max-w-sm mx-auto leading-relaxed">
                    Por el momento no tenés ninguna designación próxima confirmada.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 mb-5">
            <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
            </div>
            <h2 class="text-sm font-black text-[#0D1B3E] uppercase ">Tablón de Noticias</h2>
        </div>

        <div class="space-y-3">
            <div v-if="!noticias || noticias.length === 0" class="bg-white border border-dashed border-[#0D1B3E]/15 rounded p-8 text-center">
                <p class="text-sm text-[#0D1B3E]/40 font-bold uppercase tracking-wide">No hay noticias recientes.</p>
            </div>

            <Link v-for="noticia in noticias" :key="noticia.id" :href="route('noticias.show', noticia.id)"
                  class="flex items-start gap-4 bg-white rounded border p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                  :class="colorNoticia(noticia.tipo).border">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 border" :class="[colorNoticia(noticia.tipo).iconBg, colorNoticia(noticia.tipo).iconBorder]">
                    <svg class="w-5 h-5" :class="colorNoticia(noticia.tipo).iconText" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="flex items-center justify-between gap-3 mb-1.5">
                        <span class="px-2.5 py-0.5 text-[10px] font-black rounded-md uppercase tracking-widest border" :class="colorNoticia(noticia.tipo).tag">{{ noticia.tipo }}</span>
                        <span class="text-base text-[#0D1B3E]/40 font-bold shrink-0">{{ new Date(noticia.created_at).toLocaleDateString('es-ES') }}</span>
                    </div>
                    <h3 class="text-sm font-black text-[#0D1B3E] truncate mb-0.5">{{ noticia.titulo }}</h3>
                    <p class="text-base text-[#0D1B3E]/50 font-medium truncate">{{ noticia.contenido }}</p>
                </div>
            </Link>

            <div class="text-right pt-1" v-if="noticias && noticias.length > 0">
                <Link :href="route('noticias.index')" class="inline-flex items-center gap-1.5 text-base font-black text-[#A87C20] hover:text-[#0D1B3E] uppercase  transition-colors">
                    Ver todas las noticias
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </Link>
            </div>
        </div>

        <div v-if="mostrarModalDetalles" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm" aria-hidden="true" @click="mostrarModalDetalles = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6 border border-gray-100">
                    
                    <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="tituloModal.includes('Pendientes') ? 'bg-amber-100' : 'bg-red-100'">
                                <svg v-if="tituloModal.includes('Pendientes')" class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg v-else class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-[#0D1B3E] uppercase tracking-widest" id="modal-title">
                                {{ tituloModal }}
                            </h3>
                        </div>
                        <button @click="mostrarModalDetalles = false" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 p-2 rounded-full transition-colors focus:outline-none">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-if="datosModal.length === 0" class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-gray-500 font-bold uppercase tracking-widest text-sm">No hay registros para mostrar.</p>
                        </div>
                        
                        <div v-else class="space-y-3">
                            <div v-for="item in datosModal" :key="item.id" class="border border-gray-100 rounded-xl p-4 bg-white shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 group">
                                
                                <div class="flex-1">
                                    <Link :href="route('admin.arbitros.show', item.user.id)" class="font-black text-[#0D1B3E] text-base mb-1 hover:text-[#D4A843] transition-colors cursor-pointer inline-flex items-center gap-2">
                                        {{ item.user.apellido }}, {{ item.user.name }}
                                        <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </Link>
                                    
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="text-[10px] font-bold text-[#0D1B3E] uppercase tracking-wider bg-blue-50 border border-blue-100 px-2 py-0.5 rounded">{{ item.funcion }}</span>
                                    </div>
                                    
                                    <div class="mt-3 bg-gray-50 rounded-lg p-3 border border-gray-100">
                                        <div class="text-sm font-black text-[#0D1B3E] flex items-center gap-2 mb-2">
                                            {{ item.partido.equipo_local }} <span class="text-gray-400 text-xs font-bold">VS</span> {{ item.partido.equipo_visitante }}
                                        </div>
                                        <div class="text-xs text-gray-500 flex flex-wrap gap-x-4 gap-y-2 font-medium">
                                            <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> {{ formatearFecha(item.partido.fecha) }}</span>
                                            <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ item.partido.hora_inicio.substring(0,5) }} hs</span>
                                            <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> {{ item.partido.cancha }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="button" class="px-6 py-2.5 text-xs font-black text-gray-600 uppercase tracking-widest bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors focus:outline-none" @click="mostrarModalDetalles = false">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="mostrarModalLicencia" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm" aria-hidden="true" @click="mostrarModalLicencia = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-gray-100">
                    
                    <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-[#0D1B3E] uppercase tracking-widest">
                                Presentar Certificado
                            </h3>
                        </div>
                        <button @click="mostrarModalLicencia = false" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 p-2 rounded-full transition-colors focus:outline-none">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form @submit.prevent="enviarLicencia" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1">Desde el día</label>
                                <input type="date" v-model="formLicencia.fecha_desde" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]" required>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1">Hasta el día</label>
                                <input type="date" v-model="formLicencia.fecha_hasta" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1">Motivo (Ej: Enfermedad, Viaje)</label>
                            <input type="text" v-model="formLicencia.motivo" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]" placeholder="Describí brevemente el motivo..." required>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-1">Adjuntar Comprobante (Foto o PDF)</label>
                            <input type="file" @input="formLicencia.certificado = $event.target.files[0]" class="w-full text-sm border border-gray-300 rounded-lg p-2 bg-gray-50 focus:ring-[#D4A843] focus:border-[#D4A843]" accept=".jpg,.jpeg,.png,.pdf" required>
                            <p class="text-[10px] text-gray-500 mt-1">Tamaño máximo: 5MB</p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="mostrarModalLicencia = false" class="px-5 py-2.5 text-xs font-black text-gray-600 uppercase tracking-widest bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="formLicencia.processing" class="px-6 py-2.5 text-xs font-black text-white uppercase tracking-widest bg-[#0D1B3E] hover:bg-[#1a2b5e] rounded-lg transition-colors disabled:opacity-50">
                                {{ formLicencia.processing ? 'Enviando...' : 'Enviar a la Liga' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { onMounted, onUnmounted } from 'vue';

const user = usePage().props.auth.user;

const props = defineProps({
    esAdmin: Boolean,
    designaciones: Array,
    telefonoAdmin: String,
    stats: Object,
    statsAdmin: Object,
    noticias: Array
});

const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true
});

let intervalId = null;
let confirmadasAnteriores = props.statsAdmin ? props.statsAdmin.confirmadas : 0;
let rechazadasAnteriores = props.statsAdmin ? props.statsAdmin.rechazadas : 0;

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const partes = fecha.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

let idsAnteriores = props.designaciones ? props.designaciones.map(d => d.id).join(',') : '';

onMounted(() => {
    intervalId = setInterval(() => {

        if (props.esAdmin) {

            router.reload({
                only: ['statsAdmin'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    const nuevasConfirmadas = page.props.statsAdmin.confirmadas;
                    const nuevasRechazadas = page.props.statsAdmin.rechazadas;

                    if (nuevasConfirmadas > confirmadasAnteriores) {
                        Toast.fire({ icon: 'success', title: '¡Un árbitro acaba de CONFIRMAR su asistencia!' });
                        confirmadasAnteriores = nuevasConfirmadas;
                    }
                    if (nuevasRechazadas > rechazadasAnteriores) {
                        Toast.fire({ icon: 'warning', title: 'ALERTA: Un árbitro ha RECHAZADO un partido.' });
                        rechazadasAnteriores = nuevasRechazadas;
                    }
                }
            });
        } else {

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
                <span class="text-[#0D1B3E]/45 text-sm font-bold tracking-wide">
                    Martes 24 de Marzo · 2026
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


                <div class="bg-white rounded border border-amber-100 p-5 shadow-sm border-l-4 border-l-amber-400">
                    <p class="text-[10px] font-black text-amber-700/70 uppercase tracking-[0.15em] mb-3">
                        Faltan Confirmar
                    </p>
                    <p class="text-4xl font-black text-amber-600">{{ statsAdmin.pendientes }}</p>
                    <div class="w-8 h-0.5 bg-amber-400 mt-3 rounded-full"></div>
                </div>


                <div class="bg-white rounded border border-red-100 p-5 shadow-sm border-l-4 border-l-red-500">
                    <p class="text-[10px] font-black text-red-600/70 uppercase tracking-[0.15em] mb-3">
                        Ausencias
                    </p>
                    <p class="text-4xl font-black text-red-600">{{ statsAdmin.rechazadas }}</p>
                    <div class="w-8 h-0.5 bg-red-400 mt-3 rounded-full"></div>
                </div>
            </div>


            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                    <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
                </div>
                <h2 class="text-sm font-black text-[#0D1B3E] uppercase ">
                    Acciones
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mb-10">


                <Link :href="route('admin.importar.index')"
                    class="group bg-white border border-[#0D1B3E]/10 rounded p-6 hover:border-[#D4A843]/60 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex items-center justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-[#0D1B3E] flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-1">
                            Subir Designaciones
                        </h3>
                        <p class="text-base text-[#0D1B3E]/50 font-semibold leading-relaxed">
                            Cargá partidos nuevos desde Excel
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-[#D4A843]/40 group-hover:text-[#D4A843] group-hover:translate-x-1 transition-all shrink-0 ml-4"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>


                <Link :href="route('noticias.create')"
                    class="group bg-white border border-[#0D1B3E]/10 rounded p-6 hover:border-[#D4A843]/60 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex items-center justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-[#0D1B3E] flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-1">
                            Publicar Noticia
                        </h3>
                        <p class="text-base text-[#0D1B3E]/50 font-semibold leading-relaxed">
                            Avisa reuniones o adjunta reglamentos
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-[#D4A843]/40 group-hover:text-[#D4A843] group-hover:translate-x-1 transition-all shrink-0 ml-4"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>


                <Link :href="route('admin.asignar.index')"
                    class="group bg-white border border-[#0D1B3E]/10 rounded p-6 hover:border-[#D4A843]/60 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex items-center justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-lg bg-[#0D1B3E] flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-1">
                            Asignar Árbitros
                        </h3>
                        <p class="text-base text-[#0D1B3E]/50 font-semibold leading-relaxed">
                            Cruza partidos con el plantel
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-[#D4A843]/40 group-hover:text-[#D4A843] group-hover:translate-x-1 transition-all shrink-0 ml-4"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
        </div>


        <div v-else>


            <div v-if="designaciones && designaciones.length > 0" class="mb-10 space-y-6">

                <div class="flex items-center gap-3 mb-5">
                    <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                        <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
                    </div>
                    <h2 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest">
                        Tus Próximos Partidos ({{ designaciones.length }})
                    </h2>
                </div>

                <div v-for="desig in designaciones" :key="desig.id"
                    class="bg-white rounded border border-[#0D1B3E]/10 overflow-hidden shadow-sm">

                    <div class="bg-[#0D1B3E] px-6 py-3 flex items-center justify-between">
                        <span class="text-[#D4A843] text-[10px] font-black uppercase tracking-[0.2em]">
                            Partido Designado
                        </span>
                        <span
                            class="px-2.5 py-1 bg-[#D4A843]/15 border border-[#D4A843]/30 text-[#D4A843] text-[10px] font-black uppercase tracking-wider rounded-md">
                            {{ desig.partido.categoria }}
                        </span>
                    </div>

                    <div class="p-6 md:p-8">
                        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">

                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="flex-1 text-center">
                                        <p
                                            class="text-[10px] font-black text-[#0D1B3E]/40 uppercase tracking-[0.15em] mb-1">
                                            Local</p>
                                        <p class="text-xl md:text-2xl font-black text-[#0D1B3E] leading-tight">{{
                                            desig.partido.equipo_local }}</p>
                                    </div>
                                    <div class="shrink-0 flex flex-col items-center gap-1">
                                        <span
                                            class="text-base font-black text-[#D4A843] bg-[#0D1B3E] px-3 py-1.5 rounded-md">VS</span>
                                    </div>
                                    <div class="flex-1 text-center">
                                        <p
                                            class="text-[10px] font-black text-[#0D1B3E]/40 uppercase tracking-[0.15em] mb-1">
                                            Visitante</p>
                                        <p class="text-xl md:text-2xl font-black text-[#0D1B3E] leading-tight">{{
                                            desig.partido.equipo_visitante }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-[#F7F5F0] rounded-lg px-4 py-3 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase mb-0.5">Hora</p>
                                        <p class="text-sm font-black text-[#0D1B3E]">{{ desig.partido.hora_inicio }}</p>
                                    </div>
                                    <div class="bg-[#F7F5F0] rounded-lg px-4 py-3 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase mb-0.5">Fecha</p>
                                        <p class="text-sm font-black text-[#0D1B3E]">{{
                                            formatearFecha(desig.partido.fecha) }}</p>
                                    </div>
                                    <div class="bg-[#F7F5F0] rounded-lg px-4 py-3 text-center">
                                        <p class="text-[10px] font-black text-[#0D1B3E]/40 uppercase mb-0.5">Cancha</p>
                                        <p class="text-sm font-black text-[#0D1B3E] truncate">{{ desig.partido.cancha }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center justify-center gap-4 lg:border-l lg:border-[#0D1B3E]/8 lg:pl-10 min-w-[200px]">

                                <div v-if="desig.estado_confirmacion === 'pendiente'" class="flex gap-2 w-full">
                                    <button @click="responder(desig.id, 'confirmado')"
                                        class="flex-1 py-3 bg-green-50 text-green-800 border border-green-400 font-black text-xs uppercase rounded">Asisto</button>
                                    <button @click="responder(desig.id, 'rechazado')"
                                        class="flex-1 py-3 bg-red-50 text-red-800 border border-red-400 font-black text-xs uppercase rounded">No
                                        Asisto</button>
                                </div>

                                <div v-else-if="desig.estado_confirmacion === 'confirmado'" class="w-full text-center">
                                    <div
                                        class="bg-green-50 text-green-800 border border-green-400 py-3 font-black text-xs uppercase rounded w-full mb-2">
                                        ¡Confirmado!</div>
                                    <button @click="responder(desig.id, 'rechazado')"
                                        class="text-[10px] text-red-500 font-bold hover:underline">Cancelar
                                        asistencia</button>
                                </div>

                                <div v-else
                                    class="bg-red-50 text-red-800 border border-red-400 py-3 font-black text-xs uppercase rounded w-full text-center">
                                    Rechazado</div>

                                <a :href="`https://wa.me/${telefonoAdmin}?text=Hola soy el árbitro ${user.apellido}, te escribo por el partido ${desig.partido.equipo_local} vs ${desig.partido.equipo_visitante} del día ${desig.partido.fecha}.`"
                                    target="_blank"
                                    class="w-full flex items-center justify-center gap-2 mt-2 px-4 py-2.5 bg-[#25D366]/10 text-[#075E54] border border-[#25D366]/30 rounded hover:bg-[#25D366]/20 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.031 0C5.385 0 0 5.383 0 12.03c0 2.124.553 4.195 1.605 6.01L.031 24l6.143-1.579a12.015 12.015 0 005.857 1.522c6.645 0 12.03-5.385 12.03-12.03C24 5.383 18.675 0 12.031 0zm0 21.944c-1.802 0-3.565-.483-5.111-1.398l-.367-.217-3.801.977 1.015-3.69-.239-.38a9.988 9.988 0 01-1.525-5.305c0-5.508 4.484-9.992 9.992-9.992s9.992 4.484 9.992 9.992-4.484 9.992-9.992 9.992zm5.48-7.48c-.301-.15-1.782-.88-2.057-.98-.276-.1-.478-.15-.678.15-.2.302-.779.98-.954 1.18-.175.201-.351.226-.652.076a8.212 8.212 0 01-2.42-1.493 8.973 8.973 0 01-1.678-2.083c-.176-.301-.019-.465.132-.615.136-.135.301-.351.452-.526.15-.176.2-.302.3-.502.101-.202.051-.377-.025-.527-.075-.15-.678-1.63-.928-2.232-.243-.586-.489-.507-.678-.516-.175-.008-.376-.008-.577-.008a1.11 1.11 0 00-.803.376 3.36 3.36 0 00-1.054 2.502c0 1.956 1.405 3.847 1.605 4.122.2.276 2.81 4.288 6.804 6.015 2.593 1.123 3.633 1.206 4.887 1.018.99-.148 3.123-1.275 3.563-2.507.44-1.232.44-2.285.308-2.507-.132-.222-.478-.352-.779-.502z" />
                                    </svg>
                                    <span class="text-[11px] font-black uppercase tracking-widest">Contactar a la
                                        Liga</span>
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div v-else
                class="mb-10 bg-white border border-dashed border-[#0D1B3E]/15 rounded p-10 text-center shadow-sm">
                <div
                    class="w-14 h-14 rounded-full bg-[#F7F5F0] border border-[#0D1B3E]/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-[#0D1B3E]/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-lg font-black text-[#0D1B3E] mb-2 uppercase tracking-widest">
                    Sin partidos asignados
                </h2>
                <p class="text-sm text-[#0D1B3E]/50 font-medium max-w-sm mx-auto leading-relaxed">
                    Por el momento no tenés ninguna designación próxima confirmada por la administración.
                </p>
            </div>
        </div>



        <div class="flex items-center gap-3 mb-5">
            <div class="flex items-center justify-center w-7 h-7 bg-[#0D1B3E] rounded-md">
                <span class="w-2.5 h-2.5 bg-[#D4A843] rounded-sm rotate-45 block"></span>
            </div>
            <h2 class="text-sm font-black text-[#0D1B3E] uppercase ">
                Tablón de Noticias
            </h2>
        </div>

        <div class="space-y-3">


            <div v-if="!noticias || noticias.length === 0"
                class="bg-white border border-dashed border-[#0D1B3E]/15 rounded p-8 text-center">
                <p class="text-sm text-[#0D1B3E]/40 font-bold uppercase tracking-wide">
                    No hay noticias recientes.
                </p>
            </div>


            <Link v-for="noticia in noticias" :key="noticia.id" :href="route('noticias.show', noticia.id)"
                class="flex items-start gap-4 bg-white rounded border p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                :class="colorNoticia(noticia.tipo).border">


                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 border"
                    :class="[colorNoticia(noticia.tipo).iconBg, colorNoticia(noticia.tipo).iconBorder]">
                    <svg class="w-5 h-5" :class="colorNoticia(noticia.tipo).iconText" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>


                <div class="flex-1 overflow-hidden">
                    <div class="flex items-center justify-between gap-3 mb-1.5">
                        <span class="px-2.5 py-0.5 text-[10px] font-black rounded-md uppercase tracking-widest border"
                            :class="colorNoticia(noticia.tipo).tag">
                            {{ noticia.tipo }}
                        </span>
                        <span class="text-base text-[#0D1B3E]/40 font-bold shrink-0">
                            {{ new Date(noticia.created_at).toLocaleDateString('es-ES') }}
                        </span>
                    </div>
                    <h3 class="text-sm font-black text-[#0D1B3E] truncate mb-0.5">{{ noticia.titulo }}</h3>
                    <p class="text-base text-[#0D1B3E]/50 font-medium truncate">{{ noticia.contenido }}</p>
                </div>


                <svg class="w-4 h-4 text-[#D4A843]/40 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </Link>


            <div class="text-right pt-1" v-if="noticias && noticias.length > 0">
                <Link :href="route('noticias.index')"
                    class="inline-flex items-center gap-1.5 text-base font-black text-[#A87C20] hover:text-[#0D1B3E] uppercase  transition-colors">
                    Ver todas las noticias
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

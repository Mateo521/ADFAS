<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

 
const props = defineProps({
    esAdmin: Boolean,
    proximaDesignacion: Object,
    stats: Object,
    statsAdmin: Object
});

const responder = (idDesignacion, estadoRespuesta) => {
    router.patch(route('designaciones.responder', idDesignacion), {
        estado: estadoRespuesta
    }, { preserveScroll: true });
};
</script>

<template>

    <Head title="Inicio - ADFAS" />

    <AuthenticatedLayout>
        <div class="mb-10">
            <p class="text-gray-500 text-lg uppercase tracking-widest font-semibold mb-1">Bienvenido de vuelta</p>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-wider uppercase">
                {{ user.name }} {{ user.apellido }}
            </h1>
            <div class="flex items-center gap-3 mt-3">
                <span
                    class="px-4 py-1.5 bg-blue-100 border border-blue-300 text-blue-800 text-sm font-bold uppercase rounded-md shadow-sm">
                    {{ user.rol === 'admin' ? 'Administrador' : 'Árbitro' }}
                </span>
                <span class="text-gray-600 font-medium text-lg">Martes 24 de Marzo · 2026</span>
            </div>
        </div>

        <div v-if="props.esAdmin">

            <div class="flex items-center gap-3 mb-5">
                <div class="w-1.5 h-8 bg-purple-600 rounded-full"></div>
                <h2 class="text-2xl font-bold tracking-widest text-gray-800 uppercase">Estado General del Torneo</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-gray-500 text-sm font-bold uppercase tracking-widest mb-1">Partidos en Sistema</p>
                    <p class="text-5xl font-black text-gray-800">{{ statsAdmin.totalPartidos }}</p>
                </div>
                <div class="bg-white border-2 border-green-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-green-600 text-sm font-bold uppercase tracking-widest mb-1">Árbitros Confirmados</p>
                    <p class="text-5xl font-black text-green-600">{{ statsAdmin.confirmadas }}</p>
                </div>
                <div class="bg-white border-2 border-yellow-300 rounded-2xl p-6 shadow-sm">
                    <p class="text-yellow-700 text-sm font-bold uppercase tracking-widest mb-1">Faltan Confirmar</p>
                    <p class="text-5xl font-black text-yellow-600">{{ statsAdmin.pendientes }}</p>
                </div>
                <div class="bg-white border-2 border-red-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-red-600 text-sm font-bold uppercase tracking-widest mb-1">Ausencias (Rechazos)</p>
                    <p class="text-5xl font-black text-red-600">{{ statsAdmin.rechazadas }}</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mb-10">
                <Link :href="route('admin.importar.index')"
                    class="bg-purple-50 border-2 border-purple-200 rounded-2xl p-8 hover:bg-purple-100 hover:border-purple-400 transition-all group flex items-center justify-between cursor-pointer">
                <div>
                    <h3 class="text-2xl font-black text-purple-900 mb-2 uppercase tracking-wider">Subir Designaciones
                    </h3>
                    <p class="text-lg text-purple-700 font-medium">Carga partidos nuevos desde el Excel.</p>
                </div>
                <svg class="w-12 h-12 text-purple-400 group-hover:text-purple-600 group-hover:scale-110 transition-all"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                </Link>

              <Link :href="route('noticias.create')" class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-8 hover:bg-gray-100 transition-all group flex items-center justify-between cursor-pointer">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 mb-2 uppercase tracking-wider">Publicar Noticia</h3>
                        <p class="text-lg text-gray-600 font-medium">Avisa a los árbitros sobre reuniones o adjunta reglamentos.</p>
                    </div>
                    <svg class="w-12 h-12 text-gray-400 group-hover:text-gray-600 group-hover:scale-110 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>

        </div>
        <div v-else>
            <div v-if="proximaDesignacion" class="mb-12">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-1.5 h-8 bg-blue-600 rounded-full"></div>
                    <h2 class="text-2xl font-bold tracking-widest text-gray-800 uppercase">Tu Próxima Designación</h2>
                </div>

                <div class="overflow-hidden rounded-2xl border-2 border-blue-100 bg-white shadow-lg">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col md:flex-row justify-between gap-8">

                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-5">
                                    <span
                                        class="px-3 py-1 bg-red-100 border border-red-300 text-red-700 text-sm font-black rounded uppercase tracking-wider shadow-sm">
                                        {{ proximaDesignacion.partido.categoria }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-6 mb-6">
                                    <div class="text-center w-1/3">
                                        <p class="text-3xl font-black text-gray-900">{{
                                            proximaDesignacion.partido.equipo_local }}</p>
                                        <p class="text-gray-500 text-sm font-bold uppercase mt-1">Local</p>
                                    </div>
                                    <div class="flex flex-col items-center justify-center w-1/3">
                                        <span
                                            class="text-blue-600 text-2xl font-black bg-blue-50 px-4 py-2 rounded-full">VS</span>
                                    </div>
                                    <div class="text-center w-1/3">
                                        <p class="text-3xl font-black text-gray-900">{{
                                            proximaDesignacion.partido.equipo_visitante }}</p>
                                        <p class="text-gray-500 text-sm font-bold uppercase mt-1">Visitante</p>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-wrap items-center gap-8 text-lg bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <div class="flex items-center gap-2 text-gray-700 font-bold">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ proximaDesignacion.partido.hora_inicio }} hs
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-700 font-bold">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ proximaDesignacion.partido.fecha }}
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-700 font-bold">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        {{ proximaDesignacion.partido.cancha }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex flex-col items-center justify-center gap-4 md:border-l-2 md:border-gray-100 md:pl-10 w-full md:w-auto">
                                <p class="text-lg text-gray-600 font-bold uppercase tracking-widest mb-2 text-center">
                                    ¿Confirmás asistencia?</p>

                                <div v-if="proximaDesignacion.estado_confirmacion === 'pendiente'"
                                    class="flex w-full md:flex-col gap-4">
                                    <button @click="responder(proximaDesignacion.id, 'confirmado')"
                                        class="flex-1 flex items-center justify-center gap-3 px-6 py-5 bg-green-50 hover:bg-green-100 border-2 border-green-500 rounded-xl transition-all shadow-sm cursor-pointer">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-green-700 text-xl font-black uppercase">Asisto</span>
                                    </button>

                                    <button @click="responder(proximaDesignacion.id, 'rechazado')"
                                        class="flex-1 flex items-center justify-center gap-3 px-6 py-5 bg-red-50 hover:bg-red-100 border-2 border-red-500 rounded-xl transition-all shadow-sm cursor-pointer">
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span class="text-red-700 text-xl font-black uppercase">No asisto</span>
                                    </button>
                                </div>

                                <div v-else-if="proximaDesignacion.estado_confirmacion === 'confirmado'"
                                    class="flex items-center gap-3 bg-green-100 px-6 py-4 rounded-xl border-2 border-green-500 w-full justify-center">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-green-800 text-xl font-black uppercase">¡Confirmado!</span>
                                </div>

                                <div v-else
                                    class="flex items-center gap-3 bg-red-100 px-6 py-4 rounded-xl border-2 border-red-500 w-full justify-center">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="text-red-800 text-xl font-black uppercase">Rechazado</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div v-else
                class="mb-12 bg-white border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center shadow-sm">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h2 class="text-2xl font-black text-gray-700 mb-2 uppercase tracking-widest">Sin partidos asignados</h2>
                <p class="text-lg text-gray-500 font-medium">Por el momento no tienes ninguna designación próxima
                    confirmada por la administración.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 mb-10">

                <div class="lg:col-span-1 space-y-4">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-1.5 h-8 bg-blue-600 rounded-full"></div>
                        <h2 class="text-2xl font-bold tracking-widest text-gray-800 uppercase">Resumen</h2>
                    </div>

                    <div
                        class="bg-white border-2 border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-bold uppercase tracking-widest mb-1">Partidos 2026</p>
                            <p class="text-4xl font-black text-blue-600">24</p>
                        </div>
                    </div>
                    <div
                        class="bg-white border-2 border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-bold uppercase tracking-widest mb-1">Confirmados</p>
                            <p class="text-4xl font-black text-green-600">22</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-1.5 h-8 bg-blue-600 rounded-full"></div>
                        <h2 class="text-2xl font-bold tracking-widest text-gray-800 uppercase">Tablón de Noticias</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-white border-2 border-yellow-300 rounded-xl p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-14 h-14 bg-yellow-100 border-2 border-yellow-400 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-black rounded-md uppercase border border-yellow-300">Citación</span>
                                        <span class="text-gray-500 font-bold">16/03/2026</span>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-900 mb-2">CITACIÓN A TRABAJOS PRÁCTICOS</h3>
                                    <p class="text-lg text-gray-600 font-medium leading-relaxed">Predio L.A. Messi –
                                        Ezeiza. Presentarse a las 8:15 con ropa deportiva oficial...</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-2 border-gray-200 rounded-xl p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-14 h-14 bg-blue-50 border-2 border-blue-200 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-black rounded-md uppercase border border-blue-300">Info</span>
                                        <span class="text-gray-500 font-bold">10/03/2026</span>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-900 mb-2">ACTUALIZACIÓN REGLAMENTO 2026</h3>
                                    <p class="text-lg text-gray-600 font-medium leading-relaxed">Se informa a todos los
                                        árbitros que se han actualizado las disposiciones del reglamento...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
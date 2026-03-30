<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    partidos: Array,
    arbitros: Array
});

const form = useForm({
    asignaciones: props.partidos.map(partido => ({
        partido_id: partido.id,
        principal_id: '',
        asistente_id: '',
        asistente_2_id: '',
        asistente_3_id: ''
    }))
});

const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true
});

const CLAVE_BORRADOR = 'adfas_borrador_asignaciones';
let temporizador = null;
const tieneBorrador = ref(false);

onMounted(() => {
    const borradorGuardado = localStorage.getItem(CLAVE_BORRADOR);

    if (borradorGuardado && props.partidos.length > 0) {
        const asignacionesRecuperadas = JSON.parse(borradorGuardado);

        if (asignacionesRecuperadas.length === form.asignaciones.length) {
            form.asignaciones = asignacionesRecuperadas;
            tieneBorrador.value = true;

            Toast.fire({
                icon: 'info',
                title: 'Progreso recuperado',
                text: 'Se restauraron tus asignaciones previas.',
                timer: 4000
            });
        } else {
            localStorage.removeItem(CLAVE_BORRADOR);
        }
    }
});

watch(() => form.asignaciones, (nuevosDatos) => {
    clearTimeout(temporizador);

    temporizador = setTimeout(() => {
        localStorage.setItem(CLAVE_BORRADOR, JSON.stringify(nuevosDatos));
        tieneBorrador.value = true;

        Toast.fire({
            icon: 'success',
            title: 'Cambios guardados',
            timer: 1500
        });
    }, 1000);

}, { deep: true });


// ═════════════════════════════════════════════════════════
//  1. LÓGICA DE DÍAS Y CANCHAS (NUEVO SELECTOR)
// ═════════════════════════════════════════════════════════
const fechasDisponibles = computed(() => {
    if (!props.partidos) return [];
    const fechas = [...new Set(props.partidos.map(p => p.fecha))];
    return fechas.sort();
});

const fechaSeleccionada = ref(fechasDisponibles.value.length > 0 ? fechasDisponibles.value[0] : null);

watch(() => fechasDisponibles.value, (nuevasFechas) => {
    if (nuevasFechas.length > 0 && !fechaSeleccionada.value) {
        fechaSeleccionada.value = nuevasFechas[0];
    }
});

const formatearFechaTab = (fechaString) => {
    if (!fechaString) return '';
    const [year, month, day] = fechaString.split('-');
    const date = new Date(year, month - 1, day);
    const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    return `${dias[date.getDay()]} ${day}/${month}`;
};

const partidosAgrupados = computed(() => {
    if (!props.partidos) return {};

    return props.partidos.reduce((grupos, partido, index) => {
        if (partido.fecha !== fechaSeleccionada.value) return grupos;
        const cancha = partido.cancha || 'CANCHA SIN DEFINIR';
        if (!grupos[cancha]) grupos[cancha] = [];

        grupos[cancha].push({
            datos: partido,
            indiceOriginal: index
        });
        return grupos;
    }, {});
});

// NUEVO: Calculamos las canchas de este día y seleccionamos la primera
const canchasDisponibles = computed(() => Object.keys(partidosAgrupados.value));
const canchaSeleccionada = ref(null);

watch(canchasDisponibles, (nuevasCanchas) => {
    if (nuevasCanchas.length > 0) {
        if (!nuevasCanchas.includes(canchaSeleccionada.value)) {
            canchaSeleccionada.value = nuevasCanchas[0];
        }
    } else {
        canchaSeleccionada.value = null;
    }
}, { immediate: true });


// ═════════════════════════════════════════════════════════
//  2. FUNCIONES DE AYUDA Y MATEMÁTICA
// ═════════════════════════════════════════════════════════
const limpiarBorrador = () => {
    Swal.fire({
        title: '¿Limpiar pizarra?',
        text: "Se borrarán todas las designaciones que hiciste hasta ahora y volverán a estar vacías.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem(CLAVE_BORRADOR);
            tieneBorrador.value = false;

            form.asignaciones = props.partidos.map(partido => ({
                partido_id: partido.id,
                principal_id: '',
                asistente_id: '',
                asistente_2_id: '',
                asistente_3_id: ''
            }));
        }
    });
};

const aMinutos = (horaStr) => {
    if (!horaStr) return 0;
    const [horas, minutos] = horaStr.split(':').map(Number);
    return (horas * 60) + (minutos || 0);
};

const obtenerDuracion = (disciplina) => {
    const texto = (disciplina || '').toString().toUpperCase();
    if (texto.includes('FEMENINO') || texto.includes('FUTSAL') || texto.includes('INFERIORES') || texto === 'F' || texto === 'I') {
        return 90;
    }
    return 120;
};

const obtenerLicenciaActiva = (arbitro, fechaPartido) => {
    if (!arbitro.licencias || arbitro.licencias.length === 0) return null;
    return arbitro.licencias.find(lic => fechaPartido >= lic.fecha_desde && fechaPartido <= lic.fecha_hasta);
};

const chequearHistorial = (arbitroId, equipoLocal, equipoVisitante) => {
    if (!arbitroId) return null;
    const arbitro = props.arbitros.find(a => a.id === arbitroId);
    if (!arbitro || !arbitro.designaciones) return null;

    const local = (equipoLocal || '').toLowerCase().trim();
    const visitante = (equipoVisitante || '').toLowerCase().trim();

    const partidoRepetido = arbitro.designaciones.find(d => {
        if (!d.partido) return false;
        const pLocal = (d.partido.equipo_local || '').toLowerCase().trim();
        const pVisitante = (d.partido.equipo_visitante || '').toLowerCase().trim();

        return pLocal === local || pLocal === visitante || pVisitante === local || pVisitante === visitante;
    });

    if (partidoRepetido) {
        return `Dirigió a este equipo el ${formatearFechaTab(partidoRepetido.partido.fecha)}`;
    }
    return null;
};

const formatearNombreArbitro = (arbitro, partido) => {
    const licencia = obtenerLicenciaActiva(arbitro, partido.fecha);
    const nombreBase = `${arbitro.apellido.toUpperCase()}, ${arbitro.name}`;

    if (licencia) {
        const [y, m, d] = licencia.fecha_hasta.split('-');
        return `⛔ [LICENCIA] ${nombreBase} (Hasta ${d}/${m})`;
    }

    const repetido = chequearHistorial(arbitro.id, partido.equipo_local, partido.equipo_visitante);
    if (repetido) {
        return `⚠️ [REPETIDO] ${nombreBase}`;
    }
    return nombreBase;
};

const arbitrosDisponibles = (indexPartidoActual) => {
    const partidoActual = props.partidos[indexPartidoActual];

    const inicioActual = aMinutos(partidoActual.hora_inicio);
    const finActual = inicioActual + obtenerDuracion(partidoActual.disciplina);

    const arbitrosOcupados = form.asignaciones.map((asignacion, i) => {
        const otroPartido = props.partidos[i];

        if (i !== indexPartidoActual && otroPartido.fecha === partidoActual.fecha) {
            const inicioOtro = aMinutos(otroPartido.hora_inicio);
            const finOtro = inicioOtro + obtenerDuracion(otroPartido.disciplina);

            if (inicioActual < finOtro && finActual > inicioOtro) {
                return [
                    asignacion.principal_id,
                    asignacion.asistente_id,
                    asignacion.asistente_2_id,
                    asignacion.asistente_3_id
                ];
            }
        }
        return [];
    }).flat().filter(id => id && id !== '');

    return props.arbitros.filter(arbitro => !arbitrosOcupados.includes(arbitro.id));
};

const submit = () => {
    form.post(route('admin.asignar.store'), {
        preserveScroll: true,
        onSuccess: () => {
            localStorage.removeItem(CLAVE_BORRADOR);
            tieneBorrador.value = false;
            Toast.fire({ icon: 'success', title: '¡Designaciones publicadas y enviadas a los árbitros!' });
        },
    });
};
</script>

<template>

    <Head title="Pizarra de Asignaciones - ADFAS" />

    <AuthenticatedLayout>

        <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1
                    class="font-['Playfair_Display',serif]- text-[28px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-2 uppercase tracking-wide">
                    Pizarra de Asignaciones
                </h1>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                    <span class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span>
                </div>
                <p class="text-base text-[#6B7280] font-black uppercase ">Módulo de Administración</p>
            </div>

            <div v-if="partidos.length > 0"
                class="flex items-center gap-3 bg-white border border-[#D4A843]/30 px-5 py-2.5 rounded-lg shadow-sm">
                <div class="w-2 h-2 rounded-full bg-[#D4A843] animate-pulse"></div>
                <span class="text-[#0D1B3E] font-black text-[12px] uppercase tracking-widest">{{ partidos.length }} Partidos pendientes</span>
            </div>
        </div>

        <div v-if="partidos.length === 0"
            class="bg-white border-2 border-dashed border-[#E5E7EB] rounded p-12 text-center shadow-sm max-w-3xl mx-auto mt-10">
            <div
                class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-green-100">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2
                class="font-['Playfair_Display',serif]- text-[24px] font-extrabold text-[#0D1B3E] mb-2 uppercase tracking-wide">
                ¡Todo al día!</h2>
            <p class="text-[14px] text-[#6B7280] font-medium">No hay partidos pendientes de asignación en este momento. Subí una nueva planilla excel para comenzar.</p>
            <Link :href="route('admin.importar.index')"
                class="inline-block mt-6 px-6 py-2.5 bg-gradient-to-r from-[#0D1B3E] to-[#162554] text-white text-base font-black uppercase tracking-widest rounded-lg shadow-md hover:shadow-lg transition-all">
                Ir a Importar Excel
            </Link>
        </div>

        <form v-else @submit.prevent="submit" class="flex flex-col gap-6">

            <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm flex flex-col md:flex-row md:items-end justify-between gap-6 z-10 relative">
                
                <div class="flex-1 overflow-hidden">
                    <label class="block text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] mb-2 pl-1">
                        1. Seleccionar Día
                    </label>
                    <div v-if="fechasDisponibles.length > 0" class="flex overflow-x-auto gap-2 hide-scrollbar flex-wrap justify-between pb-1">
                        <button v-for="fecha in fechasDisponibles" :key="fecha" @click.prevent="fechaSeleccionada = fecha"
                            class="whitespace-nowrap py-2.5 px-5 text-sm font-black uppercase tracking-widest transition-all focus:outline-none rounded-lg border-2"
                            :class="fechaSeleccionada === fecha
                                ? 'bg-[#0D1B3E] text-[#D4A843] border-[#0D1B3E] shadow-md'
                                : 'bg-white text-gray-500 border-transparent hover:border-gray-200 hover:bg-gray-50'">
                            {{ formatearFechaTab(fecha) }}
                        </button>
                    </div>
                </div>

                <div v-if="canchasDisponibles.length > 0" class="min-w-[280px]">
                    <label class="block text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] mb-2 pl-1">
                        2. Seleccionar Cancha
                    </label>
                    <div class="relative">
                        <select v-model="canchaSeleccionada"
                            class="block w-full text-[13px] font-bold border-[#E5E7EB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded-lg py-2.5 pl-10 pr-4 bg-gray-50 hover:bg-white transition-colors cursor-pointer text-[#0D1B3E] shadow-sm appearance-none">
                            <option v-for="cancha in canchasDisponibles" :key="cancha" :value="cancha">
                                {{ cancha }} ({{ partidosAgrupados[cancha].length }} partidos)
                            </option>
                        </select>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="canchaSeleccionada && partidosAgrupados[canchaSeleccionada]"
                class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden flex flex-col">

                <div class="bg-[#0D1B3E] px-6 py-4 border-b border-[#D4A843]/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#D4A843] animate-pulse"></span>
                        <h3 class="text-white font-black uppercase tracking-widest text-[15px]">
                            {{ canchaSeleccionada }}
                        </h3>
                    </div>
                    <span class="text-[#0D1B3E] text-[11px] font-black uppercase tracking-wider bg-[#D4A843] px-3 py-1 rounded-md shadow-sm">
                        {{ partidosAgrupados[canchaSeleccionada].length }} Encuentros
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[55vh] custom-scrollbar relative">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-[#F9FAFB] sticky top-0 z-30 shadow-sm">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">Hora</th>
                                <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB]">Cat.</th>
                                <th scope="col" class="px-5 py-3.5 border-b border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-right bg-[#F9FAFB]">Local</th>
                                <th scope="col" class="px-2 py-3.5 border-b border-[#E5E7EB] bg-[#F9FAFB]"></th>
                                <th scope="col" class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">Visitante</th>
                                <th scope="col" class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-blue-50 min-w-[180px]">Principal</th>
                                <th scope="col" class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-blue-50 min-w-[180px]">Asistente 1</th>
                                <th scope="col" class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-blue-50 min-w-[180px]">Asistente 2</th>
                                <th scope="col" class="px-4 py-3.5 border-b border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-blue-50 min-w-[180px]">Asistente 3 / Crono</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in partidosAgrupados[canchaSeleccionada]" :key="item.datos.id"
                                class="border-b border-[#E5E7EB] hover:bg-gray-50 transition-colors group">

                                <td class="px-5 py-3 border-r border-[#E5E7EB] bg-white group-hover:bg-gray-50">
                                    <span class="text-[#D4A843] font-black text-[13px]">{{ item.datos.hora_inicio.substring(0, 5) }}</span>
                                </td>

                                <td class="px-5 py-3 border-r border-[#E5E7EB] text-center">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] font-black uppercase tracking-wider border border-gray-200" :title="item.datos.disciplina">
                                        {{ item.datos.categoria }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-right font-black text-[13px] text-[#0D1B3E] uppercase">{{ item.datos.equipo_local }}</td>
                                <td class="px-2 py-3 text-center text-[10px] font-bold text-gray-400">VS</td>
                                <td class="px-5 py-3 font-black text-[13px] text-[#0D1B3E] uppercase border-r border-[#E5E7EB]">{{ item.datos.equipo_visitante }}</td>

                                <td class="px-2 py-2 border-r border-[#E5E7EB] bg-blue-50/10 group-hover:bg-blue-50/30 transition-colors align-top">
                                    <select v-model="form.asignaciones[item.indiceOriginal].principal_id"
                                        class="block w-full text-[11px] font-semibold border-[#D1D5DB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded py-1 px-2 bg-white shadow-sm cursor-pointer text-[#111827]">
                                        <option value="" disabled class="text-gray-400">-- Seleccionar --</option>
                                        <option v-for="arbitro in arbitrosDisponibles(item.indiceOriginal)"
                                            :key="arbitro.id" :value="arbitro.id"
                                            :disabled="obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null"
                                            :class="{
                                                'text-red-600 font-black bg-red-50': obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null,
                                                'text-amber-600 font-bold': chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante) && !obtenerLicenciaActiva(arbitro, item.datos.fecha),
                                                'text-gray-900 font-medium': !obtenerLicenciaActiva(arbitro, item.datos.fecha) && !chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante)
                                            }">
                                            {{ formatearNombreArbitro(arbitro, item.datos) }}
                                        </option>
                                    </select>
                                    <p v-if="form.asignaciones[item.indiceOriginal].principal_id && chequearHistorial(form.asignaciones[item.indiceOriginal].principal_id, item.datos.equipo_local, item.datos.equipo_visitante)"
                                        class="text-[9px] text-red-600 font-black mt-1 flex items-start gap-1 leading-tight">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        {{ chequearHistorial(form.asignaciones[item.indiceOriginal].principal_id, item.datos.equipo_local, item.datos.equipo_visitante) }}
                                    </p>
                                </td>

                                <td class="px-2 py-2 border-r border-[#E5E7EB] bg-blue-50/10 group-hover:bg-blue-50/30 transition-colors align-top">
                                    <select v-model="form.asignaciones[item.indiceOriginal].asistente_id"
                                        class="block w-full text-[11px] font-semibold border-[#D1D5DB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded py-1 px-2 bg-white shadow-sm cursor-pointer text-[#111827]">
                                        <option value="" class="text-gray-400">-- Vacío --</option>
                                        <option v-for="arbitro in arbitrosDisponibles(item.indiceOriginal)"
                                            :key="arbitro.id" :value="arbitro.id"
                                            :disabled="obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null"
                                            :class="{
                                                'text-red-600 font-black bg-red-50': obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null,
                                                'text-amber-600 font-bold': chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante) && !obtenerLicenciaActiva(arbitro, item.datos.fecha),
                                                'text-gray-900 font-medium': !obtenerLicenciaActiva(arbitro, item.datos.fecha) && !chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante)
                                            }">
                                            {{ formatearNombreArbitro(arbitro, item.datos) }}
                                        </option>
                                    </select>
                                    <p v-if="form.asignaciones[item.indiceOriginal].asistente_id && chequearHistorial(form.asignaciones[item.indiceOriginal].asistente_id, item.datos.equipo_local, item.datos.equipo_visitante)"
                                        class="text-[9px] text-red-600 font-black mt-1 flex items-start gap-1 leading-tight">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        {{ chequearHistorial(form.asignaciones[item.indiceOriginal].asistente_id, item.datos.equipo_local, item.datos.equipo_visitante) }}
                                    </p>
                                </td>

                                <td class="px-2 py-2 border-r border-[#E5E7EB] bg-blue-50/10 group-hover:bg-blue-50/30 transition-colors align-top">
                                    <select v-model="form.asignaciones[item.indiceOriginal].asistente_2_id"
                                        class="block w-full text-[11px] font-semibold border-[#D1D5DB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded py-1 px-2 bg-white shadow-sm cursor-pointer text-[#111827]">
                                        <option value="" class="text-gray-400">-- Vacío --</option>
                                        <option v-for="arbitro in arbitrosDisponibles(item.indiceOriginal)"
                                            :key="arbitro.id" :value="arbitro.id"
                                            :disabled="obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null"
                                            :class="{
                                                'text-red-600 font-black bg-red-50': obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null,
                                                'text-amber-600 font-bold': chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante) && !obtenerLicenciaActiva(arbitro, item.datos.fecha),
                                                'text-gray-900 font-medium': !obtenerLicenciaActiva(arbitro, item.datos.fecha) && !chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante)
                                            }">
                                            {{ formatearNombreArbitro(arbitro, item.datos) }}
                                        </option>
                                    </select>
                                    <p v-if="form.asignaciones[item.indiceOriginal].asistente_2_id && chequearHistorial(form.asignaciones[item.indiceOriginal].asistente_2_id, item.datos.equipo_local, item.datos.equipo_visitante)"
                                        class="text-[9px] text-red-600 font-black mt-1 flex items-start gap-1 leading-tight">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        {{ chequearHistorial(form.asignaciones[item.indiceOriginal].asistente_2_id, item.datos.equipo_local, item.datos.equipo_visitante) }}
                                    </p>
                                </td>

                                <td class="px-2 py-2 bg-blue-50/10 group-hover:bg-blue-50/30 transition-colors align-top">
                                    <select v-model="form.asignaciones[item.indiceOriginal].asistente_3_id"
                                        class="block w-full text-[11px] font-semibold border-[#D1D5DB] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 rounded py-1 px-2 bg-white shadow-sm cursor-pointer text-[#111827]">
                                        <option value="" class="text-gray-400">-- Vacío --</option>
                                        <option v-for="arbitro in arbitrosDisponibles(item.indiceOriginal)"
                                            :key="arbitro.id" :value="arbitro.id"
                                            :disabled="obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null"
                                            :class="{
                                                'text-red-600 font-black bg-red-50': obtenerLicenciaActiva(arbitro, item.datos.fecha) !== null,
                                                'text-amber-600 font-bold': chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante) && !obtenerLicenciaActiva(arbitro, item.datos.fecha),
                                                'text-gray-900 font-medium': !obtenerLicenciaActiva(arbitro, item.datos.fecha) && !chequearHistorial(arbitro.id, item.datos.equipo_local, item.datos.equipo_visitante)
                                            }">
                                            {{ formatearNombreArbitro(arbitro, item.datos) }}
                                        </option>
                                    </select>
                                    <p v-if="form.asignaciones[item.indiceOriginal].asistente_3_id && chequearHistorial(form.asignaciones[item.indiceOriginal].asistente_3_id, item.datos.equipo_local, item.datos.equipo_visitante)"
                                        class="text-[9px] text-red-600 font-black mt-1 flex items-start gap-1 leading-tight">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        {{ chequearHistorial(form.asignaciones[item.indiceOriginal].asistente_3_id, item.datos.equipo_local, item.datos.equipo_visitante) }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-5 border border-t-0 border-[#E5E7EB] rounded-b-xl shadow-sm flex items-center justify-between z-30 shrink-0">
                <p class="text-[12px] text-[#6B7280] font-medium hidden md:block">
                    Al confirmar, se guardarán los datos de todas las pestañas y se enviarán las notificaciones.
                </p>
                <button type="submit" :disabled="form.processing"
                    class="group relative overflow-hidden px-8 py-3.5 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[12px] font-black uppercase tracking-[2px] rounded-lg transition-all duration-300 shadow-[0_2px_8px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] hover:shadow-[0_8px_20px_rgba(168,124,32,0.35)] disabled:opacity-65 disabled:cursor-not-allowed disabled:transform-none w-full md:w-auto">

                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <span v-if="form.processing" class="relative z-10 flex items-center justify-center gap-2">
                        <svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" stroke-dasharray="20" stroke-dashoffset="5" />
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

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
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
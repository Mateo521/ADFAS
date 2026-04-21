<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    partidos: Object,
    arbitros: Array,
    filtros: Object,
    jornadas: Array
});

const filtroFecha = ref(props.filtros.fecha || '');
const filtroCategoria = ref(props.filtros.categoria || '');
const filtroEquipo = ref(props.filtros.equipo || '');
const filtroArbitro = ref(props.filtros.arbitro || '');
const filtroDisciplina = ref(props.filtros.disciplina || '');
const filtroJornada = ref(props.filtros.jornada || '');

const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const partes = fecha.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

const esPartidoPasado = (fechaString) => {
    if (!fechaString) return false;

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const [year, month, day] = fechaString.split('-');
    const fechaPartido = new Date(year, month - 1, day);
    fechaPartido.setHours(0, 0, 0, 0);

    return fechaPartido < hoy;
};

const modalAbierto = ref(false);
const partidoSeleccionado = ref(null);

const formReasignar = useForm({
    principal_id: '',
    asistente_id: '',
    asistente_2_id: '',
    asistente_3_id: ''
});

const abrirModalReasignar = (partido) => {
    partidoSeleccionado.value = partido;
    const principal = getPrincipal(partido);
    const asistente = getAsistente(partido);
    const asistente2 = getAsistente2(partido);
    const asistente3 = getAsistente3(partido);

    formReasignar.principal_id = principal ? principal.user_id : '';
    formReasignar.asistente_id = asistente ? asistente.user_id : '';
    formReasignar.asistente_2_id = asistente2 ? asistente2.user_id : '';
    formReasignar.asistente_3_id = asistente3 ? asistente3.user_id : '';

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

const getStatusClasses = (estado, fechaPartido) => {
    if (estado === 'confirmado') return 'bg-green-50 text-green-700 border-green-200';
    if (estado === 'rechazado') return 'bg-red-50 text-red-700 border-red-200';

    if (esPartidoPasado(fechaPartido)) return 'bg-gray-100 text-gray-500 border-gray-300';

    return 'bg-[#D4A843]/10 text-[#A87C20] border-[#D4A843]/30';
};

const getEstadoTexto = (estado, fechaPartido) => {
    if (estado === 'confirmado') return 'CONFIRMADO';
    if (estado === 'rechazado') return 'RECHAZADO';

    if (esPartidoPasado(fechaPartido)) return 'NO CONFIRMÓ';

    return 'PENDIENTE';
};

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
                                const horaPartido = partido.hora_inicio.substring(0, 5);

                                if (estadoNuevo === 'confirmado' || estadoNuevo === 'rechazado') {
                                    const esConfirmado = estadoNuevo === 'confirmado';

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: esConfirmado ? 'success' : 'error',
                                        title: esConfirmado ? 'Asistencia confirmada' : ' Asistencia rechazada',
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
                                        customClass: { popup: 'border border-gray-200 shadow-xl' }
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
    router.get(route('admin.historial.index'), {
        fecha: filtroFecha.value,
        categoria: filtroCategoria.value,
        equipo: filtroEquipo.value,
        arbitro: filtroArbitro.value,
        disciplina: filtroDisciplina.value,
        jornada: filtroJornada.value
    }, { preserveState: true, replace: true });
};

const limpiarFiltros = () => {
    filtroFecha.value = '';
    filtroCategoria.value = '';
    filtroEquipo.value = '';
    filtroArbitro.value = '';
    filtroDisciplina.value = '';
    filtroJornada.value = '';
    aplicarFiltros();
};

const getPrincipal = (partido) => partido.designaciones.find(d => d.funcion === 'ARBITRO PRINCIPAL');
const getAsistente = (partido) => partido.designaciones.find(d => d.funcion === 'ASISTENTE 1');
const getAsistente2 = (partido) => partido.designaciones.find(d => d.funcion === 'ASISTENTE 2');
const getAsistente3 = (partido) => partido.designaciones.find(d => d.funcion === 'ASISTENTE 3 / CRONO');

</script>

<template>

    <Head title="Gestión y Monitor - ADFAS" />

    <AuthenticatedLayout>

        <div class="font-['Lato',sans-serif]">

            <div class="mb-8 flex items-center justify-between">





                <div>
                    <h1 class="- text-[28px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-2 uppercase tracking-wide">
                        Gestión y Monitoreo
                    </h1>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                        <span class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span>
                    </div>
                    <p class="text-[12px] text-[#6B7280] font-medium tracking-wide">Supervisa el estado de confirmación
                        de los árbitros en tiempo real y revisa el historial.</p>
                </div>



                <div
                    class="hidden md:flex items-center gap-2 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full shadow-sm">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-[10px] font-black text-green-700 uppercase tracking-widest">En Vivo</span>
                </div>
            </div>

            <div v-if="jornadas && jornadas.length > 0" class="mb-6 max-w-xs">
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1 ml-1">Seleccionar
                    Jornada</label>
                <select v-model="filtroJornada" @change="aplicarFiltros()"
                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843] font-bold text-[#0D1B3E] shadow-sm bg-white cursor-pointer">
                    <option value="">TODAS LAS JORNADAS</option>
                    <option v-for="jor in jornadas" :key="jor" :value="jor">
                        {{ jor }}
                    </option>
                </select>
            </div>

            <div
                class="bg-white p-5 rounded border border-[#E5E7EB] shadow-sm mb-6 flex flex-wrap gap-4 items-end relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-[#D4A843]"></div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Buscar por
                        Fecha</label>
                    <input type="date" v-model="filtroFecha" @change="aplicarFiltros"
                        class="w-[160px] px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase tracking-wide">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Disciplina</label>
                    <select v-model="filtroDisciplina" @change="aplicarFiltros"
                        class="w-[140px] px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase cursor-pointer">
                        <option value="">TODAS</option>
                        <option value="FUTSAL">FUTSAL</option>
                        <option value="FUTBOL 11">FÚTBOL 11</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Categoría</label>
                    <input type="text" v-model="filtroCategoria" @keyup.enter="aplicarFiltros"
                        placeholder="Ej: PRIMERA A"
                        class="w-[140px] px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase">
                </div>

                <div class="flex flex-col gap-1.5 flex-1 min-w-[200px]">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Buscar Equipo</label>
                    <input type="text" v-model="filtroEquipo" @keyup.enter="aplicarFiltros"
                        placeholder="Buscar por club..."
                        class="w-full px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase">
                </div>

                <div class="flex flex-col gap-1.5 flex-1 min-w-[150px]">
                    <label class="text-[10px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Buscar Árbitro</label>
                    <input type="text" v-model="filtroArbitro" @keyup.enter="aplicarFiltros"
                        placeholder="Ej: Noguera..."
                        class="w-full px-3 py-2 border border-[#D1D5DB] rounded-lg text-[13px] font-medium text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all uppercase">
                </div>

                <div class="flex gap-2">
                    <button @click="limpiarFiltros"
                        class="px-5 py-2 rounded-lg text-[12px] font-black uppercase tracking-widest text-[#6B7280] bg-white border border-[#D1D5DB] hover:bg-gray-50 transition-colors">Limpiar</button>
                    <button @click="aplicarFiltros"
                        class="px-6 py-2 rounded-lg text-[12px] font-black uppercase tracking-widest text-white bg-gradient-to-br from-[#0D1B3E] to-[#1E3370] border border-[#D4A843]/30 hover:shadow-md transition-all hover:-translate-y-[1px]">Buscar</button>
                </div>
            </div>

            <div
                class="bg-white border border-[#E5E7EB] rounded shadow-sm overflow-auto max-h-[calc(100vh-320px)] custom-scrollbar">
                <table class="w-full text-left whitespace-nowrap relative">

                    <thead class="sticky top-0 z-20 shadow-sm">
                        <tr>
                            <th
                                class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] bg-[#F9FAFB]">
                                Fecha / Hora</th>
                            <th
                                class="px-5 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB]">
                                Partido</th>

                            <th
                                class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-[#EFF6FF] min-w-[200px]">
                                Principal</th>
                            <th
                                class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-[#EFF6FF] min-w-[200px]">
                                Asistente 1</th>
                            <th
                                class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-[#EFF6FF] min-w-[200px]">
                                Asistente 2</th>
                            <th
                                class="px-4 py-3.5 border-b border-r border-[#E5E7EB] text-[10px] font-black text-[#0D1B3E] uppercase tracking-[0.15em] bg-[#EFF6FF] min-w-[200px]">
                                Asis 3 / Crono</th>

                            <th
                                class="px-5 py-3.5 border-b border-[#E5E7EB] text-[10px] font-black text-[#6B7280] uppercase tracking-[0.15em] text-center bg-[#F9FAFB] sticky right-0">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="partidos.data.length === 0">
                            <td colspan="7" class="px-5 py-12 text-center"><span
                                    class="text-[14px] text-gray-500 font-bold">No se encontraron partidos.</span></td>
                        </tr>

                        <tr v-for="partido in partidos.data" :key="partido.id"
                            class="border-b border-[#E5E7EB] transition-colors group"
                            :class="esPartidoPasado(partido.fecha) ? 'bg-gray-50/80' : 'hover:bg-gray-50'">

                            <td class="px-5 py-3 border-r border-[#E5E7EB]">
                                <span class="font-bold text-[13px] block"
                                    :class="esPartidoPasado(partido.fecha) ? 'text-gray-500' : 'text-[#374151]'">{{
                                        formatearFecha(partido.fecha) }}</span>
                                <span class="font-black text-[13px] block"
                                    :class="esPartidoPasado(partido.fecha) ? 'text-gray-400' : 'text-[#D4A843]'">{{
                                        partido.hora_inicio.substring(0, 5) }} hs</span>
                                <span class="text-[9px] text-gray-400 font-bold uppercase mt-0.5 block">{{
                                    partido.disciplina || 'FUTBOL 11' }}</span>
                            </td>

                            <td class="px-5 py-3 border-r border-[#E5E7EB] text-center min-w-[180px]">
                                <div class="font-black text-[12px] uppercase"
                                    :class="esPartidoPasado(partido.fecha) ? 'text-gray-500' : 'text-[#0D1B3E]'">{{
                                        partido.equipo_local }}</div>
                                <div class="text-[9px] text-gray-400 font-bold my-0.5">VS</div>
                                <div class="font-black text-[12px] uppercase"
                                    :class="esPartidoPasado(partido.fecha) ? 'text-gray-500' : 'text-[#0D1B3E]'">{{
                                        partido.equipo_visitante }}</div>
                                <div class="flex items-center justify-center gap-1 mt-1.5">
                                    <span
                                        class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-black uppercase tracking-wider border border-gray-200">{{
                                            partido.categoria }}</span>
                                </div>
                                <div
                                    class="text-[11px] text-[#6B7280] mt-1 font-medium flex items-center justify-center gap-1">
                                    {{ partido.cancha }}</div>
                            </td>

                            <td class="px-4 py-3 border-r border-[#E5E7EB] relative group/celda cursor-pointer"
                                :class="esPartidoPasado(partido.fecha) ? '' : 'bg-blue-50/10'">
                                <div v-if="getPrincipal(partido)" class="flex items-center gap-3"
                                    :class="esPartidoPasado(partido.fecha) ? 'opacity-70' : ''">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white border border-gray-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm">
                                        <img v-if="getPrincipal(partido).user.foto_perfil"
                                            :src="`/storage/${getPrincipal(partido).user.foto_perfil}`"
                                            class="w-full h-full object-cover grayscale"
                                            :class="!esPartidoPasado(partido.fecha) && 'grayscale-0'">
                                        <span v-else class="text-[10px] font-black text-gray-500">{{
                                            getPrincipal(partido).user.name.charAt(0) }}{{
                                                getPrincipal(partido).user.apellido.charAt(0) }}</span>
                                    </div>
                                    <div class="flex flex-col items-start gap-1">
                                        <p class="font-bold text-[12px] text-[#111827] uppercase">{{
                                            getPrincipal(partido).user.apellido }}, {{
                                                getPrincipal(partido).user.name.charAt(0) }}.</p>

                                        <span
                                            class="text-[8px] px-2 py-0.5 rounded-full border uppercase font-black tracking-widest transition-colors duration-500"
                                            :class="getStatusClasses(getPrincipal(partido).estado_confirmacion, partido.fecha)">
                                            {{ getEstadoTexto(getPrincipal(partido).estado_confirmacion, partido.fecha)
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <span v-else
                                    class="text-[11px] text-gray-400 font-medium italic flex items-center justify-center h-full">-</span>

                                <Link v-if="getPrincipal(partido)"
                                    :href="route('admin.arbitros.show', getPrincipal(partido).user.id)"
                                    class="absolute inset-1 bg-white/95 backdrop-blur-[2px] flex items-center justify-center opacity-0 group-hover/celda:opacity-100 transition-all duration-300 border border-[#D4A843]/50 rounded shadow-sm z-10 scale-95 group-hover/celda:scale-100">

                                    <span
                                        class="text-[11px] font-black text-[#0D1B3E] uppercase tracking-widest flex items-center gap-1.5 hover:text-[#D4A843] transition-colors">
                                        Ver Ficha
                                    </span>

                                </Link>
                            </td>

                            <td class="px-4 py-3 border-r border-[#E5E7EB] relative group/celda cursor-pointer"
                                :class="esPartidoPasado(partido.fecha) ? '' : 'bg-blue-50/10'">
                                <div v-if="getAsistente(partido)" class="flex items-center gap-3"
                                    :class="esPartidoPasado(partido.fecha) ? 'opacity-70' : ''">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white border border-gray-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm">
                                        <img v-if="getAsistente(partido).user.foto_perfil"
                                            :src="`/storage/${getAsistente(partido).user.foto_perfil}`"
                                            class="w-full h-full object-cover grayscale"
                                            :class="!esPartidoPasado(partido.fecha) && 'grayscale-0'">
                                        <span v-else class="text-[10px] font-black text-gray-500">{{
                                            getAsistente(partido).user.name.charAt(0) }}{{
                                                getAsistente(partido).user.apellido.charAt(0) }}</span>
                                    </div>
                                    <div class="flex flex-col items-start gap-1">
                                        <p class="font-bold text-[12px] text-[#111827] uppercase">{{
                                            getAsistente(partido).user.apellido }}, {{
                                                getAsistente(partido).user.name.charAt(0) }}.</p>
                                        <span
                                            class="text-[8px] px-2 py-0.5 rounded-full border uppercase font-black tracking-widest transition-colors duration-500"
                                            :class="getStatusClasses(getAsistente(partido).estado_confirmacion, partido.fecha)">
                                            {{ getEstadoTexto(getAsistente(partido).estado_confirmacion, partido.fecha)
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <span v-else
                                    class="text-[11px] text-gray-400 font-medium italic flex items-center justify-center h-full">-</span>

                                <Link v-if="getAsistente(partido)"
                                    :href="route('admin.arbitros.show', getAsistente(partido).user.id)"
                                    class="absolute inset-1 bg-white/95 backdrop-blur-[2px] flex items-center justify-center opacity-0 group-hover/celda:opacity-100 transition-all duration-300 border border-[#D4A843]/50 rounded shadow-sm z-10 scale-95 group-hover/celda:scale-100">
                                    <span
                                        class="text-[11px] font-black text-[#0D1B3E] uppercase tracking-widest flex items-center gap-1.5 hover:text-[#D4A843] transition-colors">Ver
                                        Ficha</span>
                                </Link>
                            </td>

                            <td class="px-4 py-3 border-r border-[#E5E7EB] relative group/celda cursor-pointer"
                                :class="esPartidoPasado(partido.fecha) ? '' : 'bg-blue-50/10'">
                                <div v-if="getAsistente2(partido)" class="flex items-center gap-3"
                                    :class="esPartidoPasado(partido.fecha) ? 'opacity-70' : ''">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white border border-gray-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm">
                                        <img v-if="getAsistente2(partido).user.foto_perfil"
                                            :src="`/storage/${getAsistente2(partido).user.foto_perfil}`"
                                            class="w-full h-full object-cover grayscale"
                                            :class="!esPartidoPasado(partido.fecha) && 'grayscale-0'">
                                        <span v-else class="text-[10px] font-black text-gray-500">{{
                                            getAsistente2(partido).user.name.charAt(0) }}{{
                                                getAsistente2(partido).user.apellido.charAt(0) }}</span>
                                    </div>
                                    <div class="flex flex-col items-start gap-1">
                                        <p class="font-bold text-[12px] text-[#111827] uppercase">{{
                                            getAsistente2(partido).user.apellido }}, {{
                                                getAsistente2(partido).user.name.charAt(0) }}.</p>
                                        <span
                                            class="text-[8px] px-2 py-0.5 rounded-full border uppercase font-black tracking-widest transition-colors duration-500"
                                            :class="getStatusClasses(getAsistente2(partido).estado_confirmacion, partido.fecha)">
                                            {{ getEstadoTexto(getAsistente2(partido).estado_confirmacion, partido.fecha)
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <span v-else
                                    class="text-[11px] text-gray-400 font-medium italic flex items-center justify-center h-full">-</span>

                                <Link v-if="getAsistente2(partido)"
                                    :href="route('admin.arbitros.show', getAsistente2(partido).user.id)"
                                    class="absolute inset-1 bg-white/95 backdrop-blur-[2px] flex items-center justify-center opacity-0 group-hover/celda:opacity-100 transition-all duration-300 border border-[#D4A843]/50 rounded shadow-sm z-10 scale-95 group-hover/celda:scale-100">
                                    <span
                                        class="text-[11px] font-black text-[#0D1B3E] uppercase tracking-widest flex items-center gap-1.5 hover:text-[#D4A843] transition-colors">Ver
                                        Ficha</span>
                                </Link>
                            </td>

                            <td class="px-4 py-3 border-r border-[#E5E7EB] relative group/celda cursor-pointer"
                                :class="esPartidoPasado(partido.fecha) ? '' : 'bg-blue-50/10'">
                                <div v-if="getAsistente3(partido)" class="flex items-center gap-3"
                                    :class="esPartidoPasado(partido.fecha) ? 'opacity-70' : ''">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white border border-gray-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm">
                                        <img v-if="getAsistente3(partido).user.foto_perfil"
                                            :src="`/storage/${getAsistente3(partido).user.foto_perfil}`"
                                            class="w-full h-full object-cover grayscale"
                                            :class="!esPartidoPasado(partido.fecha) && 'grayscale-0'">
                                        <span v-else class="text-[10px] font-black text-gray-500">{{
                                            getAsistente3(partido).user.name.charAt(0) }}{{
                                                getAsistente3(partido).user.apellido.charAt(0) }}</span>
                                    </div>
                                    <div class="flex flex-col items-start gap-1">
                                        <p class="font-bold text-[12px] text-[#111827] uppercase">{{
                                            getAsistente3(partido).user.apellido }}, {{
                                                getAsistente3(partido).user.name.charAt(0) }}.</p>
                                        <span
                                            class="text-[8px] px-2 py-0.5 rounded-full border uppercase font-black tracking-widest transition-colors duration-500"
                                            :class="getStatusClasses(getAsistente3(partido).estado_confirmacion, partido.fecha)">
                                            {{ getEstadoTexto(getAsistente3(partido).estado_confirmacion, partido.fecha)
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <span v-else
                                    class="text-[11px] text-gray-400 font-medium italic flex items-center justify-center h-full">-</span>

                                <Link v-if="getAsistente3(partido)"
                                    :href="route('admin.arbitros.show', getAsistente3(partido).user.id)"
                                    class="absolute inset-1 bg-white/95 backdrop-blur-[2px] flex items-center justify-center opacity-0 group-hover/celda:opacity-100 transition-all duration-300 border border-[#D4A843]/50 rounded shadow-sm z-10 scale-95 group-hover/celda:scale-100">
                                    <span
                                        class="text-[11px] font-black text-[#0D1B3E] uppercase tracking-widest flex items-center gap-1.5 hover:text-[#D4A843] transition-colors">Ver
                                        Ficha</span>
                                </Link>
                            </td>

                            <td class="px-5 py-3 text-center sticky right-0 border-l border-[#E5E7EB] shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.05)]"
                                :class="esPartidoPasado(partido.fecha) ? 'bg-gray-50/80' : 'bg-white group-hover:bg-gray-50'">

                                <span v-if="esPartidoPasado(partido.fecha)"
                                    class="inline-block bg-[#111827] text-white font-black text-[10px] uppercase tracking-widest px-3 py-1.5 rounded shadow-sm opacity-90">
                                    Terminado
                                </span>

                                <button v-else @click="abrirModalReasignar(partido)"
                                    class="text-[#0D1B3E] font-bold text-[11px] uppercase tracking-wider bg-[#0D1B3E]/5 px-3 py-2 rounded border border-[#0D1B3E]/20 hover:bg-[#D4A843]/10 hover:text-[#A87C20] hover:border-[#D4A843]/30 transition-colors w-full">
                                    Reasignar
                                </button>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center text-[12px] text-[#6B7280] font-medium gap-4"
                v-if="partidos.links && partidos.links.length > 3">
                <div>Mostrando <span class="font-bold text-[#0D1B3E]">{{ partidos.from || 0 }}</span> a <span
                        class="font-bold text-[#0D1B3E]">{{ partidos.to || 0 }}</span> de <span
                        class="font-bold text-[#0D1B3E]">{{ partidos.total }}</span> registros</div>
                <div class="flex gap-1 shadow-sm rounded-lg overflow-hidden border border-[#E5E7EB]">
                    <Link v-for="(link, key) in partidos.links" :key="key" :href="link.url || '#'"
                        class="px-3.5 py-2 border-r border-[#E5E7EB] last:border-0 font-bold transition-colors"
                        :class="{ 'bg-[#0D1B3E] text-white': link.active, 'bg-white text-[#374151] hover:bg-gray-50 hover:text-[#D4A843]': !link.active && link.url, 'bg-gray-50 text-gray-400 cursor-not-allowed': !link.url }"
                        v-html="link.label"></Link>
                </div>
            </div>

        </div>

        <div v-if="modalAbierto"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 font-['Lato',sans-serif] overflow-y-auto">
            <div
                class="bg-white  shadow-2xl w-full max-w-2xl overflow-hidden animate-formAppear border border-[#D4A843]/30 my-8">

                <div class="bg-[#0D1B3E] p-6 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#D4A843]/20 rounded-full blur-2xl"></div>
                    <h2 class="relative z-10 - text-[22px] font-extrabold text-white uppercase tracking-wide">
                        Modificar Designación
                    </h2>
                    <p class="relative z-10 text-[12px] font-medium text-[#D4A843] mt-1 uppercase tracking-widest">
                        {{ partidoSeleccionado.equipo_local }} VS {{ partidoSeleccionado.equipo_visitante }}
                    </p>
                </div>

                <div class="p-6 md:p-8">
                    <form @submit.prevent="confirmarReasignacion" class="space-y-5">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-2">
                                <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Nuevo
                                    Principal</label>
                                <select v-model="formReasignar.principal_id" required
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-[#D1D5DB] rounded-lg text-[13px] font-semibold text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                    <option value="" disabled class="text-gray-400">-- Seleccione --</option>
                                    <option v-for="arbitro in arbitrosDisponibles" :key="arbitro.id"
                                        :value="arbitro.id">
                                        {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Nuevo
                                    Asistente 1</label>
                                <select v-model="formReasignar.asistente_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-[#D1D5DB] rounded-lg text-[13px] font-semibold text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                    <option value="">-- Vacío --</option>
                                    <option v-for="arbitro in arbitrosDisponibles" :key="arbitro.id"
                                        :value="arbitro.id">
                                        {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Nuevo
                                    Asistente 2</label>
                                <select v-model="formReasignar.asistente_2_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-[#D1D5DB] rounded-lg text-[13px] font-semibold text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                    <option value="">-- Vacío --</option>
                                    <option v-for="arbitro in arbitrosDisponibles" :key="arbitro.id"
                                        :value="arbitro.id">
                                        {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Nuevo
                                    Asistente 3 / Crono</label>
                                <select v-model="formReasignar.asistente_3_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-[#D1D5DB] rounded-lg text-[13px] font-semibold text-[#111827] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all cursor-pointer">
                                    <option value="">-- Vacío --</option>
                                    <option v-for="arbitro in arbitrosDisponibles" :key="arbitro.id"
                                        :value="arbitro.id">
                                        {{ arbitro.apellido.toUpperCase() }}, {{ arbitro.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex gap-3 mt-4">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-[12px] font-medium text-blue-800 leading-tight">Al guardar, los árbitros
                                anteriores van a ser desvinculados y los nuevos van a recibir una notificación en su
                                Dashboard.</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-5 border-t border-[#E5E7EB]">
                            <button type="button" @click="cerrarModal"
                                class="px-6 py-2.5 rounded-lg text-[11px] font-black uppercase tracking-widest text-[#6B7280] bg-white border border-[#D1D5DB] hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="formReasignar.processing"
                                class="px-6 py-2.5 rounded-lg text-[11px] font-black uppercase tracking-widest text-[#0D1B3E] bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] hover:shadow-md transition-all disabled:opacity-50">
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
.animate-formAppear {
    animation: formAppear 0.3s ease-out forwards;
}

@keyframes formAppear {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
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
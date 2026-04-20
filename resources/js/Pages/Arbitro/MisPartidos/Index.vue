<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    partidos: Array,
    resumen: Object,
    filtros: Object
});

const mesSeleccionado = ref(props.filtros.mes);
const anioSeleccionado = ref(props.filtros.anio);

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

// Generar años dinámicamente hasta 2025
const aniosDisponibles = computed(() => {
    const anioActual = new Date().getFullYear();
    const anios = [];
    for (let i = anioActual; i >= 2025; i--) anios.push(i);
    if (!anios.includes(Number(anioSeleccionado.value))) anios.push(Number(anioSeleccionado.value));
    return anios.sort((a, b) => b - a);
});

const formatMoneda = (monto) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 0 }).format(monto);
};

const formatDate = (fechaStr) => {
    if (!fechaStr) return '';
    const partes = fechaStr.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

const cambiarPeriodo = () => {
    router.get(route('mis-partidos.index'), {
        mes: mesSeleccionado.value,
        anio: anioSeleccionado.value
    }, { preserveState: true });
};
</script>

<template>

    <Head title="Mis Partidos - ADFAS" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto pb-12 font-['Lato',sans-serif]">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h1 class="text-[28px] font-extrabold text-[#0D1B3E] uppercase tracking-tight leading-none mb-2">Mi
                        Actividad</h1>
                    <div class="flex items-center gap-2.5 mb-2"><span
                            class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span><span
                            class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span></div>
                    <p class="text-sm text-[#6B7280] font-bold uppercase tracking-widest">Historial y liquidaciones</p>
                </div>

                <div class="flex gap-2 bg-white p-2 shadow-sm border border-gray-200  shrink-0">
                    <select v-model="mesSeleccionado" @change="cambiarPeriodo"
                        class="text-sm border-none focus:ring-0 font-bold text-[#0D1B3E] cursor-pointer bg-transparent py-1">
                        <option v-for="(n, i) in meses" :key="i" :value="i + 1">{{ n }}</option>
                    </select>
                    <select v-model="anioSeleccionado" @change="cambiarPeriodo"
                        class="text-sm border-none focus:ring-0 font-bold text-[#0D1B3E] cursor-pointer bg-transparent py-1">
                        <option v-for="anio in aniosDisponibles" :key="anio" :value="anio">{{ anio }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-4  border border-gray-200 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Mes</p>
                    <p class="text-xl font-black text-gray-700">{{ resumen.cantidad }} Part.</p>
                </div>
                <div class="bg-white p-4  border border-gray-200 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Confirmados</p>
                    <p class="text-xl font-black text-green-600">{{ resumen.confirmados }} Part.</p>
                </div>
                <div class="bg-white p-4  border border-[#D4A843]/30 bg-[#D4A843]/5 shadow-sm">
                    <p class="text-[10px] font-black text-[#D4A843] uppercase tracking-widest mb-1">Recaudado</p>
                    <p class="text-xl font-black text-[#0D1B3E]">{{ formatMoneda(resumen.total_ganado) }}</p>
                </div>
                <div class="bg-[#0D1B3E] p-4  border border-[#0D1B3E] shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-[#D4A843]/20 rounded-full blur-xl -mr-4 -mt-4">
                    </div>
                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-1 relative z-10">10% A
                        Pagar</p>
                    <p class="text-xl font-black text-[#D4A843] relative z-10">{{ formatMoneda(resumen.a_pagar) }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div v-if="partidos.length === 0"
                    class="text-center py-12 bg-white  border border-gray-200 border-dashed">
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No tenés designaciones en este
                        mes.</p>
                </div>

                <div v-for="partido in partidos" :key="partido.id"
                    class="bg-white  border border-gray-200 shadow-sm overflow-hidden flex flex-col md:flex-row relative hover:border-[#D4A843]/50 transition-colors">

                    <div class="absolute left-0 top-0 bottom-0 w-1" :class="{
                        'bg-green-500': partido.estado_confirmacion === 'confirmado',
                        'bg-amber-400': partido.estado_confirmacion === 'pendiente',
                        'bg-red-500': partido.estado_confirmacion === 'rechazado' || partido.estado_confirmacion === 'cancelado'
                    }"></div>

                    <div class="p-5 flex-1 pl-6">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-black text-[#0D1B3E] bg-gray-100 px-2 py-1 rounded">{{
                                formatDate(partido.fecha) }}</span>
                            <span class="text-xs font-black text-gray-500">{{ partido.hora }} hs</span>
                            <span class="text-[10px] font-black text-[#D4A843] uppercase ml-auto tracking-widest">{{
                                partido.disciplina }}</span>
                        </div>

                        <h3 class="text-base font-black text-[#0D1B3E] uppercase">{{ partido.partido }}</h3>

                        <div class="flex items-center gap-4 mt-3 text-xs font-bold text-gray-500">
                            <div class="flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>{{ partido.cancha }}</div>
                            <div class="flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>{{ partido.categoria }}</div>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 p-5 md:w-56 border-t md:border-t-0 md:border-l border-gray-100 flex flex-col justify-center items-end shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded border mb-2"
                            :class="{
                                'bg-green-100 text-green-700 border-green-200': partido.estado_confirmacion === 'confirmado',
                                'bg-amber-100 text-amber-700 border-amber-200': partido.estado_confirmacion === 'pendiente',
                                'bg-red-100 text-red-700 border-red-200': partido.estado_confirmacion === 'rechazado' || partido.estado_confirmacion === 'cancelado'
                            }">{{ partido.estado_confirmacion }}</span>

                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-0.5">{{ partido.funcion
                            }}</p>
                        <p v-if="partido.estado_confirmacion === 'confirmado'"
                            class="text-xl font-black text-green-600">{{ formatMoneda(partido.monto) }}</p>
                        <p v-else class="text-sm font-bold text-gray-400">-</p>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
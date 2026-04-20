<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    arbitros: Array,
    filtros: Object
});

const mesSeleccionado = ref(props.filtros.mes);
const anioSeleccionado = ref(props.filtros.anio);

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const aniosDisponibles = computed(() => {
    const anioActual = new Date().getFullYear();
    const anioInicio = 2025;
    const anios = [];


    for (let i = anioActual; i >= anioInicio; i--) {
        anios.push(i);
    }


    if (!anios.includes(Number(anioSeleccionado.value))) {
        anios.push(Number(anioSeleccionado.value));
    }

    return anios.sort((a, b) => b - a);
});

const modalTicketVisible = ref(false);
const arbitroSeleccionado = ref(null);

const formatMoneda = (monto) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 0 }).format(monto);
};

const formatDate = (fechaStr) => {
    if (!fechaStr) return '';
    const partes = fechaStr.split('-');
    return `${partes[2]}/${partes[1]}`;
};

const cambiarPeriodo = () => {
    router.get(route('admin.pagos.index'), {
        mes: mesSeleccionado.value,
        anio: anioSeleccionado.value
    }, { preserveState: true });
};


const refrescarDatos = () => {
    router.reload({ only: ['arbitros'], preserveScroll: true, preserveState: true });
};


let intervaloFondo;
onMounted(() => {
    intervaloFondo = setInterval(refrescarDatos, 30000);
});


onUnmounted(() => {
    clearInterval(intervaloFondo);
});

const abrirTicket = (arbitro) => {
    arbitroSeleccionado.value = arbitro;
    modalTicketVisible.value = true;
};

const cerrarTicket = () => {
    modalTicketVisible.value = false;
    arbitroSeleccionado.value = null;
};

const registrarPago = (user) => {
    Swal.fire({
        title: '¿Confirmar cobro?',
        text: `Vas a registrar el cobro de ${formatMoneda(user.deuda_calculada)} a ${user.apellido}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#25D366',
        confirmButtonText: 'Sí, pago recibido',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.pagos.registrar'), {
                user_id: user.id,
                mes: mesSeleccionado.value,
                anio: anioSeleccionado.value
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cobro registrado correctamente', showConfirmButton: false, timer: 3000 });
                }
            });
        }
    });
};
</script>

<template>

    <Head title="Liquidaciones - ADFAS" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto pb-12 font-['Lato',sans-serif]">

            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-[28px] font-extrabold text-[#0D1B3E] uppercase tracking-tight leading-none mb-2">
                        Liquidaciones Mensuales</h1>
                    <div class="flex items-center gap-2.5 mb-2"><span
                            class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span><span
                            class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span></div>
                    <p class="text-sm text-[#6B7280] font-bold uppercase tracking-widest">Cálculo del 10% por partidos
                        dirigidos</p>
                </div>

                <div class="flex gap-2 bg-white p-2 shadow-sm border border-gray-100 rounded-lg items-center">
                    <select v-model="mesSeleccionado" @change="cambiarPeriodo"
                        class="text-sm border-none focus:ring-0 font-bold text-[#0D1B3E] cursor-pointer bg-transparent py-1">
                        <option v-for="(n, i) in meses" :key="i" :value="i + 1">{{ n }}</option>
                    </select>

                    <select v-model="anioSeleccionado" @change="cambiarPeriodo"
                        class="text-sm border-none focus:ring-0 font-bold text-[#0D1B3E] cursor-pointer bg-transparent py-1">
                        <option v-for="anio in aniosDisponibles" :key="anio" :value="anio">
                            {{ anio }}
                        </option>
                    </select>

                    <div class="w-px h-5 bg-gray-200 mx-1"></div>
                    <button @click="refrescarDatos" title="Sincronizar pagos"
                        class="p-1 text-gray-400 hover:text-[#D4A843] transition-colors rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>



            </div>

            <div class="bg-white shadow-sm border border-[#E5E7EB] overflow-hidden rounded-sm">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Árbitro
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">
                                Partidos</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                                Total Ganado</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-[#0D1B3E] uppercase tracking-widest text-right bg-[#D4A843]/10">
                                10% A Pagar</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">
                                Estado</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in arbitros" :key="user.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-[#0D1B3E] uppercase">{{ user.apellido }}, {{ user.name
                                    }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">{{
                                    user.ticket_preview ? user.ticket_preview.length : 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-black text-gray-600">{{ formatMoneda(user.total_ganado) }}</p>
                            </td>
                            <td class="px-6 py-4 text-right bg-[#D4A843]/5">
                                <p class="text-sm font-black text-[#0D1B3E]">{{ formatMoneda(user.deuda_calculada) }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="user.esta_al_dia"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-700 text-[10px] font-black uppercase rounded-full border border-green-200">
                                    PAGADO
                                </span>
                                <span v-else-if="user.ticket_preview && user.ticket_preview.length > 0"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 text-[10px] font-black uppercase rounded-full border border-amber-200">
                                    DEUDA
                                </span>
                                <span v-else class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sin
                                    Deuda</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button v-if="user.ticket_preview && user.ticket_preview.length > 0"
                                        @click="abrirTicket(user)"
                                        class="p-2 text-gray-500 hover:text-[#0D1B3E] bg-gray-100 hover:bg-gray-200 rounded transition-colors"
                                        title="Ver Desglose">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>

                                    <button
                                        v-if="!user.esta_al_dia && user.ticket_preview && user.ticket_preview.length > 0"
                                        @click="registrarPago(user)"
                                        class="px-4 py-2 bg-[#0D1B3E] text-[#D4A843] text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-[#162554] transition-all shadow-sm">
                                        Cobrar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="modalTicketVisible"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div
                class="bg-white rounded-sm shadow-2xl max-w-lg w-full overflow-hidden flex flex-col font-['Lato',sans-serif]">

                <div class="bg-[#0D1B3E] p-5 text-center relative overflow-hidden shrink-0">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-[#D4A843]/20 rounded-full blur-xl"></div>
                    <h3 class="text-white font-black uppercase tracking-widest text-lg relative z-10">Liquidación de
                        Árbitro</h3>
                    <p class="text-[#D4A843] text-xs font-bold uppercase mt-1 relative z-10">{{
                        arbitroSeleccionado.apellido }}, {{ arbitroSeleccionado.name }}</p>
                </div>

                <div class="p-6 overflow-y-auto max-h-[60vh] bg-gray-50">
                    <div v-for="(item, index) in arbitroSeleccionado.ticket_preview" :key="index"
                        class="bg-white p-4 rounded border border-gray-200 mb-3 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-[10px] font-black text-[#D4A843] uppercase">{{ formatDate(item.fecha) }}
                                    • {{ item.categoria }}</p>
                                <p class="text-sm font-black text-[#0D1B3E] uppercase mt-0.5">{{ item.partido }}</p>
                            </div>
                            <p class="text-sm font-black text-green-600">{{ formatMoneda(item.monto) }}</p>
                        </div>
                        <span
                            class="inline-block bg-gray-100 text-gray-500 text-[9px] font-black uppercase px-2 py-0.5 rounded">{{
                                item.funcion }}</span>
                    </div>

                    <div v-if="arbitroSeleccionado.ticket_preview.length === 0" class="text-center py-6">
                        <p class="text-gray-400 font-bold uppercase text-xs">No hay partidos registrados en este mes.
                        </p>
                    </div>
                </div>

                <div class="bg-white border-t border-gray-200 p-6 shrink-0">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Suma Total
                            Ganada:</span>
                        <span class="text-base font-black text-gray-600">{{
                            formatMoneda(arbitroSeleccionado.total_ganado) }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center bg-[#D4A843]/10 p-3 rounded-lg border border-[#D4A843]/30">
                        <span class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest">10% A Abonar:</span>
                        <span class="text-xl font-black text-[#0D1B3E]">{{
                            formatMoneda(arbitroSeleccionado.deuda_calculada) }}</span>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button @click="cerrarTicket"
                            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-lg transition-colors">Cerrar</button>
                    </div>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
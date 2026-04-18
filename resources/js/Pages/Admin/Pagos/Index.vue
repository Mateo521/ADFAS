<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    arbitros: Array,
    filtros: Object,
    ajustes: Object
});

const mesSeleccionado = ref(props.filtros.mes);
const anioSeleccionado = ref(props.filtros.anio);

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const cambiarPeriodo = () => {
    router.get(route('admin.pagos.index'), {
        mes: mesSeleccionado.value,
        anio: anioSeleccionado.value
    }, { preserveState: true });
};

const registrarPago = (user) => {
    Swal.fire({
        title: '¿Confirmar pago manual?',
        text: `Vas a marcar como AL DÍA a ${user.apellido} para el mes de ${meses[mesSeleccionado.value - 1]}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#25D366',
        confirmButtonText: 'Sí, recibió el dinero',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.pagos.registrar'), {
                user_id: user.id,
                mes: mesSeleccionado.value,
                anio: anioSeleccionado.value,


                monto: props.ajustes.cuota_monto

            }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Pago registrado',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        }
    });
};


const formAjustes = useForm({
    cuota_monto: props.ajustes.cuota_monto,
    cuota_dia_vencimiento: props.ajustes.cuota_dia_vencimiento
});

const actualizarConfiguracion = () => {
    formAjustes.post(route('admin.pagos.ajustes'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Configuración actualizada', showConfirmButton: false, timer: 2000 });
        }
    });
};

</script>

<template>

    <Head title="Control de Cuotas - ADFAS" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto pb-12 font-['Lato',sans-serif]">

            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-[28px] font-extrabold text-[#0D1B3E] uppercase tracking-tight leading-none mb-2">
                        Control de Cuotas</h1>
                    <div class="flex items-center gap-2.5 mb-2"><span
                            class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span><span
                            class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span></div>
                    <p class="text-sm text-[#6B7280] font-bold uppercase tracking-widest">Estado de los árbitros
                    </p>
                </div>

                <div class="flex gap-2 bg-white p-2  shadow-sm border border-gray-100">
                    <select v-model="mesSeleccionado" @change="cambiarPeriodo"
                        class="text-sm border-none focus:ring-0 font-bold text-[#0D1B3E] cursor-pointer">
                        <option v-for="(n, i) in meses" :key="i" :value="i + 1">{{ n }}</option>
                    </select>
                    <select v-model="anioSeleccionado" @change="cambiarPeriodo"
                        class="text-sm border-none focus:ring-0 font-bold text-[#0D1B3E] cursor-pointer">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>

            <div class="bg-white  shadow-sm border border-[#E5E7EB] overflow-hidden">

                <div class="bg-white p-6  shadow-sm border border-[#D4A843]/30 mb-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#D4A843]/5 rounded-bl-full"></div>

                    <h2
                        class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-6 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Configuración de Cuota Mensual
                    </h2>

                    <form @submit.prevent="actualizarConfiguracion"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end relative z-10">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-1 ml-1">Monto de la
                                Cuota ($)</label>
                            <input type="number" v-model="formAjustes.cuota_monto"
                                class="w-full border-gray-200  focus:ring-[#D4A843] focus:border-[#D4A843] font-bold text-[#0D1B3E]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-1 ml-1">Día Límite de
                                Pago (Día del mes)</label>
                            <input type="number" v-model="formAjustes.cuota_dia_vencimiento" min="1" max="28"
                                class="w-full border-gray-200  focus:ring-[#D4A843] focus:border-[#D4A843] font-bold text-[#0D1B3E]">
                        </div>
                        <button type="submit" :disabled="formAjustes.processing"
                            class="h-[42px] bg-[#0D1B3E] text-[#D4A843] text-[11px] font-black uppercase tracking-widest  hover:bg-[#162554] transition-all shadow-md disabled:opacity-50">
                            Actualizar Valores
                        </button>
                    </form>
                </div>

                <table class="w-full text-left">
                    <thead class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Árbitro
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado
                                cuota</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha
                                Pago</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                                Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in arbitros" :key="user.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-[#0D1B3E] uppercase">{{ user.apellido }}, {{ user.name
                                }}</p>
                                <p class="text-[11px] text-gray-500 font-bold">{{ user.email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.esta_al_dia"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-700 text-[10px] font-black uppercase rounded-full border border-green-200">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> AL DÍA
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 text-[10px] font-black uppercase rounded-full border border-red-200">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> DEUDOR
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">
                                {{ user.pago_info ? new Date(user.pago_info.fecha_pago).toLocaleDateString() : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button v-if="!user.esta_al_dia" @click="registrarPago(user)"
                                    class="px-4 py-2 bg-[#0D1B3E] text-[#D4A843] text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-[#162554] transition-all shadow-sm">
                                    Registrar Pago
                                </button>
                                <span v-else
                                    class="text-[10px] font-black text-green-600 uppercase tracking-widest">Completado</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
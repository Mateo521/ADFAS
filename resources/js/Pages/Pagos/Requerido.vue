<script setup>
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    ticket: Array,
    total_ganado: Number,
    monto_a_pagar: Number,
    mes_nombre: String,
    dia_vencimiento: Number
});


const verificarPago = () => {
    router.get(route('dashboard')); 
};

const formatearMoneda = (monto) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(monto);
};

const formatDate = (fechaStr) => {
    if (!fechaStr) return '';
    const partes = fechaStr.split('-');
    return `${partes[2]}/${partes[1]}`;
};
</script>

<template>

    <Head title="Acceso Suspendido" />

    <div class="min-h-screen bg-[#F9FAFB] flex flex-col items-center justify-center p-4 font-['Lato',sans-serif] py-12">

        <div class="bg-white max-w-lg w-full  shadow-xl overflow-hidden border border-red-100">
            <div class="bg-red-500 p-6 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-xl -mr-10 -mt-10"></div>
                <div
                    class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-3 relative z-10 shadow-sm">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-xl font-black text-white uppercase tracking-widest relative z-10">Acceso Suspendido</h1>
                <p class="text-red-100 text-xs font-bold mt-1 uppercase relative z-10">Período de pago finalizado (Día
                    {{ dia_vencimiento }})</p>
            </div>

            <div class="p-6 md:p-8">

                <h2 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Liquidación de {{ mes_nombre }}
                </h2>

                <div class="bg-gray-50 border border-gray-200 rounded-sm p-1 mb-6">
                    <div class="max-h-60 overflow-y-auto custom-scrollbar p-3 space-y-3">

                        <div v-if="ticket.length === 0" class="text-center py-4">
                            <p class="text-xs font-bold text-gray-400 uppercase">No registrás partidos el mes pasado.
                            </p>
                        </div>

                        <div v-for="(item, index) in ticket" :key="index"
                            class="bg-white border border-gray-100 rounded-lg p-3 shadow-sm">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <p class="text-[9px] font-black text-[#D4A843] uppercase">{{ formatDate(item.fecha)
                                    }} • {{ item.cancha }}</p>
                                    <p class="text-xs font-black text-[#0D1B3E] uppercase mt-0.5">{{ item.partido }}</p>
                                </div>
                                <p class="text-xs font-black text-green-600">${{ formatearMoneda(item.monto) }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <span
                                    class="text-[9px] font-bold text-gray-500 uppercase bg-gray-100 px-2 py-0.5 rounded">{{
                                        item.categoria }}</span>
                                <span
                                    class="text-[9px] font-bold text-gray-500 uppercase bg-gray-100 px-2 py-0.5 rounded">{{
                                        item.funcion }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border-t border-gray-200 p-4 rounded-b-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-500 uppercase">Total Recaudado</span>
                            <span class="text-sm font-black text-gray-700">${{ formatearMoneda(total_ganado) }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-[#D4A843]/10 p-3 rounded-lg border border-[#D4A843]/20">
                            <span class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest">10% A Pagar</span>
                            <span class="text-2xl font-black text-[#0D1B3E]">${{ formatearMoneda(monto_a_pagar)
                            }}</span>
                        </div>
                    </div>
                </div>

                <button disabled
                    class="w-full py-4 bg-[#009EE3] text-white text-xs font-black uppercase tracking-widest rounded-sm shadow-md opacity-50 cursor-not-allowed flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                    Pagar con Mercado Pago
                </button>

                <div class="mt-6 border-t border-gray-100 pt-6">
                    <button @click="verificarPago"
                        class="w-full py-3 bg-white border-2 border-gray-200 text-gray-600 hover:border-[#D4A843] hover:text-[#0D1B3E] text-xs font-black uppercase tracking-widest rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Ya aboné
                    </button>
                </div>


            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}
</style>
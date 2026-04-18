<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    ajustes: Object
});

const formatearMoneda = (monto) => {
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(monto);
};
</script>

<template>

    <Head title="Acceso Suspendido" />

    <div class="min-h-screen bg-[#F9FAFB] flex flex-col items-center justify-center p-4 font-['Lato',sans-serif]">
        <div class="bg-white max-w-md w-full rounded-sm shadow-xl overflow-hidden text-center border border-red-100">
            <div class="bg-red-500 py-6 flex justify-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="p-8">
                <h1 class="text-2xl font-black text-[#0D1B3E] uppercase mb-2">Acceso Restringido</h1>

                <p class="text-gray-500 text-sm mb-8 font-medium leading-relaxed">
                    Tu acceso a la plataforma se encuentra suspendido temporalmente porque el período de pago mensual
                    (del 1 al {{ ajustes.cuota_dia_vencimiento }}) terminó.
                </p>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8 text-left">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total a Pagar</p>

                    <p class="text-3xl font-black text-[#0D1B3E]">
                        $ {{ formatearMoneda(ajustes.cuota_monto) }}
                        <span class="text-sm font-bold text-gray-500">ARS</span>
                    </p>
                </div>

                <button disabled
                    class="w-full py-3.5 bg-blue-500 text-black font-black uppercase tracking-widest rounded-lg shadow-md opacity-50 cursor-not-allowed flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                    Pagar con Mercado Pago
                </button>


            </div>
        </div>
    </div>
</template>
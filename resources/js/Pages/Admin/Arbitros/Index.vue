<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    arbitros: Object,
    pendientes: Array,
    filtros: Object
});

const buscar = ref(props.filtros.buscar || '');

const aplicarFiltro = () => {
    router.get(route('admin.arbitros.index'), { buscar: buscar.value }, { preserveState: true, replace: true });
};
const aprobarArbitro = (id, nombre) => {
    Swal.fire({
        title: '¿Aprobar ingreso?',
        text: `Estás por darle acceso oficial al sistema a ${nombre}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#25D366',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.arbitros.aprobar', id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                        icon: 'success', title: 'Árbitro aprobado correctamente'
                    });
                }
            });
        }
    });
};

const limpiarFiltro = () => {
    buscar.value = '';
    aplicarFiltro();
};
</script>

<template>

    <Head title="Directorio de Árbitros - ADFAS" />

    <AuthenticatedLayout>
        <div class="font-['Lato',sans-serif] max-w-7xl mx-auto pb-12">

            <div v-if="pendientes && pendientes.length > 0"
                    class="mb-8 bg-red-50 border-l-4 border-red-500 rounded-r-xl p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-red-800 font-black uppercase tracking-widest text-[13px]">Solicitudes Pendientes
                            de Aprobación ({{ pendientes.length }})</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div v-for="user in pendientes" :key="user.id"
                            class="bg-white border border-red-100 rounded-lg p-3 flex items-center justify-between shadow-sm">
                            <div class="flex flex-col">
                                <span class="text-[13px] font-bold text-[#0D1B3E] uppercase">{{ user.name }} {{
                                    user.apellido }}</span>
                                <span class="text-[11px] text-gray-500">{{ user.email }}</span>
                            </div>
                            <button @click="aprobarArbitro(user.id, user.apellido)"
                                class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-[10px] font-black uppercase tracking-widest transition-colors shadow-sm">
                                Aprobar
                            </button>
                        </div>
                    </div>
                </div>

            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                

                <div>
                    <p class="text-[11px] font-black tracking-[3px] uppercase text-[#A87C20] mb-2">Plantel Oficial</p>
                    <h1
                        class=" text-[32px] md:text-[40px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-3">
                        Directorio de Árbitros
                    </h1>
                    <div class="flex items-center gap-2.5">
                        <span class="w-16 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                        <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                    </div>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <div class="relative flex items-center flex-1 md:w-[600px]">
                        <svg class="w-4 h-4 right-4 text-[#9CA3AF] absolute  top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        <input type="text" v-model="buscar" @keyup.enter="aplicarFiltro" placeholder="Buscar..."
                            class="w-full pl-10 pr-10 py-3 bg-white border border-[#E5E7EB] rounded-lg  font-bold text-[#111827] placeholder:text-[#9CA3AF] focus:border-[#D4A843] focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all shadow-sm">
                    </div>

                    <button v-if="buscar" @click="limpiarFiltro"
                        class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors border border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <button @click="aplicarFiltro"
                        class="px-6 py-3 bg-[#0D1B3E] text-[#D4A843] text-[12px] font-black uppercase tracking-widest rounded-lg hover:bg-[#162554] transition-colors shadow-sm">
                        Buscar
                    </button>
                </div>


            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                <Link v-for="arbitro in arbitros.data" :key="arbitro.id"
                    :href="route('admin.arbitros.show', arbitro.id)"
                    class="group bg-white rounded-2xl border border-[#E5E7EB] p-6 text-center hover:border-[#D4A843]/50 hover:shadow-[0_12px_30px_rgba(13,27,62,0.06)] transition-all duration-300 hover:-translate-y-1 relative overflow-hidden">

                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-[#D4A843]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-bl-full">
                    </div>

                    <div
                        class="w-20 h-20 mx-auto rounded-full bg-[#0D1B3E] border-2 border-[#D4A843]/30 overflow-hidden flex items-center justify-center mb-4 group-hover:border-[#D4A843] transition-colors shadow-sm relative z-10">
                        <img v-if="arbitro.foto_perfil" :src="`/storage/${arbitro.foto_perfil}`"
                            class="w-full h-full object-cover">
                        <span v-else class="text-[#D4A843] font-black text-2xl">{{ arbitro.name.charAt(0) }}{{
                            arbitro.apellido.charAt(0) }}</span>
                    </div>

                    <div class="relative z-10">
                        <h2
                            class="text-[16px] font-black text-[#0D1B3E] uppercase tracking-wide leading-tight mb-1 group-hover:text-[#A87C20] transition-colors">
                            {{ arbitro.apellido }}, {{ arbitro.name }}
                        </h2>
                        <p class="text-[11px] font-bold text-[#6B7280] uppercase tracking-widest mb-4">Árbitro</p>

                        <div
                            class="inline-flex items-center justify-center w-full py-2.5 bg-[#F9FAFB] border border-[#E5E7EB] rounded text-[11px] font-black text-[#0D1B3E] uppercase tracking-widest group-hover:bg-[#0D1B3E] group-hover:text-white transition-colors duration-300">
                            Ver Ficha Técnica
                        </div>
                    </div>
                </Link>

            </div>

            <div v-if="arbitros.data.length === 0"
                class="bg-white border border-[#E5E7EB] rounded-2xl p-16 text-center shadow-sm">
                <p class="text-lg font-black text-[#0D1B3E] uppercase tracking-widest mb-2">No se encontraron árbitros
                </p>
                <p class="text-sm text-[#6B7280] font-medium">Intentá con otro término de búsqueda.</p>
            </div>

            <div v-if="arbitros.links.length > 3" class="mt-12 flex justify-center">
                <nav class="inline-flex gap-2 p-1.5 bg-white border border-[#E5E7EB] rounded-xl shadow-sm">
                    <template v-for="(link, key) in arbitros.links" :key="key">
                        <div v-if="link.url === null"
                            class="px-4 py-2 text-[11px] font-black text-gray-300 uppercase tracking-widest cursor-not-allowed"
                            v-html="link.label"></div>
                        <Link v-else :href="link.url"
                            class="px-4 py-2 text-[11px] font-black uppercase tracking-widest rounded-lg transition-all duration-200"
                            :class="link.active ? 'bg-[#0D1B3E] text-[#D4A843] shadow-md' : 'text-[#6B7280] hover:bg-[#F7F5F0] hover:text-[#0D1B3E]'"
                            v-html="link.label"></Link>
                    </template>
                </nav>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
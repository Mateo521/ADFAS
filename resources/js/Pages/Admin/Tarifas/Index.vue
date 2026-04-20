<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    tarifas: Array
});

const form = useForm({
    categoria: '',
    disciplina: 'FUTBOL 11',
    monto_principal: '',
    monto_asistente: ''
});

const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

const crearTarifa = () => {
    form.post(route('admin.tarifas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('categoria', 'monto_principal', 'monto_asistente');
            Toast.fire({ icon: 'success', title: 'Arancel registrado' });
        }
    });
};

const editarTarifa = (tarifa) => {
    Swal.fire({
        title: `Editar Arancel: ${tarifa.categoria}`,
        html: `
            <div class="flex flex-col gap-3 mt-2 text-left">
                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Monto Árbitro Principal ($)</label>
                    <input id="swal-prin" type="number" class="w-full mt-1 border-gray-300 rounded" value="${tarifa.monto_principal}">
                </div>
                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Monto Asistentes ($)</label>
                    <input id="swal-asis" type="number" class="w-full mt-1 border-gray-300 rounded" value="${tarifa.monto_asistente}">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar cambios',
        confirmButtonColor: '#0D1B3E',
        preConfirm: () => {
            return {
                monto_principal: document.getElementById('swal-prin').value,
                monto_asistente: document.getElementById('swal-asis').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(route('admin.tarifas.update', tarifa.id), result.value, {
                onSuccess: () => Toast.fire({ icon: 'success', title: 'Actualizado' })
            });
        }
    });
};

const eliminarTarifa = (id) => {
    Swal.fire({
        title: '¿Eliminar arancel?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.tarifas.destroy', id), {
                onSuccess: () => Toast.fire({ icon: 'success', title: 'Eliminado' })
            });
        }
    });
};

const formatMoneda = (monto) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 0 }).format(monto);
};
</script>

<template>

    <Head title="Aranceles de Partidos - ADFAS" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto pb-12 font-['Lato',sans-serif]">

            <div class="mb-8">
                <h1 class="text-[28px] font-extrabold text-[#0D1B3E] uppercase tracking-tight leading-none mb-2">Lista
                    de Aranceles</h1>
                <div class="flex items-center gap-2.5 mb-2"><span
                        class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span><span
                        class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span></div>
                <p class="text-sm text-[#6B7280] font-bold">Configurá cuánto paga cada categoría. El sistema va a usar esto
                    para calcular el 10% semanal.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1">
                    <div class="bg-white p-6  shadow-sm border border-[#E5E7EB]">
                        <h3 class="text-sm font-black text-[#0D1B3E] uppercase tracking-widest mb-5">Nuevo Arancel</h3>

                        <form @submit.prevent="crearTarifa" class="flex flex-col gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-500 uppercase mb-1">Disciplina</label>
                                <select v-model="form.disciplina"
                                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]">
                                    <option value="FUTBOL 11">FÚTBOL 11</option>
                                    <option value="FUTSAL">FUTSAL</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1">Categoría (Ej:
                                    PRIMERA A)</label>
                                <input type="text" v-model="form.categoria" required
                                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843] uppercase">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-1">Pago
                                        Principal</label>
                                    <input type="number" v-model="form.monto_principal" required placeholder="Ej: 24000"
                                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-1">Pago
                                        Asistente</label>
                                    <input type="number" v-model="form.monto_asistente" required placeholder="Ej: 10000"
                                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]">
                                </div>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="mt-2 w-full py-3 bg-[#0D1B3E] text-[#D4A843] text-xs font-black uppercase tracking-widest rounded-sm hover:bg-[#162554] shadow-sm transition-colors disabled:opacity-50">
                                Guardar Arancel
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white border border-[#E5E7EB]  shadow-sm overflow-hidden">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-[10px] font-black text-[#6B7280] uppercase tracking-widest">
                                        Disciplina / Cat.</th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-black text-[#6B7280] uppercase tracking-widest text-right">
                                        Principal</th>
                                    <th
                                        class="px-6 py-4 text-[10px] font-black text-[#6B7280] uppercase tracking-widest text-right">
                                        Asistentes</th>
                                    <th class="px-4 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="tarifas.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 font-bold">No
                                        hay aranceles cargados.</td>
                                </tr>
                                <tr v-for="tarifa in tarifas" :key="tarifa.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <p class="text-[10px] font-black text-gray-400 uppercase">{{ tarifa.disciplina
                                            }}</p>
                                        <p class="text-sm font-black text-[#0D1B3E] uppercase">{{ tarifa.categoria }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="bg-green-50 text-green-700 px-3 py-1 rounded font-black text-sm">{{
                                            formatMoneda(tarifa.monto_principal) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded font-black text-sm">{{
                                            formatMoneda(tarifa.monto_asistente) }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="editarTarifa(tarifa)"
                                                class="p-2 text-gray-400 hover:text-[#0D1B3E] bg-gray-50 hover:bg-gray-200 rounded transition-colors"><svg
                                                    class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg></button>
                                            <button @click="eliminarTarifa(tarifa.id)"
                                                class="p-2 text-gray-400 hover:text-red-600 bg-gray-50 hover:bg-red-50 rounded transition-colors"><svg
                                                    class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
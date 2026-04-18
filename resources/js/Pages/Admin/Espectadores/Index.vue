<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    espectadores: Array
});

const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
});

const form = useForm({
    name: '',
    email: '',
    password: ''
});

const crearEspectador = () => {
    form.post(route('admin.espectadores.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            Toast.fire({ icon: 'success', title: 'Cuenta creada exitosamente' });
        },
    });
};

const eliminarEspectador = (id, nombre) => {
    Swal.fire({
        title: '¿Revocar acceso?',
        text: `Se eliminará la cuenta de ${nombre}. Ya no podrá ver la pizarra.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.espectadores.destroy', id), {
                preserveScroll: true,
                onSuccess: () => Toast.fire({ icon: 'success', title: 'Cuenta eliminada' })
            });
        }
    });
};
</script>

<template>
    <Head title="Cuentas de Espectadores - ADFAS" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto pb-12 font-['Lato',sans-serif]">
            
            <div class="mb-8">
                <h1 class="text-[28px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-2 uppercase tracking-wide">Cuentas de Visualización</h1>
                <div class="flex items-center gap-2.5 mb-2"><span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span><span class="w-1 h-1 bg-[#D4A843] rotate-45 shrink-0"></span></div>
                <p class="text-sm text-[#6B7280] font-bold">Creá accesos de solo vista.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-[#0D1B3E] px-5 py-4 border-b border-[#D4A843]/30">
                            <h3 class="text-white font-black uppercase tracking-widest text-[13px] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Nuevo Acceso
                            </h3>
                        </div>
                        <form @submit.prevent="crearEspectador" class="p-5 flex flex-col gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-[#6B7280] uppercase tracking-widest mb-1">Nombre - identificador</label>
                                <input type="text" v-model="form.name" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-[#6B7280] uppercase tracking-widest mb-1">Usuario (Correo)</label>
                                <input type="email" v-model="form.email" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]" placeholder="club@adfas.com" required>
                                <p v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-[#6B7280] uppercase tracking-widest mb-1">Contraseña</label>
                                <input type="password" v-model="form.password" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#D4A843] focus:border-[#D4A843]" required minlength="6">
                            </div>
                            <button type="submit" :disabled="form.processing" class="mt-2 w-full py-2.5 bg-[#0D1B3E] text-[#D4A843] text-xs font-black uppercase tracking-widest rounded-lg hover:bg-[#162554] transition-colors shadow-sm disabled:opacity-50">
                                {{ form.processing ? 'Creando...' : 'Generar Cuenta' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-[#6B7280] uppercase tracking-widest">Entidad / Nombre</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-[#6B7280] uppercase tracking-widest">Usuario (Login)</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-[#6B7280] uppercase tracking-widest text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="espectadores.length === 0">
                                    <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500 font-bold">No hay cuentas de espectador creadas.</td>
                                </tr>
                                <tr v-for="user in espectadores" :key="user.id" class="border-b border-[#E5E7EB] last:border-0 hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center border border-blue-100">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </div>
                                            <span class="font-black text-[13px] text-[#0D1B3E] uppercase">{{ user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold text-gray-600">{{ user.email }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="eliminarEspectador(user.id, user.name)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition-colors" title="Eliminar Acceso">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
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
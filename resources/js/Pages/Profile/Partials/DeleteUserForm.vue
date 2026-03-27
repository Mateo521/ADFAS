<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <h2 class="font-['Playfair_Display',serif] text-[24px] font-extrabold text-red-900">
                    Eliminar Cuenta
                </h2>
            </div>
            <p class="text-[13px] text-red-800/80 font-medium">
                Una vez que elimines tu cuenta, todos los recursos y datos se borrarán permanentemente.
            </p>
        </header>

        <button @click="confirmUserDeletion" class="px-6 py-3 bg-red-600 text-white text-[12px] font-black uppercase tracking-widest rounded-lg hover:bg-red-700 transition-colors shadow-sm border border-red-700">
            Eliminar mi cuenta
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 font-['Lato',sans-serif]">
                <h2 class="font-['Playfair_Display',serif] text-[22px] font-extrabold text-[#0D1B3E] mb-2">
                    ¿Estás seguro que deseas eliminar tu cuenta?
                </h2>

                <p class="text-[13px] text-[#6B7280] font-medium mb-6">
                    Una vez que tu cuenta sea eliminada, toda su información será borrada permanentemente. Por favor, ingresa tu contraseña para confirmar.
                </p>

                <div class="flex flex-col gap-2 mb-6">
                    <label for="password" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Contraseña</label>
                    <input id="password" ref="passwordInput" v-model="form.password" type="password" placeholder="Ingresa tu contraseña" @keyup.enter="deleteUser"
                           class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-red-500 focus:bg-white focus:ring-[2px] focus:ring-red-500/20 transition-all">
                    <p v-if="form.errors.password" class="text-[11px] font-bold text-red-500">{{ form.errors.password }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
                    <button @click="closeModal" class="px-6 py-3 rounded-lg text-[12px] font-black uppercase tracking-widest text-[#6B7280] bg-white border border-[#D1D5DB] hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button @click="deleteUser" :disabled="form.processing" class="px-6 py-3 rounded-lg text-[12px] font-black uppercase tracking-widest text-white bg-red-600 hover:bg-red-700 border border-red-700 transition-colors disabled:opacity-50">
                        Sí, Eliminar
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
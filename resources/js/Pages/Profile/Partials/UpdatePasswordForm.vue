<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-6">
            <h2 class=" text-[24px] font-extrabold text-[#0D1B3E] mb-1">
                Actualizar contraseña
            </h2>
            <p class="text-[13px] text-[#6B7280] font-medium">
                Asegurate de que tu cuenta esté usando una contraseña larga y aleatoria para mantenerte seguro.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-6">
            
            <div class="flex flex-col gap-2">
                <label for="current_password" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Contraseña Actual</label>
                <input id="current_password" ref="currentPasswordInput" v-model="form.current_password" type="password" autocomplete="current-password"
                       class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                <p v-if="form.errors.current_password" class="text-[11px] font-bold text-red-500">{{ form.errors.current_password }}</p>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Nueva Contraseña</label>
                <input id="password" ref="passwordInput" v-model="form.password" type="password" autocomplete="new-password"
                       class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                <p v-if="form.errors.password" class="text-[11px] font-bold text-red-500">{{ form.errors.password }}</p>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Confirmar Nueva Contraseña</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password"
                       class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                <p v-if="form.errors.password_confirmation" class="text-[11px] font-bold text-red-500">{{ form.errors.password_confirmation }}</p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" :disabled="form.processing"
                        class="px-8 py-3 bg-[#0D1B3E] text-white text-[12px] font-black uppercase tracking-[2px] rounded-lg transition-all hover:bg-[#162554] disabled:opacity-65 shadow-md">
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Guardar Contraseña</span>
                </button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-[12px] font-black text-green-600 flex items-center gap-1 uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Actualizada
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
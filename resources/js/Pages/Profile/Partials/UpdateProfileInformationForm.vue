<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

// Agregamos telefono y foto_perfil al formulario
const form = useForm({
    _method: 'patch', // Truco necesario para subir archivos en Laravel/Inertia
    name: user.name,
    apellido: user.apellido || '',
    email: user.email,
    telefono: user.telefono || '',
    foto_perfil: null,
});

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Limpiamos el input de archivo si todo salió bien
            form.foto_perfil = null;
            const fileInput = document.getElementById('foto_perfil');
            if (fileInput) fileInput.value = '';
        }
    });
};
</script>

<template>
    <section>
        <header class="mb-6">
            <h2 class="font-['Playfair_Display',serif] text-[24px] font-extrabold text-[#0D1B3E] mb-1">
                Información del Perfil
            </h2>
            <p class="text-[13px] text-[#6B7280] font-medium">
                Actualizá tu nombre, correo electrónico, número de contacto y foto de perfil.
            </p>
        </header>

        <form @submit.prevent="submit" class="space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label for="name" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Nombre</label>
                    <input id="name" type="text" v-model="form.name" required autofocus autocomplete="name"
                           class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                    <p v-if="form.errors.name" class="text-[11px] font-bold text-red-500">{{ form.errors.name }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="apellido" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Apellido</label>
                    <input id="apellido" type="text" v-model="form.apellido" required autocomplete="family-name"
                           class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                    <p v-if="form.errors.apellido" class="text-[11px] font-bold text-red-500">{{ form.errors.apellido }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Correo Electrónico</label>
                <input id="email" type="email" v-model="form.email" required autocomplete="username"
                       class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                <p v-if="form.errors.email" class="text-[11px] font-bold text-red-500">{{ form.errors.email }}</p>
            </div>

            <div class="flex flex-col gap-2">
                <label for="telefono" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E] flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.383 0 12.03c0 2.124.553 4.195 1.605 6.01L.031 24l6.143-1.579a12.015 12.015 0 005.857 1.522c6.645 0 12.03-5.385 12.03-12.03C24 5.383 18.675 0 12.031 0zm0 21.944c-1.802 0-3.565-.483-5.111-1.398l-.367-.217-3.801.977 1.015-3.69-.239-.38a9.988 9.988 0 01-1.525-5.305c0-5.508 4.484-9.992 9.992-9.992s9.992 4.484 9.992 9.992-4.484 9.992-9.992 9.992zm5.48-7.48c-.301-.15-1.782-.88-2.057-.98-.276-.1-.478-.15-.678.15-.2.302-.779.98-.954 1.18-.175.201-.351.226-.652.076a8.212 8.212 0 01-2.42-1.493 8.973 8.973 0 01-1.678-2.083c-.176-.301-.019-.465.132-.615.136-.135.301-.351.452-.526.15-.176.2-.302.3-.502.101-.202.051-.377-.025-.527-.075-.15-.678-1.63-.928-2.232-.243-.586-.489-.507-.678-.516-.175-.008-.376-.008-.577-.008a1.11 1.11 0 00-.803.376 3.36 3.36 0 00-1.054 2.502c0 1.956 1.405 3.847 1.605 4.122.2.276 2.81 4.288 6.804 6.015 2.593 1.123 3.633 1.206 4.887 1.018.99-.148 3.123-1.275 3.563-2.507.44-1.232.44-2.285.308-2.507-.132-.222-.478-.352-.779-.502z"/></svg>
                    Número de WhatsApp
                </label>
                <input id="telefono" type="text" v-model="form.telefono" placeholder="Ej: 5492664000000"
                       class="w-full px-4 py-3 bg-[#F9FAFB] border border-[#D1D5DB] rounded-lg text-[14px] font-semibold text-[#111827] focus:border-[#D4A843] focus:bg-white focus:ring-[2px] focus:ring-[#D4A843]/20 transition-all">
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide">Incluye el código de país sin el signo + (Ej: 549...)</p>
                <p v-if="form.errors.telefono" class="text-[11px] font-bold text-red-500">{{ form.errors.telefono }}</p>
            </div>

            <div class="flex flex-col gap-2">
                <label for="foto_perfil" class="text-[11px] font-black tracking-[1px] uppercase text-[#0D1B3E]">Foto de Perfil</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-[#0D1B3E] border-2 border-[#D4A843]/50 overflow-hidden flex items-center justify-center shrink-0">
                        <img v-if="user.foto_perfil" :src="`/storage/${user.foto_perfil}`" class="w-full h-full object-cover">
                        <span v-else class="text-[#D4A843] font-black text-lg">{{ user.name.charAt(0) }}</span>
                    </div>
                    <div class="flex-1">
                        <input id="foto_perfil" type="file" @input="form.foto_perfil = $event.target.files[0]" accept="image/*"
                               class="block w-full text-[12px] text-[#6B7280] font-medium file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[11px] file:font-black file:uppercase file:tracking-wider file:bg-[#0D1B3E] file:text-white hover:file:bg-[#162554] hover:file:text-[#D4A843] file:transition-colors cursor-pointer focus:outline-none">
                    </div>
                </div>
                <p v-if="form.errors.foto_perfil" class="text-[11px] font-bold text-red-500">{{ form.errors.foto_perfil }}</p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" :disabled="form.processing"
                        class="px-8 py-3 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[12px] font-black uppercase tracking-[2px] rounded-lg transition-all duration-300 shadow-[0_2px_8px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] disabled:opacity-65 disabled:cursor-not-allowed">
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Guardar Cambios</span>
                </button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-[12px] font-black text-green-600 flex items-center gap-1 uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Actualizado
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
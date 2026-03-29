<script setup>
import { onMounted } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';


const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});


onMounted(() => {
    if (props.status) {
        Swal.fire({
            icon: 'info',
            title: 'Aviso del Sistema',
            text: props.status,
            confirmButtonColor: '#0D1B3E',
            confirmButtonText: 'Entendido'
        });
    }
});
</script>

<template>
    <GuestLayout>

        <Head title="Ingresar — ADFAS San Luis" />

        <div class="mb-8 text-center">
            <p class="text-base font-black tracking-[3px] uppercase text-[#A87C20] mb-2">
                Portal
            </p>
            <h1
                class="font-['Playfair_Display',serif]- text-[32px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-[14px]">
                Acceso al sistema
            </h1>
            <div class="flex items-center justify-center gap-2.5">
                <span class="w-12 h-px bg-gradient-to-r from-transparent to-[#D4A843]"></span>
                <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                <span class="w-12 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
            </div>
        </div>

        <div v-if="status"
            class="flex items-center gap-2.5 mb-6 px-4 py-3 bg-[#F0FDF4] border border-[#BBF7D0] rounded-lg text-[13px] font-semibold text-[#166534]">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="shrink-0">
                <circle cx="8" cy="8" r="7.25" stroke="#166534" stroke-width="1.5" />
                <path d="M5 8l2 2 4-4" stroke="#166534" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-[22px]" novalidate>

            <div class="flex flex-col gap-1.5">
                <label for="email"
                    class="flex items-center gap-[7px] text-[12px] font-black tracking-[1px] uppercase text-[#0D1B3E]">
                    <span class="flex items-center text-[#D4A843]">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <rect x="1" y="2.5" width="12" height="9" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M1 4.5l6 4 6-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg>
                    </span>
                    Correo Electrónico Oficial
                </label>
                <div class="relative rounded-lg border-[1.5px] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15"
                    :class="form.errors.email ? 'border-[#B91C1C]' : 'border-[#D1D5DB]'">
                    <TextInput id="email" type="email"
                        class="w-full px-4 py-[13px] border-none bg-transparent font-['Lato',sans-serif] text-[15px] text-[#111827] rounded-lg focus:ring-0 placeholder:text-[#9CA3AF] placeholder:font-normal"
                        v-model="form.email" required autofocus autocomplete="username"
                        placeholder="ejemplo@gmail.com" />
                </div>
                <InputError class="text-[12px] font-bold text-[#B91C1C] mt-1" :message="form.errors.email" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password"
                    class="flex items-center gap-[7px] text-[12px] font-black tracking-[1px] uppercase text-[#0D1B3E]">
                    <span class="flex items-center text-[#D4A843]">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <rect x="3" y="6" width="8" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3" />
                            <path d="M5 6V4.5a2 2 0 014 0V6" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg>
                    </span>
                    Contraseña Privada
                </label>
                <div class="relative rounded-lg border-[1.5px] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15"
                    :class="form.errors.password ? 'border-[#B91C1C]' : 'border-[#D1D5DB]'">
                    <TextInput id="password" type="password"
                        class="w-full px-4 py-[13px] border-none bg-transparent font-['Lato',sans-serif] text-[15px] text-[#111827] rounded-lg focus:ring-0 placeholder:text-[#9CA3AF] placeholder:font-normal"
                        v-model="form.password" required autocomplete="current-password" placeholder="••••••••••" />
                </div>
                <InputError class="text-[12px] font-bold text-[#B91C1C] mt-1" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2.5 text-[13px] font-semibold text-[#374151] cursor-pointer">
                <Checkbox name="remember" v-model:checked="form.remember"
                    class="w-4 h-4 rounded border-[#9CA3AF] text-[#1E3370] focus:ring-[#1E3370]" />
                <span>Recordarme en este dispositivo</span>
            </label>

            <div class="flex flex-col gap-4 pt-2 border-t border-[#E5E7EB] mt-1">
                <div class="flex w-full justify-between">
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-[12px] font-bold text-[#1E3370] hover:text-[#D4A843] hover:underline tracking-[0.3px] transition-colors self-start">
                        ¿Olvidaste tu contraseña?
                    </Link>

                    <Link v-if="canResetPassword" :href="route('register')"
                        class="text-[12px] font-bold text-[#1E3370] hover:text-[#D4A843] hover:underline tracking-[0.3px] transition-colors self-start">
                        Registrarse
                    </Link>
                </div>
                <button type="submit" :disabled="form.processing"
                    class="group relative overflow-hidden w-full py-[15px] px-6 bg-gradient-to-br from-[#0D1B3E] to-[#1E3370] text-white border border-[#D4A843]/40 rounded-lg font-['Lato',sans-serif] text-[13px] font-black tracking-[2px] uppercase transition-all duration-300 hover:-translate-y-[1px] hover:shadow-[0_8px_24px_rgba(13,27,62,0.25),0_0_0_1px_rgba(212,168,67,0.4)] hover:border-[#D4A843]/70 disabled:opacity-65 disabled:cursor-not-allowed disabled:transform-none">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-[#D4A843]/15 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>

                    <span v-if="!form.processing" class="relative z-10 flex items-center justify-center gap-2.5">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Iniciar sesión
                    </span>
                    <span v-else class="relative z-10 flex items-center justify-center gap-2.5">
                        <svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" stroke-dasharray="20"
                                stroke-dashoffset="5" />
                        </svg>
                        Verificando…
                    </span>
                </button>
            </div>
        </form>

    </GuestLayout>
</template>
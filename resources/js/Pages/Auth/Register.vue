<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registro Institucional — ADFAS San Luis" />

        <div class="mb-7 text-center">
            <p class="text-base font-black tracking-[3px] uppercase text-[#A87C20] mb-2">
                Portal 
            </p>
            <h1 class="font-['Playfair_Display',serif]- text-[30px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-3">
                Crear Cuenta
            </h1>
            <div class="flex items-center justify-center gap-2.5 mb-3">
                <span class="w-10 h-px bg-gradient-to-r from-transparent to-[#D4A843]"></span>
                <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                <span class="w-10 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
            </div>
            <p class="text-[13px] text-[#6B7280] font-medium leading-relaxed">
                Completá los datos para el solicitar acceso al sistema de la ADFAS
            </p>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-[18px]" novalidate>

            <div class="flex flex-col gap-1.5">
                <label for="name" class="flex items-center gap-[7px] text-[12px] font-black tracking-[1px] uppercase text-[#0D1B3E]">
                    <span class="flex items-center text-[#D4A843]">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <circle cx="7" cy="5" r="2.5" stroke="currentColor" stroke-width="1.3"/>
                            <path d="M2 12c0-2.761 2.239-5 5-5s5 2.239 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                        </svg>
                    </span>
                    Nombre Completo
                </label>
                <div class="relative rounded-lg border-[1.5px] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15"
                     :class="form.errors.name ? 'border-[#B91C1C]' : 'border-[#D1D5DB]'">
                    <TextInput
                        id="name"
                        type="text"
                        class="w-full px-[15px] py-[12px] border-none bg-transparent font-['Lato',sans-serif] text-[14px] text-[#111827] rounded-lg focus:ring-0 placeholder:text-[#9CA3AF] placeholder:font-normal"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Ej: Juan Martínez"
                    />
                </div>
                <InputError class="text-[12px] font-bold text-[#B91C1C]" :message="form.errors.name" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="email" class="flex items-center gap-[7px] text-[12px] font-black tracking-[1px] uppercase text-[#0D1B3E]">
                    <span class="flex items-center text-[#D4A843]">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <rect x="1" y="2.5" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                            <path d="M1 4.5l6 4 6-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                        </svg>
                    </span>
                    Correo Electrónico
                </label>
                <div class="relative rounded-lg border-[1.5px] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15"
                     :class="form.errors.email ? 'border-[#B91C1C]' : 'border-[#D1D5DB]'">
                    <TextInput
                        id="email"
                        type="email"
                        class="w-full px-[15px] py-[12px] border-none bg-transparent font-['Lato',sans-serif] text-[14px] text-[#111827] rounded-lg focus:ring-0 placeholder:text-[#9CA3AF] placeholder:font-normal"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="ejemplo@gmail.com"
                    />
                </div>
                <InputError class="text-[12px] font-bold text-[#B91C1C]" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[14px]">
                
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="flex items-center gap-[7px] text-[12px] font-black tracking-[1px] uppercase text-[#0D1B3E]">
                        <span class="flex items-center text-[#D4A843]">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <rect x="3" y="6" width="8" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M5 6V4.5a2 2 0 014 0V6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                        </span>
                        Contraseña
                    </label>
                    <div class="relative rounded-lg border-[1.5px] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15"
                         :class="form.errors.password ? 'border-[#B91C1C]' : 'border-[#D1D5DB]'">
                        <TextInput
                            id="password"
                            type="password"
                            class="w-full px-[15px] py-[12px] border-none bg-transparent font-['Lato',sans-serif] text-[14px] text-[#111827] rounded-lg focus:ring-0 placeholder:text-[#9CA3AF] placeholder:font-normal"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Mín. 8 caracteres"
                        />
                    </div>
                    <InputError class="text-[12px] font-bold text-[#B91C1C]" :message="form.errors.password" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="password_confirmation" class="flex items-center gap-[7px] text-[12px] font-black tracking-[1px] uppercase text-[#0D1B3E]">
                        <span class="flex items-center text-[#D4A843]">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <rect x="3" y="6" width="8" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M5 6V4.5a2 2 0 014 0V6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                <path d="M6 9.5l1 1 2-2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        Confirmar Clave
                    </label>
                    <div class="relative rounded-lg border-[1.5px] bg-white transition-all duration-200 focus-within:border-[#D4A843] focus-within:ring-[3px] focus-within:ring-[#D4A843]/15"
                         :class="form.errors.password_confirmation ? 'border-[#B91C1C]' : 'border-[#D1D5DB]'">
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="w-full px-[15px] py-[12px] border-none bg-transparent font-['Lato',sans-serif] text-[14px] text-[#111827] rounded-lg focus:ring-0 placeholder:text-[#9CA3AF] placeholder:font-normal"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Repetir contraseña"
                        />
                    </div>
                    <InputError class="text-[12px] font-bold text-[#B91C1C]" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="flex items-center gap-2 px-3.5 py-2.5 bg-[#D4A843]/5 border border-[#D4A843]/20 rounded-md text-[12px] font-semibold text-[#6B7280]">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" class="shrink-0">
                    <path d="M6.5 1L2 3v3.5c0 2.5 2 4.8 4.5 5.5C9 11.3 11 9 11 6.5V3L6.5 1z" stroke="#A87C20" stroke-width="1.2" stroke-linejoin="round"/>
                </svg>
                Sus datos son confidenciales.
            </div>

            <div class="flex flex-col gap-3.5 pt-2 border-t border-[#E5E7EB]">
                <Link :href="route('login')" class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#1E3370] hover:text-[#D4A843] transition-colors self-start tracking-[0.3px]">
                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                        <path d="M8 2L4 6.5 8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Ya tengo una cuenta
                </Link>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="group relative overflow-hidden w-full py-[15px] px-6 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] rounded-lg font-['Lato',sans-serif] text-[13px] font-black tracking-[2px] uppercase transition-all duration-300 shadow-[0_2px_8px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] hover:shadow-[0_8px_20px_rgba(168,124,32,0.35)] disabled:opacity-65 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <span v-if="!form.processing" class="relative z-10 flex items-center justify-center gap-2.5">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6.75" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Registrarse
                    </span>
                    <span v-else class="relative z-10 flex items-center justify-center gap-2.5">
                        <svg class="animate-spin" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" stroke-dasharray="20" stroke-dashoffset="5"/>
                        </svg>
                        Procesando…
                    </span>
                </button>
            </div>
        </form>

    </GuestLayout>
</template>
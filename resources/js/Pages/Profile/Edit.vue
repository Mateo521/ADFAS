<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Mi Perfil - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="max-w-5xl mx-auto font-['Lato',sans-serif] pb-12">
            
            <div class="mb-8">
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6B7280] hover:text-[#D4A843] uppercase tracking-widest transition-colors group mb-6">
                    <div class="p-1.5 rounded-full bg-white border border-[#E5E7EB] group-hover:border-[#D4A843] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </div>
                    Volver al Panel
                </Link>
                
                <p class="text-[11px] font-black tracking-[3px] uppercase text-[#A87C20] mb-2">Configuración de Cuenta</p>
                <h1 class="font-['Playfair_Display',serif] text-[32px] md:text-[40px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-3">
                    Mi Perfil
                </h1>
                <div class="flex items-center gap-2.5">
                    <span class="w-16 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                    <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                </div>
            </div>

            <div class="space-y-8">
                
                <div class="bg-white border border-[#E5E7EB] rounded p-8 md:p-10 shadow-[0_8px_30px_rgba(13,27,62,0.04)] relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-[#D4A843]/5 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="relative z-10 max-w-7xl">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>
                </div>

                <div class="bg-white border border-[#E5E7EB] rounded p-8 md:p-10 shadow-[0_8px_30px_rgba(13,27,62,0.04)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-64 h-64 bg-[#0D1B3E]/5 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="relative z-10 max-w-7xl">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <div class="bg-red-50/30 border border-red-100 rounded p-8 md:p-10 shadow-sm relative overflow-hidden">
                    <div class="relative z-10 max-w-7xl">
                        <DeleteUserForm />
                    </div>
                </div>

            </div>
        </div>
        
    </AuthenticatedLayout>
</template>
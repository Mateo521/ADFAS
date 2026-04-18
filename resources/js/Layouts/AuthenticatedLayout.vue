<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#F7F5F0] text-gray-900">




        <nav class="bg-[#0D1B3E] sticky top-0 z-50 border-b-2 border-[#D4A843]/25 shadow-[0_4px_24px_rgba(0,0,0,0.35)]">


            <div class="h-[3px] bg-gradient-to-r from-transparent via-[#D4A843] to-transparent opacity-60"></div>

            <div class=" px-4 sm:px-6 lg:px-8 h-[72px] flex items-center justify-between gap-6">
                <!-- max-w-7xl mx-auto -->


                <Link :href="route('dashboard')"
                    class="flex items-center gap-3 shrink-0 group hover:opacity-90 transition-opacity">
                    <div
                        class="w-11 h-11 rounded-full border-2 border-[#D4A843]/50 p-[3px] bg-[#D4A843]/10 overflow-hidden shrink-0 shadow-[0_0_0_4px_rgba(212,168,67,0.08)]">
                        <img src="/images/logo-adfas.jpeg" alt="Logo ADFAS San Luis"
                            class="w-full h-full object-cover rounded-full">
                    </div>
                    <div class="hidden lg:block">
                        <p class="text-white font-black text-base leading-none  uppercase">ADFAS</p>
                        <p class="text-[#D4A843] text-[9px] font-bold uppercase tracking-[0.18em] mt-0.5 opacity-80">
                            Asociación Arbitral San Luis
                        </p>
                    </div>
                </Link>


                <div class="hidden md:flex items-center gap-2 flex-1 justify-center">

                    <Link :href="route('dashboard')"
                        class="relative px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                        :class="route().current('dashboard')
                            ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                            : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                        Inicio
                    </Link>

                    <template v-if="user.rol === 'admin'">
                        <div class="w-px h-4 bg-white/15 mx-1"></div>

                        <Link :href="route('admin.importar.index')"
                            class="px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                            :class="route().current('admin.importar.*')
                                ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                                : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                            Importar Excel
                        </Link>

                        <Link :href="route('admin.historial.index')"
                            class="px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                            :class="route().current('admin.historial.*')
                                ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                                : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                            Gestión y Monitoreo
                        </Link>
                        <Link :href="route('admin.arbitros.index')"
                            class="px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                            :class="route().current('admin.arbitros.*')
                                ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                                : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                            Panel de árbitros
                        </Link>

                        <Link :href="route('admin.espectadores.index')"
                            class="px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                            :class="route().current('admin.arbitros.*')
                                ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                                : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                            Espectadores
                        </Link>

                    </template>

                    <div class="w-px h-4 bg-white/15 mx-1"></div>

                    <Link :href="route('noticias.index')"
                        class="px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                        :class="route().current('noticias.*')
                            ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                            : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                        Noticias
                    </Link>

                    <Link :href="route('profile.edit')"
                        class="px-4 py-2 text-sm font-black uppercase tracking-[0.15em] rounded-md transition-all duration-200"
                        :class="route().current('profile.*')
                            ? 'text-[#D4A843] bg-[#D4A843]/10 ring-1 ring-[#D4A843]/25'
                            : 'text-white/55 hover:text-white hover:bg-white/[0.07]'">
                        Mi perfil
                    </Link>
                </div>


                <div class="hidden md:flex items-center gap-3 shrink-0">

                    <div class="flex items-center gap-3 pl-4 border-l border-white/15">

                        <div class="text-right">
                            <p class="text-white text-sm font-black leading-none tracking-wide">
                                {{ user.name }} {{ user.apellido }}
                            </p>
                            <p
                                class="text-[#D4A843] text-[9px] font-bold uppercase tracking-[0.15em] mt-0.5 opacity-80">
                                {{ user.rol }}
                            </p>
                        </div>

                        <div
                            class="w-10 h-10 rounded-full border-2 border-[#D4A843]/50 overflow-hidden bg-[#162554] flex items-center justify-center text-[#D4A843] font-black text-sm shrink-0 shadow-[0_0_0_3px_rgba(212,168,67,0.1)]">
                            <img v-if="user.foto_perfil" :src="`/storage/${user.foto_perfil}`"
                                class="w-full h-full object-cover">
                            <span v-else>{{ user.name.charAt(0) }}{{ user.apellido.charAt(0) }}</span>
                        </div>
                    </div>


                    <Link :href="route('logout')" method="post" as="button"
                        class="ml-1 px-3 py-2 bg-red-950/60 text-red-300 border border-red-500/25 rounded-md text-[10px] font-black uppercase tracking-widest hover:bg-red-900/70 hover:text-red-200 hover:border-red-400/40 transition-all cursor-pointer">
                        Salir
                    </Link>
                </div>


                <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="md:hidden text-white/70 hover:text-[#D4A843] p-2 rounded-lg bg-white/[0.07] hover:bg-white/10 transition-all border border-white/10">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path
                            :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path
                            :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </nav>


        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
            class="md:hidden bg-[#060E20] border-b-2 border-[#D4A843]/20 absolute w-full z-40 shadow-2xl">

            <!-- Links -->
            <div class="px-4 py-3 space-y-0.5">
                <Link :href="route('dashboard')"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                    :class="route().current('dashboard')
                        ? 'bg-[#D4A843]/12 text-[#D4A843]'
                        : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                    <span class="w-1 h-3 rounded-full"
                        :class="route().current('dashboard') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                    Inicio
                </Link>

                <template v-if="user.rol === 'admin'">
                    <Link :href="route('admin.importar.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                        :class="route().current('admin.importar.*')
                            ? 'bg-[#D4A843]/12 text-[#D4A843]'
                            : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                        <span class="w-1 h-3 rounded-full"
                            :class="route().current('admin.importar.*') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                        Importar Excel
                    </Link>
                    <Link :href="route('admin.historial.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                        :class="route().current('admin.historial.*')
                            ? 'bg-[#D4A843]/12 text-[#D4A843]'
                            : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                        <span class="w-1 h-3 rounded-full"
                            :class="route().current('admin.historial.*') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                        Gestión y Monitoreo
                    </Link>

                    <Link :href="route('admin.arbitros.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                        :class="route().current('admin.arbitros.*')
                            ? 'bg-[#D4A843]/12 text-[#D4A843]'
                            : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                        <span class="w-1 h-3 rounded-full"
                            :class="route().current('admin.arbitros.*') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                        Panel de árbitros
                    </Link>

                    <Link :href="route('admin.espectadores.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                        :class="route().current('admin.arbitros.*')
                            ? 'bg-[#D4A843]/12 text-[#D4A843]'
                            : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                        <span class="w-1 h-3 rounded-full"
                            :class="route().current('admin.arbitros.*') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                        Espectadores
                    </Link>
                </template>

                <Link :href="route('noticias.index')"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                    :class="route().current('noticias.*')
                        ? 'bg-[#D4A843]/12 text-[#D4A843]'
                        : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                    <span class="w-1 h-3 rounded-full"
                        :class="route().current('noticias.*') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                    Tablón de Noticias
                </Link>

                <Link :href="route('profile.edit')"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-black uppercase tracking-[0.15em] transition-colors"
                    :class="route().current('profile.edit')
                        ? 'bg-[#D4A843]/12 text-[#D4A843]'
                        : 'text-white/60 hover:bg-white/[0.06] hover:text-white'">
                    <span class="w-1 h-3 rounded-full"
                        :class="route().current('profile.edit') ? 'bg-[#D4A843]' : 'bg-white/20'"></span>
                    Perfil
                </Link>

            </div>


            <div class="px-4 py-5 border-t border-white/10 bg-[#030810]">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-[#D4A843]/50 overflow-hidden bg-[#162554] flex items-center justify-center text-[#D4A843] font-black text-base shrink-0">
                        <img v-if="user.foto_perfil" :src="`/storage/${user.foto_perfil}`"
                            class="w-full h-full object-cover">
                        <span v-else>{{ user.name.charAt(0) }}{{ user.apellido.charAt(0) }}</span>
                    </div>
                    <div>
                        <p class="text-white font-black text-base leading-none">{{ user.name }} {{ user.apellido }}</p>
                        <p class="text-[#D4A843] text-[10px] font-bold uppercase tracking-widest mt-1 opacity-80">{{
                            user.rol }}
                        </p>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full text-center px-4 py-3 bg-red-950/60 text-red-300 border border-red-500/25 rounded-lg text-base font-black uppercase tracking-widest cursor-pointer hover:bg-red-900/70 transition-colors">
                    Cerrar Sesión
                </Link>
            </div>
        </div>


        <main class=" mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <slot />
        </main>

    </div>
</template>

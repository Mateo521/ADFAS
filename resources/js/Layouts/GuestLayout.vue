<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet" />
    </Head>

    <div class="flex flex-col md:flex-row w-full min-h-screen bg-[#F7F5F0] font-['Lato',sans-serif]">

        <aside class="relative flex items-center justify-center w-full md:w-[420px] md:min-w-[420px] min-h-[220px] bg-[#0D1B3E] overflow-hidden p-8 md:p-0">
            
            <div class="absolute inset-0 pattern-bg"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-[#162554]/60 to-[#0D1B3E]/95"></div>

            <div class="absolute -bottom-8 -right-5 font-['Playfair_Display',serif]- text-[100px] md:text-[160px] font-extrabold text-[#D4A843]/5 tracking-tighter pointer-events-none select-none leading-none">
                ADFAS
            </div>

            <div class="relative z-10 flex flex-row md:flex-col items-center justify-center gap-4 md:gap-7 p-0 md:p-12 text-center flex-wrap">
                
                <div class="w-20 h-20 md:w-[110px] md:h-[110px] rounded-full border-[3px] border-[#D4A843] p-1.5 bg-white/5 shadow-[0_0_0_8px_rgba(212,168,67,0.1),0_8px_32px_rgba(0,0,0,0.4)] animate-logoAppear">
                    <img src="/images/logo-adfas.jpeg" alt="Logo ADFAS" class="w-full h-full object-cover rounded-full" />
                </div>

                <div class="flex flex-col items-center gap-2 md:gap-2.5 animate-fadeUp delay-200">
                    <p class="font-['Playfair_Display',serif]- text-3xl md:text-[42px] font-extrabold text-white tracking-[6px] leading-none">ADFAS</p>
                    <div class="hidden md:block w-[60px] h-[2px] bg-gradient-to-r from-transparent via-[#D4A843] to-transparent"></div>
                    <p class="hidden md:block text-[13px] font-bold text-white/70 tracking-[1.5px] uppercase leading-relaxed text-center">
                        Asociación de Árbitros<br>de Fútbol San Luis
                    </p>
                </div>

                <div class="hidden md:flex items-center gap-3 w-[200px] animate-fadeUp delay-300">
                    <span class="flex-1 h-[1px] bg-gradient-to-r from-transparent to-[#D4A843]/40"></span>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <rect x="1" y="1" width="16" height="16" rx="2" stroke="#D4A843" stroke-width="1.5"/>
                        <rect x="5" y="5" width="8" height="8" rx="1" fill="#D4A843" opacity="0.6"/>
                    </svg>
                    <span class="flex-1 h-[1px] bg-gradient-to-r from-[#D4A843]/40 to-transparent"></span>
                </div>

                <blockquote class="hidden md:block font-['Playfair_Display',serif]- italic text-base text-white/75 leading-relaxed animate-fadeUp delay-400">
                    <p>"Prueba de app<br>acá podria ir alguna frase."</p>
                </blockquote>

                <div class="hidden md:block mt-1 animate-fadeUp delay-500">
                    <span class="inline-block  py-1.5 border border-[#D4A843]/30 rounded-full text-base font-bold px-5 uppercase text-[#D4A843]/70">
                        San Luis, Argentina
                    </span>
                </div>
            </div>

            <div class="hidden md:block absolute top-0 right-0 bottom-0 w-1 bg-gradient-to-b from-transparent via-[#D4A843]/50 to-transparent"></div>
        </aside>

        <main class="flex-1 flex flex-col items-center justify-center relative bg-[#F7F5F0] px-5 py-12 md:p-12 min-h-screen">
            
            <div class="hidden md:block absolute top-6 right-6 w-20 h-20 border-t-2 border-r-2 border-[#D4A843]/30 pointer-events-none"></div>
            <div class="hidden md:block absolute bottom-16 left-6 w-20 h-20 border-b-2 border-l-2 border-[#D4A843]/30 pointer-events-none"></div>

            <div class="w-full max-w-[460px] animate-formAppear">
                <slot />
            </div>

            <footer class="absolute bottom-5 flex items-center gap-2.5 text-base text-[#6B7280] font-semibold tracking-wide">
                <span>© {{ new Date().getFullYear() }} ADFAS San Luis</span>
                <span class="text-[#A87C20]">·</span>
                
            </footer>
        </main>
    </div>
</template>

<style scoped>
 
.pattern-bg {
    background-image:
        radial-gradient(circle at 20% 20%, rgba(212,168,67,0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(212,168,67,0.06) 0%, transparent 50%),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23D4A843' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

 
.animate-logoAppear { animation: logoAppear 0.8s ease both; }
.animate-fadeUp { animation: fadeUp 0.8s ease both; }
.animate-formAppear { animation: formAppear 0.7s 0.1s ease both; }

 
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
.delay-500 { animation-delay: 0.5s; }

@keyframes logoAppear {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes formAppear {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
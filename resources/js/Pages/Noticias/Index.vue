<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    noticias: Object  
});

const colorTipo = (tipo) => {
    if (tipo === 'Urgente') return 'bg-red-50 text-red-700 border-red-200';
    if (tipo === 'Citación') return 'bg-[#D4A843]/10 text-[#A87C20] border-[#D4A843]/30';
    return 'bg-[#0D1B3E]/5 text-[#0D1B3E] border-[#0D1B3E]/20';
};

// ═════════════════════════════════════════════════════════
// NUEVO: Función para formatear texto estilo WhatsApp (Preview)
// ═════════════════════════════════════════════════════════
const formatearTextoWhatsApp = (texto) => {
    if (!texto) return '';
    let seguro = texto.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    seguro = seguro.replace(/\*\*(.*?)\*\*/g, '<strong class="font-black text-[#0D1B3E]">$1</strong>');
    seguro = seguro.replace(/\*(.*?)\*/g, '<strong class="font-black text-[#0D1B3E]">$1</strong>');
    seguro = seguro.replace(/_(.*?)_/g, '<em class="italic">$1</em>');
    return seguro;
};
</script>

<template>
    <Head title="Tablón de Noticias - ADFAS" />

    <AuthenticatedLayout>
        
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="text-base font-black tracking-[3px] uppercase text-[#A87C20] mb-2">Comunicados Oficiales</p>
                <h1 class="- text-[32px] md:text-[40px] font-extrabold text-[#0D1B3E] leading-[1.1] mb-3">
                    Tablón de Noticias
                </h1>
                <div class="flex items-center gap-2.5">
                    <span class="w-16 h-px bg-gradient-to-r from-[#D4A843] to-transparent"></span>
                    <span class="w-1.5 h-1.5 bg-[#D4A843] rotate-45 shrink-0"></span>
                </div>
            </div>
            
            <Link v-if="$page.props.auth.user.rol === 'admin'" :href="route('noticias.create')" 
                  class="group relative overflow-hidden px-7 py-3.5 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[12px] font-black uppercase tracking-[2px] rounded-lg transition-all duration-300 shadow-[0_2px_8px_rgba(212,168,67,0.3)] hover:bg-[100%_0] hover:-translate-y-[1px] hover:shadow-[0_8px_20px_rgba(168,124,32,0.35)] shrink-0 text-center">
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <span class="relative z-10 flex items-center justify-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 1v12M1 7h12"/></svg>
                    Redactar Noticia
                </span>
            </Link>
        </div>

        <div class="space-y-6 max-w-7xl pb-10">
            
            <div v-if="noticias.data.length === 0" class="bg-white border border-[#E5E7EB] rounded p-16 text-center shadow-sm relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-[#F9FAFB] to-white pointer-events-none"></div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-20 h-20 bg-[#0D1B3E] rounded-full flex items-center justify-center mb-5 border-[3px] border-[#D4A843]/30 shadow-inner">
                        <svg class="w-8 h-8 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2-0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    <h2 class="- text-[22px] font-extrabold text-[#0D1B3E] mb-2 uppercase tracking-wide">Sin comunicados recientes</h2>
                    <p class="text-[14px] text-[#6B7280] font-medium">El tablón de anuncios oficial se encuentra vacío en este momento.</p>
                </div>
            </div>

            <Link v-for="noticia in noticias.data" :key="noticia.id" :href="route('noticias.show', noticia.id)" 
                  class="group flex flex-col md:flex-row bg-white rounded overflow-hidden border border-[#E5E7EB] hover:border-[#D4A843]/50 hover:shadow-[0_12px_30px_rgba(13,27,62,0.06)] transition-all duration-300 hover:-translate-y-1">
                
                <div v-if="noticia.imagen_ruta" class="w-full md:w-[280px] h-48 md:h-auto shrink-0 overflow-hidden relative border-r border-[#E5E7EB]">
                    <div class="absolute inset-0 bg-[#0D1B3E]/10 group-hover:bg-transparent transition-colors duration-500 z-10"></div>
                    <img :src="`/storage/${noticia.imagen_ruta}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                </div>
                
                <div v-else class="w-full md:w-[280px] h-48 md:h-auto bg-[#0D1B3E] shrink-0 relative overflow-hidden flex items-center justify-center border-r border-[#E5E7EB]">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23D4A843\' fill-opacity=\'0.05\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E')] opacity-50"></div>
                    <div class="w-20 h-20 rounded-full border border-[#D4A843]/20 flex items-center justify-center bg-white/5 backdrop-blur-sm">
                        <svg class="w-8 h-8 text-[#D4A843]/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                </div>

                <div class="flex-1 p-6 md:p-8 flex flex-col justify-center">
                    <div class="flex items-center justify-between mb-4">
                        <span :class="colorTipo(noticia.tipo)" class="px-3 py-1 text-[10px] font-black uppercase tracking-[0.15em] border rounded">
                            {{ noticia.tipo }}
                        </span>
                        <span class="text-[12px] font-bold text-[#9CA3AF] tracking-wide uppercase">
                            {{ new Date(noticia.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </span>
                    </div>
                    
                    <h2 class="- text-[24px] md:text-[28px] font-extrabold text-[#0D1B3E] mb-3 leading-tight group-hover:text-[#D4A843] transition-colors duration-300 line-clamp-2">
                        {{ noticia.titulo }}
                    </h2>
                    
                    <p class="text-[14px] text-[#6B7280] font-medium leading-relaxed line-clamp-2" v-html="formatearTextoWhatsApp(noticia.contenido)"></p>
                    
                    <div v-if="noticia.archivo_ruta" class="mt-5 inline-flex items-center gap-2 text-[#162554] bg-[#F7F5F0] px-4 py-2 rounded-lg border border-[#E5E7EB] text-base font-bold tracking-wide uppercase self-start group-hover:border-[#D4A843]/40 transition-colors">
                        <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Documento Adjunto
                    </div>
                </div>
            </Link>

            <div v-if="noticias.links.length > 3" class="mt-12 flex justify-center">
                <nav class="inline-flex gap-2 p-1.5 bg-white border border-[#E5E7EB] rounded-xl shadow-sm">
                    <template v-for="(link, key) in noticias.links" :key="key">
                        <div v-if="link.url === null" 
                             class="px-4 py-2 text-base font-black text-gray-300 uppercase tracking-widest cursor-not-allowed"
                             v-html="link.label">
                        </div>
                        
                        <Link v-else 
                              :href="link.url" 
                              class="px-4 py-2 text-base font-black uppercase tracking-widest rounded-lg transition-all duration-200"
                              :class="link.active 
                                ? 'bg-[#0D1B3E] text-[#D4A843] shadow-md' 
                                : 'text-[#6B7280] hover:bg-[#F7F5F0] hover:text-[#0D1B3E]'"
                              v-html="link.label">
                        </Link>
                    </template>
                </nav>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
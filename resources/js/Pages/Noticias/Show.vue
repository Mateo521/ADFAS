<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    noticia: Object
});

const user = usePage().props.auth.user;

const colorTipo = (tipo) => {
    if (tipo === 'Urgente') return 'bg-red-50 text-red-700 border-red-200';
    if (tipo === 'Citación') return 'bg-[#D4A843]/10 text-[#A87C20] border-[#D4A843]/30';
    return 'bg-[#0D1B3E]/5 text-[#0D1B3E] border-[#0D1B3E]/20';
};

// ═════════════════════════════════════════════════════════
// NUEVO: Función para formatear texto estilo WhatsApp
// ═════════════════════════════════════════════════════════
const formatearTextoWhatsApp = (texto) => {
    if (!texto) return '';
    
    // 1. Escapamos etiquetas HTML por seguridad (evitar XSS)
    let seguro = texto.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    
    // 2. Reemplazamos **texto** por negrita (Markdown estándar)
    seguro = seguro.replace(/\*\*(.*?)\*\*/g, '<strong class="font-black text-[#0D1B3E]">$1</strong>');
    
    // 3. Reemplazamos *texto* por negrita (Estilo WhatsApp)
    seguro = seguro.replace(/\*(.*?)\*/g, '<strong class="font-black text-[#0D1B3E]">$1</strong>');
    
    // 4. Reemplazamos _texto_ por cursiva (Estilo WhatsApp)
    seguro = seguro.replace(/_(.*?)_/g, '<em class="italic">$1</em>');

    return seguro;
};

const confirmarEliminacion = () => {
    Swal.fire({
        title: '¿Eliminar Comunicado?',
        text: "Esta acción no se puede deshacer y borrará los archivos adjuntos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#B91C1C',
        cancelButtonColor: '#0D1B3E',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'font-["Lato",sans-serif] rounded-xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('noticias.destroy', props.noticia.id), {
                onSuccess: () => {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: 'Comunicado eliminado', showConfirmButton: false, timer: 3000
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head :title="noticia.titulo + ' - ADFAS'" />

    <AuthenticatedLayout>
        
        <div class="max-w-4xl mx-auto pb-12 font-['Lato',sans-serif]">
            
            <div class="mb-6">
                <Link :href="route('noticias.index')" class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6B7280] hover:text-[#D4A843] uppercase tracking-widest transition-colors group">
                    <div class="p-1.5 rounded-full bg-white border border-[#E5E7EB] group-hover:border-[#D4A843] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </div>
                    Volver al Tablón
                </Link>
            </div>

            <article class="bg-white border border-[#E5E7EB] rounded overflow-hidden shadow-[0_8px_30px_rgba(13,27,62,0.04)]">
                
                <div v-if="noticia.imagen_ruta" class="w-full h-[300px] md:h-[450px] relative">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent z-10"></div>
                    <img :src="`/storage/${noticia.imagen_ruta}`" class="w-full h-full object-cover">
                </div>
                <div v-else class="w-full h-32 bg-[#0D1B3E] relative overflow-hidden flex items-center justify-center border-b border-[#D4A843]/30">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23D4A843\' fill-opacity=\'0.05\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E')] opacity-50"></div>
                    <div class="absolute top-0 right-0 bottom-0 w-32 bg-gradient-to-l from-[#D4A843]/10 to-transparent"></div>
                </div>

                <div class="p-8 md:p-14">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E7EB] pb-6 mb-8">
                        <div>
                            <span :class="colorTipo(noticia.tipo)" class="inline-block px-3 py-1 text-[11px] font-black uppercase tracking-[0.15em] border rounded mb-3">
                                {{ noticia.tipo }}
                            </span>
                            <div class="flex items-center gap-2 text-[13px] font-semibold text-[#6B7280]">
                                <svg class="w-4 h-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Emitido por: <span class="text-[#0D1B3E] font-black uppercase tracking-wider">{{ noticia.autor.name }} {{ noticia.autor.apellido }}</span>
                            </div>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-[12px] font-bold text-[#9CA3AF] uppercase tracking-widest mb-1">Fecha de Publicación</p>
                            <p class="text-[16px] font-black text-[#0D1B3E]">{{ new Date(noticia.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase() }}</p>
                        </div>
                    </div>

                    <h1 class="font-['Playfair_Display',serif]- text-[36px] md:text-[48px] font-extrabold text-[#0D1B3E] leading-[1.15] mb-6">
                        {{ noticia.titulo }}
                    </h1>
                    <div class="flex items-center gap-3 mb-10">
                        <span class="w-16 h-[2px] bg-[#D4A843]"></span>
                        <span class="w-2 h-2 bg-[#D4A843] rotate-45"></span>
                        <span class="flex-1 h-px bg-gradient-to-r from-[#D4A843]/50 to-transparent"></span>
                    </div>

                    <div class="text-[16px] md:text-[18px] text-[#374151] font-medium leading-[1.8] whitespace-pre-wrap mb-14 text-justify" v-html="formatearTextoWhatsApp(noticia.contenido)"></div>

                    <div v-if="noticia.archivo_ruta" class="bg-[#0D1B3E] rounded-xl p-8 md:p-10 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8 border border-[#D4A843]/30 shadow-lg mb-10">
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#D4A843]/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="relative z-10 flex-1 text-center md:text-left">
                            <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                                <svg class="w-5 h-5 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <h3 class="text-[14px] font-black text-[#D4A843] uppercase tracking-[0.2em]">Material Adjunto</h3>
                            </div>
                            <p class="text-[15px] text-white/80 font-medium leading-relaxed">Este comunicado incluye un documento oficial.</p>
                        </div>
                        <div class="relative z-10 w-full md:w-auto shrink-0">
                            <a :href="`/storage/${noticia.archivo_ruta}`" target="_blank" download class="group relative overflow-hidden flex items-center justify-center gap-3 w-full px-8 py-4 bg-gradient-to-r from-[#C9920E] via-[#D4A843] to-[#C9920E] bg-[length:200%_100%] text-[#0D1B3E] text-[13px] font-black uppercase tracking-[2px] rounded-lg transition-all shadow-[0_2px_12px_rgba(212,168,67,0.25)] hover:bg-[100%_0] hover:-translate-y-[1px]">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <span class="relative z-10 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Descargar Archivo
                                </span>
                            </a>
                        </div>
                    </div>

                    <div v-if="user.rol === 'admin'" class="bg-[#F9FAFB] border border-[#E5E7EB] rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#0D1B3E] text-[#D4A843] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-black text-[#0D1B3E] uppercase tracking-widest">Gestión de Contenido</p>
                                <p class="text-[11px] text-[#6B7280] font-medium">Solo visible para administradores.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <Link :href="route('noticias.edit', noticia.id)" class="flex-1 sm:flex-none text-center px-6 py-2.5 bg-white border border-[#D1D5DB] text-[#0D1B3E] text-[11px] font-black uppercase tracking-widest rounded-lg hover:border-[#0D1B3E] hover:bg-gray-50 transition-colors">
                                Editar
                            </Link>
                            <button @click="confirmarEliminacion" class="flex-1 sm:flex-none text-center px-6 py-2.5 bg-red-50 border border-red-200 text-red-600 text-[11px] font-black uppercase tracking-widest rounded-lg hover:bg-red-100 transition-colors">
                                Eliminar
                            </button>
                        </div>
                    </div>

                </div>
            </article>
        </div>
    </AuthenticatedLayout>
</template>
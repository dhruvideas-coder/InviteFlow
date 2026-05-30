<template>
    <Teleport to="body">
        <Transition name="slide-up">
            <div v-if="modelValue" class="fixed inset-0 z-50 flex flex-col bg-slate-50 overflow-hidden">

                <!-- ── Header ──────────────────────────────────────────────── -->
                <header class="shrink-0 bg-white border-b border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3 h-14 sm:h-16 px-4 sm:px-6 max-w-7xl mx-auto w-full">
                        <button @click="close()" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </button>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-base sm:text-lg font-bold text-gray-900 truncate">{{ lang.t('recipients') }}</h1>
                            <p class="text-xs text-gray-400 truncate">{{ document?.name }}</p>
                        </div>
                        <span class="hidden sm:flex items-center gap-1.5 text-xs font-semibold bg-primary-50 text-primary-700 px-3 py-1.5 rounded-xl border border-primary-100">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ pagination.total }} {{ lang.t('recipients').toLowerCase() }}
                        </span>
                    </div>
                </header>

                <!-- ── Scrollable body ─────────────────────────────────────── -->
                <div class="flex-1 overflow-y-auto min-h-0">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6 space-y-5">

                        <!-- Action row -->
                        <div class="flex flex-row items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-xl font-bold text-gray-900 truncate">{{ lang.t('all_recipients') }}</h2>
                                <p class="text-sm text-gray-500 truncate">{{ document?.name }}</p>
                            </div>
                            <button @click="openSelectModal()" class="btn btn-primary shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                {{ lang.t('select_recipients') }}
                            </button>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div class="stat-card flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xl font-bold text-gray-900">{{ pagination.total }}</p>
                                    <p class="text-xs text-gray-500">{{ lang.t('total_recipients') }}</p>
                                </div>
                            </div>
                            <div class="stat-card flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xl font-bold text-gray-900">{{ globalStats.total_sent }}</p>
                                    <p class="text-xs text-gray-500">{{ lang.t('links_sent') }}</p>
                                </div>
                            </div>
                            <div class="stat-card flex items-center gap-3 col-span-2 sm:col-span-1">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xl font-bold text-gray-900">{{ globalStats.total_pending }}</p>
                                    <p class="text-xs text-gray-500">{{ lang.t('pending') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input v-model="search" type="text" class="input pl-10" :placeholder="lang.t('search_recipients')" @input="onSearchInput" />
                            </div>
                            <div class="flex gap-3 sm:contents">
                                <select v-model="villageFilter" class="select flex-1 sm:flex-none sm:w-44" @change="fetchRecipients(1)">
                                    <option value="">{{ lang.t('all_villages') }}</option>
                                    <option v-for="v in villages" :key="v" :value="v">{{ v }}</option>
                                </select>
                                <select v-model="perPage" class="select w-28 shrink-0 sm:shrink" @change="fetchRecipients(1)">
                                    <option :value="10">10 / Page</option>
                                    <option :value="25">25 / Page</option>
                                    <option :value="50">50 / Page</option>
                                    <option :value="100">100 / Page</option>
                                </select>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-wrap">
                            <div class="overflow-x-auto">
                                <table class="table min-w-[400px] sm:min-w-[580px]">
                                    <thead>
                                        <tr>
                                            <th>{{ lang.t('name') }}</th>
                                            <th v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell">Name (Gujarati)</th>
                                            <th>{{ lang.t('phone') }}</th>
                                            <th>{{ lang.t('village') }}</th>
                                            <th v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell">Village (Gujarati)</th>
                                            <th>{{ lang.t('status') }}</th>
                                            <th>{{ lang.t('selected_by') }}</th>
                                            <th class="text-right">{{ lang.t('actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="loading">
                                        <tr v-for="i in 5" :key="i">
                                            <td colspan="99" class="py-5">
                                                <div class="animate-pulse flex items-center gap-4 px-4">
                                                    <div class="w-8 h-8 rounded-full bg-gray-100 shrink-0"></div>
                                                    <div class="h-4 bg-gray-100 rounded flex-1"></div>
                                                    <div class="h-4 bg-gray-100 rounded w-24 hidden sm:block"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr v-for="r in recipients" :key="r.id">
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                                        {{ (lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en)?.[0] ?? '?' }}
                                                    </div>
                                                    <span class="font-medium text-gray-900">{{ lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en }}</span>
                                                </div>
                                            </td>
                                            <td v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell text-gray-700" style="font-family:serif">{{ r.name_gu }}</td>
                                            <td class="text-gray-600 font-mono text-xs">{{ formatMobile(r.mobile) }}</td>
                                            <td><span class="tag">{{ lang.currentLocale === 'gu' ? r.village_gu || r.village_en : r.village_en }}</span></td>
                                            <td v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell text-gray-700" style="font-family: serif;">{{ r.village_gu }}</td>
                                            <td>
                                                <span :class="['badge text-xs', r.sent ? 'badge-green' : 'badge-gray']">
                                                    {{ r.sent ? lang.t('sent') : lang.t('pending') }}
                                                </span>
                                            </td>
                                            <td>
                                                <template v-if="getSelector(r)">
                                                    <div class="flex flex-col gap-1 min-w-[90px]">
                                                        <span class="text-xs font-medium text-gray-800 truncate max-w-[120px]">{{ getSelector(r).name }}</span>
                                                        <span :class="['badge text-[9px] self-start', roleBadgeClass(getSelector(r).role)]">
                                                            {{ lang.t(getSelector(r).role) }}
                                                        </span>
                                                    </div>
                                                </template>
                                                <span v-else class="text-gray-300 text-xs">—</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1 justify-end">
                                                    <button @click="sendWhatsApp(r)" :disabled="sendingIds.has(r.id)" class="btn btn-ghost btn-sm text-green-600 hover:text-green-700 hover:bg-green-50 disabled:opacity-50" title="Send WhatsApp">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                                    </button>
                                                    <button @click="openDocPreviewModal(r)" class="btn btn-ghost btn-sm text-violet-500 hover:text-violet-600 hover:bg-violet-50" title="Preview Document">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </button>
                                                    <button @click="openLinksModal(r)" class="btn btn-ghost btn-sm text-primary-600 hover:text-primary-700 hover:bg-primary-50" title="View Links">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="!loading && !recipients.length">
                                            <td colspan="99" class="text-center py-20">
                                                <div class="flex flex-col items-center gap-2">
                                                    <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mb-2">
                                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    </div>
                                                    <h3 class="font-semibold text-gray-800">{{ lang.t('no_recipients_for_doc') }}</h3>
                                                    <p class="text-sm text-gray-400">{{ lang.t('select_recipients_hint') }}</p>
                                                    <button @click="openSelectModal()" class="btn btn-primary mt-3 btn-sm">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                        {{ lang.t('select_recipients') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 py-3 bg-white border-t border-gray-100">
                                <span class="text-xs text-gray-500">
                                    Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} recipients
                                </span>
                                <div v-if="pagination.last_page > 1" class="flex items-center gap-1">
                                    <button @click="fetchRecipients(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <div class="flex items-center gap-1 mx-2">
                                        <button v-for="p in visiblePages" :key="p" @click="p !== '...' && fetchRecipients(p)"
                                            class="w-8 h-8 rounded-lg text-xs font-bold transition-all"
                                            :class="[ p === pagination.current_page ? 'bg-primary-600 text-white shadow-md shadow-primary-200' : p === '...' ? 'cursor-default text-gray-400' : 'hover:bg-gray-100 text-gray-600' ]"
                                        >{{ p }}</button>
                                    </div>
                                    <button @click="fetchRecipients(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ══ Select Recipients Modal ═══════════════════════════════ -->
                <Teleport to="body">
                    <Transition name="modal">
                        <div v-if="showSelectModal" class="modal-overlay" style="z-index:60" @click.self="showSelectModal = false">
                            <div class="modal max-w-2xl w-[95%] p-0 overflow-hidden flex flex-col max-h-[90vh]">

                                <!-- Header -->
                                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900">{{ lang.t('select_recipients') }}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ document?.name }}</p>
                                    </div>
                                    <button @click="showSelectModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Search -->
                                <div class="px-5 py-3 border-b border-gray-100 shrink-0 bg-gray-50">
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <input v-model="selectSearch" type="text" class="input pl-9 h-9 text-sm" :placeholder="lang.t('search_recipients')" />
                                    </div>
                                </div>

                                <!-- Selection info bar -->
                                <div v-if="selectedForDoc.length" class="px-5 py-2 bg-primary-50 border-b border-primary-100 shrink-0 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-primary-700">
                                        {{ selectedForDoc.length }} {{ lang.t('selected') }}
                                    </span>
                                    <button @click="selectedForDoc = []" class="text-xs text-primary-600 hover:text-primary-800 font-medium">
                                        {{ lang.t('clear') }}
                                    </button>
                                </div>

                                <!-- List -->
                                <div class="flex-1 overflow-y-auto min-h-0">
                                    <!-- Loading skeleton -->
                                    <div v-if="loadingSelect" class="p-6 space-y-3">
                                        <div v-for="i in 6" :key="i" class="animate-pulse flex items-center gap-3 px-2">
                                            <div class="w-5 h-5 rounded bg-gray-100 shrink-0"></div>
                                            <div class="w-9 h-9 rounded-full bg-gray-100 shrink-0"></div>
                                            <div class="flex-1 space-y-1.5">
                                                <div class="h-3.5 bg-gray-100 rounded w-2/3"></div>
                                                <div class="h-3 bg-gray-100 rounded w-1/3"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty -->
                                    <div v-else-if="!filteredSelectRecipients.length" class="py-16 text-center text-sm text-gray-400">
                                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ selectSearch ? 'No recipients match your search.' : lang.t('no_recipients_found') }}
                                    </div>

                                    <!-- Recipient rows -->
                                    <div v-else class="divide-y divide-gray-50">
                                        <label
                                            v-for="r in filteredSelectRecipients" :key="r.id"
                                            :class="[
                                                'flex items-center gap-3 px-5 py-3 transition-colors select-none',
                                                alreadyAddedIds.has(r.id)
                                                    ? 'opacity-60 cursor-default bg-gray-50/50'
                                                    : 'cursor-pointer hover:bg-gray-50 active:bg-gray-100'
                                            ]"
                                        >
                                            <input
                                                type="checkbox"
                                                class="w-4 h-4 rounded text-primary-600 shrink-0"
                                                :checked="alreadyAddedIds.has(r.id) || selectedForDoc.includes(r.id)"
                                                :disabled="alreadyAddedIds.has(r.id)"
                                                @change="toggleSelectRecipient(r.id)"
                                            />
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                                {{ (r.name_en || '?')[0].toUpperCase() }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">
                                                    {{ lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en }}
                                                </p>
                                                <p class="text-xs text-gray-500 truncate mt-0.5">
                                                    {{ formatMobile(r.mobile) }}
                                                    <span v-if="r.village_en || r.village_gu" class="ml-1.5">· {{ lang.currentLocale === 'gu' ? r.village_gu || r.village_en : r.village_en }}</span>
                                                </p>
                                            </div>
                                            <span v-if="alreadyAddedIds.has(r.id)" class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                {{ lang.t('in_document') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between gap-3 shrink-0">
                                    <p class="text-xs text-gray-500 truncate">
                                        <template v-if="selectedForDoc.length">
                                            <span class="font-semibold text-gray-700">{{ selectedForDoc.length }}</span> {{ lang.t('recipients').toLowerCase() }} {{ lang.t('selected').toLowerCase() }}
                                        </template>
                                        <template v-else>
                                            {{ filteredSelectRecipients.filter(r => !alreadyAddedIds.has(r.id)).length }} {{ lang.t('available').toLowerCase() }}
                                        </template>
                                    </p>
                                    <div class="flex gap-2 shrink-0">
                                        <button @click="showSelectModal = false" class="btn btn-secondary btn-sm">{{ lang.t('cancel') }}</button>
                                        <button @click="addSelectedToDoc()" :disabled="!selectedForDoc.length || addingToDoc" class="btn btn-primary btn-sm">
                                            <svg v-if="addingToDoc" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            {{ addingToDoc ? lang.t('loading') + '...' : lang.t('add_to_document', { count: selectedForDoc.length }) }}
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </Transition>
                </Teleport>

                <!-- ══ Links Modal ═══════════════════════════════════════════ -->
                <Teleport to="body">
                    <Transition name="modal">
                        <div v-if="showLinksModal" class="modal-overlay" style="z-index:60" @click.self="showLinksModal = false">
                            <div class="modal max-w-lg w-[95%] p-0 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ lang.t('personalized_links') }}</h3>
                                            <p class="text-xs text-gray-500">{{ selectedRecipient?.name_en }} • {{ formatMobile(selectedRecipient?.mobile) }}</p>
                                        </div>
                                    </div>
                                    <button @click="showLinksModal = false" class="p-2 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="p-6">
                                    <div v-if="!selectedRecipient?.invitation_links?.length" class="text-center py-8">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ lang.t('no_links_generated') }}</p>
                                        <button @click="showLinksModal = false; sendWhatsApp(selectedRecipient)" class="btn btn-primary btn-sm mt-4">{{ lang.t('generate_first_link') }}</button>
                                    </div>
                                    <div v-else class="space-y-3">
                                        <div v-for="link in selectedRecipient.invitation_links" :key="link.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-primary-200 transition-colors">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ link.document?.name || lang.t('unknown_document') }}</p>
                                                <p class="text-[10px] text-gray-500 mt-0.5">{{ lang.t('created_on', { date: formatDate(link.created_at) }) }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button @click="copyLink(link.token)" class="btn btn-secondary btn-sm h-9 px-3 gap-1.5" :class="copiedToken === link.token ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ''">
                                                    <svg v-if="copiedToken === link.token" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    <span class="text-xs">{{ copiedToken === link.token ? lang.t('copied') : lang.t('copy') }}</span>
                                                </button>
                                                <a :href="`/doc/view/${link.token}`" target="_blank" class="btn btn-primary btn-sm h-9 px-3 gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    <span class="text-xs">{{ lang.t('view') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                                    <button @click="showLinksModal = false" class="btn btn-secondary btn-sm">{{ lang.t('close') }}</button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </Teleport>

                <!-- ══ Doc Preview Modal ═════════════════════════════════════ -->
                <Teleport to="body">
                    <Transition name="modal">
                        <div v-if="showDocPreviewModal" class="modal-overlay" style="z-index:60" @click.self="showDocPreviewModal = false">
                            <div class="modal max-w-3xl w-[96%] max-h-[95vh] flex flex-col p-0 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">Document Preview</h3>
                                            <p class="text-xs text-gray-500">{{ lang.currentLocale === 'gu' ? selectedRecipient?.name_gu || selectedRecipient?.name_en : selectedRecipient?.name_en }}</p>
                                        </div>
                                    </div>
                                    <button @click="showDocPreviewModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                                    <div v-if="previewDoc" class="space-y-4">
                                        <div ref="previewDocContainerRef" class="w-full max-w-[600px] mx-auto">
                                            <div class="relative overflow-hidden bg-white shadow-xl border border-gray-200 rounded-xl mx-auto" :style="{ width: previewDocW + 'px', height: previewDocH + 'px' }">
                                                <img v-if="isImage(previewDoc)" :src="previewDoc.file_url" class="absolute inset-0 w-full h-full object-contain pointer-events-none" @load="onPreviewDocImageLoad"/>
                                                <PdfCanvas v-else :src="previewDoc.file_url" :width="previewDocW" :page="previewDocPage" class="pointer-events-none" @rendered="onPreviewDocPdfRendered" @total-pages="n => previewDocTotalPages = n"/>
                                                <div v-for="field in previewDocFilteredFields" :key="field.id" class="absolute flex items-center justify-center text-center pointer-events-none"
                                                    :style="{ left:(field.x_percent/100*previewDocW)+'px', top:(field.y_percent/100*previewDocH)+'px', width:(field.width_percent/100*previewDocW)+'px', height:(field.height_percent/100*previewDocH)+'px', border:`1px dashed ${field.color||'#3b82f6'}60`, backgroundColor:(field.color||'#3b82f6')+'10' }">
                                                    <span class="font-bold px-1 truncate w-full text-center" :style="{ fontSize:Math.max(8,(field.height_percent/100*previewDocH)*0.55)+'px', fontFamily:previewDoc.language==='en'?'sans-serif':'serif', color:field.color||'#111827' }">{{ getPreviewDocFieldValue(field) }}</span>
                                                </div>
                                                <div class="absolute top-3 right-3 z-10">
                                                    <div class="bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full border border-white/50 text-[10px] font-bold text-violet-600 shadow-sm flex items-center gap-1.5">
                                                        <div class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></div>
                                                        Preview
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="!isImage(previewDoc) && previewDocTotalPages > 1" class="flex items-center justify-center gap-3">
                                            <button @click="previewDocPage > 1 && previewDocPage--" :disabled="previewDocPage === 1" class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-30 text-sm font-medium text-gray-700 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                                Previous
                                            </button>
                                            <span class="text-sm font-bold text-gray-700 bg-gray-100 px-5 py-2 rounded-xl min-w-[110px] text-center">Page {{ previewDocPage }} / {{ previewDocTotalPages }}</span>
                                            <button @click="previewDocPage < previewDocTotalPages && previewDocPage++" :disabled="previewDocPage === previewDocTotalPages" class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-30 text-sm font-medium text-gray-700 shadow-sm">
                                                Next
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
                                    <p class="text-xs text-gray-500 truncate max-w-[60%]"><span v-if="previewDoc">{{ previewDoc.name }}</span></p>
                                    <button @click="showDocPreviewModal = false" class="btn btn-secondary btn-sm">{{ lang.t('close') }}</button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </Teleport>

            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import PdfCanvas from '@/components/PdfCanvas.vue';
import { useLanguageStore } from '@/stores/language';
import { formatMobile } from '@/utils/format';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    document:   { type: Object, default: null },
});
const emit = defineEmits(['update:modelValue']);

const lang = useLanguageStore();

// ── Document-filtered recipients list ─────────────────────────────────
const recipients    = ref([]);
const loading       = ref(false);
const search        = ref('');
const villageFilter = ref('');
const perPage       = ref(10);
const globalStats   = ref({ total_sent: 0, total_pending: 0 });
const pagination    = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });

const villages = computed(() => [...new Set(recipients.value.map(r => r.village_en).filter(Boolean))].sort());

const visiblePages = computed(() => {
    const total = pagination.value.last_page, cur = pagination.value.current_page;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    if (cur <= 4)         return [1, 2, 3, 4, 5, '...', total];
    if (cur >= total - 3) return [1, '...', total-4, total-3, total-2, total-1, total];
    return [1, '...', cur-1, cur, cur+1, '...', total];
});

async function fetchRecipients(page = 1) {
    if (!props.document) return;
    loading.value = true;
    try {
        const { data } = await axios.get('/api/recipients', {
            params: { page, per_page: perPage.value, document_id: props.document.id, search: search.value || undefined, village: villageFilter.value || undefined },
        });
        recipients.value  = data.data;
        globalStats.value = data.stats || { total_sent: 0, total_pending: 0 };
        pagination.value  = { current_page: data.current_page, last_page: data.last_page, total: data.total, from: data.from, to: data.to };
    } finally {
        loading.value = false;
    }
}

let searchTimer = null;
function onSearchInput() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchRecipients(1), 500); }

// ── Docs list (WhatsApp / preview selectors) ──────────────────────────
const docs        = ref([]);
const loadingDocs = ref(false);

async function fetchDocs() {
    if (docs.value.length > 1) return;
    loadingDocs.value = true;
    try {
        const { data } = await axios.get('/api/documents', { params: { per_page: 100 } });
        docs.value = data.data || data;
    } finally {
        loadingDocs.value = false;
    }
}

// ── Select Recipients Modal ────────────────────────────────────────────
const showSelectModal  = ref(false);
const allRecipients    = ref([]);
const loadingSelect    = ref(false);
const selectSearch     = ref('');
const selectedForDoc   = ref([]);
const addingToDoc      = ref(false);
const alreadyAddedIds  = ref(new Set());

const filteredSelectRecipients = computed(() => {
    const q = selectSearch.value.trim().toLowerCase();
    if (!q) return allRecipients.value;
    return allRecipients.value.filter(r =>
        (r.name_en || '').toLowerCase().includes(q) ||
        (r.name_gu || '').toLowerCase().includes(q) ||
        (r.mobile  || '').includes(q) ||
        (r.village_en || '').toLowerCase().includes(q)
    );
});

async function openSelectModal() {
    selectedForDoc.value  = [];
    selectSearch.value    = '';
    showSelectModal.value = true;
    loadingSelect.value   = true;
    try {
        const [allRes, docRes] = await Promise.all([
            axios.get('/api/recipients', { params: { per_page: 500 } }),
            axios.get('/api/recipients', { params: { per_page: 500, document_id: props.document?.id } }),
        ]);
        allRecipients.value   = allRes.data.data  || allRes.data;
        const linked          = (docRes.data.data || docRes.data).map(r => r.id);
        alreadyAddedIds.value = new Set(linked);
    } finally {
        loadingSelect.value = false;
    }
}

function toggleSelectRecipient(id) {
    if (alreadyAddedIds.value.has(id)) return;
    const idx = selectedForDoc.value.indexOf(id);
    if (idx === -1) selectedForDoc.value.push(id);
    else            selectedForDoc.value.splice(idx, 1);
}

async function addSelectedToDoc() {
    if (!selectedForDoc.value.length || !props.document) return;
    addingToDoc.value = true;
    try {
        await Promise.allSettled(
            selectedForDoc.value.map(rid =>
                axios.post('/api/invitation-links', { recipient_id: rid, document_id: props.document.id, via: 'link' })
            )
        );
        selectedForDoc.value.forEach(id => alreadyAddedIds.value.add(id));
        selectedForDoc.value  = [];
        await fetchRecipients(1);
        showSelectModal.value = false;
    } finally {
        addingToDoc.value = false;
    }
}

// ── WhatsApp direct send ───────────────────────────────────────────────
const selectedRecipient = ref(null);
const sendingIds        = ref(new Set());
const copiedToken       = ref(null);

async function sendWhatsApp(recipient) {
    if (!props.document || sendingIds.value.has(recipient.id)) return;
    sendingIds.value = new Set([...sendingIds.value, recipient.id]);
    try {
        const { data } = await axios.post('/api/invitation-links', {
            recipient_id: recipient.id,
            document_id:  props.document.id,
            via:          'WhatsApp',
        });
        const doc  = props.document;
        const name = doc.language === 'gu' ? recipient.name_gu || recipient.name_en : recipient.name_en;
        const msg  = encodeURIComponent(`Hello ${name},\n\nYou are cordially invited: *${doc.name}*.\n\n${window.location.origin}/doc/view/${data.token}\n\nRegards,\nInviteFlow`);
        window.open(`https://wa.me/${recipient.mobile.replace(/\D/g, '')}?text=${msg}`, '_blank');
        const idx = recipients.value.findIndex(r => r.id === recipient.id);
        if (idx !== -1) {
            recipients.value[idx].sent = true;
            recipients.value[idx].invitation_links ??= [];
            recipients.value[idx].invitation_links.unshift({ id: data.id, token: data.token, created_at: data.created_at, document: doc });
        }
    } finally {
        const next = new Set(sendingIds.value);
        next.delete(recipient.id);
        sendingIds.value = next;
    }
}

// ── Links Modal ────────────────────────────────────────────────────────
const showLinksModal = ref(false);
function openLinksModal(recipient) { selectedRecipient.value = recipient; showLinksModal.value = true; }
function copyLink(token) {
    navigator.clipboard.writeText(`${window.location.origin}/doc/view/${token}`);
    copiedToken.value = token;
    setTimeout(() => { copiedToken.value = null; }, 2000);
}

// ── Doc Preview Modal ──────────────────────────────────────────────────
const showDocPreviewModal    = ref(false);
const previewDocSelectedId   = ref(null);
const previewDocPage         = ref(1);
const previewDocTotalPages   = ref(1);
const previewDocContainerRef = ref(null);
const previewDocW            = ref(500);
const previewDocAspectRatio  = ref(1.4142);
const previewDocH            = computed(() => Math.round(previewDocW.value * previewDocAspectRatio.value));
const previewDoc             = computed(() => docs.value.find(d => d.id === previewDocSelectedId.value));
const previewDocFilteredFields = computed(() => previewDoc.value?.fields?.filter(f => (f.page_number || 1) === previewDocPage.value) ?? []);

function getPreviewDocFieldValue(field) {
    if (!selectedRecipient.value || !previewDoc.value) return '';
    const l = previewDoc.value.language || 'en';
    return field.field_type === 'village'
        ? (l === 'gu' ? selectedRecipient.value.village_gu : selectedRecipient.value.village_en)
        : (l === 'gu' ? selectedRecipient.value.name_gu   : selectedRecipient.value.name_en);
}
function onPreviewDocPdfRendered({ width, height }) { previewDocAspectRatio.value = height / width; }
function onPreviewDocImageLoad(e) { const { naturalWidth: w, naturalHeight: h } = e.target; if (w && h) previewDocAspectRatio.value = h / w; }

function openDocPreviewModal(recipient) {
    selectedRecipient.value     = recipient;
    previewDocPage.value        = 1;
    previewDocTotalPages.value  = 1;
    previewDocAspectRatio.value = 1.4142;
    previewDocSelectedId.value  = props.document?.id ?? null;
    showDocPreviewModal.value   = true;
    fetchDocs();
}

watch(previewDocSelectedId, () => { previewDocPage.value = 1; previewDocTotalPages.value = 1; previewDocAspectRatio.value = 1.4142; });
watch(showDocPreviewModal, val => {
    if (!val) return;
    setTimeout(() => {
        const el = previewDocContainerRef.value;
        if (!el) return;
        previewDocW.value = el.clientWidth || 500;
        const ro = new ResizeObserver(([e]) => { previewDocW.value = e.contentRect.width || 500; });
        ro.observe(el);
    }, 100);
});

// ── Helpers ────────────────────────────────────────────────────────────
const gradients = ['bg-gradient-to-br from-pink-400 to-rose-500','bg-gradient-to-br from-primary-400 to-primary-600','bg-gradient-to-br from-amber-400 to-orange-500','bg-gradient-to-br from-emerald-400 to-teal-500','bg-gradient-to-br from-violet-400 to-purple-600'];
function docGradient(id) { return gradients[id % gradients.length]; }
function isImage(doc)    { return ['jpg','jpeg','png','webp','gif'].includes(doc?.file_type?.toLowerCase()); }
function formatDate(s)   { return s ? new Date(s).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : ''; }

function getSelector(recipient) {
    if (!props.document || !recipient.invitation_links?.length) return null;
    const link = recipient.invitation_links.find(l => l.document_id === props.document.id);
    return link?.created_by ?? null;
}

function roleBadgeClass(role) {
    if (role === 'super_admin') return 'badge-amber';
    if (role === 'admin')       return 'badge-primary';
    return 'badge-green';
}

// ── Open / Close ───────────────────────────────────────────────────────
function close() { search.value = ''; villageFilter.value = ''; emit('update:modelValue', false); }

watch(() => props.modelValue, val => {
    if (val && props.document) {
        recipients.value = [];
        pagination.value = { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
        fetchRecipients(1);
        if (!docs.value.find(d => d.id === props.document.id)) docs.value = [props.document, ...docs.value];
        previewDocSelectedId.value = props.document.id;
    }
});
</script>

<style scoped>
@reference "../../css/app.css";

.slide-up-enter-active { transition: opacity 0.25s ease, transform 0.3s ease; }
.slide-up-leave-active { transition: opacity 0.2s ease, transform 0.25s ease; }
.slide-up-enter-from   { opacity: 0; transform: translateY(24px); }
.slide-up-leave-to     { opacity: 0; transform: translateY(16px); }

.modal-enter-active, .modal-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>

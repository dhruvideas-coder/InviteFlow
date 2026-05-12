<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ lang.t('all_recipients') }}</h2>
                <p class="text-sm text-gray-500">Manage invitation recipients and their details</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="showCsvModal = true" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    {{ lang.t('import_recipients') }}
                </button>
                <button v-if="contactPickerSupported" @click="pickFromContacts" :disabled="pickingContacts" class="btn btn-secondary">
                    <svg v-if="pickingContacts" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ pickingContacts ? lang.t('opening') : lang.t('from_contacts') }}
                </button>
                <button @click="openAddModal" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ lang.t('add_recipient') }}
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="stat-card flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-gray-900">{{ recipients.length }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ lang.t('total_recipients') }}</p>
                </div>
            </div>
            <div class="stat-card flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-gray-900">{{ recipients.filter(r => r.sent).length }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ lang.t('links_sent') }}</p>
                </div>
            </div>
            <div class="stat-card flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-gray-900">{{ recipients.filter(r => !r.sent).length }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ lang.t('pending') }}</p>
                </div>
            </div>
        </div>

        <!-- Search & filter -->
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input v-model="search" type="text" class="input pl-10" :placeholder="lang.t('search_recipients')" />
            </div>
            <select v-model="villageFilter" class="select sm:w-44">
                <option value="">{{ lang.t('all_villages') }}</option>
                <option v-for="v in villages" :key="v" :value="v">{{ v }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="table min-w-[700px]">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="rounded" @change="toggleAll" />
                        </th>
                        <th>{{ lang.t('name') }}</th>
                        <th v-if="lang.currentLocale === 'en'">Name (Gujarati)</th>
                        <th>{{ lang.t('phone') }}</th>
                        <th>{{ lang.t('village') }}</th>
                        <th>{{ lang.t('status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in filteredRecipients" :key="r.id">
                        <td>
                            <input type="checkbox" class="rounded" v-model="selected" :value="r.id" />
                        </td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ (lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en)[0] }}
                                </div>
                                <span class="font-medium text-gray-900">{{ lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en }}</span>
                            </div>
                        </td>
                        <td v-if="lang.currentLocale === 'en'" class="text-gray-700" style="font-family: serif;">{{ r.name_gu }}</td>
                        <td class="text-gray-600">{{ r.mobile }}</td>
                        <td><span class="tag">{{ lang.currentLocale === 'gu' ? r.village_gu || r.village_en : r.village_en }}</span></td>
                        <td>
                            <span :class="['badge text-xs', r.sent ? 'badge-green' : 'badge-gray']">
                                {{ r.sent ? lang.t('sent') : lang.t('pending') }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1 justify-end">
                                <button 
                                    @click="openWhatsAppModal(r)"
                                    class="btn btn-ghost btn-sm text-green-600 hover:text-green-700 hover:bg-green-50" 
                                    title="Send WhatsApp"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </button>
                                <button 
                                    @click="openLinksModal(r)"
                                    class="btn btn-ghost btn-sm text-primary-600 hover:text-primary-700 hover:bg-primary-50" 
                                    title="View Links"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </button>
                                <button 
                                    @click="editRecipient(r)"
                                    class="btn btn-ghost btn-sm" title="Edit"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button 
                                    @click="deleteRecipient(r.id)"
                                    class="btn btn-ghost btn-sm text-red-400 hover:text-red-500 hover:bg-red-50" title="Delete"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Bulk actions -->
            <div v-if="selected.length" class="flex items-center gap-3 px-4 py-3 bg-primary-50 border-t border-primary-100">
                <span class="text-sm text-primary-700 font-medium">{{ selected.length }} {{ lang.t('selected') }}</span>
                <button class="btn btn-sm bg-green-500 text-white hover:bg-green-600">{{ lang.t('send_whatsapp_all') }}</button>
                <button class="btn btn-secondary btn-sm">{{ lang.t('generate_links') }}</button>
                <button @click="bulkDelete" class="btn btn-danger btn-sm ml-auto">{{ lang.t('delete_selected') }}</button>
            </div>
        </div>

        <!-- Add/Edit Recipient Modal -->
        <Teleport to="body">
            <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
                <div class="modal">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-gray-900">{{ isEditing ? lang.t('edit_recipient') : lang.t('add_recipient') }}</h3>
                        <button @click="showAddModal = false" class="btn btn-ghost btn-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="label">Name (English) <span class="text-red-400">*</span></label>
                            <input v-model="form.name_en" type="text" class="input" placeholder="e.g. Sureshbhai Patel" />
                        </div>
                        <div class="form-group">
                            <label class="label">Name (Gujarati)</label>
                            <div class="flex gap-2">
                                <input v-model="form.name_gu" type="text" class="input" placeholder="Auto-converted or manual" style="font-family: serif;" />
                                <button @click="autoConvert('name')" :disabled="autoConverting" class="btn btn-secondary btn-sm shrink-0 text-xs flex items-center gap-1">
                                    <svg v-if="autoConverting" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ autoConverting ? 'Converting…' : 'Auto Convert' }}
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="label">Mobile Number <span class="text-red-400">*</span></label>
                            <input v-model="form.mobile" type="tel" class="input" placeholder="+91 98765 43210" />
                        </div>
                        <div class="form-group">
                            <label class="label">Village (English)</label>
                            <input v-model="form.village_en" type="text" class="input" placeholder="e.g. Anand" />
                        </div>
                        <div class="form-group">
                            <label class="label">Village (Gujarati)</label>
                            <div class="flex gap-2">
                                <input v-model="form.village_gu" type="text" class="input" placeholder="e.g. આણંદ" style="font-family: serif;" />
                                <button @click="autoConvert('village')" :disabled="autoConvertingVillage" class="btn btn-secondary btn-sm shrink-0 text-xs flex items-center gap-1">
                                    <svg v-if="autoConvertingVillage" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ autoConvertingVillage ? 'Converting…' : 'Auto Convert' }}
                                </button>
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end mt-2">
                            <button @click="showAddModal = false" class="btn btn-secondary">{{ lang.t('cancel') }}</button>
                            <button @click="saveRecipient" class="btn btn-primary" :disabled="saving">
                                <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                {{ isEditing ? lang.t('update_recipient') : lang.t('add_recipient') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- WhatsApp Share Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showWhatsAppModal" class="modal-overlay" @click.self="showWhatsAppModal = false">
                    <div class="modal max-w-2xl w-[95%] max-h-[90vh] flex flex-col p-0 overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Send Invitation</h3>
                                    <p class="text-xs text-gray-500">To: {{ selectedRecipient?.name_en }}</p>
                                </div>
                            </div>
                            <button @click="showWhatsAppModal = false" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-6">
                            <!-- Step 1: Select Document -->
                            <div class="space-y-3">
                                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-md bg-primary-100 flex items-center justify-center text-primary-600 text-[10px]">1</div>
                                    Select Document Template
                                </label>
                                <div v-if="loadingDocs" class="flex items-center gap-2 text-xs text-gray-400 p-3 bg-gray-50 rounded-xl">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Loading documents...
                                </div>
                                <div v-else-if="docs.length === 0" class="p-4 bg-red-50 rounded-xl text-center border border-red-100">
                                    <p class="text-xs text-red-500">No active documents found. Please create one first.</p>
                                    <RouterLink to="/documents/create" class="text-xs text-primary-600 font-bold mt-2 inline-block hover:underline">Create Document</RouterLink>
                                </div>
                                <div v-else class="relative group">
                                    <select 
                                        v-model="selectedDocId" 
                                        class="select w-full pl-11 h-12 bg-white border-gray-200 focus:border-primary-500 focus:ring-primary-500/20 transition-all rounded-xl appearance-none"
                                    >
                                        <option v-for="doc in docs" :key="doc.id" :value="doc.id">
                                            {{ doc.name }} ({{ doc.fields_count || 0 }} fields)
                                        </option>
                                    </select>
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                        <div :class="['w-6 h-6 rounded-md flex items-center justify-center text-white', docGradient(selectedDocId || 0)]">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Preview -->
                            <div v-if="selectedDoc" class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-primary-100 flex items-center justify-center text-primary-600 text-[10px]">2</div>
                                        Document Preview
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] bg-primary-50 text-primary-700 px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider border border-primary-100">
                                            {{ selectedDoc.language === 'gu' ? 'Gujarati' : 'English' }}
                                        </span>
                                        <!-- Page Navigation -->
                                        <div v-if="!isImage(selectedDoc) && pdfTotalPages > 1" class="flex items-center bg-white border border-gray-100 rounded-lg p-0.5 shadow-sm">
                                            <button @click="previewPage > 1 && previewPage--" :disabled="previewPage === 1" class="p-1 hover:bg-gray-50 rounded disabled:opacity-30 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                            </button>
                                            <span class="text-[10px] font-bold text-gray-600 w-10 text-center select-none">{{ previewPage }}/{{ pdfTotalPages }}</span>
                                            <button @click="previewPage < pdfTotalPages && previewPage++" :disabled="previewPage === pdfTotalPages" class="p-1 hover:bg-gray-50 rounded disabled:opacity-30 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div ref="previewContainerParent" class="relative rounded-3xl overflow-hidden bg-white border border-gray-200 shadow-xl group transition-all duration-500">
                                    <!-- Toolbar -->
                                    <div class="absolute top-0 inset-x-0 h-10 bg-gradient-to-b from-black/20 to-transparent z-10 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    
                                    <div 
                                        class="mx-auto relative overflow-hidden transition-all duration-300" 
                                        :style="{ 
                                            maxWidth: '100%', 
                                            width: previewW + 'px', 
                                            height: previewH + 'px' 
                                        }"
                                    >
                                        <template v-if="isImage(selectedDoc)">
                                            <img 
                                                :src="selectedDoc.file_url" 
                                                class="absolute inset-0 w-full h-full object-contain pointer-events-none select-none" 
                                                @load="onPreviewImageLoad"
                                            />
                                        </template>
                                        <template v-else>
                                            <PdfCanvas 
                                                :src="selectedDoc.file_url" 
                                                :width="previewW" 
                                                :page="previewPage"
                                                class="pointer-events-none select-none"
                                                @rendered="onPreviewPdfRendered"
                                                @total-pages="n => pdfTotalPages = n"
                                            />
                                        </template>

                                        <!-- Exact Name Overlay -->
                                        <div 
                                            v-for="field in filteredFields" :key="field.id"
                                            class="absolute flex flex-col items-center justify-center text-center pointer-events-none select-none whitespace-nowrap overflow-hidden"
                                            :style="{ 
                                                left: (field.x_percent / 100 * previewW) + 'px', 
                                                top: (field.y_percent / 100 * previewH) + 'px',
                                                width: (field.width_percent / 100 * previewW) + 'px',
                                                height: (field.height_percent / 100 * previewH) + 'px',
                                                transform: 'translate(0, 0)',
                                                border: `1px dashed ${field.color || '#3b82f6'}50`,
                                                backgroundColor: (field.color || '#3b82f6') + '08'
                                            }"
                                        >
                                            <span 
                                                class="font-bold drop-shadow-sm animate-pulse-slow px-1 truncate w-full"
                                                :style="{ 
                                                    fontSize: Math.max(6, (field.height_percent / 100 * previewH) * 0.6) + 'px', 
                                                    fontFamily: selectedDoc.language === 'en' ? 'sans-serif' : 'serif',
                                                    color: field.color || '#111827'
                                                }"
                                            >
                                                {{ getPreviewFieldValue(field) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Hover Status -->
                                    <div class="absolute top-4 right-4 z-10">
                                        <div class="bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/50 text-[10px] font-bold text-primary-600 shadow-sm flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></div>
                                            Live Preview
                                        </div>
                                    </div>

                                    <!-- Bottom Gradient -->
                                    <div class="absolute bottom-0 inset-x-0 h-20 bg-gradient-to-t from-black/5 to-transparent pointer-events-none"></div>
                                </div>

                                <!-- Template Info -->
                                <div class="bg-primary-50/50 rounded-2xl p-4 flex items-center gap-4 border border-primary-100/50">
                                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-primary-600 shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-bold text-gray-900">Personalization Active</p>
                                        <p class="text-[10px] text-gray-500">The recipient's details (name, village, etc.) will appear exactly as shown in the preview above across <b>{{ selectedDoc.fields?.length || 0 }}</b> positions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
                            <button @click="showWhatsAppModal = false" class="btn btn-secondary">Cancel</button>
                            <button 
                                @click="generateAndSend" 
                                class="btn btn-primary bg-green-600 hover:bg-green-700 border-green-600 gap-2 px-6"
                                :disabled="!selectedDocId || sending"
                            >
                                <svg v-if="sending" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                Send via WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Contact Picker Review Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showContactsModal" class="modal-overlay" @click.self="showContactsModal = false">
                    <div class="modal max-w-lg w-[95%] p-0 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Add from Contacts</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ pickedContacts.length }} contact{{ pickedContacts.length !== 1 ? 's' : '' }} selected — review before adding</p>
                            </div>
                            <button @click="showContactsModal = false" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="overflow-y-auto max-h-[60vh] p-4 space-y-2">
                            <div
                                v-for="(contact, idx) in pickedContacts"
                                :key="idx"
                                class="flex items-center gap-3 p-3 rounded-xl border transition-colors"
                                :class="contact.skip ? 'border-gray-100 bg-gray-50 opacity-50' : 'border-gray-200 bg-white'"
                            >
                                <input
                                    type="checkbox"
                                    class="rounded shrink-0"
                                    :checked="!contact.skip"
                                    @change="contact.skip = !$event.target.checked"
                                />
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                    {{ (contact.name || '?')[0].toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input
                                        v-model="contact.name"
                                        type="text"
                                        class="input h-8 text-sm font-medium px-2 py-1 mb-1"
                                        placeholder="Name"
                                    />
                                    <input
                                        v-model="contact.mobile"
                                        type="tel"
                                        class="input h-8 text-sm px-2 py-1 text-gray-600"
                                        placeholder="Mobile number"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ pickedContacts.filter(c => !c.skip).length }} will be added</span>
                            <div class="flex gap-2">
                                <button @click="showContactsModal = false" class="btn btn-secondary btn-sm">Cancel</button>
                                <button
                                    @click="savePickedContacts"
                                    :disabled="savingContacts || pickedContacts.every(c => c.skip)"
                                    class="btn btn-primary btn-sm"
                                >
                                    <svg v-if="savingContacts" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Add {{ pickedContacts.filter(c => !c.skip).length }} Recipient{{ pickedContacts.filter(c => !c.skip).length !== 1 ? 's' : '' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Links Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showLinksModal" class="modal-overlay" @click.self="showLinksModal = false">
                    <div class="modal max-w-lg w-[95%] p-0 overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Personalized Links</h3>
                                    <p class="text-xs text-gray-500">For {{ selectedRecipient?.name_en }}</p>
                                </div>
                            </div>
                            <button @click="showLinksModal = false" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <div v-if="!selectedRecipient?.invitation_links?.length" class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 text-sm">No links generated yet for this recipient.</p>
                                <button @click="showLinksModal = false; openWhatsAppModal(selectedRecipient)" class="btn btn-primary btn-sm mt-4">Generate First Link</button>
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="link in selectedRecipient.invitation_links" :key="link.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-primary-200 transition-colors group">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ link.document?.name || 'Unknown Document' }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5">Created on {{ formatDate(link.created_at) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="copyLink(link.token)" 
                                            class="btn btn-secondary btn-sm h-9 px-3 gap-1.5"
                                            :class="copiedToken === link.token ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ''"
                                        >
                                            <svg v-if="copiedToken === link.token" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-xs">{{ copiedToken === link.token ? 'Copied' : 'Copy' }}</span>
                                        </button>
                                        <a 
                                            :href="`/doc/view/${link.token}`" 
                                            target="_blank" 
                                            class="btn btn-primary btn-sm h-9 px-3 gap-1.5"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            <span class="text-xs">View</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button @click="showLinksModal = false" class="btn btn-secondary btn-sm">Close</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import PdfCanvas from '@/components/PdfCanvas.vue';
import { useLanguageStore } from '@/stores/language';

const lang = useLanguageStore();

const search = ref('');
const villageFilter = ref('');
const selected = ref([]);
const showAddModal = ref(false);
const showCsvModal = ref(false);

// Contact Picker API
const contactPickerSupported = 'contacts' in navigator && 'ContactsManager' in window;
const pickingContacts = ref(false);
const showContactsModal = ref(false);
const pickedContacts = ref([]);
const savingContacts = ref(false);

async function pickFromContacts() {
    pickingContacts.value = true;
    try {
        const contacts = await navigator.contacts.select(['name', 'tel'], { multiple: true });
        if (!contacts || contacts.length === 0) return;

        pickedContacts.value = contacts.map(c => ({
            name: c.name?.[0] ?? '',
            mobile: c.tel?.[0] ?? '',
            skip: false,
        })).filter(c => c.name || c.mobile);

        if (pickedContacts.value.length === 0) return;

        if (pickedContacts.value.length === 1) {
            // Single contact — pre-fill the add modal
            const c = pickedContacts.value[0];
            isEditing.value = false;
            editId.value = null;
            form.value = { name_en: c.name, name_gu: '', mobile: c.mobile, village_en: '', village_gu: '' };
            showAddModal.value = true;
        } else {
            showContactsModal.value = true;
        }
    } catch (err) {
        if (err.name !== 'AbortError') console.error('Contact picker error:', err);
    } finally {
        pickingContacts.value = false;
    }
}

async function savePickedContacts() {
    const toAdd = pickedContacts.value.filter(c => !c.skip && (c.name || c.mobile));
    if (!toAdd.length) return;

    savingContacts.value = true;
    try {
        const results = await Promise.allSettled(
            toAdd.map(c => axios.post('/api/recipients', {
                name_en: c.name,
                name_gu: '',
                mobile: c.mobile,
                village_en: '',
                village_gu: '',
            }))
        );

        results.forEach(r => {
            if (r.status === 'fulfilled') recipients.value.unshift(r.value.data);
        });

        showContactsModal.value = false;
    } catch (err) {
        console.error('Failed to save contacts:', err);
    } finally {
        savingContacts.value = false;
    }
}

// WhatsApp Modal State
const showWhatsAppModal = ref(false);
const showLinksModal = ref(false);
const selectedRecipient = ref(null);
const selectedDocId = ref(null);
const docs = ref([]);
const loadingDocs = ref(false);
const sending = ref(false);
const saving = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const copiedToken = ref(null);

// Preview Pagination
const previewPage = ref(1);
const pdfTotalPages = ref(1);

const getPreviewFieldValue = (field) => {
    if (!selectedRecipient.value || !selectedDoc.value) return '';
    const lang = selectedDoc.value.language || 'en';
    
    if (field.field_type === 'village') {
        return lang === 'gu' ? selectedRecipient.value.village_gu : selectedRecipient.value.village_en;
    }
    
    return lang === 'gu' ? selectedRecipient.value.name_gu : selectedRecipient.value.name_en;
};

// Preview Dimensions
const previewContainerParent = ref(null);
const previewW = ref(350);
const previewAspectRatio = ref(1.4);
const previewH = computed(() => Math.round(previewW.value * previewAspectRatio.value));

function onPreviewPdfRendered({ width, height }) {
    previewAspectRatio.value = height / width;
}

function onPreviewImageLoad(e) {
    const { naturalWidth, naturalHeight } = e.target;
    if (naturalWidth && naturalHeight) {
        previewAspectRatio.value = naturalHeight / naturalWidth;
    }
}

watch(showWhatsAppModal, (val) => {
    if (val) {
        setTimeout(() => {
            const el = previewContainerParent.value;
            if (el) {
                const ro = new ResizeObserver(([entry]) => {
                    previewW.value = Math.min(350, entry.contentRect.width);
                });
                ro.observe(el);
            }
        }, 100);
    }
});

const form = ref({ name_en: '', name_gu: '', mobile: '', village_en: '', village_gu: '' });
const autoConverting = ref(false);
const autoConvertingVillage = ref(false);
let autoConvertTimer = null;
let autoConvertVillageTimer = null;

async function autoConvert(type = 'name') {
    const isName = type === 'name';
    const text = isName ? form.value.name_en.trim() : form.value.village_en.trim();
    if (!text) return;
    
    if (isName) autoConverting.value = true;
    else autoConvertingVillage.value = true;
    
    try {
        const res = await fetch(
            `https://inputtools.google.com/request?text=${encodeURIComponent(text)}&itc=gu-t-i0-und&num=1&cp=0&cs=1&ie=utf-8&oe=utf-8`
        );
        const data = await res.json();
        if (data[0] === 'SUCCESS' && data[1]?.[0]?.[1]?.[0]) {
            if (isName) form.value.name_gu = data[1][0][1][0];
            else form.value.village_gu = data[1][0][1][0];
        }
    } catch (err) {
        console.error('Auto convert failed:', err);
    } finally {
        if (isName) autoConverting.value = false;
        else autoConvertingVillage.value = false;
    }
}

watch(() => form.value.name_en, (val) => {
    clearTimeout(autoConvertTimer);
    if (!val.trim()) { form.value.name_gu = ''; return; }
    autoConvertTimer = setTimeout(() => autoConvert('name'), 700);
});

watch(() => form.value.village_en, (val) => {
    clearTimeout(autoConvertVillageTimer);
    if (!val.trim()) { form.value.village_gu = ''; return; }
    autoConvertVillageTimer = setTimeout(() => autoConvert('village'), 700);
});

const villages = computed(() => {
    const v = recipients.value.map(r => r.village_en).filter(Boolean);
    return [...new Set(v)].sort();
});

const gradients = [
    'bg-gradient-to-br from-pink-400 to-rose-500',
    'bg-gradient-to-br from-primary-400 to-primary-600',
    'bg-gradient-to-br from-amber-400 to-orange-500',
    'bg-gradient-to-br from-emerald-400 to-teal-500',
    'bg-gradient-to-br from-violet-400 to-purple-600',
];

function docGradient(id) {
    return gradients[id % gradients.length];
}

function isImage(doc) {
    return ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(doc.file_type?.toLowerCase());
}

const selectedDoc = computed(() => docs.value.find(d => d.id === selectedDocId.value));

const filteredFields = computed(() => {
    if (!selectedDoc.value || !selectedDoc.value.fields) return [];
    return selectedDoc.value.fields.filter(f => (f.page_number || 1) === previewPage.value);
});

watch(selectedDocId, () => {
    previewPage.value = 1;
});

async function fetchDocuments() {
    loadingDocs.value = true;
    try {
        const { data } = await axios.get('/api/documents');
        docs.value = data;
        if (data.length > 0 && !selectedDocId.value) {
            selectedDocId.value = data[0].id;
        }
    } catch (err) {
        console.error('Failed to fetch documents:', err);
    } finally {
        loadingDocs.value = false;
    }
}

const recipients = ref([]);

const filteredRecipients = computed(() => {
    return recipients.value.filter(r => {
        // If Gujarati is selected, show only those with Gujarati names
        if (lang.currentLocale === 'gu' && !r.name_gu) {
            return false;
        }

        const q = search.value.toLowerCase();
        const matchSearch = !q || 
            r.name_en.toLowerCase().includes(q) || 
            (r.name_gu && r.name_gu.includes(q)) ||
            r.mobile.includes(q) || 
            (r.village_en && r.village_en.toLowerCase().includes(q)) ||
            (r.village_gu && r.village_gu.includes(q));
        const matchVillage = !villageFilter.value || r.village_en === villageFilter.value;
        return matchSearch && matchVillage;
    });
});

async function fetchRecipients() {
    try {
        const { data } = await axios.get('/api/recipients');
        recipients.value = data;
    } catch (err) {
        console.error('Failed to fetch recipients:', err);
    }
}

function openAddModal() {
    isEditing.value = false;
    editId.value = null;
    form.value = { name_en: '', name_gu: '', mobile: '', village_en: '', village_gu: '' };
    showAddModal.value = true;
}

function editRecipient(recipient) {
    isEditing.value = true;
    editId.value = recipient.id;
    form.value = { 
        name_en: recipient.name_en, 
        name_gu: recipient.name_gu, 
        mobile: recipient.mobile, 
        village_en: recipient.village_en,
        village_gu: recipient.village_gu
    };
    showAddModal.value = true;
}

function toggleAll(e) {
    selected.value = e.target.checked ? filteredRecipients.value.map(r => r.id) : [];
}

async function saveRecipient() {
    if (!form.value.name_en || !form.value.mobile) return;
    
    saving.value = true;
    try {
        if (isEditing.value) {
            const { data } = await axios.put(`/api/recipients/${editId.value}`, form.value);
            const idx = recipients.value.findIndex(r => r.id === editId.value);
            if (idx !== -1) recipients.value[idx] = data;
        } else {
            const { data } = await axios.post('/api/recipients', form.value);
            recipients.value.unshift(data);
        }
        showAddModal.value = false;
        form.value = { name_en: '', name_gu: '', mobile: '', village_en: '', village_gu: '' };
    } catch (err) {
        console.error('Failed to save recipient:', err);
    } finally {
        saving.value = false;
    }
}

async function deleteRecipient(id) {
    if (!confirm('Are you sure you want to delete this recipient?')) return;
    try {
        await axios.delete(`/api/recipients/${id}`);
        recipients.value = recipients.value.filter(r => r.id !== id);
    } catch (err) {
        console.error('Failed to delete recipient:', err);
    }
}

async function bulkDelete() {
    if (!selected.value.length) return;
    if (!confirm(`Are you sure you want to delete ${selected.value.length} selected recipients?`)) return;
    
    try {
        await Promise.all(selected.value.map(id => axios.delete(`/api/recipients/${id}`)));
        recipients.value = recipients.value.filter(r => !selected.value.includes(r.id));
        selected.value = [];
    } catch (err) {
        console.error('Failed to delete recipients:', err);
    }
}

function openWhatsAppModal(recipient) {
    selectedRecipient.value = recipient;
    showWhatsAppModal.value = true;
    if (docs.value.length === 0) {
        fetchDocuments();
    }
}

function openLinksModal(recipient) {
    selectedRecipient.value = recipient;
    showLinksModal.value = true;
}

function copyLink(token) {
    const url = `${window.location.origin}/doc/view/${token}`;
    navigator.clipboard.writeText(url);
    copiedToken.value = token;
    setTimeout(() => {
        copiedToken.value = null;
    }, 2000);
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

async function generateAndSend() {
    if (!selectedRecipient.value || !selectedDocId.value) return;
    
    sending.value = true;
    try {
        // Create link via API
        const { data } = await axios.post('/api/invitation-links', {
            recipient_id: selectedRecipient.value.id,
            document_id: selectedDocId.value,
            via: 'WhatsApp'
        });

        const token = data.token;
        const name = selectedDoc.value.language === 'gu' ? selectedRecipient.value.name_gu : selectedRecipient.value.name_en;
        
        const message = encodeURIComponent(
            `Hello ${name},\n\n` +
            `You are cordially invited to our event: *${selectedDoc.value.name}*.\n\n` +
            `Please check your personalized invitation and details here:\n` +
            `${window.location.origin}/doc/view/${token}\n\n` +
            `You can also download your invitation PDF from the link above.\n\n` +
            `Regards,\nInviteFlow`
        );
        
        const mobile = selectedRecipient.value.mobile.replace(/\D/g, '');
        const whatsappUrl = `https://wa.me/${mobile}?text=${message}`;
        
        window.open(whatsappUrl, '_blank');
        
        // Update local state
        const idx = recipients.value.findIndex(r => r.id === selectedRecipient.value.id);
        if (idx !== -1) {
            recipients.value[idx].sent = true;
            // Add the new link to the recipient's invitation_links
            if (!recipients.value[idx].invitation_links) {
                recipients.value[idx].invitation_links = [];
            }
            recipients.value[idx].invitation_links.unshift({
                id: data.id,
                token: data.token,
                created_at: data.created_at,
                document: docs.value.find(d => d.id === selectedDocId.value)
            });
        }
        
        showWhatsAppModal.value = false;
    } catch (err) {
        console.error('Failed to generate link:', err);
    } finally {
        sending.value = false;
    }
}

onMounted(() => {
    fetchDocuments();
    fetchRecipients();
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

@keyframes pulse-slow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.02); }
}
.animate-pulse-slow {
    animation: pulse-slow 3s infinite ease-in-out;
}
</style>

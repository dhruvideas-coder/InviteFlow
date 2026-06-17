<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ lang.t('all_recipients') }}</h2>
                <p class="text-sm text-gray-500">Manage invitation recipients and their details</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <ViewToggle v-model="viewMode" />
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
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="stat-card flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-gray-900">{{ totalCount }}</p>
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
                    <p class="text-xl font-bold text-gray-900">{{ globalStats.total_sent }}</p>
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
                    <p class="text-xl font-bold text-gray-900">{{ globalStats.total_pending }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ lang.t('pending') }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input v-model="search" type="text" class="input pl-10" :placeholder="lang.t('search_recipients')" @input="onSearchInput" />
            </div>
            <div class="flex gap-3 sm:contents">
            <select v-model="villageFilter" class="select flex-1 sm:w-44" @change="fetchRecipients(1)">
                <option value="">{{ lang.t('all_villages') }}</option>
                <option v-for="v in villages" :key="v" :value="v">{{ v }}</option>
            </select>
            <select v-model="perPage" class="select w-28 shrink-0" @change="fetchRecipients(1)">
                <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }} / Page</option>
            </select>
            </div>
        </div>

        <!-- Table View (desktop only when table mode is active) -->
        <div class="table-wrap hidden" :class="{ 'sm:block': viewMode === 'table' }">
            <table class="table min-w-[500px] sm:min-w-[700px]">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="rounded" @change="toggleAll" />
                        </th>
                        <th>{{ lang.t('name') }}</th>
                        <th v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell">Name (Gujarati)</th>
                        <th>{{ lang.t('phone') }}</th>
                        <th>{{ lang.t('village') }}</th>
                        <th v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell">Village (Gujarati)</th>
                        <th>{{ lang.t('status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in recipients" :key="r.id">
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
                        <td v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell text-gray-700" style="font-family: serif;">{{ r.name_gu }}</td>
                        <td class="text-gray-600 font-mono text-xs">{{ formatMobile(r.mobile) }}</td>
                        <td><span class="tag">{{ lang.currentLocale === 'gu' ? r.village_gu || r.village_en : r.village_en }}</span></td>
                        <td v-if="lang.currentLocale === 'en'" class="hidden sm:table-cell text-gray-700" style="font-family: serif;">{{ r.village_gu }}</td>
                        <td>
                            <span :class="['badge text-xs', r.sent ? 'badge-green' : 'badge-gray']">
                                {{ r.sent ? lang.t('sent') : lang.t('pending') }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1 justify-end">
                                <button
                                    @click="editRecipient(r)"
                                    class="btn btn-ghost btn-sm" title="Edit"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDelete(r)"
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
        </div>

        <!-- Card View (mobile always; desktop when card mode is active) -->
        <div :class="{ 'sm:hidden': viewMode === 'table' }">
            <div v-if="!recipients.length" class="card py-12 text-center text-gray-400 text-sm">
                No recipients found
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="r in recipients"
                    :key="r.id"
                    class="card card-hover p-4 flex flex-col gap-3"
                    :class="{ 'ring-2 ring-primary-400': selected.includes(r.id) }"
                >
                    <div class="flex items-start gap-3">
                        <input type="checkbox" class="rounded mt-1 shrink-0" v-model="selected" :value="r.id" />
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ (lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en)[0] }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 truncate">{{ lang.currentLocale === 'gu' ? r.name_gu || r.name_en : r.name_en }}</p>
                            <p v-if="lang.currentLocale === 'en' && r.name_gu" class="text-xs text-gray-500 truncate" style="font-family: serif;">{{ r.name_gu }}</p>
                            <p class="text-gray-600 font-mono text-xs mt-0.5">{{ formatMobile(r.mobile) }}</p>
                        </div>
                        <span :class="['badge text-xs shrink-0', r.sent ? 'badge-green' : 'badge-gray']">
                            {{ r.sent ? lang.t('sent') : lang.t('pending') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-3 mt-auto border-t border-gray-50">
                        <span class="tag">{{ lang.currentLocale === 'gu' ? r.village_gu || r.village_en : r.village_en }}</span>
                        <div class="flex items-center gap-1">
                            <button @click="editRecipient(r)" class="btn btn-ghost btn-sm" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="confirmDelete(r)" class="btn btn-ghost btn-sm text-red-400 hover:text-red-500 hover:bg-red-50" title="Delete">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk actions & Pagination (shared between views) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 py-3 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div v-if="selected.length" class="flex items-center gap-2">
                        <span class="text-sm text-primary-700 font-medium">{{ selected.length }} {{ lang.t('selected') }}</span>
                        <button @click="bulkDelete" class="btn btn-danger btn-sm">{{ lang.t('delete_selected') }}</button>
                    </div>
                    <span v-else class="text-xs text-gray-500">
                        Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} recipients
                    </span>
                </div>

                <div v-if="pagination.last_page > 1" class="flex items-center gap-1">
                    <button 
                        @click="fetchRecipients(pagination.current_page - 1)" 
                        :disabled="pagination.current_page === 1"
                        class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-1 mx-2">
                        <button 
                            v-for="p in visiblePages" 
                            :key="p"
                            @click="p !== '...' && fetchRecipients(p)"
                            class="w-8 h-8 rounded-lg text-xs font-bold transition-all"
                            :class="[
                                p === pagination.current_page 
                                    ? 'bg-primary-600 text-white shadow-md shadow-primary-200' 
                                    : p === '...' 
                                        ? 'cursor-default text-gray-400' 
                                        : 'hover:bg-gray-100 text-gray-600'
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <button 
                        @click="fetchRecipients(pagination.current_page + 1)" 
                        :disabled="pagination.current_page === pagination.last_page"
                        class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        <!-- /Bulk actions & Pagination -->

        <!-- Delete Recipient Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
                    <div class="modal max-w-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ lang.t('delete_recipient') }}</h3>
                                <p class="text-xs text-gray-500">{{ lang.t('action_cannot_undone') }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                            Are you sure you want to delete <span class="font-semibold text-gray-900">{{ lang.currentLocale === 'gu' ? deleteTarget.name_gu || deleteTarget.name_en : deleteTarget.name_en }}</span>? All associated data will be removed.
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button @click="deleteTarget = null" class="btn btn-secondary" :disabled="deleting">{{ lang.t('cancel') }}</button>
                            <button @click="deleteRecipient" class="btn btn-danger" :disabled="deleting">
                                <svg v-if="deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span v-else>{{ lang.t('delete_recipient') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Bulk Delete Recipients Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showBulkDeleteModal" class="modal-overlay" @click.self="showBulkDeleteModal = false">
                    <div class="modal max-w-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ lang.t('delete_recipients') }}</h3>
                                <p class="text-xs text-gray-500">{{ lang.t('action_cannot_undone') }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                            Are you sure you want to delete <span class="font-semibold text-gray-900">{{ selected.length }}</span> selected recipients? All associated data will be removed.
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button @click="showBulkDeleteModal = false" class="btn btn-secondary" :disabled="deleting">{{ lang.t('cancel') }}</button>
                            <button @click="confirmBulkDelete" class="btn btn-danger" :disabled="deleting">
                                <svg v-if="deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span v-else>{{ lang.t('delete_recipients') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

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
                            <label class="label">{{ lang.t('name_english') }} <span class="text-red-400">*</span></label>
                            <input v-model="form.name_en" type="text" class="input" :placeholder="lang.t('name_english')" />
                        </div>
                        <div class="form-group">
                            <label class="label">{{ lang.t('name_gujarati') }}</label>
                            <div class="flex gap-2">
                                <input v-model="form.name_gu" type="text" class="input" :placeholder="lang.t('name_gujarati')" style="font-family: serif;" />
                                <button @click="autoConvert('name')" :disabled="autoConverting" class="btn btn-secondary btn-sm shrink-0 text-xs flex items-center gap-1">
                                    <svg v-if="autoConverting" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ autoConverting ? lang.t('converting') : lang.t('auto_convert') }}
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="label">{{ lang.t('mobile_number') }} <span class="text-red-400">*</span></label>
                            <div class="flex items-center border rounded-lg overflow-hidden" :class="formErrors.mobile ? 'border-red-400' : 'border-gray-300'">
                                <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm font-medium border-r select-none" :class="formErrors.mobile ? 'border-red-400' : 'border-gray-300'">+91</span>
                                <input
                                    v-model="form.mobile"
                                    type="tel"
                                    inputmode="numeric"
                                    maxlength="10"
                                    class="flex-1 px-3 py-2 text-sm outline-none bg-white"
                                    placeholder="98765 43210"
                                    @input="form.mobile = form.mobile.replace(/\D/g, '').slice(0, 10); formErrors.mobile = ''"
                                />
                            </div>
                            <p v-if="formErrors.mobile" class="text-xs text-red-500 mt-1">{{ formErrors.mobile }}</p>
                        </div>
                        <div class="form-group">
                            <label class="label">{{ lang.t('village_english') }}</label>
                            <input v-model="form.village_en" type="text" class="input" :placeholder="lang.t('village_english')" />
                        </div>
                        <div class="form-group">
                            <label class="label">{{ lang.t('village_gujarati') }}</label>
                            <div class="flex gap-2">
                                <input v-model="form.village_gu" type="text" class="input" :placeholder="lang.t('village_gujarati')" style="font-family: serif;" />
                                <button @click="autoConvert('village')" :disabled="autoConvertingVillage" class="btn btn-secondary btn-sm shrink-0 text-xs flex items-center gap-1">
                                    <svg v-if="autoConvertingVillage" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ autoConvertingVillage ? lang.t('converting') : lang.t('auto_convert') }}
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
                                    <h3 class="text-lg font-bold text-gray-900">{{ lang.t('send_invitation') }}</h3>
                                    <p class="text-xs text-gray-500">{{ lang.t('to') }}: {{ selectedRecipient?.name_en }} • {{ formatMobile(selectedRecipient?.mobile) }}</p>
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
                                    {{ lang.t('select_template') }}
                                </label>
                                <div v-if="loadingDocs" class="flex items-center gap-2 text-xs text-gray-400 p-3 bg-gray-50 rounded-xl">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ lang.t('loading') }}...
                                </div>
                                <div v-else-if="docs.length === 0" class="p-4 bg-red-50 rounded-xl text-center border border-red-100">
                                    <p class="text-xs text-red-500">{{ lang.t('no_templates_found') }}</p>
                                    <RouterLink to="/documents/create" class="text-xs text-primary-600 font-bold mt-2 inline-block hover:underline">{{ lang.t('create_document') }}</RouterLink>
                                </div>
                                <div v-else class="relative group">
                                    <select 
                                        v-model="selectedDocId" 
                                        class="select w-full pl-11 h-12 bg-white border-gray-200 focus:border-primary-500 focus:ring-primary-500/20 transition-all rounded-xl appearance-none"
                                    >
                                        <option v-for="doc in docs" :key="doc.id" :value="doc.id">
                                            {{ doc.name }} ({{ doc.fields_count || 0 }} {{ lang.t('fields') }})
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
                                        {{ lang.t('preview') }}
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] bg-primary-50 text-primary-700 px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider border border-primary-100">
                                            {{ selectedDoc.language === 'gu' ? lang.t('gujarati') : lang.t('english') }}
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
                                            {{ lang.t('live_preview') }}
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
                                        <p class="text-xs font-bold text-gray-900">{{ lang.t('personalization_active') }}</p>
                                        <p class="text-[10px] text-gray-500">{{ lang.t('details_will_appear', { count: selectedDoc.fields?.length || 0 }) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
                            <button @click="showWhatsAppModal = false" class="btn btn-secondary">{{ lang.t('cancel') }}</button>
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
                                {{ lang.t('send_whatsapp') }}
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
                                <h3 class="text-lg font-bold text-gray-900">{{ lang.t('add_from_contacts') }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ lang.t('contacts_selected', { count: pickedContacts.length }) }}</p>
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
                            <span class="text-xs text-gray-500">{{ pickedContacts.filter(c => !c.skip).length }} {{ lang.t('will_be_added') }}</span>
                            <div class="flex gap-2">
                                <button @click="showContactsModal = false" class="btn btn-secondary btn-sm">{{ lang.t('cancel') }}</button>
                                <button
                                    @click="savePickedContacts"
                                    :disabled="savingContacts || pickedContacts.every(c => c.skip)"
                                    class="btn btn-primary btn-sm"
                                >
                                    <svg v-if="savingContacts" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ lang.t('add_recipients', { count: pickedContacts.filter(c => !c.skip).length }) }}
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
                                    <h3 class="text-lg font-bold text-gray-900">{{ lang.t('personalized_links') }}</h3>
                                    <p class="text-xs text-gray-500">{{ selectedRecipient?.name_en }} • {{ formatMobile(selectedRecipient?.mobile) }}</p>
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
                                <p class="text-gray-500 text-sm">{{ lang.t('no_links_generated') }}</p>
                                <button @click="showLinksModal = false; openWhatsAppModal(selectedRecipient)" class="btn btn-primary btn-sm mt-4">{{ lang.t('generate_first_link') }}</button>
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="link in selectedRecipient.invitation_links" :key="link.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-primary-200 transition-colors group">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ link.document?.name || lang.t('unknown_document') }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5">{{ lang.t('created_on', { date: formatDate(link.created_at) }) }}</p>
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
                                            <span class="text-xs">{{ copiedToken === link.token ? lang.t('copied') : lang.t('copy') }}</span>
                                        </button>
                                        <a 
                                            :href="`/doc/view/${link.token}`" 
                                            target="_blank" 
                                            class="btn btn-primary btn-sm h-9 px-3 gap-1.5"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            <span class="text-xs">{{ lang.t('view') }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button @click="showLinksModal = false" class="btn btn-secondary btn-sm">{{ lang.t('close') }}</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Document Preview Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDocPreviewModal" class="modal-overlay" @click.self="showDocPreviewModal = false">
                    <div class="modal max-w-3xl w-[96%] max-h-[95vh] flex flex-col p-0 overflow-hidden">

                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Document Preview</h3>
                                    <p class="text-xs text-gray-500">
                                        {{ lang.currentLocale === 'gu' ? selectedRecipient?.name_gu || selectedRecipient?.name_en : selectedRecipient?.name_en }}
                                    </p>
                                </div>
                            </div>
                            <button @click="showDocPreviewModal = false" class="p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">

                            <!-- Document Selector -->
                            <div v-if="loadingDocs" class="flex items-center gap-2 text-xs text-gray-400 p-3 bg-gray-50 rounded-xl">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                {{ lang.t('loading') }}...
                            </div>
                            <div v-else-if="docs.length === 0" class="p-4 bg-red-50 rounded-xl text-center border border-red-100">
                                <p class="text-xs text-red-500">{{ lang.t('no_templates_found') }}</p>
                            </div>
                            <div v-else class="relative">
                                <select v-model="previewDocSelectedId" class="select w-full pl-11 h-11 bg-white appearance-none">
                                    <option v-for="doc in docs" :key="doc.id" :value="doc.id">
                                        {{ doc.name }}
                                    </option>
                                </select>
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <div :class="['w-6 h-6 rounded-md flex items-center justify-center text-white', docGradient(previewDocSelectedId || 0)]">
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

                            <!-- A4 Document Preview -->
                            <div v-if="previewDoc" class="space-y-4">
                                <!-- Measurement container (full width, capped at 600px) -->
                                <div ref="previewDocContainerRef" class="w-full max-w-[600px] mx-auto">
                                    <!-- A4 canvas wrapper -->
                                    <div
                                        class="relative overflow-hidden bg-white shadow-xl border border-gray-200 rounded-xl mx-auto"
                                        :style="{ width: previewDocW + 'px', height: previewDocH + 'px' }"
                                    >
                                        <template v-if="isImage(previewDoc)">
                                            <img
                                                :src="previewDoc.file_url"
                                                class="absolute inset-0 w-full h-full object-contain pointer-events-none select-none"
                                                @load="onPreviewDocImageLoad"
                                            />
                                        </template>
                                        <template v-else>
                                            <PdfCanvas
                                                :src="previewDoc.file_url"
                                                :width="previewDocW"
                                                :page="previewDocPage"
                                                class="pointer-events-none select-none"
                                                @rendered="onPreviewDocPdfRendered"
                                                @total-pages="n => previewDocTotalPages = n"
                                            />
                                        </template>

                                        <!-- Name / Village field overlays -->
                                        <div
                                            v-for="field in previewDocFilteredFields"
                                            :key="field.id"
                                            class="absolute flex items-center justify-center text-center pointer-events-none select-none"
                                            :style="{
                                                left:   (field.x_percent     / 100 * previewDocW) + 'px',
                                                top:    (field.y_percent     / 100 * previewDocH) + 'px',
                                                width:  (field.width_percent / 100 * previewDocW) + 'px',
                                                height: (field.height_percent / 100 * previewDocH) + 'px',
                                                border: `1px dashed ${field.color || '#3b82f6'}60`,
                                                backgroundColor: (field.color || '#3b82f6') + '10',
                                            }"
                                        >
                                            <span
                                                class="font-bold px-1 truncate w-full text-center"
                                                :style="{
                                                    fontSize:   Math.max(8, (field.height_percent / 100 * previewDocH) * 0.55) + 'px',
                                                    fontFamily: previewDoc.language === 'en' ? 'sans-serif' : 'serif',
                                                    color:      field.color || '#111827',
                                                }"
                                            >
                                                {{ getPreviewDocFieldValue(field) }}
                                            </span>
                                        </div>

                                        <!-- Live badge -->
                                        <div class="absolute top-3 right-3 z-10">
                                            <div class="bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full border border-white/50 text-[10px] font-bold text-violet-600 shadow-sm flex items-center gap-1.5">
                                                <div class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></div>
                                                Preview
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Page Navigation -->
                                <div v-if="!isImage(previewDoc) && previewDocTotalPages > 1" class="flex items-center justify-center gap-3">
                                    <button
                                        @click="previewDocPage > 1 && previewDocPage--"
                                        :disabled="previewDocPage === 1"
                                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all text-sm font-medium text-gray-700 shadow-sm"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Previous
                                    </button>
                                    <span class="text-sm font-bold text-gray-700 bg-gray-100 px-5 py-2 rounded-xl min-w-[110px] text-center select-none">
                                        Page {{ previewDocPage }} / {{ previewDocTotalPages }}
                                    </span>
                                    <button
                                        @click="previewDocPage < previewDocTotalPages && previewDocPage++"
                                        :disabled="previewDocPage === previewDocTotalPages"
                                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all text-sm font-medium text-gray-700 shadow-sm"
                                    >
                                        Next
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Single-page note -->
                                <p v-else-if="!isImage(previewDoc) && previewDocTotalPages === 1" class="text-center text-xs text-gray-400">
                                    Single page document
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
                            <p class="text-xs text-gray-500 truncate max-w-[60%]">
                                <span v-if="previewDoc">{{ previewDoc.name }}</span>
                            </p>
                            <button @click="showDocPreviewModal = false" class="btn btn-secondary btn-sm">{{ lang.t('close') }}</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- CSV/Excel Import Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showCsvModal" class="modal-overlay" @click.self="showCsvModal = false">
                    <div class="modal max-w-2xl w-[95%] p-0 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ lang.t('import_recipients') }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ lang.t('import_desc') }}</p>
                            </div>
                            <button @click="showCsvModal = false" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6">
                            <!-- File Upload Area -->
                            <div v-if="!importedData.length" class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-primary-400 transition-colors cursor-pointer" @click="$refs.fileInput.click()">
                                <input type="file" ref="fileInput" class="hidden" accept=".csv, .xlsx, .xls" @change="handleFileUpload" />
                                <div class="w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-gray-900">{{ lang.t('upload_instruction') }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ lang.t('file_types_supported') }}</p>
                            </div>

                            <!-- Error Message -->
                            <div v-if="importError" class="mt-4 p-4 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-600">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium">{{ importError }}</p>
                            </div>

                            <!-- Data Preview Table -->
                            <div v-if="importedData.length" class="mt-4 border border-gray-100 rounded-xl overflow-hidden">
                                <div class="max-h-[40vh] overflow-y-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-gray-50 sticky top-0">
                                            <tr>
                                                <th class="p-3">
                                                    <input type="checkbox" class="rounded" :checked="selectedImportRows.length === importedData.length" @change="toggleAllImport" />
                                                </th>
                                                <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider">{{ lang.t('display_name') }}</th>
                                                <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider">{{ lang.t('mobile_phone') }}</th>
                                                <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider">{{ lang.t('village') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            <tr v-for="row in importedData" :key="row.id" class="hover:bg-gray-50/50">
                                                <td class="p-3">
                                                    <input type="checkbox" class="rounded" v-model="selectedImportRows" :value="row.id" />
                                                </td>
                                                <td class="p-3 text-sm text-gray-900 font-medium">{{ row.name }}</td>
                                                <td class="p-3 text-sm text-gray-600 font-mono">{{ formatMobile(row.mobile) }}</td>
                                                <td class="p-3 text-sm text-gray-500">{{ row.village || '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button @click="importedData = []; importError = ''" v-if="importedData.length" class="btn btn-secondary btn-sm">{{ lang.t('clear') }}</button>
                                <a href="/recipients_sample.xlsx" download class="btn btn-secondary btn-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Sample File
                                </a>
                            </div>
                            <div class="flex gap-2">
                                <button @click="showCsvModal = false" class="btn btn-secondary btn-sm">{{ lang.t('cancel') }}</button>
                                <button 
                                    v-if="importedData.length" 
                                    @click="saveImportedRecipients" 
                                    :disabled="importingCsv || selectedImportRows.length === 0" 
                                    class="btn btn-primary btn-sm"
                                >
                                    <svg v-if="importingCsv" class="w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ importingCsv ? importStatus : lang.t('import_recipients_count', { count: selectedImportRows.length }) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import PdfCanvas from '@/components/PdfCanvas.vue';
import { useLanguageStore } from '@/stores/language';
import Papa from 'papaparse';
import * as XLSX from 'xlsx';
import { formatMobile } from '@/utils/format';
import { useViewMode } from '@/composables/useViewMode';
import { useMessageTemplate } from '@/composables/useMessageTemplate';
import ViewToggle from '@/components/ViewToggle.vue';

const lang = useLanguageStore();
const { viewMode } = useViewMode('recipients');
const { buildMessage } = useMessageTemplate();

const search = ref('');
const villageFilter = ref('');
const selected = ref([]);
const showAddModal = ref(false);
const showCsvModal = ref(false);
const deleteTarget = ref(null);
const showBulkDeleteModal = ref(false);
const deleting = ref(false);

// Pagination State
// Card view uses multiples of 9 (fits the 3-column grid), table view uses 10/25/50/100.
const perPageOptions = computed(() => (viewMode.value === 'card' ? [9, 18, 27, 36] : [10, 25, 50, 100]));
const perPage = ref(viewMode.value === 'card' ? 9 : 10);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

// Switching view mode swaps the per-page option set and reloads from page 1.
watch(viewMode, () => {
    if (!perPageOptions.value.includes(perPage.value)) {
        perPage.value = perPageOptions.value[0];
    }
    fetchRecipients(1);
});

const totalCount = computed(() => pagination.value.total);

const visiblePages = computed(() => {
    const total = pagination.value.last_page;
    const current = pagination.value.current_page;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    
    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
    if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
    return [1, '...', current - 1, current, current + 1, '...', total];
});

// CSV/Excel Import State
const importedData = ref([]);
const importError = ref('');
const selectedImportRows = ref([]);
const importingCsv = ref(false);
const importStatus = ref('');
const fileInput = ref(null);

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
            mobile: formatMobile(c.tel?.[0] ?? ''),
            skip: false,
        })).filter(c => c.name || c.mobile);

        if (pickedContacts.value.length === 0) return;

        if (pickedContacts.value.length === 1) {
            // Single contact — pre-fill the add modal
            const c = pickedContacts.value[0];
            isEditing.value = false;
            editId.value = null;
            formErrors.value = { mobile: '' };
            const cDigits = String(c.mobile || '').replace(/\D/g, '');
            const cTen = cDigits.startsWith('91') && cDigits.length === 12 ? cDigits.slice(2) : cDigits.slice(-10);
            form.value = { name_en: c.name, name_gu: '', mobile: cTen, village_en: '', village_gu: '' };
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

        await fetchRecipients(1);
        showContactsModal.value = false;
    } catch (err) {
        console.error('Failed to save contacts:', err);
    } finally {
        savingContacts.value = false;
    }
}

// CSV/Excel Import Logic
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    const extension = file.name.split('.').pop().toLowerCase();
    
    if (extension === 'csv') {
        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            complete: (results) => {
                validateAndProcessData(results.data);
            },
            error: (err) => {
                importError.value = "Error parsing CSV: " + err.message;
            }
        });
    } else if (['xlsx', 'xls'].includes(extension)) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet);
            validateAndProcessData(jsonData);
        };
        reader.readAsArrayBuffer(file);
    } else {
        importError.value = "Unsupported file format. Please upload CSV or Excel.";
    }
};

const validateAndProcessData = (data) => {
    importError.value = '';
    importedData.value = [];
    
    if (!data || data.length === 0) {
        importError.value = "The file is empty.";
        return;
    }
    
    // Check for required columns (case-insensitive and trimmed)
    const firstRow = data[0];
    const keys = Object.keys(firstRow);
    const nameKey = keys.find(k => k.trim().toLowerCase() === 'display name');
    const phoneKey = keys.find(k => k.trim().toLowerCase() === 'mobile phone');
    const villageKey = keys.find(k => k.trim().toLowerCase() === 'village');

    if (!nameKey || !phoneKey) {
        importError.value = "Required columns 'Display Name' and 'Mobile Phone' not found.";
        return;
    }

    const seenMobiles = new Set();
    let duplicatesInFile = 0;

    importedData.value = data.map((row, index) => {
        let name = String(row[nameKey] || '').trim();
        let mobile = String(row[phoneKey] || '').trim();
        let village = villageKey ? String(row[villageKey] || '').trim() : '';

        // Clean mobile: remove all non-digit characters except +
        let cleanedMobile = mobile.replace(/[^\d+]/g, '');

        if (!name || !cleanedMobile) return null;

        const digitsOnly = cleanedMobile.replace(/\D/g, '');
        if (digitsOnly.length < 9) return null;

        if (!cleanedMobile.startsWith('+')) {
            if (digitsOnly.length === 10) {
                cleanedMobile = '+91' + digitsOnly;
            } else {
                cleanedMobile = '+' + digitsOnly;
            }
        }

        if (seenMobiles.has(cleanedMobile)) {
            duplicatesInFile++;
            return null;
        }
        seenMobiles.add(cleanedMobile);

        return {
            id: index,
            name: name,
            mobile: cleanedMobile,
            village: village,
        };
    }).filter(r => r !== null);

    if (duplicatesInFile > 0) {
        importError.value = `${duplicatesInFile} duplicate mobile number(s) were removed from the file.`;
    }

    selectedImportRows.value = importedData.value.map(r => r.id);
};

const toggleAllImport = (e) => {
    selectedImportRows.value = e.target.checked ? importedData.value.map(r => r.id) : [];
};

async function saveImportedRecipients() {
    const toAdd = importedData.value.filter(r => selectedImportRows.value.includes(r.id));
    if (!toAdd.length) return;

    importingCsv.value = true;
    importStatus.value = 'Translating to Gujarati…';
    try {
        // Translate unique names and villages to Gujarati in parallel
        const uniqueNames = [...new Set(toAdd.map(r => r.name).filter(Boolean))];
        const uniqueVillages = [...new Set(toAdd.map(r => r.village).filter(Boolean))];

        const [nameResults, villageResults] = await Promise.all([
            Promise.all(uniqueNames.map(t => translateToGujarati(t).then(gu => [t, gu]))),
            Promise.all(uniqueVillages.map(t => translateToGujarati(t).then(gu => [t, gu]))),
        ]);

        const nameMap = Object.fromEntries(nameResults);
        const villageMap = Object.fromEntries(villageResults);

        importStatus.value = `Saving ${toAdd.length} recipients…`;
        const results = await Promise.allSettled(
            toAdd.map(r => axios.post('/api/recipients', {
                name_en: r.name,
                name_gu: nameMap[r.name] || '',
                mobile: String(r.mobile),
                village_en: r.village || '',
                village_gu: villageMap[r.village] || '',
            }))
        );

        const savedCount = results.filter(r => r.status === 'fulfilled').length;
        const duplicateCount = results.filter(
            r => r.status === 'rejected' && r.reason?.response?.status === 422 && r.reason?.response?.data?.errors?.mobile
        ).length;
        const otherErrors = results.filter(
            r => r.status === 'rejected' && !(r.reason?.response?.status === 422 && r.reason?.response?.data?.errors?.mobile)
        ).length;

        await fetchRecipients(1);

        if (duplicateCount > 0 || otherErrors > 0) {
            let msg = `${savedCount} recipient(s) saved.`;
            if (duplicateCount > 0) msg += ` ${duplicateCount} skipped — mobile number already exists.`;
            if (otherErrors > 0) msg += ` ${otherErrors} failed due to other errors.`;
            importError.value = msg;
            importedData.value = [];
            selectedImportRows.value = [];
        } else {
            showCsvModal.value = false;
            importedData.value = [];
            selectedImportRows.value = [];
        }
    } catch (err) {
        console.error('Failed to save imported recipients:', err);
    } finally {
        importingCsv.value = false;
    }
}

// Document Preview Modal State
const showDocPreviewModal = ref(false);
const previewDocSelectedId = ref(null);
const previewDocPage = ref(1);
const previewDocTotalPages = ref(1);
const previewDocContainerRef = ref(null);
const previewDocW = ref(500);
const previewDocAspectRatio = ref(1.4142); // A4 default
const previewDocH = computed(() => Math.round(previewDocW.value * previewDocAspectRatio.value));

const previewDoc = computed(() => docs.value.find(d => d.id === previewDocSelectedId.value));

const previewDocFilteredFields = computed(() => {
    if (!previewDoc.value?.fields) return [];
    return previewDoc.value.fields.filter(f => (f.page_number || 1) === previewDocPage.value);
});

function getPreviewDocFieldValue(field) {
    if (!selectedRecipient.value || !previewDoc.value) return '';
    const docLang = previewDoc.value.language || 'en';
    if (field.field_type === 'village') {
        return docLang === 'gu' ? selectedRecipient.value.village_gu : selectedRecipient.value.village_en;
    }
    return docLang === 'gu' ? selectedRecipient.value.name_gu : selectedRecipient.value.name_en;
}

function onPreviewDocPdfRendered({ width, height }) {
    previewDocAspectRatio.value = height / width;
}

function onPreviewDocImageLoad(e) {
    const { naturalWidth, naturalHeight } = e.target;
    if (naturalWidth && naturalHeight) previewDocAspectRatio.value = naturalHeight / naturalWidth;
}

function openDocPreviewModal(recipient) {
    selectedRecipient.value = recipient;
    previewDocPage.value = 1;
    previewDocTotalPages.value = 1;
    previewDocAspectRatio.value = 1.4142;
    if (!previewDocSelectedId.value && docs.value.length > 0) {
        previewDocSelectedId.value = docs.value[0].id;
    }
    showDocPreviewModal.value = true;
    if (docs.value.length === 0) fetchDocuments();
}

watch(previewDocSelectedId, () => {
    previewDocPage.value = 1;
    previewDocTotalPages.value = 1;
    previewDocAspectRatio.value = 1.4142;
});

watch(showDocPreviewModal, (val) => {
    if (!val) return;
    setTimeout(() => {
        const el = previewDocContainerRef.value;
        if (!el) return;
        previewDocW.value = el.clientWidth || 500;
        const ro = new ResizeObserver(([entry]) => {
            previewDocW.value = entry.contentRect.width || 500;
        });
        ro.observe(el);
    }, 100);
});

// WhatsApp Modal State
const showWhatsAppModal = ref(false);
const showLinksModal = ref(false);
const selectedRecipient = ref(null);
const selectedDocId = ref(null);
const docs = ref([]);
const loadingDocs = ref(false);
const sending = ref(false);
const saving = ref(false);
const formErrors = ref({ mobile: '' });
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
let suppressAutoConvert = false;

async function translateToGujarati(text) {
    if (!text) return '';
    try {
        const res = await fetch(
            `https://inputtools.google.com/request?text=${encodeURIComponent(text)}&itc=gu-t-i0-und&num=1&cp=0&cs=1&ie=utf-8&oe=utf-8`
        );
        const data = await res.json();
        if (data[0] === 'SUCCESS' && data[1]?.[0]?.[1]?.[0]) return data[1][0][1][0];
    } catch {}
    return '';
}

async function autoConvert(type = 'name') {
    const isName = type === 'name';
    const text = isName ? (form.value.name_en || '').trim() : (form.value.village_en || '').trim();
    if (!text) return;

    if (isName) autoConverting.value = true;
    else autoConvertingVillage.value = true;

    try {
        const result = await translateToGujarati(text);
        if (result) {
            if (isName) form.value.name_gu = result;
            else form.value.village_gu = result;
        }
    } finally {
        if (isName) autoConverting.value = false;
        else autoConvertingVillage.value = false;
    }
}

watch(() => form.value.name_en, (val) => {
    clearTimeout(autoConvertTimer);
    if (suppressAutoConvert) return;
    if (!val || !val.trim()) { form.value.name_gu = ''; return; }
    autoConvertTimer = setTimeout(() => autoConvert('name'), 700);
});

watch(() => form.value.village_en, (val) => {
    clearTimeout(autoConvertVillageTimer);
    if (suppressAutoConvert) return;
    if (!val || !val.trim()) { form.value.village_gu = ''; return; }
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
        const { data } = await axios.get('/api/documents', {
            params: { per_page: 100 } // Get more for the selector
        });
        // Handle both paginated and non-paginated (for safety)
        const docsList = data.data || data;
        docs.value = docsList;
        if (docsList.length > 0 && !selectedDocId.value) {
            selectedDocId.value = docsList[0].id;
        }
    } catch (err) {
        console.error('Failed to fetch documents:', err);
    } finally {
        loadingDocs.value = false;
    }
}

const recipients = ref([]);
const globalStats = ref({ total_sent: 0, total_pending: 0 });

// Filtering is now handled on the backend via fetchRecipients

async function fetchRecipients(page = 1) {
    try {
        const { data } = await axios.get('/api/recipients', {
            params: {
                page,
                per_page: perPage.value,
                search: search.value || undefined,
                village: villageFilter.value || undefined
            }
        });
        recipients.value = data.data;
        globalStats.value = data.stats || { total_sent: 0, total_pending: 0 };
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            from: data.from,
            to: data.to
        };
    } catch (err) {
        console.error('Failed to fetch recipients:', err);
    }
}

let searchTimer = null;
function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchRecipients(1), 500);
}

function openAddModal() {
    isEditing.value = false;
    editId.value = null;
    form.value = { name_en: '', name_gu: '', mobile: '', village_en: '', village_gu: '' };
    formErrors.value = { mobile: '' };
    showAddModal.value = true;
}

function editRecipient(recipient) {
    isEditing.value = true;
    editId.value = recipient.id;
    suppressAutoConvert = true;
    formErrors.value = { mobile: '' };
    const digits = String(recipient.mobile || '').replace(/\D/g, '');
    form.value = {
        name_en: recipient.name_en,
        name_gu: recipient.name_gu,
        mobile: digits.startsWith('91') && digits.length === 12 ? digits.slice(2) : digits.slice(-10),
        village_en: recipient.village_en,
        village_gu: recipient.village_gu
    };
    showAddModal.value = true;
    nextTick(() => { suppressAutoConvert = false; });
}

function toggleAll(e) {
    selected.value = e.target.checked ? recipients.value.map(r => r.id) : [];
}

async function saveRecipient() {
    if (!form.value.name_en || !form.value.mobile) return;

    const digits = form.value.mobile.replace(/\D/g, '');
    if (digits.length !== 10) {
        formErrors.value.mobile = 'Please enter a valid 10-digit mobile number.';
        return;
    }

    formErrors.value = { mobile: '' };
    saving.value = true;
    const payload = { ...form.value, mobile: '+91' + digits };
    try {
        if (isEditing.value) {
            await axios.put(`/api/recipients/${editId.value}`, payload);
            await fetchRecipients(pagination.value.current_page);
        } else {
            await axios.post('/api/recipients', payload);
            await fetchRecipients(1);
        }
        showAddModal.value = false;
        form.value = { name_en: '', name_gu: '', mobile: '', village_en: '', village_gu: '' };
    } catch (err) {
        if (err.response?.status === 422 && err.response.data?.errors?.mobile) {
            formErrors.value.mobile = err.response.data.errors.mobile[0];
        } else {
            console.error('Failed to save recipient:', err);
        }
    } finally {
        saving.value = false;
    }
}

function confirmDelete(recipient) {
    deleteTarget.value = recipient;
}

async function deleteRecipient() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    try {
        await axios.delete(`/api/recipients/${deleteTarget.value.id}`);
        recipients.value = recipients.value.filter(r => r.id !== deleteTarget.value.id);
        selected.value = selected.value.filter(id => id !== deleteTarget.value.id);
        deleteTarget.value = null;
    } catch (err) {
        console.error('Failed to delete recipient:', err);
    } finally {
        deleting.value = false;
    }
}

function bulkDelete() {
    if (!selected.value.length) return;
    showBulkDeleteModal.value = true;
}

async function confirmBulkDelete() {
    if (!selected.value.length) return;
    deleting.value = true;
    try {
        await Promise.all(selected.value.map(id => axios.delete(`/api/recipients/${id}`)));
        // Refresh current page
        await fetchRecipients(pagination.value.current_page);
        selected.value = [];
        showBulkDeleteModal.value = false;
    } catch (err) {
        console.error('Failed to delete recipients:', err);
    } finally {
        deleting.value = false;
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
        const link = `${window.location.origin}/doc/view/${token}`;
        const body = await buildMessage(selectedRecipient.value, selectedDoc.value, link);
        const message = encodeURIComponent(body);

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

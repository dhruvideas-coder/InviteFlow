<template>
    <div class="flex flex-col h-full gap-6">

        <!-- Header + Step Indicator (single row on desktop) -->
        <div class="flex flex-col lg:flex-row lg:items-center gap-6 lg:gap-8">
            <div class="flex items-center gap-3 shrink-0">
                <RouterLink to="/documents" class="btn btn-secondary p-2 rounded-xl">
                    <ChevronLeft class="w-5 h-5" />
                </RouterLink>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ isEdit ? lang.t('edit_document') : lang.t('create_document') }}</h1>
                    <nav class="flex text-xs text-gray-500 mt-0.5">
                        <RouterLink to="/" class="hover:text-primary-600">{{ lang.t('home') }}</RouterLink>
                        <span class="mx-1.5">/</span>
                        <RouterLink to="/documents" class="hover:text-primary-600">{{ lang.t('documents') }}</RouterLink>
                        <span class="mx-1.5">/</span>
                        <span class="text-gray-900 font-medium">{{ isEdit ? lang.t('edit') : lang.t('create') }}</span>
                    </nav>
                </div>
            </div>
            
            <!-- Step Indicator -->
            <div class="flex items-center justify-center flex-1 max-w-2xl mx-auto w-full px-4 lg:px-0">
            <template v-for="(step, i) in steps" :key="i">
                <div class="flex flex-col items-center relative z-10">
                    <div :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 border-2',
                        currentStep > i + 1 
                            ? 'bg-emerald-500 border-emerald-500 text-white' 
                            : currentStep === i + 1 
                                ? 'bg-primary-600 border-primary-600 text-white shadow-lg shadow-primary-100' 
                                : 'bg-white border-gray-200 text-gray-400'
                    ]">
                        <Check v-if="currentStep > i + 1" class="w-5 h-5" />
                        <span v-else>{{ i + 1 }}</span>
                    </div>
                    <span :class="[
                        'text-[11px] font-semibold mt-2 absolute -bottom-6 whitespace-nowrap transition-colors',
                        currentStep === i + 1 ? 'text-primary-600' : 'text-gray-400'
                    ]">{{ lang.t(step.toLowerCase()) }}</span>
                </div>
                <div v-if="i < steps.length - 1" :class="[
                    'h-0.5 flex-1 mx-2 transition-all duration-500',
                    currentStep > i + 1 ? 'bg-emerald-500' : 'bg-gray-100'
                ]"></div>
            </template>
            </div>

            <!-- Publish (only on the final step) -->
            <button v-if="currentStep === 4" @click="saveDocument('active')" class="btn btn-primary px-6 w-full lg:w-auto shrink-0" :disabled="saving">
                <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
                <span v-else>{{ lang.t('publish_document') }}</span>
            </button>
        </div>

        <!-- Content Area -->
        <div class="flex-1 mt-8">
            
            <!-- STEP 1: Name & Description -->
            <div v-if="currentStep === 1" class="max-w-xl mx-auto">
                <div class="card p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ lang.t('name_your_document') }}</h2>
                    <p class="text-sm text-gray-500 mb-8">{{ lang.t('document_name_desc') }}</p>
                    
                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="label">{{ lang.t('document_title') }} <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" class="input py-3" placeholder="e.g. Wedding Invitation" />
                        </div>
                        <div class="form-group">
                            <label class="label">{{ lang.t('description') }} <span class="text-gray-400 font-normal">({{ lang.t('optional') }})</span></label>
                            <textarea v-model="form.description" class="input min-h-[120px] py-3 resize-none" :placeholder="lang.t('description_placeholder')"></textarea>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <button @click="currentStep = 2" :disabled="!form.name" class="btn btn-primary px-8 py-3">
                            {{ lang.t('continue') }}
                            <ArrowRight class="w-4 h-4 ml-2" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Upload Document -->
            <div v-if="currentStep === 2" class="max-w-4xl mx-auto space-y-6">
                <div class="card p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ lang.t('upload') }}</h2>
                    <p class="text-sm text-gray-500 mb-8">Upload a PDF or Image for personalization. Max 50 MB.</p>

                    <!-- Dropzone -->
                    <div 
                        class="border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center hover:border-primary-400 hover:bg-primary-50/30 transition-all cursor-pointer group"
                        @click="fileInput?.click()"
                    >
                        <input ref="fileInput" type="file" class="hidden" accept=".pdf,image/*" @change="handleFileSelect" />
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center mb-4 group-hover:bg-white group-hover:shadow-sm transition-all">
                                <Upload class="w-8 h-8 text-gray-400 group-hover:text-primary-500" />
                            </div>
                            <h3 class="text-base font-bold text-gray-900">{{ lang.t('drop_files_here') }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ lang.t('upload_desc') }}</p>
                            <button class="btn btn-secondary mt-6 px-6">{{ lang.t('choose_files') }}</button>
                        </div>
                    </div>

                    <!-- Previously uploaded documents -->
                    <div v-if="selectedFile" class="mt-8">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">{{ lang.t('uploaded_document') }}</h3>
                        <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-xl">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center shrink-0">
                                <FileText class="w-5 h-5 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-emerald-900 truncate">{{ selectedFile.name }}</p>
                                <p class="text-xs text-emerald-600 font-medium">{{ fileSizeMB }} MB</p>
                            </div>
                            <span class="badge bg-white text-emerald-600 font-bold border border-emerald-100 px-3 py-1">{{ lang.t('uploaded') }}</span>
                            <button @click="clearFile" class="p-1.5 text-emerald-400 hover:text-red-500 transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Auto-detected language -->
                        <div class="mt-4 flex items-center gap-3 p-4 rounded-xl border" :class="detectingLanguage ? 'bg-gray-50 border-gray-100' : 'bg-primary-50/40 border-primary-100'">
                            <Loader2 v-if="detectingLanguage" class="w-5 h-5 text-primary-500 animate-spin shrink-0" />
                            <Sparkles v-else class="w-5 h-5 text-primary-500 shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Detected Language</p>
                                <p v-if="detectingLanguage" class="text-sm font-bold text-gray-500">Detecting…</p>
                                <p v-else class="text-sm font-bold text-gray-900">
                                    {{ form.language === 'gu' ? 'Gujarati' : 'English' }}
                                    <span v-if="!languageAutoDetected" class="text-[10px] text-amber-500 font-medium ml-1">(couldn't auto-detect — set manually)</span>
                                </p>
                            </div>
                            <!-- Override in case detection is wrong -->
                            <select v-model="form.language" :disabled="detectingLanguage" class="select w-auto text-xs py-1.5 bg-white border-gray-200">
                                <option value="gu">Gujarati</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-between">
                        <button @click="currentStep = 1" class="btn btn-secondary px-6">{{ lang.t('cancel') }}</button>
                        <button @click="currentStep = 3" :disabled="!selectedFile || detectingLanguage" class="btn btn-primary px-8 py-3">
                            {{ detectingLanguage ? 'Detecting language…' : lang.t('continue') }}
                            <ArrowRight v-if="!detectingLanguage" class="w-4 h-4 ml-2" />
                        </button>
                    </div>
                </div>

                <!-- Preview below upload -->
                <div v-if="selectedFile" class="card overflow-hidden">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Document Preview</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div v-if="!isImageFile && pdfTotalPages > 1" class="flex items-center gap-1.5 bg-white p-1 rounded-lg border border-gray-100">
                                <button @click="prevPage" :disabled="currentPage === 1" class="p-1 rounded hover:bg-gray-50 disabled:opacity-30 transition-all">
                                    <ChevronLeft class="w-3.5 h-3.5" />
                                </button>
                                <span class="text-[10px] font-bold text-gray-600 min-w-[40px] text-center">{{ lang.t('page') }} {{ currentPage }} / {{ pdfTotalPages }}</span>
                                <button @click="nextPage" :disabled="currentPage === pdfTotalPages" class="p-1 rounded hover:bg-gray-50 disabled:opacity-30 transition-all">
                                    <ChevronRight class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div ref="step2ContainerRef" class="p-4 sm:p-8 bg-slate-800 flex justify-center">
                         <div class="relative shadow-2xl">
                             <img v-if="isImageFile" :src="previewSrc" class="max-w-full h-auto" />
                             <PdfCanvas v-else :src="previewSrc" :width="step2CanvasW" :page="currentPage" @rendered="onPdfRendered" @total-pages="n => pdfTotalPages = n" />
                         </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Add Fields -->
            <div v-if="currentStep === 3" class="h-full flex flex-col gap-6">
                <div class="flex flex-col lg:flex-row gap-6 h-full">

                    <!-- Mobile backdrop (only when the drawer is open) -->
                    <div
                        v-if="mobileSidebarOpen"
                        @click="mobileSidebarOpen = false"
                        class="lg:hidden fixed inset-0 bg-black/40 z-40"
                    ></div>

                    <!-- Sidebar (slide-in drawer on mobile, static column on desktop) -->
                    <div :class="[
                        'shrink-0 flex flex-col gap-4',
                        'lg:relative lg:w-80 lg:translate-x-0 lg:p-0 lg:bg-transparent lg:shadow-none lg:overflow-visible lg:z-auto',
                        'fixed inset-y-0 left-0 z-50 w-[85%] max-w-sm bg-gray-50 p-4 overflow-y-auto overflow-x-hidden shadow-2xl transition-transform duration-300',
                        mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    ]">
                        <!-- Mobile drawer close control -->
                        <div class="lg:hidden flex justify-end">
                            <button @click="mobileSidebarOpen = false" class="flex items-center gap-1 p-2 text-sm font-bold text-gray-500 hover:text-gray-700">
                                <ChevronLeft class="w-5 h-5" /> {{ lang.t('close') }}
                            </button>
                        </div>

                        <div class="card p-6">
                            <h3 class="font-bold text-gray-900 mb-2">{{ lang.t('add_fields') }}</h3>
                            <p class="text-sm text-gray-500 mb-6">{{ lang.t('add_fields_desc') }}</p>
                            
                            <div class="space-y-4">
                                <button @click="addField('name')" class="w-full flex items-center gap-3 p-4 rounded-xl border-2 border-primary-100 bg-primary-50/30 text-primary-700 hover:bg-primary-50 transition-all text-left group mb-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary-600 text-white flex items-center justify-center shadow-lg shadow-primary-200">
                                        <User class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ lang.t('recipient_name') }}</p>
                                        <p class="text-[11px] opacity-70">Click & Drag to position</p>
                                    </div>
                                    <Plus class="w-4 h-4 ml-auto opacity-40 group-hover:opacity-100" />
                                </button>

                                <button @click="addField('village')" class="w-full flex items-center gap-3 p-4 rounded-xl border-2 border-amber-100 bg-amber-50/30 text-amber-700 hover:bg-amber-50 transition-all text-left group">
                                    <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-200">
                                        <MapPin class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ lang.t('village_name') }}</p>
                                        <p class="text-[11px] opacity-70">Place village location</p>
                                    </div>
                                    <Plus class="w-4 h-4 ml-auto opacity-40 group-hover:opacity-100" />
                                </button>
                            </div>
                        </div>

                        <div class="card p-6 flex-1 flex flex-col overflow-hidden">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold text-gray-900">{{ lang.t('placed_fields') }}</h3>
                                <span class="badge bg-gray-100 text-gray-600 font-bold px-2">{{ fields.length }}</span>
                            </div>
                            
                            <div class="flex-1 overflow-y-auto space-y-3 pr-2">
                                <div v-for="(field, i) in fields" :key="i" 
                                    class="p-4 rounded-xl border border-gray-100 bg-white hover:border-primary-200 hover:shadow-sm transition-all group"
                                    :class="{'border-primary-500 ring-1 ring-primary-100': selectedFieldIndex === i}"
                                    @click="selectedFieldIndex = i"
                                >
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center" :class="field.field_type === 'village' ? 'bg-amber-100 text-amber-600' : 'bg-primary-100 text-primary-600'">
                                            <User v-if="field.field_type === 'name'" class="w-4 h-4" />
                                            <MapPin v-else class="w-4 h-4" />
                                        </div>
                                        <input v-model="field.label" class="flex-1 min-w-0 bg-transparent border-none p-0 text-sm font-bold focus:ring-0 text-gray-800" />
                                        <button @click.stop="removeField(i)" class="shrink-0 p-1 text-gray-300 hover:text-red-500 transition-colors">
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="text-[10px] text-gray-400 font-bold uppercase">{{ lang.t('width') }} %</label>
                                            <input type="number" v-model.number="field.width_percent" class="w-full bg-gray-50 border-none rounded-lg py-1 px-2 text-xs font-medium focus:bg-white focus:ring-1 focus:ring-primary-100" />
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-gray-400 font-bold uppercase">{{ lang.t('height') }} %</label>
                                            <input type="number" v-model.number="field.height_percent" class="w-full bg-gray-50 border-none rounded-lg py-1 px-2 text-xs font-medium focus:bg-white focus:ring-1 focus:ring-primary-100" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-[10px] text-gray-400 font-bold uppercase mb-2 block">{{ lang.t('color') }}</label>
                                        <div class="flex flex-wrap gap-2">
                                            <!-- Black & White always -->
                                            <button 
                                                v-for="c in ['#000000', '#FFFFFF']" :key="c"
                                                @click.stop="field.color = c"
                                                class="w-6 h-6 rounded-full border border-gray-200 transition-transform hover:scale-110"
                                                :class="{'ring-2 ring-primary-500 ring-offset-1': field.color === c}"
                                                :style="{ backgroundColor: c }"
                                                :title="c === '#000000' ? 'Black' : 'White'"
                                            ></button>
                                            
                                            <!-- Extracted Colors -->
                                            <button 
                                                v-for="c in extractedColors" :key="c"
                                                @click.stop="field.color = c"
                                                class="w-6 h-6 rounded-full border border-gray-100 transition-transform hover:scale-110"
                                                :class="{'ring-2 ring-primary-500 ring-offset-1': field.color === c}"
                                                :style="{ backgroundColor: c }"
                                            ></button>

                                            <!-- Custom Hex -->
                                            <div class="relative w-6 h-6 rounded-full border border-gray-200 overflow-hidden">
                                                <input type="color" v-model="field.color" class="absolute -inset-1 w-[200%] h-[200%] cursor-pointer" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="!fields.length" class="text-center py-12 flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center">
                                        <MousePointer2 class="w-5 h-5 text-gray-300" />
                                    </div>
                                    <p class="text-xs text-gray-400 font-medium">No fields placed yet</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-auto">
                            <button @click="currentStep = 2" class="btn btn-secondary flex-1">Back</button>
                            <button @click="currentStep = 4" :disabled="!fields.length" class="btn btn-primary flex-1">Continue</button>
                        </div>
                    </div>

                    <!-- Editor Area -->
                    <div class="flex-1 card overflow-hidden flex flex-col bg-slate-900">
                         <div class="bg-white px-5 py-3 border-b border-gray-100 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></div>
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">DOCUMENT EDITOR</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div v-if="!isImageFile && pdfTotalPages > 1" class="flex items-center gap-1.5 bg-gray-50 p-1 rounded-lg border border-gray-100">
                                    <button @click="prevPage" :disabled="currentPage === 1" class="p-1 rounded hover:bg-white disabled:opacity-30 transition-all">
                                        <ChevronLeft class="w-3.5 h-3.5" />
                                    </button>
                                    <span class="text-[10px] font-bold text-gray-600 min-w-[40px] text-center">PAGE {{ currentPage }} / {{ pdfTotalPages }}</span>
                                    <button @click="nextPage" :disabled="currentPage === pdfTotalPages" class="p-1 rounded hover:bg-white disabled:opacity-30 transition-all">
                                        <ChevronRight class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <span class="text-[11px] font-bold text-gray-400">{{ fields.length }} FIELDS ON THIS DOCUMENT</span>
                                <div class="h-4 w-px bg-gray-100"></div>
                            </div>
                        </div>

                        <div ref="canvasContainerRef" class="flex-1 overflow-auto p-4 sm:p-8 lg:p-12 flex justify-center items-start">
                            <div 
                                ref="canvasRef"
                                class="relative bg-white shadow-2xl"
                                :style="{ width: canvasW + 'px', height: canvasH + 'px' }"
                                @click="handleCanvasClick"
                            >
                                <img v-if="isImageFile" :src="previewSrc" @load="handleImageLoad" class="w-full h-full select-none pointer-events-none" />
                                <PdfCanvas v-else :src="previewSrc" :width="canvasW" :page="currentPage" @rendered="onPdfRendered" @total-pages="n => pdfTotalPages = n" class="pointer-events-none" />

                                <!-- Fields -->
                                <div 
                                    v-for="(field, i) in fields" :key="i"
                                    v-show="(field.page_number ?? 1) === currentPage"
                                    class="absolute border-2 flex items-center gap-2 cursor-move group select-none shadow-lg"
                                    :class="[
                                        selectedFieldIndex === i ? 'ring-2 ring-offset-2' : '',
                                        field.field_type === 'village' 
                                            ? 'border-amber-500 bg-amber-50/90 ring-amber-300' 
                                            : 'border-primary-500 bg-primary-50/90 ring-primary-300'
                                    ]"
                                    :style="{
                                        left: field.x_percent + '%',
                                        top: field.y_percent + '%',
                                        width: field.width_percent + '%',
                                        height: field.height_percent + '%',
                                        fontSize: Math.max(8, Math.round((field.height_percent / 100 * canvasH) * 0.52)) + 'px',
                                        zIndex: 10 + i
                                    }"
                                    @mousedown.stop.prevent="startDrag($event, i)"
                                    @touchstart.stop.prevent="startDrag($event, i)"
                                    @click.stop="selectedFieldIndex = i"
                                >
                                    <div class="flex-1 flex items-center gap-2 px-3 overflow-hidden">
                                        <User v-if="field.field_type === 'name'" :size="Math.max(10, Math.round((field.height_percent / 100 * canvasH) * 0.48))" class="shrink-0" :style="{ color: field.color }" />
                                        <MapPin v-else :size="Math.max(10, Math.round((field.height_percent / 100 * canvasH) * 0.48))" class="shrink-0" :style="{ color: field.color }" />
                                        <span class="font-bold truncate" :style="{ color: field.color }">{{ getTranslatedLabel(field) }}</span>
                                    </div>

                                    <!-- Delete Icon -->
                                    <button
                                        @click.stop="removeField(i)"
                                        @touchstart.stop.prevent="removeField(i)"
                                        class="absolute -top-2.5 -right-2.5 w-6 h-6 lg:w-5 lg:h-5 text-white rounded-full flex items-center justify-center transition-opacity shadow-md z-30"
                                        :class="[
                                            field.field_type === 'village' ? 'bg-amber-600' : 'bg-red-500',
                                            selectedFieldIndex === i ? 'opacity-100' : 'opacity-0 lg:group-hover:opacity-100'
                                        ]"
                                    >
                                        <X class="w-3 h-3 stroke-[3px]" />
                                    </button>

                                    <!-- Resize handle -->
                                    <div 
                                        v-if="selectedFieldIndex === i"
                                        class="absolute -right-2 -bottom-2 w-5 h-5 lg:w-4 lg:h-4 rounded-full border-2 border-white shadow-md cursor-nwse-resize touch-none z-20"
                                        :class="field.field_type === 'village' ? 'bg-amber-600' : 'bg-primary-600'"
                                        @mousedown.stop.prevent="startResize($event, i)"
                                        @touchstart.stop.prevent="startResize($event, i)"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile floating toggle to reopen the fields drawer -->
                <button
                    v-if="!mobileSidebarOpen"
                    @click="mobileSidebarOpen = true"
                    class="lg:hidden fixed bottom-6 right-6 z-40 btn btn-primary px-5 py-3 rounded-full shadow-xl flex items-center gap-2"
                >
                    <Plus class="w-5 h-5" />
                    <span class="font-bold">{{ lang.t('add_fields') }}</span>
                </button>
            </div>

            <!-- STEP 4: Confirm -->
            <div v-if="currentStep === 4" class="max-w-4xl mx-auto space-y-6 pb-20">
                <div class="card p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center">
                            <CheckCircle class="w-8 h-8" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ lang.t('review_confirm') }}</h2>
                            <p class="text-sm text-gray-500">Please review the details before finalizing your document.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">General Details</h3>
                                <div class="bg-gray-50 rounded-2xl p-6 space-y-4">
                                    <div>
                                        <label class="text-[10px] text-gray-400 font-bold uppercase">Title</label>
                                        <p class="text-base font-bold text-gray-900">{{ form.name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-400 font-bold uppercase">Description</label>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ form.description || 'No description provided' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Document & Fields</h3>
                                <div class="bg-gray-50 rounded-2xl p-6 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="text-[10px] text-gray-400 font-bold uppercase">File</label>
                                            <p class="text-sm font-bold text-gray-900">{{ selectedFile?.name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <label class="text-[10px] text-gray-400 font-bold uppercase">Size</label>
                                            <p class="text-sm font-bold text-gray-900">{{ fileSizeMB }} MB</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-400 font-bold uppercase">Total Fields</label>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span v-for="field in fields" :key="field.id" 
                                                class="badge font-bold px-3 py-1 flex items-center gap-2"
                                                :style="{ backgroundColor: (field.color || '#3b82f6') + '10', color: field.color || '#3b82f6', borderColor: (field.color || '#3b82f6') + '30' }"
                                            >
                                                <div class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: field.color || '#3b82f6' }"></div>
                                                {{ field.label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pt-4 border-t border-gray-100">
                                        <label class="text-[10px] text-gray-400 font-bold uppercase mb-1">Language</label>
                                        <p class="text-sm font-bold text-gray-900">{{ form.language === 'gu' ? 'Gujarati' : 'English' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Document Summary</h3>
                            <div class="card overflow-hidden bg-slate-100 flex justify-center p-6 min-h-[400px]">
                                <div class="relative shadow-lg bg-white">
                                     <img v-if="isImageFile" :src="previewSrc" class="max-w-full h-auto" />
                                     <PdfCanvas v-else :src="previewSrc" :width="400" />
                                     
                                     <!-- Preview of fields with sample values -->
                                     <div 
                                        v-for="field in fields" :key="field.id"
                                        class="absolute flex items-center justify-center text-center overflow-hidden whitespace-nowrap px-1"
                                        :style="{
                                            left: field.x_percent + '%',
                                            top: field.y_percent + '%',
                                            width: field.width_percent + '%',
                                            height: field.height_percent + '%',
                                            backgroundColor: (field.color || '#3b82f6') + '10',
                                            borderColor: field.color || '#3b82f6',
                                            borderWidth: '1px',
                                            borderStyle: 'dashed',
                                            color: field.color || '#000000',
                                            fontSize: Math.max(6, Math.round((field.height_percent / 100 * 400) * 0.6)) + 'px',
                                            fontWeight: 'bold'
                                        }"
                                    >
                                        {{ getTranslatedLabel(field) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 pt-8 border-t border-gray-100 flex justify-between">
                        <button @click="currentStep = 3" class="btn btn-secondary px-8">{{ lang.t('back_to_editor') }}</button>
                        <div class="flex gap-4">
                            <button @click="saveDocument('draft')" class="btn btn-secondary px-8" :disabled="saving">{{ lang.t('save_as_draft') }}</button>
                            <button @click="saveDocument('active')" class="btn btn-primary px-8" :disabled="saving">
                                <Loader2 v-if="saving" class="w-4 h-4 animate-spin" />
                                <span v-else>{{ lang.t('publish_document') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watchEffect, nextTick, watch } from 'vue';
import { useRouter, useRoute, RouterLink } from 'vue-router';
import { 
    ChevronLeft, ChevronRight, Check, Upload, FileText, Trash2, ArrowRight, 
    Plus, User, X, MousePointer2, Loader2, CheckCircle, MapPin, Sparkles
} from 'lucide-vue-next';
import axios from 'axios';
import PdfCanvas from '@/components/PdfCanvas.vue';
import { useLanguageStore } from '@/stores/language';
import { detectDocumentLanguage } from '@/utils/languageDetect';

const router = useRouter();
const route = useRoute();
const lang = useLanguageStore();

const isEdit = computed(() => !!route.params.id);
const currentStep = ref(1);
const steps = ['Details', 'Upload', 'Fields', 'Review'];

const form = ref({
    name: '',
    description: '',
    status: 'draft',
    language: lang.currentLocale
});

// Language is auto-detected from the uploaded file (see handleFileSelect).
const detectingLanguage = ref(false);
const languageAutoDetected = ref(false);

const selectedFile = ref(null);
const previewSrc = ref(null);
const fileInput = ref(null);
const saving = ref(false);

const fields = ref([]);
const selectedFieldIndex = ref(null);

// On mobile the fields panel is a slide-in drawer: open it automatically when
// the user reaches the editor, and close it once a field is selected so the
// document preview is visible behind it. (Ignored on desktop via lg: classes.)
const mobileSidebarOpen = ref(false);
watch(currentStep, (s) => { if (s === 3) mobileSidebarOpen.value = true; });
watch(selectedFieldIndex, (v) => { if (v !== null) mobileSidebarOpen.value = false; });

// Canvas/Editor Logic
const canvasRef = ref(null);
const canvasContainerRef = ref(null);
const canvasW = ref(800);
const step2CanvasW = ref(700);
const canvasH = ref(1000);
const pdfAspectRatio = ref(1.414); // A4 default

const isImageFile = computed(() => selectedFile.value?.type?.startsWith('image/'));
const fileSizeMB = computed(() => selectedFile.value ? (selectedFile.value.size / (1024 * 1024)).toFixed(2) : '0');

// PDF/Image loading
const onPdfRendered = ({ width, height }) => {
    pdfAspectRatio.value = height / width;
    canvasH.value = canvasW.value * pdfAspectRatio.value;
    
    // Extract colors from the rendered canvas
    nextTick(() => {
        if (canvasRef.value) {
            const canvas = canvasRef.value.querySelector('canvas');
            if (canvas) extractColorsFromCanvas(canvas);
        }
    });
};

const extractedColors = ref([]);

function extractColorsFromCanvas(canvas) {
    try {
        const ctx = canvas.getContext('2d');
        const w = canvas.width;
        const h = canvas.height;
        
        // Sample points to find colors
        const imageData = ctx.getImageData(0, 0, w, h).data;
        const colorCounts = {};
        
        // Sample every 20th pixel to save performance
        for (let i = 0; i < imageData.length; i += 4 * 20) {
            const r = imageData[i];
            const g = imageData[i+1];
            const b = imageData[i+2];
            const a = imageData[i+3];
            
            if (a < 128) continue; // Skip transparent
            
            // Round to reduce number of unique colors
            const rd = Math.round(r / 10) * 10;
            const gd = Math.round(g / 10) * 10;
            const bd = Math.round(b / 10) * 10;
            
            const hex = "#" + ((1 << 24) + (rd << 16) + (gd << 8) + bd).toString(16).slice(1);
            
            // Skip very light/white and very dark/black as we provide them separately
            const brightness = (rd * 299 + gd * 587 + bd * 114) / 1000;
            if (brightness > 240 || brightness < 15) continue;
            
            colorCounts[hex] = (colorCounts[hex] || 0) + 1;
        }
        
        // Sort by frequency and take top 8
        const sortedColors = Object.keys(colorCounts).sort((a, b) => colorCounts[b] - colorCounts[a]);
        extractedColors.value = sortedColors.slice(0, 8);
    } catch (e) {
        console.warn('Could not extract colors:', e);
    }
}

const handleImageLoad = (e) => {
    const img = e.target;
    const canvas = document.createElement('canvas');
    canvas.width = img.naturalWidth;
    canvas.height = img.naturalHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    extractColorsFromCanvas(canvas);
};

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        // Release the previous preview before swapping in the new file.
        if (previewSrc.value?.startsWith('blob:')) URL.revokeObjectURL(previewSrc.value);
        selectedFile.value = file;
        previewSrc.value = URL.createObjectURL(file);
        autoDetectLanguage(file);
    }
};

// Each detection claims a token; a slow earlier run can't clobber a newer file.
let detectSeq = 0;

// Read the document and auto-pick English vs Gujarati (mostly-used wins).
const autoDetectLanguage = async (file) => {
    const seq = ++detectSeq;
    detectingLanguage.value = true;
    languageAutoDetected.value = false;
    try {
        const detected = await detectDocumentLanguage(file);
        if (seq !== detectSeq) return; // a newer file was selected meanwhile
        if (detected) {
            form.value.language = detected;
            languageAutoDetected.value = true;
        }
        // If detection returns null we leave languageAutoDetected false so the
        // UI prompts the user to set it manually instead of trusting a stale value.
    } finally {
        if (seq === detectSeq) detectingLanguage.value = false;
    }
};

const clearFile = () => {
    detectSeq++; // invalidate any in-flight detection
    if (previewSrc.value?.startsWith('blob:')) URL.revokeObjectURL(previewSrc.value);
    selectedFile.value = null;
    previewSrc.value = null;
    pdfTotalPages.value = 1;
    currentPage.value = 1;
    detectingLanguage.value = false;
    languageAutoDetected.value = false;
    if (fileInput.value) fileInput.value.value = '';
};

// PDF Pagination
const pdfTotalPages = ref(1);
const currentPage = ref(1);
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < pdfTotalPages.value) currentPage.value++; };

// Field Logic
const addField = (type) => {
    fields.value.push({
        field_type: type,
        label: type === 'name' ? 'Recipient Name' : 'Village Name',
        x_percent: 10,
        y_percent: 10,
        width_percent: 25,
        height_percent: 5,
        color: '#000000',
        page_number: currentPage.value,
        required: true
    });
    selectedFieldIndex.value = fields.value.length - 1;
};

const getTranslatedLabel = (field) => {
    if (field.field_type === 'name') {
        return lang.t('recipient_name');
    }
    if (field.field_type === 'village') {
        return lang.t('village_name');
    }
    return field.label;
};



const removeField = (index) => {
    fields.value.splice(index, 1);
    if (selectedFieldIndex.value === index) selectedFieldIndex.value = null;
};

const handleCanvasClick = (e) => {
    if (e.target !== e.currentTarget) return; // Only if clicking canvas background
    
    const rect = e.currentTarget.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;

    fields.value.push({
        field_type: 'name',
        label: 'Recipient Name',
        x_percent: Math.max(0, Math.min(95, x)),
        y_percent: Math.max(0, Math.min(95, y)),
        width_percent: 22,
        height_percent: 4,
        color: '#000000',
        page_number: currentPage.value,
        required: true
    });
    selectedFieldIndex.value = fields.value.length - 1;
};

// Drag & Resize
let isDragging = false;
let isResizing = false;
let startX = 0;
let startY = 0;
let startFieldX = 0;
let startFieldY = 0;
let startFieldW = 0;
let startFieldH = 0;

// Pointer position works for both mouse and touch events.
const getPoint = (e) => {
    const t = e.touches?.[0] || e.changedTouches?.[0];
    return t ? { x: t.clientX, y: t.clientY } : { x: e.clientX, y: e.clientY };
};

const addMoveListeners = () => {
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', stopDragResize);
    window.addEventListener('touchmove', handleMouseMove, { passive: false });
    window.addEventListener('touchend', stopDragResize);
};

const startDrag = (e, index) => {
    isDragging = true;
    selectedFieldIndex.value = index;
    const field = fields.value[index];
    const p = getPoint(e);
    startX = p.x;
    startY = p.y;
    startFieldX = field.x_percent;
    startFieldY = field.y_percent;
    addMoveListeners();
};

const startResize = (e, index) => {
    isResizing = true;
    selectedFieldIndex.value = index;
    const field = fields.value[index];
    const p = getPoint(e);
    startX = p.x;
    startY = p.y;
    startFieldW = field.width_percent;
    startFieldH = field.height_percent;
    addMoveListeners();
};

const handleMouseMove = (e) => {
    if (!isDragging && !isResizing) return;
    if (e.cancelable) e.preventDefault(); // keep the page from scrolling mid-drag on touch

    const p = getPoint(e);
    const dx = ((p.x - startX) / canvasW.value) * 100;
    const dy = ((p.y - startY) / canvasH.value) * 100;
    const field = fields.value[selectedFieldIndex.value];

    if (isDragging) {
        field.x_percent = Math.max(0, Math.min(100 - field.width_percent, startFieldX + dx));
        field.y_percent = Math.max(0, Math.min(100 - field.height_percent, startFieldY + dy));
    } else if (isResizing) {
        field.width_percent = Math.max(5, Math.min(100 - field.x_percent, startFieldW + dx));
        field.height_percent = Math.max(2, Math.min(100 - field.y_percent, startFieldH + dy));
    }
};

const stopDragResize = () => {
    isDragging = false;
    isResizing = false;
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('mouseup', stopDragResize);
    window.removeEventListener('touchmove', handleMouseMove);
    window.removeEventListener('touchend', stopDragResize);
};

const step2ContainerRef = ref(null);

watchEffect((onCleanup) => {
    // Observer for Step 3 Editor
    const editorEl = canvasContainerRef.value;
    if (editorEl) {
        const ro = new ResizeObserver(([entry]) => {
            canvasW.value = Math.max(280, entry.contentRect.width - (window.innerWidth < 640 ? 32 : 64)); 
        });
        ro.observe(editorEl);
        onCleanup(() => ro.disconnect());
    }

    // Observer for Step 2 Preview
    const previewEl = step2ContainerRef.value;
    if (previewEl) {
        const ro = new ResizeObserver(([entry]) => {
            step2CanvasW.value = Math.max(280, entry.contentRect.width - (window.innerWidth < 640 ? 32 : 64));
        });
        ro.observe(previewEl);
        onCleanup(() => ro.disconnect());
    }
});

// Save logic
const saveDocument = async (status = 'draft') => {
    if (fields.value.length === 0) {
        alert('Validation Error: Please add at least one field (e.g., Name or Village) to the document before saving.');
        currentStep.value = 3;
        return;
    }

    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('type', 'document');
        formData.append('name', form.value.name);
        formData.append('description', form.value.description || '');
        formData.append('status', status);
        formData.append('language', form.value.language);
        formData.append('total_signers', fields.value.length); 

        if (selectedFile.value instanceof File) {
            formData.append('file', selectedFile.value);
        }

        let docId = route.params.id;
        let res;

        if (isEdit.value) {
            // Update basic info
            await axios.put(`/api/documents/${docId}`, {
                name: form.value.name,
                description: form.value.description,
                status: status,
                language: form.value.language,
                total_signers: fields.value.length
            });
            res = { data: { id: docId } };
        } else {
            res = await axios.post('/api/documents', formData);
            docId = res.data.id;
        }

        // Save fields
        await axios.post(`/api/documents/${docId}/fields`, {
            fields: fields.value
        });

        router.push('/documents');
    } catch (err) {
        console.error('Failed to save document:', err);
        alert('Failed to save document. Please check the form and try again.');
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    if (isEdit.value) {
        try {
            const { data } = await axios.get(`/api/documents/${route.params.id}`);
            form.value.name = data.name;
            form.value.description = data.description;
            form.value.language = data.language || 'gu';
            languageAutoDetected.value = true; // use the saved value; no re-detect on edit
            fields.value = data.fields || [];
            previewSrc.value = data.file_url;
            selectedFile.value = { name: data.file_name, size: 0 }; // Placeholder
        } catch (err) {
            console.error('Failed to fetch document:', err);
        }
    }
});

onUnmounted(() => {
    if (previewSrc.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewSrc.value);
    }
});
</script>

<style scoped>
@reference "../../css/app.css";
.input {
    @apply border-gray-100 bg-gray-50/50 hover:bg-white focus:bg-white;
}

.label {
    @apply text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2;
}

.card {
    @apply shadow-sm border-gray-100;
}

.btn-primary {
    @apply bg-primary-600 hover:bg-primary-700 shadow-lg shadow-primary-100 rounded-xl font-bold;
}

.btn-secondary {
    @apply border-gray-100 bg-white text-gray-600 hover:bg-gray-50 rounded-xl font-bold;
}

.badge {
    @apply rounded-lg;
}
</style>

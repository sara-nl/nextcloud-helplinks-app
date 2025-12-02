<template>
    <div class="helplinks-admin">
        <div class="admin-header">
            <NcButton
                type="primary"
                @click="addSection"
            >
                <template #icon>
                    <Plus :size="20" />
                </template>
                {{ t('helplinks', 'Add Section') }}
            </NcButton>
            
            <NcButton
                v-if="hasChanges"
                type="success"
                @click="saveAll"
                :disabled="saving"
            >
                <template #icon>
                    <Check v-if="!saving" :size="20" />
                    <NcLoadingIcon v-else :size="20" />
                </template>
                {{ saving ? t('helplinks', 'Saving...') : t('helplinks', 'Save All') }}
            </NcButton>
        </div>

        <NcEmptyContent
            v-if="sections.length === 0 && !loading"
            :name="t('helplinks', 'No help sections configured')"
            :description="t('helplinks', 'Click Add Section to create your first help section')"
            icon="icon-info"
        />

        <draggable
            v-else
            v-model="sections"
            handle=".drag-handle"
            @end="onReorder"
            class="sections-list"
        >
            <div
                v-for="(section, index) in sections"
                :key="section.id"
                class="admin-section"
            >
                <div class="section-header">
                    <div class="drag-handle">
                        <DragVertical :size="20" />
                    </div>
                    <h3>{{ t('helplinks', 'Section {number}', { number: index + 1 }) }}</h3>
                    <NcActions>
                        <NcActionButton @click="deleteSection(section.id)">
                            <template #icon>
                                <Delete :size="20" />
                            </template>
                            {{ t('helplinks', 'Delete Section') }}
                        </NcActionButton>
                    </NcActions>
                </div>

                <div class="section-fields">
                    <NcTextField
                        :value.sync="section.title"
                        :label="t('helplinks', 'Section Title')"
                        :placeholder="t('helplinks', 'e.g., Research Drive Help')"
                        @update:value="markChanged"
                    />

                    <NcTextArea
                        :value.sync="section.description"
                        :label="t('helplinks', 'Description')"
                        :placeholder="t('helplinks', 'e.g., For more information please see:')"
                        @update:value="markChanged"
                    />

                    <div class="main-link-section">
                        <h4>{{ t('helplinks', 'Main Link') }}</h4>
                        <NcTextField
                            :value.sync="section.mainLinkText"
                            :label="t('helplinks', 'Link Text')"
                            :placeholder="t('helplinks', 'Main topic link text')"
                            @update:value="markChanged"
                        />
                        <NcTextField
                            :value.sync="section.mainLinkUrl"
                            :label="t('helplinks', 'URL')"
                            :placeholder="t('helplinks', 'https://...')"
                            type="url"
                            @update:value="markChanged"
                        />
                    </div>

                    <div class="sublinks-section">
                        <div class="sublinks-header">
                            <h4>{{ t('helplinks', 'Sub-links') }}</h4>
                            <NcButton
                                type="secondary"
                                @click="addSubLink(section)"
                            >
                                <template #icon>
                                    <Plus :size="16" />
                                </template>
                                {{ t('helplinks', 'Add Sub-link') }}
                            </NcButton>
                        </div>

                        <div
                            v-for="(subLink, subIndex) in section.subLinks"
                            :key="subLink.id"
                            class="sublink-item"
                        >
                            <div class="sublink-header">
                                <span class="sublink-number">{{ subIndex + 1 }}</span>
                                <NcButton
                                    type="error"
                                    @click="removeSubLink(section, subLink.id)"
                                >
                                    <template #icon>
                                        <Delete :size="16" />
                                    </template>
                                </NcButton>
                            </div>
                            <NcTextField
                                :value.sync="subLink.text"
                                :label="t('helplinks', 'Sub-link Description')"
                                :placeholder="t('helplinks', 'Description text')"
                                @update:value="markChanged"
                            />
                            <NcTextField
                                :value.sync="subLink.url"
                                :label="t('helplinks', 'URL')"
                                :placeholder="t('helplinks', 'https://...')"
                                type="url"
                                @update:value="markChanged"
                            />
                        </div>

                        <p v-if="section.subLinks.length === 0" class="empty-sublinks">
                            {{ t('helplinks', 'No sub-links added yet') }}
                        </p>
                    </div>
                </div>
            </div>
        </draggable>
    </div>
</template>

<script>
import {
    NcButton,
    NcActions,
    NcActionButton,
    NcTextField,
    NcTextArea,
    NcEmptyContent,
    NcLoadingIcon,
} from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'
import draggable from 'vuedraggable'
import Plus from 'vue-material-design-icons/Plus.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Check from 'vue-material-design-icons/Check.vue'
import DragVertical from 'vue-material-design-icons/DragVertical.vue'

export default {
    name: 'AdminApp',
    components: {
        NcButton,
        NcActions,
        NcActionButton,
        NcTextField,
        NcTextArea,
        NcEmptyContent,
        NcLoadingIcon,
        draggable,
        Plus,
        Delete,
        Check,
        DragVertical,
    },
    data() {
        return {
            sections: [],
            loading: true,
            saving: false,
            hasChanges: false,
        }
    },
    async mounted() {
        await this.loadSections()
    },
    methods: {
        async loadSections() {
            try {
                const response = await axios.get(generateUrl('/apps/helplinks/api/sections'))
                let list = response.data

                // OCS format
                if (list?.ocs?.data) {
                    list = list.ocs.data
                }

                // Wrapped inside object
                if (list?.sections) {
                    list = list.sections
                }

                // Ensure array
                if (!Array.isArray(list)) {
                    console.warn('Expected array, got:', list)
                    list = []
                }

                this.sections = list.map(item => ({
                    id: item.section.id,
                    title: item.section.title,
                    description: item.section.description,
                    mainLinkText: item.section.mainLinkText,
                    mainLinkUrl: item.section.mainLinkUrl,
                    sortOrder: item.section.sortOrder,
                    subLinks: item.subLinks || [],
                    isNew: false,
                }))
            } catch (error) {
                console.error('Error loading sections:', error)
                showError(t('helplinks', 'Failed to load help sections'))
            } finally {
                this.loading = false
            }
        },
        
        addSection() {
            this.sections.push({
                id: Date.now(),
                title: '',
                description: '',
                mainLinkText: '',
                mainLinkUrl: '',
                sortOrder: this.sections.length,
                subLinks: [],
                isNew: true,
            })
            this.markChanged()
        },
        
        async deleteSection(id) {
            const index = this.sections.findIndex(s => s.id === id)
            if (index === -1) return
            
            const section = this.sections[index]
            
            if (!section.isNew) {
                try {
                    await axios.delete(generateUrl(`/apps/helplinks/api/sections/${id}`))
                    showSuccess(t('helplinks', 'Section deleted'))
                } catch (error) {
                    console.error('Error deleting section:', error)
                    showError(t('helplinks', 'Failed to delete section'))
                    return
                }
            }
            
            this.sections.splice(index, 1)
            this.markChanged()
        },
        
        addSubLink(section) {
            section.subLinks.push({
                id: Date.now(),
                text: '',
                url: '',
                sectionId: section.id,
            })
            this.markChanged()
        },
        
        removeSubLink(section, subLinkId) {
            const index = section.subLinks.findIndex(sl => sl.id === subLinkId)
            if (index !== -1) {
                section.subLinks.splice(index, 1)
                this.markChanged()
            }
        },
        
        async saveAll() {
            this.saving = true
            
            try {
                for (const section of this.sections) {
                    const data = {
                        title: section.title,
                        description: section.description,
                        mainLinkText: section.mainLinkText,
                        mainLinkUrl: section.mainLinkUrl,
                        subLinks: section.subLinks.map(sl => ({
                            text: sl.text,
                            url: sl.url,
                        })),
                        sortOrder: section.sortOrder,
                    }
                    
                    if (section.isNew) {
                        const response = await axios.post(
                            generateUrl('/apps/helplinks/api/sections'),
                            data
                        )
                        section.id = response.data.section.id
                        section.isNew = false
                    } else {
                        await axios.put(
                            generateUrl(`/apps/helplinks/api/sections/${section.id}`),
                            data
                        )
                    }
                }
                
                showSuccess(t('helplinks', 'All changes saved'))
                this.hasChanges = false
                await this.loadSections()
            } catch (error) {
                console.error('Error saving sections:', error)
                showError(t('helplinks', 'Failed to save changes'))
            } finally {
                this.saving = false
            }
        },
        
        async onReorder() {
            this.sections.forEach((section, index) => {
                section.sortOrder = index
            })
            
            const orderData = this.sections.map((section, index) => ({
                id: section.id,
                sortOrder: index,
            }))
            
            try {
                await axios.post(
                    generateUrl('/apps/helplinks/api/sections/reorder'),
                    { order: orderData }
                )
            } catch (error) {
                console.error('Error reordering sections:', error)
            }
        },
        
        markChanged() {
            this.hasChanges = true
        },
    },
}
</script>

<style scoped>
.helplinks-admin {
    padding: 20px 0;
}

.admin-header {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.sections-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.admin-section {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 20px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.drag-handle {
    cursor: move;
    color: var(--color-text-lighter);
}

.section-header h3 {
    flex: 1;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.section-fields {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.main-link-section,
.sublinks-section {
    padding: 15px;
    background: var(--color-background-dark);
    border-radius: var(--border-radius);
}

.main-link-section h4,
.sublinks-section h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
}

.sublinks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.sublink-item {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: 15px;
    margin-bottom: 10px;
}

.sublink-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.sublink-number {
    font-weight: 600;
    color: var(--color-text-lighter);
}

.empty-sublinks {
    color: var(--color-text-lighter);
    font-style: italic;
    margin: 0;
}
</style>
<template>
  <div class="helplinks-admin">
    <!-- Header -->
    <div class="admin-header">
      <NcButton type="primary" @click="addSection">
        <template #icon><Plus :size="20" /></template>
        {{ t('helplinks', 'Add Section') }}
      </NcButton>

      <NcButton v-if="hasChanges" type="success" @click="saveAll" :disabled="saving">
        <template #icon>
          <Check v-if="!saving" :size="20" />
          <NcLoadingIcon v-else :size="20" />
        </template>
        {{ saving ? t('helplinks', 'Saving...') : t('helplinks', 'Save All') }}
      </NcButton>
    </div>

    <!-- Empty -->
    <NcEmptyContent
      v-if="sections.length === 0 && !loading"
      :name="t('helplinks', 'No help sections configured')"
      :description="t('helplinks', 'Click Add Section to create your first help section')"
      icon="icon-info"
    />

    <!-- Sections list -->
    <div v-else ref="sectionsList" class="sections-list">
      <div
        v-for="(section, index) in sections"
        :key="section.id"
        class="admin-section"
        :data-id="section.id"
      >
        <!-- Section header -->
        <div class="section-header">
          <div class="drag-handle"><DragVertical :size="20" /></div>
          <h3>{{ t('helplinks', 'Section {number}', { number: index + 1 }) }}</h3>
          <NcActions>
            <NcActionButton @click="deleteSection(section.id)">
              <template #icon><Delete :size="20" /></template>
              {{ t('helplinks', 'Delete Section') }}
            </NcActionButton>
          </NcActions>
        </div>

        <!-- Section fields -->
        <div class="section-fields">
          <NcTextField
            v-model="section.title"
            :label="t('helplinks', 'Section Title')"
            :placeholder="t('helplinks', 'e.g., Research Drive Help')"
            @update:value="markChanged"
          />
          <NcTextArea
            v-model="section.description"
            :label="t('helplinks', 'Description')"
            :placeholder="t('helplinks', 'e.g., For more information please see:')"
            @update:value="markChanged"
          />

          <!-- Main link -->
          <div class="main-link-section">
            <h4>{{ t('helplinks', 'Main Link') }}</h4>
            <NcTextField
              v-model="section.mainLinkText"
              :label="t('helplinks', 'Link Text')"
              :placeholder="t('helplinks', 'Main topic link text')"
              @update:value="markChanged"
            />
            <NcTextField
              v-model="section.mainLinkUrl"
              :label="t('helplinks', 'URL')"
              :placeholder="t('helplinks', 'https://...')"
              type="url"
              @update:value="markChanged"
            />
          </div>

          <!-- Sublinks -->
          <div class="sublinks-section">
            <div class="sublinks-header">
              <h4>{{ t('helplinks', 'Sub-links') }}</h4>
              <NcButton type="secondary" @click="addSubLink(section)">
                <template #icon><Plus :size="16" /></template>
                {{ t('helplinks', 'Add Sub-link') }}
              </NcButton>
            </div>

            <div :ref="'subLinks-' + section.id">
              <div
                v-for="(subLink, subIndex) in section.subLinks"
                :key="subLink.id"
                class="sublink-item"
                :data-id="subLink.id"
              >
                <div class="sublink-header">
                  <span class="drag-handle-sub"><DragVertical :size="16" /></span>
                  <span class="sublink-number">{{ subIndex + 1 }}</span>
                  <NcButton type="error" @click="removeSubLink(section, subLink.id)">
                    <template #icon><Delete :size="16" /></template>
                  </NcButton>
                </div>
                <NcTextField
                  v-model="subLink.text"
                  :label="t('helplinks', 'Sub-link Description')"
                  :placeholder="t('helplinks', 'Description text')"
                  @update:value="markChanged"
                />
                <NcTextField
                  v-model="subLink.url"
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
      </div>
    </div>
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
  NcLoadingIcon
} from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'
import Sortable from 'sortablejs'
import Plus from 'vue-material-design-icons/Plus.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Check from 'vue-material-design-icons/Check.vue'
import DragVertical from 'vue-material-design-icons/DragVertical.vue'

export default {
  name: 'AdminApp',
  components: { NcButton, NcActions, NcActionButton, NcTextField, NcTextArea, NcEmptyContent, NcLoadingIcon, Plus, Delete, Check, DragVertical },
  data() {
    return {
      sections: [],
      loading: true,
      saving: false,
      hasChanges: false
    }
  },
  async mounted() {
    await this.loadSections()
    this.initSortableSections()
    this.initSortableSubLinks()
  },
  methods: {
    async loadSections() {
      try {
        const response = await axios.get(generateUrl('/apps/helplinks/api/sections'))
        const list = response.data.sections ?? []

        this.sections = list.map(item => {
          const section = item.section
          return {
            id: section.id,
            title: section.title ?? '',
            description: section.description ?? '',
            mainLinkText: section.mainLinkText ?? '',
            mainLinkUrl: section.mainLinkUrl ?? '',
            sortOrder: section.sortOrder ?? 0,
            subLinks: (item.subLinks ?? []).map(sl => ({
              id: sl.id,
              sectionId: sl.sectionId,
              text: sl.text ?? '',
              url: sl.url ?? '',
              sortOrder: sl.sortOrder ?? 0
            })),
            isNew: false
          }
        })
      } catch (error) {
        console.error('Error loading sections:', error)
        showError(t('helplinks', 'Failed to load help sections'))
      } finally {
        this.loading = false
      }
    },

    initSortableSections() {
      const el = this.$refs.sectionsList
      if (!el) return
      Sortable.create(el, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: () => {
          this.sections.forEach((section, i) => section.sortOrder = i)
          this.markChanged()
        }
      })
    },

    initSortableSubLinks() {
      this.sections.forEach(section => {
        this.$nextTick(() => {
          let container = this.$refs['subLinks-' + section.id]
          if (Array.isArray(container)) container = container[0]
          if (!container || container._sortable) return

          container._sortable = Sortable.create(container, {
            handle: '.drag-handle-sub',
            animation: 150,
            onEnd: () => {
              const nodes = [...container.children]
              section.subLinks = nodes.map((child, index) => {
                const sl = section.subLinks.find(s => s.id === Number(child.dataset.id))
                sl.sortOrder = index
                return sl
              })
              this.markChanged()
            }
          })
        })
      })
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
        isNew: true
      })
      this.markChanged()
      this.$nextTick(() => this.initSortableSubLinks())
    },

    deleteSection(id) {
      const index = this.sections.findIndex(s => s.id === id)
      if (index !== -1) {
        this.sections.splice(index, 1)
        this.markChanged()
      }
    },

    addSubLink(section) {
      section.subLinks.push({
        id: Date.now(),
        sectionId: section.id,
        text: '',
        url: '',
        sortOrder: section.subLinks.length
      })
      this.markChanged()
      this.$nextTick(() => this.initSortableSubLinks())
    },

    removeSubLink(section, subLinkId) {
      const index = section.subLinks.findIndex(sl => sl.id === subLinkId)
      if (index !== -1) {
        section.subLinks.splice(index, 1)
        section.subLinks.forEach((sl, i) => sl.sortOrder = i)
        this.markChanged()
      }
    },

    markChanged() {
      this.hasChanges = true
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
            sortOrder: section.sortOrder,
            subLinks: section.subLinks.map(sl => ({
              text: sl.text,
              url: sl.url,
              sortOrder: sl.sortOrder
            }))
          }
          if (section.isNew) {
            const res = await axios.post(generateUrl('/apps/helplinks/api/sections'), data)
            section.id = res.data.section.id
            section.isNew = false
          } else {
            await axios.put(generateUrl(`/apps/helplinks/api/sections/${section.id}`), data)
          }
        }
        showSuccess(t('helplinks', 'All changes saved'))
        this.hasChanges = false
        await this.loadSections()
        this.$nextTick(() => this.initSortableSubLinks())
      } catch (err) {
        console.error('Save failed', err)
        showError(t('helplinks', 'Failed to save changes'))
      } finally {
        this.saving = false
      }
    }
  }
}
</script>

<style scoped>
.helplinks-admin { padding: 20px 0; }
.admin-header { display: flex; gap: 10px; margin-bottom: 20px; }
.sections-list { display: flex; flex-direction: column; gap: 20px; }
.admin-section { background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: var(--border-radius-large); padding: 20px; }
.section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.drag-handle, .drag-handle-sub { cursor: move; color: var(--color-text-lighter); }
.section-header h3 { flex: 1; margin: 0; font-size: 16px; font-weight: 600; }
.section-fields { display: flex; flex-direction: column; gap: 15px; }
.main-link-section, .sublinks-section { padding: 15px; background: var(--color-background-dark); border-radius: var(--border-radius); }
.main-link-section h4, .sublinks-section h4 { margin: 0 0 15px 0; font-size: 14px; font-weight: 600; }
.sublinks-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.sublink-item { background: var(--color-main-background); border: 1px solid var(--color-border); border-radius: var(--border-radius); padding: 15px; margin-bottom: 10px; }
.sublink-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.sublink-number { font-weight: 600; color: var(--color-text-lighter); }
.empty-sublinks { color: var(--color-text-lighter); font-style: italic; margin: 0; }
</style>
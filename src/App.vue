<template>
    <NcContent app-name="helplinks">
        <NcAppContent>
            <div class="helplinks-content">
                <h2>{{ t('helplinks', 'Help & Documentation') }}</h2>
                
                <NcEmptyContent
                    v-if="loading"
                    :name="t('helplinks', 'Loading help sections...')"
                    icon="icon-loading"
                />
                
                <NcEmptyContent
                    v-else-if="sections.length === 0"
                    :name="t('helplinks', 'No help sections available')"
                    :description="t('helplinks', 'Contact your administrator to configure help links.')"
                    icon="icon-info"
                />
                
                <div v-else class="sections-container">
                    <div v-for="section in sections" :key="section.section.id" class="help-section">
                        <h3>{{ section.section.title }}</h3>
                        <p v-if="section.section.description" class="section-description">
                            {{ section.section.description }}
                        </p>
                        
                        <ul class="links-list">
                            <li v-if="section.section.mainLinkText && section.section.mainLinkUrl">
                                <a :href="section.section.mainLinkUrl" target="_blank" rel="noopener noreferrer">
                                    {{ section.section.mainLinkText }} ↗
                                </a>
                            </li>
                            
                            <li v-if="section.subLinks.length > 0">
                                <ul class="sublinks-list">
                                    <li v-for="subLink in section.subLinks" :key="subLink.id">
                                        <a :href="subLink.url" target="_blank" rel="noopener noreferrer">
                                            {{ subLink.text }} ↗
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </NcAppContent>
    </NcContent>
</template>

<script>
import { NcContent, NcAppContent, NcEmptyContent } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'

export default {
    name: 'App',
    components: {
        NcContent,
        NcAppContent,
        NcEmptyContent,
    },
    data() {
        return {
            sections: [],
            loading: true,
        }
    },
    async mounted() {
        await this.loadSections()
    },
    methods: {
        async loadSections() {
            try {
                const response = await axios.get(generateUrl('/apps/helplinks/api/sections'))
                this.sections = response.data
            } catch (error) {
                console.error('Error loading sections:', error)
                showError(t('helplinks', 'Failed to load help sections'))
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<style scoped>
.helplinks-content {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
}

.sections-container {
    margin-top: 20px;
}

.help-section {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 20px;
    margin-bottom: 20px;
}

.help-section h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 600;
}

.section-description {
    color: var(--color-text-lighter);
    margin-bottom: 15px;
}

.links-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.links-list > li {
    margin-bottom: 8px;
}

.links-list a {
    color: var(--color-primary-element);
    text-decoration: none;
}

.links-list a:hover {
    text-decoration: underline;
}

.sublinks-list {
    list-style: none;
    padding-left: 20px;
    margin-top: 8px;
}

.sublinks-list li {
    margin-bottom: 6px;
}
</style>
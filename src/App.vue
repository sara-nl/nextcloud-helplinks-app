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
                    v-else-if="sections.length === 0 && !introvoxEnabled && !supportEmail && !supportUrl"
                    :name="t('helplinks', 'No help sections available')"
                    :description="t('helplinks', 'Contact your administrator to configure help links.')"
                    icon="icon-info"
                />
                
                <div v-else class="sections-container">
                    <!-- Introvox Interactive Tutorial Section -->
                    <div v-if="introvoxEnabled" class="help-section introvox-section">
                        <h3>{{ t('helplinks', 'Introvox Interactive Tutorial') }}</h3>
                        <p class="section-description">
                            {{ t('helplinks', 'IntroVox offers a user-friendly interactive onboarding tour that helps you get started quickly and easily find your way around the environment. You can find the IntroVox interactive onboarding tour in your personal settings.') }}
                        </p>
                        <NcButton
                            type="primary"
                            @click="openIntrovoxHelp"
                            class="introvox-help-button"
                        >
                            <template #icon>
                                <HelpCircle :size="20" />
                            </template>
                            {{ t('helplinks', 'Go to Introvox') }}
                        </NcButton>
                    </div>

                    <!-- Regular Help Sections -->
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

                    <!-- IT Support Section -->
                    <div v-if="supportEmail || supportUrl" class="help-section support-section">
                        <h3>{{ t('helplinks', 'Support') }}</h3>
                        <p class="section-description">
                            {{ t('helplinks', 'For further support, please contact your IT Service Desk.') }}
                        </p>

                        <ul class="links-list">
                            <!-- Support via e-mail -->
                            <li v-if="supportEmail">
                                {{ t('helplinks', 'Request support via') }}
                                <a :href="`mailto:${supportEmail}`">
                                    <u>{{ t('helplinks', 'your IT-Servicedesk') }}</u> ↗
                                </a>
                            </li>

                            <!-- Support via URL -->
                            <li v-if="supportUrl">
                                {{ t('helplinks', 'Request support via') }}
                                <a :href="supportUrl" target="_blank" rel="noopener">
                                    <u>{{ t('helplinks', 'your IT-Servicedesk') }}</u> ↗
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </NcAppContent>
    </NcContent>
</template>

<script>
import { NcContent, NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import HelpCircle from 'vue-material-design-icons/HelpCircle.vue'

export default {
    name: 'App',
    components: {
        NcContent,
        NcAppContent,
        NcEmptyContent,
        NcButton,
        HelpCircle,
    },
    data() {
        return {
            sections: [],
            introvoxEnabled: false,
            supportEmail: '',
            supportUrl: '',
            environmentName: '',
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
                this.sections = response.data.sections || []
                this.introvoxEnabled = response.data.introvoxEnabled || false
                this.supportEmail = response.data.supportEmail || ''
                this.supportUrl = response.data.supportUrl || ''
                this.environmentName = response.data.environmentName || ''
            } catch (error) {
                console.error('Error loading sections:', error)
                showError(t('helplinks', 'Failed to load help sections'))
            } finally {
                this.loading = false
            }
        },
        openIntrovoxHelp() {
            const url = generateUrl('/settings/user/introvox-help')
            window.location.href = url
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

.support-section {
    background: var(--color-warning);
    border-color: var(--color-warning-text);
}

.support-section .links-list {
    font-size: 16px;
}

.support-section a {
    color: var(--color-main-text);
    font-weight: 600;
}

.introvox-section {
    background: var(--color-primary-element-light);
    border-color: var(--color-primary-element);
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

.introvox-help-button {
    margin-top: 10px;
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
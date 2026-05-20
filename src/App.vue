<template>
    <NcContent :app-name="appName">
        <NcAppContent>
            <div class="helplinks-content">
                <h2>{{ t('helplinks', 'Help & Documentation') }}</h2>
                
                <NcEmptyContent
                    v-if="loading"
                    :name="t('helplinks', 'Loading help sections...')"
                    icon="icon-loading"
                />
                
                <NcEmptyContent
                    v-else-if="visibleSections.length === 0 && !introvoxEnabled && !supportEmail && !supportUrl && !cloudId"
                    :name="t('helplinks', 'No help sections available')"
                    :description="t('helplinks', 'Contact your administrator to configure help links.')"
                    icon="icon-info"
                />
                
                <div v-else ref="sectionsContainer" class="sections-container">
                    <!-- Introvox Interactive Tutorial Section -->
                    <div v-if="introvoxEnabled" class="help-section introvox-section">
                        <h3>{{ t('helplinks', 'Interactive Tutorial') }}</h3>
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
                    <div v-for="section in visibleSections" :key="section.section.id" class="help-section">
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

                            <li v-if="section.visibleSubLinks.length > 0">
                                <ul class="sublinks-list">
                                    <li v-for="subLink in section.visibleSubLinks" :key="subLink.id">
                                        <a :href="subLink.url" target="_blank" rel="noopener noreferrer">
                                            {{ subLink.text }} ↗
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <!-- Nextcloud Talk Desktop Client Section -->
                    <div v-if="talkEnabled" class="help-section nextcloud-section">
                        <h3>{{ t('helplinks', 'Talk Desktop Client') }}</h3>
                        <p class="section-description">
                            {{ t('helplinks', 'Download and install the Nextcloud Talk desktop client to use video calls, chat, and screen sharing directly from your desktop.') }}
                        </p>
                        <p class="section-description">
                            <strong>{{ t('helplinks', 'Configuration:') }}</strong><br>
                            {{ t('helplinks', 'When setting up the client, use the following server URL:') }}
                        </p>
                        <p class="environment-url">
                            {{ environmentUrl }}
                        </p>
                        <NcButton
                            type="secondary"
                            :href="talkDownloadUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="talk-download-button"
                        >
                            <template #icon>
                                <Download :size="20" />
                            </template>
                            {{ t('helplinks', 'Download Nextcloud Talk Client') }}
                        </NcButton>
                    </div>

                    <!-- Nextcloud Files Desktop Client Section -->
                    <div class="help-section nextcloud-section">
                        <h3>{{ t('helplinks', 'Files Desktop Client') }}</h3>
                        <p class="section-description">
                            {{ t('helplinks', 'Download and install the Nextcloud Files desktop client to keep your files synchronized between the server and your desktop.') }}
                        </p>
                        <p class="section-description">
                            <strong>{{ t('helplinks', 'Configuration:') }}</strong><br>
                            {{ t('helplinks', 'When setting up the client, use the following server URL:') }}
                        </p>
                        <p class="environment-url">
                            {{ environmentUrl }}
                        </p>
                        <NcButton
                            type="secondary"
                            :href="filesDownloadUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="files-download-button"
                        >
                            <template #icon>
                                <Download :size="20" />
                            </template>
                            {{ t('helplinks', 'Download Nextcloud Files Client') }}
                        </NcButton>
                    </div>

                    <!-- Federated Cloud ID Section -->
                    <div v-if="cloudId" class="help-section cloud-id-section">
                        <h3>{{ t('helplinks', 'Your Federated Cloud ID') }}</h3>
                        <p class="section-description">
                            {{ t('helplinks', 'Share your Federated Cloud ID with users on other Nextcloud servers to collaborate on files and folders.') }}
                            {{ t('helplinks', 'Others can share files with you by entering your Federated Cloud ID.') }}
                        </p>

                        <p class="environment-url">
                            {{ cloudId }}
                        </p>
                            
                        <!-- Address Book Tip: opens in a popover so it floats
                             above the page and never pushes other cards down. -->
                        <div class="address-book-expandable">
                            <NcPopover :shown="addressBookTipExpanded" @update:shown="addressBookTipExpanded = $event">
                                <template #trigger>
                                    <button
                                        class="expand-button"
                                        :aria-expanded="addressBookTipExpanded.toString()"
                                    >
                                        <BookAccount :size="20" class="tip-icon" />
                                        <span class="expand-title">{{ t('helplinks', 'Pro Tip: Save to Address Book for Easy Sharing') }}</span>
                                        <ChevronDown
                                            :size="20"
                                            class="chevron-icon"
                                            :class="{ 'rotated': addressBookTipExpanded }"
                                        />
                                    </button>
                                </template>
                                <div class="address-book-tip">
                                    <p>
                                        {{ t('helplinks', 'Save frequently used Federated Cloud IDs to your personal address book for quick access.') }}
                                        {{ t('helplinks', 'Next time you share a file or folder, simply start typing the contact\'s name and their Federated Cloud ID will appear in the autocomplete suggestions, making cross-instance sharing effortless!') }}
                                    </p>
                                    <div class="tip-steps">
                                        <strong>{{ t('helplinks', 'How to add a federated contact:') }}</strong>
                                        <ol>
                                            <li>{{ t('helplinks', 'Open the Contacts app from the app menu') }}</li>
                                            <li>{{ t('helplinks', 'Click the "+ New contact" button') }}</li>
                                            <li>{{ t('helplinks', 'Enter the contact name and their Federated Cloud ID') }}</li>
                                            <li>{{ t('helplinks', 'Add optional details like organization or notes') }}</li>
                                            <li>{{ t('helplinks', 'Save the contact') }}</li>
                                        </ol>
                                    </div>
                                    <NcButton
                                        type="secondary"
                                        @click="openContactsApp"
                                        class="contacts-button"
                                    >
                                        <template #icon>
                                            <BookAccount :size="20" />
                                        </template>
                                        {{ t('helplinks', 'Open Contacts App') }}
                                    </NcButton>
                                </div>
                            </NcPopover>
                        </div>
                    </div>

                    <!-- WebDAV Section -->
                    <div class="help-section webdav-section">
                        <h3>{{ t('helplinks', 'WebDAV File Access') }}</h3>
                        <p class="section-description">
                            {{ t('helplinks', 'You can access your files using any WebDAV client. This allows you to mount your cloud storage as a network drive on your computer or access it through compatible applications.') }}
                        </p>
                        <p class="section-description">
                            <strong>{{ t('helplinks', 'Setup Instructions:') }}</strong>
                        </p>
                        <ol class="setup-steps">
                            <li>{{ t('helplinks', 'Create WebDAV credentials in your personal settings') }}</li>
                            <li>{{ t('helplinks', 'Use these credentials to connect your WebDAV client') }}</li>
                        </ol>
                        <NcButton
                            type="secondary"
                            @click="openWebdavSettings"
                            class="webdav-settings-button"
                        >
                            <template #icon>
                                <Key :size="20" />
                            </template>
                            {{ t('helplinks', 'Create WebDAV Credentials') }}
                        </NcButton>
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
                                {{ t('helplinks', 'Request support by email request from') }}
                                <a :href="`mailto:${supportEmail}`">
                                    <u>{{ t('helplinks', 'your IT-Servicedesk') }}</u> ↗
                                </a>
                            </li>

                            <!-- Support via URL -->
                            <li v-if="supportUrl">
                                {{ t('helplinks', 'Request support by service request from') }}
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
import { NcContent, NcAppContent, NcEmptyContent, NcButton, NcPopover } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import HelpCircle from 'vue-material-design-icons/HelpCircle.vue'
import Download from 'vue-material-design-icons/Download.vue'
import Key from 'vue-material-design-icons/Key.vue'
import ContentCopy from 'vue-material-design-icons/ContentCopy.vue'
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import BookAccount from 'vue-material-design-icons/BookAccount.vue'
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue'

export default {
    name: 'App',
    components: {
        NcContent,
        NcAppContent,
        NcEmptyContent,
        NcButton,
        NcPopover,
        HelpCircle,
        Download,
        Key,
        ContentCopy,
        InformationOutline,
        BookAccount,
        ChevronDown,
    },
    data() {
        return {
			appName: appName,
            sections: [],
            introvoxEnabled: false,
            talkEnabled: false,
            supportEmail: '',
            supportUrl: '',
            environmentName: '',
            environmentUrl: '',
            cloudId: '',
            addressBookTipExpanded: false,
            talkDownloadUrl: 'https://nextcloud.com/install/#desktop-talk',
            filesDownloadUrl: 'https://nextcloud.com/install/#desktop-files',
            loading: true,
        }
    },
    computed: {
        /**
         * Sections shown to the user. A section is only rendered when its
         * main link URL is set; sub-links with an empty URL are filtered out
         * so empty link blocks are never shown.
         */
        visibleSections() {
            return this.sections
                .filter(section => section.section.mainLinkUrl)
                .map(section => ({
                    ...section,
                    visibleSubLinks: (section.subLinks || []).filter(subLink => subLink.url),
                }))
        },
    },
    async mounted() {
        await this.loadSections()
        this._onResize = () => this.scheduleBalance()
        window.addEventListener('resize', this._onResize)
    },
    beforeUnmount() {
        if (this._onResize) {
            window.removeEventListener('resize', this._onResize)
        }
        if (this._balanceRaf) {
            cancelAnimationFrame(this._balanceRaf)
        }
    },
    methods: {
        async loadSections() {
            try {
                const response = await axios.get(generateUrl('/apps/helplinks/api/sections'))
                this.sections = response.data.sections || []

                this.introvoxEnabled = response.data.introvoxEnabled || false
                this.talkEnabled = response.data.talkEnabled || false
                this.supportEmail = response.data.supportEmail || ''
                this.supportUrl = response.data.supportUrl || ''
                this.environmentName = response.data.environmentName || ''
                this.environmentUrl = response.data.environmentUrl || window.location.origin
                this.cloudId = response.data.cloudId || ''
            } catch (error) {
                console.error('Error loading sections:', error)
                showError(t('helplinks', 'Failed to load help sections'))
                this.sections = [] // Ensure it's an empty array on error
            } finally {
                this.loading = false
                this.scheduleBalance()
            }
        },

        /** Debounce column balancing to one run per animation frame. */
        scheduleBalance() {
            if (this._balanceRaf) {
                cancelAnimationFrame(this._balanceRaf)
            }
            this._balanceRaf = requestAnimationFrame(() => {
                this._balanceRaf = null
                this.$nextTick(() => this.balanceColumns())
            })
        },

        /**
         * Set the flex container's max-height to roughly half the total card
         * height so cards wrap into two balanced columns. Above the single
         * card width (mobile) the cap is removed so everything stacks.
         */
        balanceColumns() {
            const container = this.$refs.sectionsContainer
            if (!container) {
                return
            }
            const cards = [...container.querySelectorAll('.help-section')]
            // Single column below the 768px breakpoint: let it flow naturally.
            if (cards.length < 2 || window.innerWidth < 768) {
                container.style.maxHeight = ''
                return
            }
            const gap = 20
            const heights = cards.map(c => c.offsetHeight)

            // Find the smallest column height that still fits every card into
            // exactly two columns. flex column-wrap fills column 1 until the
            // next card would exceed max-height, then starts column 2; if the
            // cap is too small a third column appears. We binary-search the
            // smallest cap that keeps the simulated wrap at two columns, which
            // both guarantees max two columns and balances them.
            const columnsNeeded = (cap) => {
                let columns = 1
                let used = 0
                for (const h of heights) {
                    if (h > cap) {
                        return Infinity // a single card taller than the cap
                    }
                    const add = used === 0 ? h : h + gap
                    if (used + add > cap) {
                        columns++
                        used = h
                    } else {
                        used += add
                    }
                }
                return columns
            }

            const tallest = Math.max(...heights)
            const totalHeight = heights.reduce((sum, h) => sum + h + gap, 0)
            let low = tallest
            let high = totalHeight
            // Smallest cap that fits in <= 2 columns.
            while (low < high) {
                const mid = Math.floor((low + high) / 2)
                if (columnsNeeded(mid) <= 2) {
                    high = mid
                } else {
                    low = mid + 1
                }
            }
            container.style.maxHeight = `${low + gap}px`
        },

        async copyCloudId() {
            try {
                await navigator.clipboard.writeText(this.cloudId)
                showSuccess(t('helplinks', 'Federated Cloud ID copied to clipboard'))
            } catch (error) {
                console.error('Error copying to clipboard:', error)
                showError(t('helplinks', 'Failed to copy to clipboard'))
            }
        },

        openIntrovoxHelp() {
            const url = generateUrl('/settings/user/introvox-help')
            window.location.href = url
        },

        openContactsApp() {
            const url = generateUrl('/apps/contacts')
            window.location.href = url
        },

        openWebdavSettings() {
            const url = generateUrl('/settings/user/security')
            window.location.href = url
        },
    },
}
</script>

<style lang="scss" scoped>
.helplinks-content {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.sections-container {
    margin-top: 20px;
    /* Balanced columns: cards flow top-to-bottom and wrap into a second column.
       JS sets the container's max-height to ~half the total card height, so the
       two columns end up roughly equal length, each card keeps its own height,
       and there are no large gaps. Single column on narrow screens. */
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    align-content: flex-start;
    gap: 20px;
}

.help-section {
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-large);
    padding: 20px;
    /* One column by default: each card spans the full width. */
    width: 100%;
}

/* Two balanced columns on wide screens (tablets and up). */
@media (min-width: 768px) {
    .help-section {
        /* Half width minus half the 20px gap. */
        width: calc(50% - 10px);
    }
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

.cloud-id-display {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
    padding: 12px;
    background: var(--color-main-background);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
}

.cloud-id-value {
    flex: 1;
    font-family: monospace;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-primary-element);
    padding: 8px 12px;
    background: var(--color-background-dark);
    border-radius: var(--border-radius);
}

/* Address book */
.address-book-expandable {
    margin: 20px 0;
}

.expand-button {
    margin: 0px !important;
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, var(--color-primary-element-light) 0%, var(--color-background-hover) 100%);
    border: none;
    cursor: pointer;
    transition: background 0.2s ease;
}

.expand-button:hover {
    background: var(--color-primary-element-light);
}

.expand-button:focus {
    outline: 2px solid var(--color-primary-element);
    outline-offset: -2px;
}

.expand-title {
    flex: 1;
    text-align: left;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-primary-element);
}

.tip-icon,
.chevron-icon {
    color: var(--color-primary-element);
    flex-shrink: 0;
}

.chevron-icon {
    transition: transform 0.3s ease;
}

.chevron-icon.rotated {
    transform: rotate(180deg);
}

.address-book-tip {
    /* Rendered inside the floating popover panel; cap the width so the tip
       reads as a column rather than stretching across the viewport. */
    max-width: 420px;
    padding: 20px;
}

.address-book-tip > p {
    margin: 0 0 15px;
    line-height: 1.6;
    color: var(--color-main-text);
}

.tip-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.tip-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-primary-element);
}

.tip-steps {
    background: var(--color-main-background);
    border-radius: var(--border-radius);
    padding: 15px;
    margin: 15px 0;
}

.tip-steps strong {
    display: block;
    margin-bottom: 10px;
    color: var(--color-main-text);
    font-size: 14px;
}

.tip-steps ol {
    list-style: decimal;
    padding-left: 20px;
    margin: 10px 0;
}

.tip-steps ol li {
    margin: 6px 0;
    line-height: 1.5;
    color: var(--color-text-lighter);
}

.contacts-button {
    margin-top: 15px;
}

.environment-url {
    background: var(--color-background-dark);
    padding: 10px 15px;
    border-radius: var(--border-radius);
    font-family: monospace;
    font-size: 14px;
    margin: 10px 0;
    word-break: break-all;
}

.talk-download-button,
.files-download-button,
.webdav-settings-button {
    margin-top: 10px;
}

.setup-steps {
    margin: 10px 0;
    padding-left: 20px;
}

.setup-steps li {
    margin-bottom: 8px;
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
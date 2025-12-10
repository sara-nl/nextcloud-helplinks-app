/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import path from 'path'
import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	'main': path.join(__dirname, 'src', 'main.js'),
	'admin-settings': path.join(__dirname, 'src', 'admin.js'),
}, {
	inlineCSS: false,
})

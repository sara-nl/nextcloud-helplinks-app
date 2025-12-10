<?php
namespace OCA\HelpLinks\Settings;

use OCA\HelpLinks\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {
    public function getForm(): TemplateResponse {
        Util::addScript(Application::APP_ID, 'helplinks-admin-settings');
        Util::addStyle(Application::APP_ID, 'helplinks-admin-settings');
        return new TemplateResponse(Application::APP_ID, 'admin');
    }

    public function getSection(): string {
        return 'additional';
    }

    public function getPriority(): int {
        return 50;
    }
}
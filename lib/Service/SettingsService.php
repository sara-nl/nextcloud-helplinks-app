<?php

namespace OCA\HelpLinks\Service;

use OCP\IConfig;

class SettingsService {
    private $config;
    private const APP_ID = 'helplinks';

    public function __construct(IConfig $config) {
        $this->config = $config;
    }

    public function getSupportEmail(): ?string {
        return $this->config->getAppValue(self::APP_ID, 'support_email', null);
    }

    public function setSupportEmail(?string $email): void {
        if (empty($email)) {
            $this->config->deleteAppValue(self::APP_ID, 'support_email');
        } else {
            $this->config->setAppValue(self::APP_ID, 'support_email', $email);
        }
    }

    public function getSupportUrl(): ?string {
        return $this->config->getAppValue(self::APP_ID, 'support_url', null);
    }

    public function setSupportUrl(?string $url): void {
        if (empty($url)) {
            $this->config->deleteAppValue(self::APP_ID, 'support_url');
        } else {
            $this->config->setAppValue(self::APP_ID, 'support_url', $url);
        }
    }

    public function getEnvironmentName(): ?string {
        return $this->config->getAppValue(self::APP_ID, 'environment_name', null);
    }

    public function setEnvironmentName(?string $name): void {
        if (empty($name)) {
            $this->config->deleteAppValue(self::APP_ID, 'environment_name');
        } else {
            $this->config->setAppValue(self::APP_ID, 'environment_name', $name);
        }
    }

    public function getAll(): array {
        return [
            'supportEmail' => $this->getSupportEmail(),
            'supportUrl' => $this->getSupportUrl(),
            'environmentName' => $this->getEnvironmentName(),
        ];
    }
}
<?php
namespace OCA\HelpLinks\Controller;

use OCA\HelpLinks\AppInfo\Application;
use OCA\HelpLinks\Service\SectionService;
use OCA\HelpLinks\Service\SettingsService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class ApiController extends OCSController {
    private $service;
    private $settingsService;

    public function __construct(
        IRequest $request,
        SectionService $service,
        SettingsService $settingsService
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->service = $service;
        $this->settingsService = $settingsService;
    }

    /**
     * Get all sections
     *
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function getSections(): DataResponse {
        try {
            $sections = $this->service->findAll();
            return new DataResponse([
                'sections' => $sections,
            ], Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a single section
     *
     * @param int $id
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function getSection(int $id): DataResponse {
        try {
            $sections = $this->service->findAll();
            $section = null;
            
            foreach ($sections as $s) {
                if ($s['section']->getId() === $id) {
                    $section = $s;
                    break;
                }
            }
            
            if ($section === null) {
                return new DataResponse([
                    'message' => 'Section not found',
                ], Http::STATUS_NOT_FOUND);
            }
            
            return new DataResponse($section, Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new section
     *
     * @param string $title
     * @param string $description
     * @param string $mainLinkText
     * @param string $mainLinkUrl
     * @param array $subLinks
     * @param int $sortOrder
     * @return DataResponse
     */
    #[NoCSRFRequired]
    public function createSection(
        string $title = '',
        string $description = '',
        string $mainLinkText = '',
        string $mainLinkUrl = '',
        array $subLinks = [],
        int $sortOrder = 0
    ): DataResponse {
        try {
            $data = [
                'title' => $title,
                'description' => $description,
                'mainLinkText' => $mainLinkText,
                'mainLinkUrl' => $mainLinkUrl,
                'subLinks' => $subLinks,
                'sortOrder' => $sortOrder,
            ];
            
            $result = $this->service->create($data);
            
            return new DataResponse([
                'section' => $result['section'],
                'subLinks' => $result['subLinks'],
            ], Http::STATUS_CREATED);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Update an existing section
     *
     * @param int $id
     * @param string $title
     * @param string $description
     * @param string $mainLinkText
     * @param string $mainLinkUrl
     * @param array $subLinks
     * @return DataResponse
     */
    #[NoCSRFRequired]
    public function updateSection(
        int $id,
        string $title = '',
        string $description = '',
        string $mainLinkText = '',
        string $mainLinkUrl = '',
        array $subLinks = []
    ): DataResponse {
        try {
            $data = [
                'title' => $title,
                'description' => $description,
                'mainLinkText' => $mainLinkText,
                'mainLinkUrl' => $mainLinkUrl,
                'subLinks' => $subLinks,
            ];
            
            $result = $this->service->update($id, $data);
            
            return new DataResponse([
                'section' => $result['section'],
                'subLinks' => $result['subLinks'],
            ], Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_NOT_FOUND);
        }
    }

    /**
     * Delete a section
     *
     * @param int $id
     * @return DataResponse
     */
    #[NoCSRFRequired]
    public function deleteSection(int $id): DataResponse {
        try {
            $this->service->delete($id);
            
            return new DataResponse([
                'success' => true,
                'message' => 'Section deleted successfully',
            ], Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_NOT_FOUND);
        }
    }

    /**
     * Get settings
     *
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function getSettings(): DataResponse {
        try {
            $settings = $this->settingsService->getAll();
            
            return new DataResponse($settings, Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update settings
     *
     * @param string $supportEmail
     * @param string $supportUrl
     * @param string $environmentName
     * @return DataResponse
     */
    #[NoCSRFRequired]
    public function updateSettings(
        string $supportEmail = '',
        string $supportUrl = '',
        string $environmentName = ''
    ): DataResponse {
        try {
            $this->settingsService->setSupportUrl($supportUrl);
            $this->settingsService->setSupportEmail($supportEmail);
            $this->settingsService->setEnvironmentName($environmentName);
            
            return new DataResponse([
                'success' => true,
                'settings' => $this->settingsService->getAll(),
            ], Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse([
                'message' => $e->getMessage(),
            ], Http::STATUS_BAD_REQUEST);
        }
    }
}
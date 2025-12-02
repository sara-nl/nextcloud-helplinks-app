<?php
namespace OCA\HelpLinks\Controller;

use OCA\HelpLinks\AppInfo\Application;
use OCA\HelpLinks\Service\SectionService;
use OCA\HelpLinks\Service\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\App\IAppManager;

class SectionController extends Controller {
    private $service;
    private $appManager;
    private $settingsService;

    public function __construct(IRequest $request, SectionService $service, IAppManager $appManager, SettingsService $settingsService) {
        parent::__construct(Application::APP_ID, $request);
        $this->service = $service;
        $this->appManager = $appManager;
        $this->settingsService = $settingsService;
    }

    /**
     * @NoAdminRequired
     */
    public function index(): DataResponse {
        $sections = $this->service->findAll();
        $settings = $this->settingsService->getAll();
        
        // Add Introvox enabled status
        return new DataResponse([
            'sections' => $sections,
            'introvoxEnabled' => $this->appManager->isEnabledForUser('introvox'),
            'supportEmail' => $settings['supportEmail'],
            'supportUrl' => $settings['supportUrl'],
            'environmentName' => $settings['environmentName'],
        ]);
    }

    /**
     * @AdminRequired
     */
    public function create(string $title, string $description, string $mainLinkText, 
                          string $mainLinkUrl, array $subLinks, int $sortOrder): DataResponse {
        $data = [
            'title' => $title,
            'description' => $description,
            'mainLinkText' => $mainLinkText,
            'mainLinkUrl' => $mainLinkUrl,
            'subLinks' => $subLinks,
            'sortOrder' => $sortOrder,
        ];
        
        return new DataResponse($this->service->create($data));
    }

    /**
     * @AdminRequired
     */
    public function update(int $id, string $title, string $description, 
                          string $mainLinkText, string $mainLinkUrl, array $subLinks): DataResponse {
        $data = [
            'title' => $title,
            'description' => $description,
            'mainLinkText' => $mainLinkText,
            'mainLinkUrl' => $mainLinkUrl,
            'subLinks' => $subLinks,
        ];
        
        return new DataResponse($this->service->update($id, $data));
    }

    /**
     * @AdminRequired
     */
    public function destroy(int $id): DataResponse {
        $this->service->delete($id);
        return new DataResponse(['success' => true]);
    }

    /**
     * @AdminRequired
     */
    public function reorder(array $order): DataResponse {
        $this->service->reorder($order);
        return new DataResponse(['success' => true]);
    }

    /**
     * @AdminRequired
     */
    public function saveSettings(string $supportEmail, string $environmentName): DataResponse {
        $this->settingsService->setSupportEmail($supportEmail);
        $this->settingsService->setEnvironmentName($environmentName);
        
        return new DataResponse([
            'success' => true,
            'settings' => $this->settingsService->getAll()
        ]);
    }
}
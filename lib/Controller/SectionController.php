<?php
namespace OCA\HelpLinks\Controller;

use OCA\HelpLinks\AppInfo\Application;
use OCA\HelpLinks\Service\SectionService;
use OCA\HelpLinks\Service\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\App\IAppManager;

class SectionController extends Controller {
    private $service;
    private $appManager;
    private $settingsService;
    private $userSession;

    public function __construct(
        IRequest $request,
        SectionService $service,
        IAppManager $appManager,
        SettingsService $settingsService,
        IUserSession $userSession,
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->service = $service;
        $this->appManager = $appManager;
        $this->settingsService = $settingsService;
        $this->userSession = $userSession;
    }

    #[NoAdminRequired]
    public function index(): DataResponse {
        $sections = $this->service->findAll();
        $settings = $this->settingsService->getAll();
        
        // Get user's Federeated OCM Cloud ID
        $user = $this->userSession->getUser();
        $cloudId = $user ? $user->getCloudId() : '';

        return new DataResponse([
            'sections' => $sections,
            'introvoxEnabled' => $this->appManager->isEnabledForUser('introvox'),
            'talkEnabled' => $this->appManager->isEnabledForUser('spreed'),
            'supportEmail' => $settings['supportEmail'],
            'supportUrl' => $settings['supportUrl'],
            'environmentName' => $settings['environmentName'],
            'cloudId' => $cloudId,
        ]);
    }

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

    public function destroy(int $id): DataResponse {
        $this->service->delete($id);
        return new DataResponse(['success' => true]);
    }

    public function reorder(array $order): DataResponse {
        $this->service->reorder($order);
        return new DataResponse(['success' => true]);
    }

    public function saveSettings(string $supportEmail, string $supportUrl, string $environmentName): DataResponse {
        $this->settingsService->setSupportEmail($supportEmail);
        $this->settingsService->setSupportUrl($supportUrl);
        $this->settingsService->setEnvironmentName($environmentName);
        
        return new DataResponse([
            'success' => true,
            'settings' => $this->settingsService->getAll()
        ]);
    }
}
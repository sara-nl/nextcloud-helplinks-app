<?php
namespace OCA\HelpLinks\Controller;

use OCA\HelpLinks\AppInfo\Application;
use OCA\HelpLinks\Service\SectionService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SectionController extends Controller {
    private $service;

    public function __construct(IRequest $request, SectionService $service) {
        parent::__construct(Application::APP_ID, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     */
    public function index(): DataResponse {
        return new DataResponse($this->service->findAll());
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
}
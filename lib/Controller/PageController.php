<?php
namespace OCA\HelpLinks\Controller;

use OCA\HelpLinks\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
    public function __construct(IRequest $request) {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        Util::addScript(Application::APP_ID, 'helplinks-main');
        Util::addStyle(Application::APP_ID, 'style');
        
        return new TemplateResponse(Application::APP_ID, 'main');
    }
}
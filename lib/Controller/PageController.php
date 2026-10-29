<?php
namespace OCA\HelpLinks\Controller;

use OCA\HelpLinks\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
    public function __construct(IRequest $request) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        Util::addScript(Application::APP_ID, 'helplinks-main');
        Util::addStyle(Application::APP_ID, 'helplinks-main');
        // IntroVox's list of tours this user can start, <introvox-tour-list>,
        // in the Introduction tour card (styled in App.vue). IntroVox is
        // optional here, and may be enabled for some groups only.
        if (class_exists(\OCA\IntroVox\Embed\TourListEmbed::class)) {
            $tourList = \OCP\Server::get(\OCA\IntroVox\Embed\TourListEmbed::class);
            if ($tourList->isAvailable()) {
                $tourList->load();
            }
        }

        return new TemplateResponse(Application::APP_ID, 'main');
    }
}
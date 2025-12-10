<?php
namespace OCA\HelpLinks\Service;

use Exception;
use OCA\HelpLinks\Db\Section;
use OCA\HelpLinks\Db\SectionMapper;
use OCA\HelpLinks\Db\SubLink;
use OCA\HelpLinks\Db\SubLinkMapper;
use OCP\AppFramework\Db\DoesNotExistException;

class SectionService {
    private $sectionMapper;
    private $subLinkMapper;

    public function __construct(SectionMapper $sectionMapper, SubLinkMapper $subLinkMapper) {
        $this->sectionMapper = $sectionMapper;
        $this->subLinkMapper = $subLinkMapper;
    }

    public function findAll(): array {
        $sections = $this->sectionMapper->findAll();
        $result = [];

        foreach ($sections as $section) {
            $subLinks = $this->subLinkMapper->findBySection($section->getId());
            $result[] = [
                'section' => $section,
                'subLinks' => $subLinks,
            ];
        }

        return $result;
    }

    public function create(array $data): array {
        $section = new Section();
        $section->setTitle($data['title'] ?? '');
        $section->setDescription($data['description'] ?? '');
        $section->setMainLinkText($data['mainLinkText'] ?? '');
        $section->setMainLinkUrl($data['mainLinkUrl'] ?? '');
        $section->setSortOrder($data['sortOrder'] ?? 0);
        $section->setCreatedAt(time());

        $section = $this->sectionMapper->insert($section);

        $subLinks = [];
        if (isset($data['subLinks']) && is_array($data['subLinks'])) {
            foreach ($data['subLinks'] as $index => $subLinkData) {
                $subLink = new SubLink();
                $subLink->setSectionId($section->getId());
                $subLink->setText($subLinkData['text'] ?? '');
                $subLink->setUrl($subLinkData['url'] ?? '');
                $subLink->setSortOrder($index);
                $subLinks[] = $this->subLinkMapper->insert($subLink);
            }
        }

        return [
            'section' => $section,
            'subLinks' => $subLinks,
        ];
    }

    public function update(int $id, array $data): array {
        try {
            $section = $this->sectionMapper->find($id);

            $section->setTitle($data['title'] ?? $section->getTitle());
            $section->setDescription($data['description'] ?? $section->getDescription());
            $section->setMainLinkText($data['mainLinkText'] ?? $section->getMainLinkText());
            $section->setMainLinkUrl($data['mainLinkUrl'] ?? $section->getMainLinkUrl());

            $section = $this->sectionMapper->update($section);

            // Replace sublinks
            $this->subLinkMapper->deleteBySection($id);

            $subLinks = [];
            if (isset($data['subLinks']) && is_array($data['subLinks'])) {
                foreach ($data['subLinks'] as $index => $subLinkData) {
                    $subLink = new SubLink();
                    $subLink->setSectionId($id);
                    $subLink->setText($subLinkData['text'] ?? '');
                    $subLink->setUrl($subLinkData['url'] ?? '');
                    $subLink->setSortOrder($index);
                    $subLinks[] = $this->subLinkMapper->insert($subLink);
                }
            }

            return [
                'section' => $section,
                'subLinks' => $subLinks,
            ];

        } catch (DoesNotExistException $e) {
            throw new Exception('Section not found');
        }
    }

    public function delete(int $id): void {
        try {
            $section = $this->sectionMapper->find($id);
            $this->subLinkMapper->deleteBySection($id);
            $this->sectionMapper->delete($section);
        } catch (DoesNotExistException $e) {
            throw new Exception('Section not found');
        }
    }

    public function reorder(array $orderData): void {
        foreach ($orderData as $item) {
            $this->sectionMapper->updateSortOrder($item['id'], $item['sortOrder']);
        }
    }
}
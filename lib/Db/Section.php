<?php
namespace OCA\HelpLinks\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method string getMainLinkText()
 * @method void setMainLinkText(string $text)
 * @method string getMainLinkUrl()
 * @method void setMainLinkUrl(string $url)
 * @method int getSortOrder()
 * @method void setSortOrder(int $order)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $timestamp)
 */
class Section extends Entity implements JsonSerializable {
    protected $title;
    protected $description;
    protected $mainLinkText;
    protected $mainLinkUrl;
    protected $sortOrder;
    protected $createdAt;

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'mainLinkText' => $this->mainLinkText,
            'mainLinkUrl' => $this->mainLinkUrl,
            'sortOrder' => $this->sortOrder,
            'createdAt' => $this->createdAt,
        ];
    }
}
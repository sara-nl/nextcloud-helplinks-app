<?php
namespace OCA\HelpLinks\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method int getSectionId()
 * @method void setSectionId(int $sectionId)
 * @method string getText()
 * @method void setText(string $text)
 * @method string getUrl()
 * @method void setUrl(string $url)
 * @method int getSortOrder()
 * @method void setSortOrder(int $order)
 */
class SubLink extends Entity implements JsonSerializable {
    protected $sectionId;
    protected $text;
    protected $url;
    protected $sortOrder;

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'sectionId' => $this->sectionId,
            'text' => $this->text,
            'url' => $this->url,
            'sortOrder' => $this->sortOrder,
        ];
    }
}
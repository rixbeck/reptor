<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\Property;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelDocumentAdaptor implements PropertyAdaptor
{
    public function __construct(protected Spreadsheet $spreadsheet)
    {
    }

    public function getProperties(): array
    {
        $customProperties = [];
        $properties = $this->spreadsheet->getProperties();
        foreach ($properties->getCustomProperties() as $property) {
            $customProperties[$property] = $properties->getCustomPropertyValue($property);
        }

        return [
            'title' => $properties->getTitle() ?: "''",
            'description' => $properties->getDescription() ?: "''",
            'subject' => $properties->getSubject() ?: "''",
            'creator' => $properties->getCreator() ?: "''",
            'lastModifiedBy' => $properties->getLastModifiedBy() ?: "''",
            'created' => $properties->getCreated() ? sprintf('%s', $properties->getCreated()) : "''",
            'modified' => $properties->getModified() ? sprintf('%s', $properties->getModified()) : "''",
            'category' => $properties->getCategory() ?: "''",
            'keywords' => $properties->getKeywords() ?: "''",
            'manager' => $properties->getManager() ?: "''",
            'company' => $properties->getCompany() ?: "''",

            ...$customProperties
        ];
    }
}

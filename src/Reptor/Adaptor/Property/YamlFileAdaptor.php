<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\Property;

use Symfony\Component\Yaml\Yaml;

class YamlFileAdaptor implements PropertyAdaptor
{
    public function __construct(protected string $filename, protected Yaml $yaml)
    {
    }

    public function getProperties(): array
    {
        return $this->yaml->parseFile($this->filename);
    }
}

<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

class CSVReader extends AbstractReader implements ReaderInterface
{
    /**
     * @var array<string>|false
     */
    private array|false $header;
    private ?int $count = null;

    public function __construct(protected ConnectorInterface $connector, protected bool $firstLineIsHeader = true)
    {
    }

    /**
     * @param array<mixed> $params
     */
    public function row(array $params = [], callable $postOpen = null): \Generator
    {
        rewind($fin = $this->connector->__invoke());
        if ($this->firstLineIsHeader) {
            $this->header = fgetcsv($fin);
            if ($postOpen) {
                $postOpen($this->header);
            }
        }
        $this->validateParams($params);

        while ($line = fgetcsv($fin, ...$params)) {
            yield $this->header ? array_combine($this->header, $line) : $line;
        }
    }

    /**
     * @param array<mixed> $params
     */
    private function validateParams(array $params): void
    {
        static $validParams = [
            'length',
            'delimiter',
            'enclosure',
            'escape',
        ];

        $options = array_keys($params);
        if (count($fineParams = array_intersect($options, $validParams)) !== count($params)) {
            $optionList = implode(', ', array_diff($options, $fineParams));
            throw new \InvalidArgumentException(
                sprintf('Invalid parameter(s) %s passed to CSVReader::readAll()', $optionList)
            );
        }
    }
}

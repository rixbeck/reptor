<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

class JSONReader extends AbstractReader implements ReaderInterface
{
    public function __construct(protected ConnectorInterface $connector)
    {
    }

    public function row(): \Generator
    {
        rewind($fin = $this->connector->__invoke());

        fgetc($fin) === '[' ?: throw new \RuntimeException('Invalid JSON file');
        while (($dataRow = $this->readRow($fin)) !== null) {
            yield $dataRow;
        }
    }

    protected function readRow(mixed $handle): ?array
    {
        $buffer = '';
        while (!feof($handle) && strpos('\n\r \t,', $char = fgetc($handle)));
        while (!feof($handle)) {
            $buffer .= $char ?: fgetc($handle);
            $char = false;

            $decoded = json_decode($buffer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }
}

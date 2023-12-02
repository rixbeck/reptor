<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

class PDOReader extends AbstractReader implements ReaderInterface
{
    protected string $sql;

    public function __construct(protected PDOAwareInterface $connector)
    {
    }

    public function row(array $params = [], callable $postOpen = null): \Generator
    {
        $stmt = $this->connector->getStatement($this->sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch()) {
            yield $row;
        }
    }

    public function initialize(mixed $options): self
    {
        $this->sql = (string)$options;

        return $this;
    }

    public function count(array $params = []): int
    {
        static $count = [];

        $sql = sprintf('SELECT COUNT(*) FROM (%s) __COUNT_OUTER', $this->sql);
        $key = md5($sql);
        if ($count[$key] ?? false) {
            return $count[$key];
        }

        $stmt = $this->connector->getStatement($sql);
        $stmt->execute();

        return $count[$key] = (int) $stmt->fetchColumn();
    }
}

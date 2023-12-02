<?php
/**
 * @author Rix Beck <rix@neologik.hu>
 */

namespace brix\Reptor\Adaptor\DataSource;

interface PDOAwareInterface extends ConnectorInterface
{
    public function getPDO(): \PDO;

    public function getStatement(string $sql): \PDOStatement;
}

<?php

/**
 * Class is responsible for accept connection string and instantiate the specific object
 * Constructor recieves the connection string and the available adaptors
 * The connection strig can be:
 * - valid pdo dsn string
 * - valid protocol string
 * - valid socket string.
 *
 * In case of PDO dsn string the PDOConnector will be instantiated
 * In case of protocol string it will use corresponding resource opening function
 * In case of socket string it will use corresponding socket opening function
 *
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

use brix\Reptor\Templator\Context\ContextProvider;

class ConnectorProvider implements ConnectorProviderInterface
{
    private string $connectionString;

    public function __construct(private array $adaptors, private ?string $dataFormat = null)
    {
    }

    public function __invoke(string $connectionString, ContextProvider $contextProvider, ...$args): ReaderInterface
    {
        $this->connectionString = $connectionString;

        return $this->createReader($this->createConnector($contextProvider, ...$args), ...$args);
    }

    protected function createConnector(ContextProvider $contextProvider, ...$args)
    {
        if (strpos($this->connectionString, 'pdo:') === 0) {
            return new PDOConnector(substr($this->connectionString, 4));
        }

        if (strpos($this->connectionString, 'socket://') === 0) {
            return new SocketConnector($this->connectionString, ...$args);
        }

        if (strpos($this->connectionString, 'array://') === 0) {
            return new ArrayConnector($this->connectionString, $contextProvider, ...$args);
        }

        if (preg_match('/^([a-zA-Z]+):\\/\\//', $this->connectionString)) {
            return new ProtocolConnector($this->connectionString, ...$args);
        }

        throw new \InvalidArgumentException('Invalid connection string.');
    }

    protected function createReader(ConnectorInterface $connector, ...$args): ReaderInterface
    {
        $this->dataFormat ??= $connector->getDataFormat() ?? throw new \InvalidArgumentException(
            'Data format cannot be guessed. Set explicitly.'
        );

        if (!isset($this->adaptors[$this->dataFormat])) {
            throw new \InvalidArgumentException('Invalid data format.');
        }

        $reader = new $this->adaptors[$this->dataFormat]($connector, ...$args);

        return $reader;
    }
}

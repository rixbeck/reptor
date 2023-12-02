<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

/**
 * Class SocketConnector
 * Allows to connect to a socket.
 * Connection string should be in the following format:
 * socket://<host>:<port>.
 *
 * @author Rix Beck <rix@neologik.hu>
 */
class SocketConnector implements ConnectorInterface
{
    private string $connectionString;
    private $handle;

    public function __invoke()
    {
        return $this->getHandle();
    }

    /**
     * Constructor receives the connection string.
     *
     * @param string $connectionString the connection string
     */
    public function __construct(string $connectionString)
    {
        $this->connectionString = $connectionString;
        $this->connect();
    }

    public function getDataFormat(): ?string
    {
        return null;
    }

    /**
     * Connect to the socket based on the host and port.
     *
     * @throws \RuntimeException if the connection fails
     */
    private function connect(): void
    {
        $parsedUrl = parse_url($this->connectionString);
        $host = $parsedUrl['host'] ?? '';
        $port = $parsedUrl['port'] ?? 80;

        $this->handle = fsockopen($host, $port, $errno, $errstr, 30);

        if (!$this->handle) {
            throw new \RuntimeException("Failed to open socket: $errstr ($errno)");
        }
    }

    /**
     * Get the socket handle.
     *
     * @return resource the socket handle
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Destructor closes the socket handle.
     */
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}

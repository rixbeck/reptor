<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

use brix\Reptor\Adaptor\ProtoWrapper\RawDataWrapper;

/**
 * Class ProtocolConnector
 * Allows to connect to a data source based on the protocol.
 * Connection string should be in the following format:
 * <protocol>://<connection_string>
 *     - protocol: one of the supported protocols
 *     - connection_string: the connection string for the protocol
 * Example:
 *  http://example.com/data.json
 *  file:///tmp/data.csv
 *  data://text/plain;base64,SGVsbG8sIFdvcmxkIQ%3D%3D
 *  json://data
 *
 * For Raw data protocols the connection string should be in the following format:
 * <protocol>://<data>
 *      - protocol: one of the supported protocols
 *      - data: variable name in the specified context
 *
 * @package brix\Reptor\Adaptor\DataSource
 */
class ProtocolConnector implements ConnectorInterface
{
    public const RAW_DATA_PROTOCOLS = ['json', 'xml', 'csv'];
    public const FILE_PROTOCOLS = ['file', 'http', 'https', 'ftp', 'ftps', 'php', 'zlib', 'data'];
    protected array $protocols;
    private $handle;

    /**
     * Constructor receives the connection string.
     *
     * @param string $connectionString the connection string
     */
    public function __construct(private string $connectionString, protected array $context = [])
    {
        $this->protocols = array_merge(self::RAW_DATA_PROTOCOLS, self::FILE_PROTOCOLS);
        RawDataWrapper::setVariables($context);
        $this->initMemWrapper();
        $this->connect();
    }

    /**
     * Destructor closes the resource handle.
     */
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    public function __invoke()
    {
        return $this->getHandle();
    }

    public function getDataFormat(): ?string
    {
        $scheme = parse_url($this->connectionString, PHP_URL_SCHEME);
        if ($scheme === 'file') {
            return pathinfo($this->connectionString, PATHINFO_EXTENSION);
        }
        if (in_array('scheme', self::RAW_DATA_PROTOCOLS)) {
            return $scheme;
        }

        return null;
    }

    /**
     * Get the resource handle.
     *
     * @return resource the resource handle
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Connect to the resource based on the protocol.
     *
     * @throws \InvalidArgumentException if the protocol is unsupported
     * @throws \RuntimeException         if the connection fails
     */
    private function connect(): void
    {
        $scheme = parse_url($this->connectionString, PHP_URL_SCHEME);

        if (!in_array($scheme, $this->protocols)) {
            throw new \InvalidArgumentException('Unsupported protocol.');
        }

        $this->handle = fopen($this->connectionString, 'r');

        if ($this->handle === false) {
            throw new \RuntimeException('Failed to open connection.');
        }
    }

    private function initMemWrapper(): void
    {
        static $initialized = false;
        if (!$initialized) {
            foreach (self::RAW_DATA_PROTOCOLS as $memProto) {
                stream_wrapper_register($memProto, RawDataWrapper::class);
            }
            $initialized = true;
        }
    }
}

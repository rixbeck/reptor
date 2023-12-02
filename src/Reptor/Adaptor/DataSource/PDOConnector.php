<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\DataSource;

/**
 * Class PDOConnector
 * Allows to use a PDO connection in specified context as a data source. Connection string should be in the following format:
 * pdo:mysql://user:pass@host:port/dbname?charset=utf8mb4
 *  - user: username for the connection
 *  - pass: password for the connection
 *  - host: host for the connection
 *  - port: port for the connection
 *  - dbname: database name for the connection
 *  - charset: charset for the connection
 *
 * @author Rix Beck <rix@neologik.hu>
 */
class PDOConnector implements PDOAwareInterface
{
    protected string $dsn;
    protected string $password = '';
    protected string $username = '';
    protected array $options = [];
    private \PDO $pdo;
    protected array $statements = [];


    /**
     * Create a PDO connection with the given universal, uri based DSN.
     *
     * @param string $universalDSN mysql://user:pass@host:port/dbname?charset=utf8mb4
     * @param string $username     Optional, if not provided it will be parsed from DSN
     * @param string $password     Optional, if not provided it will be parsed from DSN
     * @param array  $options      Optional, if not provided it will be parsed from DSN, merged with provided options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $universalDSN, string $username = '', string $password = '', array $options = [])
    {
        $this->username = $username;
        $this->password = $password;
        $this->parseDSN($universalDSN);
        $this->options = [...$this->options, ...$options];
        $this->pdo = new \PDO($this->dsn, $this->username, $this->password, $this->options);
    }

    private function parseDSN(string $dsn): array
    {
        static $parts = [
            'Scheme',
            'Host',
            'Port',
            'User',
            'Pass',
            'Path',
            'Query',
        ];

        $parameters = parse_url($dsn);

        return array_map(fn ($key) => call_user_func(
            [$this, 'parse'.$key],
            $parameters[lcfirst($key)] ?? ''
        ), $parts);
    }

    public function __invoke(): \PDO
    {
        return $this->getPDO();
    }

    public function getPDO(): \PDO
    {
        return $this->pdo;
    }

    public function getStatement(string $sql): \PDOStatement
    {
        $key = hash('sha256', $sql);

        return $this->statements[$key] ??= $this->pdo->prepare($sql);
    }

    public function getDataFormat(): string
    {
        return 'pdo';
    }

    public function __destruct()
    {
        unset($this->pdo);
    }

    protected function parseScheme(?string $scheme = ''): bool
    {
        if (!in_array($scheme, ['mysql', 'pgsql', 'sqlite', 'sqlsrv'])) {
            throw new \InvalidArgumentException('Unsupported scheme.');
        }

        $this->dsn = $scheme.':';

        return true;
    }

    protected function parseHost(?string $host = ''): bool
    {
        if (!$host) {
            throw new \InvalidArgumentException('Host is required.');
        }

        $this->dsn .= 'host='.explode(':', $host)[0].';';

        return true;
    }

    protected function parsePort(?string $port = ''): bool
    {
        if (!$port) {
            return false;
        }

        $this->dsn .= 'port='.$port.';';

        return true;
    }

    protected function parseUser(?string $user = ''): bool
    {
        if ($this->username !== '') {
            return false;
        }

        if (!$user) {
            throw new \InvalidArgumentException('User is required.');
        }

        $this->username = $user;

        return true;
    }

    protected function parsePass(?string $pass = ''): bool
    {
        if ($this->password !== '' || !$pass) {
            return false;
        }

        $this->password = $pass;

        return true;
    }

    protected function parsePath(?string $path = ''): bool
    {
        if (!$path) {
            throw new \InvalidArgumentException('Database name is required.');
        }

        $this->dsn .= 'dbname='.ltrim($path, '/').';';

        return true;
    }

    protected function parseQuery(?string $query = ''): bool
    {
        if (!$query) {
            return false;
        }

        parse_str($query, $parameters);

        foreach ($parameters as $key => $value) {
            $this->options[$key] = $value;
        }

        return true;
    }
}

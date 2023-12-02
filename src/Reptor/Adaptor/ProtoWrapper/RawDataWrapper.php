<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\Adaptor\ProtoWrapper;

class RawDataWrapper
{
    protected static array $variables = [];
    protected string $varname;
    protected int $position;

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->position = 0;
        $uri = parse_url($path);
        $this->varname = $uri['host'];

        return true;
    }

    public function stream_read(int $count): string|false
    {
        $ret = substr(self::$variables[$this->varname], $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    public function stream_write(string $data): int|false
    {
        $left = substr(self::$variables[$this->varname], 0, $this->position);
        $right = substr(self::$variables[$this->varname], $this->position + strlen($data));
        self::$variables[$this->varname] = $left . $data . $right;
        $this->position += strlen($data);

        return strlen($data);
    }

    public function stream_truncate(int $new_size): bool
    {
        $str = substr(self::$variables[$this->varname], 0, $new_size);
        self::$variables[$this->varname] = $str;
        $this->position = min($this->position, $new_size);

        return true;
    }

    public function stream_tell(): int|false
    {
        return $this->position;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen(self::$variables[$this->varname]);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen(self::$variables[$this->varname]) && $offset >= 0) {
                    $this->position = $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_END:
                if (strlen(self::$variables[$this->varname]) + $offset >= 0) {
                    $this->position = strlen(self::$variables[$this->varname]) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool
    {
        if ($option == STREAM_META_TOUCH) {
            $url = parse_url($path);
            $varname = $url['host'];
            if (!isset(self::$variables[$varname])) {
                self::$variables[$varname] = '';
            }
            return true;
        }
        return false;
    }

    public function stream_stat(): array
    {
        return [];
    }

    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        return false;
    }

    public function stream_lock(int $operation): bool
    {
        return true;
    }

    public function stream_flush(): bool
    {
        return true;
    }

    public function stream_close(): bool
    {
        return true;
    }

    public function unlink(string $path): bool
    {
        $url = parse_url($path);
        $varname = $url['host'];
        unset(self::$variables[$varname]);
        return true;
    }
    

    public static function set(string $key, mixed $value): void
    {
        self::$variables[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return self::$variables[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return isset(self::$variables[$key]);
    }

    public static function remove(string $key): void
    {
        unset(self::$variables[$key]);
    }

    public static function setVariables(array $variables): void
    {
        self::$variables = $variables;
    }
}

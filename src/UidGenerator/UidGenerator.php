<?php

namespace Gupalo\UidGenerator;

use Exception;
use JsonSerializable;
use LogicException;
use Throwable;

class UidGenerator
{
    public static ?string $secret = 'replace_with_real_secret';

    public static function generateUnique(UidRepositoryInterface $repository, int $length = 32): string
    {
        $infinity = 1000;
        while ($infinity-- > 0) {
            $uid = self::generate($length);

            if ($repository->findOneByUid($uid) === null) {
                return $uid;
            }
        }

        throw new LogicException('Cannot generate unique uid');
    }

    public static function generate(int $length = 32): string
    {
        for ($i = 0; $i < 100; $i++) {
            try {
                return substr(bin2hex(random_bytes(ceil($length / 2))), 0, $length);
            } catch (Exception $e) {
                usleep(100000);
            }
        }

        throw new LogicException(__METHOD__);
    }

    public static function generateNumeric(int $length = 8): string
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                $result .= random_int($i ? 0 : 1, 9);
            } catch (Throwable $e) {
                /** @noinspection RandomApiMigrationInspection */
                $result = mt_rand($i ? 0 : 1, 9);
            }
        }

        return $result;
    }

    /**
     * @param string|JsonSerializable|array $item
     * @param int $passes
     * @return string
     */
    public static function hash($item, $passes = 100): string
    {
        if (!is_string($item)) {
            try {
                $item = json_encode($item, JSON_THROW_ON_ERROR, 512);
            } catch (Throwable $e) {
                $item = (string)$item;
            }
        }

        $s = $item . self::$secret;

        for ($i = 0; $i < $passes; $i++) {
            $s = md5($s);
        }

        return $s;
    }
}

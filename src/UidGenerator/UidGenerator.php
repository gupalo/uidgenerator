<?php /** @noinspection UnknownInspectionInspection */

namespace Gupalo\UidGenerator;

use Exception;
use JsonSerializable;
use LogicException;
use Throwable;

class UidGenerator
{
    public static $secret = 'replace_with_real_secret';

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

    public static function generateUnique(UidRepositoryInterface $repository, int $length = 32, int $countTries = 1000): string
    {
        while ($countTries-- > 0) {
            $uid = self::generate($length);

            if ($repository->findOneByUid($uid) === null) {
                return $uid;
            }
        }

        throw new LogicException('Cannot generate unique uid');
    }

    public static function generateNumericUnique(UidRepositoryInterface $repository, int $length = 8, int $countTries = 1000): string
    {
        while ($countTries-- > 0) {
            $uid = self::generateNumeric($length);

            if ($repository->findOneByUid($uid) === null) {
                return $uid;
            }
        }

        throw new LogicException('Cannot generate unique uid');
    }

    /**
     * @param string|JsonSerializable|array $item
     */
    public static function hash($item, int $passes = 100): string
    {
        if (!is_string($item)) {
            try {
                $item = json_encode($item);
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

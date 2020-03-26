<?php

namespace Gupalo\UidGenerator;

interface UidRepositoryInterface
{
    public function findOneByUid(string $uid): ?UidEntityInterface;
}

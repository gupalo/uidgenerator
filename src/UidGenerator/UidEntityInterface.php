<?php

namespace Gupalo\UidGenerator;

interface UidEntityInterface
{
    public function getUid(): string;

    public function setUid(string $uid): self;
}

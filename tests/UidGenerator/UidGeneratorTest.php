<?php

namespace Gupalo\Tests\UidGenerator;

use Gupalo\UidGenerator\UidEntityInterface;
use Gupalo\UidGenerator\UidGenerator;
use Gupalo\UidGenerator\UidRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class UidGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $this->assertRegExp('#^[a-f\d]{32}$#', UidGenerator::generate(32));
        $this->assertRegExp('#^[a-f\d]{8}$#', UidGenerator::generate(8));
        $this->assertRegExp('#^[a-f\d]{9}$#', UidGenerator::generate(9));
    }

    public function testHash(): void
    {
        UidGenerator::$secret = 'real_secret';
        $this->assertSame('15ec3f37c6bc7ae6c3464b082d5d320e', UidGenerator::hash(['test' => '123']));
    }

    public function testGenerateNumeric(): void
    {
        $this->assertRegExp('#^[\d]{10}$#', UidGenerator::generateNumeric(10));
    }

    public function testGenerateUnique(): void
    {
        /** @var UidRepositoryInterface $repository */
        $repository = $this->prophesize(UidRepositoryInterface::class);
        /** @var UidEntityInterface $entity */
        $entity = $this->prophesize(UidEntityInterface::class);

        $repository->findOneByUid(Argument::type('string'))->shouldBeCalled(2)->willReturn($entity->reveal(), null);

        UidGenerator::generateUnique($repository->reveal(), 32);
        $this->assertRegExp('#^[a-f\d]{32}$#', UidGenerator::generate(32));
    }

    public function testGenerateUnique_CannotGenerate(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot generate unique uid');

        /** @var UidRepositoryInterface $repository */
        $repository = $this->prophesize(UidRepositoryInterface::class);
        /** @var UidEntityInterface $entity */
        $entity = $this->prophesize(UidEntityInterface::class);

        $repository->findOneByUid(Argument::type('string'))->shouldBeCalled(1000)->willReturn($entity->reveal());

        UidGenerator::generateUnique($repository->reveal(), 32);
    }
}

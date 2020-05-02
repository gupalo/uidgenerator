<?php /** @noinspection PhpUndefinedMethodInspection */

namespace Gupalo\Tests\UidGenerator;

use Gupalo\UidGenerator\UidEntityInterface;
use Gupalo\UidGenerator\UidGenerator;
use Gupalo\UidGenerator\UidRepositoryInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class UidGeneratorTest extends TestCase
{
    use ProphecyTrait;

    public function testGenerate(): void
    {
        $this->assertMatchesRegularExpression('#^[a-f\d]{32}$#', UidGenerator::generate(32));
        $this->assertMatchesRegularExpression('#^[a-f\d]{8}$#', UidGenerator::generate(8));
        $this->assertMatchesRegularExpression('#^[a-f\d]{9}$#', UidGenerator::generate(9));

        $uids = [];
        for ($i = 0; $i < 100; $i++) {
            $uids[] = UidGenerator::generate(32);
        }
        $uids = array_unique($uids);

        $this->assertCount(100, $uids);
    }

    public function testHash(): void
    {
        UidGenerator::$secret = 'real_secret';
        $this->assertSame('15ec3f37c6bc7ae6c3464b082d5d320e', UidGenerator::hash(['test' => '123']));
    }

    public function testGenerateNumeric(): void
    {
        $this->assertMatchesRegularExpression('#^[\d]{10}$#', UidGenerator::generateNumeric(10));

        $uids = [];
        for ($i = 0; $i < 100; $i++) {
            $uids[] = UidGenerator::generateNumeric(8);
        }
        $uids = array_unique($uids);

        $this->assertCount(100, $uids);
    }

    public function testGenerateUnique(): void
    {
        /** @var UidRepositoryInterface $repository */
        $repository = $this->prophesize(UidRepositoryInterface::class);
        /** @var UidEntityInterface $entity */
        $entity = $this->prophesize(UidEntityInterface::class);

        $repository->findOneByUid(Argument::type('string'))->shouldBeCalled(2)->willReturn($entity->reveal(), null);

        $this->assertMatchesRegularExpression('#^[a-f\d]{32}$#', UidGenerator::generateUnique($repository->reveal(), 32));
    }

    public function testGenerateUnique_CannotGenerate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot generate unique uid');

        /** @var UidRepositoryInterface $repository */
        $repository = $this->prophesize(UidRepositoryInterface::class);
        /** @var UidEntityInterface $entity */
        $entity = $this->prophesize(UidEntityInterface::class);

        $repository->findOneByUid(Argument::type('string'))->shouldBeCalled(1000)->willReturn($entity->reveal());

        UidGenerator::generateUnique($repository->reveal(), 32);
    }

    public function testGenerateNumericUnique(): void
    {
        /** @var UidRepositoryInterface $repository */
        $repository = $this->prophesize(UidRepositoryInterface::class);
        /** @var UidEntityInterface $entity */
        $entity = $this->prophesize(UidEntityInterface::class);

        $repository->findOneByUid(Argument::type('string'))->shouldBeCalled(2)->willReturn($entity->reveal(), null);

        $this->assertMatchesRegularExpression('#^[\d]{8}$#', UidGenerator::generateNumericUnique($repository->reveal(), 8));
    }

    public function testGenerateNumericUnique_CannotGenerate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot generate unique uid');

        /** @var UidRepositoryInterface $repository */
        $repository = $this->prophesize(UidRepositoryInterface::class);
        /** @var UidEntityInterface $entity */
        $entity = $this->prophesize(UidEntityInterface::class);

        $repository->findOneByUid(Argument::type('string'))->shouldBeCalled(1000)->willReturn($entity->reveal());

        UidGenerator::generateNumericUnique($repository->reveal(), 32);
    }
}

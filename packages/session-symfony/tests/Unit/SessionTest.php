<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\SessionSymfony\FlashBag;
use Concept\Extensions\SessionSymfony\Session;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class SessionTest extends TestCase
{
    public function testGetFlashBagReturnsConceptFlashBag(): void
    {
        $session = new Session(new MockArraySessionStorage(), flashes: new FlashBag());

        $this->assertInstanceOf(FlashBag::class, $session->getFlashBag());
    }

    public function testGetFlashBagThrowsForUnsupportedFlashBagImplementation(): void
    {
        $session = new Session(new MockArraySessionStorage());

        $this->expectException(LogicException::class);
        $session->getFlashBag();
    }
}

<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\SessionSymfony\FlashBag;
use Concept\Extensions\SessionSymfony\Protocol\FlashType;
use PHPUnit\Framework\TestCase;

final class FlashBagTest extends TestCase
{
    public function testAddMethodsUseExpectedFlashTypes(): void
    {
        $bag = new FlashBag();
        $bag->addError('error');
        $bag->addInfo('info');
        $bag->addSuccess('success');
        $bag->addWarning('warning');

        $this->assertSame(['error'], $bag->get(FlashType::ERROR));
        $this->assertSame(['info'], $bag->get(FlashType::INFO));
        $this->assertSame(['success'], $bag->get(FlashType::SUCCESS));
        $this->assertSame(['warning'], $bag->get(FlashType::WARNING));
    }
}

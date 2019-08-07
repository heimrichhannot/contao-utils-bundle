<?php


namespace HeimrichHannot\UtilsBundle\Tests;


use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Trait ContaoFrameworkTrait
 *
 * Overrides ContaoTestCase::mockContaoFramework to replace ContaoFrameworkInterface with ContaoFramework
 *
 * @package HeimrichHannot\UtilsBundle\Tests
 */
trait ContaoFrameworkTrait
{
    /**
     * Mocks the Contao framework with optional adapters.
     *
     * A Config adapter with the default Contao configuration will be added
     * automatically if no Config adapter is given.
     *
     * @param array $adapters
     *
     * @return ContaoFramework|MockObject
     */
    protected function mockContaoFramework(array $adapters = []): ContaoFramework
    {
        $this->addConfigAdapter($adapters);

        $framework = $this->createMock(ContaoFramework::class);

        $framework
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $framework
            ->method('getAdapter')
            ->willReturnCallback(
                function (string $key) use ($adapters): ?Adapter {
                    return $adapters[$key] ?? null;
                }
            )
        ;

        return $framework;
    }
}
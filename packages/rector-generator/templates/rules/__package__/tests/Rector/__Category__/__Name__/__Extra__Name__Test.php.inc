<?php

declare(strict_types=1);

namespace Rector\__Package__\Tests\Rector\__Category__\__Name__;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class __Name__Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo, string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $this->doTestFileInfo($fileInfo);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): \Iterator
    {
        yield [new \Symplify\SmartFileSystem\SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc'), '__ExtraFileName__', __DIR__ . '/Source/extra_file.php'];
    }

    protected function getRectorClass(): string
    {
        return \Rector\__Package__\Rector\__Category__\__Name__::class;
    }
}

<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 04.01.17
 */

namespace Dopamedia\Measure\Test\Unit;

use Dopamedia\Measure\Model\UnitConverter;

class UnitConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\Measure\Model\Config
     */
    private $configMock;

    /**
     * @var UnitConverter
     */
    private $unitConverter;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Dopamedia\Measure\Model\ConfigInterface');
        $this->unitConverter = new UnitConverter(
            $this->configMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConvertThrowsExceptionBecauseUnitCouldNotBeFound()
    {
        $this->configMock->expects($this->once())->method('getUnit')->willReturn([]);
        $this->unitConverter->convert('UNIT_CODE', 'UNIT_CODE', 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConvertThrowsExceptionBecauseFamiliesAreNotTheSame()
    {
        $this->configMock->expects($this->any())->method('getUnit')->willReturn(['UNIT_CODE']);

        $this->configMock->expects($this->at(2))
            ->method('getUnitFamilyCode')
            ->willReturn('LEFT_FAMILY_CODE');

        $this->configMock->expects($this->at(3))
            ->method('getUnitFamilyCode')
            ->willReturn('RIGHT_FAMILY_CODE');

        $this->unitConverter->convert('BASE_UNIT_CODE', 'REFERENCE_UNIT_CODE', 1);
    }

    public function testConvert()
    {
        $this->configMock->expects($this->any())->method('getUnit')->willReturn(['UNIT_CODE']);

        $this->configMock->expects($this->at(2))
            ->method('getUnitFamilyCode')
            ->willReturn('FAMILY_CODE');

        $this->configMock->expects($this->at(3))
            ->method('getUnitFamilyCode')
            ->willReturn('FAMILY_CODE');

        $this->configMock->expects($this->at(4))
            ->method('getUnitConversionStrategies')
            ->willReturn(['mul' => 0.001]);

        $this->configMock->expects($this->at(5))
            ->method('getUnitConversionStrategies')
            ->willReturn(['mul' => 0.01]);

        $this->assertSame(
            0.5,
            $this->unitConverter->convert('MILLIMETRE', 'CENTIMETRE', 5)
        );
    }

    public function testCheckIfUnitExistsReturnsTrue()
    {
        $this->configMock->expects($this->once())->method('getUnit')->willReturn(['notempty']);
        $this->assertTrue($this->unitConverter->checkIfUnitExists('UNIT_CODE'));
    }

    public function testCheckIfUnitExistsReturnsFalse()
    {
        $this->configMock->expects($this->once())->method('getUnit')->willReturn([]);
        $this->assertFalse($this->unitConverter->checkIfUnitExists('UNIT_CODE'));
    }

    public function testCompareFamiliesWillReturnTrue()
    {
        $this->configMock->expects($this->exactly(2))->method('getUnitFamilyCode')->willReturn('UNIT_CODE');
        $this->assertTrue($this->unitConverter->compareUnitFamilies('left', 'right'));
    }

    public function testCompareFamiliesWillReturnFalse()
    {
        $this->configMock->expects($this->at(0))->method('getUnitFamilyCode')->willReturn('UNIT_CODE_LEFT');
        $this->configMock->expects($this->at(1))->method('getUnitFamilyCode')->willReturn('UNIT_CODE_RIGHT');
        $this->assertFalse($this->unitConverter->compareUnitFamilies('left', 'right'));
    }

    public function testConvertBaseToStandard()
    {
        $this->configMock->expects($this->once())->method('getUnitConversionStrategies')->willReturn(['mul' => 0.01]);
        $this->assertSame(
            0.05,
            $this->unitConverter->convertBaseToStandard('UNIT_CODE', 5)
        );
    }

    public function testConvertStandardToResult()
    {
        $this->configMock->expects($this->once())->method('getUnitConversionStrategies')->willReturn(['mul' => 0.01, 'div' => 3.5]);
        $this->assertSame(
            2450.0,
            $this->unitConverter->convertStandardToResult('UNIT_CODE', 7)
        );
    }
}

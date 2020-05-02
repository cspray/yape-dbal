<?php declare(strict_types=1);

namespace Cspray\Yape\Dbal;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

use Cspray\Yape\Dbal\Support\EnumTypeStub;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 *
 * @package Cspray\Yape\Test\Doctrine
 * @license See LICENSE in source root
 */
class AbstractEnumTypeTest extends TestCase {

    private $subject;
    /** @var AbstractPlatform */
    private $platform;

    /**
     * @throws DBALException
     */
    public function setUp() : void {
        if (!Type::hasType('enum_type_stub')) {
            Type::addType('enum_type_stub', EnumTypeStubType::class);
        }

        $this->subject = Type::getType('enum_type_stub');
        $this->platform = $this->getMockBuilder(AbstractPlatform::class)->getMock();
    }

    public function testDoesRequireSqlComment() {
        $this->assertTrue($this->subject->requiresSQLCommentHint($this->platform));
    }

    public function testSqlDeclarationReturnsVarcharMaxLength() {
        $this->platform->expects($this->once())
            ->method('getVarcharTypeDeclarationSQL')
            ->with(['length' => 255])
            ->willReturn('VARCHAR(255)');

        $this->assertSame('VARCHAR(255)', $this->subject->getSQLDeclaration([], $this->platform));
    }

    public function testConvertNullToPhpValue() {
        $this->assertNull($this->subject->convertToPHPValue(null, $this->platform));
    }

    public function testConvertValidEnumNameToPHPValue() {
        $this->assertSame(EnumTypeStub::Foo(), $this->subject->convertToPHPValue('Foo', $this->platform));
    }

    public function testConvertInvalidEnumNameToPhpValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "something" to Doctrine Type enum_type_stub');

        $this->subject->convertToPHPValue('something', $this->platform);
    }

    public function testConvertEnumToPhpValue() {
        $this->assertSame(EnumTypeStub::Baz(), $this->subject->convertToPHPValue(EnumTypeStub::Baz(), $this->platform));
    }

    public function testConvertArrayToPhpValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'array\' to type \'enum_type_stub\'. Expected one of the following types: string');

        $this->subject->convertToPHPValue([], $this->platform);
    }

    public function testConvertIntegerToPhpValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'1\' of type \'integer\' to type \'enum_type_stub\'. Expected one of the following types: string');

        $this->subject->convertToPHPValue(1, $this->platform);
    }

    public function testConvertDoubleToPhpValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'3.14\' of type \'double\' to type \'enum_type_stub\'. Expected one of the following types: string');

        $this->subject->convertToPHPValue(3.14, $this->platform);
    }

    public function testConvertResourceToPhpValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'resource\' to type \'enum_type_stub\'. Expected one of the following types: string');

        $this->subject->convertToPHPValue(STDIN, $this->platform);
    }

    public function testConvertObjectToPhpValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'stdClass\' to type \'enum_type_stub\'. Expected one of the following types: string');

        $this->subject->convertToPHPValue(new stdClass(), $this->platform);
    }

    public function testConvertNullToDatabaseValue() {
        $this->assertNull($this->subject->convertToDatabaseValue(null, $this->platform));
    }

    public function testConvertValidEnumToDatabaseValue() {
        $this->assertSame('Bar', $this->subject->convertToDatabaseValue(EnumTypeStub::Bar(), $this->platform));
    }

    public function testConvertStringToDatabaseValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'Foo\' of type \'string\' to type \'enum_type_stub\'. Expected one of the following types: ' . EnumTypeStub::class);

        $this->subject->convertToDatabaseValue('Foo', $this->platform);
    }

    public function testConvertIntToDatabaseValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'2\' of type \'integer\' to type \'enum_type_stub\'. Expected one of the following types: ' . EnumTypeStub::class);

        $this->subject->convertToDatabaseValue(2, $this->platform);
    }

    public function testConvertDoubleToDatabaseValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'3.14\' of type \'double\' to type \'enum_type_stub\'. Expected one of the following types: ' . EnumTypeStub::class);

        $this->subject->convertToDatabaseValue(3.14, $this->platform);
    }

    public function testConvertArrayToDatabaseValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'array\' to type \'enum_type_stub\'. Expected one of the following types: ' . EnumTypeStub::class);

        $this->subject->convertToDatabaseValue([], $this->platform);
    }

    public function testConvertResourceToDatabaseValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'resource\' to type \'enum_type_stub\'. Expected one of the following types: ' . EnumTypeStub::class);

        $this->subject->convertToDatabaseValue(STDIN, $this->platform);
    }

    public function testConvertObjectToDatabaseValue() {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'stdClass\' to type \'enum_type_stub\'. Expected one of the following types: '  . EnumTypeStub::class);

        $this->subject->convertToDatabaseValue(new stdClass(), $this->platform);
    }
}

class EnumTypeStubType extends AbstractEnumType {

    protected function getSupportedEnumType() : string {
        return EnumTypeStub::class;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName() {
        return 'enum_type_stub';
    }
}
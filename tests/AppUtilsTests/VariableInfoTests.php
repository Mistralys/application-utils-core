<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\NamedClosure;
use Closure;
use PHPUnit\Framework\TestCase;
use AppUtils\VariableInfo;
use function AppUtils\parseVariable;

final class VariableInfoTests extends TestCase
{
    public function test_variables() : void
    {
        $tests = array(
            array(
                'label' => 'Static class method',
                'value' => array('foo' => 'bar'),
                'type' => VariableInfo::TYPE_ARRAY,
                'string' => print_r(array('foo' => 'bar'), true)
            ),
            array(
                'label' => 'Boolean value',
                'value' => true,
                'type' => VariableInfo::TYPE_BOOLEAN,
                'string' => 'true'
            ),
            array(
                'label' => 'Integer value',
                'value' => 1,
                'type' => VariableInfo::TYPE_INTEGER,
                'string' => '1'
            ),
            array(
                'label' => 'Float value',
                'value' => 14.11,
                'type' => VariableInfo::TYPE_DOUBLE,
                'string' => '14.11'
            ),
            array(
                'label' => 'Class instance',
                'value' => new VariableInfoTest_DummyClass(),
                'type' => VariableInfo::TYPE_OBJECT,
                'string' => VariableInfoTest_DummyClass::class
            ),
            array(
                'label' => 'String value',
                'value' => 'Text',
                'type' => VariableInfo::TYPE_STRING,
                'string' => 'Text'
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'type' => VariableInfo::TYPE_NULL,
                'string' => 'null'
            ),
            array(
                'label' => 'Resource',
                'value' => fopen($this->assetsRootFolder.'/test-file-exists.txt', 'rb'),
                'type' => VariableInfo::TYPE_RESOURCE,
                'string' => null
            ),
            array(
                'label' => 'Function closure',
                'value' => function() {},
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => 'Closure'
            ),
            array(
                'label' => 'Object method',
                'value' => array($this, 'dummyMethod'),
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => self::class .'->'.array($this, 'dummyMethod')[1].'()'
            ),
            array(
                'label' => 'Static class method',
                'value' => array(self::class, 'dummyStaticMethod'),
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => self::class.'::'.array(self::class, 'dummyStaticMethod')[1].'()'
            ),
            array(
                'label' => 'Function name',
                'value' => 'trim',
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => 'trim()'
            ),
            array(
                'label' => 'Named closure',
                'value' => NamedClosure::fromClosure(Closure::fromCallable('trim'), 'Origin'),
                'type' => VariableInfo::TYPE_CALLABLE,
                'string' => 'Closure:Origin'
            )
        );
        
        foreach($tests as $def)
        {
            $var = parseVariable($def['value']);
            
            $this->assertEquals($def['type'], $var->getType(), $def['label'].PHP_EOL.'The type does not match.');
            
            if($def['string'] !== null) {
                $this->assertEquals($def['string'], $var->toString(), $def['label'].PHP_EOL.'The toString() result does not match');
            }
        }
    }

    public function test_isCallable() : void
    {
        $tests = array(
            array(
                'label' => 'Anonymous function',
                'variable' => function() {},
                'callable' => true
            ),
            array(
                'label' => 'Regular PHP function',
                'variable' => 'trim',
                'callable' => true
            ),
            array(
                'label' => 'Public class method array',
                'variable' => array($this, 'dummyMethod'),
                'callable' => true
            ),
            array(
                'label' => 'Static class method',
                'variable' => array(self::class, 'dummyStaticMethod'),
                'callable' => true
            ),
            array(
                'label' => 'Object method',
                'variable' => array($this, 'dummyMethod'),
                'callable' => true
            ),
            array(
                'label' => 'Vanilla closure',
                'variable' => Closure::fromCallable(array($this, 'dummyMethod')),
                'callable' => true
            ),
            array(
                'label' => 'Named closure',
                'variable' => NamedClosure::fromObject($this, 'dummyMethod'),
                'callable' => true
            ),
            array(
                'label' => 'Unknown object method',
                'variable' => array($this, 'unknownMethod'),
                'callable' => false
            ),
        );

        foreach($tests as $test)
        {
            $info = parseVariable($test['variable']);

            $this->assertSame($test['callable'], $info->isCallable(), $test['label']);
        }
    }

    public function test_enableType() : void
    {
        $tests = array(
            array(
                'label' => 'null value',
                'value' => null,
                'string' => 'null',
                'type' => VariableInfo::TYPE_NULL
            ),
            array(
                'label' => 'String value',
                'value' => 'Test text',
                'string' => 'string "Test text"',
                'type' => VariableInfo::TYPE_STRING
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo' => 'bar'),
                'string' => 'array '.print_r(array('foo' => 'bar'), true),
                'type' => VariableInfo::TYPE_ARRAY
            ),
            array(
                'label' => 'Integer value',
                'value' => 1,
                'string' => 'integer 1',
                'type' => VariableInfo::TYPE_INTEGER
            ),
            array(
                'label' => 'double value',
                'value' => 1.54,
                'string' => 'double 1.54',
                'type' => VariableInfo::TYPE_DOUBLE
            ),
            array(
                'label' => 'class value',
                'value' => new VariableInfoTest_DummyClass(),
                'string' => 'object '.VariableInfoTest_DummyClass::class,
                'type' => VariableInfo::TYPE_OBJECT
            ),
            array(
                'label' => 'callback value',
                'value' => array($this, 'dummyMethod'),
                'string' => 'callable '.self::class.'->'.array(self::class, 'dummyMethod')[1].'()',
                'type' => VariableInfo::TYPE_CALLABLE
            ),
            array(
                'label' => 'resource value',
                'value' => fopen($this->assetsRootFolder.'/test-file-exists.txt', 'rb'),
                'string' => 'resource #',
                'type' => VariableInfo::TYPE_RESOURCE
            )
        );
        
        foreach($tests as $def)
        {
            $var = parseVariable($def['value']);
            $var->enableType();
            
            $length = strlen($def['string']);
            
            $this->assertEquals($def['type'], $var->getType(), $def['label']);
            $this->assertEquals($def['string'], substr($var->toString(), 0, $length), $def['label']);
        }
    }
    
    public function test_serialize() : void
    {
        $tests = array(
            array(
                'label' => 'null value',
                'value' => null,
                'string' => 'null',
            ),
            array(
                'label' => 'String value',
                'value' => 'Test text',
                'string' => 'Test text'
            ),
            array(
                'label' => 'Array value',
                'value' => array('foo' => 'bar'),
                'string' => print_r(array('foo' => 'bar'), true)
            ),
            array(
                'label' => 'Integer value',
                'value' => 1,
                'string' => '1'
            ),
            array(
                'label' => 'double value',
                'value' => 1.54,
                'string' => '1.54'
            ),
            array(
                'label' => 'class value',
                'value' => new VariableInfoTest_DummyClass(),
                'string' => VariableInfoTest_DummyClass::class
            ),
            array(
                'label' => 'callback value',
                'value' => array($this, 'dummyMethod'),
                'string' => self::class.'->'.array(self::class, 'dummyMethod')[1].'()'
            ),
        );
        
        foreach($tests as $def)
        {
            $var = parseVariable($def['value']);
            
            $this->assertEquals($def['string'], $var->toString(), $def['label']);

            $serialized = $var->serialize();
            
            $restored = VariableInfo::fromSerialized($serialized);
            
            $this->assertEquals($def['string'], $restored->toString(), $def['label']);
        }
    }

    public function dummyMethod() : void
    {

    }

    public static function dummyStaticMethod() : void
    {
        
    }

    private string $assetsRootFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetsRootFolder = __DIR__.'/../assets';
    }
}

class VariableInfoTest_DummyClass
{
    
}

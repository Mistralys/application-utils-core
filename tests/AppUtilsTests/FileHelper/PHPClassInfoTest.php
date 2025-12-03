<?php

declare(strict_types=1);

namespace AppUtilsTests\FileHelper;

use AppUtils\FileHelper_PHPClassInfo;
use AppUtilsTestClasses\TestStubClass;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

final class PHPClassInfoTest extends TestCase
{
    // region: _Tests

    /**
     * @var array<int,array{label:string,file:string,classes:array<string,array<string,mixed>>}>
     */
    protected array $tests = array(
        array(
            'label' => 'Interfaces',
            'file' => 'interface',
            'classes' => array(
                'ExampleTestInterface' => array(
                    'name' => 'ExampleTestInterface',
                    'extends' => '',
                    'implements' => array(),
                    'declaration' => 'interface ExampleTestInterface',
                    'isClass' => false,
                    'isTrait' => false,
                    'isInterface' => true
                ),
                'ExampleExtendsInterface' => array(
                    'name' => 'ExampleExtendsInterface',
                    'extends' => 'ExampleTestInterface',
                    'implements' => array(),
                    'declaration' => 'interface ExampleExtendsInterface extends ExampleTestInterface',
                    'isClass' => false,
                    'isTrait' => false,
                    'isInterface' => true
                )
            ),
        ),
        array(
            'label' => 'A single class',
            'file' => 'single-class',
            'classes' => array(
                'SingleClass' => array(
                    'name' => 'SingleClass',
                    'extends' => '',
                    'implements' => array(),
                    'declaration' => 'class SingleClass',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'A single class, extends',
            'file' => 'single-class-extended',
            'classes' => array(
                'SingleClassExtended' => array(
                    'name' => 'SingleClassExtended',
                    'extends' => 'TestStubClass',
                    'implements' => array(),
                    'declaration' => 'class SingleClassExtended extends TestStubClass',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'A single class, implements',
            'file' => 'single-class-implements',
            'classes' => array(
                'SingleClassImplements' => array(
                    'name' => 'SingleClassImplements',
                    'extends' => '',
                    'implements' => array('TestStubInterface1'),
                    'declaration' => 'class SingleClassImplements implements TestStubInterface1',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'A single class, namespaced',
            'file' => 'single-class-namespaced',
            'classes' => array(
                'SingleClassNamespaced\SingleClass' => array(
                    'name' => 'SingleClass',
                    'extends' => '',
                    'implements' => array(),
                    'declaration' => 'class SingleClass',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'A single class, extends and implements',
            'file' => 'single-class-multiple',
            'classes' => array(
                'SingleClassMultiple' => array(
                    'name' => 'SingleClassMultiple',
                    'extends' => 'TestStubClass',
                    'implements' => array('TestStubInterface1', 'TestStubInterface2', 'TestStubInterface3'),
                    'declaration' => 'class SingleClassMultiple extends TestStubClass implements TestStubInterface1, TestStubInterface2, TestStubInterface3',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'A single class, multi with free spacing',
            'file' => 'single-class-multiple-freespacing',
            'classes' => array(
                'SingleClassMultipleFreespacing' => array(
                    'name' => 'SingleClassMultipleFreespacing',
                    'extends' => 'TestStubClass',
                    'implements' => array('TestStubInterface1', 'TestStubInterface2', 'TestStubInterface3'),
                    'declaration' => 'class SingleClassMultipleFreespacing extends TestStubClass implements TestStubInterface1, TestStubInterface2, TestStubInterface3',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'A trait',
            'file' => 'trait',
            'classes' => array(
                'SupahTrait' => array(
                    'name' => 'SupahTrait',
                    'extends' => '',
                    'implements' => array(),
                    'declaration' => 'trait SupahTrait',
                    'isClass' => false,
                    'isTrait' => true,
                    'isInterface' => false
                )
            ),
        ),
        array(
            'label' => 'Multiple classes',
            'file' => 'multi-class',
            'classes' => array(
                'MultiClassOne' => array(
                    'name' => 'MultiClassOne',
                    'extends' => '',
                    'implements' => array(),
                    'declaration' => 'class MultiClassOne',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                ),
                'MultiClassTwo' => array(
                    'name' => 'MultiClassTwo',
                    'extends' => 'TestStubClass',
                    'implements' => array(),
                    'declaration' => 'class MultiClassTwo extends TestStubClass',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                ),
                'MultiClassThree' => array(
                    'name' => 'MultiClassThree',
                    'extends' => 'MultiClassOne',
                    'implements' => array('TestStubInterface1', 'TestStubInterface2'),
                    'declaration' => 'class MultiClassThree extends MultiClassOne implements TestStubInterface1, TestStubInterface2',
                    'isClass' => true,
                    'isTrait' => false,
                    'isInterface' => false
                )
            ),
        )
    );

    public function test_fileNotExists() : void
    {
        $this->expectException(FileHelper_Exception::class);
        
        FileHelper::findPHPClasses('/path/to/unknown/file.php');
    }
    
    public function test_getInfo() : void
    {
        foreach($this->tests as $test)
        {
            $this->getInfo_checkTest($test);
        }
    }

    public function test_noClassesInFile() : void
    {
        $info = $this->getInfo('no-classes');

        $this->assertEmpty($info->getClasses());
    }

    public function test_classWrappedInComments() : void
    {
        $info = $this->getInfo('comment-class');

        $this->assertEmpty($info->getClasses());
    }

    // endregion

    // region: Support methods

    protected string $assetsFolder;

    protected function setUp() : void
    {
        if(isset($this->assetsFolder)) {
            return;
        }

        $folder = realpath(__DIR__.'/../../assets/FileHelper/PHPClassInfo');

        if($folder === false) {
            throw new InvalidArgumentException(
                'The file helper assets folder could not be found.'
            );
        }

        $this->assetsFolder = $folder;
    }

    /**
     * @param string $fileName
     * @return FileHelper_PHPClassInfo
     * @throws FileHelper_Exception
     */
    private function getInfo(string $fileName) : FileHelper_PHPClassInfo
    {
        return FileHelper::findPHPClasses($this->assetsFolder.'/'.$fileName.'.php');
    }

    /**
     * @param array{label:string,file:string,classes:array<string,array<string,mixed>>} $test
     * @return void
     * @throws FileHelper_Exception
     */
    private function getInfo_checkTest(array $test) : void
    {
        $info = $this->getInfo($test['file']);

        $names = $info->getClassNames();

        $testNames = array_keys($test['classes']);

        sort($names);
        sort($testNames);

        $this->assertEquals(
            $testNames,
            $names,
            $test['label'] . ': The class names should match.'
        );

        $classes = $info->getClasses();

        $this->assertCount(
            count($test['classes']),
            $classes,
            $test['label'] . ': The amount of classes should match.'
        );

        foreach($classes as $class)
        {
            $name = $class->getNameNS();

            $this->assertTrue(isset($test['classes'][$name]), 'The class name ['.$name.'] should exist in the array.');

            $def = $test['classes'][$name];

            $this->assertEquals($def['name'], $class->getName(), $test['label']);
            $this->assertEquals($def['extends'], $class->getExtends(), $test['label']);
            $this->assertEquals($def['implements'], $class->getImplements(), $test['label']);
            $this->assertEquals($def['declaration'], $class->getDeclaration(), $test['label']);

            if(isset($def['isTrait'])) {
                $this->assertSame($def['isTrait'], $class->isTrait());
            }

            if(isset($def['isClass'])) {
                $this->assertSame($def['isClass'], $class->isClass());
            }

            if(isset($def['isInterface'])) {
                $this->assertSame($def['isInterface'], $class->isInterface());
            }
        }
    }

    // endregion
}

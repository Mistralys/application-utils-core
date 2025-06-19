<?php

declare(strict_types=1);

namespace AppUtilsTests\ClassHelper;

use AppUtils\ClassHelper\Repository\ClassRepository;
use AppUtils\ClassHelper\Repository\ClassRepositoryException;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\FileHelper\FolderInfo;
use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderImplementsInterface;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderInterface;

final class ClassRepositoryTests extends BaseTestCase
{
    // region: _Tests

    /**
     * @covers ClassRepositoryManager::findClassesInFolder
     */
    public function test_getClassesByFolder() : void
    {
        $this->step1_analyzeFolder();
        $this->step2_useCache();
    }

    /**
     * @covers ClassRepositoryManager::registerClassLoader
     */
    public function test_registerClassLoader() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $manager->registerClassLoader('customID', function(ClassRepositoryManager $manager) : ClassRepository {
            return $manager->findClassesInFolder(
                $this->classesFolder,
                false,
                FinderInterface::class);
        });

        $this->assertTrue($manager->idExists('customID'));

        $repository = $manager->getByID('customID');

        $this->assertSame(
            self::EXPECTED_CLASSES,
            $repository->getClasses()
        );
    }

    /**
     * @covers ClassRepositoryManager::unregisterClassLoader
     */
    public function test_unregisterClassLoader() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $manager->registerClassLoader('customID', function(ClassRepositoryManager $manager) : ClassRepository {
            return $manager->findClassesInFolder(
                $this->classesFolder,
                false,
                FinderInterface::class);
        });

        $this->assertTrue($manager->idExists('customID'));
        $this->assertTrue($manager->hasClassLoader('customID'));

        $manager->unregisterClassLoader('customID');

        $this->assertFalse($manager->idExists('customID'));
        $this->assertFalse($manager->hasClassLoader('customID'));
    }

    /**
     * @covers ClassRepositoryManager::requireByID
     */
    public function test_requireByID() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $id = $manager->findClassesInFolder(
            $this->classesFolder,
            false,
            FinderInterface::class
        )
            ->getID();

        $repository = $manager->requireByID($id);

        $this->assertSame($id, $repository->getID());
    }

    /**
     * @covers ClassRepositoryManager::requireByID
     */
    public function test_requireByIDThrowsExceptionIfNotExists() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $this->expectExceptionCode(ClassRepositoryException::ERROR_REPOSITORY_NOT_FOUND);

        $manager->requireByID('nonexistentID');
    }

    /**
     * @covers ClassRepositoryManager::clearID
     */
    public function test_clearID() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $id = $manager->findClassesInFolder(
            $this->classesFolder,
            false,
            FinderInterface::class
        )
            ->getID();

        $manager->clearID($id);

        $this->assertFalse($manager->idExists($id));
    }

    // endregion

    // region: Support methods

    private FolderInfo $classesFolder;
    private FolderInfo $cacheFolder;
    private const EXPECTED_CLASSES = array(
        FinderImplementsInterface::class,
        FinderInterface::class
    );

    protected function setUp(): void
    {
        parent::setUp();

        $this->classesFolder = FolderInfo::factory(__DIR__.'/../../AppUtilsTestClasses/ClassHelper/ClassFinder');
        $this->cacheFolder = FolderInfo::factory(__DIR__.'/../../assets/ClassHelper/ClassRepository');

        ClassRepositoryManager::create($this->cacheFolder)->clearCache();
    }

    private function step1_analyzeFolder() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $this->assertFalse($manager->getCacheFile()->exists());

        $this->assertSame(
            self::EXPECTED_CLASSES,
            $manager->findClassesInFolder(
                $this->classesFolder,
                false,
                FinderInterface::class
            )
                ->getClasses()
        );

        $manager->writeCache();
        $this->assertTrue($manager->getCacheFile()->exists());
    }

    private function step2_useCache() : void
    {
        $manager = ClassRepositoryManager::create($this->cacheFolder);

        $this->assertTrue($manager->getCacheFile()->exists());

        $this->assertSame(
            self::EXPECTED_CLASSES,
            $manager->findClassesInFolder(
                $this->classesFolder,
                false,
                FinderInterface::class
            )
                ->getClasses()
        );
    }

    // endregion
}

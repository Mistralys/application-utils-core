<?php
/**
 * @package AppUtils
 * @subpackage ClassHelper
 */

declare(strict_types=1);

namespace AppUtils\ClassHelper\Repository;

/**
 * Represents a collection of classes as loaded using
 * the class repository manager, {@see ClassRepositoryManager}.
 *
 * @package AppUtils
 * @subpackage ClassHelper
 */
class ClassRepository
{
    private string $id;
    private array $classes;

    /**
     * @param string $id
     * @param class-string[] $classes
     */
    public function __construct(string $id, array $classes)
    {
        $this->id = $id;
        $this->classes = $classes;
    }

    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @return class-string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}

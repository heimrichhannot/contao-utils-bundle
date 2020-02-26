<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Module;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use Model\Collection;

class ModuleUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Get the class name of a given module.
     *
     * @param mixed $module Module as module type string, module model object or module object
     *
     * @return bool
     */
    public function getClassByModule($module): ?string
    {
        if ($module instanceof Module || $module instanceof ModuleModel) {
            return Module::findClass($module->type);
        }

        if (\is_string($module)) {
            return Module::findClass($module);
        }

        return null;
    }

    /**
     * Check whether a module is a sub module of another.
     *
     * @param mixed $module1    First module as class string, module type string, module model object or module object
     * @param mixed $module2    Second module as class string, module type string, module model object or module object
     * @param bool  $trueIfSame Return true if $module1 and $module2 are the same
     */
    public function isSubModuleOf($module1, $module2, $trueIfSame = false): bool
    {
        $module1 = $this->getModuleClass($module1);

        if (empty($module1)) {
            return false;
        }

        $module2 = $this->getModuleClass($module2);

        if (empty($module2)) {
            return false;
        }

        return $trueIfSame && $module1 === $module2 || is_subclass_of($module1, $module2);
    }

    /**
     * Get the full qualified class name for a given module.
     *
     * @param ModuleModel|Module|string $module a module object, a module model object, a full qualified model class name or model type
     *
     * @return string
     */
    public function getModuleClass($module)
    {
        if ((\is_string($module) && !class_exists($module)) || $module instanceof ModuleModel) {
            $module = $this->getClassByModule($module);
        }

        if (\is_object($module)) {
            $module = \get_class($module);
        }

        if (!$module || !class_exists($module)) {
            return '';
        }

        return $module;
    }

    public function getModulesByType(string $type, array $options = []): Collection
    {
        $modelOptions = $options['modelOptions'] ?? [
            'order' => 'tl_module.name ASC',
        ];

        $includeSubModules = $options['includeSubModules'] ?? false;

        $modules = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_module', ['tl_module.type=?'], [$type], $modelOptions);

        if (null === $modules) {
            return new Collection([], 'tl_module');
        }

        $result = $modules->getModels();

        if ($includeSubModules) {
            $modules = System::getContainer()->get('huh.utils.model')->findAllModelInstances('tl_module', $modelOptions);

            while ($modules->next()) {
                if ($this->isSubModuleOf($modules->current(), Module::findClass($type))) {
                    $result[] = $modules->current();
                }
            }
        }

        return new Collection($result, 'tl_module');
    }
}

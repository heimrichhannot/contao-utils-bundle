<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Module;

use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use Model\Collection;

class ModuleUtil
{
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
     * @param mixed $module1 First module as class string, module type string, module model object or module object
     * @param mixed $module2 Second module as class string, module type string, module model object or module object
     *
     * @return bool
     */
    public function isSubModuleOf($module1, $module2): bool
    {
        if (!\is_string($module1) || false === strpos($module1, '\\')) {
            $module1 = $this->getClassByModule($module1);
        }

        if (!$module1 || !class_exists($module1)) {
            return false;
        }

        if (\is_string($module2) || false === strpos($module2, '\\')) {
            $module2 = $this->getClassByModule($module2);
        }

        if (!$module2 || !class_exists($module2)) {
            return false;
        }

        return is_subclass_of($module1, $module2);
    }

    public function getModulesByType(string $type, array $options = []): Collection
    {
        $modelOptions = $options['modelOptions'] ?? [
            'order' => 'tl_module.name ASC',
        ];

        $includeSubModules = $options['includeSubModules'] ?? false;

        $modules = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_module', ['tl_module.type=?'], [$type], $modelOptions);

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

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ClassUtil
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ClassUtil constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getParentClasses(string $class, array $parents = [])
    {
        $strParent = get_parent_class($class);

        if ($strParent) {
            $parents[] = $strParent;

            $parents = $this->getParentClasses($strParent, $parents);
        }

        return $parents;
    }

    /**
     * Filter class constants by given prefixes and return the extracted constants.
     *
     * @param string $class            the class that should be searched for constants in
     * @param array  $prefixes         an array of prefixes that should be used to filter the class constants
     * @param bool   $returnValueAsKey boolean Return the extracted array keys from its value, if true
     *
     * @throws \ReflectionException
     *
     * @return array the extracted constants as array
     */
    public function getConstantsByPrefixes(string $class, array $prefixes = [], bool $returnValueAsKey = true)
    {
        $arrExtract = [];

        if (!class_exists($class)) {
            return $arrExtract;
        }

        $objReflection = new \ReflectionClass($class);
        $arrConstants = $objReflection->getConstants();

        if (!\is_array($arrConstants)) {
            return $arrExtract;
        }

        $arrExtract = $this->container->get('huh.utils.array')->filterByPrefixes($arrConstants, $prefixes);

        return $returnValueAsKey ? array_combine($arrExtract, $arrExtract) : $arrExtract;
    }

    /**
     * Returns all classes in the given namespace.
     *
     * @return array
     */
    public function getClassesInNamespace(string $namespace)
    {
        $arrOptions = [];

        foreach (get_declared_classes() as $strName) {
            if ($this->container->get('huh.utils.string')->startsWith($strName, $namespace)) {
                $arrOptions[$strName] = $strName;
            }
        }

        asort($arrOptions);

        return $arrOptions;
    }

    /**
     * Returns all children of a given class.
     *
     * @param string $strNamespace
     *
     * @return array
     */
    public function getChildClasses(string $qualifiedClassName)
    {
        $arrOptions = [];

        foreach (get_declared_classes() as $strName) {
            if (\in_array($qualifiedClassName, $this->getParentClasses($strName))) {
                $arrOptions[$strName] = $strName;
            }
        }

        asort($arrOptions);

        return $arrOptions;
    }

    /**
     * Serialize a class object to JSON by iterating over all public getters (get(), is(), ...).
     *
     * @param       $object
     * @param array $data
     * @param array $options
     *
     * @throws \ReflectionException if the class or method does not exist
     */
    public function jsonSerialize($object, $data = [], $options = []): array
    {
        $class = \get_class($object);

        $rc = new \ReflectionClass($object);

        // get values of properties
        if (isset($options['includeProperties']) && $options['includeProperties']) {
            foreach ($rc->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();

                $property = $rc->getProperty($propertyName);

                if (isset($options['ignorePropertyVisibility']) && $options['ignorePropertyVisibility']) {
                    $property->setAccessible(true);
                }

                $data[$propertyName] = $property->getValue($object);

                if (\is_object($data[$propertyName])) {
                    if (!($data[$propertyName] instanceof \JsonSerializable)) {
                        unset($data[$propertyName]);

                        continue;
                    }

                    $data[$propertyName] = $this->jsonSerialize($data[$propertyName]);
                }
            }
        }

        if (isset($options['ignoreMethods']) && $options['ignoreMethods']) {
            return $data;
        }

        // get values of methods
        if (isset($options['ignoreMethodVisibility']) && $options['ignoreMethodVisibility']) {
            $methods = $rc->getMethods();
        } else {
            $methods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
        }

        // add all public getter Methods
        foreach ($methods as $method) {
            // get()
            if (false !== ('get' === substr($method->name, 0, \strlen('get')))) {
                $start = 3; // highest priority
            } // is()
            elseif (false !== ('is' === substr($method->name, 0, \strlen('is')))) {
                $name = substr($method->name, 2, \strlen($method->name));
                $start = !$rc->hasMethod('has'.ucfirst($name)) && !$rc->hasMethod('get'.ucfirst($name)) ? 2 : 0;
            } elseif (false !== ('has' === substr($method->name, 0, \strlen('has')))) {
                $name = substr($method->name, 3, \strlen($method->name));
                $start = !$rc->hasMethod('is'.ucfirst($name)) && !$rc->hasMethod('get'.ucfirst($name)) ? 3 : 0;
            } else {
                continue;
            }

            // skip methods with parameters
            $rm = new \ReflectionMethod($class, $method->name);

            if ($rm->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            if (isset($options['skippedMethods']) && \is_array($options['skippedMethods']) && \in_array($method->name, $options['skippedMethods'])) {
                continue;
            }

            $property = lcfirst(substr($method->name, $start));

            $data[$property] = $object->{$method->name}();

            if (\is_object($data[$property])) {
                if (!($data[$property] instanceof \JsonSerializable)) {
                    unset($data[$property]);

                    continue;
                }
                $data[$property] = $this->jsonSerialize($data[$property]);
            }
        }

        return $data;
    }

    /**
     * Calls an object's method which is inaccessible.
     *
     * @param $entity
     *
     * @throws \ReflectionException
     *
     * @return mixed|null
     */
    public function callInaccessibleMethod($entity, string $method)
    {
        $rc = new \ReflectionClass($entity);

        if ($rc->hasMethod($method)) {
            $method = $rc->getMethod($method);
            $method->setAccessible(true);

            return $method->invoke($entity);
        }

        return null;
    }
}

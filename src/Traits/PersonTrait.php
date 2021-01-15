<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Traits;

trait PersonTrait
{
    public function getActiveGroups(string $personName): array
    {
        if (!$personName) {
            return [];
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($this->scopeMatcher->isFrontendRequest($request)) {
            $person = $this->frontendUserProvider->loadUserByUsername($personName);
        } elseif ($this->scopeMatcher->isBackendRequest($request)) {
            $person = $this->backendUserProvider->loadUserByUsername($personName);
        } else {
            return [];
        }

        if (empty($person->groups)) {
            return [];
        }

        return $person->groups;
    }

    public function hasActiveGroup(string $personName, int $personGroupId): bool
    {
        $activeGroups = $this->getActiveGroups($personName);

        if (\in_array($personGroupId, $activeGroups)) {
            foreach ($activeGroups as $activeGroup) {
                if ($personGroupId === (int) ($activeGroup)) {
                    return true;
                }
            }
        }

        return false;
    }
}

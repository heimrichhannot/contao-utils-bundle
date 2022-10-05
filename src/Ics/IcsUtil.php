<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Ics;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IcsUtil
{
    /**
     * @var ContaoFramework
     */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->framework = $container->get('contao.framework');
        $this->container = $container;
    }

    /**
     * @throws \Exception
     */
    public function generateIcs(array $data, array $options = []): ?string
    {
        if (!class_exists('Eluceo\iCal\Component\Event')) {
            throw new \Exception('The composer package eluceo/ical could not be found and is required by this service. Please install it via "composer require eluceo/ical ^0.16".');
        }

        // prepare data
        $adjustTime = $options['adjustTime'] ?? false;

        $addTime = $data['addTime'] ?? false;
        $end = null;

        if (empty($data['endDate'] ?? null)) {
            $data['endDate'] = $data['startDate'];
        }

        if ($adjustTime && $addTime) {
            $data['startTime'] = strtotime(date('Y-m-d', $data['startDate']).' '.date('H:i:s', $data['startTime']));
            $data['endTime'] = strtotime(date('Y-m-d', $data['endDate']).' '.date('H:i:s', $data['endTime']));
        }

        if ($addTime && isset($data['startTime']) && $data['startTime']) {
            $start = (new \DateTime())->setTimestamp($data['startTime']);
        } else {
            $start = (new \DateTime())->setTimestamp($data['startDate']);
            $start->setTime(0, 0, 0);
        }

        if (isset($data['endDate']) && $data['endDate']) {
            // workaround for allday events
            $end = (new \DateTime())->setTimestamp($data['endDate']);
            $end->setTime(0, 0, 0);
        }

        if ($addTime && isset($data['endTime']) && $data['endTime']) {
            $end = (new \DateTime())->setTimestamp($data['endTime']);
        }

        // create an event
        $event = new \Eluceo\iCal\Component\Event();

        $event->setNoTime(!$addTime);
        $event->setDtStart($start);

        if (null !== $end) {
            $event->setDtEnd($end);
        }

        if (isset($data['title']) && $data['title']) {
            $event->setSummary(strip_tags($data['title']));
        }

        if (isset($data['description']) && $data['description']) {
            // preserve linebreaks
            $description = preg_replace('@<br\s*/?>@i', "\n", $data['description']);
            $description = preg_replace('@</p>\s*<p>@i', "\n\n", $description);
            $description = str_replace(['<p>', '</p>'], '', $description);

            $event->setDescription(strip_tags($description));
        }

        // compose location out of various fields
        $locationData = [];

        if (isset($data['location']) && $data['location']) {
            $locationData['location'] = $data['location'];
        }

        if (isset($data['street']) && $data['street']) {
            $locationData['street'] = $data['street'];
        }

        if (isset($data['postal']) && $data['postal']) {
            $locationData['postal'] = $data['postal'];
        }

        if (isset($data['city']) && $data['city']) {
            $locationData['city'] = $data['city'];
        }

        if (isset($data['country']) && $data['country']) {
            $locationData['country'] = $data['country'];
        }

        if (!empty($locationData)) {
            $result = [];

            if (isset($locationData['location'])) {
                $result[] = $locationData['location'];
            }

            if (isset($locationData['street'])) {
                $result[] = $locationData['street'];
            }

            if (isset($locationData['postal']) && isset($locationData['city'])) {
                $result[] = $locationData['postal'].' '.$locationData['city'];
            } elseif (isset($locationData['city'])) {
                $result[] = $locationData['city'];
            }

            if (isset($locationData['country'])) {
                $result[] = $locationData['country'];
            }

            $event->setLocation(implode(', ', $result));
        }

        if (isset($data['url']) && $data['url']) {
            $event->setUrl(strip_tags($data['url']));
        }

        // create a calendar
        $calendar = new \Eluceo\iCal\Component\Calendar(Environment::get('url'));

        $calendar->setTimezone(\Config::get('timeZone'));
        $calendar->addComponent($event);

        return $calendar->render();
    }
}

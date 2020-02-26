<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Location;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class LocationUtil
{
    const GOOGLE_MAPS_GEOCODE_URL = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false';
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Computes the coordinates from a given address. Supported array keys are:.
     *
     * - street
     * - postal
     * - city
     * - country
     *
     * @return array|bool
     */
    public function computeCoordinatesByArray(array $data)
    {
        $criteria = [
            'street',
            'postal',
            'city',
            'country',
        ];

        $sortedData = [];

        // keep the right order
        foreach ($criteria as $name) {
            if (isset($data[$name])) {
                $sortedData[] = $data[$name];
            }
        }

        return $this->computeCoordinatesByString(implode(' ', $sortedData));
    }

    /**
     * Computes the coordinates from a given address. Supported array keys are:.
     *
     * - street
     * - postal
     * - city
     * - country
     *
     * @return array|bool
     */
    public function computeCoordinatesByString(string $address, string $apiKey = '')
    {
        $curlUtil = System::getContainer()->get('huh.utils.request.curl');

        $url = sprintf(static::GOOGLE_MAPS_GEOCODE_URL, urlencode($address));

        if ($apiKey) {
            $url = System::getContainer()->get('huh.utils.url')->addQueryString('key='.$apiKey, $url);
        } elseif (Config::get('utilsGoogleApiKey')) {
            $url = System::getContainer()->get('huh.utils.url')->addQueryString('key='.Config::get('utilsGoogleApiKey'), $url);
        }

        $result = $curlUtil->request($url);

        if (!$result) {
            return false;
        }

        $response = json_decode($result);

        if ($response->error_message) {
            $session = System::getContainer()->get('contao.session.contao_backend');

            $session->set('utils.location.error', $response->error_message);

            return false;
        }

        return ['lat' => $response->results[0]->geometry->location->lat, 'lng' => $response->results[0]->geometry->location->lng];
    }

    public function computeCoordinatesInSaveCallback($value, \Contao\DataContainer $dc)
    {
        $data = [
            'street' => $dc->activeRecord->street,
            'postal' => $dc->activeRecord->postal,
            'city' => $dc->activeRecord->city,
        ];

        if ($value || empty(array_filter($data))) {
            return $value;
        }

        $result = System::getContainer()->get('huh.utils.location')->computeCoordinatesByArray([
            'street' => $dc->activeRecord->street,
            'postal' => $dc->activeRecord->postal,
            'city' => $dc->activeRecord->city,
        ]);

        if (false === $result || !\is_array($result)) {
            $session = System::getContainer()->get('contao.session.contao_backend');

            if ($error = $session->get('utils.location.error')) {
                throw new \Exception($session->get('utils.location.error'));
            }

            return '';
        }

        return $result['lat'].','.$result['lng'];
    }

    /**
     * @param $kmlData string The KML data
     *
     * @return array
     */
    public function getCoordinatesFromKml(string $kmlData, array $options = [])
    {
        $kmlData = System::getContainer()->get('huh.utils.string')->convertXmlToArray($kmlData);

        if (!\is_array($kmlData) || !isset($kmlData['Document']['Placemark']['LineString']['coordinates'])) {
            return null;
        }

        $coordinates = preg_replace('/\s+/', ' ', $kmlData['Document']['Placemark']['LineString']['coordinates']);
        $coordinates = explode(' ', $coordinates);

        foreach ($coordinates as $coordinate) {
            if (!$coordinate) {
                continue;
            }

            $exploded = explode(',', $coordinate);

            if (\count($exploded) < 2) {
                continue;
            }

            $location = [
                'lat' => $exploded[1],
                'lng' => $exploded[0],
            ];

            if ((!isset($options['skipAltitude']) || !$options['skipAltitude']) && \count($exploded) > 2) {
                $location['alt'] = $exploded[2];
            }

            $locations[] = $location;
        }

        return $locations;
    }

    /**
     * @param $gpxData string The KML data
     *
     * @return array
     */
    public function getCoordinatesFromGpx(string $gpxData, array $options = [])
    {
        $locations = [];
        $gpxData = System::getContainer()->get('huh.utils.string')->convertXmlToArray($gpxData);

        if (!\is_array($gpxData) || !isset($gpxData['trk'])) {
            return null;
        }

        if (isset($gpxData['trk']['name'])) {
            foreach ($gpxData['trk']['trkseg']['trkpt'] as $trkPt) {
                $location = [
                    'lat' => $trkPt['@attributes']['lat'],
                    'lng' => $trkPt['@attributes']['lon'],
                ];

                if (isset($trkPt['ele']) && $trkPt['ele']) {
                    $location['alt'] = $trkPt['ele'];
                }

                $locations[] = $location;
            }
        } else {
            foreach ($gpxData['trk'] as $trk) {
                if (!\is_array($trk['trkseg']['trkpt'])) {
                    continue;
                }

                foreach ($trk['trkseg']['trkpt'] as $trkPt) {
                    $location = [
                        'lat' => $trkPt['@attributes']['lat'],
                        'lng' => $trkPt['@attributes']['lon'],
                    ];

                    if (isset($trkPt['ele']) && $trkPt['ele']) {
                        $location['alt'] = $trkPt['ele'];
                    }

                    $locations[] = $location;
                }
            }
        }

        return $locations;
    }
}

<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Driver;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Model;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DC_Table_Utils extends DC_Table
{
    /**
     * Initialize the object.
     *
     * @param string $strTable
     * @param array  $arrModule
     */
    public function __construct($strTable, $arrModule = [])
    {
        DataContainer::__construct();

        /** @var SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');
        $request = System::getContainer()->get('huh.request');

        // Check the request token (see #4007)
        if ($request->hasGet('act')) {
            if (!$request->hasGet('rt') || !\RequestToken::validate($request->getGet('rt'))) {
                $objSession->set('INVALID_TOKEN_URL', \Environment::get('request'));
                $this->redirect('contao/confirm.php');
            }
        }

        Controller::loadDataContainer($strTable);

        $this->intId = $request->getGet('id');

        // Clear the clipboard
        if ($request->hasGet('clipboard')) {
            $objSession->set('CLIPBOARD', []);
            $this->redirect($this->getReferer());
        }

        // Check whether the table is defined
        if ('' == $strTable || !isset($GLOBALS['TL_DCA'][$strTable])) {
            System::getContainer()->get('monolog.logger.contao')->log('Could not load the data container configuration for "'.$strTable.'"', __METHOD__, TL_ERROR);
            trigger_error('Could not load the data container configuration', E_USER_ERROR);
        }

        // Set IDs and redirect
        if ('tl_select' == $request->getPost('FORM_SUBMIT')) {
            $ids = $request->getPost('IDS');

            if (empty($ids) || !\is_array($ids)) {
                $this->reload();
            }

            $session = $objSession->all();
            $session['CURRENT']['IDS'] = $ids;
            $objSession->replace($session);

            if ($request->hasPost('edit')) {
                $this->redirect(str_replace('act=select', 'act=editAll', \Environment::get('request')));
            } elseif ($request->hasPost('delete')) {
                $this->redirect(str_replace('act=select', 'act=deleteAll', \Environment::get('request')));
            } elseif ($request->hasPost('override')) {
                $this->redirect(str_replace('act=select', 'act=overrideAll', \Environment::get('request')));
            } elseif ($request->hasPost('cut') || $request->hasPost('copy')) {
                $arrClipboard = $objSession->get('CLIPBOARD');

                $arrClipboard[$strTable] = [
                    'id' => $ids,
                    'mode' => ($request->hasPost('cut') ? 'cutAll' : 'copyAll'),
                ];

                $objSession->set('CLIPBOARD', $arrClipboard);

                // Support copyAll in the list view (see #7499)
                if ($request->hasPost('copy') && $GLOBALS['TL_DCA'][$strTable]['list']['sorting']['mode'] < 4) {
                    $this->redirect(str_replace('act=select', 'act=copyAll', \Environment::get('request')));
                }

                $this->redirect($this->getReferer());
            }
        }

        $this->strTable = $strTable;
        $this->ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] ?? null;
        $this->ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'] ?? null;
        $this->treeView = isset($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) && \in_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'], [5, 6]);
        $this->root = null;
        $this->arrModule = $arrModule;

        // FIX: Don't call onload_callbacks for performance reasons

        // Get the IDs of all root records (tree view)
        if ($this->treeView) {
            $table = (6 == $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->ptable : $this->strTable;

            // Unless there are any root records specified, use all records with parent ID 0
            if (!isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) || (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && false === $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'])) {
                $objIds = $this->Database->prepare('SELECT id FROM '.$table.' WHERE pid=?'.($this->Database->fieldExists('sorting', $table) ? ' ORDER BY sorting' : ''))->execute(0);

                if ($objIds->numRows > 0) {
                    $this->root = $objIds->fetchEach('id');
                }
            } // Get root records from global configuration file
            elseif (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && \is_array($GLOBALS['TL_DCA'][$table]['list']['sorting']['root'])) {
                $this->root = $this->eliminateNestedPages($GLOBALS['TL_DCA'][$table]['list']['sorting']['root'], $table, $this->Database->fieldExists('sorting', $table));
            }
        } // Get the IDs of all root records (list view or parent view)
        elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']) && \is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root'])) {
            $this->root = array_unique($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']);
        }

        $route = $request->attributes->get('_route');

        // Store the current referer
        if (!empty($this->ctable) && !$request->getGet('act') && !$request->getGet('key') && !$request->getGet('token') && 'contao_backend' == $route
            && !\Environment::get('isAjaxRequest')) {
            $strKey = $request->getGet('popup') ? 'popupReferer' : 'referer';
            $strRefererId = $request->attributes->get('_contao_referer_id');

            $session = $objSession->get($strKey);
            $session[$strRefererId][$this->strTable] = substr(\Environment::get('requestUri'), \strlen(\Environment::get('path')) + 1);
            $objSession->set($strKey, $session);
        }
    }

    /**
     * Create a DataContainer instance from a given Model.
     *
     * @return static
     */
    public static function createFromModel(Model $model)
    {
        $table = $model->getTable();

        $dc = new static($table);

        $dc->strTable = $model->getTable();
        $dc->activeRecord = $model;
        $dc->intId = $model->id;

        return $dc;
    }

    /**
     * Create a DataContainer instance from given model data.
     *
     * @param Model  $model
     * @param string $field
     *
     * @return static
     */
    public static function createFromModelData(array $modelData, string $table, string $field = null)
    {
        $dc = new static($table);

        $dc->strTable = $table;
        $dc->activeRecord = null;

        if (isset($modelData['id']) && $modelData['id'] > 0) {
            $dc->activeRecord = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($table, $modelData['id']);
            $dc->intId = $modelData['id'];
        }

        if ($field) {
            $dc->strField = $field;
        }

        return $dc;
    }
}

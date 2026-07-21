<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @author Chris Kulbacki (http://chriskulbacki.com)
 * @license Commercial. See the LICENSE file for details.
 */

interface DatabaseInterface
{
    public function getColumns($table, $addPrefix = true);
    public function getTables();
    public function hasTable($table);
}
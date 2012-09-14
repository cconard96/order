<?php
/*
 * @version $Id$
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderBillState extends CommonDropdown {

   const NOTPAID = 0;
   const PAID    = 1;

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['bill'][2];
   }

   function pre_deleteItem() {
      global $LANG;
      if ($this->getID() <= self::CANCELED ) {
         Session::addMessageAfterRedirect($LANG['plugin_order']['status'][15].": ".$this->fields['name'], 
                                 false, ERROR);
         return false;
      } else {
         return true;
      }
   }

   function canCreate() {
      return plugin_order_haveRight('bill', 'w');
   }

   function canView() {
      return plugin_order_haveRight('bill', 'r');
   } 
   
   static function getStates() {
      global $LANG;
      return array(self::NOTPAID => $LANG['plugin_order']['bill'][7], 
                   self::PAID    => $LANG['plugin_order']['bill'][6]);
   }
   
   static function getState($states_id) {
      $states = self::getStates();
      if (isset($states[$states_id])) {
         return $states[$states_id];
      } else {
         return '';
      }
   }
   
   static function install(Migration $migration) {
      global $DB, $LANG;
      
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billstates` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                 `comment` text COLLATE utf8_unicode_ci,
                 PRIMARY KEY (`id`),
                 KEY `name` (`name`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
      $state = new self();
      foreach (array(self::PAID     => $LANG['plugin_order']['status'][16],
                     self::NOTPAID  => $LANG['plugin_order']['status'][17]) 
               as $id => $label) {
         if (!countElementsInTable($table, "`id`='$id'")) {
            $state->add(array('id' => $id, 'name' => Toolbox::addslashes_deep($label)));
         }
      }
   }
   
   static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
   }
}
?>
<?php
/* ----------------------------------------------------------------------
   $Id: orders_status.php 437 2013-06-22 15:33:30Z r23 $

   MyOOS [Shopsystem]
   http://www.oos-shop.de/
   
   
   Copyright (c) 2003 - 2013 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: orders_status.php,v 1.5 2002/01/29 14:43:00 hpdl 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */


define('HEADING_TITLE', 'Orders Status');

define('TABLE_HEADING_ORDERS_STATUS', 'Orders Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_ORDERS_STATUS_NAME', 'Orders Status:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new orders status with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this order status?');
define('TEXT_INFO_HEADING_NEW_ORDERS_STATUS', 'New Orders Status');
define('TEXT_INFO_HEADING_EDIT_ORDERS_STATUS', 'Edit Orders Status');
define('TEXT_INFO_HEADING_DELETE_ORDERS_STATUS', 'Delete Orders Status');

define('ERROR_REMOVE_DEFAULT_ORDER_STATUS', 'Error: The default order status can not be removed. Please set another order status as default, and try again.');
define('ERROR_STATUS_USED_IN_ORDERS', 'Error: This order status is currently used in orders.');
define('ERROR_STATUS_USED_IN_HISTORY', 'Error: This order status is currently used in the order status history.');
?>

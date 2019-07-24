<?php
/* ----------------------------------------------------------------------

   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2019 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: categories.php,v 1.146 2003/07/11 14:40:27 hpdl
         categories.php,v 1.138 2002/11/18 21:38:22 dgw_
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

define('OOS_VALID_MOD', 'yes');
require 'includes/main.php';

require 'includes/functions/function_categories.php';
require 'includes/classes/class_currencies.php';

require_once MYOOS_INCLUDE_PATH . '/includes/lib/htmlpurifier/library/HTMLPurifier.auto.php';

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$cPath = (isset($_GET['cPath']) ? oos_prepare_input($_GET['cPath']) : $current_category_id);
$pID = (isset($_GET['pID']) ? intval($_GET['pID']) : 0);

if (!empty($action)) {
	switch ($action) {
		case 'insert_product':
		case 'update_product':
			$products_id = oos_db_prepare_input($_GET['pID']);		

			if (isset($_FILES['files'])) {
				foreach ($_FILES['files']['name'] as $key => $name) {
					if (empty($name)) {
						// purge empty slots
						unset($_FILES['files']['name'][$key]);
						unset($_FILES['files']['type'][$key]);
						unset($_FILES['files']['tmp_name'][$key]);
						unset($_FILES['files']['error'][$key]);
						unset($_FILES['files']['size'][$key]);
					}
				}
			}
			$nImageCounter = (!isset($_POST['image_counter']) || !is_numeric($_POST['image_counter'])) ? 0 : intval($_POST['image_counter']);


			if ((date('Y-m-d') < $products_date_available) && ($sProductsStatus == 3)) {
				$sProductsStatus = 2;
			}
			
			$products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

			$sql_data_array = array('products_quantity' => $sProductsQuantity,
                                 'products_reorder_level' => oos_db_prepare_input($_POST['products_reorder_level']),
                                 'products_model' => oos_db_prepare_input($_POST['products_model']),
								'products_replacement_product_id' => $sProductsReplacementProductID,
                                 'products_ean' => oos_db_prepare_input($_POST['products_ean']),
                                 'products_price' => oos_db_prepare_input($_POST['products_price']),
                                  'products_base_price' => $products_base_price,
                                  'products_product_quantity' => $products_product_quantity,
                                  'products_base_quantity' => $products_base_quantity,
                                  'products_base_unit' => $products_base_unit,
                                  'products_date_available' => $products_date_available,
                                  'products_weight' => oos_db_prepare_input($_POST['products_weight']),
                                  'products_status' => $sProductsStatus,
								  'products_setting' => oos_db_prepare_input($_POST['products_setting']),

                                  );


			if ($action == 'insert_product') {
				$insert_sql_data = array('products_date_added' => 'now()');

				$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

				oos_db_perform($oostable['products'], $sql_data_array);
				$products_id = $dbconn->Insert_ID();

				$products_to_categoriestable = $oostable['products_to_categories'];
				$dbconn->Execute("INSERT INTO $products_to_categoriestable (products_id, categories_id) VALUES ('" . intval($products_id) . "', '" . intval($current_category_id) . "')");

			} elseif ($action == 'update_product') {
				$update_sql_data = array('products_last_modified' => 'now()');

				$sql_data_array = array_merge($sql_data_array, $update_sql_data);

				oos_db_perform($oostable['products'], $sql_data_array, 'UPDATE', 'products_id = \'' . intval($products_id) . '\'');

			}

			$aLanguages = oos_get_languages();
			$nLanguages = count($aLanguages);

			for ($i = 0, $n = $nLanguages; $i < $n; $i++) {
				$language_id = $aLanguages[$i]['id'];
							
				$sql_data_array = array('products_name' => oos_db_prepare_input($_POST['products_name'][$language_id]),
										'products_title' => oos_db_prepare_input($_POST['products_title'][$language_id]),
										'products_description' => oos_db_prepare_input($_POST['products_description_' . $aLanguages[$i]['id']]),
										'products_short_description' => oos_db_prepare_input($_POST['products_short_description_' . $aLanguages[$i]['id']]),
										'products_essential_characteristics' => oos_db_prepare_input($_POST['products_essential_characteristics_' . $aLanguages[$i]['id']]),
										'products_description_meta' => oos_db_prepare_input($_POST['products_description_meta_' . $aLanguages[$i]['id']]),
										'products_url' => oos_db_prepare_input($_POST['products_url'][$language_id]));

				if ($action == 'insert_product') {
					$insert_sql_data = array('products_id' => $products_id,
                                      'products_languages_id' => $language_id);

					$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

					oos_db_perform($oostable['products_description'], $sql_data_array);
				} elseif ($action == 'update_product') {
					oos_db_perform($oostable['products_description'], $sql_data_array, 'UPDATE', 'products_id = \'' . intval($products_id) . '\' AND products_languages_id = \'' . intval($language_id) . '\'');
				}
			}


			if ( ($_POST['remove_image'] == 'yes') && (isset($_POST['products_previous_image'])) ) {
				$products_previous_image = oos_db_prepare_input($_POST['products_previous_image']);
				
				$productsstable = $oostable['products'];
				$dbconn->Execute("UPDATE $productsstable
                                 SET products_image = NULL
                                 WHERE products_id = '" . intval($products_id) . "'");				
				
				oos_remove_product_image($products_previous_image);				
			}

			for ($i = 1, $n = $nImageCounter+1; $i < $n; $i++) {
				if ( ($_POST['remove_products_image'][$i] == 'yes') && (isset($_POST['products_previous_large_image'][$i])) ) {
					$products_previous_large_image = oos_db_prepare_input($_POST['products_previous_large_image'][$i]);

					$dbconn->Execute("DELETE FROM " . $oostable['products_images'] . " WHERE image_name = '" . oos_db_input($products_previous_large_image) . "'");		
				
					oos_remove_category_image($products_previous_large_image);				
				}
			}


			$options = array(
				'image_versions' => array(				
                // The empty image version key defines options for the original image.
                // Keep in mind: these image manipulations are inherited by all other image versions from this point onwards. 
                // Also note that the property 'no_cache' is not inherited, since it's not a manipulation.
					'' => array(
						// Automatically rotate images based on EXIF meta data:
						'auto_orient' => TRUE
					),
					'large' => array(
						// 'auto_orient' => TRUE,
						// 'crop' => TRUE,
						// 'jpeg_quality' => 82,
						// 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
						// 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
						'max_width' => 1024, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
						'max_height' => 1024, // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
					),
					'medium_large' => array(
						// 'auto_orient' => TRUE,
						// 'crop' => TRUE,
						// 'jpeg_quality' => 82,
						// 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
						// 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
						'max_width' => 600, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
						'max_height' => 600 // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
					),					
					'medium' => array(
						// 'auto_orient' => TRUE,
						// 'crop' => TRUE,
						// 'jpeg_quality' => 82,
						// 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
						// 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
						'max_width' => 420, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
						'max_height' => 455 // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
					),				
					'small' => array(
						// 'auto_orient' => TRUE,
						// 'crop' => TRUE,
						// 'jpeg_quality' => 82,
						// 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
						// 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
						'max_width' => 150, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
						'max_height' => 150 // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
					),
					'min' => array(
						// 'auto_orient' => TRUE,
						// 'crop' => TRUE,
						// 'jpeg_quality' => 82,
						// 'no_cache' => TRUE, (there's a caching option, but this remembers thumbnail sizes from a previous action!)
						// 'strip' => TRUE, (this strips EXIF tags, such as geolocation)
						'max_width' => 45, // either specify width, or set to 0. Then width is automatically adjusted - keeping aspect ratio to a specified max_height.
						'max_height' => 45 // either specify height, or set to 0. Then height is automatically adjusted - keeping aspect ratio to a specified max_width.
					),				
				),
			);
			
			$oProductImage = new upload('products_image', $options);
			
			$dir_fs_catalog_images = OOS_ABSOLUTE_PATH . OOS_IMAGES . 'product/';
			$oProductImage->set_destination($dir_fs_catalog_images);

			if ($oProductImage->parse() && oos_is_not_null($oProductImage->filename)) {		
				$productstable = $oostable['products'];
				$dbconn->Execute("UPDATE $productstable
                            SET products_image = '" . oos_db_input($oProductImage->filename) . "'
                            WHERE products_id = '" . intval($products_id) . "'");				
			}	
			
			if (isset($_FILES['files'])) {
				$oImage = new upload('files', $options);
		
				$dir_fs_catalog_images = OOS_ABSOLUTE_PATH . OOS_IMAGES . 'product/';
				$oImage->set_destination($dir_fs_catalog_images);			
				$oImage->parse();

				if (oos_is_not_null($oImage->response)) {
					
					$sort_order = 0 + $nImageCounter;				
					foreach ($oImage->response as $index => $value) {
						$sort_order++;						
						$sql_data_array = array('products_id' => intval($products_id),
												'image_name' => oos_db_prepare_input($value),
												'sort_order' => intval($sort_order));
						oos_db_perform($oostable['products_images'], $sql_data_array);		
					}
				}
			}

			oos_redirect_admin(oos_href_link_admin($aContents['categories'], 'cPath=' . $cPath . '&pID=' . $products_id));
		break;

	}
}

// check if the catalog image directory exists
if (is_dir(OOS_ABSOLUTE_PATH . OOS_IMAGES)) {
    if (!is_writeable(OOS_ABSOLUTE_PATH . OOS_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
} else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}

require 'includes/header.php';
?>
<!-- body //-->
<div class="wrapper">
	<!-- Header //-->
	<header class="topnavbar-wrapper">
		<!-- Top Navbar //-->
		<?php require 'includes/menue.php'; ?>
	</header>
	<!-- END Header //-->
	<aside class="aside">
		<!-- Sidebar //-->
		<div class="aside-inner">
			<?php require 'includes/blocks.php'; ?>
		</div>
		<!-- END Sidebar (left) //-->
	</aside>

	<!-- Main section //-->
	<section>
		<!-- Page content //-->
		<div class="content-wrapper">

				<div class="row">
					<div class="col-lg-12">
<?php
if ($action == 'edit_3d') {
		
    $parameters = array('products_id' => '',
						'products_name' => '',
                        'products_larger_images' => array(),
                       'products_price' => 0.0,
                       'products_base_price' => 1.0,
                       'products_product_quantity' => 1.0,
                       'products_base_quantity' => 1.0,				   
                       'products_weight' => '',
                       'products_date_added' => '',
                       'products_last_modified' => '',
                       'products_date_available' => '',
					   'products_setting' => DEFAULT_SETTING_ID,
                       'products_status' => DEFAULT_PRODUTS_STATUS_ID,
                       'products_tax_class_id' => DEFAULT_TAX_CLASS_ID,
					   'products_units_id' => DEFAULT_PRODUCTS_UNITS_ID,
                       'manufacturers_id' => '');

    $pInfo = new objectInfo($parameters);	  
	  
	if (isset($_GET['pID']) && empty($_POST)) {	  
		$productstable = $oostable['products'];


		$products_descriptiontable = $oostable['products_description'];
		$product_result = $dbconn->Execute("SELECT p.products_id, pd.products_name, 
                                            FROM $productstable p,
                                                 $products_descriptiontable pd
                                           WHERE p.products_id = '" . intval($pID) . "' AND
                                                 p.products_id = pd.products_id AND
                                                 pd.products_languages_id = '" . intval($_SESSION['language_id']) . "'");									 
		$product = $product_result->fields;

		$pInfo = new objectInfo($product);

		$productstable = $oostable['products_models'];
		$products_imagestable = $oostable['products_images'];
		$products_images_result =  $dbconn->Execute("SELECT products_id, image_name, sort_order FROM $products_imagestable WHERE products_id = '" . intval($product['products_id']) . "' ORDER BY sort_order");
			
		while ($product_images = $products_images_result->fields) {
			$pInfo->products_larger_images[] = array('products_id' => $product_images['products_id'],
													'image' => $product_images['image_name'],
													'sort_order' => $product_images['sort_order']);
			// Move that ADOdb pointer!
			$products_images_result->MoveNext();
		}
    } 
	
    $manufacturers_array = array();
    $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
    $manufacturerstable = $oostable['manufacturers'];
    $manufacturers_result = $dbconn->Execute("SELECT manufacturers_id, manufacturers_name FROM $manufacturerstable ORDER BY manufacturers_name");
    while ($manufacturers = $manufacturers_result->fields) {
		$manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                     'text' => $manufacturers['manufacturers_name']);

		// Move that ADOdb pointer!
		$manufacturers_result->MoveNext();
    }

    $tax_class_array = array();
    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_classtable = $oostable['tax_class'];
    $tax_class_result = $dbconn->Execute("SELECT tax_class_id, tax_class_title FROM $tax_classtable ORDER BY tax_class_title");
    while ($tax_class = $tax_class_result->fields) {
		$tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);

		// Move that ADOdb pointer!
		$tax_class_result->MoveNext();
    }


    $products_units_array = array();
    $products_units_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $products_unitstable = $oostable['products_units'];
    $products_units_result = $dbconn->Execute("SELECT products_units_id, products_unit_name FROM $products_unitstable WHERE languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY products_unit_name");
    while ($products_units = $products_units_result->fields) {
		$products_units_array[] = array('id' => $products_units['products_units_id'],
                                      'text' => $products_units['products_unit_name']);

		// Move that ADOdb pointer!
		$products_units_result->MoveNext();
    }



    $products_status_array = array();
    $products_status_array = array(array('id' => '0', 'text' => TEXT_PRODUCT_NOT_AVAILABLE));
    $products_statustable = $oostable['products_status'];
    $products_status_result = $dbconn->Execute("SELECT products_status_id, products_status_name FROM $products_statustable WHERE products_status_languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY products_status_id");
    while ($products_status = $products_status_result->fields) {
		$products_status_array[] = array('id' => $products_status['products_status_id'],
                                       'text' => $products_status['products_status_name']);

		// Move that ADOdb pointer!
		$products_status_result->MoveNext();
    }

    $aLanguages = oos_get_languages();
	$nLanguages = count($aLanguages);
	
    $form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';

	$aSetting = array();
	$settingstable = $oostable['setting'];
	$setting_result = $dbconn->Execute("SELECT setting_id, setting_name FROM $settingstable WHERE setting_languages_id = '" . intval($_SESSION['language_id']) . "' ORDER BY setting_id");
	while ($setting = $setting_result->fields) {
		$aSetting[] = array('id' => $setting['setting_id'],
							'text' => $setting['setting_name']);
		// Move that ADOdb pointer!
		$setting_result->MoveNext();
	}
	

	if (isset($_GET['origin'])) {
		$sOrigin = oos_db_prepare_input($_GET['origin']);
        $pos_params = strpos($sOrigin, '?', 0);
        if ($pos_params != false) {
          $back_url = substr($sOrigin, 0, $pos_params);
          $back_url_params = substr($sOrigin, $pos_params + 1);
        } else {
          $back_url = $sOrigin;
          $back_url_params = '';
        }
	} else {
        $back_url = $aContents['categories'];
        $back_url_params = 'cPath=' . $cPath;
        if (oos_is_not_null($pInfo->products_id)) {
			$back_url_params .= '&pID=' . $pInfo->products_id;
        }		
	}	

	
?>
<script type="text/javascript" src="js/ckeditor/ckeditor.js"></script>

<script type="text/javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
		if ($tax_class_array[$i]['id'] > 0) {
			echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . oos_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
		}
	}
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}

function updateWithTax() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;
  var grossListValue = document.forms["new_product"].products_price_list.value;
  var grossDiscount1Value = document.forms["new_product"].products_discount1.value;
  var grossDiscount2Value = document.forms["new_product"].products_discount2.value;
  var grossDiscount3Value = document.forms["new_product"].products_discount3.value;
  var grossDiscount4Value = document.forms["new_product"].products_discount4.value;
  
  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
	grossListValue = grossListValue * ((taxRate / 100) + 1);
	grossDiscount1Value = grossDiscount1Value * ((taxRate / 100) + 1)
	grossDiscount2Value = grossDiscount2Value * ((taxRate / 100) + 1)
	grossDiscount3Value = grossDiscount3Value * ((taxRate / 100) + 1)
	grossDiscount4Value = grossDiscount4Value * ((taxRate / 100) + 1)	
  }

  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
  document.forms["new_product"].products_price_list_gross.value = doRound(grossListValue, 4);
  document.forms["new_product"].products_discount_gross1.value = doRound(grossDiscount1Value, 4); 
  document.forms["new_product"].products_discount_gross2.value = doRound(grossDiscount2Value, 4);
  document.forms["new_product"].products_discount_gross3.value = doRound(grossDiscount3Value, 4); 
  document.forms["new_product"].products_discount_gross4.value = doRound(grossDiscount4Value, 4); 
}

function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;
  var netListValue = document.forms["new_product"].products_price_list_gross.value;
  var netDiscount1Value = document.forms["new_product"].products_discount_gross1.value;
  var netDiscount2Value = document.forms["new_product"].products_discount_gross2.value;
  var netDiscount3Value = document.forms["new_product"].products_discount_gross3.value; 
  var netDiscount4Value = document.forms["new_product"].products_discount_gross4.value; 
  
  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
	netListValue = netListValue / ((taxRate / 100) + 1);
	netDiscount1Value = netDiscount1Value / ((taxRate / 100) + 1);
	netDiscount2Value = netDiscount2Value / ((taxRate / 100) + 1);
	netDiscount3Value = netDiscount3Value / ((taxRate / 100) + 1);
	netDiscount4Value = netDiscount4Value / ((taxRate / 100) + 1);	
  }

  document.forms["new_product"].products_price.value = doRound(netValue, 4);
  document.forms["new_product"].products_price_list.value = doRound(netListValue, 4);
  document.forms["new_product"].products_discount1.value = doRound(netDiscount1Value, 4);
  document.forms["new_product"].products_discount2.value = doRound(netDiscount2Value, 4);
  document.forms["new_product"].products_discount3.value = doRound(netDiscount3Value, 4);
  document.forms["new_product"].products_discount4.value = doRound(netDiscount4Value, 4);  
}

function calcBasePriceFactor() {
  var pqty = document.forms["new_product"].products_product_quantity.value;
  var bqty = document.forms["new_product"].products_base_quantity.value;

  if ((pqty != 0) || (bqty != 0)) {
     document.forms["new_product"].products_base_price.value = doRound(bqty / pqty, 6);
  } else {
     document.forms["new_product"].products_base_price.value = 1.000000;
  }

}
//--></script>
	<!-- Breadcrumbs //-->
	<div class="content-heading">
		<div class="col-lg-12">
			<h2><?php echo sprintf(TEXT_NEW_PRODUCT, oos_output_generated_category_path($current_category_id)); ?></h2>
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<?php echo '<a href="' . oos_href_link_admin($aContents['default']) . '">' . HEADER_TITLE_TOP . '</a>'; ?>
				</li>
				<li class="breadcrumb-item">
					<?php echo '<a href="' . oos_href_link_admin(oos_selected_file('catalog.php'), 'selected_box=catalog') . '">' . BOX_HEADING_CATALOG . '</a>'; ?>
				</li>
				<li class="breadcrumb-item active">
					<strong><?php echo sprintf(TEXT_NEW_PRODUCT, oos_output_generated_category_path($current_category_id)); ?></strong>
				</li>
			</ol>
		</div>
	</div>
	<!-- END Breadcrumbs //-->

	<?php echo oos_draw_form('id', 'new_product', $aContents['products'], 'cPath=' . $cPath . (!empty($pID) ? '&pID=' . intval($pID) : '') . '&action=' . $form_action, 'post', FALSE, 'enctype="multipart/form-data"'); ?>
		<?php echo oos_draw_hidden_field('products_date_added', (($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d')));
			  echo oos_hide_session_id();
		?>
               <div role="tabpanel">
                  <ul class="nav nav-tabs nav-justified">
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" href="#edit" aria-controls="edit" role="tab" data-toggle="tab"><?php echo TEXT_PRODUCTS; ?></a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#data" aria-controls="data" role="tab" data-toggle="tab"><?php echo TEXT_PRODUCTS_DATA; ?></a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#picture" aria-controls="picture" role="tab" data-toggle="tab"><?php echo TEXT_PRODUCTS_IMAGE; ?></a>
                     </li>
                  </ul>
                  <div class="tab-content">
					<div class="text-right mt-3 mb-3">
						<?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($back_url, $back_url_params) . '" role="button"><strong><i class="fa fa-chevron-left"></i> ' . IMAGE_BACK . '</strong></a>'; ?>
						<?php echo oos_submit_button(IMAGE_SAVE); ?>
						<?php echo oos_reset_button(BUTTON_RESET); ?>			   
					</div>			  
                     <div class="tab-pane active" id="edit" role="tabpanel">

                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo ENTRY_STATUS; ?></label>
                              <div class="col-lg-10"><?php echo oos_draw_pull_down_menu('products_setting', $aSetting, $pInfo->products_setting); ?></div>
                           </div>
                        </fieldset>


<?php
    for ($i = 0, $n = $nLanguages; $i < $n; $i++) {
?>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php if ($i == 0) echo TEXT_PRODUCTS_URL . '<br /><small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></label>
							  <?php if ($nLanguages > 1) echo '<div class="col-lg-1">' .  oos_flag_icon($aLanguages[$i]) . '</div>'; ?>
                              <div class="col-lg-9"><?php echo oos_draw_input_field('products_url[' . $aLanguages[$i]['id'] . ']', (($products_url[$aLanguages[$i]['iso_639_2']]) ? stripslashes($products_url[$aLanguages[$i]['id']]) : oos_get_products_url($pInfo->products_id, $aLanguages[$i]['id']))); ?></div>
                           </div>
                        </fieldset>
<?php
    }
?>
                     </div>
				 
                     <div class="tab-pane" id="data" role="tabpanel">
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_PRICE; ?></label>
                              <div class="col-lg-10">
                                <?php
									$sPrice = number_format($pInfo->products_price, 4, '.', '');
									echo oos_draw_input_field('products_price', $sPrice, 'onkeyup="updateWithTax()"');
								?>								
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_PRICE_WITH_TAX; ?></label>
                              <div class="col-lg-10">
                                <?php
									$sPrice = number_format($pInfo->products_price, TAX_DECIMAL_PLACES, '.', '');
									echo oos_draw_input_field('products_price_gross', $sPrice, 'onkeyup="updateNet()"');
								?>
                              </div>
                           </div>
                        </fieldset>						
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_LIST_PRICE; ?></label>
                              <div class="col-lg-10">
                                <?php
									$sPriceList = number_format($pInfo->products_price_list, 4, '.', '');
									echo oos_draw_input_field('products_price_list', $sPriceList, 'onkeyup="updateWithTax()"');
								?>
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_LIST_PRICE; ?></label>
                              <div class="col-lg-10">
                                <?php
									$sPriceList = number_format($pInfo->products_price_list, TAX_DECIMAL_PLACES, '.', '');
									echo oos_draw_input_field('products_price_list_gross', $sPriceList, 'onkeyup="updateNet()"');
								?>
                              </div>
                           </div>
                        </fieldset>						




                       <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_MODEL; ?></label>
                              <div class="col-lg-10">
								<?php echo oos_draw_input_field('products_model', $pInfo->products_model); ?>
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_EAN; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_ean', $pInfo->products_ean); ?>
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_QUANTITY; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_quantity', $pInfo->products_quantity); ?>
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_PRODUCT_MINIMUM_ORDER; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_quantity_order_min', ($pInfo->products_quantity_order_min==0 ? 1 : $pInfo->products_quantity_order_min)); ?>
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_PRODUCT_PACKAGING_UNIT; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_quantity_order_units', ($pInfo->products_quantity_order_units==0 ? 1 : $pInfo->products_quantity_order_units)); ?>
                              </div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_PRODUCT_MAXIMUM_ORDER; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_quantity_order_max', ($pInfo->products_quantity_order_max==0 ? 30 : $pInfo->products_quantity_order_max)); ?>
                              </div>
                           </div>
                        </fieldset>
<?php
	if (STOCK_CHECK == 'true') {
?>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_REORDER_LEVEL; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_reorder_level', $pInfo->products_reorder_level); ?>
                              </div>
                           </div>
                        </fieldset>
<?php
	}
?>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_WEIGHT; ?></label>
                              <div class="col-lg-10"><?php echo oos_draw_input_field('products_weight', $pInfo->products_weight); ?></div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_PRODUCTS_STATUS; ?></label>
                              <div class="col-lg-10"><?php echo oos_draw_pull_down_menu('products_status', $products_status_array, $pInfo->products_status); ?></div>
                           </div>
                        </fieldset>
                        <fieldset>
                           <div class="form-group row">
                              <label class="col-lg-2 col-form-label"><?php echo TEXT_REPLACEMENT_PRODUCT; ?></label>
                              <div class="col-lg-10">
                                 <?php echo oos_draw_input_field('products_replacement_product_id', $pInfo->products_replacement_product_id); ?>
                              </div>
                           </div>
                        </fieldset>



                     </div>
                     <div class="tab-pane" id="picture" role="tabpanel">
		<script type="text/javascript">
		// <!-- <![CDATA[
		window.totalinputs = 3;
		function addUploadBoxes(placeholderid, copyfromid, num) {
			for (i = 0; i < num; i++) {
				jQuery('#' + copyfromid).clone().insertBefore('#' + placeholderid);
				window.totalinputs++;
				if (window.totalinputs >= 30) {
					jQuery('#addUploadBoxes').toggle('slow');
					return;
				}
			}
		}
		function resetBoxes() {
			window.totalinputs = 3
			$('#uploadboxes').html('<div id="place" style="display: none;"></div>');
			addUploadBoxes('place', 'filetemplate', 3);
		}
		// ]]> -->
	</script>


		<div class="row mb-3">
			<div class="col-3">
				<strong><?php echo TEXT_INFO_PREVIEW; ?></strong>
			</div>
			<div class="col-9">
				<strong><?php echo TEXT_INFO_DETAILS; ?></strong>
			</div>
		</div>
		<div class="row mb-3 pb-3 bb">
			<div class="col-6 col-md-3">

<?php	
	if (oos_is_not_null($pInfo->products_image)) {
		echo '<div class="text-center"><div class="d-block" style="width: 200px; height: 150px;">';
		echo oos_info_image('product/small/' . $pInfo->products_image, $pInfo->products_name);
	    echo '</div></div>';
		
		
		echo oos_draw_hidden_field('products_previous_image', $pInfo->products_image);
		echo '<br>';
		echo oos_draw_checkbox_field('remove_image', 'yes') . ' ' . TEXT_IMAGE_REMOVE;	
	} else {		
?>


<div class="fileinput fileinput-new" data-provides="fileinput">
  <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;"></div>
  <div>
    <span class="btn btn-warning btn-file"><span class="fileinput-new"><em class="fa fa-plus-circle fa-fw"></em><?php echo BUTTON_SELECT_IMAGE; ?></span><span class="fileinput-exists"><?php echo BUTTON_CHANGE; ?></span>
	
	<input type="file" size="40" name="products_image"></span>
    <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput"><em class="fa fa-times-circle fa-fw"></em><?php echo BUTTON_DELETE; ?></a>
  </div>
</div>
<?php
	}
?>
			</div>
			<div class="col-9">
				<strong><?php echo TEXT_INFO_DETAILS; ?></strong>
			</div>	
		</div>
		
<?php
	if (is_array($pInfo->products_larger_images) || is_object($pInfo->products_larger_images)) {
		$nCounter = 0;
		
		foreach ($pInfo->products_larger_images as $image) {
			$nCounter++;
?>

		<div class="row mb-3 pb-3 bb">
			<div class="col-6 col-md-3">

<?php	
		echo '<div class="text-center"><div class="d-block" style="width: 200px; height: 150px;">';
		echo oos_info_image('product/small/' .  $image['image'], $pInfo->products_name);
	    echo '</div></div>';
		
		echo $image['image'];
		
		echo oos_draw_hidden_field('products_previous_large_image['. $nCounter . ']', $image['image']);
		echo '<br>';
		echo oos_draw_checkbox_field('remove_products_image['. $nCounter . ']', 'yes') . ' ' . TEXT_IMAGE_REMOVE;		
?>
			</div>
			<div class="col-9">
				<strong><?php echo TEXT_INFO_DETAILS; ?></strong>
			</div>	
		</div>
<?php
		}
	}
	echo oos_draw_hidden_field('image_counter', $nCounter);
?>		
		
		
		<div class="row mb-3 pb-3 bb">
			<div class="col-6 col-md-3">

<div class="fileinput fileinput-new" data-provides="fileinput">
  <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;"></div>
  <div>
    <span class="btn btn-warning btn-file"><span class="fileinput-new"><em class="fa fa-plus-circle fa-fw"></em><?php echo BUTTON_SELECT_IMAGE; ?></span><span class="fileinput-exists"><?php echo BUTTON_CHANGE; ?></span>
	
	<input type="file" size="40" name="files[]"></span>
    <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput"><em class="fa fa-times-circle fa-fw"></em><?php echo BUTTON_DELETE; ?></a>
  </div>
</div>

			</div>
			<div class="col-9">
				<strong><?php echo TEXT_INFO_DETAILS; ?></strong>
			</div>	
		</div>

		<div class="row mb-3 pb-3 bb">
			<div class="col-6 col-md-3">

<div class="fileinput fileinput-new" data-provides="fileinput">
  <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;"></div>
  <div>
    <span class="btn btn-warning btn-file"><span class="fileinput-new"><em class="fa fa-plus-circle fa-fw"></em><?php echo BUTTON_SELECT_IMAGE; ?></span><span class="fileinput-exists"><?php echo BUTTON_CHANGE; ?></span>
	
	<input type="file" size="40" name="files[]"></span>
    <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput"><em class="fa fa-times-circle fa-fw"></em><?php echo BUTTON_DELETE; ?></a>
  </div>
</div>

			</div>
			<div class="col-9">
				<strong><?php echo TEXT_INFO_DETAILS; ?></strong>
			</div>	
		</div>



	<div id="uploadboxes">
		<div id="place" style="display: none;"></div>
		<!-- New boxes get inserted before this -->
	</div>

	<div style="display:none">
		<!-- This is the template that others are copied from -->
		<div id="filetemplate" >
                        <div class="row mb-3">
                           <div class="col-3">		
<div class="fileinput fileinput-new" data-provides="fileinput">
  <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;"></div>
  <div>
    <span class="btn btn-warning btn-file"><span class="fileinput-new"><em class="fa fa-plus-circle fa-fw"></em><?php echo BUTTON_SELECT_IMAGE; ?></span><span class="fileinput-exists"><?php echo BUTTON_CHANGE; ?></span>
	
	<input type="file" size="40" name="files[]"></span>
    <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput"><em class="fa fa-times-circle fa-fw"></em><?php echo BUTTON_DELETE; ?></a>
  </div>
</div>		

                           </div>
                           <div class="col-9">
                              <strong><?php echo TEXT_INFO_DETAILS; ?></strong>
                           </div>	
						</div>
		</div>
	</div>
	<p id="addUploadBoxes"><a href="javascript:addUploadBoxes('place','filetemplate',3)" title="<?php echo TEXT_NOT_RELOAD; ?>">+ <?php echo TEXT_ADD_MORE_UPLOAD; ?></a></p>

                     </div>
                  </div>
               </div>
            <div class="text-right mt-3">
				<?php echo '<a class="btn btn-sm btn-warning mb-20" href="' . oos_href_link_admin($back_url, $back_url_params) . '" role="button"><strong><i class="fa fa-chevron-left"></i> ' . IMAGE_BACK . '</strong></a>'; ?>
				<?php echo oos_submit_button(IMAGE_SAVE); ?>
				<?php echo oos_reset_button(BUTTON_RESET); ?>			   
			</div>				
			
            </form>
<!-- body_text_eof //-->
<?php
  } elseif ($action == 'new_product_preview') {
      $product_result = $dbconn->Execute("SELECT pd.products_name, pd.products_description, pd.products_description_meta, pd.products_url, p.products_id, p.products_quantity, p.products_reorder_level, p.products_model, p.products_replacement_product_id, p.products_ean, p.products_image, p.products_price, p.products_base_price, p.products_base_unit, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available, p.products_status, p.products_tax_class_id, p.products_units_id, p.manufacturers_id, p.products_price_list, p.products_quantity_order_min, p.products_quantity_order_units, p.products_discount1, p.products_discount2, p.products_discount3, p.products_discount4, p.products_discount1_qty, p.products_discount2_qty, p.products_discount3_qty, p.products_discount4_qty, p.products_sort_order FROM " . $oostable['products'] . " p, " . $oostable['products_description'] . " pd WHERE p.products_id = '" . intval($pID) . "' and p.products_id = pd.products_id and pd.products_languages_id = '" . intval($_SESSION['language_id']) . "'");
      $product = $product_result->fields;

      $pInfo = new objectInfo($product);
      $products_image_name = $pInfo->products_image;

    $aLanguages = oos_get_languages();
	$nLanguages = count($aLanguages);
	
    for ($i = 0, $n = $nLanguages; $i < $n; $i++) {
        $pInfo->products_name = oos_get_products_name($pInfo->products_id, $aLanguages[$i]['id']);
        $pInfo->products_description = oos_get_products_description($pInfo->products_id, $aLanguages[$i]['id']);
        $pInfo->products_description_meta = oos_get_products_description_meta($pInfo->products_id, $aLanguages[$i]['id']);
        $pInfo->products_url = oos_get_products_url($pInfo->products_id, $aLanguages[$i]['id']);
      }
?>
<!-- body_text //-->
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main">
<?php
       echo (($products_image_name) ? oos_image(OOS_SHOP_IMAGES . $products_image_name, $pInfo->products_name, '', '80', 'align="right" hspace="5" vspace="5"') : '');
      echo $pInfo->products_description;
?></td>
      </tr>
<?php
      if ($pInfo->products_url) {
?>
      <tr>
        <td></td>
      </tr>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></td>
      </tr>
<?php
      }
?>
<?php
      if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, oos_date_long($pInfo->products_date_available)); ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, oos_date_long($pInfo->products_date_added)); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td></td>
      </tr>
<?php

      if (isset($_GET['origin'])) {
        $pos_params = strpos($_GET['origin'], '?', 0);
        if ($pos_params != false) {
          $back_url = substr($_GET['origin'], 0, $pos_params);
          $back_url_params = substr($_GET['origin'], $pos_params + 1);
        } else {
          $back_url = $_GET['origin'];
          $back_url_params = '';
        }
      } else {
        $back_url = $aContents['categories'];
        $back_url_params = 'cPath=' . $cPath;
        if (oos_is_not_null($pInfo->products_id)) {
          $back_url_params .= '&pID=' . $pInfo->products_id;
        }
      }
?>
      <tr>
        <td class="text-right"><?php echo '<a href="' . oos_href_link_admin($back_url, $back_url_params) . '">' . oos_button(IMAGE_BACK) . '</a>'; ?></td>
      </tr>

	      </table>
<!-- body_text_eof //-->
<?php
  }
?>


				</div>
			</div>

		</div>
	</section>
	<!-- Page footer //-->
	<footer>
		<span>&copy; 2019 - <a href="https://www.oos-shop.de" target="_blank" rel="noopener">MyOOS [Shopsystem]</a></span>
	</footer>
</div>

<?php
	require 'includes/bottom.php';
	require 'includes/nice_exit.php';
?>

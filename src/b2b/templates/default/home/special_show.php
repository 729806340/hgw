<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<style type="text/css">.public-nav-layout,.classtab a.curr,.head-search-bar .search-form,.public-nav-layout .category .hover .class{/*background: #a2375f;*/}.public-head-layout .logo-test{ color:#a2375f}.public-nav-layout .category .sub-class{ border-color: #a2375f;}
</style>

<?php 

	function loop_include_widgets($output) {
		foreach($output['list'] as $key => $value) {
			if(empty($value['item_usable'])) { continue;}
			$item_data = $value['item_data'];
			$item_edit_flag = false;
			if(file_exists(BASE_RESOURCE_PATH . '/pc_special/widgets/' . $value['item_type'] . '/' . $value['item_template'] . '.tpl.php')) {
				require(BASE_RESOURCE_PATH . '/pc_special/widgets/' . $value['item_type'] . '/' . $value['item_template'] . '.tpl.php');
			} else {
				require(BASE_RESOURCE_PATH . '/pc_special/widgets/' . $value['item_type'] . '/default.tpl.php');
			}
		}

	}
	

	// 判断是否存在自定义模板
	if(file_exists(BASE_RESOURCE_PATH . '/pc_special/tmpl/' . $output['special_info']['special_tmpl'] . '.tpl.php')) {
		require(BASE_RESOURCE_PATH . '/pc_special/tmpl/' . $output['special_info']['special_tmpl'] . '.tpl.php');
	} else {
		require(BASE_RESOURCE_PATH . '/pc_special/tmpl/default.tpl.php');
	}
?>
	
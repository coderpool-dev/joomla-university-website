<?php
/**
 * @package     All in One Accessibility®
 * @author      Skynet Technologies USA LLC.
 * @copyright   (C) 2025 - Skynet Technologies USA LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;


	$plugin = Factory::getApplication()->get('aioa_params'); // adjust if needed

	$licensekey = '';
	$color      = $plugin['color'] ?? '#420083';
	$position   = $plugin['position'] ?? 'bottom_right';
	$icon_type  = $plugin['icon_type'] ?? 'aioa-icon-type-1';
	$icon_size  = $plugin['icon_size'] ?? 'aioa-medium-icon';

	// --------------------------------------
	// DOMAIN
	// --------------------------------------
	$domain = $_SERVER['HTTP_HOST'] ?? '';
	$domain_base64 = base64_encode($domain);
	// --------------------------------------
	// CALL add-user-domain API
	// --------------------------------------
	$apiUrl = "https://ada.skynettechnologies.us/api/widget-settings";
	$postData = ['website_url' => $domain];

	$ch = curl_init($apiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	// Temporary: disable SSL verify on WAMP (to test)
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
		echo "CURL ERROR: " . curl_error($ch);
	}
	curl_close($ch);
	$apiResponse = json_decode($response, true);
	// 0 = load EU script | 1 = load normal AIO script
	$no_required_eu = $apiResponse['Data']['no_required_eu'] ?? '1';
	// --------------------------------------
	// DEBUG LOGS
	// --------------------------------------
	$debugJs = "
	<script>
		console.log('ADA Full API Response:', " . json_encode($apiResponse) . ");
		console.log('ADA no_required_eu:', '" . $no_required_eu . "');
	</script>
	";
	// --------------------------------------
	// SCRIPT LOADING LOGIC
	// --------------------------------------

	if ($no_required_eu == '0') {

		// EU SCRIPT
		$finalWidget = "
		<script>
			setTimeout(() => {
				let aioa_script_tag = document.createElement('script');
				aioa_script_tag.src = 'https://eu.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode={$color}&token={$licensekey}&position={$position}';
				aioa_script_tag.id = 'aioa-adawidget';
				aioa_script_tag.defer = true;
				document.body.appendChild(aioa_script_tag);
			}, 3000);
		</script>
		";

	} else {

		// NORMAL AIOA SCRIPT
		$finalWidget = "
		<script id='aioa-adawidget'
			src='https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?colorcode=#420083&token=&position=bottom_right'
			async='true'>
		</script>
		";
	}

	// --------------------------------------
	// OUTPUT INTO JOOMLA DOCUMENT
	// --------------------------------------
	$document = Factory::getDocument();
	$document->addCustomTag($finalWidget);

?>



<?php
/**
 * WHMCS SDK Sample Provisioning Module
 *
 * Provisioning Modules, also referred to as Product or Server Modules, allow
 * you to create modules that allow for the provisioning and management of
 * products and services in WHMCS.
 *
 * This sample file demonstrates how a provisioning module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Provisioning Modules are stored in the /modules/servers/ directory. The
 * module name you choose must be unique, and should be all lowercase,
 * containing only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "provisioningmodule" and therefore all
 * functions begin "whmcs_plex_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _ConfigOptions
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/provisioning-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.
//require __DIR__ . '/vendor/autoload.php';




/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function whmcs_plex_MetaData()
{
    return array(
        'DisplayName' => 'Zens WHMCS Plex Provisioning Module',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => false, // Set true if module requires a server to work
    );
}

/**
 * Define product configuration options.
 *
 * The values you return here define the configuration options that are
 * presented to a user when configuring a product for use with the module. These
 * values are then made available in all module function calls with the key name
 * configoptionX - with X being the index number of the field from 1 to 24.
 *
 * You can specify up to 24 parameters, with field types:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each and their possible configuration parameters are provided in
 * this sample function.
 *
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function whmcs_plex_ConfigOptions()
{
return [
        "servertoken" => [
            "FriendlyName" => "Server Token",
            "Type" => "text", # Text Box
            "Size" => "512", # Defines the Field Width
            "Description" => "<br>Token for Server To Add Users To",
            "Default" => "",
        ],
        "serverid" => [
            "FriendlyName" => "Server ID",
            "Type" => "text", # Text Box
            "Size" => "512", # Defines the Field Width
            "Description" => "<br>The ID of the server you want to add users to",
            "Default" => "",
        ],

        "libjson" => [
            "FriendlyName" => "Library JSON",
            "Type" => "text", # Text Box
            "Size" => "512", # Defines the Field Width
            "Description" => "<br>The JSON For Library Name to ID Mapping",
            "Default" => "",
        ],
        "suspendedlibraryid" => [
            "FriendlyName" => "Suspended Lib ID",
            "Type" => "text", # Text Box
            "Size" => "512", # Defines the Field Width
            "Description" => "<br>The ID of the Library to Add A Suspended User To",
            "Default" => "",
        ],
        "plexmachineid" => [
            "FriendlyName" => "Plex Machine ID",
            "Type" => "text", # Text Box
            "Size" => "512", # Defines the Field Width
            "Description" => "<br>The Plex Machine ID of your Plex Server",
            "Default" => "",
        ],
        "defaultlibraryid" => [
            "FriendlyName" => "Default Lib IDs",
            "Type" => "text", # Text Box
            "Size" => "512", # Defines the Field Width
            "Description" => "<br>The IDs of the Library to Add A New  User To",
            "Default" => "",
        ]
    ];
}


function whmcs_plex_CreateAccount(array $params)
{
    try {
        // Call the service's change password function, using the values
        // provided by WHMCS in `$params`.
       // update_plex_user($servertoken,$serverid,$params["username"], [26,23,17,11,9,7,3,2,1]);

		$libs ='';
		$lib = array();
		$command = 'GetClientsAddons';
		$postData = array(
			'clientid' => $params["userid"],
			'serviceid' => $params["serviceid"],
		);
		$adminUsername = 'admin'; // Optional for WHMCS 7.2 and later

		$results = localAPI($command, $postData, $adminUsername);

		$libarray = json_decode($params["configoption3"], true);

		foreach ($results["addons"]["addon"] as $line) {
	
					//array_push($lib,$line["notes"]);
					$name = $line["name"];
					$libid = $libarray[$name];
					if(strlen($libs) > 0)
						$libs .= ','.$libid;
					else
						$libs = $libid;
						
					//array_push($lib,$libid); 
		
		} 
		
		if(strlen($libs) <= 1)
		{
				$libs = $params["configoption6"];
		}
		logModuleCall( 'whmcs_plex', __FUNCTION__, $libs, "Adding User " . $params["customfields"]["Plex Username "] );


       	$results =  add_plex_user($params["configoption1"], $params["configoption5"],$params["customfields"]["Plex Username "], $libs);

        logModuleCall( 'whmcs_plex', __FUNCTION__, $results, "Adding User " . $params["customfields"]["Plex Username "] );


        // ```
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'whmcs_plex',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Upgrade or downgrade an instance of a product/service.
 *
 * Called to apply any change in product assignment or parameters. It
 * is called to provision upgrade or downgrade orders, as well as being
 * able to be invoked manually by an admin user.
 *
 * This same function is called for upgrades and downgrades of both
 * products and configurable options.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function whmcs_plex_ChangePackage(array $params)
{
    try {
        // Call the service's change password function, using the values
        // provided by WHMCS in `$params`.
       // update_plex_user($servertoken,$serverid,$params["username"], [26,23,17,11,9,7,3,2,1]);

		$libs ='';
		$lib = array();
		$command = 'GetClientsAddons';
		$postData = array(
			'clientid' => $params["userid"],
			'serviceid' => $params["serviceid"],
		);
		$adminUsername = 'admin'; // Optional for WHMCS 7.2 and later

		$results = localAPI($command, $postData, $adminUsername);

		$libarray = json_decode($params["configoption3"], true);

		foreach ($results["addons"]["addon"] as $line) {
	
					//array_push($lib,$line["notes"]);
					$name = $line["name"];
					$libid = $libarray[$name];
					if(strlen($libs) > 0)
						$libs .= ','.$libid;
					else
						$libs = $libid;
						
					//array_push($lib,$libid); 
		
		} 
		if(strlen($libs) <= 1)
		{
				$libs = $params["configoption6"];
		}
		logModuleCall( 'whmcs_plex', __FUNCTION__, $libs, "Updating User " . $params["customfields"]["Plex Username "] );


       	$results =  update_plex_user($params["configoption1"],$params["configoption2"], $params["configoption5"],$params["customfields"]["Plex Username "], $libs);

        logModuleCall( 'whmcs_plex', __FUNCTION__, $results, "Updating User " . $params["customfields"]["Plex Username "] );


        // ```
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'whmcs_plex',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Suspend an instance of a product/service.
 *
 * Called when a suspension is requested. This is invoked automatically by WHMCS
 * when a product becomes overdue on payment or can be called manually by admin
 * user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function whmcs_plex_SuspendAccount(array $params)
{
    try {
    //($token, $serverid, $machineid ,$login, $libs)
    	$results = suspend_plex_user($params["configoption1"],$params["configoption2"],$params["configoption5"] ,$params["customfields"]["Plex Username "], $params["configoption4"]);
        
        logModuleCall( 'whmcs_plex', __FUNCTION__, $results, "Suspending User " . $params["customfields"]["Plex Username "] );

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'whmcs_plex',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Un-suspend instance of a product/service.
 *
 * Called when an un-suspension is requested. This is invoked
 * automatically upon payment of an overdue invoice for a product, or
 * can be called manually by admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */

function whmcs_plex_UnsuspendAccount(array $params)
{
    try {
        // Call the service's change password function, using the values
        // provided by WHMCS in `$params`.
       // update_plex_user($servertoken,$serverid,$params["username"], [26,23,17,11,9,7,3,2,1]);

		$libs ='';
		$lib = array();
		$command = 'GetClientsAddons';
		$postData = array(
			'clientid' => $params["userid"],
			'serviceid' => $params["serviceid"],
		);
		$adminUsername = 'admin'; // Optional for WHMCS 7.2 and later

		$results = localAPI($command, $postData, $adminUsername);

		$libarray = json_decode($params["configoption3"], true);

		foreach ($results["addons"]["addon"] as $line) {
	
					//array_push($lib,$line["notes"]);
					$name = $line["name"];
					$libid = $libarray[$name];
					if(strlen($libs) > 0)
						$libs .= ','.$libid;
					else
						$libs = $libid;
						
					//array_push($lib,$libid); 
		
		} 
		logModuleCall( 'whmcs_plex', __FUNCTION__, $libs, "Un-Suspending User " . $params["customfields"]["Plex Username "] );


       	$results =  update_plex_user($params["configoption1"],$params["configoption2"],$params["configoption5"],$params["customfields"]["Plex Username "], $libs);

        logModuleCall( 'whmcs_plex', __FUNCTION__, $results, "Un-Suspending User " . $params["customfields"]["Plex Username "] );


        // ```
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'whmcs_plex',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Terminate instance of a product/service.
 *
 * Called when a termination is requested. This can be invoked automatically for
 * overdue products if enabled, or requested manually by an admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function whmcs_plex_TerminateAccount(array $params)
{
    try {

       $results =  delete_plex_user($params["configoption1"], $params["customfields"]["Plex Username "] );

        logModuleCall( 'whmcs_plex', __FUNCTION__, $results, "Terminate User " . $params["customfields"]["Plex Username "] );


    
       // delete_plex_user($servertoken,$serverid,$params["username"]);

      
        //logModuleCall( 'whmcs_plex', __FUNCTION__, $results, "Terminate User " . $params["username"] );



    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'whmcs_plex',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function update_plex_user($token,$serverid, $machineid ,$login, $libs) {


		$data= "{\"machineIdentifier\":\"!MACHID!\",\"librarySectionIds\":[".$libs."], \"invitedEmail\":\"dummy@plex.us\"}";

	  	$data = str_replace("dummy@plex.us",$login,$data);
	  	$data = str_replace("!MACHID!",$machineid,$data);
	  	
	  			// Get user list
		$list_xml = trim(strtolower(@file_get_contents('https://plex.tv/api/users?X-Plex-Token='.$token)));
		if (empty($list_xml))
			return (array('ERROR_LIST'));

		// Extract Server Link ID
		$link_id = false;
		if (strpos($list_xml, $login) !== false)
		{
			$link_id = explode('username="'.$login.'"', $list_xml)[1];
			$link_id = explode('</user>', $link_id)[0];
			if (strpos($link_id, '<server') !== false)
			{
				$link_id = explode('" serverid="', $link_id)[0];
				$link_id = explode('<server id="', $link_id)[1];
			}
			else
				$link_id = false;
		}

		
		$url = 'https://plex.tv/api/v2/shared_servers/'.$link_id.'?X-Plex-Product=Plex%20Web&X-Plex-Version=4.40.1&X-Plex-Client-Identifier=kfowlv9lv8su6i8ds01fbqdm&X-Plex-Platform=Chrome&X-Plex-Platform-Version=84.0&X-Plex-Sync-Version=2&X-Plex-Features=external-media%2Cindirect-media&X-Plex-Model=hosted&X-Plex-Device=OSX&X-Plex-Device-Name=Chrome&X-Plex-Device-Screen-Resolution=1271x881%2C1792x1120&X-Plex-Token='.$token.'&X-Plex-Language=en';
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

		$headers = array();
		$headers[] = 'Connection: keep-alive';
		$headers[] = 'Accept: application/json, text/javascript,q=0.01';
		$headers[] = 'Accept-Language: en';
		$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36';
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Origin: http://app.plex.tv';
		$headers[] = 'Sec-Fetch-Site: cross-site';
		$headers[] = 'Sec-Fetch-Mode: cors';
		$headers[] = 'Sec-Fetch-Dest: empty';
		$headers[] = 'Referer: http://app.plex.tv/';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		return $result;
}


function add_plex_user($token,$machineid, $login, $libs){

        //$url = "https://plex.tv/api/v2/shared_servers?X-Plex-Token=".$token."&X-Plex-Client-Identifier=ZENPLEXWHMCS";
         $url = 'https://plex.tv/api/v2/shared_servers?X-Plex-Product=Plex%20Web&X-Plex-Version=4.40.1&X-Plex-Client-Identifier=kfowlv9lv8su6i8ds01fbqdm&X-Plex-Platform=Chrome&X-Plex-Platform-Version=84.0&X-Plex-Sync-Version=2&X-Plex-Features=external-media%2Cindirect-media&X-Plex-Model=hosted&X-Plex-Device=OSX&X-Plex-Device-Name=Chrome&X-Plex-Device-Screen-Resolution=1271x881%2C1792x1120&X-Plex-Token='.$token;

         $data = "{\"machineIdentifier\":\"!MACHID!\",\"librarySectionIds\":[".$libs."],\"settings\":{\"allowSync\":\"1\",\"allowCameraUpload\":\"0\",\"filterMovies\":\"\",\"filterTelevision\":\"\",\"filterMusic\":\"\"},\"invitedEmail\":\"dummy@plex.us\"}";

	  	$data = str_replace("dummy@plex.us",$login,$data);
	  	$data = str_replace("!MACHID!",$machineid,$data);


                logModuleCall( 'whmcs_plex',"add_plex_user", $data, "Adding User " . $login );
logModuleCall( 'whmcs_plex',"add_plex_user", $url, "Adding User " . $login );


        $ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

		$headers = array();
		$headers[] = 'Connection: keep-alive';
		$headers[] = 'Accept: application/json, text/javascript';
		$headers[] = 'Accept-Language: en';
		$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36';
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Origin: http://app.plex.tv';
		$headers[] = 'Sec-Fetch-Site: cross-site';
		$headers[] = 'Sec-Fetch-Mode: cors';
		$headers[] = 'Sec-Fetch-Dest: empty';
		$headers[] = 'Referer: http://app.plex.tv/';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		return $result;
}

function suspend_plex_user($token, $serverid, $machineid ,$login, $libs){

		$result = update_plex_user($token, $serverid, $machineid, $login, $libs);
		return $result;
}

function delete_plex_user($token, $login){

       $list_xml = trim(strtolower(@file_get_contents('https://plex.tv/api/users?X-Plex-Token='.$token)));

		$link_id = false;
		if (strpos($list_xml, $login) !== false)
		{
$split = 'username="dummy"';
$split = str_replace('dummy',$login,$split);
			$link_id = explode($split, $list_xml)[0];
$link_id = explode('<user id="', $link_id);
$link_id = $link_id[count($link_id)-1];
$link_id = explode(' title="', $link_id)[0];
$link_id = str_replace('"','',$link_id);
		
		}

	$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://plex.tv/api/v2/friends/'.$link_id.'?X-Plex-Token='.$token.'&X-Plex-Language=en&X-Plex-Client-Identifier=ZENPLEXWHMCS');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

$headers = array();
$headers[] = 'Connection: keep-alive';
$headers[] = 'Accept: text/plain, */*; q=0.01';
$headers[] = 'Accept-Language: en';
$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.125 Safari/537.36';
$headers[] = 'Origin: http://app.plex.tv';
$headers[] = 'Sec-Fetch-Site: cross-site';
$headers[] = 'Sec-Fetch-Mode: cors';
$headers[] = 'Sec-Fetch-Dest: empty';
$headers[] = 'Referer: http://app.plex.tv/';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);
	
	return $result;

}








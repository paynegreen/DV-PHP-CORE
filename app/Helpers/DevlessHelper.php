<?php

namespace App\Helpers;

use App\Http\Controllers\ServiceController;
use DB;
use Hash;
use Session;
use Devless\SDK\SDK;
use App\User as user;
use Alchemy\Zippy\Zippy;
use App\Helpers\DataStore;
use Devless\Schema\DbHandler as DvSchema;
use Symfony\Component\VarDumper\Cloner\Data;
use App\Helpers\Jwt as jwt;

/*
* @author Eddymens <eddymens@devless.io
*/

class DevlessHelper extends Helper
{
    use serviceHelper, authHelper, directoryHelper;
    /**
     * set paramters for notification plate
     *
     * @param type $message
     * @param type|string $message_color
     */
    public static function flash($message, $message_color = "#736F6F")
    {
        $custom_colors =
            [
                'error' => '#EA7878',
                'warning' => '#F1D97A',
                'success' => '#7BE454',
                'notification' => '#736F6F',
            ];
        (isset($custom_colors[$message_color]))?$notification_color =
            $custom_colors[$message_color]
            : $notification_color = $message_color;

        session::flash('color', $notification_color);
        session::flash('flash_message', $message);
    }

    

    /**
     * Delete table is exists
     * @param $serviceName
     * @param $tableName
     * @return bool
     */
    public static function purge_table($serviceName, $tableName)
    {
        $service = new ServiceController();
        return DataStore::service($serviceName, $tableName, $service)->drop()? true: false;

    }


    /**
     * convert string to json
     * @param $incomingArray
     * @return string
     * @internal param $incommingArray
     * @internal param $service_components
     */
    public static function convert_to_json($incomingArray)
    {

        $formatted_json = json_encode($incomingArray, true);

        return $formatted_json;

    }

    /**
     * Check method access type
     * @param $method
     * @param $class
     */
    public static function rpcMethodAccessibility($class, $method)
    {
        $property = $class->getMethod($method);
        $docComment  = $property->getDocComment();

        $access_type = function () use ($docComment) {
            (strpos(($docComment), '@ACL private'))? Helper::interrupt(627) :
                (strpos($docComment, '@ACL protected'))? Helper::get_authenticated_user_cred(2) :
                    (strpos($docComment, '@ACL public'))? true : Helper::interrupt(638) ;

        };

        $access_type();

    }

    public static function instance_log($url, $token, $purpose)
    {
        $sdk = new SDK($url, $token);
        $instance = DataStore::instanceInfo();

        $user = $instance['admin'];
        $app  = $instance['app'];
        $data = [
          'username' => $user->username,
          'email' => $user->email,
          'token' => $app->token,
          'connected_on' => Date(DATE_RFC2822),
          'instance_url' => $_SERVER['HTTP_HOST'],
          'purpose'      => $purpose
        ];
        $status = $sdk->addData('INSTANCE_LOG', 'instance', $data);
        return ($status['status_code'] == 609)? true : false;

    }

}

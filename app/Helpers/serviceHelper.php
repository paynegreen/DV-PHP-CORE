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

trait serviceHelper{


    /**
     * Install service and or package given service path
     * @param $service_path
     * @return bool
     */
    public static function install_service($service_path)
    {

        $builder = new DvSchema();
        $service_file_path = $service_path.'service.json';
        $service_file_path = preg_replace('"\.srv"', '', $service_file_path);
        $service_file_path = preg_replace('"\.pkg"', '', $service_file_path);
        $fh = fopen($service_file_path, 'r');
        $service_json = fread($fh, filesize($service_file_path));
        fclose($fh);

        $service_object = json_decode($service_json, true);
        $service_id = [];
        $service_name = [];
        $service_id_map = [];
        $install_services = function ($service) use (&$service_id, &$service_name, &$service_id_map) {
            if (count(\DB::table('services')->where('name', $service['name'])->get()) != 0) {
                return false;
            }
            $old_service_id = $service['id'];
            $service_name[$old_service_id] = $service['name'];
            unset($service['id']);
            \DB::table('services')->insert($service);
            $last_service = \DB::table('services')->orderBy('id', 'desc')->first();
            $service_id_map[$old_service_id] = $last_service->id ;
        };
        if (!isset($service_object['service'][0])) {
            $install_services($service_object['service']);
        } else {
            foreach ($service_object['service'] as $service) {
                $install_services($service);
            }
        }
        //get meta from service_table
        $table_meta_install = function ($service_table) use (
            &$service_id_map,
            &$builder,
            &$service_name
        ) {

            if (sizeof($service_table) !== 0) {
                if (\Schema::hasTable($service_table['table_name'])) {
                        return false;
                }
                $old_service_id = $service_table['service_id'];
                $new_service_id = $service_id_map[$old_service_id];
                $service_table['schema'] = json_decode($service_table['schema'], true);
                $service_table['service_name'] = $service_name[$old_service_id];
                $service_table['driver'] = "default";
                $service_table['schema']['service_id'] = $new_service_id ;
                $service_table['service_id'] = $new_service_id ;
                $service_table['schema']['id'] = $new_service_id;
                $service_table['id'] = $new_service_id;
                $service_table['params'] = [0 =>$service_table['schema']];
                $builder->create_schema($service_table);
            }


        };
        if (!isset($service_object['tables'][0])) {
            $table_meta_install($service_object['tables']);
        } else {
            foreach ($service_object['tables'] as $service_table) {
                $table_meta_install($service_table);
            }
        }

        unlink($service_file_path);

        return true;
    }

     /**
     * install views into service_views dir
     * @param $service_name
     * @return bool
     */
    public static function install_views($service_name)
    {
        $system_view_directory = config('devless')['views_directory'];
        $service_view_directory = $service_name.'view_assets';
        self::recurse_copy($service_view_directory, $system_view_directory);
        self::deleteDirectory($service_view_directory);

        return true;
    }

     /**
     * Get table name from payload
     * @param $payload
     * @return string
     */
    public static function get_tablename_from_payload($payload)
    {
        if (strtoupper($payload['method']) == 'GET') {
            $tableName = (isset($payload['params']['table']))?$payload['params']['table']
                        :'';
        } else {
            $tableName = (isset($payload['params'][0]['name']))?$payload['params'][0]['name']
                :'';
        }
        return $tableName;
    }


    /**
     * Execute on views creation
     * @param $payload
     * @return bool
     */
    public static function execOnViewsCreation($payload)
    {
        $serviceName = $payload['serviceName'];

        $instanceInfo = DataStore::instanceInfo();

        $username = $instanceInfo['admin']->username;
        $files = ['ActionClass.php'];
        $time = date('jS \of F Y h:i:s A');
        $version = config('devless')['version'];
        $replacements = [
            '{{ServiceName}}' => $serviceName,

            '{{MAINDOC}}'=> '/**
 * Created by Devless.
 * Author: '.$username.'
 * Date Created: '.$time.'
 * Service: '.$serviceName.'
 * Version: '.$version.'
 */
',
        ];

        return self::modifyAssetContent($serviceName, $files, $replacements);
    }

    /**
     * execute scripts after installing and deleting services
     * @param type $payload
     * @return boolean
     */
    public static function execOnServiceStar($payload)
    {
        $service = $payload['serviceName'];
        $serviceMethodPath = config('devless')['views_directory'].$service.'/ActionClass.php';

        (file_exists($serviceMethodPath))?
            require_once $serviceMethodPath : false;

        if (class_exists($service)) {
                $serviceInstance = new $service();
            $results = (isset($payload['delete']) && !isset($payload['install']) && $payload['delete'] == '__onDelete')?
                $serviceInstance->__onDelete() :
                        (isset($payload['install']) && !isset($payload['delete']) && $payload['install'] == '__onImport')?
                            $serviceInstance->__onImport() : false;
            return $results;
        } else {
            return false;
        }

    }
    /**
     * get service_component from db
     * @param $service_name
     * @return service_object
     */
    public static function get_service_components($service_name)
    {
        $service = \DB::table('services')
            ->where('name', $service_name)->first();

        $tables = \DB::table('table_metas')
            ->where('service_id', $service->id)->get();

        $views_folder =$service_name;

        $service_components['service'] = $service;
        $service_components['tables'] = $tables;
        $service_components['views_folder'] = $views_folder;

        $service_components = self::convert_to_json($service_components);

        return $service_components;
    }

    /** Get all service attributes
     * @return string
     */
    public static function get_all_services()
    {
        $services = \DB::table('services')->get();
        $tables = \DB::table('table_metas')->get();

        $services_components['service'] = $services;
        $services_components['tables'] = $tables;

        $services_components = self::convert_to_json($services_components);

        return $services_components;

    }

}
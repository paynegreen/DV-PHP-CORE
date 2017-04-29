<?php

namespace Devless\Schema;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\Response as Response;
use App\Http\Controllers\ServiceController as Service;

trait destroyData
{
    use deleteParamList;
    /**
     * Remove the specified resource from storage.
     *
     * @param array $payload payload from request
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param string $resource
     */
    public function destroy($payload)
    {
        $db = $this->connect_to_db($payload);
        //check if table name is set
        $service_name = $payload['service_name'];
        $table = $payload['params'][0]['name'];
        $param_list = $payload['params'][0]['params'][0];

        //remove service appendage from service
        if (($pos = strpos($table, $service_name.'_')) !== false) {
            $tableWithoutService = substr($table, $pos + 1);
        } else {
            $tableWithoutService = $table;
        }

        $table_name = ($tableWithoutService == $payload['params'][0]['name'])
            ? $service_name.'_'.$tableWithoutService :
            $payload['params'][0]['name'];
        
        $this->check_table_existence($service_name, $table);
        

        $destroy_base_query = '$db->table("'.$table_name.'")';
        $destroy_query = $this->check_userbased_destroy($payload, $destroy_base_query);
        dd($param_list);
        
        $this->drop_table($payload, $table_name, $task);

        if (isset($payload['params'][0]['params'][0]['where'])) {
            if ($payload['params'][0]['params'][0]['where'] == true) {
                $where = $payload['params'][0]['params'][0]['where'];
                $where = str_replace(',', "','", $where);
                $where = "'".$where."'";
                $destroy_query = $destroy_query.'->where('.$where.')';
                $task = 'failed';
            }
        }
        $element = 'row';
        if (isset($payload['params'][0]['params'][0]['truncate'])) {
            if ($payload['params'][0]['params'][0]['truncate'] == true) {
                $destroy_query = $destroy_query.'->truncate()';
                $tasked = 'truncated';
                $task = 'truncate';
            }
        } elseif (isset($payload['params'][0]['params'][0]['delete'])) {
            if ($payload['params'][0]['params'][0]['delete'] == true) {
                $destroy_query = $destroy_query.'->delete()';
                $tasked = 'deleted';
                $task = 'delete';
            }
        } else {
            Helper::interrupt(615);
        }
        $destroy_query = $destroy_query.';';
        $result = eval('return'.$destroy_query);
        if ($result == 0) {
            Helper::interrupt(614, 'could not '.$task.' '.$element);
        }

        return Response::respond(636, 'The table or field has been '.$task);
    }

    private function check_userbased_destroy($payload, $base_query)
    {
        if ($payload['user_id'] !== '') {
            $user_id = $payload['user_id'];
            return $base_query.'->where("devless_user_id",'.$user_id.')';    
        }
        return $base_query;
        
    }
}

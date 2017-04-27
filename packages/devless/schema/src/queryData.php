<?php

namespace Devless\Schema;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\Response as Response;

trait queryData
{
    use queryParamList;
    /**
     * query a table.
     *
     * @param array $payload payload from request
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param string $resource
     */
    public function db_query($payload)
    {
        $service_name = $payload['service_name'];
        $connector = $this->_connector($payload);
        $queried_table_list = null;
        $size_count = null;
        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        $results = [];
        //check if table name is set
        if (isset($payload['params']['table'][0])) {
            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($service_name.'_'.$payload['params']['table'][0])) {
                return Helper::interrupt(634);
            }

            $table_name = $service_name.'_'.$payload['params']['table'][0];

            $base_query = '$db->table("'.$table_name.'")';

            $complete_query = $this->set_query_options(
                $base_query,
                $payload,
                $size_count,
                $queried_table_list
            );

            ($payload['user_id'] !== '') ?
            $complete_query = $complete_query.'->where("devless_user_id",'.$payload['user_id'].')' : '';

        //finally loop over remaining query params (where)
            foreach ($payload['params'] as $key => $query) {
                foreach ($query as $one) {
                    //prepare query for order and where
                    if (isset($this->query_params[$key])) {
                        $query_params = explode(',', $one);
                        if (isset($query_params[1], $query_params[0])) {
                            $complete_query = $complete_query.
                            '->'.$this->query_params[$key].'("'.$query_params[0].
                            '","'.$query_params[1].'")';
                        } else {
                            Helper::interrupt(612);
                        }
                    } else {
                        Helper::interrupt(610, "Query parameter `$key` does not exist");
                    }
                }
            }
            $count = ($size_count) ? $size_count : $db->table($table_name)->count();
            if (isset($queried_table_list)) {
                $related = function ($results) use ($queried_table_list, $service_name, $table_name, $payload) {
                    return $this->_get_related_data(
                        $payload,
                        $results,
                        $table_name,
                        $queried_table_list
                    );
                };
                $endOutput = [];

                $complete_query = $complete_query.'
                ->chunk($count, function($results) use (&$endOutput, $related) {
                    $endOutput =  $related($results);
                });';
            } else {
                $complete_query = 'return '.$complete_query.'->get();';
            }
            $query_output = eval($complete_query);
            $results['properties']['count'] = $count;
            $results['results'] = (isset($queried_table_list)) ? $endOutput : $query_output;
            $results['properties']['current_count'] = count($results['results']);

            return Response::respond(625, null, $results);
        } else {
            Helper::interrupt(611);
        }
    }

    private function set_query_options(&$complete_query, &$payload, &$size_count, &$queried_table_list)
    {
        $query_args_list = [
                'related' => [&$complete_query, &$payload, &$queried_table_list],
                'size' => [&$complete_query, &$payload, &$size_count],
        ];

        unset($payload['params']['table']);
        foreach ($payload['params'] as $param_name => $param_value) {
            $query_args = ($param_name != 'related' || $param_name != 'related') ? [&$complete_query, &$payload] : $query_args_list[$param_name];
            call_user_func_array([$this, $param_name], $query_args);
        }

        unset($payload['params']['table'], $payload['params']['size'], $payload['params']['offset']);

        return $complete_query;
    }
}

<?php
namespace Devless\Schema;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Helpers\Response as Response;
use App\Http\Controllers\ServiceController as Service;
use Illuminate\Database\Schema\Blueprint as Blueprint;

class DbHandler
{
    use columns, connector, tableMeta, relation;
    public $db_types = [
        'text'       => 'string',
        'textarea'   => 'longText',
        'integer'    => 'integer',
        'decimals'   => 'double',
        'password'   => 'string',
        'percentage' => 'integer',
        'url'        => 'string',
        'timestamp'  => 'timestamp',
        'boolean'    => 'boolean',
        'email'      => 'string',
        'reference'  => 'integer',
        'base64'     => 'binary',
    ];
    public $query_params = [
        'order'    => 'orderBy',
        'where'    => 'where',
        'orWhere'  => 'orWhere',
        'take'     => 'take',
        'relation' => 'relation',
        'search'   => 'search', 
        'randomize' => 'randomize'
    ];
    public $dbActionAssoc = [
        'GET'    => 'query',
        'POST'   => 'create',
        'PATCH'  => 'update',
        'DELETE' => 'delete',
    ];
    private $dbActionMethod = [
        'GET'    => 'db_query',
        'POST'   => 'add_data',
        'PATCH'  => 'update',
        'DELETE' => 'destroy',
    ];
    /**
     * Access db functions based on request method type.
     *
     * @param string resource name $resource
     * @param array payload        $payload
     *
     * @return \App\Helpers\json|\Illuminate\Http\Response
     */
    public function access_db($payload)
    {

        $payload['user_id'] = '';
        $request = $payload['method'];
        $db_action = (isset($this->dbActionAssoc[$request])) ? $this->dbActionAssoc[$request]
            : Helper::interrupt(607);
        $payload = $this->set_auth_id_if_required($db_action, $payload);
        $dbActionName = $this->dbActionMethod[$request];
        return $this->$dbActionName($payload);
    }
    /**
     * Create new table schema.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *                                   api/v1/schema
     */
    public function store(Request $request)
    {
        $this->create_schema($request['resource']);
    }
    /**
     * query for data from db.
     *
     * @param $payload
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param string $resource
     */
    public function add_data($payload)
    {
        $service_name = $payload['service_name'];
        //setup db connection
        $this->_connector($payload);
        $db = \DB::connection('DYNAMIC_DB_CONFIG');

        (isset($payload['params'][0]['name']) && count($payload['params'][0]['name'])> 0
            && gettype($payload['params'][0]['field']) == 'array' || isset($payload['params'][0]['field'][0]) )? true :
            Helper::interrupt(641);
        foreach ($payload['params'] as $table) {
            $table_name = $table['name'];
            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($service_name.'_'.$table_name)) {
                Helper::interrupt(634);
            }
            //check data against field type before adding data
            $table_data = $this->_validate_fields(
                $table_name,
                $service_name,
                $table['field'],
                true
            );
            //assigning autheticated user id
            $table_data[0]['devless_user_id'] = $payload['user_id'];
            $output = $db->table($service_name.'_'.$table['name'])->insert($table_data);
        }
        if ($output) {
            return Response::respond(609, 'Data has been added to '.$table['name']
                .' table successfully');
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param array $payload payload
     *
     * @return \App\Helpers\json
     *
     * @internal param string $resource
     */
    public function update($payload)
    {
        $this->_connector($payload);
        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        $service_name = $payload['service_name'];
        if (isset(
            $payload['params'][0]['name'],
            $payload['params'][0]['params'][0]['where'],
            $payload['params'][0]['params'][0]['data']
        )) {
            $table_name = $service_name.'_'.$payload['params'][0]['name'];
            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($table_name)) {
                return Helper::interrupt(634);
            }
            $where = $payload['params'][0]['params'][0]['where'];
            $explosion = explode(',', $where);
            $data = $payload['params'][0]['params'][0]['data'];
            if ($payload['user_id'] !== '') {
                $result = $db->table($table_name)
                    ->where($explosion[0], $explosion[1])
                    ->where('devless_user_id', $payload['user_id'])
                    ->update($data[0]);
            } else {
                $result = $db->table($table_name)
                    ->where($explosion[0], $explosion[1])
                    ->update($data[0]);
            }
            if ($result == 1) {
                return Response::respond(
                    619,
                    'table '.$payload['params'][0]['name'].' updated successfuly'
                );
            } else {
                Helper::interrupt(629, 'Table '.$payload['params'][0]['name'].' could not be updated');
            }
        } else {
            Helper::interrupt(614);
        }
    }
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
        $this->_connector($payload);
        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        //check if table name is set
        $service_name = $payload['service_name'];
        $table = $payload['params'][0]['name'];

        //remove service appendage from service
        if (($pos = strpos($table, $service_name.'_')) !== false) {
            $tableWithoutService = substr($table, $pos + 1);
        } else {
            $tableWithoutService = $table;
        }

        $table_name = ($tableWithoutService == $payload['params'][0]['name'])
            ? $service_name.'_'.$tableWithoutService:
            $payload['params'][0]['name'];
        if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
        hasTable($table_name)) {
            Helper::interrupt(634);
        }
        //
        if ($payload['user_id'] !== '') {
            $user_id = $payload['user_id'];
            $destroy_query = '$db->table("'.$table_name.'")->where("devless_user_id",'.$user_id.')';
        } else {
            $destroy_query = '$db->table("'.$table_name.'")';
        }
        if (isset($payload['params'][0]['params'][0]['drop'])) {
            if ($payload['params'][0]['params'][0]['drop'] == true) {
                \Schema::connection('DYNAMIC_DB_CONFIG')->dropIfExists($table_name);
                (Helper::is_admin_login()) ?
                    \DB::table('table_metas')->where('table_name', $table_name)->delete() : Helper::interrupt(620);
                return Response::respond(613, 'dropped table successfully');
                $task = 'drop';
            }
        }
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
        $queried_table_list = null;$size_count = null;
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
            
            $complete_query = $base_query;
            $query_params = [
                'offset'=>function() use(&$complete_query, &$payload){   
                        $complete_query =
                        $complete_query.'->skip('.$payload['params']['offset'][0].')';},

                'size'=>function() use(&$complete_query, &$payload, &$size_count){
                        $complete_query = $complete_query
                        .'->take('.$payload['params']['size'][0].')';
                        $size_count = $payload['params']['size'][0];},

                'related'=>function() use(&$complete_query, &$payload, &$queried_table_list){
                        $queried_table_list =$payload['params']['related'];
                        unset($payload['params']['related']);},

                'orderBy'=>function() use(&$complete_query, &$payload){
                        $complete_query = $complete_query
                        .'->orderBy("'.$payload['params']['orderBy'][0].'" )';
                        unset($payload['params']['orderBy']);},

                'randomize'=>function() use(&$complete_query, &$payload) {
                        $complete_query = $complete_query
                        .'->orderByRaw("RAND()")';
                        unset($payload['params']['randomize']);
                },
                'search'=>function() use(&$complete_query, &$payload){
                        $split_query = explode(',', $payload['params']['search'][0]);
                        $search_key = $split_query[0];
                        $search_words = explode(' ', $split_query[1]);
                        foreach($search_words as $search_word){
                            $complete_query = $complete_query.'->orWhere("'.$search_key.'","LIKE","%'.$search_word.'%")';
                        }
                        unset($payload['params']['search']);
                        },
            ];
            unset($payload['params']['table']);
            foreach($payload['params'] as $param_name =>$param_value){
                (isset($query_params[$param_name]))? $query_params[$param_name]():'';
            }
                
          
            unset(
                $payload['params']['table'],
                $payload['params']['size'],
                $payload['params']['offset']
            );
            
            ($payload['user_id'] !== '')?
                $complete_query = $complete_query.'->where("devless_user_id",'.$payload['user_id'].')':'';
               
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
            $count = ($size_count)? $size_count: $db->table($table_name)->count();
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
            $results['results'] = (isset($queried_table_list))? $endOutput : $query_output;
            $results['properties']['current_count'] = count($results['results']);
            return Response::respond(625, null, $results);
        } else {
            Helper::interrupt(611);
        }
    }
    /**
     * Create a service table.
     *
     * @param array $payload
     *
     * @return true
     */
    public function create_schema($payload)
    {
        if (!Helper::is_admin_login()) {
            $this->set_auth_id_if_required('schema', $payload);
        }
        $service_name = $payload['service_name'];

        //connectors mysql pgsql sqlsrv sqlite
        $this->_connector($payload);

        //dynamically create columns with schema builder
        $db_type = $this->db_types;
        $new_payload = $payload['params'][0];
        $new_payload['id'] = $payload['id'];
        $table_name = $service_name.'_'.$new_payload['name'];
        if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
        hasTable($service_name.'_'.$table_name)) {
            \Schema::connection('DYNAMIC_DB_CONFIG')->
            create($table_name, function (Blueprint
                                          $table) use (&$new_payload, $db_type, $service_name) {
                //default field
                $table->increments('id');
                $table->integer('devless_user_id');

                //generate remaining fields
                $count = 0;
                foreach ($new_payload['field'] as $field) {
                    $field['ref_table'] = $service_name.'_'.$field['ref_table'];
                    $field['field_type'] = strtolower($field['field_type']);

                    //check if users table is being referenced
                    if ($field['field_type'] == 'reference' &&
                        strpos($field['ref_table'], '_devless_users')
                        == true) {
                        $field['ref_table'] = 'users';

                        $new_payload['field'][$count]['ref_table'] = '_devless_users';
                        $new_payload['field'][$count]['name'] = 'users_id';
                    }

                    $this->field_type_exist($field);
                    //generate columns
                    $this->column_generator($field, $table, $db_type);
                    $count++;
                }
                //store table_meta details
            });
            $this->set_table_meta($service_name, $new_payload);
            return Response::respond(606);
        } else {
            Helper::interrupt(603, $table_name.' table already exist');
        }
    }
   
 
    /**
     * add user id to payload.
     *
     * @param $db_action
     * @param $payload
     *
     * @return mixed $payload || boolean
     */
    private function set_auth_id_if_required($db_action, $payload)
    {
        $service = new Service();
        $user_id = ($db_action != 'query')? 1: '';
        $access_type = $payload['resource_access_right'];
        $access_state = $service
            ->check_resource_access_right_type($access_type[$db_action]);

        if ($access_state == true) {
            $user_cred = Helper::get_authenticated_user_cred($access_state);
            $user_id = $user_cred['id'];
        }
        $payload['user_id'] = $user_id;
        return $payload;
    }

    /**
     * Get DevLess table name
     * @param $serviceName
     * @param $tableName
     * @return string
     */
    public function devlessTableName($serviceName, $tableName)
    {
        return $serviceName.'_'.$tableName;
    }
    /**
     * validate incoming  data against schema field type.
     *
     * @param string $table_name
     * @param $service_name
     * @param $table_data
     * @param bool $check_password
     *
     * @return bool
     *
     * @internal param string $service_id
     * @internal param array $field_names
     */
    private function _validate_fields(
        $table_name,
        $service_name,
        $table_data,
        $check_password = false
    ) {

        $table_meta = $this->get_tableMeta($service_name.'_'.$table_name);
        $schema = $table_meta['schema'];
        $count = 0;
        foreach ($table_data as $field_unit) {
            foreach ($field_unit as $field => $field_value) {
                foreach ($schema['field'] as $fields) {
                    if ($fields['name'] == $field) {
                        if ($check_password == true &&
                            strtolower($fields['field_type']) == 'password') {
                            $table_data[$count]['password'] =
                                Helper::password_hash($table_data[$count]['password']);
                        }
                        if (!Helper::field_check($field_value, $fields['field_type'])) {
                            Helper::interrupt(616, 'The field '.$fields['name'].' cannot  be set to `'.$field_value.'`. It\'s '. $fields['field_type']);
                        }
                        if($fields['required'] && strlen($field_value) == 0 ){Helper::interrupt(616, $field.' cannot be empty');}
                        
                    }
                }
            }
            $count++;
        }
        $hit = 1;
        if ($hit == 0) {
            Helper::interrupt(617);
        }
        if ($check_password == 'true') {
            return $table_data;
        } else {
            return true;
        }
    }
}

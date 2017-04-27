<?php

namespace Devless\Schema;

use App\Helpers\Helper;
use App\Helpers\Response as Response;

trait addData
{
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

        $db = $this->connect_to_db($payload);

        $this->validate_payload($payload);

        foreach ($payload['params'] as $table) {
            $this->check_table_exitence($table);

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
            return $this->response();
        }
    }

    private function connect_to_db()
    {
        $this->_connector($payload);

        return \DB::connection('DYNAMIC_DB_CONFIG');
    }

    private function response()
    {
        return Response::respond(609, 'Data has been added to '.$table['name']
                .' table successfully');
    }

    private function check_table_exitence($table)
    {
        $table_name = $table['name'];
        if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($service_name.'_'.$table_name)) {
            Helper::interrupt(634);
        }
    }

    private function validate_payload($payload)
    {
        (isset($payload['params'][0]['name']) && count($payload['params'][0]['name']) > 0
            && gettype($payload['params'][0]['field']) == 'array' || isset($payload['params'][0]['field'][0])) ? true :
            Helper::interrupt(641);
    }
}

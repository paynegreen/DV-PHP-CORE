<?php

namespace Devless\Schema;

use App\Helpers\Helper;
use App\Helpers\Response as Response;

trait updateData
{
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
}

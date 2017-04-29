<?php
namespace Devless\Schema;

trait deleteParamList {
	private function drop_table($param_list, $table_name, &$task)
	{
		 if ($param_list['drop'] == true) {
	            \Schema::connection('DYNAMIC_DB_CONFIG')->dropIfExists($table_name);
	            (Helper::is_admin_login()) ?
	                \DB::table('table_metas')->where('table_name', $table_name)->delete() : Helper::interrupt(620);

	            return Response::respond(613, 'dropped table successfully');
	            $task = 'drop';
	        }
	}
}
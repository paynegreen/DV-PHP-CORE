<?php

namespace Devless\Schema;

trait schemaProperties
{
    public $db_types = [
        'text' => 'string',
        'textarea' => 'longText',
        'integer' => 'integer',
        'decimals' => 'double',
        'password' => 'string',
        'percentage' => 'integer',
        'url' => 'string',
        'timestamp' => 'timestamp',
        'boolean' => 'boolean',
        'email' => 'string',
        'reference' => 'integer',
        'base64' => 'binary',
    ];
    public $query_params = [
        'order' => 'orderBy',
        'where' => 'where',
        'orWhere' => 'orWhere',
        'take' => 'take',
        'relation' => 'relation',
        'search' => 'search',
        'randomize' => 'randomize',
    ];
    public $dbActionAssoc = [
        'GET' => 'query',
        'POST' => 'create',
        'PATCH' => 'update',
        'DELETE' => 'delete',
    ];
    private $dbActionMethod = [
        'GET' => 'db_query',
        'POST' => 'add_data',
        'PATCH' => 'update',
        'DELETE' => 'destroy',
    ];
}

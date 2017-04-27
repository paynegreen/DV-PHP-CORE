<?php

namespace Devless\Schema;

trait queryParamList
{
    public $query_params = [
        'order' => 'orderBy',
        'where' => 'where',
        'orWhere' => 'orWhere',
        'take' => 'take',
        'relation' => 'relation',
        'search' => 'search',
        'randomize' => 'randomize',
    ];

    private function size(&$complete_query, &$payload, &$size_count)
    {
        $complete_query = $complete_query
                        .'->take('.$payload['params']['size'][0].')';
        $size_count = $payload['params']['size'][0];
    }

    private function offset(&$complete_query, &$payload)
    {
        $complete_query =
                        $complete_query.'->skip('.$payload['params']['offset'][0].')';
    }

    private function randomize(&$complete_query, &$payload)
    {
        $complete_query = $complete_query
                        .'->orderByRaw("RAND()")';
        unset($payload['params']['randomize']);
    }

    private function related(&$complete_query, &$payload, &$queried_table_list)
    {
        $queried_table_list = $payload['params']['related'];
        unset($payload['params']['related']);
    }

    private function search(&$complete_query, &$payload)
    {
        $split_query = explode(',', $payload['params']['search'][0]);
        $search_key = $split_query[0];
        $search_words = explode(' ', $split_query[1]);
        foreach ($search_words as $search_word) {
            $complete_query = $complete_query.'->orWhere("'.$search_key.'","LIKE","%'.$search_word.'%")';
        }
        unset($payload['params']['search']);
    }

    private function orderBy(&$complete_query, &$payload)
    {
        $complete_query = $complete_query
                        .'->orderBy("'.$payload['params']['orderBy'][0].'" )';
        unset($payload['params']['orderBy']);
    }
}

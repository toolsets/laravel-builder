<?php
/**
 * Created by PhpStorm.
 * User: raf
 * Date: 19/12/2016
 * Time: 7:33 PM
 */

namespace Toolkits\LaravelBuilder\Services\Database;


use Illuminate\Database\PostgresConnection as LaravelPostgresConnection;
use Toolkits\LaravelBuilder\Services\Database\Schema\Builder;
use Toolkits\LaravelBuilder\Services\Database\Schema\Grammars\PostgresGrammar;
use Toolkits\LaravelBuilder\Services\Database\Schema\PostgresBuilder;


class PostgresConnection extends LaravelPostgresConnection
{

    /**
     * Get a schema builder instance for the connection.
     *
     * @return PostgresBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
                $this->useDefaultSchemaGrammar();

        }

        $builder = new PostgresBuilder($this);

        return $builder;
    }
}
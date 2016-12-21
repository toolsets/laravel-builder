<?php
/**
 * Created by PhpStorm.
 * User: raf
 * Date: 19/12/2016
 * Time: 9:54 PM
 */

namespace Toolkits\LaravelBuilder\Services\Database\Migration;


use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Facades\Storage;
use Toolkits\LaravelBuilder\Parsers\Yaml;


class MigrationSnapshot
{

    public static $shots = null;

    public static $migrated = false;

    /**
     * The possible column serials.
     *
     * @var array
     */
    protected $serials = [
        'mysql' => ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'],
        'sqlite' => ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'],
        'pgsql' => ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'],
        'sqlsrv' => ['tinyInteger', 'smallInteger', 'mediumInteger', 'integer', 'bigInteger']
    ];


    protected $data = [];

    public static function capture(Blueprint $blueprint, Connection $connection)
    {
        if(is_null(static::$shots)) {
            static::$shots = new static();
        }

        static::$shots->record($blueprint, $connection);
    }


    public function record(Blueprint $blueprint, Connection $connection)
    {
        $connectionName = $connection->getName();
        $databaseName = $connection->getDatabaseName();
        $databaseDriver = $connection->getDriverName();

        $serials = $this->serials[$databaseDriver];

        $table = $blueprint->getTable();
        $addedColumns = $blueprint->getAddedColumns();
        $changedColumns = $blueprint->getChangedColumns();
        if (!isset($this->data[$connectionName])) {
            $this->data[$connectionName] = [];
        }

        if (!isset($this->data[$connectionName][$databaseName])) {
            $this->data[$connectionName][$databaseName] = [];
        }

        if (!isset($this->data[$connectionName][$databaseName][$table])) {
            $this->data[$connectionName][$databaseName][$table] = ['columns' => []];
        }

        $existingColumns = $this->data[$connectionName][$databaseName][$table]['columns'];

        if (!empty($changedColumns)) {
            foreach ($changedColumns as $column) {
                $col_name = $column->name;

                if (isset($existingColumns[$col_name])) {
                    if(!isset($existingColumns[$col_name]['attributes']))
                    {
                        $existingColumns[$col_name]['attributes']=[];
                    }

                    $current_attr = $existingColumns[$col_name]['attributes'];

                    //Check for primaryKey
                    if (in_array($column->type, $serials) && $column->autoIncrement) {
                        $column->primaryKey = true;
                    }

                    $attributes = $column->toArray();
                    unset($attributes['change']);
                    $existingColumns[$col_name]['attributes'] = array_merge($current_attr, $attributes);
                    $existingColumns[$col_name]['migrated'] = static::$migrated;
                }
            }

            $this->data[$connectionName][$databaseName][$table]['columns'] = $existingColumns;
        }


        if (!empty($addedColumns)) {
            foreach ($addedColumns as $column) {
                $col_name = $column->name;

                if (!isset($existingColumns[$col_name])) {
                    if(!isset($existingColumns[$col_name]['attributes']))
                    {
                        $existingColumns[$col_name]['attributes']=[];
                    }

                    //Check for primaryKey
                    if (in_array($column->type, $serials) && $column->autoIncrement) {
                        $column->primaryKey = true;
                    }

                    $existingColumns[$col_name]['attributes'] =  $column->toArray();
                    $existingColumns[$col_name]['migrated'] = static::$migrated;
                }
            }

            $this->data[$connectionName][$databaseName][$table]['columns'] = $existingColumns;
        }

        $commands = $blueprint->getCommands();

        if(!empty($commands))
        {
            $table_state = $this->data[$connectionName][$databaseName][$table];
            $newState = $this->processCommands($commands, $table_state);
            $this->data[$connectionName][$databaseName][$table] = $newState;
        }
//        $this->data[$connectionName][$databaseName][$table]['command'] = $commands;
        $this->data[$connectionName][$databaseName][$table]['migrated'] = static::$migrated;

    }


    public function get()
    {
        return $this->transform($this->data);
    }

    public function save()
    {
        $parser = Yaml::make();
        $yaml = $parser->toString($this->transform($this->data));

        $project_path =  'toolsets/builder/' . 'database' ;
        $project_real_path = storage_path($project_path);

        if (!file_exists($project_real_path)) {
            Storage::makeDirectory($project_path);
        }

        $filepath = $project_path . '/db_schema.yml';

        Storage::put($filepath, $yaml);

        return storage_path($filepath);
    }


    public function transform($data)
    {
        $formatted = [];

        foreach ($data as $connection => $databases) {
            $dbs = [];

            foreach ($databases as $database => $tables) {
                $tbls = [];

                foreach ($tables as $table => $describer) {

                    $attr_defaults = [
                        'name' => '',
                        'type' => '',
                        'length' => '',
                        'unsigned' => false,
                        'nullable' => false,
                        'autoIncrement' => false,
                        'primaryKey' => false,
                        'default' => NULL
                    ];


                    foreach($describer['columns'] as $col => $col_details)
                    {
                        $attr = array_merge($attr_defaults, $col_details['attributes']);

                        $describer['columns'][$col]['attributes'] = $attr;
                    }

                    $tbls[] = [
                        'table_name' => $table,
                        'columns' => $describer['columns'],
                        'relations' => $describer['relations'],
                        'indexes'   => $describer['indexes'],
                        'commands' => $describer['commands']
                    ];
                }

                $dbs[$database] = [
                    'tables' => $tbls
                ];
            }


            $formatted['connections'][$connection] = [
                'databases' => $dbs
            ];
        }

        return $formatted;
    }

    protected function processCommands($commands, $table_state)
    {
        $columns = $table_state['columns'];
        if(!isset($table_state['relations']))
        {
            $table_state['relations'] = [];
        }

        if(!isset($table_state['indexes']))
        {
            $table_state['indexes'] = [];
        }

        if(!isset($table_state['commands']))
        {
            $table_state['commands'] = [];
        }

        foreach($commands as $command)
        {
            switch ($command->name)
            {
                case 'dropForeign':
                    $relations = $table_state['relations'];
                    $index = $command->index;
                    if (isset($relations[$index])) {
                        if ($relations[$index]['migrated'] == static::$migrated) {
                            unset($relations[$index]);
                            $table_state['relations'] = $relations;
                        }
                    }
                    break;

                case 'foreign':
                    // add foreign key to relations
                    $relations = $table_state['relations'];
                    $relations[$command->index] = [
                        'migrated' => static::$migrated,
                        'index' => $command->index,
                        'columns' => $command->columns,
                        'fk_column' => $command->references,
                        'fk_table' => $command->on
                    ];

                    if (isset($command->onDelete)) {
                        $relations[$command->index]['on_delete'] = $command->onDelete;
                    }

                    if (isset($command->onUpdate)) {
                        $relations[$command->index]['on_update'] = $command->onUpdate;
                    }

                    $table_state['relations'] = $relations;
                    break;

                case 'index':
                case 'unique':
                case 'primary':
                    $indexes = $table_state['indexes'];
                    $indexes[$command->index] = [
                        'migrated' => static::$migrated,
                        'type' => $command->name,
                        'index' => $command->index,
                        'columns' => $command->columns
                    ];

                    $table_state['indexes'] = $indexes;
                    break;

                case 'dropIndex':
                case 'dropUnique':
                case 'dropPrimary':
                    $indexes = $table_state['indexes'];
                    if (isset($indexes[$command->index])) {
                        if ($indexes[$command->index]['migrated'] == static::$migrated) {
                            unset($indexes[$command->index]);
                            $table_state['indexes'] = $indexes;
                        }

                    }
                    break;

                case 'renameColumn':
                    if (isset($columns[$command->from])) {
                        $column = $columns[$command->from];
                        if ($column['migrated'] == static::$migrated) {
                            $column['attributes']['name'] = $command->to;
                            $column['migrated'] = static::$migrated;
                            $columns[$command->from] = $column;

                            $keys = array_keys($columns);
                            $values = array_values($columns);

                            $index = array_search($command->from, $keys);
                            $keys[$index] = $command->to;
                            $columns = array_combine($keys, $values);
                        }
                    }
                    break;
            }

            if(static::$migrated == false)
            {
                $command_array = $command->toArray();
                $command_array['migrated'] = static::$migrated;
                $table_state['commands'][] = $command_array;
            }

        }

        $table_state['columns'] = $columns;

        return $table_state;
    }
}
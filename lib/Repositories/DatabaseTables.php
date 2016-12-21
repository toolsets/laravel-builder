<?php
/**
 * Created by PhpStorm.
 * User: raf
 * Date: 20/12/2016
 * Time: 7:24 PM
 */

namespace Toolkits\LaravelBuilder\Repositories;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Toolkits\LaravelBuilder\Parsers\Yaml;

class DatabaseTables
{

    protected $tbl_column_fields = [
        'name' => [
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|regex:^[0-9_a-z]+$'
        ],
        'type' => [
            'label' => 'Type',
            'type' => 'select',
            'options' => [
                'integer' => 'Integer',
                'string' => 'String',
                'text' => 'Text',
                'smallInteger' => 'Small Integer',
                'bigInteger' => 'Big Integer',
                'date' => 'Date',
                'time' => 'Time',
                'dateTime' => 'Date and Time',
                'timestamp' => 'Timestamp',
                'binary' => 'Binary',
                'boolean' => 'Boolean',
                'decimal' => 'Decimal',
                'double' => 'Double'
            ],
            'validation' => 'required'
        ],
        'length' => [
            'label' => 'Length',
            'type' => 'text',
            'validation' => 'regex:(^[0-9]+$)|(^[0-9]+,[0-9]+$)'
        ],
        'unsigned' => [
            'label' => 'Unsigned',
            'type' => 'checkbox',
            'validation' => false
        ],
        'allow_null' => [
            'label' => 'Allow NULL',
            'type' => 'checkbox',
            'validation' => false
        ],
        'auto_increment' => [
            'label' => 'Auto Increment',
            'type' => 'checkbox',
            'validation' => false
        ],
        'primary_key' => [
            'label' => 'Primary Key',
            'type' => 'checkbox',
            'validation' => false
        ],
        'default' => [
            'label' => 'Default',
            'type' => 'text',
            'validation' => false
        ]

    ];


    protected $tbl_fields = [
        'name' => [
            'type' => 'text',
            'validation' => 'required|regex:^[0-9_a-z]+$'
        ],
        'connection' => [
            'type' => 'select',
            'options' => ['default']
        ]
    ];


    protected $serials;


    public function getTables()
    {
        $schema_file = tbl_project_path('database/db_schema.yml');
        if (!Storage::disk('local')->exists($schema_file)) {
            Artisan::call('builder:read-migrations');
        }

        $db_connection = config('database.default');
        $database_name = config('database.connections.'.$db_connection . '.database');

        $content = Storage::disk('local')->get($schema_file);

        $schema = Yaml::make()->parse($content);

        $tables = [];

        if (isset($schema[$db_connection]) && isset($schema[$db_connection][$database_name])) {
            $tables = $this->normalizeTableData($schema[$db_connection][$database_name]);
        }

        return $schema;
    }

    protected function normalizeTableData($tables)
    {
        $result = [];

        foreach ($tables as $table) {

            $columns = $table['columns'];

            $primaryKey = null;

            foreach($columns as $field => $details)
            {
                if (in_array($column->type, $this->serials) && $column->autoIncrement) {
                    return ' auto_increment primary key';
                }

                 $column_details = [
                    'name' => '',
                    'type' => '',
                     'length' => '',
                     'unsigned' => '',
                     'allow_null' => '',
                     'auto_increment' => '',
                     'primary_key' => '',
                     'default' => ''
                 ];
            }

        }
    }


}
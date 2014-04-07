# Sql - SQL Query Builder

![Build Status](https://img.shields.io/badge/branch-master-blue.svg)
[![Build Status](https://travis-ci.org/crysalead/sql.png?branch=master)](https://travis-ci.org/crysalead/sql)
[![Scrutinizer Coverage Status](https://scrutinizer-ci.com/g/crysalead/sql/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/crysalead/sql/?branch=master)

This library provides query builders independent of any particular database connection library.

## Main Features

* Supports MySQL and PostgreSQL
* Uses the [prefix notation](https://en.wikipedia.org/wiki/Polish_notation) for building queries
* Supports `SELECT`, `UPDATE`, `INSERT` and `DELETE`
* Supports `CREATE TABLE` and `DROP TABLE`

## Community

To ask questions, provide feedback or otherwise communicate with the team, join us on `#chaos` on Freenode.

## Documentation

### The Dialect Classes

The `Dialect` classes are the classes to instanciate to be able to generate SQL query. The available `Dialect` classes are:

* `sql\dialect\MySql`
* `sql\dialect\PostgreSql`
* `sql\Dialect` (the generic one)

Let's start with a `MySql` dialect:

```php
use sql\dialect\MySql;

$dialect = new MySql();
```

Once instanciated, creating query instances are done by using the dialect's `statement()` factory method:

```php
<?php
$select = $dialect->statement('select');
$insert = $dialect->statement('insert');
$update = $dialect->statement('update');
$delete = $dialect->statement('delete');

$createTable = $dialect->statement('create table');
$dropTable = $dialect->statement('drop table');
?>
```

Then once your query is configured, `toString()/__toString()` will generate the corresponding SQL:

```php
$select->from('mytable');
echo $select->toString(); // SELECT * FROM "mytable"
echo (string) $select;    // SELECT * FROM "mytable"
```

Note: when a query is not correctly configured it can throws exceptions. Due to some PHP limitation throwing an exception from `__toString()` generates the following fatal error `Fatal error: Method a::__toString() must not throw an exception`. To have more user-friendly error message, it's recommended to use `toString()` instead.

The generated SQL are independent of any particular database connection library so you can use [PDO](http://php.net/pdo) or the database connection or your choice to execute the query.

### Quoting

By default string values are quoted by the library, however some database connections provide their own [built-in quoting method](http://php.net/manual/en/pdo.quote.php). When available this method should be used instead of the build-in one. To do so, you need to inject the quote handler to your dialect instance like in the following:

```php
use PDO;
use sql\dialect\PostgreSql;

$connection = new PDO($dsn, $user, $password);
$dialect = new PostgreSql(['quoter' => function($string) use ($connection){
    return $connection->quote($string);
}]);
```

Note: to avoid SQL injections, table/field names are escaped and string values are quoted by default.

### SELECT

Example of `SELECT` query:

```php
<?php
$select
    ->distinct()                    // SELECT DISTINCT
    ->fields([
        'id',                       // simple field name
        'fielname' => 'alias'       // field name aliasing
    ])
    ->from('table')                 // FROM
    ->join(                         // JOIN
        'other',                    // other table name
        ['other.table_id' => [      // join condition, (more information on
            ':name' => 'table.id'   // [':name' => ...] in the Prefix Notation section)
        ]],
        'LEFT'                      // type of join
    )
    ->where([                       // WHERE `fieldname` === 'value'
        'fielname' => 'value'
    ])
    ->group('foo')                  // GROUP BY
    ->having([                      // HAVING `fieldname` === 'value'
        'fielname' => 'value'
    ])
    ->order('bar')                  // ORDER BY
    ->limit(10)                     // LIMIT
    ->offset(40)                    // OFFSET
    ->forUpdate()                   // FOR UPDATE
?>
```

#### Prefix Notation (or polish notation)

To be able to write complex SQL queries, the prefix notation has been choosed here instead of relying on an exhaustive API with many methods (e.g. `orWhere`, `andWhere`, `whereNull()`, etc.) which generally ends up to missing methods anyway.

Infix notation is the notation commonly used in arithmetical. It's characterized by the placement of operators between operands (e.g. `3 + 4`). With the prefix notation, the operator is placed to the left of their operands (e.g. `+ 3 4`).

For a developper this notation is pretty intuitive since it's very similar to how functions are defined. `+` is in a way the function name and `3` and `4` are the parameters.

Example or queries using prefix notation:

```php
$select->fields(['*' => [
    ['+' => [1, 2]], 3
]]);
echo $select->toString();            // SELECT 1 + 2 * 3

$select->fields(['*' => [
    [':()' => ['+' => [1, 2]]], 3
]]);
echo $select->toString();            // SELECT (1 + 2) * 3
```

Note: named operators need to be prefixed by a colon `:` (e.g `:or`, `:and`, `:like`, ':in', etc.). However mathematical symbol like `+`, `-`, `<=`, etc. doesn't requires colon.

If more complex than the classic SQL notation this prefix notation has two main advantages:
* is not SQL dedicated and can be used in a higher level of abstraction.
* is simpler to deal with programmatically than parsing/unparsing SQL strings.

#### Formatters

Formatters are used to escapes table/field names & quotes string values out of the box. The available built-in formatters are:

* `':name'`: it escapes a table/field names.
* `':value'`: it quotes string values.
* `':plain'`: it doesn't do anything (Warning: `':plain'` is subject to SQL injection)

Most of queries relies on the following kind of condition: `field = value`, so you can write your select conditions like the following:

```php
$select->from('table')->where([
    'field1' => 'value1',
    'field2' => 'value2'
]);
echo $select->toString();
// SELECT * FROM `table` WHERE `field1` = 'value1' AND `field2` = 'value2'
```

However the prefix notation can leverage this basic syntax to perform some more complex query. For example the `['field' => 'value']` condition can be rewrited as:

```php
$select->from('table')->where([
    'field' => [':value' => 'value']
]);
```

Which can also be rewrited as:
```php
$select->from('table')->where([
    ['=' => [[':name' => 'field'], [':value' => 'value']]]
]);
```

Most of the time the `['field' => 'value']` syntax will be enough to build your conditions from a higer level of abstraction. But if you wan't to make a `field1 = field2` condition where both part must be escaped, the prefix notation can be used to nail it down:

```php
$select->from('table')->where([
    'field1' => [':name' => 'field2']
]);
```

#### Common Operators

Bellow an exhaustive list of common operators which work for both MySQL and PostgreSQL:

* `'='`
* `'<=>'`
* `'<'`
* `'>'`
* `'<='`
* `'>='`
* `'!='`
* `'<>'`
* `'-'`
* `'+'`
* `'*'`
* `'/'`
* `'%'`
* `'>>'`
* `'<<'`
* `':='`
* `'&'`
* `'|'`
* `':mod'`
* `':div'`
* `':like'`
* `':not like'`
* `':is'`
* `':is not'`
* `':distinct'`
* `'~'`
* `':between'`
* `':not between'`
* `':in'`
* `':not in'`
* `':exists'`
* `':not exists'`
* `':all'`
* `':any'`
* `':some'`
* `':as'`
* `':not'`
* `':and'`
* `':or'`
* `':xor'`
* `'()'`
`
It's also possible to use some "free" operators. All used operators which are not present in the list above will be considered as SQL functions. So `:concat`, `:sum`, `:min`, `:max`, etc. will be generated as `FUNCTION(...)`.

#### MySQL Dedicated Operators

* `'#'`
* `':regex'`
* `':rlike'`
* `':sounds li`ke'
* `':union'`
* `':union all`'
* `':minus'`
* `':except'`

#### PostgreSQL Dedicated Operators

* `':regex'`
* `':regexi'`
* `':not regex'`
* `':not regexi'`
* `':similar to'`
* `':not similar to'`
* `':square root'`
* `':cube root'`
* `':fact'`
* `'|/'`
* `'||/'`
* `'!!'`
* `':concat'`
* `':pow'`
* `'#'`
* `'@'`
* `'<@'`
* `'@>'`
* `':union'`
* `':union all'`
* `':except'`
* `':except all'`
* `':intersect'`
* `':intersect all'`

#### Custom Dedicated Operators

It's also possible to create your own operator as well as the handler to build it.

Example:

```php
$dialect = new PostgreSql([
    'builders' => [
        'braces' => function ($operator, $parts) {
            return "{" . array_shift($parts)  ."}";
        }
    ],
    'operators' => [
        '{}' => ['builder' => 'braces']
        // Note: ['format' => '{%s}'] would also be enough here.
    ]
]);

$select = $dialect->statement('select');
$select->fields(['{}' => [1]]); // SELECT {1}
]]);
```

#### Subqueries

To use a subquery inside another query or doing some algebraic operations on queries, you just need to mix them together:

```php
$subquery = $dialect->statement('select')
$subquery->from('table2')->alias('t2');

$select->from('table')->join($subquery);

echo $select->toString();
// SELECT * FROM "table" LEFT JOIN (SELECT * FROM "table2") AS "t2"
```

You can also perform `UNION` query:

```php
$select1 = $dialect->statement('select')->from('table1');
$select2 = $dialect->statement('select')->from('table2');

echo $dialect->conditions([
    ':union' => [$select1, $select2]
]);
// SELECT * FROM `table1` UNION SELECT * FROM `table2`
```

### INSERT

Example of `INSERT` query:

```php
$insert = $dialect->statement('insert');

$insert
    ->into('table')               // INTO
    ->values([                    // (field1, ...) VALUES (value1, ...)"
        'field1' => 'value1',
        'field2' => 'value2'
    ]);
```

The `values()` method allows you to pass an array of key-value pairs where the key is the field name and value the field value.

### UPDATE

Example of `UPDATE` query:

```php
$update = $dialect->statement('update');

$update
    ->table('table')              // TABLE
    ->values([                    // (field1, ...) VALUES (value1, ...)"
        'field1' => 'value1',
        'field2' => 'value2'
    ])
    ->where(['id' => 123]);       // WHERE
```

The `values()` method allows you to pass an array of key-value pairs where the key is the field name and value the field value.

### DELETE

Example of `DELETE` query:

```php
$delete = $dialect->statement('delete');

$delete
    ->from('table')                 // FROM
    ->where(['id' => 123]);         // WHERE
```

### CREATE TABLE

Example of `CREATE TABLE` query:

```php
$createTable = $dialect->statement('create table');
$createTable
    ->table('table')                 // TABLE
    ->columns([])                    // columns definition
    ->meta([])                       // table meta definition
    ->constraints([]);               // constraints definition
```

Bellow an example of a MySQL table creation:

```php
$createTable = $dialect->statement('create table');
$createTable
    ->table('table')
    ->columns([
        'id' => ['type' => 'serial'],
        'table_id' => ['type' => 'integer'],
        'published' => [
            'type' => 'datetime',
            'null' => false,
            'default' => [':plain' => 'CURRENT_TIMESTAMP']
        ],
        'decimal' => [
            'type' => 'float',
            'length' => 10,
            'precision' => 2
        ],
        'integer' => [
            'type' => 'integer',
            'use' => 'numeric',
            'length' => 10,
            'precision' => 2
        ],
        'date' => [
            'type' => 'date',
            'null' => false,
        ],
        'text' => [
            'type' => 'text',
            'null' => false,
        ]
    ])
    ->meta([
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
        'engine' => 'InnoDB'
    ])
    ->constraints([
        [
            'type' => 'check',
            'expr' => [
               'integer' => ['<' => 10]
            ]
        ],
        [
            'type' => 'foreign key',
            'foreignKey' => 'table_id',
            'to' => 'other_table',
            'primaryKey' => 'id',
            'on' => 'DELETE NO ACTION'
        ]
    ]);

echo $this->create->toString();
// CREATE TABLE `table` (
// `id` int NOT NULL AUTO_INCREMENT,
// `table_id` int,
// `published` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
// `decimal` decimal(10,2),
// `integer` numeric(10,2),
// `date` date NOT NULL,
// `text` text NOT NULL,
// CHECK (`integer` < 10),
// FOREIGN KEY (`table_id`) REFERENCES `other_table` (`id`) ON DELETE NO ACTION,
// PRIMARY KEY (`id`))
// DEFAULT CHARSET utf8 COLLATE utf8_unicode_ci ENGINE InnoDB
```

#### Abstract types

Databases uses different naming conventions for types which can be quite missleading. So to be the most generic possible, columns definition can be done using some abstract `'type'` definitions. Out of the box the following types are supported:

* `'id'`: foreign key ID
* `'serial'`: autoincremented serial primary key
* `'string'`: string value
* `'text'`: text value
* `'integer'`: integer value
* `'boolean'`: boolean value
* `'float'`: foat value
* `'decimal'`: decimal value with 2 decimal places
* `'date'`: date value
* `'time'`: time value
* `'datetime'`: datetime value
* `'binary'`: binary value

For example with MySQL the `'serial'` type will generate the following query:

```php
$createTable = $dialect->statement('create table');
$createTable
    ->table('table')
    ->columns([
        'id' =>  ['type' => 'serial']
    ]);

echo $this->create->toString();
// CREATE TABLE `table` (`id` int NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))
```

And PostgreSQL will generate:

```php
$createTable = $dialect->statement('create table');
$createTable
    ->table('table')
    ->columns([
        'id' =>  ['type' => 'serial']
    ]);

echo $this->create->toString();
// CREATE TABLE "table" ("id" serial NOT NULL, PRIMARY KEY ("id"))
```

However it's possible to add your own abstract types. For example to make `'uuid'` to stand for `char(30)` columns, we can write:

```php
$dialect = new MySql();
$dialect->type('uuid', ['use' => 'char', 'length' => 30]);
```

If you don't want to deal with abstract types you directly use `'use'` instead of `'type'` to define a column:

```php
$createTable = $dialect->statement('create table');
$createTable
    ->table('table')
    ->columns([
        'id'   =>  ['type' => 'serial'],
        'data' =>  ['use' => 'blob']
    ]);
```

### DROP TABLE

Example of `DROP TABLE` query:

```php
$dropTable = $dialect->statement('drop table');

$dropTable->table('table');         // TABLE
```

## Testing

The spec suite can be runned with:

```
cd sql
composer install
./bin/kahlan
```

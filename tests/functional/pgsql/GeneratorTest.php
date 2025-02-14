<?php

declare(strict_types=1);

namespace bizley\tests\functional\pgsql;

use bizley\tests\stubs\MigrationControllerStub;
use Throwable;
use yii\base\InvalidRouteException;
use yii\base\NotSupportedException;
use yii\console\Exception as ConsoleException;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\helpers\Json;

/** @group pgsql */
final class GeneratorTest extends \bizley\tests\functional\GeneratorTest
{
    /** @var string */
    public static $schema = 'pgsql';

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithNonStandardColumns(): void
    {
        $this->createTables(
            [
                'non_standard_columns' => [
                    'col_tiny_int' => $this->tinyInteger(),
                    'col_date_time' => $this->dateTime(),
                    'col_float' => $this->float(),
                    'col_timestamp' => $this->timestamp(),
                    'col_json' => $this->json(),
                ]
            ]
        );

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['non_standard_columns']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%non_standard_columns}}\',
            [
                \'col_tiny_int\' => $this->smallInteger(),
                \'col_date_time\' => $this->timestamp(),
                \'col_float\' => $this->double(),
                \'col_timestamp\' => $this->timestamp(),
                \'col_json\' => $this->json(),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateNonGeneralSchemaTable(): void
    {
        $this->createTables(
            [
                'non_gs_columns' => [
                    'id' => $this->primaryKey(),
                    'col_big_int' => $this->bigInteger(),
                    'col_int' => $this->integer(),
                    'col_small_int' => $this->smallInteger(),
                    'col_tiny_int' => $this->tinyInteger(),
                    'col_bin' => $this->binary(),
                    'col_bool' => $this->boolean(),
                    'col_char' => $this->char(),
                    'col_date' => $this->date(),
                    'col_date_time' => $this->dateTime(),
                    'col_decimal' => $this->decimal(),
                    'col_double' => $this->double(),
                    'col_float' => $this->float(),
                    'col_money' => $this->money(),
                    'col_string' => $this->string(),
                    'col_text' => $this->text(),
                    'col_time' => $this->time(),
                    'col_timestamp' => $this->timestamp(),
                    'col_json' => $this->json(),
                ]
            ]
        );

        $this->controller->generalSchema = false;
        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['non_gs_columns']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%non_gs_columns}}\',
            [
                \'id\' => $this->integer()->notNull()->append(\'PRIMARY KEY\'),
                \'col_big_int\' => $this->bigInteger(),
                \'col_int\' => $this->integer(),
                \'col_small_int\' => $this->smallInteger(),
                \'col_tiny_int\' => $this->smallInteger(),
                \'col_bin\' => $this->binary(),
                \'col_bool\' => $this->boolean(),
                \'col_char\' => $this->char(1),
                \'col_date\' => $this->date(),
                \'col_date_time\' => $this->timestamp(0),
                \'col_decimal\' => $this->decimal(10, 0),
                \'col_double\' => $this->double(),
                \'col_float\' => $this->double(),
                \'col_money\' => $this->decimal(19, 4),
                \'col_string\' => $this->string(255),
                \'col_text\' => $this->text(),
                \'col_time\' => $this->time(0),
                \'col_timestamp\' => $this->timestamp(0),
                \'col_json\' => $this->json(),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableButKeepNonDefaultSize(): void
    {
        $this->createTables(
            [
                'non_default_size' => [
                    'col_char' => $this->char(4),
                    'col_decimal' => $this->decimal(10, 2),
                    'col_string' => $this->string(10),
                ]
            ]
        );

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['non_default_size']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%non_default_size}}\',
            [
                \'col_char\' => $this->char(4),
                \'col_decimal\' => $this->decimal(10, 2),
                \'col_string\' => $this->string(10),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateNonGeneralSchemaTableWithBigPrimaryKey(): void
    {
        $this->createTables(['big_primary_key' => ['id' => $this->bigPrimaryKey()]]);

        $this->controller->generalSchema = false;
        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['big_primary_key']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%big_primary_key}}\',
            [
                \'id\' => $this->bigInteger()->notNull()->append(\'PRIMARY KEY\'),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithNonGeneralBigPrimaryKey(): void
    {
        $this->createTables(['big_primary_key' => ['id' => $this->bigInteger()->notNull()->append('PRIMARY KEY')]]);

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['big_primary_key']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%big_primary_key}}\',
            [
                \'id\' => $this->bigPrimaryKey(),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithNonGeneralColumnsDefaultValues(): void
    {
        $this->createTables(
            [
                'non_gs_columns' => [
                    'id' => $this->integer()->notNull()->append('PRIMARY KEY'),
                    'col_char' => $this->char(1),
                    'col_decimal' => $this->decimal(10, 0),
                    'col_money' => $this->decimal(19, 4),
                    'col_string' => $this->string(255),
                ]
            ]
        );

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['non_gs_columns']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%non_gs_columns}}\',
            [
                \'id\' => $this->primaryKey(),
                \'col_char\' => $this->char(),
                \'col_decimal\' => $this->decimal(),
                \'col_money\' => $this->money(),
                \'col_string\' => $this->string(),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithColumnsWithAppendixes(): void
    {
        $this->createTables(
            [
                'appendixes' => [
                    'col1' => $this->integer()->defaultValue(2),
                    'col2' => $this->integer()->unsigned(),
                    'col3' => $this->string()->defaultValue('abc'),
                    'col4' => $this->integer()->comment('comment'),
                    'col5' => $this->integer()->notNull(),
                    'col6' => $this->integer()->null(),
                    'col7' => $this->timestamp()->defaultExpression('NOW()'),
                    'col8' => $this->json()->defaultValue(Json::encode(['a' => 'b'])),
                ]
            ]
        );

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['appendixes']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%appendixes}}\',
            [
                \'col1\' => $this->integer()->defaultValue(\'2\'),
                \'col2\' => $this->integer(),
                \'col3\' => $this->string()->defaultValue(\'abc\'),
                \'col4\' => $this->integer()->comment(\'comment\'),
                \'col5\' => $this->integer()->notNull(),
                \'col6\' => $this->integer(),
                \'col7\' => $this->timestamp()->defaultExpression(\'now()\'),
                \'col8\' => $this->json()->defaultValue(\'{"a":"b"}\'),
            ],
            $tableOptions
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithCompositePrimaryKey(): void
    {
        $this->createTables(
            [
                'composite_primary_key' => [
                    'col1' => $this->integer(),
                    'col2' => $this->integer(),
                ]
            ]
        );
        $this->getDb()->createCommand()->addPrimaryKey('PK', 'composite_primary_key', ['col1', 'col2'])->execute();

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['composite_primary_key']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%composite_primary_key}}\',
            [
                \'col1\' => $this->integer()->notNull(),
                \'col2\' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey(\'PK\', \'{{%composite_primary_key}}\', [\'col1\', \'col2\']);
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithUniqueColumn(): void
    {
        $this->createTables(['unique' => ['col' => $this->integer()->unique()]]);

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['unique']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%unique}}\',
            [
                \'col\' => $this->integer(),
            ],
            $tableOptions
        );

        $this->createIndex(\'unique_col_key\', \'{{%unique}}\', [\'col\'], true);
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTableWithForeignKey(): void
    {
        try {
            $this->getDb()->createCommand()->dropForeignKey('fk-table12', 'table12')->execute();
        } catch (Throwable $exception) {
        }
        $this->createTables(
            [
                'table11' => ['id' => $this->primaryKey(11)],
                'table12' => ['col' => $this->integer(11)]
            ]
        );
        $this->getDb()->createCommand()->addForeignKey('fk-table12', 'table12', ['col'], 'table11', ['id'])->execute();

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['table12']));
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%table12}}\',
            [
                \'col\' => $this->integer(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            \'fk-table12\',
            \'{{%table12}}\',
            [\'col\'],
            \'{{%table11}}\',
            [\'id\'],
            \'NO ACTION\',
            \'NO ACTION\'
        );
',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaCrossReferredTables(): void
    {
        try {
            $this->getDb()->createCommand()->dropForeignKey('fk-table21', 'table21')->execute();
            $this->getDb()->createCommand()->dropForeignKey('fk-table22', 'table22')->execute();
        } catch (Throwable $exception) {
        }

        $this->createTables(
            [
                'table21' => [
                    'id1' => $this->primaryKey(),
                    'fk1' => $this->integer(),
                ],
                'table22' => [
                    'id2' => $this->primaryKey(),
                    'fk2' => $this->integer(),
                ]
            ]
        );
        $this->getDb()->createCommand()->addForeignKey(
            'fk-table21',
            'table21',
            ['fk1'],
            'table22',
            ['id2'],
            'CASCADE',
            'CASCADE'
        )->execute();
        $this->getDb()->createCommand()->addForeignKey(
            'fk-table22',
            'table22',
            ['fk2'],
            'table21',
            ['id1'],
            'CASCADE',
            'CASCADE'
        )->execute();

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['table21,table22']));
        self::assertStringContainsString(
            'public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === \'mysql\') {
            $tableOptions = \'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB\';
        }

        $this->createTable(
            \'{{%table22}}\',
            [
                \'id2\' => $this->primaryKey(),
                \'fk2\' => $this->integer(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable(\'{{%table22}}\');
    }',
            MigrationControllerStub::$content
        );
        self::assertStringContainsString(
            'public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === \'mysql\') {
            $tableOptions = \'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB\';
        }

        $this->createTable(
            \'{{%table21}}\',
            [
                \'id1\' => $this->primaryKey(),
                \'fk1\' => $this->integer(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            \'fk-table21\',
            \'{{%table21}}\',
            [\'fk1\'],
            \'{{%table22}}\',
            [\'id2\'],
            \'CASCADE\',
            \'CASCADE\'
        );
    }

    public function safeDown()
    {
        $this->dropTable(\'{{%table21}}\');
    }',
            MigrationControllerStub::$content
        );
        self::assertStringContainsString(
            'public function safeUp()
    {
        $this->addForeignKey(
            \'fk-table22\',
            \'{{%table22}}\',
            [\'fk2\'],
            \'{{%table21}}\',
            [\'id1\'],
            \'CASCADE\',
            \'CASCADE\'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(\'fk-table22\', \'{{%table22}}\');
    }',
            MigrationControllerStub::$content
        );
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateGeneralSchemaTablesInProperOrder(): void
    {
        try {
            $this->getDb()->createCommand()->dropForeignKey('fk-table31', 'table31')->execute();
            $this->getDb()->createCommand()->dropForeignKey('fk-table32', 'table32')->execute();
        } catch (Throwable $exception) {
        }

        $this->createTables(
            [
                'table31' => [
                    'id1' => $this->primaryKey(),
                    'fk1' => $this->integer(),
                ],
                'table32' => [
                    'id2' => $this->primaryKey(),
                    'fk2' => $this->integer(),
                ],
                'table33' => ['id3' => $this->primaryKey()],
            ]
        );
        $this->getDb()->createCommand()->addForeignKey(
            'fk-table31',
            'table31',
            ['fk1'],
            'table32',
            ['id2'],
            'CASCADE',
            'CASCADE'
        )->execute();
        $this->getDb()->createCommand()->addForeignKey(
            'fk-table32',
            'table32',
            ['fk2'],
            'table33',
            ['id3'],
            'CASCADE',
            'CASCADE'
        )->execute();

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['table31,table32,table33']));
        preg_match_all('/create_table_table(\d{2})/', MigrationControllerStub::$content, $matches);
        self::assertSame(['33', '32', '31'], $matches[1]);
    }

    /**
     * @test
     * @throws ConsoleException
     * @throws Exception
     * @throws InvalidRouteException
     * @throws NotSupportedException
     * @throws \yii\base\Exception
     */
    public function shouldGenerateTablesWithSchema(): void
    {
        $this->createSchema('schema1');

        $this->createTables([
            'schema1.table' => ['col' => $this->integer()]
        ]);

        self::assertEquals(ExitCode::OK, $this->controller->runAction('create', ['schema1.table']));

        self::assertStringContainsString(
            '
 > Generating migration for creating table \'schema1.table\' ...DONE!
 > Saved as \'',
            MigrationControllerStub::$stdout
        );
        self::assertStringContainsString(
            '_create_table_schema1_table.php\'

 Generated 1 file
 (!) Remember to verify files before applying migration.
',
            MigrationControllerStub::$stdout
        );

        self::assertStringContainsString(
            '_create_table_schema1_table extends Migration',
            MigrationControllerStub::$content
        );
        self::assertStringContainsString(
            '
        $this->createTable(
            \'{{%schema1.table}}\',
            [
                \'col\' => $this->integer(),
            ],
            $tableOptions
        );',
            MigrationControllerStub::$content
        );

        $this->getDb()->createCommand()->dropTable('schema1.table')->execute();
    }
}

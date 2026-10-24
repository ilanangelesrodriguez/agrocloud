<?php

namespace Tests\Unit;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SunatMigrationTest extends TestCase
{
    public function test_adds_nullable_verification_without_changing_existing_records(): void
    {
        $previous = Facade::getFacadeApplication();
        $capsule = new Manager;
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $db = $capsule->getConnection();
        $schema = $db->getSchemaBuilder();
        $container = $capsule->getContainer();
        $container->bind('db.schema', fn () => $schema);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        try {
            foreach (['empresa', 'productor', 'solicitudes_acceso'] as $table) {
                $schema->create($table, function ($t) { $t->id(); $t->string('ruc'); $t->boolean('estado'); });
                $db->table($table)->insert(['ruc' => '20330791412', 'estado' => false]);
            }
            $migration = require __DIR__.'/../../database/migrations/2026_10_24_130000_add_sunat_verification.php';
            $migration->up();
            foreach (['empresa', 'productor', 'solicitudes_acceso'] as $table) {
                $row = $db->table($table)->first();
                $this->assertSame('20330791412', $row->ruc);
                $this->assertSame(0, $row->estado);
                $this->assertNull($row->sunat_verificacion);
                $db->table($table)->update(['sunat_verificacion' => json_encode(['resultado' => 'verificado', 'estado' => 'ACTIVO'])]);
                $this->assertSame(0, $db->table($table)->first()->estado);
            }
            $migration->down();
            foreach (['empresa', 'productor', 'solicitudes_acceso'] as $table) {
                $this->assertSame(1, $db->table($table)->count());
                $this->assertFalse($schema->hasColumn($table, 'sunat_verificacion'));
            }
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($previous);
        }
    }
}

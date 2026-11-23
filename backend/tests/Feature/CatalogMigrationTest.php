<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogMigrationTest extends TestCase
{
    public function test_catalog_migrations_upgrade_and_roll_back_over_a_populated_scaffold(): void
    {
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => glob(database_path('migrations/0001_*.php')),
        ])->assertSuccessful();

        $legacyMember = User::factory()->create();
        $session = [
            'id' => 'legacy-session',
            'user_id' => $legacyMember->id,
            'payload' => 'legacy-payload',
            'last_activity' => 1,
        ];
        DB::table('sessions')->insert($session);

        $this->artisan('migrate')->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $legacyMember->id,
            'password' => $legacyMember->password,
            'microsoft_oid' => null,
            'microsoft_tid' => null,
            'is_admin' => false,
        ]);
        $this->assertDatabaseHas('sessions', $session);

        $member = User::factory()->microsoft()->create()->refresh();

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'password' => null,
            'microsoft_oid' => $member->microsoft_oid,
            'microsoft_tid' => $member->microsoft_tid,
            'is_admin' => false,
        ]);
        $this->assertFalse($member->is_admin);

        $this->artisan('migrate:rollback')->assertSuccessful();

        $this->assertFalse(Schema::hasTable('items'));
        $this->assertFalse(Schema::hasTable('categories'));
        $this->assertFalse(Schema::hasTable('locations'));
        $this->assertFalse(Schema::hasColumn('users', 'microsoft_oid'));
        $this->assertDatabaseHas('users', ['id' => $legacyMember->id, 'password' => $legacyMember->password]);
        $this->assertDatabaseHas('users', ['id' => $member->id, 'password' => '']);
        $this->assertDatabaseHas('sessions', $session);

        $this->artisan('migrate')->assertSuccessful();
        $this->assertTrue(Schema::hasTable('items'));
    }
}

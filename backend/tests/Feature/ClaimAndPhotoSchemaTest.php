<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ClaimAndPhotoSchemaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_claims_default_to_pending_and_reject_a_duplicate_member_item_pair(): void
    {
        $item = $this->createItem();
        $claim = $this->claim($item, User::factory()->microsoft()->create()->id);
        DB::table('claims')->insert($claim);

        DB::table('claims')->insert($this->claim($item, User::factory()->microsoft()->create()->id));

        $this->assertDatabaseHas('claims', $claim + ['status' => 'pending', 'response_note' => null]);
        $this->assertDatabaseCount('claims', 2);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('claims')->insert($claim);
    }

    public function test_claim_rejects_a_missing_item(): void
    {
        $claim = $this->claim(0, User::factory()->microsoft()->create()->id);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('FOREIGN KEY constraint failed');
        DB::table('claims')->insert($claim);
    }

    #[TestWith([0, 'FOREIGN KEY constraint failed'])]
    #[TestWith([null, 'NOT NULL constraint failed: claims.claimant_id'])]
    public function test_claim_requires_an_existing_member(?int $claimant, string $message): void
    {
        $claim = $this->claim($this->createItem(), $claimant);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage($message);
        DB::table('claims')->insert($claim);
    }

    public function test_photo_rejects_a_missing_item(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('FOREIGN KEY constraint failed');
        DB::table('item_photos')->insert($this->photo(0));
    }

    #[TestWith(['path', 'item-photos/another.jpg'])]
    #[TestWith(['sort_order', 1])]
    public function test_photo_rejects_duplicate_item_slots_and_storage_keys(string $column, string|int $value): void
    {
        $photo = $this->photo($this->createItem());
        DB::table('item_photos')->insert($photo);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('item_photos')->insert([$column => $value] + $photo);
    }

    public function test_claim_prevents_deletion_of_its_claimant(): void
    {
        $claimant = User::factory()->microsoft()->create();
        DB::table('claims')->insert($this->claim($this->createItem(), $claimant->id));

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('FOREIGN KEY constraint failed');
        $claimant->delete();
    }

    public function test_claim_prevents_deletion_of_its_item(): void
    {
        $item = $this->createItem();
        DB::table('claims')->insert($this->claim($item, User::factory()->microsoft()->create()->id));

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('FOREIGN KEY constraint failed');
        DB::table('items')->where('id', $item)->delete();
    }

    public function test_photo_prevents_deletion_of_its_item(): void
    {
        $item = $this->createItem();
        DB::table('item_photos')->insert($this->photo($item));

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('FOREIGN KEY constraint failed');
        DB::table('items')->where('id', $item)->delete();
    }

    private function createItem(): int
    {
        $poster = User::factory()->microsoft()->create();
        $category = DB::table('categories')->insertGetId(['name' => 'Bags', 'slug' => 'bags']);
        $location = DB::table('locations')->insertGetId(['name' => 'Library', 'slug' => 'library']);

        return DB::table('items')->insertGetId([
            'user_id' => $poster->id,
            'category_id' => $category,
            'location_id' => $location,
            'type' => 'found',
            'name' => 'Backpack',
            'description' => 'A blue backpack',
            'date_seen' => '2025-10-16',
            'holding_location' => 'Library staff desk',
        ]);
    }

    /** @return array{item_id: int, claimant_id: ?int, claimant_name: string, proof: string} */
    private function claim(int $item, ?int $claimant): array
    {
        return [
            'item_id' => $item,
            'claimant_id' => $claimant,
            'claimant_name' => 'Campus Member',
            'proof' => 'The notebook inside has a green cover.',
        ];
    }

    /** @return array{item_id: int, disk: string, path: string, mime_type: string, size_bytes: int, sort_order: int} */
    private function photo(int $item): array
    {
        return [
            'item_id' => $item,
            'disk' => 'local',
            'path' => 'item-photos/backpack.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
            'sort_order' => 0,
        ];
    }
}

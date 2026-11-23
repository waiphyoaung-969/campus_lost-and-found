<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CatalogModelsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_search_includes_open_items_at_the_creation_cutoff(): void
    {
        $this->travelTo('2026-04-01 12:00:00');

        $atCutoff = Item::factory()->create([
            'created_at' => '2026-01-31 12:00:00',
            'date_seen' => '2026-01-30',
        ]);
        $sameCatalog = Item::factory()->recycle([$atCutoff->user, $atCutoff->category, $atCutoff->location]);
        $recent = $sameCatalog->create(['date_seen' => '2026-01-15']);
        $sameCatalog->create([
            'created_at' => '2026-01-31 11:59:59',
            'date_seen' => '2026-01-31',
        ]);
        $sameCatalog->create(['status' => 'resolved']);
        $sameCatalog->create(['status' => 'closed']);

        $visibleIds = Item::query()->forPublicSearch()->orderBy('id')->pluck('id')->all();

        $this->assertSame([$atCutoff->id, $recent->id], $visibleIds);
    }

    public function test_factories_create_a_report_with_navigable_relationships(): void
    {
        $claim = Claim::factory()->create();
        $item = $claim->item;
        $photo = ItemPhoto::factory()->for($item)->create();

        $item->load('user', 'category', 'location', 'claims', 'photos');

        $this->assertSame('found', $item->type);
        $this->assertNotNull($item->holding_location);
        $this->assertSame($item->id, $item->user->items()->sole()->id);
        $this->assertSame($item->id, $item->category->items()->sole()->id);
        $this->assertSame($item->id, $item->location->items()->sole()->id);
        $this->assertNotSame($item->user->id, $claim->claimant->id);
        $this->assertSame($claim->id, $claim->claimant->claims()->sole()->id);
        $this->assertSame($claim->id, $item->claims->sole()->id);
        $this->assertSame($photo->id, $item->photos->sole()->id);
        $this->assertSame($item->id, $photo->item->id);
        $this->assertInstanceOf(Carbon::class, $item->date_seen);
        $this->assertArrayNotHasKey('proof', $claim->toArray());
        $this->assertArrayNotHasKey('path', $photo->toArray());
        $this->assertArrayNotHasKey('holding_location', $item->toArray());
    }

    public function test_user_mass_assignment_cannot_replace_microsoft_identity_or_grant_admin(): void
    {
        $member = User::factory()->microsoft()->make(['is_admin' => false]);
        $originalOid = $member->microsoft_oid;
        $originalTid = $member->microsoft_tid;

        $member->fill([
            'is_admin' => true,
            'microsoft_oid' => fake()->uuid(),
            'microsoft_tid' => fake()->uuid(),
        ]);

        $this->assertFalse($member->is_admin);
        $this->assertSame($originalOid, $member->microsoft_oid);
        $this->assertSame($originalTid, $member->microsoft_tid);
        $this->assertArrayNotHasKey('microsoft_oid', $member->toArray());
        $this->assertArrayNotHasKey('microsoft_tid', $member->toArray());
        $this->assertArrayNotHasKey('password', $member->toArray());
    }
}

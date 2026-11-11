<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\Claim;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $alex = User::firstOrCreate(['email' => 'student@campus.demo'], ['name' => 'Alex Morgan', 'password' => 'demo-password']);
        $maya = User::firstOrCreate(['email' => 'member@campus.demo'], ['name' => 'Maya Chen', 'password' => 'demo-password']);
        User::firstOrCreate(['email' => 'admin@campus.demo'], ['name' => 'Campus Admin', 'password' => 'demo-password', 'is_admin' => true]);
        $samples = [
            ['found','Blue insulated bottle','Other','Matte navy bottle with a silver cap.','2026-11-09','Science block, lecture hall 2','Science block reception'],
            ['lost','Student ID card','ID & cards','Student card in a clear sleeve.','2026-11-10','Central library, ground floor',null],
            ['found','Wireless earbuds case','Electronics','Small white charging case.','2026-11-08','Cafeteria patio','Security office, main entrance'],
            ['lost','Green canvas tote','Bags & accessories','Green canvas bag with a notebook inside.','2026-11-06','East campus bus stop',null],
            ['found','Silver keyring','Keys','Three keys on a round metal ring.','2026-11-10','Gym changing area','Gym front desk'],
            ['found','Calculus notebook','Books & stationery','Blue ruled notebook with lecture notes.','2026-11-07','Engineering building, room 104','Engineering reception'],
            ['lost','Black umbrella','Other','Compact folding umbrella.','2026-11-05','Student union lobby',null],
            ['found','Red zip-up hoodie','Clothing','Medium red hoodie left in the study lounge.','2026-11-09','Arts building study lounge','Arts building front desk'],
        ];
        foreach ($samples as [$type,$name,$category,$description,$date,$location,$holding]) {
            Item::firstOrCreate(['name'=>$name,'user_id'=>$alex->id], ['type'=>$type,'category'=>$category,'description'=>$description,'date_seen'=>$date,'location'=>$location,'holding_location'=>$holding,'status'=>'open']);
        }

        $mayaItems = [
            ['lost','Navy insulated bottle','Other','Matte navy bottle with a small moon sticker underneath the base.','2026-11-10','Science block, lecture hall 2',null],
            ['found','Green notebook','Books & stationery','Green spiral notebook with handwritten lecture notes.','2026-11-09','West library, quiet study area','West library service desk'],
        ];
        foreach ($mayaItems as [$type,$name,$category,$description,$date,$location,$holding]) {
            Item::firstOrCreate(['name'=>$name,'user_id'=>$maya->id], ['type'=>$type,'category'=>$category,'description'=>$description,'date_seen'=>$date,'location'=>$location,'holding_location'=>$holding,'status'=>'open']);
        }

        $foundBottle = Item::where('user_id',$alex->id)->where('name','Blue insulated bottle')->first();
        if ($foundBottle) {
            $demoClaim = Claim::firstOrCreate(['item_id'=>$foundBottle->id,'claimant_id'=>$maya->id], [
                'claimant_name'=>'Maya Chen',
                'student_id'=>'2024-1024',
                'proof'=>'There is a small moon sticker underneath the base, and a tiny dent beside the lid.',
                'status'=>'pending',
            ]);
            $demoClaim->update(array_filter([
                'claimant_name'=>$demoClaim->claimant_name ?: 'Maya Chen',
                'student_id'=>$demoClaim->student_id ?: '2024-1024',
            ]));
        }
    }
}

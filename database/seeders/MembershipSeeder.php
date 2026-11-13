<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        $plan = MembershipPlan::query()
            ->whereHas('gym.owner', fn (Builder $query): Builder => $query->where('name', 'Proprietar Fitline Demo'))
            ->whereHas('gym', fn (Builder $query): Builder => $query->where('is_active', true))
            ->where('name', 'Full Time — 1 lună')
            ->where('is_active', true)
            ->orderBy('id')->first();

        if ($plan === null) {
            return;
        }

        $member = User::firstOrCreate(
            ['email' => 'member@fitline.example'],
            ['name' => 'Membru Fitline Demo', 'password' => Hash::make('password')],
        );

        if (! $member->memberships()->where('membership_plan_id', $plan->id)->where('is_demo', true)->exists()) {
            Membership::factory()
                ->for($member)
                ->forPlan($plan, CarbonImmutable::now('UTC'))
                ->create();
        }
    }
}

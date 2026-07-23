<?php

namespace Tests\Feature;

use App\Models\MaterialSubLotTitle;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaterialTypeApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_unauthorized_response(): void
    {
        $this->getJson('/api/material-types')->assertUnauthorized();
    }

    public function test_authenticated_user_without_material_view_permission_is_forbidden(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/material-types')->assertForbidden();
    }

    public function test_unverified_user_with_permission_is_forbidden(): void
    {
        $user = User::factory()->unverified()->create();
        $this->grantMaterialViewPermission($user);
        Sanctum::actingAs($user);

        $this->getJson('/api/material-types')->assertForbidden();
    }

    public function test_authorized_responses_preserve_existing_json_shapes(): void
    {
        $user = User::factory()->create();
        $this->grantMaterialViewPermission($user);
        Sanctum::actingAs($user);

        MaterialSubLotTitle::query()->create([
            'material_type' => 'ALARM',
            'title' => 'Synthetic title',
            'sort_order' => 1,
        ]);

        $this->getJson('/api/material-types')
            ->assertOk()
            ->assertExactJson(config('sublot_fields'));

        $this->getJson('/api/material-types/ALARM/sub-lot-fields')
            ->assertOk()
            ->assertExactJson(config('sublot_fields.ALARM'));

        $this->getJson('/api/material-types/ALARM/sub-lot-titles')
            ->assertOk()
            ->assertExactJson([
                ['title' => 'Synthetic title', 'sort_order' => 1],
            ]);
    }

    public function test_same_origin_inertia_session_can_call_the_protected_api(): void
    {
        config()->set('sanctum.stateful', ['localhost']);

        $user = User::factory()->create();
        $this->grantMaterialViewPermission($user);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->withHeaders([
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/material-monitoring-checksheets/create',
        ])->getJson('/api/material-types/ALARM/sub-lot-fields')
            ->assertOk()
            ->assertExactJson(config('sublot_fields.ALARM'));
    }

    private function grantMaterialViewPermission(User $user): void
    {
        $permission = UserPermission::query()->create([
            'name' => 'View material test records',
            'slug' => 'material.view',
            'description' => 'Synthetic test permission',
            'module' => 'material',
            'action' => 'view',
        ]);

        $user->role->grantPermission($permission);
        $user->unsetRelation('role');
    }
}

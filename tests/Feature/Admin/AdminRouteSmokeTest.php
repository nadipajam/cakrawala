<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_create_page_is_reachable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.users.create', [], false));

        $response->assertOk();
        $response->assertViewIs('admin.users.create');
    }

    public function test_manager_cannot_access_admin_users_create_page(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->get(route('admin.users.create', [], false));

        $response->assertRedirect(route('admin.dashboard', [], false));
    }

    public function test_admin_primary_pages_do_not_return_404(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $routeNames = [
            'admin.dashboard',
            'admin.contact-messages.index',
            'admin.users.index',
            'admin.users.create',
            'admin.reports.index',
            'admin.passengers.index',
            'admin.flights.index',
            'admin.bookings.index',
            'admin.payments.index',
            'admin.tickets.index',
            'admin.profile.index',
            'admin.addons.index',
            'admin.change-requests.index',
        ];

        foreach ($routeNames as $name) {
            $response = $this->actingAs($admin)->get(route($name, [], false));
            $this->assertNotSame(404, $response->getStatusCode(), "Route {$name} returned 404.");
        }
    }

    public function test_admin_can_create_user_from_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.users.store', [], false), [
            'name' => 'Staff QA',
            'email' => 'staff.qa@example.com',
            'phone' => '081200000001',
            'role' => 'staff',
            'employee_id' => 'EMP-QA-001',
            'department' => 'Operations',
            'job_title' => 'Staff Ops',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index', [], false));
        $this->assertDatabaseHas('users', [
            'email' => 'staff.qa@example.com',
            'role' => 'staff',
        ]);
    }

    public function test_admin_user_management_action_links_do_not_break(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $managedUser = User::factory()->create(['role' => 'staff']);

        $paths = [
            route('admin.users.create', [], false),
            route('admin.users.show', $managedUser, false),
            route('admin.users.edit', $managedUser, false),
        ];

        foreach ($paths as $path) {
            $response = $this->actingAs($admin)->get($path);
            $this->assertNotSame(404, $response->getStatusCode(), "Path {$path} returned 404.");
        }
    }
}

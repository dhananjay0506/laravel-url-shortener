<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $adminA;
    private User $memberA;
    private User $memberB;
    private Company $companyA;
    private Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::create(['name' => 'Company Alpha']);
        $this->companyB = Company::create(['name' => 'Company Beta']);

        $this->superAdmin = User::create([
            'name' => 'Root Admin',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
            'company_id' => null,
        ]);

        $this->adminA = User::create([
            'name' => 'Admin Alpha',
            'email' => 'admin@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
            'company_id' => $this->companyA->id,
        ]);

        $this->memberA = User::create([
            'name' => 'Member Alpha',
            'email' => 'member@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'company_id' => $this->companyA->id,
        ]);

        $this->memberB = User::create([
            'name' => 'Member Beta',
            'email' => 'member@beta.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'company_id' => $this->companyB->id,
        ]);
    }

    public function test_superadmin_cannot_create_short_urls(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('urls.store'), [
            'original_url' => 'https://laravel.com',
        ]);

        $response->assertRedirect(route('urls.index'));
        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_admin_and_member_can_create_short_urls(): void
    {
        $this->actingAs($this->adminA)->post(route('urls.store'), [
            'original_url' => 'https://laravel.com',
        ]);

        $this->actingAs($this->memberA)->post(route('urls.store'), [
            'original_url' => 'https://php.net',
        ]);

        $this->assertDatabaseCount('short_urls', 2);
    }

    public function test_admin_can_only_see_company_urls(): void
    {
        ShortUrl::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->adminA->id,
            'original_url' => 'https://laravel.com',
            'short_code' => 'alpha1',
        ]);

        ShortUrl::create([
            'company_id' => $this->companyB->id,
            'user_id' => $this->memberB->id,
            'original_url' => 'https://google.com',
            'short_code' => 'beta1',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('urls.index'));
        $response->assertSee('alpha1');
        $response->assertDontSee('beta1');
    }

    public function test_member_can_only_see_their_own_urls(): void
    {
        ShortUrl::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->adminA->id,
            'original_url' => 'https://laravel.com',
            'short_code' => 'adminurl',
        ]);

        ShortUrl::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->memberA->id,
            'original_url' => 'https://php.net',
            'short_code' => 'memberurl',
        ]);

        $response = $this->actingAs($this->memberA)->get(route('urls.index'));
        $response->assertSee('memberurl');
        $response->assertDontSee('adminurl');
    }

    public function test_short_urls_are_publicly_resolvable_and_redirect(): void
    {
        $url = ShortUrl::create([
            'company_id' => $this->companyA->id,
            'user_id' => $this->memberA->id,
            'original_url' => 'https://example.com/target',
            'short_code' => 'redir1',
        ]);

        $response = $this->get('/' . $url->short_code);
        $response->assertRedirect('https://example.com/target');

        $this->assertDatabaseHas('short_urls', [
            'id' => $url->id,
            'clicks' => 1,
        ]);
    }
}
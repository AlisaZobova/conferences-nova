<?php

namespace Tests\Unit;

use App\Models\Conference;
use App\Models\User;
use App\Nova\Actions\ExportListeners;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use JoshGaber\NovaUnit\Actions\NovaActionTest;
use Tests\TestCase;

class ConferenceListenersExportTest extends TestCase
{
    use RefreshDatabase;
    use NovaActionTest;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public function test_successful_export_file_created()
    {
        Storage::fake('export');

        $conference = Conference::factory()->create();

        $response = $this->actingAs($this->getAdmin())
            ->json(
                'POST',
                'nova-api/conferences/action?action=export-listeners&pivotAction=false&filters=W10%3D',
                ['resources' => $conference->id]
            );
        $response->assertStatus(200);

        Storage::disk('export')->assertExists($response->original['name']);
    }

    public function test_successful_export_file_download()
    {
        Storage::fake('export');

        $action = $this->novaAction(ExportListeners::class);
        $models = Conference::factory()->create();
        $fields = [];
        $response = $action->handle($fields, $models);
        $response->assertDownload();
    }

    public function test_fail_export_no_auth()
    {
        $response = $this
            ->json(
                'POST',
                'nova-api/conferences/action?action=export-listeners&pivotAction=false&filters=W10%3D',
                ['resources' => 'all']
            );
        $response->assertStatus(401);
    }

    public function test_fail_export_no_admin()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->json(
                'POST',
                'nova-api/conferences/action?action=export-listeners&pivotAction=false&filters=W10%3D',
                ['resources' => 'all']
            );

        $response->assertStatus(403);
    }

    public function getAdmin()
    {
        return User::whereHas(
            'roles', function ($q) {
            $q->where('name', 'Admin');
        }
        )->first();
    }
}

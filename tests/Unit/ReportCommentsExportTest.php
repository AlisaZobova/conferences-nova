<?php

namespace Tests\Unit;

use App\Models\Report;
use App\Models\User;
use App\Nova\Actions\ExportComments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use JoshGaber\NovaUnit\Actions\NovaActionTest;
use Tests\TestCase;

class ReportCommentsExportTest extends TestCase
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

        $report = Report::factory()->create();

        $response = $this->actingAs($this->getAdmin())
            ->json(
                'POST',
                'nova-api/reports/action?action=export-comments&pivotAction=false&filters=W10%3D',
                ['resources' => $report->id]
            );
        $response->assertStatus(200);

        Storage::disk('export')->assertExists($response->original['name']);
    }

    public function test_successful_export_file_download()
    {
        Storage::fake('export');

        $action = $this->novaAction(ExportComments::class);
        $models = Report::factory()->create();
        $fields = [];
        $response = $action->handle($fields, $models);
        $response->assertDownload();
    }

    public function test_fail_export_no_auth()
    {
        $response = $this
            ->json(
                'POST',
                'nova-api/reports/action?action=export-comments&pivotAction=false&filters=W10%3D',
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
                'nova-api/reports/action?action=export-comments&pivotAction=false&filters=W10%3D',
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

<?php

namespace Tests\Feature;

use App\Models\AnnealingCheck;
use App\Models\ApprovalNotification;
use App\Models\MagnetismBatch;
use App\Models\MagnetismCheckpoint;
use App\Models\MagnetismChecksheet;
use App\Models\MaterialPart;
use App\Models\TempRecord;
use App\Models\TorqueReading;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetSample;
use Database\Seeders\MirsTestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MirsTestingSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_only_synthetic_related_records(): void
    {
        $this->seed(MirsTestingSeeder::class);

        $this->assertSame(3, User::count());
        $this->assertSame(1, User::where('status', 'active')->count());
        $this->assertSame(1, User::where('status', 'inactive')->count());
        $this->assertSame(1, User::where('status', 'suspended')->count());
        $this->assertSame(3, User::where('email', 'like', '%@example.test')->count());
        $this->assertSame(1, AnnealingCheck::where('status', 'pending')->count());
        $this->assertSame(1, TempRecord::where('status', 'pending')->count());
        $this->assertSame(1, TorqueRecord::where('status', 'pending')->count());
        $this->assertSame(1, TorqueReading::count());
        $this->assertSame(1, MagnetismChecksheet::count());
        $this->assertSame(1, MagnetismBatch::count());
        $this->assertSame(1, MagnetismCheckpoint::count());
        $this->assertSame(1, MaterialPart::count());
        $this->assertSame(1, WeldingChecksheet::where('status', 'pending')->count());
        $this->assertSame(1, WeldingChecksheetSample::count());
        $this->assertSame(1, ApprovalNotification::where('status', 'pending')->count());
    }

    public function test_testing_seeder_does_not_reference_production_seeders(): void
    {
        $source = file_get_contents((new \ReflectionClass(MirsTestingSeeder::class))->getFileName());

        $this->assertIsString($source);
        $this->assertStringNotContainsString('DatabaseSeeder::class', $source);
        $this->assertStringNotContainsString('AdminUserSeeder::class', $source);
        $this->assertStringNotContainsString('UserManagementSeeder::class', $source);
    }
}

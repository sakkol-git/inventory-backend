<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\LabLocation;
use App\Modules\Inventory\Enums\SampleStatus;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Requests\Sample\StorePlantSampleRequest;
use App\Modules\Inventory\Resources\PlantSampleResource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class PlantSamplePayloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('student');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plant_varieties', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('plant_species_id')->nullable();
            $table->string('name');
            $table->string('variety_code');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plant_samples', function (Blueprint $table): void {
            $table->id();
            $table->string('sample_name');
            $table->string('sample_code');
            $table->foreignId('plant_variety_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('department')->nullable();
            $table->string('origin_location')->nullable();
            $table->date('brought_at')->nullable();
            $table->enum('lab_location', ['lab_a', 'lab_b', 'lab_c'])->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->integer('quantity')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('image_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plant_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plant_sample_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->string('status')->default('available');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_store_request_maps_nested_relationship_ids_to_foreign_keys(): void
    {
        $request = StorePlantSampleRequest::create('/', 'POST', [
            'sample_name' => 'Alpha Sample',
            'sample_code' => 'S-001',
            'status' => SampleStatus::ACTIVE->value,
            'relationships' => [
                'variety' => ['id' => 12],
                'contributor' => ['id' => 34],
            ],
        ]);

        $method = new ReflectionMethod(StorePlantSampleRequest::class, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertSame(12, $request->input('plant_variety_id'));
        $this->assertSame(34, $request->input('user_id'));
    }

    public function test_sample_model_can_mass_assign_relationship_foreign_keys(): void
    {
        $sample = new PlantSample();
        $sample->fill([
            'plant_variety_id' => 12,
            'user_id' => 34,
            'sample_name' => 'Alpha Sample',
            'sample_code' => 'S-001',
            'status' => SampleStatus::ACTIVE,
            'lab_location' => LabLocation::LAB_B,
        ]);

        $this->assertSame(12, $sample->getAttribute('plant_variety_id'));
        $this->assertSame(34, $sample->getAttribute('user_id'));
    }

    public function test_resource_exposes_foreign_keys_and_contributor_name(): void
    {
        $contributor = new User([
            'name' => 'Jane Researcher',
            'email' => 'jane@example.com',
            'role' => 'student',
        ]);
        $contributor->id = 34;

        $sample = new PlantSample([
            'sample_name' => 'Alpha Sample',
            'sample_code' => 'S-001',
            'plant_variety_id' => 12,
            'user_id' => 34,
            'department' => 'Botany',
            'origin_location' => 'Greenhouse',
            'brought_at' => '2026-05-21',
            'lab_location' => LabLocation::LAB_B,
            'status' => SampleStatus::ACTIVE,
            'description' => 'Observed in the lab',
            'image_url' => 'https://example.test/sample.jpg',
        ]);
        $sample->id = 99;
        $sample->setRelation('contributor', $contributor);

        $data = (new PlantSampleResource($sample))->toArray(new HttpRequest);

        $this->assertSame(12, $data['plant_variety_id']);
        $this->assertSame(34, $data['user_id']);
        $this->assertSame('Jane Researcher', $data['details']['owner']);
    }
}
<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Timesheet;
use Laravel\Passport\Passport;

/**
 * @author Fahed
 * @group timesheets
 */
class TimesheetControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();
        $this->project->users()->attach($this->user->id);
        
        Passport::actingAs($this->user);
    }

    /**
     * Test listing timesheets with filters.
     *
     * @return void
     */
    public function test_can_list_timesheets_with_filters(): void
    {
        Timesheet::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'date' => '2024-03-10'
        ]);

        // Test user filter
        $response = $this->getJson("/api/timesheets?user_id={$this->user->id}");
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Test date filter
        $response = $this->getJson('/api/timesheets?date_from=2024-03-10&date_to=2024-03-10');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test creating a new timesheet entry.
     *
     * @return void
     */
    public function test_can_create_timesheet(): void
    {
        $timesheetData = [
            'project_id' => $this->project->id,
            'task_name' => 'Development Task',
            'date' => '2024-03-10',
            'hours' => 8
        ];

        $response = $this->postJson('/api/timesheets', $timesheetData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'task_name' => 'Development Task',
                'hours' => 8
            ]);

        $this->assertDatabaseHas('timesheets', [
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'task_name' => 'Development Task'
        ]);
    }

    /**
     * Test timesheet creation validation.
     *
     * @return void
     */
    public function test_cannot_create_timesheet_with_invalid_data(): void
    {
        $response = $this->postJson('/api/timesheets', [
            'hours' => 25, // Invalid hours
            'date' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'task_name', 'date', 'hours']);
    }

    /**
     * Test viewing a specific timesheet entry.
     *
     * @return void
     */
    public function test_can_view_timesheet(): void
    {
        $timesheet = Timesheet::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson("/api/timesheets/{$timesheet->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $timesheet->id,
                'task_name' => $timesheet->task_name
            ]);
    }

    /**
     * Test updating a timesheet entry.
     *
     * @return void
     */
    public function test_can_update_timesheet(): void
    {
        $timesheet = Timesheet::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $updateData = [
            'task_name' => 'Updated Task',
            'hours' => 6
        ];

        $response = $this->putJson("/api/timesheets/{$timesheet->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('timesheets', [
            'id' => $timesheet->id,
            'task_name' => 'Updated Task',
            'hours' => 6
        ]);
    }

    /**
     * Test timesheet update validation.
     *
     * @return void
     */
    public function test_cannot_update_timesheet_with_invalid_data(): void
    {
        $timesheet = Timesheet::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->putJson("/api/timesheets/{$timesheet->id}", [
            'hours' => 25 // Invalid hours
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hours']);
    }

    /**
     * Test deleting a timesheet entry.
     *
     * @return void
     */
    public function test_can_delete_timesheet(): void
    {
        $timesheet = Timesheet::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->deleteJson("/api/timesheets/{$timesheet->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('timesheets', ['id' => $timesheet->id]);
    }

    /**
     * Test unauthorized access to timesheet.
     *
     * @return void
     */
    public function test_unauthorized_user_cannot_access_timesheet(): void
    {
        $otherUser = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $otherUser->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson("/api/timesheets/{$timesheet->id}");
        $response->assertStatus(404);
    }
}

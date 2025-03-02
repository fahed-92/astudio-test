<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Attribute;
use Laravel\Passport\Passport;

/**
 * @author Fahed
 * @group projects
 */
class ProjectControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    /**
     * Test listing projects with filters.
     *
     * @return void
     */
    public function test_can_list_projects_with_filters(): void
    {
        $project = Project::factory()->create(['name' => 'Test Project']);
        $project->users()->attach($this->user->id);

        // Test regular field filter
        $response = $this->getJson('/api/projects?filters[name]=Test');
        
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Project']);

        // Test status filter
        $response = $this->getJson('/api/projects?filters[status]=active');
        
        $response->assertStatus(200);
    }

    /**
     * Test creating a new project.
     *
     * @return void
     */
    public function test_can_create_project(): void
    {
        $projectData = [
            'name' => 'New Project',
            'status' => 'active',
            'user_ids' => [$this->user->id],
            'attributes' => [
                'department' => 'IT'
            ]
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'New Project',
                'status' => 'active'
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
            'status' => 'active'
        ]);
    }

    /**
     * Test project creation validation.
     *
     * @return void
     */
    public function test_cannot_create_project_with_invalid_data(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => '',
            'status' => 'invalid-status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status', 'user_ids']);
    }

    /**
     * Test viewing a specific project.
     *
     * @return void
     */
    public function test_can_view_project(): void
    {
        $project = Project::factory()->create();
        $project->users()->attach($this->user->id);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $project->id,
                'name' => $project->name
            ]);
    }

    /**
     * Test updating a project.
     *
     * @return void
     */
    public function test_can_update_project(): void
    {
        $project = Project::factory()->create();
        $project->users()->attach($this->user->id);

        $updateData = [
            'name' => 'Updated Project',
            'status' => 'completed'
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('projects', $updateData);
    }

    /**
     * Test project update validation.
     *
     * @return void
     */
    public function test_cannot_update_project_with_invalid_data(): void
    {
        $project = Project::factory()->create();
        $project->users()->attach($this->user->id);

        $response = $this->putJson("/api/projects/{$project->id}", [
            'status' => 'invalid-status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test deleting a project.
     *
     * @return void
     */
    public function test_can_delete_project(): void
    {
        $project = Project::factory()->create();
        $project->users()->attach($this->user->id);

        $response = $this->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /**
     * Test unauthorized access to project.
     *
     * @return void
     */
    public function test_unauthorized_user_cannot_access_project(): void
    {
        Passport::actingAs(User::factory()->create()); // Different user
        $project = Project::factory()->create();

        $response = $this->getJson("/api/projects/{$project->id}");
        $response->assertStatus(404);
    }
}

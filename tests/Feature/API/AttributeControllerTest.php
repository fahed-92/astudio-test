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
 * @group attributes
 */
class AttributeControllerTest extends TestCase
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
     * Test listing attributes.
     *
     * @return void
     */
    public function test_can_list_attributes(): void
    {
        Attribute::factory()->count(3)->create([
            'project_id' => $this->project->id
        ]);

        $response = $this->getJson('/api/attributes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test listing attributes with project filter.
     *
     * @return void
     */
    public function test_can_list_attributes_by_project(): void
    {
        // Create attributes for current project
        Attribute::factory()->count(2)->create([
            'project_id' => $this->project->id
        ]);

        // Create attributes for another project
        $otherProject = Project::factory()->create();
        Attribute::factory()->count(3)->create([
            'project_id' => $otherProject->id
        ]);

        // Test filtering by current project
        $response = $this->getJson("/api/attributes?project_id={$this->project->id}");
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Verify all attributes belong to the requested project
        $responseData = json_decode($response->getContent(), true);
        foreach ($responseData['data'] as $attribute) {
            $this->assertEquals($this->project->id, $attribute['project_id']);
        }
    }

    /**
     * Test creating a new attribute.
     *
     * @return void
     */
    public function test_can_create_attribute(): void
    {
        $attributeData = [
            'project_id' => $this->project->id,
            'name' => 'department',
            'value' => 'IT',
            'type' => 'string'
        ];

        $response = $this->postJson('/api/attributes', $attributeData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'department',
                'value' => 'IT',
                'type' => 'string'
            ]);

        $this->assertDatabaseHas('attributes', [
            'project_id' => $this->project->id,
            'name' => 'department',
            'value' => 'IT'
        ]);
    }

    /**
     * Test preventing duplicate attribute names within the same project.
     *
     * @return void
     */
    public function test_cannot_create_duplicate_attribute_names_in_same_project(): void
    {
        // Create first attribute
        $attributeData = [
            'project_id' => $this->project->id,
            'name' => 'department',
            'value' => 'IT',
            'type' => 'string'
        ];
        $this->postJson('/api/attributes', $attributeData);

        // Try to create another attribute with the same name in the same project
        $response = $this->postJson('/api/attributes', $attributeData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Verify same name is allowed in different project
        $otherProject = Project::factory()->create();
        $attributeData['project_id'] = $otherProject->id;
        $response = $this->postJson('/api/attributes', $attributeData);
        $response->assertStatus(201);
    }

    /**
     * Test creating a select-type attribute.
     *
     * @return void
     */
    public function test_can_create_select_attribute(): void
    {
        $attributeData = [
            'project_id' => $this->project->id,
            'name' => 'department',
            'type' => 'select',
            'options' => ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'],
            'value' => 'IT'
        ];

        $response = $this->postJson('/api/attributes', $attributeData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'department',
                'type' => 'select',
                'options' => ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'],
                'value' => 'IT'
            ]);

        $this->assertDatabaseHas('attributes', [
            'project_id' => $this->project->id,
            'name' => 'department',
            'type' => 'select',
            'value' => 'IT'
        ]);
    }

    /**
     * Test select attribute validation.
     *
     * @return void
     */
    public function test_select_attribute_validation(): void
    {
        // Test without options
        $response = $this->postJson('/api/attributes', [
            'project_id' => $this->project->id,
            'name' => 'department',
            'type' => 'select',
            'value' => 'IT'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['options']);

        // Test with empty options array
        $response = $this->postJson('/api/attributes', [
            'project_id' => $this->project->id,
            'name' => 'department',
            'type' => 'select',
            'options' => [],
            'value' => 'IT'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['options']);

        // Test with value not in options
        $response = $this->postJson('/api/attributes', [
            'project_id' => $this->project->id,
            'name' => 'department',
            'type' => 'select',
            'options' => ['HR', 'Finance'],
            'value' => 'IT'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    /**
     * Test value type validation for each attribute type.
     *
     * @return void
     */
    public function test_value_type_validation(): void
    {
        $testCases = [
            'string' => [
                'valid' => 'test string',
                'invalid' => ['array', 'value']
            ],
            'number' => [
                'valid' => 42,
                'invalid' => 'not a number'
            ],
            'date' => [
                'valid' => '2024-03-10',
                'invalid' => 'invalid-date'
            ],
            'select' => [
                'valid' => ['value' => 'Option 1', 'options' => ['Option 1', 'Option 2']],
                'invalid' => ['value' => 'Invalid Option', 'options' => ['Option 1', 'Option 2']]
            ]
        ];

        foreach ($testCases as $type => $values) {
            // Test valid value
            $attributeData = [
                'project_id' => $this->project->id,
                'name' => $this->faker->word,
                'type' => $type
            ];

            if ($type === 'select') {
                $attributeData['options'] = $values['valid']['options'];
                $attributeData['value'] = $values['valid']['value'];
            } else {
                $attributeData['value'] = $values['valid'];
            }

            $response = $this->postJson('/api/attributes', $attributeData);
            $response->assertStatus(201);

            // Test invalid value
            $attributeData = [
                'project_id' => $this->project->id,
                'name' => $this->faker->word,
                'type' => $type
            ];

            if ($type === 'select') {
                $attributeData['options'] = $values['invalid']['options'];
                $attributeData['value'] = $values['invalid']['value'];
            } else {
                $attributeData['value'] = $values['invalid'];
            }

            $response = $this->postJson('/api/attributes', $attributeData);
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['value']);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\Project;

class AttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all projects
        $projects = Project::all();

        // Common attribute definitions
        $commonAttributes = [
            [
                'name' => 'department',
                'type' => 'select',
                'options' => ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'],
                'value' => 'IT' // Default value
            ],
            [
                'name' => 'start_date',
                'type' => 'date',
                'value' => now()->format('Y-m-d')
            ],
            [
                'name' => 'end_date',
                'type' => 'date',
                'value' => now()->addMonths(3)->format('Y-m-d')
            ],
            [
                'name' => 'budget',
                'type' => 'number',
                'value' => 10000
            ],
            [
                'name' => 'priority',
                'type' => 'select',
                'options' => ['Low', 'Medium', 'High', 'Critical'],
                'value' => 'Medium' // Default value
            ]
        ];

        // Create common attributes for each project
        foreach ($projects as $project) {
            foreach ($commonAttributes as $attribute) {
                Attribute::create(array_merge(
                    $attribute,
                    ['project_id' => $project->id]
                ));
            }

            // Create additional random attributes for each project
            Attribute::factory()
                ->count(3)
                ->create([
                    'project_id' => $project->id
                ]);
        }
    }
}

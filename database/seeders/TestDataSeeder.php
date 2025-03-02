<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Attribute;
use App\Models\Timesheet;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create attributes
        $departmentAttr = Attribute::create([
            'name' => 'department',
            'type' => 'select',
            'options' => ['IT', 'HR', 'Finance', 'Marketing']
        ]);

        $startDateAttr = Attribute::create([
            'name' => 'start_date',
            'type' => 'date'
        ]);

        $budgetAttr = Attribute::create([
            'name' => 'budget',
            'type' => 'number'
        ]);

        // Create projects
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'status' => 'active'
        ]);

        $project1->users()->attach($user->id);
        $project1->setDynamicAttribute('department', 'IT');
        $project1->setDynamicAttribute('start_date', '2024-03-01');
        $project1->setDynamicAttribute('budget', '50000');

        $project2 = Project::create([
            'name' => 'HR System Implementation',
            'status' => 'on_hold'
        ]);

        $project2->users()->attach($user->id);
        $project2->setDynamicAttribute('department', 'HR');
        $project2->setDynamicAttribute('start_date', '2024-04-01');
        $project2->setDynamicAttribute('budget', '75000');

        // Create timesheets
        Timesheet::create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
            'task_name' => 'Frontend Development',
            'date' => '2024-03-02',
            'hours' => 8
        ]);

        Timesheet::create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
            'task_name' => 'Backend API',
            'date' => '2024-03-03',
            'hours' => 6
        ]);

        Timesheet::create([
            'user_id' => $user->id,
            'project_id' => $project2->id,
            'task_name' => 'Requirements Analysis',
            'date' => '2024-03-02',
            'hours' => 4
        ]);
    }
}

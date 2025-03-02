<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Timesheet;

class TimesheetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::with('users')->get();

        foreach ($projects as $project) {
            // For each user in the project
            foreach ($project->users as $user) {
                // Create 5-10 timesheet entries per user per project
                $numEntries = rand(5, 10);
                
                Timesheet::factory()
                    ->count($numEntries)
                    ->forUser($user)
                    ->forProject($project)
                    ->create();
            }
        }
    }
}

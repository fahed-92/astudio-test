<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        // Create projects with different statuses
        Project::factory()
            ->count(5)
            ->active()
            ->create()
            ->each(function ($project) use ($users) {
                // Attach 2-4 random users to each project
                $project->users()->attach(
                    $users->random(rand(2, 4))->pluck('id')->toArray()
                );
            });

        Project::factory()
            ->count(3)
            ->completed()
            ->create()
            ->each(function ($project) use ($users) {
                $project->users()->attach(
                    $users->random(rand(2, 4))->pluck('id')->toArray()
                );
            });

        Project::factory()
            ->count(2)
            ->onHold()
            ->create()
            ->each(function ($project) use ($users) {
                $project->users()->attach(
                    $users->random(rand(2, 4))->pluck('id')->toArray()
                );
            });

        Project::factory()
            ->count(1)
            ->cancelled()
            ->create()
            ->each(function ($project) use ($users) {
                $project->users()->attach(
                    $users->random(rand(2, 4))->pluck('id')->toArray()
                );
            });
    }
}

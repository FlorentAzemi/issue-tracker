<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'title'       => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(2, true),
            'status'      => $this->faker->randomElement(['open', 'in_progress', 'closed']),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high']),
            'due_date'    => $this->faker->optional()->dateTimeBetween('now', '+2 months'),
        ];
    }
}

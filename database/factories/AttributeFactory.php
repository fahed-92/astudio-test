<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attribute;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['string', 'number', 'boolean', 'date', 'select']);
        $value = match($type) {
            'string' => fake()->word(),
            'number' => fake()->numberBetween(1, 1000),
            'boolean' => fake()->boolean(),
            'date' => fake()->date(),
            'select' => fake()->randomElement(['Option 1', 'Option 2', 'Option 3']),
        };

        return [
            'name' => fake()->unique()->word(),
            'type' => $type,
            'value' => $value,
            'options' => $type === 'select' ? ['Option 1', 'Option 2', 'Option 3'] : null,
        ];
    }

    public function string(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'string',
            'value' => fake()->word(),
            'options' => null,
        ]);
    }

    public function date(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'date',
            'value' => fake()->date(),
            'options' => null,
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'number',
            'value' => fake()->numberBetween(1, 1000),
            'options' => null,
        ]);
    }

    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'value' => fake()->boolean(),
            'options' => null,
        ]);
    }

    public function select(array $options = null): static
    {
        $options = $options ?? ['Option 1', 'Option 2', 'Option 3'];
        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'value' => fake()->randomElement($options),
            'options' => $options,
        ]);
    }
}

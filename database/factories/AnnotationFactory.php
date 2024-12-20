<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Annotation>
 */
class AnnotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'x'=>fake()->randomFloat(2,0),
            'y'=> fake()->randomFloat(2,0),
            'label'=> fake()->word(),
            'image_id'=> \App\Models\Image::factory()
        ];
    }
}

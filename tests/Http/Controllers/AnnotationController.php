<?php

namespace Tests\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnotationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_authenticated()
    {
        $image = Image::factory()->create();
        $this->postJson("/api/images/{$image->id}/annotations")->assertStatus(401);
    }
    public function test_store_authorized()
    {
        $image = Image::factory()->create();
        $this->be(User::factory()->make());
        $this->postJson("/api/images/{$image->id}/annotations")->assertStatus(403);
    }

    public function test_store_validation()
    {
        $user = User::factory()->create();
        $image = Image::factory()->create(['user_id' => $user->id]);
        $this->be($user);

        $this->postJson("/api/images/{$image->id}/annotations")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['x', 'y', 'label']);

        $this->postJson("/api/images/{$image->id}/annotations", [
                'x' => -1,
                'y' => -1,
                'label' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['x', 'y', 'label']);
    }
    public function test_store_new()
    {
        $user = User::factory()->create();
        $image = Image::factory()->create(['user_id' => $user->id]);
        $this->be($user);
    
        $this->postJson("/api/images/{$image->id}/annotations", [
                'x' => 1,
                'y' => 2,
                'label' => 'object',
            ])
            ->assertStatus(201);
    
        $annotation = $image->annotations()->first();
        $this->assertNotNull($annotation);
        $this->assertEquals(1, $annotation->x);
        $this->assertEquals(2, $annotation->y);
        $this->assertEquals('object', $annotation->label);
    }

}
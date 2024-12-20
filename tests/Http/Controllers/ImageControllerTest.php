<?php

namespace Tests\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Models\Annotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_store_authenticated()
    {
        $this->postJson('/api/images')->assertStatus(401);
    }
    public function test_store_new()
    {
        $user = User::factory()->create();
        $this->be($user);
        // Validation error because no hash is provided.
        $this->postJson('/api/images')->assertStatus(422);

        $hash = fake()->sha256();
        $this->postJson('/api/images', ['hash' => $hash])
            ->assertStatus(201)
            ->assertJson(fn ($json) => $json->has('id'));

        $image = $user->images()->first();
        $this->assertNotNull($image);
        $this->assertEquals($hash, $image->hash);
    }
    public function test_store_existing()
    {
        $user = User::factory()->create();
        $image = Image::factory()->create(['user_id' => $user->id]);
        $this->be($user);
        $this->postJson('/api/images', ['hash' => $image->hash])
            ->assertStatus(200)
            ->assertJson(fn ($json) => 
                $json->where('id',$image->id)
                    ->where('annotations',[])
        );
        $this->assertEquals(1, $user->images()->count());
    }
    public function test_store_duplicate_other_user()
    {
        $user = User::factory()->create();
        $image = Image::factory()->create();
        $this->be($user);
        $this->postJson('/api/images', ['hash' => $image->hash])
            ->assertStatus(201)
            ->assertJson(fn ($json) => $json->whereNot('id', $image->id));

        $newImage = $user->images()->first();
        $this->assertNotNull($newImage);
        $this->assertEquals($image->hash, $newImage->hash);
        $this->assertNotEquals($image->id, $newImage->id);
    }
    public function test_store_existing_annotations()
    {
        $user = User::factory()->create();
        $image = Image::factory()->create(['user_id' => $user->id]);
        $annotation = Annotation::factory()->create(['image_id' => $image->id]);
        $expectAnnotation = [
            'x' => $annotation->x,
            'y' => $annotation->y,
            'label' => $annotation->label,
            'id'=>$annotation->id,
            'image_id'=>$annotation->image_id
        ];
    
        $this->be($user);
        $this->postJson('/api/images', ['hash' => $image->hash])
            ->assertStatus(200)
            ->assertJson(fn ($json) =>
                $json->where('id', $image->id)
                    ->where('annotations', [$expectAnnotation])
            );
    }    

}
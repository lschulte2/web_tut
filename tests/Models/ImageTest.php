<?php

namespace Tests\Models;

use App\Models\Image;
use App\Models\User;
use App\Models\Annotation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_image_hash_unique()
    {
        $image1 = Image::factory()->create();
        $this->expectException(QueryException::class);
        $image2 = $image1->replicate();
        $image2->save();
    }
    public function test_annotation_delete_cascade()
    {
        $image = Image::factory()->create();
        $annotation = Annotation::factory()->create([
            'image_id' => $image->id,
        ]);
        $image->delete();
        $this->assertNull($annotation->fresh());
    }
    public function test_can_update()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $image = Image::factory()->create(['user_id' => $user1->id]);

        $this->assertTrue($user1->can('update', $image));
        $this->assertFalse($user2->can('update', $image));
    }
    }
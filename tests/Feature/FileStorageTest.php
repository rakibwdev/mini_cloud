<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageTest extends TestCase
{
    use RefreshDatabase;

    const STORAGE_LIMIT = 524288000;

    public function test_user_can_upload_file()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000); // 1MB

        $response = $this->postJson("/api/users/{$user->id}/files", [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['file' => ['id', 'name', 'size', 'upload_time']]);

        $this->assertDatabaseHas('user_files', [
            'user_id' => $user->id,
            'name' => 'document.pdf',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'used_storage' => 1024 * 1000,
        ]);
    }

    public function test_upload_fails_if_storage_limit_exceeded()
    {
        Storage::fake('local');
        $user = User::factory()->create(['used_storage' => self::STORAGE_LIMIT - 100]);
        $file = UploadedFile::fake()->create('large_file.pdf', 200); // 200KB > 100 bytes remaining

        $response = $this->postJson("/api/users/{$user->id}/files", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Storage limit exceeded']);
    }

    public function test_upload_fails_if_duplicate_name()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $this->postJson("/api/users/{$user->id}/files", ['file' => $file]);
        
        $response = $this->postJson("/api/users/{$user->id}/files", ['file' => $file]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'File with this name already exists']);
    }

    public function test_deduplication_logic()
    {
        Storage::fake('local');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Same content, different name
        $file1 = UploadedFile::fake()->createWithContent('file1.txt', 'content');
        $file2 = UploadedFile::fake()->createWithContent('file2.txt', 'content');

        $this->postJson("/api/users/{$user1->id}/files", ['file' => $file1]);
        $this->postJson("/api/users/{$user2->id}/files", ['file' => $file2]);

        $this->assertEquals(1, File::count());
        $this->assertEquals(2, UserFile::count());
    }

    public function test_delete_file()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson("/api/users/{$user->id}/files", ['file' => $file]);
        $fileId = $response->json('file.id');

        $response = $this->deleteJson("/api/users/{$user->id}/files/{$fileId}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_files', ['id' => $fileId]);
        
        $user->refresh();
        $this->assertEquals(0, $user->used_storage);
    }
    
    public function test_storage_summary()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000); // 1MB

        $this->postJson("/api/users/{$user->id}/files", ['file' => $file]);

        $response = $this->getJson("/api/users/{$user->id}/storage-summary");

        $response->assertStatus(200)
            ->assertJson([
                'total_storage_used' => 1024 * 1000,
                'remaining_storage' => self::STORAGE_LIMIT - (1024 * 1000),
                'total_active_files' => 1,
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageTest extends TestCase
{
    use RefreshDatabase;

    const STORAGE_LIMIT = 524288000;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_upload_file()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($user)->postJson("/api/files", [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_files', [
            'user_id' => $user->id,
            'name' => 'document.pdf',
        ]);
    }

    public function test_upload_fails_if_storage_limit_exceeded()
    {
        Storage::fake('local');
        $user = User::factory()->create(['used_storage' => self::STORAGE_LIMIT - 100]);
        $file = UploadedFile::fake()->create('large_file.pdf', 200);

        $response = $this->actingAs($user)->postJson("/api/files", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Storage limit exceeded']);
    }

    public function test_deduplication_logic()
    {
        Storage::fake('local');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $fileContent = 'content';
        $file1 = UploadedFile::fake()->createWithContent('file1.txt', $fileContent);
        $file2 = UploadedFile::fake()->createWithContent('file2.txt', $fileContent);

        $this->actingAs($user1)->postJson("/api/files", ['file' => $file1]);
        $this->actingAs($user2)->postJson("/api/files", ['file' => $file2]);

        $this->assertEquals(1, File::count());
        $this->assertEquals(2, UserFile::count());
    }

    public function test_delete_file()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $uploadResponse = $this->actingAs($user)->postJson("/api/files", ['file' => $file]);
        $fileId = $uploadResponse->json('file.id');

        $response = $this->actingAs($user)->deleteJson("/api/files/{$fileId}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_files', ['id' => $fileId]);
    }
}

# Mini Cloud Storage System

A backend system for a simulated cloud file storage service, built with Laravel. It supports file uploads with a 500MB storage limit per user, deduplication, file deletion, and storage summaries.

## Features

- **User Storage Limit:** Each user has a fixed storage limit of 500 MB.
- **File Upload:** Upload files with metadata (name, size, hash, upload time).
- **Deduplication:** Optimized storage by storing unique file content only once (based on SHA-256 hash), while maintaining individual user references.
- **Concurrency Handling:** Uses database transactions and row locking (`lockForUpdate`) to handle simultaneous uploads and prevent storage limit violations.
- **File Management:** View storage summary, list uploaded files, and delete files.

## Tech Stack

- **Backend:** Laravel (PHP)
- **Database:** MySQL
- **Testing:** PHPUnit

## Setup Instructions

1.  **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd mini_cloud
    ```

2.  **Install Dependencies:**
    ```bash
    composer install
    ```

3.  **Configure Environment:**
    Copy `.env.example` to `.env` and configure your database settings:
    ```bash
    cp .env.example .env
    ```
    Update `.env` with your MySQL credentials:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=mini_cloud
    DB_USERNAME=root
    DB_PASSWORD=your_password
    ```

4.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

5.  **Run Migrations:**
    ```bash
    php artisan migrate
    ```

6.  **Serve the Application:**
    ```bash
    php artisan serve
    ```

## API Documentation

### 1. Upload File
**Endpoint:** `POST /api/users/{user_id}/files`
**Description:** Uploads a file for a specific user.
**Body:** `form-data` with key `file`.

**Example Response:**
```json
{
    "message": "File uploaded successfully",
    "file": {
        "id": 1,
        "name": "document.pdf",
        "size": 1048576,
        "upload_time": "2023-10-27T10:00:00.000000Z"
    }
}
```

### 2. Delete File
**Endpoint:** `DELETE /api/users/{user_id}/files/{file_id}`
**Description:** Deletes a specific file for a user and updates their storage usage.

**Example Response:**
```json
{
    "message": "File deleted successfully"
}
```

### 3. Get Storage Summary
**Endpoint:** `GET /api/users/{user_id}/storage-summary`
**Description:** Returns the user's storage usage details.

**Example Response:**
```json
{
    "total_storage_used": 1048576,
    "remaining_storage": 523239424,
    "total_active_files": 1
}
```

### 4. List User Files
**Endpoint:** `GET /api/users/{user_id}/files`
**Description:** Lists all active files for a user.

**Example Response:**
```json
[
    {
        "id": 1,
        "name": "document.pdf",
        "size": 1048576,
        "upload_time": "2023-10-27T10:00:00.000000Z"
    }
]
```

## Running Tests

To run the automated tests:

```bash
php artisan test
```

## Design Decisions

- **Deduplication:** A `files` table stores physical file metadata (hash, size) uniquely. The `user_files` table links users to these files. This saves space when multiple users upload the same file.
- **Concurrency:** `DB::transaction` ensures atomicity. `User::lockForUpdate()` prevents race conditions where two simultaneous uploads could exceed the storage limit by reading the same initial usage value.
- **Storage Limit:** Enforced at the application level within the transaction before any database writes occur.


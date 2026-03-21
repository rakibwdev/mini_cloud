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

## Docker Setup (Recommended)

To run the project easily using Docker:

1.  **Ensure Docker and Docker Compose are installed.**
2.  **Run the application stack:**
    ```bash
    docker-compose up -d --build
    ```
3.  **Setup the application inside the container:**
    ```bash
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate
    docker-compose exec app php artisan db:seed
    ```
4.  **Access the application:** Open `http://localhost:8000` in your browser.

## Manual Setup Instructions

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

## Sample Requests

For a list of ready-to-use `curl` commands to test the API, please refer to the [SAMPLE_REQUESTS.md](SAMPLE_REQUESTS.md) file.

## Running Tests

To run the automated tests:

```bash
php artisan test
```

## Design Decisions

- **Deduplication:** A `files` table stores physical file metadata (hash, size) uniquely. The `user_files` table links users to these files. This saves space when multiple users upload the same file (e.g., viral content).
- **Concurrency Control:** 
    - Critical for enforcing the 500MB limit accurately. 
    - We use **Pessimistic Locking** (`User::lockForUpdate()`) within a database transaction.
    - When a user uploads a file, their row is locked. Simultaneous uploads for the *same* user are serialized (queued), ensuring the `used_storage` check is always based on the latest committed data.
    - This does not block *other* users, maintaining system throughput.
- **File Storage:** As per requirements, we only store file metadata and calculate hashes from the uploaded temporary file. The physical file content is not persisted to disk to save space/complexity for this demo, but the architecture allows enabling it easily via Laravel's `Storage` facade.

## Scaling Strategy (100K+ Users)

If the system grows to 100,000 users, the following strategies would be applied:

1.  **Database Optimization:**
    - **Indexing:** Ensure `user_id` and `file_hash` are indexed (already handled by Foreign Keys).
    - **Read Replicas:** The `GET /files` and `GET /summary` endpoints are read-heavy. We can route these to read-only database replicas to reduce load on the primary writer node.
    - **Sharding:** If `user_files` grows into millions of rows, we can shard the database based on `user_id`.

2.  **Storage Layer (If persisting files):**
    - Local disk storage won't scale. We would switch to **Object Storage** (AWS S3, Google Cloud Storage).
    - Metadata (DB) would store the S3 key/path instead of just the hash.

3.  **Caching:**
    - User storage summaries are calculated frequently. We can cache the `storage-summary` response in Redis, invalidating/updating it only when a file is uploaded or deleted.

4.  **Load Balancing:**
    - Deploy multiple instances of the Laravel application behind a Load Balancer (Nginx/HAProxy) to handle incoming HTTP traffic.


# Sample API Requests

This document provides `curl` commands to test the Mini Cloud Storage API.

## Base URL
Assuming the server is running locally on port 8000:
`http://localhost:8000/api`

## 1. Upload a File
Simulate uploading a file named `document.pdf` for User ID 1.

**Note:** You must create a dummy file locally first if testing with a real file.
```bash
# Create a dummy file (1MB)
dd if=/dev/zero of=document.pdf bs=1M count=1

# Upload the file
curl -X POST http://localhost:8000/api/users/1/files \
  -H "Accept: application/json" \
  -F "file=@document.pdf"
```

**Response (Success - 201 Created):**
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

## 2. Get Storage Summary
Check the current storage usage for User ID 1.

```bash
curl -X GET http://localhost:8000/api/users/1/storage-summary \
  -H "Accept: application/json"
```

**Response (200 OK):**
```json
{
    "total_storage_used": 1048576,
    "remaining_storage": 523239424,
    "total_active_files": 1
}
```

## 3. List User Files
Retrieve all files uploaded by User ID 1.

```bash
curl -X GET http://localhost:8000/api/users/1/files \
  -H "Accept: application/json"
```

**Response (200 OK):**
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

## 4. Delete a File
Delete the file with ID 1 for User ID 1.

```bash
curl -X DELETE http://localhost:8000/api/users/1/files/1 \
  -H "Accept: application/json"
```

**Response (200 OK):**
```json
{
    "message": "File deleted successfully"
}
```

## 5. Test Deduplication
Upload the same physical file for a *different* user (User ID 2).

```bash
# Upload same file for User 2
curl -X POST http://localhost:8000/api/users/2/files \
  -H "Accept: application/json" \
  -F "file=@document.pdf"
```

**Outcome:**
- A new record is created in `user_files` linking User 2 to the existing physical file.
- No new record is created in `files` (deduplication works).
- Both users "own" a copy of the file logically.

## 6. Test Storage Limit
Try to upload a file larger than the remaining quota (simulated).

**Note:** You can create a large file or just upload a small one repeatedly until the limit is hit. Since the limit is 500MB, creating a 501MB file is impractical for quick tests, but you can adjust the `STORAGE_LIMIT` in `app/Http/Controllers/FileController.php` temporarily to test this easily (e.g., set to 5MB).

```bash
# Example response when limit exceeded
{
    "error": "Storage limit exceeded"
}
```

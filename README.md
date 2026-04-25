# Polaris Attendance

Polaris Attendance is a web-based attendance system for staff/drivers that supports:
- Driver identity verification (ID + facial verification) before access
- Capturing attendance (check-in / check-out)
- Offline device uploads via a JSON API endpoint
- Admin tooling like reports, audit logs, settings, and live location updates

## Tech Stack
- **Backend + API**: [Laravel](https://laravel.com) (PHP 8.2+, Laravel 12)
- **Frontend**: Blade templates + Vite (Tailwind CSS) + JavaScript (Axios + Chart.js)
- **Face Recognition**: Python DeepFace service (local processing)
- **OCR**: PaddleOCR for ID card text extraction
- **Database**: Laravel Eloquent ORM with configurable SQL database (default: SQLite)

## Architecture Overview
- **Web UI routes** in `routes/web.php` render Blade pages (login, driver verification UI, dashboard, reports, etc.)
- **JSON API routes** in `routes/api.php` serve hardware/software clients (offline devices)
- **Face recognition** handled by `FaceRecognitionService` with multiple fallback methods
- **OCR processing** handled by `PythonVisionService` with PaddleOCR backend

## Main API Endpoints

### Offline Sync
- **Endpoint**: `POST /api/offline/attendance`
- **Authentication**: Bearer token (`Authorization: Bearer <device_api_token>`) or `device_token` in request body
- **Request Body**:
  ```json
  {
    "events": [
      {
        "driver_id": 1,
        "type": "check_in",
        "captured_at": "2026-04-16T10:00:00Z",
        "face_confidence": 0.85,
        "liveness_score": 0.92,
        "device_ref": "CAM-1"
      }
    ]
  }
  ```
- **Response**: Returns number of events stored plus device info

### PowerShell Example
```powershell
$body = @{
  events = @(
    @{
      driver_id = 1
      type = "check_in"
      captured_at = "2026-04-16T10:00:00Z"
      face_confidence = 0.85
      liveness_score = 0.92
      device_ref = "CAM-1"
    }
  )
} | ConvertTo-Json -Depth 5

curl -Uri "http://localhost:8000/api/offline/attendance" `
  -Method Post `
  -Headers @{ Authorization = "Bearer YOUR_DEVICE_API_TOKEN" } `
  -ContentType "application/json" `
  -Body $body
```

## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js + npm
- Python 3.8+ (for face recognition and OCR)

### Step-by-Step Setup

1. **Configure Environment**
   ```bash
   cp .env.example .env
   ```

2. **Install Backend Dependencies**
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --force
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   npm run dev
   ```

4. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

5. **Setup Python Services** (Optional - for face recognition/OCR)
   - Python services will auto-install required packages
   - Face recognition uses local DeepFace processing
   - OCR uses PaddleOCR for text extraction

### Handy Composer Scripts
- `composer setup`: Installs deps, generates key, migrates, and builds frontend assets
- `composer dev`: Starts Laravel + queue + Vite concurrently

## Features

### Face Recognition System
- **Multi-tier fallback system**:
  1. DeepFace API (if configured)
  2. Python DeepFace service (local)
  3. Simple image comparison
  4. Hash-based comparison
- **Adaptive confidence thresholds** based on image quality
- **Liveness detection** support
- **Driver enrollment** with face templates

### OCR Processing
- **PaddleOCR** for high-performance OCR (primary)
- **EasyOCR integration** for reliable text extraction (fallback)
- **Multiple fallback services** for compatibility
- **ID card information parsing**
- **Support for multiple image formats** (JPEG, PNG, TIFF, BMP, WEBP)

### Attendance Management
- **Check-in/Check-out** with face verification
- **Location tracking** and route compliance
- **Offline device sync** capability
- **Real-time notifications**
- **Comprehensive reporting** and analytics

## Using the Application

1. **Access the Web UI**
   - Navigate to your `APP_URL` or `http://localhost:8000`
   - Log in with your credentials

2. **Driver Verification**
   - Drivers may need to complete ID + face verification
   - Depends on app configuration and middleware settings

3. **Attendance Capture**
   - Use the camera interface for check-in/check-out
   - Face recognition automatically verifies identity
   - Location tracking if enabled

4. **Admin Features**
   - View attendance reports and analytics
   - Manage driver accounts and verifications
   - Configure system settings
   - Monitor audit logs

## Testing

```bash
# Run PHP tests
php artisan test

# Test Python services
cd python
python deepface_service.py
python simple_ocr_service.py
```

## Configuration

### Environment Variables
Key settings in `.env`:
- `APP_URL`: Application URL
- `DB_DATABASE`: Database configuration
- `FACE_RECOGNITION_ENABLED`: Enable/disable face recognition
- `LIVENESS_DETECTION_ENABLED`: Enable/disable liveness detection
- `MIN_FACE_CONFIDENCE`: Minimum confidence threshold

### Face Recognition Settings
- `MIN_FACE_CONFIDENCE`: Default 80%
- `MIN_LIVENESS_SCORE`: Default 0.7
- `REQUIRE_PHOTO_ATTENDANCE`: Require photo for attendance

### PaddleOCR Setup
- **Installation**: PaddleOCR is automatically installed with Python dependencies
- **Configuration**: No additional API keys required
- **Priority**: PaddleOCR is used as primary OCR service
- **Fallbacks**: Automatically falls back to EasyOCR if PaddleOCR fails

## Troubleshooting

### Face Recognition Issues
- Ensure Python services are accessible
- Check face image quality and lighting
- Verify driver face enrollment exists

### OCR Issues
- EasyOCR auto-installs on first use
- Check image quality and text clarity
- Verify supported image formats

### Performance
- Use SSD for database storage
- Configure queue workers for background jobs
- Enable caching where appropriate

## API Documentation

### Authentication
- **Web UI**: Laravel session authentication
- **API**: Bearer tokens or device tokens
- **Devices**: Pre-configured API tokens

### Response Format
All API responses return JSON with consistent structure:
```json
{
  "success": true|false,
  "data": {...},
  "message": "Optional message",
  "errors": {...}
}
```

## Security Features
- **Face verification** for identity confirmation
- **Liveness detection** prevents photo spoofing
- **API token authentication** for device access
- **Audit logging** for compliance
- **Role-based access control**

## License

The Laravel framework is open-sourced software licensed under the MIT license.

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects.

For more information:
- [Laravel Documentation](https://laravel.com/docs)
- [Laracasts](https://laracasts.com)
- [Laravel Learn](https://laravel.com/learn)

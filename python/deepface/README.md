# DeepFace Python Implementation

This directory contains the Python implementation of DeepFace for face recognition and verification.

## Setup

1. Install Python 3.8+ and pip
2. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Run the DeepFace server:
   ```bash
   python app.py
   ```

The server will be available at `http://localhost:8225` by default.

## Configuration

Set the following environment variables in your `.env` file:
- `DEEPFACE_BASE_URL`: Base URL of the DeepFace server (e.g., http://localhost:8225)
- `DEEPFACE_RECOGNITION_API_KEY`: API key for face recognition
- `DEEPFACE_VERIFICATION_API_KEY`: API key for face verification

## API Endpoints

- `POST /api/v1/recognition/faces` - Enroll a face
- `POST /api/v1/recognition/recognize` - Recognize faces
- `POST /api/v1/verification/verify` - Verify face similarity

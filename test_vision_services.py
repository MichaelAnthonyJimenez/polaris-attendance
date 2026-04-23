#!/usr/bin/env python3
"""
Test script for vision services (OCR and DeepFace)
"""

import sys
import os
import base64
import json
from io import BytesIO
from PIL import Image, ImageDraw

# Add the python directory to the path
python_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'python')
sys.path.append(python_dir)

from ocr_service import extract_text_from_image, extract_id_info
from deepface_service import DeepFaceService

def create_test_image(text="TEST IMAGE", size=(200, 100)):
    """Create a simple test image with text"""
    img = Image.new('RGB', size, color='white')
    draw = ImageDraw.Draw(img)

    # Draw text
    try:
        # Try to use a basic font
        draw.text((10, 10), text, fill='black')
    except:
        # If font loading fails, just create a simple pattern
        for i in range(0, size[0], 20):
            for j in range(0, size[1], 20):
                if (i + j) % 40 == 0:
                    draw.rectangle([i, j, i+10, j+10], fill='black')

    return img

def create_test_face_image():
    """Create a simple face-like test image"""
    img = Image.new('RGB', (200, 200), color='white')
    draw = ImageDraw.Draw(img)

    # Draw a simple face
    # Head outline
    draw.ellipse([50, 30, 150, 170], outline='black', width=3)

    # Eyes
    draw.ellipse([70, 70, 90, 90], fill='black')
    draw.ellipse([110, 70, 130, 90], fill='black')

    # Nose
    draw.polygon([(100, 100), (90, 120), (110, 120)], fill='black')

    # Mouth
    draw.ellipse([80, 130, 120, 150], outline='black', width=2)

    return img

def image_to_base64(image):
    """Convert PIL Image to base64 string"""
    buffer = BytesIO()
    image.save(buffer, format='JPEG')
    img_str = base64.b64encode(buffer.getvalue()).decode()
    return f"data:image/jpeg;base64,{img_str}"

def test_ocr_service():
    """Test OCR service"""
    print("Testing OCR Service...")

    # Create test image with text
    test_img = create_test_image("HELLO WORLD\nJohn Doe\nID: 123456789")
    test_data = image_to_base64(test_img)

    try:
        result = extract_text_from_image(test_data)
        print(f"OCR Success: {result['success']}")
        if result['success']:
            print(f"Extracted Text: {result['text']}")
            print(f"Average Confidence: {result['avg_confidence']:.2f}%")

            # Test ID info extraction
            id_info = extract_id_info(result['text'])
            print("Extracted ID Info:")
            for key, value in id_info.items():
                print(f"  {key}: {value}")
        else:
            print(f"OCR Error: {result['error']}")
    except Exception as e:
        print(f"OCR Test Failed: {e}")

    print()

def test_deepface_service():
    """Test DeepFace service"""
    print("Testing DeepFace Service...")

    try:
        service = DeepFaceService()

        # Create test face image
        test_img = create_test_face_image()
        test_data = image_to_base64(test_img)

        # Test face detection
        print("Testing face detection...")
        detection_result = service.detect_face(test_data, enforce_detection=False)
        print(f"Detection Success: {detection_result['success']}")
        if detection_result['success']:
            print(f"Faces Detected: {detection_result['total_faces']}")
            for face in detection_result['faces']:
                print(f"  Face {face['face_index']}: Confidence {face['confidence']:.2f}")
        else:
            print(f"Detection Error: {detection_result['error']}")

        # Test face analysis
        print("\nTesting face analysis...")
        analysis_result = service.analyze_face(test_data, ['age', 'gender', 'emotion', 'race'])
        print(f"Analysis Success: {analysis_result['success']}")
        if analysis_result['success']:
            print("Face Analysis Results:")
            for key, value in analysis_result['analysis'].items():
                print(f"  {key}: {value}")
        else:
            print(f"Analysis Error: {analysis_result['error']}")

    except Exception as e:
        print(f"DeepFace Test Failed: {e}")

    print()

def test_service_integration():
    """Test service integration"""
    print("Testing Service Integration...")

    try:
        # Test the same image with both services
        test_img = create_test_image("John Doe\nDriver License\nID: 123456789", (300, 200))
        test_data = image_to_base64(test_img)

        # OCR Test
        ocr_result = extract_text_from_image(test_data)
        print(f"OCR Result: {ocr_result['success']}")

        # DeepFace Test
        service = DeepFaceService()
        face_result = service.detect_face(test_data, enforce_detection=False)
        print(f"Face Detection Result: {face_result['success']}")

        print("Integration test completed successfully!")

    except Exception as e:
        print(f"Integration Test Failed: {e}")

    print()

def main():
    """Main test function"""
    print("=== Vision Services Test ===\n")

    # Test OCR service
    test_ocr_service()

    # Test DeepFace service
    test_deepface_service()

    # Test integration
    test_service_integration()

    print("=== Test Complete ===")

if __name__ == "__main__":
    main()

#!/usr/bin/env python3
"""
OCR Service for ID Card Text Extraction
Uses PaddleOCR for OCR functionality
Supports multiple image formats: JPEG, PNG, TIFF, BMP, WEBP
"""

import os
import sys
import json
import base64
import mimetypes
from io import BytesIO
from PIL import Image
import numpy as np
from paddleocr import PaddleOCR

# Supported image formats
SUPPORTED_FORMATS = {
    'image/jpeg': ['.jpg', '.jpeg'],
    'image/png': ['.png'],
    'image/tiff': ['.tiff', '.tif'],
    'image/bmp': ['.bmp'],
    'image/webp': ['.webp']
}

# Max file size (10MB)
MAX_FILE_SIZE = 10 * 1024 * 1024

# Initialize PaddleOCR
try:
    ocr = PaddleOCR(lang='en')
    print("PaddleOCR initialized successfully")
except Exception as e:
    print(f"Failed to initialize PaddleOCR: {e}")
    sys.exit(1)

def validate_image_format(image_data, is_base64=False):
    """
    Validate image format and file size

    Args:
        image_data: Image data (bytes or file path)
        is_base64: Whether the input is base64 encoded

    Returns:
        tuple: (is_valid, format_info, error_message)
    """
    try:
        if is_base64:
            # For base64, check the data URI prefix
            if isinstance(image_data, str) and image_data.startswith('data:image'):
                mime_type = image_data.split(';')[0].split(':')[1]
                if mime_type not in SUPPORTED_FORMATS:
                    return False, None, f"Unsupported image format: {mime_type}"
                return True, {'mime_type': mime_type}, None
            else:
                return False, None, "Invalid base64 image data URI"
        else:
            # For file paths, check if file exists and try to open as image
            if not os.path.exists(image_data):
                return False, None, "File does not exist"

            file_size = os.path.getsize(image_data)
            if file_size > MAX_FILE_SIZE:
                return False, None, f"File too large. Max size: {MAX_FILE_SIZE // (1024*1024)}MB"

            # First try to open the file as an image to verify it's actually an image
            try:
                with Image.open(image_data) as img:
                    # Verify we can load the image
                    img.verify()

                # Reopen for format detection (verify() closes the file)
                with Image.open(image_data) as img:
                    file_ext = os.path.splitext(image_data)[1].lower()
                    actual_format = img.format.upper() if img.format else 'UNKNOWN'

                    # Check if extension is supported
                    for mime_type, extensions in SUPPORTED_FORMATS.items():
                        if file_ext in extensions:
                            return True, {
                                'mime_type': mime_type,
                                'extension': file_ext,
                                'actual_format': actual_format
                            }, None

                    # If extension is not recognized but the file is a valid image,
                    # try to map the actual format to a supported MIME type
                    format_mapping = {
                        'JPEG': 'image/jpeg',
                        'PNG': 'image/png',
                        'TIFF': 'image/tiff',
                        'BMP': 'image/bmp',
                        'WEBP': 'image/webp'
                    }

                    if actual_format in format_mapping:
                        mime_type = format_mapping[actual_format]
                        return True, {
                            'mime_type': mime_type,
                            'extension': file_ext,
                            'actual_format': actual_format,
                            'extension_mismatch': True
                        }, None

                    return False, None, f"The file is a valid {actual_format} image but this format is not supported"

            except Exception as e:
                # If we can't open it as an image, it's not a valid image file
                return False, None, f"The file must be an image. Error: {str(e)}"

    except Exception as e:
        return False, None, f"Validation error: {str(e)}"

def convert_to_rgb(image):
    """
    Convert image to RGB format for OCR processing

    Args:
        image: PIL Image object

    Returns:
        PIL Image in RGB format
    """
    if image.mode != 'RGB':
        return image.convert('RGB')
    return image

def get_supported_formats():
    """
    Get list of supported image formats

    Returns:
        dict: Supported formats with MIME types and extensions
    """
    return SUPPORTED_FORMATS

def extract_text_from_image(image_data):
    """
    Extract text from an image using OCR

    Args:
        image_data: Base64 encoded image string or file path

    Returns:
        dict: Extracted text and metadata
    """
    try:
        # Determine input type and validate
        is_base64 = isinstance(image_data, str) and image_data.startswith('data:image')
        is_file_path = isinstance(image_data, str) and os.path.exists(image_data)

        if not (is_base64 or is_file_path):
            raise ValueError("Invalid image data format. Expected base64 data URI or file path.")

        # Validate image format
        is_valid, format_info, error_msg = validate_image_format(image_data, is_base64)
        if not is_valid:
            raise ValueError(error_msg)

        print(f"Processing image: {format_info}")

        # Handle base64 input
        if is_base64:
            try:
                # Extract base64 data
                image_data = image_data.split(',')[1]
                image_bytes = base64.b64decode(image_data)
                image = Image.open(BytesIO(image_bytes))
                image = convert_to_rgb(image)
            except Exception as e:
                print(f"Error processing base64 image: {e}")
                raise ValueError("Invalid base64 image data")
        elif is_file_path:
            # Handle file path
            image_path = image_data
            try:
                image = Image.open(image_path)
                image = convert_to_rgb(image)
            except Exception as e:
                raise ValueError(f"Cannot open image file: {e}")
        else:
            raise ValueError("Invalid image data format")

        # For file path input, use the path directly; for base64, save to temp file
        if is_file_path:
            result = ocr.ocr(image_data)
        else:
            # Save base64 image to temporary file with appropriate extension
            import tempfile

            # Determine file extension from format info
            if format_info and 'mime_type' in format_info:
                mime_type = format_info['mime_type']
                extensions = SUPPORTED_FORMATS.get(mime_type, ['.png'])
                suffix = extensions[0]
            else:
                suffix = '.png'

            # Create temp file and ensure it's properly closed before OCR
            temp_file_path = None
            try:
                with tempfile.NamedTemporaryFile(suffix=suffix, delete=False) as tmp_file:
                    temp_file_path = tmp_file.name
                    # Determine PIL format name
                    pil_format = image.format or 'PNG'
                    if pil_format in ['JPG', 'JPEG']:
                        pil_format = 'JPEG'
                    elif pil_format == 'JPG':
                        pil_format = 'JPEG'
                    image.save(tmp_file.name, format=pil_format)

                # Run OCR on the saved temp file
                result = ocr.ocr(temp_file_path)

            finally:
                # Clean up temp file
                if temp_file_path and os.path.exists(temp_file_path):
                    try:
                        os.unlink(temp_file_path)
                    except:
                        pass  # Ignore cleanup errors

        # Process results
        words = []

        if result and len(result) > 0:
            # New PaddleOCR format returns a list with dictionaries
            ocr_result = result[0]

            if 'rec_texts' in ocr_result and 'rec_scores' in ocr_result and 'rec_polys' in ocr_result:
                texts = ocr_result['rec_texts']
                scores = ocr_result['rec_scores']
                polys = ocr_result['rec_polys']

                for i in range(len(texts)):
                    if i < len(scores) and i < len(polys):
                        bbox_points = polys[i]
                        text = texts[i]
                        confidence = scores[i]

                        # Convert bbox points to x,y,width,height format
                        x_coords = [point[0] for point in bbox_points]
                        y_coords = [point[1] for point in bbox_points]
                        x_min, x_max = min(x_coords), max(x_coords)
                        y_min, y_max = min(y_coords), max(y_coords)

                        words.append({
                            'text': text,
                            'confidence': confidence * 100,  # Convert to percentage
                            'bbox': {
                                'x': int(x_min),
                                'y': int(y_min),
                                'width': int(x_max - x_min),
                                'height': int(y_max - y_min)
                            }
                        })

        # Combine all text
        text = '\n'.join([word['text'] for word in words])

        # Get image metadata
        image_metadata = {
            'width': image.width,
            'height': image.height,
            'mode': image.mode,
            'format': image.format
        }

        return {
            'success': True,
            'text': text.strip(),
            'words': words,
            'avg_confidence': sum(w['confidence'] for w in words) / len(words) if words else 0,
            'format_info': format_info,
            'image_metadata': image_metadata
        }

    except Exception as e:
        return {
            'success': False,
            'error': str(e),
            'text': '',
            'words': [],
            'avg_confidence': 0
        }

def extract_id_info(text):
    """
    Extract specific ID information from OCR text

    Args:
        text (str): OCR extracted text

    Returns:
        dict: Parsed ID information
    """
    lines = [line.strip() for line in text.split('\n') if line.strip()]

    id_info = {
        'name': '',
        'id_number': '',
        'address': '',
        'birth_date': '',
        'other_info': []
    }

    # Simple pattern matching for common ID fields
    for line in lines:
        line_lower = line.lower()

        # Name detection (simplified)
        if any(keyword in line_lower for keyword in ['name', 'nombre', 'pangalan']):
            name_part = line.split(':')[-1].strip() if ':' in line else line
            if len(name_part) > 3:
                id_info['name'] = name_part

        # ID number detection
        if any(keyword in line_lower for keyword in ['id', 'number', 'no', 'license']):
            id_part = line.split(':')[-1].strip() if ':' in line else line
            if any(c.isdigit() for c in id_part):
                id_info['id_number'] = id_part

        # Birth date detection
        if any(keyword in line_lower for keyword in ['birth', 'born', 'fecha']):
            date_part = line.split(':')[-1].strip() if ':' in line else line
            if '/' in date_part or '-' in date_part:
                id_info['birth_date'] = date_part

        # Address detection
        if any(keyword in line_lower for keyword in ['address', 'direccion', 'address']):
            addr_part = line.split(':')[-1].strip() if ':' in line else line
            if len(addr_part) > 10:
                id_info['address'] = addr_part

        # Store other potentially useful info
        if len(line) > 5 and line not in [id_info['name'], id_info['id_number'], id_info['address'], id_info['birth_date']]:
            id_info['other_info'].append(line)

    return id_info

if __name__ == "__main__":
    # Test the OCR service
    if len(sys.argv) > 1:
        image_path = sys.argv[1]
        result = extract_text_from_image(image_path)

        if result['success']:
            print("OCR Results:")
            print(f"Extracted Text: {result['text']}")
            print(f"Average Confidence: {result['avg_confidence']:.2f}%")

            id_info = extract_id_info(result['text'])
            print("\nExtracted ID Information:")
            for key, value in id_info.items():
                print(f"{key}: {value}")
        else:
            print(f"OCR failed: {result['error']}")
    else:
        print("Usage: python ocr_service.py <image_path>")
        print("Or import and use the functions in your code.")

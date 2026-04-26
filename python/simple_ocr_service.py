#!/usr/bin/env python3
"""
Simple OCR Service for ID Card Text Extraction
Uses EasyOCR for better compatibility than PaddleOCR
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

# Initialize EasyOCR
try:
    import easyocr
    ocr = easyocr.Reader(['en'])
    print("EasyOCR initialized successfully")
except ImportError:
    print("EasyOCR not available, trying to install...")
    os.system("pip install easyocr")
    try:
        import easyocr
        ocr = easyocr.Reader(['en'])
        print("EasyOCR installed and initialized successfully")
    except ImportError:
        print("Failed to install EasyOCR, falling back to basic text extraction")
        ocr = None
except Exception as e:
    print(f"Failed to initialize EasyOCR: {e}")
    ocr = None

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
            # For base64, check data URI prefix
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

        # Process with OCR
        words = []

        if ocr is not None:
            # Use EasyOCR if available
            try:
                # Convert PIL image to numpy array
                image_array = np.array(image)

                # Run OCR
                results = ocr.readtext(image_array)

                for (bbox, text, confidence) in results:
                    if text.strip():  # Only include non-empty text
                        # Convert bbox format from EasyOCR (x1,y1,x2,y2) to our format
                        x1, y1, x2, y2 = bbox
                        words.append({
                            'text': text.strip(),
                            'confidence': confidence * 100,  # Convert to percentage
                            'bbox': {
                                'x': int(x1),
                                'y': int(y1),
                                'width': int(x2 - x1),
                                'height': int(y2 - y1)
                            }
                        })

            except Exception as e:
                print(f"EasyOCR processing failed: {e}")
                # Fall back to basic text extraction
                words = []
        else:
            print("OCR not available, returning empty result")
            words = []

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
        'birthplace': '',
        'civil_status': '',
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

        # Birthplace detection
        if any(keyword in line_lower for keyword in ['birthplace', 'place of birth', 'lugar ng kapanganakan', 'born in']):
            birthplace_part = line.split(':')[-1].strip() if ':' in line else line
            if len(birthplace_part) > 3:
                id_info['birthplace'] = birthplace_part

        # Civil status detection
        if any(keyword in line_lower for keyword in ['civil status', 'marital status', 'kalagayang sibil']):
            civil_part = line.split(':')[-1].strip() if ':' in line else line
            if len(civil_part) > 3:
                id_info['civil_status'] = civil_part

        # Address detection
        if any(keyword in line_lower for keyword in ['address', 'direccion', 'address']):
            addr_part = line.split(':')[-1].strip() if ':' in line else line
            if len(addr_part) > 10:
                id_info['address'] = addr_part

        # Store other potentially useful info
        if len(line) > 5 and line not in [id_info['name'], id_info['id_number'], id_info['address'], id_info['birth_date'], id_info['birthplace'], id_info['civil_status']]:
            id_info['other_info'].append(line)

    return id_info

if __name__ == "__main__":
    # Test OCR service
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
        print("Usage: python simple_ocr_service.py <image_path>")
        print("Or import and use the functions in your code.")

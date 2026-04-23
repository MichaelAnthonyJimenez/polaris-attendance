#!/usr/bin/env python3
"""
OCR Service for ID Card Text Extraction
Uses pytesseract for OCR functionality
"""

import os
import sys
import json
import base64
from io import BytesIO
from PIL import Image
import pytesseract

# Configure pytesseract path
# Try common installation paths
tesseract_paths = [
    r"C:\Program Files\Tesseract-OCR\tesseract.exe",
    r"C:\Tesseract-OCR\tesseract.exe",
    r"C:\Program Files (x86)\Tesseract-OCR\tesseract.exe",
    "tesseract"  # If in PATH
]

tesseract_path = None
for path in tesseract_paths:
    if os.path.exists(path) or path == "tesseract":
        tesseract_path = path
        break

if tesseract_path:
    pytesseract.pytesseract.tesseract_cmd = tesseract_path
    print(f"Using Tesseract at: {tesseract_path}")
else:
    print("Tesseract not found. Please install Tesseract OCR.")
    sys.exit(1)

def extract_text_from_image(image_data):
    """
    Extract text from an image using OCR
    
    Args:
        image_data: Base64 encoded image string or file path
    
    Returns:
        dict: Extracted text and metadata
    """
    try:
        # Handle base64 input
        if isinstance(image_data, str) and image_data.startswith('data:image'):
            # Extract base64 data
            image_data = image_data.split(',')[1]
            image_bytes = base64.b64decode(image_data)
            image = Image.open(BytesIO(image_bytes))
        elif isinstance(image_data, str) and os.path.exists(image_data):
            # Handle file path
            image = Image.open(image_data)
        else:
            raise ValueError("Invalid image data format")
        
        # Configure for better ID card OCR
        custom_config = r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz '
        
        # Extract text
        text = pytesseract.image_to_string(image, config=custom_config)
        
        # Get detailed data
        data = pytesseract.image_to_data(image, output_type=pytesseract.Output.DICT, config=custom_config)
        
        # Process results
        words = []
        for i in range(len(data['text'])):
            if data['text'][i].strip():
                words.append({
                    'text': data['text'][i],
                    'confidence': data['conf'][i],
                    'bbox': {
                        'x': data['left'][i],
                        'y': data['top'][i],
                        'width': data['width'][i],
                        'height': data['height'][i]
                    }
                })
        
        return {
            'success': True,
            'text': text.strip(),
            'words': words,
            'avg_confidence': sum(w['confidence'] for w in words) / len(words) if words else 0
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

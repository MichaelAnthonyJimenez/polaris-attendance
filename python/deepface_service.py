#!/usr/bin/env python3
"""
DeepFace Service for Facial Recognition
Provides face detection, verification, and analysis capabilities
"""

import os
import sys
import json
import base64
import cv2
import numpy as np
from io import BytesIO
from PIL import Image
from deepface import DeepFace
import tempfile

class DeepFaceService:
    def __init__(self):
        """Initialize the DeepFace service with default models"""
        self.models = ['VGG-Face', 'Facenet', 'Facenet512', 'OpenFace', 'DeepFace', 'ArcFace', 'Dlib']
        self.detector_backend = 'opencv'
        self.distance_metric = 'cosine'
        
    def base64_to_image(self, base64_string):
        """Convert base64 string to PIL Image"""
        if base64_string.startswith('data:image'):
            base64_string = base64_string.split(',')[1]
        
        image_bytes = base64.b64decode(base64_string)
        image = Image.open(BytesIO(image_bytes))
        return image
    
    def image_to_cv2(self, image):
        """Convert PIL Image to OpenCV format"""
        if isinstance(image, Image.Image):
            return cv2.cvtColor(np.array(image), cv2.COLOR_RGB2BGR)
        return image
    
    def detect_face(self, image_data, enforce_detection=True):
        """
        Detect faces in an image
        
        Args:
            image_data: Base64 encoded image string
            enforce_detection: Whether to enforce face detection
            
        Returns:
            dict: Face detection results
        """
        try:
            image = self.base64_to_image(image_data)
            img_array = self.image_to_cv2(image)
            
            # Detect faces
            faces = DeepFace.extract_faces(
                img_array,
                detector_backend=self.detector_backend,
                enforce_detection=enforce_detection
            )
            
            if not faces:
                return {
                    'success': False,
                    'error': 'No face detected',
                    'faces': []
                }
            
            # Process detected faces
            face_results = []
            for i, face in enumerate(faces):
                if face['confidence'] > 0.9:  # High confidence threshold
                    face_data = {
                        'face_index': i,
                        'confidence': face['confidence'],
                        'box': face['facial_area'],
                        'is_detected': True
                    }
                    face_results.append(face_data)
            
            return {
                'success': True,
                'faces': face_results,
                'total_faces': len(face_results)
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'faces': [],
                'total_faces': 0
            }
    
    def verify_faces(self, image1_data, image2_data, model_name='VGG-Face'):
        """
        Verify if two images contain the same person
        
        Args:
            image1_data: Base64 encoded first image
            image2_data: Base64 encoded second image
            model_name: Face recognition model to use
            
        Returns:
            dict: Verification results
        """
        try:
            img1 = self.base64_to_image(image1_data)
            img2 = self.base64_to_image(image2_data)
            
            img1_array = self.image_to_cv2(img1)
            img2_array = self.image_to_cv2(img2)
            
            # Perform verification
            result = DeepFace.verify(
                img1_path=img1_array,
                img2_path=img2_array,
                model_name=model_name,
                detector_backend=self.detector_backend,
                distance_metric=self.distance_metric
            )
            
            # Always expose raw confidence derived from distance.
            # The PHP layer applies project-specific acceptance thresholds.
            confidence = max(0.0, min(100.0, (1 - float(result['distance'])) * 100))

            return {
                'success': True,
                'verified': bool(result['verified']),
                'distance': result['distance'],
                'threshold': result['threshold'],
                'model': model_name,
                'confidence': confidence
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'verified': False,
                'distance': 0,
                'threshold': 0,
                'model': model_name,
                'confidence': 0
            }
    
    def analyze_face(self, image_data, actions=['age', 'gender', 'emotion', 'race']):
        """
        Analyze facial attributes
        
        Args:
            image_data: Base64 encoded image
            actions: List of analyses to perform
            
        Returns:
            dict: Face analysis results
        """
        try:
            image = self.base64_to_image(image_data)
            img_array = self.image_to_cv2(image)
            
            # Perform analysis
            result = DeepFace.analyze(
                img_array,
                actions=actions,
                detector_backend=self.detector_backend,
                enforce_detection=True
            )
            
            # Handle single face result
            if isinstance(result, list):
                result = result[0] if result else {}
            
            return {
                'success': True,
                'analysis': result,
                'actions_performed': actions
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'analysis': {},
                'actions_performed': actions
            }
    
    def find_similar_faces(self, image_data, db_path, model_name='VGG-Face', threshold=0.4):
        """
        Find similar faces in a database
        
        Args:
            image_data: Base64 encoded query image
            db_path: Path to face database
            model_name: Face recognition model
            threshold: Similarity threshold
            
        Returns:
            dict: Similar faces results
        """
        try:
            if not os.path.exists(db_path):
                return {
                    'success': False,
                    'error': f'Database path not found: {db_path}',
                    'matches': []
                }
            
            # Save image temporarily for processing
            image = self.base64_to_image(image_data)
            with tempfile.NamedTemporaryFile(suffix='.jpg', delete=False) as temp_file:
                image.save(temp_file.name)
                temp_path = temp_file.name
            
            try:
                # Find similar faces
                result = DeepFace.find(
                    img_path=temp_path,
                    db_path=db_path,
                    model_name=model_name,
                    detector_backend=self.detector_backend,
                    distance_metric=self.distance_metric,
                    threshold=threshold
                )
                
                matches = []
                if result is not None and len(result) > 0:
                    for _, row in result.iterrows():
                        if row['distance'] < threshold:
                            matches.append({
                                'identity': row['identity'],
                                'distance': row['distance'],
                                'confidence': max(0, (1 - row['distance']) * 100)
                            })
                
                return {
                    'success': True,
                    'matches': matches,
                    'total_matches': len(matches)
                }
                
            finally:
                # Clean up temporary file
                if os.path.exists(temp_path):
                    os.unlink(temp_path)
                    
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'matches': [],
                'total_matches': 0
            }
    
    def enroll_face(self, image_data, person_id, db_path):
        """
        Enroll a face in the database
        
        Args:
            image_data: Base64 encoded face image
            person_id: Unique identifier for the person
            db_path: Database path to store faces
            
        Returns:
            dict: Enrollment result
        """
        try:
            # Create person directory if it doesn't exist
            person_dir = os.path.join(db_path, str(person_id))
            os.makedirs(person_dir, exist_ok=True)
            
            # Save the face image
            image = self.base64_to_image(image_data)
            timestamp = int(time.time())
            filename = f"face_{timestamp}.jpg"
            filepath = os.path.join(person_dir, filename)
            
            image.save(filepath)
            
            # Verify the face was saved and detectable
            detection_result = self.detect_face(image_data)
            if not detection_result['success'] or detection_result['total_faces'] == 0:
                # Clean up if no face detected
                if os.path.exists(filepath):
                    os.unlink(filepath)
                return {
                    'success': False,
                    'error': 'No face detected in the provided image',
                    'filepath': None
                }
            
            return {
                'success': True,
                'filepath': filepath,
                'person_id': person_id,
                'faces_detected': detection_result['total_faces']
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'filepath': None
            }

# Service instance
deepface_service = DeepFaceService()

def main():
    """Command line interface for testing"""
    if len(sys.argv) < 2:
        print("Usage: python deepface_service.py <action> [options]")
        print("Actions: detect, analyze, verify, enroll, find-similar")
        print("Examples:")
        print("  python deepface_service.py detect <base64_image>")
        print("  python deepface_service.py analyze <base64_image>")
        print("  python deepface_service.py verify <base64_image1> <base64_image2>")
        print("  python deepface_service.py enroll <base64_image> <person_id> --db-path <path>")
        print("  python deepface_service.py find-similar <base64_image> --db-path <path>")
        return
    
    action = sys.argv[1]
    
    # Parse arguments
    args = sys.argv[2:]
    model = 'VGG-Face'
    db_path = None
    threshold = 0.4
    enforce_detection = True
    actions = ['age', 'gender', 'emotion', 'race']
    
    i = 0
    while i < len(args):
        if args[i] == '--model' and i + 1 < len(args):
            model = args[i + 1]
            i += 2
        elif args[i] == '--db-path' and i + 1 < len(args):
            db_path = args[i + 1]
            i += 2
        elif args[i] == '--threshold' and i + 1 < len(args):
            threshold = float(args[i + 1])
            i += 2
        elif args[i] == '--enforce-detection':
            enforce_detection = True
            i += 1
        elif args[i] == '--no-enforce-detection':
            enforce_detection = False
            i += 1
        elif args[i] == '--action' and i + 1 < len(args):
            if args[i + 1] not in actions:
                actions = [args[i + 1]]
            i += 2
        else:
            i += 1
    
    if action == 'detect':
        if len(args) < 1:
            print(json.dumps({'success': False, 'error': 'detect requires base64 image data'}))
            return
        
        image_data = args[0]
        result = deepface_service.detect_face(image_data, enforce_detection)
        print(json.dumps(result, indent=2))
    
    elif action == 'analyze':
        if len(args) < 1:
            print(json.dumps({'success': False, 'error': 'analyze requires base64 image data'}))
            return
        
        image_data = args[0]
        result = deepface_service.analyze_face(image_data, actions)
        print(json.dumps(result, indent=2))
    
    elif action == 'verify':
        if len(args) < 2:
            print(json.dumps({'success': False, 'error': 'verify requires two base64 image data strings'}))
            return
        
        img1_data = args[0]
        img2_data = args[1]
        result = deepface_service.verify_faces(img1_data, img2_data, model)
        print(json.dumps(result, indent=2))
    
    elif action == 'enroll':
        if len(args) < 2:
            print(json.dumps({'success': False, 'error': 'enroll requires base64 image data and person_id'}))
            return
        
        if not db_path:
            print(json.dumps({'success': False, 'error': 'enroll requires --db-path'}))
            return
        
        image_data = args[0]
        person_id = args[1]
        result = deepface_service.enroll_face(image_data, person_id, db_path)
        print(json.dumps(result, indent=2))
    
    elif action == 'find-similar':
        if len(args) < 1:
            print(json.dumps({'success': False, 'error': 'find-similar requires base64 image data'}))
            return
        
        if not db_path:
            print(json.dumps({'success': False, 'error': 'find-similar requires --db-path'}))
            return
        
        image_data = args[0]
        result = deepface_service.find_similar_faces(image_data, db_path, model, threshold)
        print(json.dumps(result, indent=2))
    
    else:
        print(json.dumps({'success': False, 'error': f'Unknown action: {action}'}))

if __name__ == "__main__":
    import time
    main()

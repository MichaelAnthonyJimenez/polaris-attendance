/**
 * Optiic.dev OCR Service Integration
 * Provides OCR functionality using Optiic.dev API
 */

class OptiicService {
    constructor(apiKey) {
        this.apiKey = apiKey;
        this.baseUrl = 'https://api.optiic.dev';
    }

    /**
     * Extract text from image using Optiic.dev OCR
     * @param {string} imageData - Base64 encoded image data
     * @returns {Promise} - OCR result
     */
    async extractTextFromImage(imageData) {
        try {
            // Remove data URL prefix if present
            const base64Data = imageData.includes('base64,') 
                ? imageData.split('base64,')[1] 
                : imageData;

            const response = await fetch(`${this.baseUrl}/ocr`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiKey}`
                },
                body: JSON.stringify({
                    image: base64Data,
                    language: 'en'
                })
            });

            if (!response.ok) {
                throw new Error(`Optiic.dev API error: ${response.status} ${response.statusText}`);
            }

            const result = await response.json();
            
            return {
                success: true,
                text: result.text || '',
                confidence: result.confidence || 0,
                words: result.words || [],
                avg_confidence: result.confidence || 0,
                processing_time: result.processing_time || 0
            };

        } catch (error) {
            console.error('Optiic.dev OCR Error:', error);
            return {
                success: false,
                error: error.message,
                text: '',
                words: [],
                avg_confidence: 0
            };
        }
    }

    /**
     * Check if the API key is configured
     * @returns {boolean}
     */
    isConfigured() {
        return this.apiKey && this.apiKey.length > 0;
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OptiicService;
} else if (typeof window !== 'undefined') {
    window.OptiicService = OptiicService;
}

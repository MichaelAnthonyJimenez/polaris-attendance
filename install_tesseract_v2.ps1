# Alternative Tesseract OCR Installation Script
Write-Host "Installing Tesseract OCR using alternative method..."

# Check if Python is available (for pip install)
try {
    $pythonVersion = python --version 2>$null
    Write-Host "Python found: $pythonVersion"
    
    # Install pytesseract and pillow via pip
    Write-Host "Installing pytesseract and pillow..."
    pip install pytesseract pillow
    Write-Host "Python packages installed successfully."
    
    # Download Tesseract binary directly
    Write-Host "Downloading Tesseract binary..."
    $tesseractPath = "C:\Tesseract-OCR"
    if (-not (Test-Path $tesseractPath)) {
        New-Item -ItemType Directory -Path $tesseractPath -Force
    }
    
    # Try downloading from a different source
    $downloadUrl = "https://github.com/UB-Mannheim/tesseract/releases/download/5.5.0/tesseract-ocr-w64-setup-5.5.0.20241114.exe"
    $installerPath = "$env:TEMP\tesseract-setup.exe"
    
    try {
        Invoke-WebRequest -Uri "https://github.com/UB-Mannheim/tesseract/releases/download/5.5.0/tesseract-ocr-w64-setup-5.5.0.20241114.exe" -OutFile $installerPath -UseBasicParsing
        Write-Host "Download completed. Installing..."
        
        # Run installer silently
        Start-Process -FilePath $installerPath -ArgumentList "/S", "/D=$tesseractPath" -Wait -NoNewWindow
        
        # Add to PATH
        $currentPath = [Environment]::GetEnvironmentVariable("PATH", "User")
        if ($currentPath -notlike "*$tesseractPath*") {
            [Environment]::SetEnvironmentVariable("PATH", "$currentPath;$tesseractPath", "User")
            Write-Host "Tesseract added to user PATH."
        }
        
        # Verify installation
        if (Test-Path "$tesseractPath\tesseract.exe") {
            Write-Host "Tesseract installed successfully at $tesseractPath"
            
            # Test the installation
            & "$tesseractPath\tesseract.exe" --version
        } else {
            Write-Host "Binary installation failed, but Python packages are available."
        }
    } catch {
        Write-Host "Binary installation failed: $_"
        Write-Host "Please install Tesseract manually from: https://github.com/UB-Mannheim/tesseract/releases"
    }
    
} catch {
    Write-Host "Python not found. Please install Python first or install Tesseract manually."
}

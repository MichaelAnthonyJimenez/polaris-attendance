# Tesseract OCR Installation Script
Write-Host "Installing Tesseract OCR..."

# Create installation directory
$installPath = "C:\Program Files\Tesseract-OCR"
if (-not (Test-Path $installPath)) {
    New-Item -ItemType Directory -Path $installPath -Force
}

# Download Tesseract (using a reliable source)
Write-Host "Downloading Tesseract..."
$downloadUrl = "https://digi.bib.uni-mannheim.de/tesseract/tesseract-ocr-w64-setup-5.5.0.20241114.exe"
$installerPath = "$env:TEMP\tesseract-installer.exe"

try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $installerPath -UseBasicParsing
    Write-Host "Download completed. Installing..."
    
    # Run installer silently
    Start-Process -FilePath $installerPath -ArgumentList "/S", "/D=$installPath" -Wait -NoNewWindow
    
    # Add to PATH
    $currentPath = [Environment]::GetEnvironmentVariable("PATH", "Machine")
    if ($currentPath -notlike "*$installPath*") {
        [Environment]::SetEnvironmentVariable("PATH", "$currentPath;$installPath", "Machine")
        Write-Host "Tesseract added to PATH. Please restart PowerShell to use it."
    }
    
    # Verify installation
    if (Test-Path "$installPath\tesseract.exe") {
        Write-Host "Tesseract installed successfully at $installPath"
    } else {
        Write-Host "Installation failed. Please install manually."
    }
} catch {
    Write-Host "Error downloading or installing Tesseract: $_"
} finally {
    # Cleanup
    if (Test-Path $installerPath) {
        Remove-Item $installerPath -Force
    }
}

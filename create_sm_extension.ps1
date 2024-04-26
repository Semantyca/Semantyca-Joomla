# Load necessary assembly for ZIP file manipulation
Add-Type -AssemblyName System.IO.Compression.FileSystem
Write-Host "Assembly loaded for ZIP file manipulation."

# Define source directory and destination ZIP file
$sourceDir = "com_semantycanm"
$zipFile = "com_semantycanm.zip"
$fullZipPath = Join-Path -Path (Get-Location) -ChildPath $zipFile
Write-Host "Source directory: $sourceDir"
Write-Host "Destination ZIP file: $fullZipPath"

# Check if the destination ZIP file exists and delete it if -Force is applied
if (Test-Path $fullZipPath)
{
    Remove-Item $fullZipPath -Force
    Write-Host "Existing ZIP file removed."

    # Check if file still exists after attempting to delete
    if (Test-Path $fullZipPath)
    {
        Write-Host "Failed to remove existing ZIP file, please check file permissions or if it is in use."
        exit
    }
}
else
{
    Write-Host "No existing ZIP file to remove."
}

# Create a new ZIP archive
try
{
    $zipArchive = [System.IO.Compression.ZipFile]::Open($fullZipPath, [System.IO.Compression.ZipArchiveMode]::Create)
    Write-Host "New ZIP archive opened."

    $files = Get-ChildItem -Path $sourceDir -Recurse | Where-Object { -not $_.PSIsContainer -and $_.FullName -notmatch '\\node_modules\\' }
    $totalFiles = $files.Count
    Write-Host "$totalFiles fonts found for compression."

    $currentFile = 0
    foreach ($file in $files)
    {
        $currentFile++
        $relativePath = $file.FullName.Substring((Get-Location).Path.Length + 1)
        Write-Host "Processing file ${currentFile} of ${totalFiles}: ${file.FullName}"

        # Show progress
        $progressParams = @{
            Activity = "Compressing fonts..."
            Status = "Adding ${file.FullName} to archive"
            PercentComplete = ($currentFile / $totalFiles) * 100
        }
        Write-Progress @progressParams

        # Add file to the archive
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zipArchive, $file.FullName, $relativePath, [System.IO.Compression.CompressionLevel]::Optimal)
    }

}
catch
{
    Write-Host "An error occurred: $_"
}
finally
{
    if ($null -ne $zipArchive)
    {
        $zipArchive.Dispose()
        Write-Host "ZIP archive closed."
    }
}

Write-Host "Compression complete. '$zipFile' has been created."

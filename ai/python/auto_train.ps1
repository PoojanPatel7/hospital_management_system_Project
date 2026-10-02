# Auto-Training & Installation Monitor for BHOOMA HMS AI
$jobName = "PyTorch_CUDA_Download"
$targetWhl = "D:\pip_cache\torch-2.6.0+cu124-cp312-cp312-win_amd64.whl"
$pythonExe = "D:\ai_env\Scripts\python.exe"

Write-Host "================================================================="
Write-Host "  BHOOMA HMS AI: LIVE DOWNLOAD & GPU TRAINING PIPELINE"
Write-Host "================================================================="

# 1. Monitor BITS Download
while ($true) {
    $job = Get-BitsTransfer | Where-Object { $_.DisplayName -eq $jobName }
    if ($null -eq $job) {
        if (Test-Path $targetWhl) {
            Write-Host "[OK] PyTorch CUDA package already downloaded!"
            break
        } else {
            Write-Host "[WAIT] Waiting for transfer job to register..."
            Start-Sleep -Seconds 5
            continue
        }
    }

    $state = $job.JobState
    $transferredMB = [math]::Round($job.BytesTransferred / 1MB, 2)
    $totalMB = 2415.0  # Approx total wheel size
    $percent = [math]::Round(($transferredMB / $totalMB) * 100, 1)

    Write-Host "[DOWNLOAD] Status: $state | Transferred: $transferredMB MB / $totalMB MB ($percent%)"

    if ($state -eq "Transferred") {
        Write-Host "`n[COMPLETING] Finalizing downloaded file..."
        Complete-BitsTransfer -BitsJob $job
        break
    } elseif ($state -eq "Error" -or $state -eq "TransientError") {
        Write-Host "[WARNING] Transient network issue encountered, retrying..."
        Resume-BitsTransfer -BitsJob $job -ErrorAction SilentlyContinue
    }

    Start-Sleep -Seconds 15
}

# 2. Install PyTorch CUDA Package
Write-Host "`n================================================================="
Write-Host "[INSTALL] Installing PyTorch CUDA 12.4 into D:\ai_env..."
Write-Host "================================================================="
& $pythonExe -m pip install $targetWhl --no-deps --force-reinstall

# 3. Verify CUDA GPU Detection
Write-Host "`n================================================================="
Write-Host "[VERIFY] Checking NVIDIA GeForce RTX 4060 GPU..."
Write-Host "================================================================="
& $pythonExe -c "import torch; print('CUDA Available:', torch.cuda.is_available(), '| Device:', torch.cuda.get_device_name(0) if torch.cuda.is_available() else 'None')"

# 4. Launch Fine-Tuning
Write-Host "`n================================================================="
Write-Host "[TRAIN] Launching Local GPU Fine-Tuning on RTX 4060..."
Write-Host "================================================================="
Set-Location "D:\xampp\htdocs\Hospital Management System\ai\python"
& $pythonExe fine_tune_local.py

Write-Host "`n================================================================="
Write-Host "DONE"
Write-Host "================================================================="

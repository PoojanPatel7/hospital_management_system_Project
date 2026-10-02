@echo off
title BHOOMA HMS AI - Local GPU Training (RTX 4060)
cd /d "%~dp0"

echo =================================================================
echo   BHOOMA HOSPITAL MANAGEMENT SYSTEM - LOCAL AI MODEL TRAINING
echo =================================================================
echo Target GPU: NVIDIA GeForce RTX 4060 (8GB VRAM)
echo Model     : Qwen2.5-3B-Instruct
echo Dataset   : 2,600 Claude/Gemini Style Clinical Examples
echo =================================================================
echo.

set "PIP_CACHE_DIR=D:\pip_cache"
set "HF_HOME=D:\huggingface"
set "TEMP=D:\temp"
set "TMP=D:\temp"

if not exist "D:\ai_env\Scripts\python.exe" (
    echo [ERROR] Virtual environment D:\ai_env not found.
    pause
    exit /b 1
)

echo [1/3] Generating Latest Database Dataset...
D:\ai_env\Scripts\python.exe dataset_generator.py

echo.
echo [2/3] Checking Dependencies & GPU Acceleration...
D:\ai_env\Scripts\python.exe -c "import torch; exit(0 if torch.cuda.is_available() else 1)" 2>nul
if %errorlevel% neq 0 (
    echo [NOTE] PyTorch CUDA driver not yet active. Checking local package...
    if exist "D:\pip_cache\torch-2.6.0+cu124-cp312-cp312-win_amd64.whl" (
        echo Installing PyTorch CUDA for RTX 4060...
        D:\ai_env\Scripts\python.exe -m pip install "D:\pip_cache\torch-2.6.0+cu124-cp312-cp312-win_amd64.whl" --no-deps
    ) else (
        echo PyTorch CUDA package is currently downloading in the background.
        echo Please wait for the background download to complete.
    )
) else (
    echo [OK] NVIDIA RTX 4060 CUDA Acceleration is Active!
)

echo.
echo [3/3] Launching Local GPU Training...
D:\ai_env\Scripts\python.exe fine_tune_local.py

echo.
echo =================================================================
echo   TRAINING COMPLETED!
echo =================================================================
pause

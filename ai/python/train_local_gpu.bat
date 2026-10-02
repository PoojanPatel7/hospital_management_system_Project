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
echo [2/3] Checking Dependencies (transformers, peft, trl, datasets)...
D:\ai_env\Scripts\python.exe -m pip install transformers peft trl datasets accelerate --quiet

echo.
echo [3/3] Launching Local GPU Training...
D:\ai_env\Scripts\python.exe fine_tune_local.py

echo.
echo =================================================================
echo   TRAINING COMPLETED!
echo =================================================================
pause

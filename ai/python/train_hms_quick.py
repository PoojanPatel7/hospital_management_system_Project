#!/usr/bin/env python3
"""
HMS AI One-Click Training & Verification Pipeline
=================================================
Automated script that:
1. Verifies dataset readiness (2,600 examples in ChatML & Alpaca format)
2. Checks GPU & CUDA hardware availability
3. Tests current Ollama custom model inference
4. Provides Google Colab 1-click fine-tuning setup
"""

import os
import sys
import json
import urllib.request

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

def check_dataset():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    chatml = os.path.join(base_dir, "hms_training_chatml.jsonl")
    alpaca = os.path.join(base_dir, "hms_training_alpaca.json")

    print("[1/4] Checking Training Datasets...")
    if os.path.exists(chatml) and os.path.exists(alpaca):
        with open(chatml, "r", encoding="utf-8") as f:
            lines = sum(1 for _ in f)
        print(f"  [OK] Found ChatML Dataset: {lines} training pairs")
        print(f"  [OK] Found Alpaca Dataset: {alpaca}")
        return True
    else:
        print("  [ERROR] Datasets missing. Please run dataset_generator.py first.")
        return False

def check_cuda():
    print("\n[2/4] Checking Deep Learning Hardware (CUDA / GPU)...")
    try:
        import torch
        if torch.cuda.is_available():
            gpu_name = torch.cuda.get_device_name(0)
            vram_gb = torch.cuda.get_device_properties(0).total_memory / (1024**3)
            print(f"  [OK] NVIDIA GPU Detected: {gpu_name} ({vram_gb:.1f} GB VRAM)")
            return True
        else:
            print("  [NOTE] No CUDA GPU detected on local environment. (Training will run on CPU or Free Google Colab GPU).")
            return False
    except ImportError:
        print("  [NOTE] PyTorch not yet installed in local Python environment.")
        return False

def check_ollama_model():
    print("\n[3/4] Checking Ollama Custom Model ('hms-ai:latest')...")
    try:
        req = urllib.request.Request("http://localhost:11434/api/tags")
        with urllib.request.urlopen(req, timeout=5) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            models = [m.get("name") for m in data.get("models", [])]
            if any("hms-ai" in m for m in models):
                print(f"  [OK] Model 'hms-ai:latest' is actively registered and live in Ollama!")
                return True
            else:
                print(f"  [WARNING] 'hms-ai' not found in models: {models}")
                return False
    except Exception as e:
        print(f"  [ERROR] Could not connect to Ollama: {e}")
        return False

def print_colab_instructions():
    print("\n[4/4] Free Google Colab 1-Click Fine-Tuning Guide:")
    print("=" * 65)
    print("To fine-tune on a FREE NVIDIA T4 GPU (15-20 minutes):")
    print("1. Open Google Colab: https://colab.research.google.com")
    print("2. Select Runtime -> Change runtime type -> T4 GPU")
    print("3. Run the following cell:")
    print("""
!pip install unsloth "xformers<0.0.28" "trl<0.9.0" peft accelerate bitsandbytes
from unsloth import FastLanguageModel
import torch

max_seq_length = 2048
model, tokenizer = FastLanguageModel.from_pretrained(
    model_name = "unsloth/Qwen2.5-3B-Instruct",
    max_seq_length = max_seq_length,
    load_in_4bit = True,
)

# Upload 'hms_training_chatml.jsonl' and start training!
print("Model loaded & ready for fine-tuning!")
""")
    print("=" * 65)

def main():
    print("=" * 65)
    print("[HMS AI] System Training & Model Readiness Verification")
    print("=" * 65)
    check_dataset()
    check_cuda()
    check_ollama_model()
    print_colab_instructions()

if __name__ == "__main__":
    main()

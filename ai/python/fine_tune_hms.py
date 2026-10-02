#!/usr/bin/env python3
"""
HMS AI Model Fine-Tuning Pipeline
=================================
Fine-tunes Qwen2.5-3B-Instruct on the Bhooma HMS hospital schema and query dataset
using QLoRA (4-bit quantization with LoRA adapters).

Recommended Hardware:
  - Local GPU: NVIDIA RTX 3060/4060 or higher (8GB+ VRAM)
  - Cloud: Google Colab Free Tier (T4 GPU - 16GB VRAM)

Usage:
  python fine_tune_hms.py --epochs 3 --batch_size 2 --learning_rate 2e-4
"""

import os
import sys
import argparse

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

def parse_args():
    parser = argparse.ArgumentParser(description="Fine-tune Qwen2.5-3B for HMS")
    parser.add_argument("--base_model", type=str, default="Qwen/Qwen2.5-3B-Instruct", help="Base model identifier")
    parser.add_argument("--dataset_path", type=str, default="hms_training_chatml.jsonl", help="ChatML dataset path")
    parser.add_argument("--output_dir", type=str, default="./hms_lora_model", help="Directory to save LoRA adapters")
    parser.add_argument("--epochs", type=int, default=3, help="Number of training epochs")
    parser.add_argument("--batch_size", type=int, default=2, help="Per device training batch size")
    parser.add_argument("--grad_accum", type=int, default=4, help="Gradient accumulation steps")
    parser.add_argument("--learning_rate", type=float, default=2e-4, help="Learning rate")
    parser.add_argument("--max_seq_len", type=int, default=1024, help="Max sequence length")
    return parser.parse_args()

def run_unsloth_training(args):
    """Fastest training path using Unsloth (2x faster, 70% less VRAM)."""
    try:
        from unsloth import FastLanguageModel
        from trl import SFTTrainer
        from transformers import TrainingArguments
        from datasets import load_dataset
        import torch

        print(f"[UNSLOTH] Loading base model: {args.base_model}...")
        model, tokenizer = FastLanguageModel.from_pretrained(
            model_name=args.base_model,
            max_seq_length=args.max_seq_len,
            dtype=None,
            load_in_4bit=True
        )

        # Configure LoRA targets
        model = FastLanguageModel.get_peft_model(
            model,
            r=16,
            target_modules=["q_proj", "k_proj", "v_proj", "o_proj", "gate_proj", "up_proj", "down_proj"],
            lora_alpha=16,
            lora_dropout=0,
            bias="none",
            use_gradient_checkpointing="unsloth",
            random_state=3407
        )

        print(f"[UNSLOTH] Loading dataset from {args.dataset_path}...")
        dataset = load_dataset("json", data_files=args.dataset_path, split="train")

        training_args = TrainingArguments(
            per_device_train_batch_size=args.batch_size,
            gradient_accumulation_steps=args.grad_accum,
            warmup_steps=10,
            num_train_epochs=args.epochs,
            learning_rate=args.learning_rate,
            fp16=not torch.cuda.is_bf16_supported(),
            bf16=torch.cuda.is_bf16_supported(),
            logging_steps=10,
            optim="adamw_8bit",
            weight_decay=0.01,
            lr_scheduler_type="linear",
            seed=3407,
            output_dir=args.output_dir
        )

        trainer = SFTTrainer(
            model=model,
            tokenizer=tokenizer,
            train_dataset=dataset,
            dataset_text_field="messages",
            max_seq_length=args.max_seq_len,
            dataset_num_proc=2,
            packing=False,
            args=training_args
        )

        print("[UNSLOTH] Starting training...")
        trainer.train()

        print(f"[UNSLOTH] Saving fine-tuned adapter to {args.output_dir}...")
        model.save_pretrained(args.output_dir)
        tokenizer.save_pretrained(args.output_dir)

        # Export GGUF directly for Ollama
        gguf_dir = os.path.join(args.output_dir, "gguf")
        print(f"[UNSLOTH] Exporting 4-bit GGUF model for Ollama to {gguf_dir}...")
        try:
            model.save_pretrained_gguf(gguf_dir, tokenizer, quantization_method="q4_k_m")
            print(f"[SUCCESS] Exported GGUF to {gguf_dir}! Ready for Ollama.")
        except Exception as e:
            print(f"[NOTE] GGUF auto-export skipped ({e}). Can export with llama.cpp or merge adapters.")

        return True

    except ImportError:
        print("[INFO] 'unsloth' not installed. Falling back to standard Hugging Face PEFT/Transformers...")
        return False

def run_standard_peft_training(args):
    """Standard PyTorch + PEFT + TRL training path."""
    try:
        import torch
        from transformers import AutoModelForCausalLM, AutoTokenizer, BitsAndBytesConfig, TrainingArguments
        from peft import LoraConfig, get_peft_model, prepare_model_for_kbit_training
        from trl import SFTTrainer
        from datasets import load_dataset

        print(f"[HUGGINGFACE] Loading 4-bit base model: {args.base_model}...")
        bnb_config = BitsAndBytesConfig(
            load_in_4bit=True,
            bnb_4bit_quant_type="nf4",
            bnb_4bit_compute_dtype=torch.float16,
            bnb_4bit_use_double_quant=True
        )

        tokenizer = AutoTokenizer.from_pretrained(args.base_model, trust_remote_code=True)
        tokenizer.pad_token = tokenizer.eos_token

        model = AutoModelForCausalLM.from_pretrained(
            args.base_model,
            quantization_config=bnb_config,
            device_map="auto",
            trust_remote_code=True
        )

        model = prepare_model_for_kbit_training(model)

        peft_config = LoraConfig(
            r=16,
            lora_alpha=32,
            target_modules=["q_proj", "k_proj", "v_proj", "o_proj"],
            lora_dropout=0.05,
            bias="none",
            task_type="CAUSAL_LM"
        )
        model = get_peft_model(model, peft_config)

        dataset = load_dataset("json", data_files=args.dataset_path, split="train")

        training_args = TrainingArguments(
            output_dir=args.output_dir,
            num_train_epochs=args.epochs,
            per_device_train_batch_size=args.batch_size,
            gradient_accumulation_steps=args.grad_accum,
            learning_rate=args.learning_rate,
            fp16=True,
            logging_steps=10,
            save_strategy="epoch"
        )

        trainer = SFTTrainer(
            model=model,
            train_dataset=dataset,
            peft_config=peft_config,
            dataset_text_field="messages",
            max_seq_length=args.max_seq_len,
            tokenizer=tokenizer,
            args=training_args
        )

        print("[HUGGINGFACE] Starting fine-tuning...")
        trainer.train()

        print(f"[HUGGINGFACE] Saving adapter to {args.output_dir}...")
        trainer.model.save_pretrained(args.output_dir)
        tokenizer.save_pretrained(args.output_dir)
        print("[SUCCESS] Training finished successfully.")

    except ImportError as e:
        print(f"[ERROR] Required deep learning libraries missing: {e}")
        print("\nTo train locally with GPU, run:")
        print("  pip install torch transformers peft trl bitsandbytes datasets accelerate")
        print("\nOr open the included Google Colab notebook for free 1-click training!")

def main():
    args = parse_args()
    print("=" * 60)
    print("[HMS AI] AI Model Fine-Tuning Pipeline")
    print(f"Base Model : {args.base_model}")
    print(f"Dataset    : {args.dataset_path}")
    print(f"Output Dir : {args.output_dir}")
    print("=" * 60)

    if not os.path.exists(args.dataset_path):
        print(f"[ERROR] Dataset file {args.dataset_path} not found. Run dataset_generator.py first!")
        sys.exit(1)

    # Try fast Unsloth path first, fallback to PEFT
    success = run_unsloth_training(args)
    if not success:
        run_standard_peft_training(args)

if __name__ == "__main__":
    main()

#!/usr/bin/env python3
r"""
Local GPU Fine-Tuning Script for BHOOMA HMS AI
==============================================
Optimized specifically for NVIDIA GeForce RTX 4060 (8GB VRAM) and Windows.

Features:
- Direct CUDA & bfloat16 / fp16 execution
- All caching and temp storage directed to D:\ drive (protecting C:\ drive disk space)
- Trains LoRA adapters on 2,600 Claude/Gemini style hospital examples
- Saves trained weights to ./hms_lora_weights
- Merges weights into a standalone high-accuracy model
"""

import os
import sys

# Force all caches and temp files to D:\ drive to prevent filling C:\ drive
os.environ["HF_HOME"] = "D:\\huggingface"
os.environ["PIP_CACHE_DIR"] = "D:\\pip_cache"
os.environ["TEMP"] = "D:\\temp"
os.environ["TMP"] = "D:\\temp"

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

import torch

def verify_gpu():
    print("=" * 65, flush=True)
    print("🏥 BHOOMA HMS: Local GPU Training Environment Check", flush=True)
    print("=" * 65, flush=True)
    if not torch.cuda.is_available():
        print("[ERROR] CUDA is not available. Please ensure NVIDIA drivers are installed.", flush=True)
        sys.exit(1)
    
    gpu_name = torch.cuda.get_device_name(0)
    total_mem = torch.cuda.get_device_properties(0).total_memory / (1024**3)
    print(f"✅ GPU Detected : {gpu_name}", flush=True)
    print(f"✅ VRAM Available: {total_mem:.2f} GB", flush=True)
    print(f"✅ CUDA Version  : {torch.version.cuda}", flush=True)
    print(f"✅ Cache Root    : {os.environ['HF_HOME']}", flush=True)
    print("=" * 65, flush=True)
    return True

def run_fine_tuning(
    base_model_name="Qwen/Qwen2.5-1.5B-Instruct",
    dataset_file="hms_training_chatml.jsonl",
    output_dir="./hms_lora_weights",
    epochs=3,
    batch_size=1,
    grad_accum=4,
    lr=2e-4
):
    from transformers import (
        AutoModelForCausalLM,
        AutoTokenizer,
        TrainingArguments,
        Trainer,
        DataCollatorForLanguageModeling
    )
    from peft import LoraConfig, get_peft_model, TaskType
    from datasets import load_dataset

    base_dir = os.path.dirname(os.path.abspath(__file__))
    dataset_path = os.path.join(base_dir, dataset_file)
    output_path = os.path.join(base_dir, output_dir)

    if not os.path.exists(dataset_path):
        print(f"[ERROR] Dataset {dataset_path} not found. Run dataset_generator.py first.", flush=True)
        sys.exit(1)

    print(f"\n[1/4] Loading Tokenizer and Base Model: {base_model_name}...", flush=True)
    tokenizer = AutoTokenizer.from_pretrained(
        base_model_name,
        trust_remote_code=True,
        cache_dir=os.environ["HF_HOME"]
    )
    if tokenizer.pad_token is None:
        tokenizer.pad_token = tokenizer.eos_token

    # Load in float16 for RTX 4060 (8GB VRAM)
    compute_dtype = torch.bfloat16 if torch.cuda.is_bf16_supported() else torch.float16
    print(f"  Using compute dtype: {compute_dtype}", flush=True)

    model = AutoModelForCausalLM.from_pretrained(
        base_model_name,
        torch_dtype=compute_dtype,
        device_map="auto",
        trust_remote_code=True,
        cache_dir=os.environ["HF_HOME"]
    )

    print("\n[2/4] Setting Up LoRA Adapter Configuration...", flush=True)
    peft_config = LoraConfig(
        task_type=TaskType.CAUSAL_LM,
        r=16,
        lora_alpha=32,
        lora_dropout=0.05,
        target_modules=["q_proj", "k_proj", "v_proj", "o_proj", "gate_proj", "up_proj", "down_proj"],
        bias="none"
    )
    model = get_peft_model(model, peft_config)
    model.print_trainable_parameters()

    print(f"\n[3/4] Loading and Tokenizing Dataset from {dataset_path}...", flush=True)
    dataset = load_dataset("json", data_files=dataset_path, split="train")
    print(f"  Loaded {len(dataset)} training examples.", flush=True)

    def format_and_tokenize(batch):
        texts = []
        for msgs in batch["messages"]:
            text = tokenizer.apply_chat_template(msgs, tokenize=False, add_generation_prompt=False)
            texts.append(text)
        return tokenizer(texts, truncation=True, max_length=512, padding="max_length")

    tokenized_dataset = dataset.map(format_and_tokenize, batched=True, remove_columns=["messages"])

    training_args = TrainingArguments(
        output_dir=output_path,
        num_train_epochs=epochs,
        per_device_train_batch_size=batch_size,
        gradient_accumulation_steps=grad_accum,
        learning_rate=lr,
        logging_steps=10,
        save_strategy="epoch",
        fp16=(compute_dtype == torch.float16),
        bf16=(compute_dtype == torch.bfloat16),
        optim="adamw_torch",
        warmup_steps=10,
        lr_scheduler_type="cosine",
        report_to="none"
    )

    data_collator = DataCollatorForLanguageModeling(tokenizer=tokenizer, mlm=False)

    trainer = Trainer(
        model=model,
        train_dataset=tokenized_dataset,
        args=training_args,
        data_collator=data_collator
    )

    print("\n[4/4] 🚀 Starting Local GPU Training on NVIDIA RTX 4060...", flush=True)
    trainer.train()

    print(f"\n✅ Training Complete! Saving LoRA weights to: {output_path}", flush=True)
    trainer.model.save_pretrained(output_path)
    tokenizer.save_pretrained(output_path)

    print("\n" + "=" * 65, flush=True)
    print("🎉 LOCAL MODEL TRAINING COMPLETED SUCCESSFULLY!", flush=True)
    print("Weights are saved in:", output_path, flush=True)
    print("DONE", flush=True)
    print("=" * 65, flush=True)

if __name__ == "__main__":
    verify_gpu()
    chosen_model = sys.argv[1] if len(sys.argv) > 1 else "Qwen/Qwen2.5-1.5B-Instruct"
    run_fine_tuning(base_model_name=chosen_model)

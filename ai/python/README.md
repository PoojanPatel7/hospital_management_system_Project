# 🏥 Bhooma HMS AI Training & Fine-Tuning Suite

This directory contains the Python toolkit to generate domain datasets, fine-tune models, and deploy dedicated hospital AI models for the Bhooma Hospital Management System.

---

## 📌 Architecture: Which Approach is Best?

### The Truth About Hospital AI
| Task | Best Engine | Why? |
| :--- | :--- | :--- |
| **Live Database Actions** (Admit Bed, Book Appointment, Mark Attendance) | **Deterministic Form & SQL Engine** (PHP + MySQL) | **Zero Hallucination.** Patient admissions and finances require 100% ACID transactional safety. |
| **Natural Language Understanding & SQL Generation** | **Custom Fine-Tuned Model (`hms-ai:3b`)** | Understands 34 HMS tables, column aliases (`first_name`, `staff_code`), and medical abbreviations without massive prompt overhead. |

**The Winning Solution:** Combine both!
1. The **Deterministic Action Engine** handles live forms and transactions.
2. The **Custom `hms-ai` Model** handles conversational intent and generates perfect MySQL read queries.

---

## 🚀 Quickstart

### Step 1: Generate the Training Dataset
Generates 1,250+ hospital domain examples in both **Alpaca** and **ChatML** format:
```bash
python dataset_generator.py
```
Outputs:
* `hms_training_chatml.jsonl` (ChatML format for Hugging Face & Axolotl)
* `hms_training_alpaca.json` (Instruction/Input/Output for Unsloth & LLaMA-Factory)

---

### Step 2: Build the Local Custom Model in Ollama
We created an optimized `Modelfile` with pre-aligned parameters, schema instructions, and stop tokens:
```bash
ollama create hms-ai -f Modelfile
```

---

### Step 3: Run Fine-Tuning (Optional GPU Training)
To train LoRA adapters on an NVIDIA GPU (or on Google Colab with free T4 GPU):

#### Requirements:
```bash
pip install torch transformers peft trl bitsandbytes datasets
```

#### Run Training:
```bash
python fine_tune_hms.py --epochs 3 --batch_size 2 --learning_rate 2e-4
```
* **With Unsloth:** 2-5x faster training with 70% lower VRAM.
* **Outputs:** `./hms_lora_model/` containing the trained LoRA adapter and quantized `.gguf` weights.

---

### Step 4: Benchmark & Evaluate
Test latency and response accuracy across real clinical questions:
```bash
python test_offline_eval.py
```
Measures response speed and verifies direct answers and SQL output syntax.

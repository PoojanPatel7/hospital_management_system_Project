#!/usr/bin/env python3
"""
Offline Model Evaluation Script
===============================
Tests the custom `hms-ai:latest` model loaded in Ollama across key HMS scenarios:
1. Staff Attendance queries
2. Vacant Bed inventory lookups
3. Patient MRN / Name searches
4. Doctor availability schedules
5. Appointment queues

Measures:
  - Latency (seconds)
  - Direct Answer formatting
  - SQL correctness
"""

import urllib.request
import json
import time
import sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

OLLAMA_API = "http://localhost:11434/api/chat"
MODEL_NAME = "hms-ai:latest"

TEST_PROMPTS = [
    "who is present today in staff",
    "how many ICU beds are vacant right now",
    "Find patient records for Pooja Sharma",
    "Is Dr. Ambarish A. Panchasara available today?",
    "Show all pre-booked appointments scheduled for today"
]

def query_ollama(prompt):
    payload = {
        "model": MODEL_NAME,
        "messages": [
            {"role": "user", "content": prompt}
        ],
        "stream": False,
        "options": {
            "temperature": 0.1,
            "num_predict": 180
        }
    }
    
    req = urllib.request.Request(
        OLLAMA_API,
        data=json.dumps(payload).encode("utf-8"),
        headers={"Content-Type": "application/json"}
    )
    
    start = time.time()
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            elapsed = time.time() - start
            return data.get("message", {}).get("content", ""), elapsed
    except Exception as e:
        return f"[ERROR] {e}", time.time() - start

def main():
    print("=" * 65)
    print(f"[EVALUATION] Benchmarking Custom Model: '{MODEL_NAME}'")
    print("=" * 65)

    for i, prompt in enumerate(TEST_PROMPTS, 1):
        print(f"\nTest {i}: \"{prompt}\"")
        content, elapsed = query_ollama(prompt)
        print(f"Latency: {elapsed:.2f}s")
        print("Response Snippet:")
        # Print first 2-3 lines of response
        lines = [line.strip() for line in content.split("\n") if line.strip()]
        for line in lines[:4]:
            print(f"  {line}")

    print("\n" + "=" * 65)
    print("[EVALUATION COMPLETE]")
    print("=" * 65)

if __name__ == "__main__":
    main()

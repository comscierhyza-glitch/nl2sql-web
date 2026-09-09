<?php

// 1. OpenRouter Free Key
define("OPENROUTER_API_KEY", getenv("OPENROUTER_API_KEY") ?: "YOUR_API_KEY_HERE");
define("OPENROUTER_MODEL", "openrouter/free");

// 2. Groq Ultra-Fast Free Key (14,400 req/day)
define("GROQ_API_KEY", getenv("GROQ_API_KEY") ?: "YOUR_API_KEY_HERE");
define("GROQ_MODEL", "llama-3.3-70b-versatile");

// 3. Google AI Studio Gemini Free Key (1,500 req/day)
define("GEMINI_API_KEY", getenv("GEMINI_API_KEY") ?: "YOUR_API_KEY_HERE");
define("GEMINI_MODEL", "gemini-2.0-flash");

?>
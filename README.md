# DarkAI

**DarkAI** — an open-source platform for creating, editing and transforming images and short videos using AI.  
This repository provides a lightweight PHP + frontend demo to upload media, call AI endpoints and display generated results.

---

## 🔥 Project Overview
DarkAI aims to be a compact, self-hostable toolkit that demonstrates a practical web UI for common AI media tasks:
- Image → Video (generate short clips from a single image + prompt)  
- Text → Video (generate short clip from a text prompt)  
- Image editing & generation (tools named _gemini_ and _flux_ in the UI)  
- Minimal PHP upload handler and a responsive front-end UI (HTML/CSS/JS)

This repository is intended as a base for customization, integration with AI backends, and experimentation.

---

## ✅ Key Features
- Simple drag-and-drop / click-to-upload image uploader (PHP backend).  
- Multi-language UI (Arabic / English / Russian / Chinese) out of the box.  
- Client-side preview and inline playback for generated videos & images.  
- Built-in API hooks for `img-to-video`, `text-to-video`, `gemini-img`, and `flux` endpoints (adjustable).  
- Lightweight, single-file frontend layout ideal for embedding or quick demos.

---

## ⚙️ Requirements
- Web server with PHP 7.4+ (Apache or Nginx + PHP-FPM)  
- `file_uploads = On` in `php.ini`  
- Writable `uploads/` directory (or let the script create it)  
- Optional: HTTPS and reverse proxy (Cloudflare recommended for production)

Don't forget to subscribe to our channel and support it on Telegram. 

- Telegram channel: [@DarkAIx](https://t.me/DarkAIx)
- Developer: [@sii_3](https://t.me/sii_3)
- Support: [@zDarkAI](https://t.me/zDarkAI)
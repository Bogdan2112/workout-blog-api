# Repwise — backend API

Proiectele sunt separate:

- `C:\workout-blog-api`: Laravel API, pornit cu `php artisan serve`.
- `C:\repwise-frontend`: React, pornit cu `npm run dev` în alt terminal.

Deschide interfața la adresa afișată de Vite. Frontendul folosește `VITE_API_URL=http://127.0.0.1:8000/api` în propriul `.env`. Backendul permite cereri CORS de la `localhost:5173` și `127.0.0.1:5173`; pentru altă adresă configurează `FRONTEND_URL` în `.env` din backend.

Laravel răspunde la `/` cu starea API-ului și nu servește interfața React. Nu este necesar `npm run dev` sau `npm run build` în backend pentru folosirea API-ului. Cheia `WORKOUTX_API_KEY` rămâne doar în backend.

Verificare backend: `php artisan test --compact`. Verificare frontend: `npm run build` în `C:\repwise-frontend`.

---
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## Best project in ze world


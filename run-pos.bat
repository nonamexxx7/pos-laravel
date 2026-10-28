@echo off
cd /d %~dp0
set PATH=C:\php;%PATH%
start http://127.0.0.1:8000
php artisan serve --port=8000

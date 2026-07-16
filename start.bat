@echo off
setlocal
rem ============================================================
rem  PH Catalogo - sobe o app com dois cliques (Windows)
rem
rem  O que faz:
rem    1. localiza o PHP (PATH ou instalacao padrao do Laragon)
rem    2. importa database\dump.sql se o banco ainda nao existir
rem    3. sobe o servidor em http://localhost:8000 e abre o navegador
rem
rem  Requisitos: PHP 8+ e MySQL/MariaDB (Laragon ou XAMPP resolvem)
rem ============================================================

set "PORTA=8000"
cd /d "%~dp0"

rem ---- 1. localizar o PHP -------------------------------------
set "PHP_EXE=php"
where php >nul 2>nul
if errorlevel 1 (
    for /d %%D in ("C:\laragon\bin\php\php-*") do set "PHP_EXE=%%D\php.exe"
)
"%PHP_EXE%" -v >nul 2>nul
if errorlevel 1 (
    echo [ERRO] PHP nao encontrado. Instale o Laragon ^(https://laragon.org^)
    echo        ou adicione o PHP ao PATH e rode este script de novo.
    pause
    exit /b 1
)

rem ---- 2. importar o banco se ainda nao existir ---------------
set "MYSQL_EXE=mysql"
where mysql >nul 2>nul
if errorlevel 1 (
    for /d %%D in ("C:\laragon\bin\mysql\mysql-*") do set "MYSQL_EXE=%%D\bin\mysql.exe"
)
"%MYSQL_EXE%" -u root -e "USE ph_catalogo" >nul 2>nul
if errorlevel 1 (
    echo Banco ph_catalogo nao encontrado. Importando database\dump.sql ...
    "%MYSQL_EXE%" -u root < database\dump.sql
    if errorlevel 1 (
        echo [AVISO] Import automatico falhou. O MySQL esta rodando?
        echo         Importe manualmente:  mysql -u root ^< database\dump.sql
        echo         ^(usuario/senha diferentes? copie .env.example para .env e ajuste^)
    ) else (
        echo Banco importado com sucesso.
    )
)

rem ---- 3. subir o servidor ------------------------------------
echo.
echo Catalogo no ar: http://localhost:%PORTA%   (Ctrl+C encerra)
start "" "http://localhost:%PORTA%"
"%PHP_EXE%" -S localhost:%PORTA% -t public

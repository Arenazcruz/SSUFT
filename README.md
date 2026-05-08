# SSUFT

Aplicacion web basada en Laravel.

## Stack y versiones

- Framework: Laravel `13.1.1`
- PHP requerido: `^8.3` (recomendado `8.4.18`)
- Composer recomendado: `2.9.5+`
- Node.js recomendado: `24.13.0` (LTS actual o superior)
- npm recomendado: `11.6.2+`
- Base de datos por defecto: PostgreSQL (`pgsql`)

## Requisitos previos

Asegurate de tener instalado:

- PHP 8.3 o superior
- Composer 2
- Node.js y npm
- Git
- PostgreSQL 14+ (recomendado 16+)
- Extension PHP `pdo_pgsql` habilitada

## Ejecucion con Docker

1. Crear archivo de entorno para Docker:

```powershell
Copy-Item .env.docker.example .env
```

2. Construir y levantar contenedores:

```bash
docker compose up --build -d
```

3. Generar clave de aplicacion:

```bash
docker compose exec app php artisan key:generate
```

4. Ejecutar migraciones:

```bash
docker compose exec app php artisan migrate
```

La app quedara disponible en:

- `http://localhost:8000`

Comandos utiles en Docker:

```bash
# Instalar dependencias frontend
docker compose exec app npm install

# Levantar Vite en modo desarrollo
docker compose exec app npm run dev -- --host 0.0.0.0 --port 5173

# Ejecutar pruebas
docker compose exec app php artisan test
```

## Instalacion del proyecto

1. Clonar repositorio:

```bash
git clone https://github.com/Arenazcruz/SSUFT.git
cd SSUFT
```

2. Instalar dependencias PHP:

```bash
composer install
```

3. Crear archivo de entorno:

```bash
cp .env.example .env
```

En Windows PowerShell, si `cp` no funciona:

```powershell
Copy-Item .env.example .env
```

4. Generar clave de aplicacion:

```bash
php artisan key:generate
```

5. Crear base de datos en PostgreSQL:

```bash
# Ejemplo con psql
createdb -U postgres ssuft
```

6. Configurar conexion de base de datos en `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ssuft
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

7. Ejecutar migraciones:

```bash
php artisan migrate
```

8. Instalar dependencias frontend:

```bash
npm install
```

## Ejecucion en desarrollo

### Opcion 1 (recomendada)

Ejecuta backend + cola + logs + Vite con un solo comando:

```bash
composer dev
```

### Opcion 2 (manual)

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

La app estara disponible en:

- `http://127.0.0.1:8000`

## Build para produccion

```bash
npm run build
php artisan optimize
```

## Pruebas

```bash
php artisan test
```

O usando script de Composer:

```bash
composer test
```

## Flujo de ramas

Este repositorio usa dos ramas principales:

- `master`: version estable/final para despliegue
- `developer`: integracion de cambios y actualizaciones

Flujo sugerido:

1. Crear feature branch desde `developer`
2. Hacer merge de feature branch a `developer`
3. Pasar a `master` cuando este validado para release

## Comandos utiles

- Limpiar cache de configuracion:

```bash
php artisan config:clear
```

- Reiniciar cache de aplicacion:

```bash
php artisan optimize:clear
```

- Ver rutas:

```bash
php artisan route:list
```

## Notas

- `vendor/` y `node_modules/` no se versionan.
- No subas credenciales reales en `.env`.

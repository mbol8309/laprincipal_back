# Generar controladores y modelos en Laravel a partir de una base de datos existente, puedes utilizar una herramienta llamada "reverse engineering" o "ingeniería inversa".

## Instalar paquete "reliese/laravel"
composer require reliese/laravel --dev

## Add the models.php configuration file to your config directory and clear the config cache:
php artisan vendor:publish --tag=reliese-models

# Let's refresh our config cache just in case
php artisan config:clear

## Generar modelos a partir de la base de datos, antes de ejecutar este comando se debe excluir las tablas que no se desee importar en el fichero "config\models.php", por ejemplo "'migrations','users','settings','password_reset_tokens','personal_access_tokens','failed_jobs'":
php artisan code:models

## Como nomenclatura de las tablas tendremos lo siguiente:
- sin prefijo: tablas por defecto de laravel
- prefijo "sys_": tablas del sistema (users, logs, roles, etc)
- prefijo "bs_": tablas del negocio

# Json call example:
/generic/getAll
{
    "model":"libro",
    "per_page":10,
    "page":1,
    "filters": {"biblioteca.nombre":"Nacional"},
    "with": ["biblioteca","autores"]
}

/generic/getById
{
    "id":4,
    "model":"libro",
    "with": ["biblioteca","autore"]
}
response:
{
    "data": {
        "id": 4,
        "titulo": "El viejo y el mar",
        "descripcion": "Novela corta",
        "fecha_publicacion": "1952-09-01",
        "biblioteca_id": 1,
        "autor_id": 4,
        "biblioteca": {
            "id": 1,
            "nombre": "Biblioteca Nacional",
            "direccion": "Av. de Mayo 575, Buenos Aires"
        },
        "autore": {
            "id": 4,
            "nombre": "Ernest",
            "apellido": "Hemingway",
            "email": "ehemingway@example.com"
        }
    }
}

# SQL tabla validaciones de formularios:
CREATE TABLE validation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(255) NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_parameters TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


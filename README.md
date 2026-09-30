# SEMILLA-DASH UTS

Sistema de gestión para centralizar, gestionar y dar seguimiento a los grupos y semilleros de investigación, sus proyectos y productos de Ciencia, Tecnología e Innovación (CTeI) de las Unidades Tecnológicas de Santander (UTS).

## Stack Tecnológico

- **Backend**: Laravel 10 (PHP)
- **Frontend**: Blade o Livewire, JavaScript y Tailwind CSS
- **Base de Datos**: PostgreSQL o MySQL
- **Almacenamiento**: Sistema de archivos privado para evidencias
- **API**: REST con JSON

## Módulos Implementados

### Módulo 1: Autenticación, Usuarios e Instituciones ✅
- Sistema de autenticación basado en tokens (Sanctum)
- Gestión de usuarios con roles RBAC
- Vinculaciones institucionales
- Identificadores externos (ORCID, CvLAC)

### Módulo 2: Grupos y Semilleros ✅
- CRUD de grupos de investigación
- CRUD de semilleros
- Articulación semillero-grupo
- Gestión de miembros
- Líneas de investigación y planes de trabajo

## Estructura del Proyecto

```
semilla-dash/
├── app/
│   ├── Http/
│   │   └── Controllers/          # Controladores API
│   ├── Models/                   # Modelos Eloquent
│   ├── Repositories/             # Capa de Repositorio
│   ├── Services/                 # Capa de Servicios
│   └── Http/Requests/           # Validaciones
├── config/                       # Configuración
├── database/
│   └── migrations/              # Migraciones de BD
├── routes/
│   └── api.php                  # Rutas API
└── resources/                    # Vistas Blade
```

## Arquitectura

El proyecto sigue los patrones de diseño solicitados:

- **MVC**: Separación clara entre Modelos, Vistas y Controladores
- **Inyección de Dependencias**: A través del contenedor de Laravel
- **Patrón Repositorio**: Abstracción del acceso a datos
- **Capa de Servicios**: Lógica de negocio separada de los controladores

## Instalación

1. Clonar el repositorio
2. Instalar dependencias: `composer install`
3. Configurar archivo `.env` basado en `.env.example`
4. Generar clave de aplicación: `php artisan key:generate`
5. Ejecutar migraciones: `php artisan migrate`
6. Inicializar roles por defecto: `php artisan db:seed --class=RoleSeeder`

## API Endpoints

### Usuarios
- `GET /api/v1/users` - Listar usuarios
- `POST /api/v1/users` - Crear usuario
- `GET /api/v1/users/{id}` - Obtener usuario
- `PUT /api/v1/users/{id}` - Actualizar usuario
- `DELETE /api/v1/users/{id}` - Eliminar usuario
- `POST /api/v1/users/{id}/roles` - Asignar rol
- `DELETE /api/v1/users/{id}/roles` - Remover rol

### Roles
- `GET /api/v1/roles` - Listar roles
- `POST /api/v1/roles` - Crear rol
- `GET /api/v1/roles/{id}` - Obtener rol
- `PUT /api/v1/roles/{id}` - Actualizar rol
- `DELETE /api/v1/roles/{id}` - Eliminar rol
- `POST /api/v1/roles/initialize-default` - Inicializar roles por defecto

### Grupos
- `GET /api/v1/grupos` - Listar grupos
- `POST /api/v1/grupos` - Crear grupo
- `GET /api/v1/grupos/{id}` - Obtener grupo
- `PUT /api/v1/grupos/{id}` - Actualizar grupo
- `DELETE /api/v1/grupos/{id}` - Eliminar grupo
- `POST /api/v1/grupos/{id}/members` - Agregar miembro
- `DELETE /api/v1/grupos/{id}/members` - Remover miembro

### Semilleros
- `GET /api/v1/semilleros` - Listar semilleros
- `POST /api/v1/semilleros` - Crear semillero
- `GET /api/v1/semilleros/{id}` - Obtener semillero
- `PUT /api/v1/semilleros/{id}` - Actualizar semillero
- `DELETE /api/v1/semilleros/{id}` - Eliminar semillero
- `POST /api/v1/semilleros/{id}/members` - Agregar integrante
- `DELETE /api/v1/semilleros/{id}/members` - Remover integrante
- `POST /api/v1/semilleros/{id}/grupos` - Articular con grupo
- `DELETE /api/v1/semilleros/{id}/grupos` - Remover articulación

## Roles del Sistema

1. **Integrante de Semillero**: Registra participación y evidencias propias
2. **Coordinador de Semillero**: Gestiona el plan formativo y a los integrantes
3. **Investigador**: Mantiene referencias de sus productos
4. **Líder de Grupo**: Administra el plan estratégico del grupo
5. **Director de Proyecto**: Gestiona cronograma y presupuesto
6. **Gestor de Investigación**: Revisa evidencias y normaliza metadatos
7. **Aval Institucional**: Emite decisiones formales sobre expedientes
8. **Administrador**: Gestiona catálogos, ventanas de observación, auditoría

## Seguridad

- Autenticación mediante Laravel Sanctum
- Encriptación de contraseñas con bcrypt
- Validaciones en frontend y backend
- Prevención de inyecciones SQL, XSS y CSRF
- Logs de auditoría para acciones sensibles

## Próximos Módulos

- Módulo 3: Proyectos de Investigación
- Módulo 4: Catálogo de Productos CTeI y Software
- Módulo 5: Gestión de Evidencias y Flujo de Avales
- Módulo 6: Ventanas de Observación y Calidad
- Módulo 7: Tableros (Dashboards) y Reportes

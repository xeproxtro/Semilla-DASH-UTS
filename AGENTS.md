# SEMILLA-DASH UTS - Guía de Desarrollo

## Información del Proyecto

Este es un sistema de gestión para centralizar, gestionar y dar seguimiento a los grupos y semilleros de investigación, sus proyectos y productos de Ciencia, Tecnología e Innovación (CTeI) de las Unidades Tecnológicas de Santander (UTS).

## Stack Tecnológico

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Blade o Livewire, JavaScript y Tailwind CSS
- **Base de Datos**: PostgreSQL o MySQL
- **Almacenamiento**: Sistema de archivos privado para evidencias
- **API**: REST con JSON

## Arquitectura Implementada

### Patrones de Diseño
- **MVC**: Separación clara entre Modelos, Vistas y Controladores
- **Inyección de Dependencias**: A través del contenedor de Laravel
- **Patrón Repositorio**: Abstracción del acceso a datos
- **Capa de Servicios**: Lógica de negocio separada de los controladores

### Estructura de Directorios
```
app/
├── Http/
│   ├── Controllers/          # Controladores API REST
│   ├── Middleware/          # Middleware de autenticación y autorización
│   └── Requests/            # Clases de validación
├── Models/                   # Modelos Eloquent con relaciones
├── Repositories/            # Capa de acceso a datos
├── Services/                # Lógica de negocio
└── ...
```

## Módulos Implementados

### ✅ Módulo 1: Autenticación, Usuarios e Instituciones
- Sistema de autenticación basado en tokens (Laravel Sanctum)
- Gestión de usuarios con roles RBAC
- Vinculaciones institucionales temporales
- Identificadores externos (ORCID, CvLAC)
- Control de acceso basado en roles

### ✅ Módulo 2: Grupos y Semilleros
- CRUD completo de grupos de investigación
- CRUD completo de semilleros
- Articulación semillero-grupo (relación muchos a muchos)
- Gestión de miembros con roles específicos
- Líneas de investigación y planes de trabajo

### ✅ Módulo 3: Proyectos de Investigación
- Sistema para registrar proyectos (investigación, extensión, formativos)
- Cronograma y hitos con estructura jerárquica
- Fuentes de financiación y recursos
- Participantes del proyecto con roles
- Gestión de riesgos y avances
- Registro de decisiones tomadas

### ✅ Módulo 4: Catálogo de Productos CTeI y Software
- Catálogo parametrizable para tipologías de Minciencias
- Registro de productos con múltiples relaciones (sin duplicidad)
- Identificadores externos (DOI, ISBN, ISSN, patente, registro, Handle, URL)
- Manejo especial para software (4 fases: Análisis, Diseño, Implementación, Validación)
- Certificación de innovación y registro DNDA
- Ventanas de observación dinámicas

### ✅ Módulo 5: Evidencias, Revisión y Avales
- Subida segura de archivos con validación de integridad (hash SHA-256)
- Listas de chequeo parametrizables por categoría y subtipo
- Flujo de estados: Borrador -> Enviado -> Devuelto -> Revisado -> Avalado -> Reportado -> Validado_Externamente -> Rechazado/Anulado
- Trazabilidad completa de aprobaciones/rechazos con roles y observaciones
- Separación clara entre revisión de completitud y aval institucional formal
- Gestión de respuestas a listas de chequeo
- Registro de historial de transiciones con metadata de auditoría

### ✅ Módulo 6: Ventanas, Cortes Históricos y Tableros
- Motor de reglas de negocio para ventanas de tiempo
- Estados: dentro de ventana, próximo a vencer, fuera, no determinable
- Congelamiento de cortes históricos (snapshots inmutables)
- Sistema de versionamiento de cortes (reapertura genera nueva versión)
- Métricas automáticas por categoría y tipo
- Sistema de alertas para productos próximos a vencer y expedientes incompletos
- API de dashboard con indicadores principales y tendencias
- Comparación entre cortes históricos

## Comandos de Desarrollo

### Instalación
```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure las credenciales de usuarios en el archivo .env
# Variables requeridas: ADMIN_EMAIL, ADMIN_PASSWORD, LIDER_EMAIL, LIDER_PASSWORD, etc.
php artisan migrate
php artisan db:seed
```

### Desarrollo
```bash
php artisan serve
php artisan migrate:fresh --seed
php artisan tinker
```

### Testing
```bash
php artisan test
```

## Roles del Sistema (RBAC)

1. **integrante_semillero**: Registra participación y evidencias propias
2. **coordinador_semillero**: Gestiona el plan formativo y a los integrantes
3. **investigador**: Mantiene referencias de sus productos
4. **lider_grupo**: Administra el plan estratégico del grupo
5. **director_proyecto**: Gestiona cronograma y presupuesto
6. **gestor_investigacion**: Revisa evidencias y normaliza metadatos
7. **aval_institucional**: Emite decisiones formales sobre expedientes
8. **administrador**: Gestiona catálogos, ventanas de observación, auditoría

## API Endpoints Principales

### Usuarios
- `GET /api/v1/users` - Listar usuarios (paginado)
- `POST /api/v1/users` - Crear usuario
- `GET /api/v1/users/{id}` - Obtener usuario por ID
- `PUT /api/v1/users/{id}` - Actualizar usuario
- `DELETE /api/v1/users/{id}` - Eliminar usuario (soft delete)
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

### Proyectos
- `GET /api/v1/proyectos` - Listar proyectos
- `GET /api/v1/proyectos/estado/{estado}` - Listar por estado
- `GET /api/v1/proyectos/director/{directorId}` - Listar por director
- `POST /api/v1/proyectos` - Crear proyecto
- `GET /api/v1/proyectos/{id}` - Obtener proyecto
- `PUT /api/v1/proyectos/{id}` - Actualizar proyecto
- `DELETE /api/v1/proyectos/{id}` - Eliminar proyecto
- `POST /api/v1/proyectos/{id}/grupos` - Agregar grupo
- `DELETE /api/v1/proyectos/{id}/grupos` - Remover grupo
- `POST /api/v1/proyectos/{id}/estado` - Cambiar estado

### Actividades
- `GET /api/v1/actividades` - Listar actividades
- `GET /api/v1/actividades/proyecto/{proyectoId}` - Listar por proyecto
- `GET /api/v1/actividades/proyecto/{proyectoId}/arbol` - Árbol de actividades
- `GET /api/v1/actividades/proyecto/{proyectoId}/atrasadas` - Actividades atrasadas
- `POST /api/v1/actividades` - Crear actividad
- `GET /api/v1/actividades/{id}` - Obtener actividad
- `PUT /api/v1/actividades/{id}` - Actualizar actividad
- `DELETE /api/v1/actividades/{id}` - Eliminar actividad
- `POST /api/v1/actividades/{id}/estado` - Cambiar estado

### Productos CTeI
- `GET /api/v1/productos` - Listar productos
- `GET /api/v1/productos/proximos-vencer` - Productos próximos a vencer
- `GET /api/v1/productos/fuera-ventana` - Productos fuera de ventana
- `POST /api/v1/productos` - Crear producto
- `GET /api/v1/productos/{id}` - Obtener producto
- `PUT /api/v1/productos/{id}` - Actualizar producto
- `DELETE /api/v1/productos/{id}` - Eliminar producto
- `POST /api/v1/productos/{id}/autores` - Agregar autor
- `DELETE /api/v1/productos/{id}/autores` - Remover autor
- `POST /api/v1/productos/{id}/estado` - Cambiar estado

### Software Registrado
- `GET /api/v1/software` - Listar software
- `GET /api/v1/software/con-certificacion` - Software con certificación
- `GET /api/v1/software/anio/{anio}` - Software por año
- `POST /api/v1/software` - Crear software
- `GET /api/v1/software/{id}` - Obtener software
- `PUT /api/v1/software/{id}` - Actualizar software
- `DELETE /api/v1/software/{id}` - Eliminar software
- `POST /api/v1/software/fase/{faseId}/estado` - Cambiar estado de fase
- `POST /api/v1/software/{softwareId}/certificacion` - Agregar certificación
- `GET /api/v1/software/{softwareId}/completitud` - Verificar completitud

### Evidencias
- `GET /api/v1/evidencias` - Listar evidencias
- `GET /api/v1/evidencias/{id}` - Obtener evidencia
- `POST /api/v1/evidencias` - Crear evidencia
- `PUT /api/v1/evidencias/{id}` - Actualizar evidencia
- `DELETE /api/v1/evidencias/{id}` - Eliminar evidencia
- `POST /api/v1/evidencias/{id}/validar-integridad` - Validar integridad (hash)
- `POST /api/v1/evidencias/{id}/marcar-validada` - Marcar como validada
- `GET /api/v1/evidencias/producto/{productoId}` - Evidencias por producto

### Revisiones de Expedientes
- `GET /api/v1/revisiones` - Listar revisiones
- `GET /api/v1/revisiones/{id}` - Obtener revisión
- `POST /api/v1/revisiones` - Crear revisión
- `PUT /api/v1/revisiones/{id}` - Actualizar revisión
- `DELETE /api/v1/revisiones/{id}` - Eliminar revisión
- `POST /api/v1/revisiones/{id}/enviar` - Enviar a revisión
- `POST /api/v1/revisiones/{id}/revisar` - Revisar completitud
- `POST /api/v1/revisiones/{id}/aprobar` - Aprobar expediente
- `POST /api/v1/revisiones/{id}/devolver` - Devolver expediente
- `POST /api/v1/revisiones/{id}/rechazar` - Rechazar expediente
- `POST /api/v1/revisiones/{id}/reportar` - Reportar expediente
- `POST /api/v1/revisiones/{revisionId}/respuestas` - Registrar respuesta
- `GET /api/v1/revisiones/historial/{productoId}` - Historial del producto
- `GET /api/v1/revisiones/pendientes` - Revisiones pendientes
- `GET /api/v1/revisiones/completados` - Revisiones completados

### Cortes Históricos
- `GET /api/v1/cortes` - Listar cortes
- `GET /api/v1/cortes/{id}` - Obtener corte
- `POST /api/v1/cortes` - Crear corte
- `PUT /api/v1/cortes/{id}` - Actualizar corte
- `DELETE /api/v1/cortes/{id}` - Eliminar corte
- `POST /api/v1/cortes/{id}/cerrar` - Cerrar corte
- `POST /api/v1/cortes/{id}/reabrir` - Reabrir corte (genera nueva versión)
- `POST /api/v1/cortes/{id}/archivar` - Archivar corte
- `GET /api/v1/cortes/abiertos` - Cortes abiertos
- `GET /api/v1/cortes/cerrados` - Cortes cerrados
- `GET /api/v1/cortes/recientes` - Cortes recientes
- `GET /api/v1/cortes/ultimo` - Último corte

### Dashboard (Tableros)
- `GET /api/v1/dashboard/resumen-grupos-semilleros` - Resumen de unidades activas
- `GET /api/v1/dashboard/portafolio-ctei` - Portafolio CTeI agrupado
- `GET /api/v1/dashboard/alertas` - Alertas de tablero
- `GET /api/v1/dashboard/aportes-semilleros/{grupoId}` - Aportes de semilleros
- `GET /api/v1/dashboard/metricas-corte-actual` - Métricas del corte actual
- `GET /api/v1/dashboard/tendencias-produccion` - Tendencias de producción
- `POST /api/v1/dashboard/comparar-cortes` - Comparar cortes históricos
- `GET /api/v1/dashboard/indicadores-principales` - Indicadores principales agregados

## Próximos Módulos a Desarrollar

### 📋 Módulo 7: Frontend (Blade/Livewire + Tailwind)
- Interfaces gráficas con Tailwind CSS
- Componentes Livewire para interactividad
- Vistas para todos los módulos del backend
- Formularios con validación en tiempo real
- Tablas con paginación y filtrado
- Gráficos y visualizaciones de dashboard
- Exportación XLSX/CSV

## Seguridad Implementada

- ✅ Autenticación mediante Laravel Sanctum
- ✅ Encriptación de contraseñas con bcrypt
- ✅ Validaciones en frontend y backend
- ✅ Prevención de inyecciones SQL (Eloquent ORM)
- ✅ Prevención de XSS (Blade templates)
- ✅ Protección CSRF
- ✅ Logs de auditoría para acciones sensibles
- ✅ Soft deletes para preservar datos

## Normativa Minciencias 2024

El sistema implementa las categorías de productos del Modelo de Minciencias 2024:
- **GNC**: Generación de Nuevo Conocimiento
- **DTI**: Desarrollo Tecnológico e Innovación
- **ASC-DPC**: Apropiación Social del Conocimiento
- **FRH**: Formación de Recurso Humano de Alto Nivel

## Notas Importantes

- Todos los controladores siguen el principio de "Thin Controllers"
- La lógica de negocio está delegada a las clases Service
- Los modelos incluyen todas las relaciones Eloquent necesarias
- Las migraciones incluyen índices para optimización
- Se usa soft deletes para preservar integridad de datos
- Los nombres de tablas siguen convenciones en español

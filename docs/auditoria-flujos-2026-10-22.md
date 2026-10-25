# Correcciones de la auditoría de flujos

Alcance: acceso, seguridad de cuentas, lotes, generación de muestras y QA. No se modificaron el servicio, las rutas, los estados ni la interfaz de Análisis IA.

## Cambios implementados

- **Lotes:** la ruta histórica DELETE anula lógicamente solo lotes pendientes de recepción. El listado permite anular con confirmación y conserva el expediente y sus eventos. Los lotes con recepción, muestreo o dictamen no pueden anularse ni eliminarse desde esa ruta. La anulación bloquea recepción y edición.
- **Correos:** normalización al guardar y validar usuarios; acceso compatible con cuentas históricas en mayúsculas. Validación de unicidad sin distinguir mayúsculas ni espacios exteriores. Una migración normaliza las cuentas e incorpora un índice único sobre la expresión normalizada. Si hay colisiones, la migración se detiene sin fusionar cuentas ni cambiar empresas.
- **Invitaciones empresariales:** reenvío serializado con activación mediante bloqueo de la solicitud. Si el transporte falla, el rollback conserva el hash, vencimiento y fecha de envío anteriores. Se registra el fallo para permitir reintento manual. Un envío aceptado por SMTP no se presenta como confirmación de recepción del mensaje.
- **Contraseñas:** política compartida por activación, administración de usuarios, perfil y recuperación: entre 12 y 255 caracteres, mayúsculas, minúsculas y números, con frases largas permitidas. El login conserva compatibilidad con contraseñas existentes.
- **Recuperación:** pantallas públicas y enlace desde login; broker y tabla de tokens nativos de Laravel adaptados a `correo` y `password_hash`. Respuesta neutral, límites de solicitudes, vencimiento de 60 minutos, uso único, exclusión de usuarios/roles/empresas inactivos y bloqueo de cuentas aún no activadas. Se rota el token de recordar, se eliminan sesiones de base de datos si ese es el driver activo y se comprueba el hash en las demás sesiones con el middleware de Laravel. Se envía un aviso del cambio.
- **Muestreo:** una inserción con `generate_series` en PostgreSQL reemplaza hasta 200 inserciones remotas. Se conserva la transacción y el rango permitido de 1 a 100 000 frutos, sin truncar códigos de seis dígitos. No se estableció un límite agrícola nuevo sin un protocolo aprobado.
- **QA:** fichas para las regresiones de acceso y toasts ausentes en la captura, y para todas las pruebas añadidas. El validador sigue exigiendo correspondencia exacta e IDs únicos. Chromium verifica navegación pública y errores de los formularios; su JUnit se integra en el informe y el resultado del job cuenta para la aprobación.

## Validación local

- Catálogo: 80 casos, identificadores únicos y correspondencia con las pruebas existentes.
- PHPUnit unitario: 5 pruebas, 25 aserciones, aprobadas.
- Frontend DOM: 10 pruebas aprobadas.
- Chromium: 3 pruebas aprobadas.
- Generador de informes: 8 pruebas Python aprobadas.
- Vistas Blade, rutas de recuperación, sintaxis PHP y compilación de producción verificadas.
- Las regresiones de PostgreSQL se detuvieron antes de acceder a la base: faltan identificadores de Neon testing en este entorno. No se ejecutaron migraciones aquí ni se sustituyó esa comprobación de seguridad.

## Puesta en servicio y límites

1. Ejecutar el pipeline sobre Neon testing verificado. Incluye las regresiones de correos, invitaciones, tokens, anulación y generación de 100 000 frutos.
2. Antes de desplegar, revisar posibles correos duplicados al normalizar. La migración `2026_10_22_180000_normalize_user_emails.php` debe completarse antes de habilitar las nuevas altas. Ejecutar las migraciones mediante el proceso habitual de despliegue.
3. Configurar y comprobar el transporte transaccional de correo. La aceptación por el transporte no garantiza llegada a la bandeja del destinatario; la recuperación mantiene una respuesta pública neutral incluso si falla el envío.
4. La cobertura Chromium es pública: no certifica todavía el recorrido completo de los cinco perfiles, el dictamen ni la trazabilidad en navegador. Las pruebas funcionales existentes y las nuevas regresiones siguen siendo necesarias en CI.

No se introdujeron colas para generar muestras, un seguimiento público de expedientes, invitaciones individuales para sustituir las contraseñas asignadas por administradores ni consultas externas de contraseñas comprometidas. Son ampliaciones propuestas por el reporte, no requisitos definidos de los flujos actuales. La inserción en servidor aborda los viajes de red del muestreo manteniendo su atomicidad; queda pendiente medir su duración real en Neon testing antes de decidir sobre colas y progreso.

La recuperación utiliza el [broker nativo de Laravel](https://laravel.com/framework/docs/passwords); el correo histórico se consulta mediante una condición adicional admitida por la [autenticación de Laravel](https://github.com/laravel/docs/blob/13.x/authentication.md).

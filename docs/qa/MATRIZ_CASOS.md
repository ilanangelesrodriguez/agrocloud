# Matriz de casos de prueba

Fuente: [catalogo_casos.json](../../scripts/qa/catalogo_casos.json). Los resultados se obtienen de cada ejecución, no de esta matriz.

| ID | Módulo | Escenario verificable | Tipo | Prioridad |
|---|---|---|---|---|
| CP-ACC-001 | Acceso | Renderizar correos con datos escapados y enlace de activación | Funcional / integración | Alta |
| CP-ACC-002 | Acceso | Actualizar decisiones sin redirección y conservar validaciones | Funcional / integración | Alta |
| CP-ADM-001 | Administracion | Filtrar, paginar y exportar empresas sin modificar la URL | Funcional / integración | Media |
| CP-ADM-002 | Administracion | Restaurar filtros desde enlaces antiguos y limpiar la URL | Funcional / integración | Media |
| CP-ADM-003 | Administracion | Paginar permisos en español sin modificar la URL | Funcional / integración | Media |
| CP-ADM-004 | Administracion | Filtrar maestros y usuarios con paginación limpia | Funcional / integración | Media |
| CP-ADM-005 | Administracion | Mostrar Limpiar filtros solamente cuando existen filtros de permisos | Funcional / integración | Media |
| CP-CFG-001 | Configuracion | Utilizar HTTPS público y restringir orígenes de Reverb | Seguridad | Alta |
| CP-CFG-002 | Configuracion | Exponer configuración pública sin revelar secretos de difusión | Seguridad | Alta |
| CP-CFG-003 | Configuracion | Deshabilitar Echo cuando la difusión está desactivada | Funcional / integración | Media |
| CP-EMP-001 | Empresas | Normalizar dominio y teléfono sin alterar la empresa del usuario | Funcional / integración | Media |
| CP-EMP-002 | Empresas | Aplicar la misma normalización al crear empresas | Funcional / integración | Media |
| CP-EMP-003 | Empresas | Rechazar URL insegura y teléfono inválido con mensajes en español | Funcional / integración | Media |
| CP-EMP-004 | Empresas | Exigir dominios válidos en los datos de contacto | Funcional / integración | Media |
| CP-EMP-005 | Empresas | Mostrar solamente el logo de la empresa actual | Funcional / integración | Media |
| CP-EMP-006 | Empresas | Renderizar el dashboard según rol y permisos | Seguridad | Alta |
| CP-EMP-007 | Empresas | Contar solamente datos de la empresa actual | Seguridad | Alta |
| CP-EMP-008 | Empresas | Guardar ubicación del productor con ubigeo calculado | Funcional / integración | Media |
| CP-EMP-009 | Empresas | Rechazar ubicación geográfica inconsistente | Funcional / integración | Media |
| CP-EMP-010 | Empresas | Mostrar ubicación guardada mediante tres selectores | Funcional / integración | Media |
| CP-EMP-011 | Empresas | Usar campos geográficos en formularios de productores | Funcional / integración | Media |
| CP-UI-001 | Interfaz | Mostrar estados operativos con colores y etiquetas consistentes | Funcional / integración | Media |
| CP-UI-002 | Interfaz | Aplicar el estándar visual a enums y estados activos | Funcional / integración | Media |
| CP-MAE-001 | Maestros | Restringir la administración de catálogos globales a SUPERADMIN | Seguridad | Alta |
| CP-MAE-002 | Maestros | Aislar catálogos y bloquear modificaciones entre empresas | Seguridad | Alta |
| CP-MAE-003 | Maestros | Extender y desactivar opciones compartidas por empresa | Funcional / integración | Media |
| CP-MAE-004 | Maestros | Proteger nombres utilizados y preservar historial al desactivar | Funcional / integración | Media |
| CP-MAE-005 | Maestros | Validar variedad de producto y conversión de peso | Funcional / integración | Media |
| CP-MAE-006 | Maestros | Mantener ubigeo de solo lectura y restringir gestión de catálogos | Seguridad | Alta |
| CP-MAE-007 | Maestros | Responder al inicio de sesión con destino JSON y errores de validación | Funcional / integración | Media |
| CP-MAE-008 | Maestros | Renderizar catálogos y almacenar el peso del lote en kilogramos | Funcional / integración | Media |
| CP-MAE-009 | Maestros | Crear opciones de catálogo en línea respetando permisos | Seguridad | Alta |
| CP-MAE-010 | Maestros | Guardar el tema únicamente para el usuario actual | Seguridad | Alta |
| CP-NOT-001 | Notificaciones | Persistir, aislar y marcar como leída una notificación | Seguridad | Alta |
| CP-NOT-002 | Notificaciones | Impedir redirecciones de notificaciones fuera del sitio | Seguridad | Alta |
| CP-OPE-001 | Operaciones | Completar recepción, muestreo, inspección y dictamen con trazabilidad | Funcional / integración | Alta |
| CP-OPE-002 | Operaciones | Aislar lotes y reportes por empresa y verificar permisos por rol | Seguridad | Alta |
| CP-PUB-001 | Publico | Mostrar la landing pública con navegación y acceso empresarial | Funcional / integración | Media |
| CP-REP-001 | Reportes | Exportar empresas en un archivo Excel con XML válido | Funcional / integración | Media |
| CP-REP-002 | Reportes | Exportar usuarios vacíos y normalizar el nombre de la hoja | Funcional / integración | Media |
| CP-SEG-001 | Seguridad | Rechazar carga de evidencia JSON sin autenticación | Seguridad | Alta |
| CP-SEG-002 | Seguridad | Rechazar análisis JSON sin autenticación | Seguridad | Alta |
| CP-SEG-003 | Seguridad | Rechazar validación de detecciones sin autenticación | Seguridad | Alta |
| CP-SEG-004 | Seguridad | Rechazar hallazgos manuales sin autenticación | Seguridad | Alta |
| CP-SEG-005 | Seguridad | Exigir autenticación para muestreos | Seguridad | Alta |
| CP-SEG-006 | Seguridad | Exigir autenticación para inspecciones | Seguridad | Alta |
| CP-SEG-007 | Seguridad | Exigir autenticación para calidad | Seguridad | Alta |
| CP-INF-001 | Support | Permitir únicamente la identidad verificada de Neon testing | Unitaria | Media |
| CP-INF-002 | Support | Bloquear producción, identidades incorrectas y alteraciones de conexión | Unitaria | Media |
| CP-INF-003 | Support | Comprobar la ejecución básica de PHPUnit (caso heredado) | Unitaria | Media |
| CP-FE-001 | Frontend | listas dependientes calculan UBIGEO y no duplican eventos al navegar | DOM sintético | Media |
| CP-FE-002 | Frontend | al editar una empresa resuelve UBIGEO y opciones relacionadas | DOM sintético | Media |
| CP-FE-003 | Frontend | si cambias departamento limpia valores dependientes | DOM sintético | Media |
| CP-FE-004 | Frontend | bloquea provincia inválida al guardar después de modificar ubicación | DOM sintético | Media |
| CP-FE-005 | Frontend | si no carga el catálogo los campos permanecen editables | DOM sintético | Media |
| CP-FE-006 | Frontend | contador de descripción y error para logo demasiado grande | DOM sintético | Media |
| CP-FE-007 | Frontend | los botones de tema sincronizan estado, etiqueta y preferencia guardada | DOM sintético | Media |

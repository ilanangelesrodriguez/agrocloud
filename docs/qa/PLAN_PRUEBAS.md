# Plan de pruebas de AgroCloud AI · QA v1

## Objetivo y alcance

Conservar las pruebas existentes, darles identificadores estables y generar evidencias verificables desde PHPUnit y Node. El catálogo contiene 57 casos: 47 de aplicación, 3 unitarios (incluido el caso básico heredado) y 7 de DOM sintético. Los escenarios que recorren varios módulos siguen siendo un solo caso; no se infla su cantidad contando cada aserción.

Esta versión no incluye Playwright, capturas, resultados de IA inventados ni tendencias históricas. La ejecución funcional y de seguridad real requiere Neon testing verificado en CI. Los resultados locales parciales no certifican el sistema completo.

## Organización

- `tests/Feature/`: Acceso, Administracion, Configuracion, Empresas, Interfaz, Maestros, Notificaciones, Operaciones, Publico, Reportes y Seguridad.
- `tests/Unit/Support/`: prueba básica existente y verificaciones de la protección de base de datos, sin conexión externa.
- `tests/frontend/`: pruebas DOM existentes, independientes de PHPUnit; no equivalen a E2E.
- `scripts/qa/catalogo_casos.json`: fuente versionada de fichas, prioridades y referencias a métodos reales.
- `scripts/qa/validar_catalogo.py`: verifica que no falten fichas ni existan IDs duplicados o métodos inexistentes.
- `scripts/qa/generar_reporte.py`: consolida JUnit en HTML, CSV, JSON y resumen de GitHub.

## Pipeline nuevo y transición

Archivo: [qa-pipeline.yml](../../.github/workflows/qa-pipeline.yml).

1. **Calidad del código y pruebas unitarias**: Composer, sintaxis PHP del proyecto, Pint sobre la infraestructura QA nueva, auditoría PHP, coherencia del catálogo y pruebas del generador.
2. **Frontend y experiencia de interfaz**: instalación reproducible, auditoría npm, compilación y pruebas DOM con JUnit.
3. **PostgreSQL, funcionalidad y seguridad**: verifica la identidad de Neon, prueba migraciones y ejecuta las dos categorías secuencialmente. La segunda suite se ejecuta aunque falle la primera; el job conserva el resultado fallido.
4. **Informe QA y evidencias**: se intenta incluso cuando otros jobs fallan. Una etapa fallida, evidencia ausente, caso omitido o error del catálogo impide aprobar la ejecución completa.

El workflow nuevo comienza con `workflow_dispatch`, tal como exige la transición propuesta. El workflow anterior continúa atendiendo los eventos existentes. Ambos usan el mismo grupo de concurrencia `agrocloud-neon-testing`; no pueden modificar la misma base al mismo tiempo. Se añadió también la verificación completa de conexión antes de la migración del workflow anterior.

Después de integrar estos archivos en GitHub:

1. Ejecutar manualmente **AgroCloud AI | Calidad y pruebas** en la rama correspondiente.
2. Revisar los cuatro jobs, las evidencias y los 57 casos. Comparar con la última ejecución del workflow anterior; el caso básico y las pruebas nuevas quedan identificados en la matriz.
3. Solo tras validar esa ejecución, retirar `.github/workflows/phpunit.yml` y añadir al nuevo workflow los eventos `push` para `main` y `feature/ilan`, y `pull_request` según la política del repositorio.
4. Conservar `workflow_dispatch` para reejecuciones. Los PR externos sin acceso a secretos no deben utilizar la base Neon; no usar `pull_request_target` para ejecutar código no confiable con credenciales.

No se activó el reemplazo automático ni se eliminó el workflow anterior sin esa comprobación remota. No se modificaron GitHub Secrets ni la rama Neon.

## Entorno de PostgreSQL

Configuración de GitHub necesaria:

| Tipo | Nombre | Propósito |
|---|---|---|
| Secret | `NEON_TEST_DATABASE_URL` | URL exclusiva de Neon testing |
| Variable | `NEON_TEST_HOST` | Host permitido para pruebas |
| Variable | `NEON_PROD_HOST` | Host de producción que debe rechazarse |
| Variable | `NEON_TEST_DATABASE` | Base esperada |
| Variable | `NEON_TEST_USER` | Usuario esperado |

`DatabaseSafety` exige entorno testing, driver pgsql, CI, autorización explícita, host distinto de producción, base y usuario exactos, y TLS requerido. Se rechazan parámetros de URL que puedan alterar el host, la base o la identidad. El verificador comprueba además `current_database()` y `current_user` de la conexión real antes de `migrate:fresh`. No imprime secretos ni excepciones del proveedor.

`migrate:fresh` y `RefreshDatabase` destruyen y reconstruyen tablas **solo de esa base testing verificada**. No ejecutar varias suites destructivas en paralelo ni compartir la rama de pruebas con trabajo manual. `tests/TestCase.php` repite la protección antes de preparar cada aplicación.

## Casos y criterios de aceptación

La [matriz](MATRIZ_CASOS.md) identifica cada escenario. La [trazabilidad](TRAZABILIDAD.md) enlaza el método y su criterio interno `QA-*`; esas referencias no pretenden sustituir requisitos funcionales aprobados. Los datos de entrada y pasos detallados permanecen en los fixtures y las aserciones de cada método, enlazados desde su ficha. Al añadir o dividir un caso, actualizar el catálogo sin reciclar IDs existentes.

Cada resultado conserva ID, escenario, tipo, prioridad, estado, tiempo, clase/método y archivo de evidencia. Commit, ID de ejecución, intento y fecha UTC quedan en `resumen.json`. Los fallos incorporan detalle técnico escapado y conexiones redactadas. Los JUnit originales son evidencia técnica: mantener los artefactos sujetos a los permisos del repositorio, sin hacerlos públicos automáticamente.

Criterio de aprobación global: catálogo completo, evidencias legibles y sin duplicados, todas las etapas exitosas y todos los casos aprobados. La tasa de aprobación usa aprobados / (aprobados + fallidos + errores). Los omitidos y no ejecutados se muestran aparte y bloquean la aprobación global. La duración es la suma de duraciones por caso, no el tiempo total del pipeline.

## Uso local

```sh
composer qa:unit
npm test
python scripts/qa/validar_catalogo.py
python -m unittest discover -s scripts/qa -p 'test_*.py' -v
```

Para crear evidencias reales y un reporte parcial local, sin Neon:

```sh
# Crear reports/evidencias con la herramienta de tu sistema antes de ejecutar.
php vendor/bin/phpunit --testsuite Unitarias --log-junit reports/evidencias/unitarias.xml
node --test --test-reporter=junit --test-reporter-destination=reports/evidencias/frontend.xml tests/frontend/empresa-form.test.cjs tests/frontend/theme.test.cjs
python scripts/qa/generar_reporte.py
```

Las pruebas de aplicación permanecen como **No ejecutado** en ese informe. No se desactiva la protección PostgreSQL para obtener un resultado local artificialmente verde. `composer qa:application` verifica la conexión antes de iniciar la suite; requiere el entorno de CI autorizado.

## Entregables y cobertura

El artefacto **agrocloud-informe-qa** contiene `index.html`, `casos.csv`, `resumen.json`, `resumen.md` y `evidencias/*.xml`. Se conserva 30 días. Descargar, descomprimir y abrir `index.html`; no necesita CDN ni servidor. GitHub muestra el resumen y el enlace al artefacto. Las evidencias funcionales y de seguridad se conservan separadas; el nuevo workflow no utiliza `--testdox-html`.

La opción manual `cobertura` genera Clover con PCOV para funcionalidad y seguridad por separado. No se anuncia una cobertura global combinada ni se impone un umbral sin establecer una línea base. La inclusión del código de aplicación se mantiene en `phpunit.xml`.

Referencias de ejecución: [PHPUnit 12.5](https://docs.phpunit.de/en/12.5/cli-options.html) y [artefactos de GitHub Actions](https://docs.github.com/actions/configuring-and-managing-workflows/persisting-workflow-data-using-artifacts).

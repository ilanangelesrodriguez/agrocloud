# Plan de calidad y evidencias de AgroCloud

El workflow `.github/workflows/qa-pipeline.yml` se ejecuta al subir cambios a `feature/ilan` y también mediante **Run workflow**. No publica sitios ni modifica producción. Mantiene la base Neon testing ya configurada.

## Ocho etapas

| Etapa | Qué comprueba | Evidencia |
|---|---|---|
| 01 · Calidad estática | Composer, sintaxis PHP, formato de infraestructura, vulnerabilidades, catálogo y generador | Estado y logs de GitHub Actions |
| 02 · Unitarias | Reglas aisladas, HTTP simulado y SQLite en memoria | `unitarias.xml` |
| 03 · Frontend | Dependencias, interacciones DOM y compilación única | `frontend.xml` y artefacto `qa-build` |
| 04 · PostgreSQL y seguridad | Migraciones, flujos, roles y aislamiento multiempresa | `funcionales.xml`, `seguridad.xml`, cobertura opcional |
| 05 · Navegador público | Login, recuperación, validaciones de campos | JUnit, capturas, trazas de fallos e informe Playwright |
| 06 · E2E autenticado | Navegar por dashboard, productores y lotes; registrar, buscar y editar un productor; bloquear acceso ajeno/global | JUnit, capturas, trazas de fallos e informe Playwright |
| 07 · Calidad visual | Dashboard, productores, lotes, empresas, roles, permisos y formulario público; escritorio/móvil, etiquetas y desbordamiento | Capturas completas, JUnit e informe Playwright |
| 08 · Informe consolidado | Resultados reales y estado de todas las etapas | HTML, JSON, CSV, JUnit, capturas y trazas |

Las etapas de Neon se encadenan **PostgreSQL → autenticado → visual**. La concurrencia global del workflow impide que dos ejecuciones utilicen la base testing al mismo tiempo. Las etapas estáticas y unitarias son independientes. Los jobs consumidores reutilizan `qa-build` y las cachés de Composer, npm y Chromium.

## Base existente y credenciales

Se conservan estos valores de GitHub:

| Tipo | Nombre |
|---|---|
| Secret | `NEON_TEST_DATABASE_URL` |
| Variable | `NEON_TEST_HOST` |
| Variable | `NEON_PROD_HOST` |
| Variable | `NEON_TEST_DATABASE` |
| Variable | `NEON_TEST_USER` |

`DatabaseSafety` exige `APP_ENV=testing`, PostgreSQL, ejecución en CI, autorización explícita, identidad exacta, host distinto de producción y TLS. `verificar_bd.php` comprueba además la base y usuario de la conexión real, sin imprimir credenciales.

La etapa PostgreSQL conserva el `migrate:fresh` y las pruebas con `RefreshDatabase` existentes: reconstruyen exclusivamente **Neon testing verificado**, que no debe contener datos que se quieran conservar. Las nuevas etapas de navegador no realizan otro borrado de tablas.

`preparar_e2e.php` vuelve a verificar la identidad antes de crear o actualizar únicamente fixtures ficticias de dos empresas, un productor por empresa, un lote y dos usuarios. Incluye un catálogo mínimo para mostrar la solicitud pública. No se ejecuta desde el seeder normal de la aplicación.

Cada job de navegador genera una contraseña temporal aleatoria, enmascarada en los logs. El login se realiza en un contexto sin grabación. Las sesiones y el manifiesto de fixtures se guardan en `playwright/.auth/`, ignorado por Git, y se retiran al finalizar el job. Nunca se suben en los artefactos. Las pruebas deshabilitan APIs RUC/IA, Cloudinary y envíos reales de correo.

## Informe descargable

En **Actions → ejecución → Artifacts**, descargar **agrocloud-informe-qa**, descomprimir y abrir `index.html`.

- `index.html`: resultados por módulo, estado y caso, enlaces a evidencia y galería de capturas.
- `resumen.json`, `resumen.md`, `casos.csv`: resultados, tiempos, commit e identificador de ejecución.
- `evidencias/`: JUnit con nombres únicos; no se sobrescriben archivos de otros jobs.
- `browser/publico/`, `browser/autenticado/`, `browser/visual/`: manifiestos, capturas y trazas disponibles.
- `browser/<suite>/html/`: informe nativo Playwright. Para servirlo, usar `npx playwright show-report <carpeta-html>`.

Las trazas ZIP se descargan desde el caso y se abren con Playwright Trace Viewer. Los artefactos del informe se conservan durante 30 días; la compilación compartida, 7 días.

El informe final se intenta generar aunque alguna etapa falle. Un caso sin JUnit queda **No ejecutado**. Las omisiones, fallos, adjuntos ausentes y errores de infraestructura impiden una aprobación global. Nunca se presentan capturas simuladas ni ejecuciones locales como evidencia de CI.

Las comprobaciones visuales verifican carga, etiquetas básicas y adaptación al viewport; no son una auditoría completa de accesibilidad ni comparaciones de píxeles contra baselines.

## Catálogo y trazabilidad

`scripts/qa/catalogo_casos.json` es la fuente de fichas. La [matriz](MATRIZ_CASOS.md) y la [trazabilidad](TRAZABILIDAD.md) enlazan escenarios y criterios internos `QA-*`. Al añadir casos, registrar sus nombres reales e IDs nuevos. El validador incluye archivos E2E en subdirectorios.

Se pueden revisar sintaxis y catálogo sin ejecutar pruebas. Las suites, los recorridos de navegador y las capturas se ejecutarán en GitHub Actions cuando se suban estos cambios. No se añadió una programación nocturna ni se habilitaron PR externos con acceso a secretos.

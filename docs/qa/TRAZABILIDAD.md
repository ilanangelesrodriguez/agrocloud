# Trazabilidad de validaciones

Las referencias `QA-*` son criterios internos de validación. No se presentan como requisitos funcionales aprobados por un cliente. Los escenarios compuestos conservan un único resultado; no se cuentan sus pasos como casos independientes.

| Caso | Criterio interno | Evidencia ejecutable |
|---|---|---|
| CP-ACC-001 | QA-ACC | [test_email_templates_render_escaped_company_data_and_activation_link](../../tests/Feature/Acceso/SolicitudesAccesoTest.php) |
| CP-ACC-002 | QA-ACC | [test_review_updates_the_record_without_a_redirect_and_preserves_validation](../../tests/Feature/Acceso/SolicitudesAccesoTest.php) |
| CP-ADM-001 | QA-ADM | [test_company_filters_pagination_and_exports_work_without_query_strings](../../tests/Feature/Administracion/SuperadminListsTest.php) |
| CP-ADM-002 | QA-ADM | [test_old_filter_links_redirect_to_clean_urls_and_restore_filters](../../tests/Feature/Administracion/SuperadminListsTest.php) |
| CP-ADM-003 | QA-ADM | [test_permissions_pagination_is_in_spanish_and_does_not_change_url](../../tests/Feature/Administracion/SuperadminListsTest.php) |
| CP-ADM-004 | QA-ADM | [test_master_filters_and_company_users_use_clean_pagination](../../tests/Feature/Administracion/SuperadminListsTest.php) |
| CP-ADM-005 | QA-ADM | [test_permission_filters_and_conditional_clear_button](../../tests/Feature/Administracion/SuperadminListsTest.php) |
| CP-CFG-001 | QA-CFG | [test_production_uses_public_https_endpoint_and_restricts_origins](../../tests/Feature/Configuracion/ReverbConfigurationTest.php) |
| CP-CFG-002 | QA-CFG | [test_layout_exposes_public_configuration_without_broadcast_secret](../../tests/Feature/Configuracion/ReverbConfigurationTest.php) |
| CP-CFG-003 | QA-CFG | [test_layout_does_not_enable_echo_when_broadcasting_is_disabled](../../tests/Feature/Configuracion/ReverbConfigurationTest.php) |
| CP-EMP-001 | QA-EMP | [test_company_accepts_bare_domain_and_formatted_phone_and_preserves_tenant_state](../../tests/Feature/Empresas/CompanyValidationTest.php) |
| CP-EMP-002 | QA-EMP | [test_admin_creation_uses_the_same_normalization](../../tests/Feature/Empresas/CompanyValidationTest.php) |
| CP-EMP-003 | QA-EMP | [test_validation_errors_are_in_spanish_and_unsafe_urls_and_long_phones_are_rejected](../../tests/Feature/Empresas/CompanyValidationTest.php) |
| CP-EMP-004 | QA-EMP | [test_company_contact_fields_require_real_domains](../../tests/Feature/Empresas/CompanyValidationTest.php) |
| CP-EMP-005 | QA-EMP | [test_sidebar_uses_only_current_company_logo_and_has_fallback](../../tests/Feature/Empresas/CompanyValidationTest.php) |
| CP-EMP-006 | QA-EMP | [test_dashboard_renders_for_every_role_and_honors_permissions](../../tests/Feature/Empresas/LocationAndDashboardTest.php) |
| CP-EMP-007 | QA-EMP | [test_dashboard_counts_only_the_current_company](../../tests/Feature/Empresas/LocationAndDashboardTest.php) |
| CP-EMP-008 | QA-EMP | [test_producer_location_is_saved_with_calculated_ubigeo](../../tests/Feature/Empresas/LocationAndDashboardTest.php) |
| CP-EMP-009 | QA-EMP | [test_mismatched_location_is_rejected](../../tests/Feature/Empresas/LocationAndDashboardTest.php) |
| CP-EMP-010 | QA-EMP | [test_company_renders_three_selects_with_saved_location](../../tests/Feature/Empresas/LocationAndDashboardTest.php) |
| CP-EMP-011 | QA-EMP | [test_producer_forms_use_geographic_fields_without_legacy_location_input](../../tests/Feature/Empresas/LocationAndDashboardTest.php) |
| CP-UI-001 | QA-UI | [test_workflow_states_use_consistent_colors_and_readable_labels](../../tests/Feature/Interfaz/StateBadgeTest.php) |
| CP-UI-002 | QA-UI | [test_enum_states_and_existing_active_badges_share_the_standard](../../tests/Feature/Interfaz/StateBadgeTest.php) |
| CP-MAE-001 | QA-MAE | [test_global_catalogs_can_be_managed_only_by_superadmin](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-002 | QA-MAE | [test_company_catalogs_do_not_leak_or_allow_cross_company_updates](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-003 | QA-MAE | [test_shared_options_can_be_extended_and_disabled_per_company](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-004 | QA-MAE | [test_used_options_cannot_be_renamed_and_deactivation_keeps_history](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-005 | QA-MAE | [test_product_variety_relationship_and_weight_conversion_are_validated](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-006 | QA-MAE | [test_ubigeo_is_read_only_and_non_admin_roles_cannot_manage_catalogs](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-007 | QA-MAE | [test_login_returns_json_destination_and_errors_without_full_page_response](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-008 | QA-MAE | [test_catalog_fields_render_and_lot_weight_is_saved_in_kilograms](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-009 | QA-MAE | [test_inline_master_creation_returns_the_option_and_enforces_permissions](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-MAE-010 | QA-MAE | [test_theme_is_saved_only_for_the_current_user_and_rendered_from_database](../../tests/Feature/Maestros/MaestrosTest.php) |
| CP-NOT-001 | QA-NOT | [test_notificacion_persistida_segregada_y_marcada_como_leida](../../tests/Feature/Notificaciones/OperationalNotificationsTest.php) |
| CP-NOT-002 | QA-NOT | [test_bandeja_no_permita_redireccion_fuera_del_sitio](../../tests/Feature/Notificaciones/OperationalNotificationsTest.php) |
| CP-OPE-001 | QA-OPE | [test_reception_sample_identity_trace_and_missing_fruit_guard](../../tests/Feature/Operaciones/OperationalWorkflowTest.php) |
| CP-OPE-002 | QA-OPE | [test_company_isolation_reports_and_role_gates](../../tests/Feature/Operaciones/OperationalWorkflowTest.php) |
| CP-PUB-001 | QA-PUB | [test_guest_home_shows_public_landing](../../tests/Feature/Publico/ExampleTest.php) |
| CP-REP-001 | QA-REP | [test_excel_de_empresas_contiene_archivos_y_xml_validos](../../tests/Feature/Reportes/XlsxExporterTest.php) |
| CP-REP-002 | QA-REP | [test_excel_de_usuarios_vacio_y_nombre_hoja_se_crean_correctamente](../../tests/Feature/Reportes/XlsxExporterTest.php) |
| CP-SEG-001 | QA-SEG | [test_guest_cannot_upload_evidence_via_json](../../tests/Feature/Seguridad/InspectorJsonAccessTest.php) |
| CP-SEG-002 | QA-SEG | [test_guest_cannot_analyze_evidence_via_json](../../tests/Feature/Seguridad/InspectorJsonAccessTest.php) |
| CP-SEG-003 | QA-SEG | [test_guest_cannot_validate_detections_via_json](../../tests/Feature/Seguridad/InspectorJsonAccessTest.php) |
| CP-SEG-004 | QA-SEG | [test_guest_cannot_register_manual_findings_via_json](../../tests/Feature/Seguridad/InspectorJsonAccessTest.php) |
| CP-SEG-005 | QA-SEG | [test_muestreos_requires_authentication](../../tests/Feature/Seguridad/InspectorRoutesTest.php) |
| CP-SEG-006 | QA-SEG | [test_inspecciones_requires_authentication](../../tests/Feature/Seguridad/InspectorRoutesTest.php) |
| CP-SEG-007 | QA-SEG | [test_calidad_requires_authentication](../../tests/Feature/Seguridad/InspectorRoutesTest.php) |
| CP-INF-001 | QA-INF | [test_allows_only_the_verified_testing_identity](../../tests/Unit/Support/DatabaseSafetyTest.php) |
| CP-INF-002 | QA-INF | [test_rejects_production_wrong_identity_and_connection_overrides](../../tests/Unit/Support/DatabaseSafetyTest.php) |
| CP-INF-003 | QA-INF | [test_that_true_is_true](../../tests/Unit/Support/ExampleTest.php) |
| CP-FE-001 | QA-FE | [listas dependientes calculan UBIGEO y no duplican eventos al navegar](../../tests/frontend/empresa-form.test.cjs) |
| CP-FE-002 | QA-FE | [al editar una empresa resuelve UBIGEO y opciones relacionadas](../../tests/frontend/empresa-form.test.cjs) |
| CP-FE-003 | QA-FE | [si cambias departamento limpia valores dependientes](../../tests/frontend/empresa-form.test.cjs) |
| CP-FE-004 | QA-FE | [bloquea provincia inválida al guardar después de modificar ubicación](../../tests/frontend/empresa-form.test.cjs) |
| CP-FE-005 | QA-FE | [si no carga el catálogo los campos permanecen editables](../../tests/frontend/empresa-form.test.cjs) |
| CP-FE-006 | QA-FE | [contador de descripción y error para logo demasiado grande](../../tests/frontend/empresa-form.test.cjs) |
| CP-FE-007 | QA-FE | [los botones de tema sincronizan estado, etiqueta y preferencia guardada](../../tests/frontend/theme.test.cjs) |
| CP-RUC-001 | QA-RUC | [test_adds_nullable_verification_without_changing_existing_records](../../tests/Unit/SunatMigrationTest.php) |
| CP-RUC-002 | QA-RUC | [test_caches_provider_data_but_compares_each_declared_name_on_server](../../tests/Unit/SunatRucServiceTest.php) |
| CP-RUC-003 | QA-RUC | [test_classifies_errors_without_exposing_provider_body](../../tests/Unit/SunatRucServiceTest.php) |
| CP-RUC-004 | QA-RUC | [test_connection_failure_is_pending_and_can_be_retried](../../tests/Unit/SunatRucServiceTest.php) |
| CP-RUC-005 | QA-RUC | [test_rejects_mismatched_or_malformed_responses](../../tests/Unit/SunatRucServiceTest.php) |
| CP-RUC-006 | QA-RUC | [test_missing_key_and_invalid_format_make_no_http_requests](../../tests/Unit/SunatRucServiceTest.php) |
| CP-RUC-007 | QA-RUC | [test_tax_status_is_separate_from_name_match](../../tests/Unit/SunatRucServiceTest.php) |
| CP-FE-008 | QA-RUC | [consulta muestra datos sin sobrescribirlos y permite aplicar explícitamente la razón social](../../tests/frontend/ruc-lookup.test.cjs) |
| CP-FE-009 | QA-RUC | [descarta una respuesta cuando el usuario cambia el RUC durante la consulta](../../tests/frontend/ruc-lookup.test.cjs) |
| CP-FE-010 | QA-RUC | [un RUC incompleto no consulta y un error de red permite continuar](../../tests/frontend/ruc-lookup.test.cjs) |
| CP-FE-011 | QA-RUC | [consulta pública envía POST con CSRF sin exponer los datos en la URL](../../tests/frontend/ruc-lookup.test.cjs) |
| CP-E2E-004 | QA-UI | [errores nativos quedan junto a cada campo y se limpian al corregir, sin toast](../../tests/e2e/form-validation.spec.js) |
| CP-E2E-005 | QA-UI | [errores del servidor no se duplican y mantienen el texto seguro](../../tests/e2e/form-validation.spec.js) |
| CP-E2E-006 | QA-E2E | [E2E autenticado: navegar por dashboard, productores y lotes](../../tests/e2e/internal/operations.spec.js) |
| CP-E2E-007 | QA-E2E | [E2E autenticado: registrar, buscar y editar un productor ficticio](../../tests/e2e/internal/operations.spec.js) |
| CP-E2E-008 | QA-E2E | [E2E seguridad: impedir acceso a productor ajeno y administración global](../../tests/e2e/internal/operations.spec.js) |
| CP-E2E-009 | QA-VIS | [Visual: dashboard de empresa en escritorio](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-010 | QA-VIS | [Visual: productores en escritorio](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-011 | QA-VIS | [Visual: lotes en escritorio](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-012 | QA-VIS | [Visual: formulario de productor en móvil](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-013 | QA-VIS | [Visual: dashboard de empresa en móvil](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-014 | QA-VIS | [Visual: empresas en escritorio](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-015 | QA-VIS | [Visual: roles y permisos en escritorio](../../tests/e2e/visual/screens.spec.js) |
| CP-E2E-016 | QA-VIS | [Visual: solicitud de acceso en móvil](../../tests/e2e/visual/screens.spec.js) |

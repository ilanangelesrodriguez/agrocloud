<?php

namespace Tests\Feature;

use Tests\TestCase;

class InspectorJsonAccessTest extends TestCase
{
    public function test_guest_cannot_upload_evidence_via_json(): void
    {
        $this->postJson('/inspecciones/2/evidencias')->assertUnauthorized();
    }

    public function test_guest_cannot_analyze_evidence_via_json(): void
    {
        $this->postJson('/inspecciones/2/evidencias/1/analizar')->assertUnauthorized();
    }

    public function test_guest_cannot_validate_detections_via_json(): void
    {
        $this->postJson('/inspecciones/2/validacion/1')->assertUnauthorized();
    }

    public function test_guest_cannot_register_manual_findings_via_json(): void
    {
        $this->postJson('/inspecciones/2/hallazgos')->assertUnauthorized();
    }
}

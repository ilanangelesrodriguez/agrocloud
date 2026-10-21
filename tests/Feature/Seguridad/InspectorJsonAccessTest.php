<?php

namespace Tests\Feature\Seguridad;

use Tests\TestCase;

class InspectorJsonAccessTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_guest_cannot_upload_evidence_via_json(): void
    {
        $this->postJson('/inspecciones/2/evidencias')->assertUnauthorized();
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_guest_cannot_analyze_evidence_via_json(): void
    {
        $this->postJson('/inspecciones/2/evidencias/1/analizar')->assertUnauthorized();
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_guest_cannot_validate_detections_via_json(): void
    {
        $this->postJson('/inspecciones/2/validacion/1')->assertUnauthorized();
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_guest_cannot_register_manual_findings_via_json(): void
    {
        $this->postJson('/inspecciones/2/hallazgos')->assertUnauthorized();
    }
}

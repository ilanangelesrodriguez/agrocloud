<?php

namespace Tests\Feature;

use App\Enums\LoteEstado;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StateBadgeTest extends TestCase
{
    public function test_workflow_states_use_consistent_colors_and_readable_labels(): void
    {
        foreach ([
            ['REGISTRADO', 'slate', 'Registrado'],
            ['EN_INSPECCION', 'blue', 'En inspección'],
            ['PENDIENTE_VALIDACION', 'amber', 'Pendiente de dictamen'],
            ['OBSERVADO', 'amber', 'Observado'],
            ['COMPLETADO', 'emerald', 'Completado'],
            ['RECHAZADO', 'red', 'Rechazado'],
            ['ERROR', 'red', 'Error'],
        ] as [$state, $color, $label]) {
            $html = Blade::render('<x-ui.state-badge :state="$state" />', compact('state'));
            $this->assertStringContainsString('bg-'.$color.'-', $html);
            $this->assertStringContainsString('dark:text-'.$color.'-', $html);
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_enum_states_and_existing_active_badges_share_the_standard(): void
    {
        $html = Blade::render('<x-ui.state-badge :state="$state" />', ['state' => LoteEstado::CLASIFICADO]);
        $this->assertStringContainsString('bg-emerald-50', $html);
        $this->assertStringContainsString('Clasificado', $html);
        $html = Blade::render('<x-ui.status-badge :active="false" inactive-label="Empresa inactiva" />');
        $this->assertStringContainsString('bg-slate-100', $html);
        $this->assertStringContainsString('Empresa inactiva', $html);
    }
}

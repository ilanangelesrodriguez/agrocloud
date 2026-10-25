"""Reúne XML y evidencias visuales de artefactos sin sobrescribir archivos homónimos."""
from pathlib import Path
import shutil


def collect(source=Path('qa-artifacts'), destination=Path('reports')):
    junit = destination / 'evidencias'
    junit.mkdir(parents=True, exist_ok=True)
    for artifact in sorted(source.iterdir()) if source.exists() else []:
        if not artifact.is_dir():
            continue
        for xml in artifact.rglob('*.xml'):
            # Las coberturas Clover no son casos JUnit.
            if xml.name.startswith('cobertura'):
                continue
            target = junit / (artifact.name + '-' + xml.name)
            if target.exists():
                raise ValueError('Evidencia JUnit duplicada: ' + target.name)
            shutil.copyfile(xml, target)
        for manifest in artifact.rglob('evidence.json'):
            suite = manifest.parent.name
            if suite not in {'publico', 'autenticado', 'visual'}:
                raise ValueError('Suite de evidencias desconocida.')
            target = destination / 'browser' / suite
            if target.exists():
                raise ValueError('Suite de evidencias duplicada: ' + suite)
            shutil.copytree(manifest.parent, target)


if __name__ == '__main__':
    collect()

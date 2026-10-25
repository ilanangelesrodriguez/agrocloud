import json
from pathlib import Path
import tempfile
import unittest
from generar_reporte import generate


class ReportTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(dir=Path.cwd())
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.catalog = self.root / 'catalog.json'
        self.catalog.write_text(json.dumps({'casos': [{'id': 'CP-001', 'titulo': '<script>Texto</script>', 'modulo': 'Prueba', 'categoria': 'Unitaria', 'test': 'Example::test_one'}]}), encoding='utf-8')

    def run_report(self, xml=None, stages=None):
        inputs = []
        if xml is not None:
            path = self.root / 'unitarias.xml'
            path.write_text(xml, encoding='utf-8')
            inputs.append(path)
        return generate(self.catalog, inputs, self.root / 'out', stages)

    def test_missing_evidence_is_never_approved(self):
        result = self.run_report()
        self.assertEqual(result['estados']['No ejecutado'], 1)
        self.assertIsNone(result['aprobacion_ejecutados'])
        self.assertEqual(result['resultado'], 'Requiere revisión')

    def test_passed_case_uses_real_time_and_escapes_html(self):
        result = self.run_report('<testsuite><testcase classname="Example" name="test_one" time="0.25"/></testsuite>')
        self.assertEqual(result['resultado'], 'Aprobado')
        self.assertEqual(result['duracion_acumulada_segundos'], 0.25)
        page = (self.root / 'out/index.html').read_text(encoding='utf-8')
        self.assertIn('&lt;script&gt;Texto&lt;/script&gt;', page)
        self.assertNotIn('<script>Texto</script>', page)
        self.assertTrue((self.root / 'out/casos.csv').exists())

    def test_failures_errors_and_skips_have_distinct_statuses(self):
        for tag, expected in [('failure', 'Fallido'), ('error', 'Error'), ('skipped', 'Omitido')]:
            with self.subTest(tag=tag):
                result = self.run_report(f'<testsuite><testcase classname="Example" name="test_one"><{tag} message="detalle"/></testcase></testsuite>')
                self.assertEqual(result['estados'][expected], 1)
                self.assertEqual(result['resultado'], 'Requiere revisión')

    def test_invalid_xml_and_unknown_cases_are_reported(self):
        self.assertTrue(self.run_report('<broken>')['advertencias'])
        result = self.run_report('<testsuite><testcase classname="Other" name="unknown"/></testsuite>')
        self.assertTrue(result['advertencias'])
        self.assertEqual(result['estados']['No ejecutado'], 1)

    def test_failed_job_cannot_be_hidden_by_passing_cases(self):
        result = self.run_report('<testsuite><testcase classname="Example" name="test_one"/></testsuite>', {'calidad': 'failure'})
        self.assertEqual(result['resultado'], 'Requiere revisión')

    def test_connection_secrets_are_redacted(self):
        result = self.run_report('<testsuite><testcase classname="Example" name="test_one"><error>postgresql://user:secret@host/db password=secret</error></testcase></testsuite>')
        self.assertNotIn('secret', result['casos'][0]['detalle'])

    def test_phpunit_dotted_classname_is_matched_to_catalog(self):
        catalog = json.loads(self.catalog.read_text(encoding='utf-8'))
        catalog['casos'][0]['test'] = 'Tests\\Example::test_one'
        self.catalog.write_text(json.dumps(catalog), encoding='utf-8')
        result = self.run_report('<testsuite><testcase classname="Tests.Example" name="test_one"/></testsuite>')
        self.assertEqual(result['estados']['Aprobado'], 1)
        self.assertEqual(result['estados']['No ejecutado'], 0)

    def test_duplicate_evidence_is_not_a_clean_pass(self):
        result = self.run_report('<testsuite><testcase classname="Example" name="test_one"/><testcase classname="Example" name="test_one"/></testsuite>')
        self.assertTrue(result['advertencias'])
        self.assertEqual(result['resultado'], 'Requiere revisión')

    def test_visual_attachments_are_linked_to_their_case(self):
        folder = self.root / 'out/browser/visual/assets'
        folder.mkdir(parents=True)
        (folder / 'screen.png').write_bytes(b'fixture-image')
        (folder.parent / 'evidence.json').write_text(json.dumps({'cases': [{'test': 'Example::test_one', 'status': 'passed', 'files': [{'path': 'assets/screen.png'}]}]}), encoding='utf-8')
        result = self.run_report('<testsuite><testcase classname="Example" name="test_one"/></testsuite>')
        self.assertEqual(len(result['evidencias_visuales']), 1)
        self.assertIn('browser/visual/assets/screen.png', (self.root / 'out/index.html').read_text(encoding='utf-8'))

    def test_visual_attachment_cannot_escape_its_suite_directory(self):
        folder = self.root / 'out/browser/visual'
        folder.mkdir(parents=True)
        (self.root / 'private.png').write_bytes(b'private')
        (folder / 'evidence.json').write_text(json.dumps({'cases': [{'test': 'Example::test_one', 'status': 'passed', 'files': [{'path': '../../../private.png'}]}]}), encoding='utf-8')
        result = self.run_report('<testsuite><testcase classname="Example" name="test_one"/></testsuite>')
        self.assertFalse(result['evidencias_visuales'])
        self.assertTrue(result['advertencias'])
        self.assertEqual(result['resultado'], 'Requiere revisión')

    def test_nested_playwright_titles_match_the_catalog(self):
        catalog = json.loads(self.catalog.read_text(encoding='utf-8'))
        catalog['casos'][0]['test'] = 'Pantalla visual'
        self.catalog.write_text(json.dumps(catalog), encoding='utf-8')
        result = self.run_report('<testsuite><testcase classname="screens.spec.js" name="Administración › Pantalla visual"/></testsuite>')
        self.assertEqual(result['estados']['Aprobado'], 1)
        self.assertEqual(result['estados']['No ejecutado'], 0)

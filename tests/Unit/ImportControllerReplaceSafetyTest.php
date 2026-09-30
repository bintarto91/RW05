<?php

use PHPUnit\Framework\TestCase;

final class ImportControllerReplaceSafetyTest extends TestCase
{
    public function testAllRowsArePreparedBeforeReplaceCanDeleteExistingRows(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/Admin/ImportController.php');
        self::assertIsString($controller);
        $methodStart = strpos($controller, 'private function importFile(');
        $methodEnd = strpos($controller, 'private function parseCsv(', $methodStart);
        self::assertNotFalse($methodStart);
        self::assertNotFalse($methodEnd);
        $method = substr($controller, $methodStart, $methodEnd - $methodStart);

        $prepareRecords = strpos($method, '$records[] = $this->buildRecord($type, $data);');
        $connectDatabase = strpos($method, '$db = db_connect();');
        $beginTransaction = strpos($method, '$db->transBegin();');
        $deleteExisting = strpos($method, 'DELETE FROM');

        self::assertNotFalse($prepareRecords);
        self::assertNotFalse($connectDatabase);
        self::assertNotFalse($beginTransaction);
        self::assertNotFalse($deleteExisting);
        self::assertTrue($prepareRecords < $connectDatabase);
        self::assertTrue($connectDatabase < $beginTransaction);
        self::assertTrue($beginTransaction < $deleteExisting);
        self::assertStringNotContainsString('emptyTable(', $method);
        self::assertStringContainsString('$db->transRollback();', $method);
    }

    public function testReplaceRequiresAnExplicitConfirmationInControllerAndView(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Controllers/Admin/ImportController.php');
        $view = file_get_contents($root . '/app/Views/admin/import.php');
        self::assertIsString($controller);
        self::assertIsString($view);
        self::assertStringContainsString('$this->request->getPost(\'confirm_replace\') !== \'yes\'', $controller);
        self::assertStringContainsString('name="confirm_replace" value="yes"', $view);
        self::assertStringContainsString('menggantikan seluruh isi dataset', $view);
    }
}
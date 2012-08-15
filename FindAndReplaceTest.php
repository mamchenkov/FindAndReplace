<?php
/**
 * Unit tests for FindAndReplace class
 *
 * @author Leonid Mamchenkov <leonid@mamchenkov.net>
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'FindAndReplace.php';

class LockerTest extends PHPUnit_Framework_TestCase {

	public function test__regexp_simple() {

		$good_original = 'this is a sample string';
		$good_result = 'this is a real string';
		$pattern = '/sample/';
		$replacement = 'real';

		$test_file = 'testfile.txt';

		file_put_contents($test_file, $good_original);
		$result = FindAndReplace::regexp($pattern, $replacement, $test_file);

		$this->AssertGreaterThan(0, $result, "Test file replacement failed");

		$result = file_get_contents($test_file);
		$this->AssertEquals($result, $good_result, "Test file replacement went wrong");

		unlink($test_file);
	}

}
?>

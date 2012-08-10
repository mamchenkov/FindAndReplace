<?php
/**
 * Find and replace that works with large files
 *
 * <code>
 * <?php
 * require_once 'FindAndReplace.php';
 * $replace = array(
 *   	'/foo/' => 'bar',
 *   	'/baz/' => 'qux',
 *   );
 * $file = 'large_file.txt';
 * FindAndReplace::regexp(array_keys($replace), array_values($replace), $file);
 * </code>
 *
 * @author Leonid Mamchenkov <leonid@mamchenkov.net>
 */
class FindAndReplace {

	/** 
	 * Buffer size in bytes 
	 */
	public static $bufferSize = 102400;

	/**
	 * Replace patterns in source file, saving to destination
	 *
	 * The difference between the first and second run is that in second run we
	 * are making sure that the pattern doesn't fall on the border of the buffer, 
	 * so the first read buffer is half the size of the rest.  This provides a 
	 * shift in buffer borders between the first run and the second.
	 *
	 * @param string $src Source file
	 * @param string $dst Destination file
	 * @param array $pattern Patterns
	 * @param array $replacement Replacements
	 * @firstRun boolean Whether to do the first or the second run
	 * @return integer Number of bytes written to destination file
	 */
	protected static function replace($src, $dst, $pattern, $replacement, $firstRun) {
		$result = 0;

		$srcFp = fopen($src, 'r');
		$dstFp = fopen($dst, 'w');

		$firstBuffer = true;
		while (!feof($srcFp)) {
			$bufferSize = self::$bufferSize;

			if ($firstBuffer && !$firstRun) {
				$bufferSize = floor($bufferSize / 2);
				$firstBuffer = false;
			}

			$buffer = fread($srcFp, $bufferSize);
			$buffer = preg_replace($pattern, $replacement, $buffer);
			$result += fwrite($dstFp, $buffer);
		}

		fclose($srcFp);
		fclose($dstFp);

		return $result;
	}

	/**
	 * Regular expression replacement
	 *
	 * For more information on $pattern and $replacement, see PHP manual
	 * for preg_repace() function.
	 *
	 * @param mixed $pattern Single pattern string, or an array
	 * @param mixed $replacement Single replacement string, or an array
	 * @param string $source Path to file
	 * @return integer Number of bytes written to a resulting file
	 */
	public static function regexp($pattern, $replacement, $source) {
		$result = 0;

		if (!is_array($pattern))     { $pattern = array($pattern); }
		if (!is_array($replacement)) { $replacement = array($replacement); }

		if (count($pattern) <> count($replacement)) {
			throw new InvalidArgumentException("Pattern count is different from replacement count");
		}

		if (!file_exists($source) || !is_file($source) || !is_readable($source)	|| !is_writable($source)) {
			throw new InvalidArgumentException("File [$source] does not exist, is not readable or writable");
		}

		// Temporary file
		$tempFileName = tempnam(dirname($source), basename($source));

		// Yeah, I know, but it just looks better this way. ;)
		$result = self::replace($source, $tempFileName, $pattern, $replacement, true);
		$result = self::replace($tempFileName, $source, $pattern, $replacement, false);

		unlink($tempFileName);

		return $result;
	}
}
?>

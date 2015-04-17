<?php
class TYPO3_Sniffs_PHP_DeprecatedFunctionsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff {

	protected $namespace = '';
	protected $uses = array();

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_NAMESPACE, T_USE, T_STRING);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned
	 * @param int $stackPtr The position of the current token in the stack passed in $tokens
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		$namespaceTokens = array(T_STRING, T_NS_SEPARATOR);
		$functionOrDelimiterTokens = array_merge(
			PHP_CodeSniffer_Tokens::$emptyTokens,
			$namespaceTokens,
			array(T_BITWISE_AND, T_BITWISE_OR, T_BOOLEAN_NOT)
		);

		switch ($tokens[$stackPtr]['code']) {
			case T_NAMESPACE:
				$namespace = '';
				$namespacePtr = $stackPtr;
				$nextToken = $phpcsFile->findNext($namespaceTokens, $namespacePtr);
				while (in_array($tokens[$nextToken]['code'], $namespaceTokens)) {
					$namespace .= $tokens[$nextToken++]['content'];
				}
				$this->namespace = '\\' . $namespace . '\\';
				return;
			case T_USE:
				$namespace = '';
				$namespacePtr = $stackPtr;
				$alias = '';
				$nextToken = $phpcsFile->findNext($namespaceTokens, $namespacePtr);
				while (in_array($tokens[$nextToken]['code'], $namespaceTokens)) {
					$alias = $tokens[$nextToken++]['content'];
					$namespace .= $alias;
				}
				if ($namespace{0} !== '\\') $namespace = '\\' . $namespace;

				// Is there an alias used?
				$nextToken = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, $nextToken, null, true);
				if ($tokens[$nextToken]['code'] === T_AS) {
					$nextToken = $phpcsFile->findNext(T_STRING, $nextToken);
					$alias = $tokens[$nextToken]['content'];
				}
				$this->uses[$alias] = $namespace;
				return;
		}

		// Skip tokens that are the names of functions or classes within their definitions.
		// For example: function myFunction... "myFunction" is T_STRING but we should skip
		// because it is not a function or method *call*.
		$functionName = $stackPtr;
		$findTokens = array_merge(
			PHP_CodeSniffer_Tokens::$emptyTokens,
			array(T_BITWISE_AND)
		);

		$functionKeyword = $phpcsFile->findPrevious($findTokens, ($stackPtr - 1), null, true);

		if ($tokens[$functionKeyword]['code'] === T_FUNCTION
			|| $tokens[$functionKeyword]['code'] === T_CLASS) {
		
			return;
		}

		// If the next non-whitespace token after the function or method call is not an
		// opening parenthesis then it can't really be a *call*.
		$openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($functionName + 1), null, true);

		if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}

		// Compute the complete function call (only works for static methods)
		//var_dump($this->getConstantName('T_', $tokens[$functionKeyword]['code']));
		if ($tokens[$functionKeyword]['code'] !== T_DOUBLE_COLON) {
			// Non static method
			return;
		}
		
		$function = '::' . $tokens[$stackPtr]['content'];
		$namespacePtr = $stackPtr - 2;
		$previousToken = $phpcsFile->findPrevious($functionOrDelimiterTokens, $namespacePtr);
		while (in_array($tokens[$previousToken]['code'], $namespaceTokens)) {
			$function = $tokens[$previousToken]['content'] . $function;
			$previousToken = $phpcsFile->findPrevious($functionOrDelimiterTokens, --$namespacePtr);
		}

		if ($function{0} !== '\\') {
			$function = $this->expandFunction($function);
		}

		var_dump($function);
	}

	protected function expandFunction($partialName) {
		list($prefix, $suffix) = explode('::', $partialName, 2);
		if (isset($this->uses[$prefix])) {
			$name = $this->uses[$prefix];
		} else {
			$name = $this->namespace;
		}
		return $name . '::' . $suffix;
	}

	// DEBUG
	private function getConstantName($category, $constantNumber) {
		foreach (get_defined_constants() as $key => $value) {
			if (strlen($key) > strlen($category)) {
				if (substr($key, 0, strlen($category)) === $category) {
					if ($value == $constantNumber) {
						return $key;
					}
				}
			}
		}
		return "No constant found.";
	}
}

PHP CodeSniffer for TYPO3
=========================

Purpose of this project is to automatically find outdated calls to deprecated API methods
of TYPO3. Since this uses a parser and not a static analysis from some compiled version of
the classes, only static calls may be analyzed.

Usage:

```
git clone https://github.com/xperseguers/PHP_CodeSniffer_TYPO3.git
cd PHP_CodeSniffer_TYPO3
composer install
vendor/bin/phpcs --config-set report_width auto

# Test files
vendor/bin/phpcs --standard=Standards/TYPO3 example/TestUtility.php
```


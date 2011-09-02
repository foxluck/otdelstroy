@echo off
set PHP_PEAR_PHP_BIN=@PHP-BIN@
set PHP_PEAR_BIN_DIR=@BIN-DIR@

:: Die if either php.exe or the script dir doesn't exist.
if not exist "%PHP_PEAR_BIN_DIR%" GOTO DIR_ERROR
if not exist "%PHP_PEAR_PHP_BIN%" GOTO BIN_ERROR

:: Go.
goto INSTALL

:DIR_ERROR
	echo PHP_PEAR_BIN_DIR is not set correctly.
	echo The current value is:%PHP_PEAR_BIN_DIR%
	goto END

:BIN_ERROR
	echo PHP_PEAR_PHP_BIN is not set correctly.
	echo The current value is:%PHP_PEAR_PHP_BIN%
	goto END

:INSTALL
	::Save the current path so that we can modify it and restore it later
	set OLD_PATH=%PATH%
	::Set the new path with PHP_PEAR_BIN_DIR included
	set PATH=%PATH%;%PHP_PEAR_BIN_DIR%
	"%PHP_PEAR_PHP_BIN%" -C -d output_buffering=1 -f "%PHP_PEAR_BIN_DIR%\paypal-sdk-update" %0 %1 %2 %3 %4
	::Restore the path to the previous
	set PATH=%OLD_PATH%
:END
	::Pause to display information
	pause

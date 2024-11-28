@echo off

cls 

set BinDir=../../vendor/bin
set AnalysisLevel=6
set OutputFile=./output.txt
set ConfigFile=./phpstan.neon

echo -------------------------------------------------------
echo RUNNING PHPSTAN @ LEVEL %AnalysisLevel%
echo -------------------------------------------------------

echo.

call %BinDir%/phpstan analyse -c %ConfigFile% -l %AnalysisLevel% > %OutputFile%

start "" "%OutputFile%"

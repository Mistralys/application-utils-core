<?php

use AppUtilsTestClasses\TestStubInterface1;
use AppUtilsTestClasses\TestStubInterface2;
use AppUtilsTestClasses\TestStubInterface3;
use AppUtilsTestClasses\TestStubClass;

class SingleClassMultiple extends TestStubClass implements TestStubInterface1, TestStubInterface2, TestStubInterface3
{
    
}

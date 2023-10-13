<?php

use AppUtilsTestClasses\TestStubInterface1;
use AppUtilsTestClasses\TestStubInterface2;
use AppUtilsTestClasses\TestStubClass;

class MultiClassOne
{
    
}

class MultiClassTwo extends TestStubClass
{
    
}

class MultiClassThree extends MultiClassOne implements TestStubInterface1, TestStubInterface2
{
    
}

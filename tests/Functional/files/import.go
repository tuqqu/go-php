package main

import (
    "test_package_1"
    "test_package_2"
)

func main() {
    TestPrint()

    test_package_2.TestPrint()
    test_package_1.TestPrint()

    println(test_package_1.X)

    test_package_1.TestPrintCounter()
    test_package_1.C++
    test_package_1.TestPrintCounter()
}

func TestPrint() {
    println("print_from_main")
}
